<?php
require_once __DIR__ . '/../../../../conexao.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

$userId = $_SESSION['usuario_id'];

// Basic expected fields
 $title = trim($_POST['quiz_title'] ?? '');
 $description = trim($_POST['quiz_description'] ?? '');
 $categoryId = isset($_POST['categoria_id']) ? (int) $_POST['categoria_id'] : 0;
 $questionsJson = $_POST['questions_json'] ?? null;

if (!$title || !$description || !$questionsJson || !$categoryId) {
    http_response_code(400);
    $missing = [];
    if (!$title) $missing[] = 'quiz_title';
    if (!$description) $missing[] = 'quiz_description';
    if (!$questionsJson) $missing[] = 'questions_json';
    if (!$categoryId) $missing[] = 'categoria_id';
    echo json_encode(['success' => false, 'message' => 'Campos obrigatórios ausentes (título, descrição, categoria ou perguntas).', 'missing' => $missing]);
    exit;
}

$questions = json_decode($questionsJson, true);
if (!is_array($questions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Formato de perguntas inválido.']);
    exit;
}

$quizIdFromPost = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;

// Prepare upload directory: use `assets/data/images/uploads` (images folder inside data)
$dataImagesDir = realpath(__DIR__ . '/../../images');
if ($dataImagesDir === false) {
    // fallback to creating the directory relative to this script
    $dataImagesDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'images';
}
$quizUploadDir = rtrim($dataImagesDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads';
if (!is_dir($quizUploadDir)) {
    if (!mkdir($quizUploadDir, 0755, true) && !is_dir($quizUploadDir)) {
        echo json_encode(['success' => false, 'message' => 'Falha ao criar diretório de uploads dentro de assets/data/images.']);
        exit;
    }
}

function saveUploadedFile($fileField, $destDir)
{
    if (!isset($_FILES[$fileField]) || !is_uploaded_file($_FILES[$fileField]['tmp_name'])) return null;
    $f = $_FILES[$fileField];
    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
    $safe = bin2hex(random_bytes(8)) . '_' . time() . ($ext ? '.' . $ext : '');
    $dest = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safe;
    if (move_uploaded_file($f['tmp_name'], $dest)) {
        // return only the filename (basename) to store in DB
        return $safe;
    }
    return null;
}

// save main cover if provided
$coverPath = null;
if (isset($_FILES['quiz_cover']) && is_uploaded_file($_FILES['quiz_cover']['tmp_name'])) {
    $coverPath = saveUploadedFile('quiz_cover', $quizUploadDir);
}

// save question images if any (keys: question_image_0, question_image_1, ... or as provided)
$questionImages = [];
foreach ($_FILES as $k => $v) {
    if (strpos($k, 'question_image_') === 0) {
        $questionImages[$k] = saveUploadedFile($k, $quizUploadDir);
    }
}

// enable mysqli exceptions for cleaner error handling
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // $conexao is created in conexao.php
    if (!isset($conexao) || !$conexao instanceof mysqli) {
        throw new Exception('Conexão ao banco de dados não encontrada.');
    }

    $conexao->begin_transaction();

    $status = 1;
    // ensure capa fallback
    $placeholder = 'placeholder-cover.png';

    if ($quizIdFromPost > 0) {
        // EDIT path: verify owner and update quiz
        $vstmt = $conexao->prepare('SELECT id, criador_id, capa FROM quizzes WHERE id = ? LIMIT 1');
        $vstmt->bind_param('i', $quizIdFromPost);
        $vstmt->execute();
        $vres = $vstmt->get_result();
        $existingQuiz = $vres->fetch_assoc();
        $vstmt->close();

        if (!$existingQuiz) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Quiz não encontrado para edição.']);
            exit;
        }
        if ((int)$existingQuiz['criador_id'] !== (int)$userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Permissão negada para editar este quiz.']);
            exit;
        }

        // fetch old perguntas (texto, options, correta, imagem) ordered by posicao
        $oldQuestions = [];
        $pi = $conexao->prepare('SELECT texto, op1, op2, op3, op4, correta, imagem FROM perguntas WHERE quiz_id = ? ORDER BY posicao ASC');
        $pi->bind_param('i', $quizIdFromPost);
        $pi->execute();
        $pres = $pi->get_result();
        while ($r = $pres->fetch_assoc()) {
            $oldQuestions[] = $r;
        }
        $pi->close();

        // Determine final cover: if new uploaded ($coverPath) use it, else keep existing
        $finalCover = $existingQuiz['capa'] ?? $placeholder;
        if (!empty($coverPath)) {
            $finalCover = $coverPath;
            // remove old cover file if exists and not placeholder
            if (!empty($existingQuiz['capa']) && basename($existingQuiz['capa']) !== $placeholder) {
                $oldCoverFile = rtrim($quizUploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($existingQuiz['capa']);
                if (is_file($oldCoverFile)) {@unlink($oldCoverFile);} 
            }
        }

        // Update quiz row
        $ust = $conexao->prepare('UPDATE quizzes SET titulo = ?, descricao = ?, capa = ?, categorias_id = ?, status = ? WHERE id = ?');
        $ust->bind_param('sssiii', $title, $description, $finalCover, $categoryId, $status, $quizIdFromPost);
        $ust->execute();
        $ust->close();

        // Determine if textual changes were made that should clear scores
        $shouldClearScores = false;
        // if counts differ, it's a structural change => clear scores
        if (count($questions) !== count($oldQuestions)) {
            $shouldClearScores = true;
        } else {
            // compare each question's text, options and correta (ignore imagem differences)
            for ($i = 0; $i < count($questions); $i++) {
                $newQ = $questions[$i];
                $oldQ = $oldQuestions[$i];
                $newText = trim((string)($newQ['question'] ?? ''));
                $oldText = trim((string)($oldQ['texto'] ?? ''));
                if ($newText !== $oldText) { $shouldClearScores = true; break; }
                $newOpts = $newQ['options'] ?? [];
                $newOpts = array_pad($newOpts, 4, '');
                for ($j = 0; $j < 4; $j++) {
                    $nopt = trim((string)$newOpts[$j]);
                    $oopt = trim((string)($oldQ['op' . ($j+1)] ?? ''));
                    if ($nopt !== $oopt) { $shouldClearScores = true; break 2; }
                }
                $newCorrect = isset($newQ['correct']) ? (int)$newQ['correct'] : 0;
                $oldCorrect = isset($oldQ['correta']) ? (int)$oldQ['correta'] : 0;
                if ($newCorrect !== $oldCorrect) { $shouldClearScores = true; break; }
            }
        }

        // Remove old perguntas (we'll re-insert from submitted data)
        $d1 = $conexao->prepare('DELETE FROM perguntas WHERE quiz_id = ?');
        $d1->bind_param('i', $quizIdFromPost);
        $d1->execute();
        $d1->close();

        // Insert new perguntas, tracking which old images were reused/kept
        $qStmt = $conexao->prepare('INSERT INTO perguntas (quiz_id, posicao, texto, imagem, op1, op2, op3, op4, correta, criado_em) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $keptOldImages = [];
        foreach ($questions as $i => $q) {
            $pos = $i + 1;
            $qText = $q['question'] ?? '';
            $opts = $q['options'] ?? [];
            $opts = array_pad($opts, 4, '');
            $correct = isset($q['correct']) ? (int) $q['correct'] : 0;

            $imgKey = 'question_image_' . $i;
            $imgPath = $questionImages[$imgKey] ?? null;
            // if no uploaded file for this question, check for existing hidden field from form
            if (empty($imgPath) && isset($_POST['existing_qimage_' . $i])) {
                $imgPath = trim($_POST['existing_qimage_' . $i]) ?: null;
            }

            // if we uploaded a new image for this position and there was an old image at same position, mark old for deletion later
            if (!empty($imgPath)) {
                $keptOldImages[] = $imgPath;
            }

            $qStmt->bind_param('iissssssi', $quizIdFromPost, $pos, $qText, $imgPath, $opts[0], $opts[1], $opts[2], $opts[3], $correct);
            $qStmt->execute();
        }
        $qStmt->close();

        // Build list of old images (from previously fetched perguntas) for cleanup
        $oldImages = [];
        foreach ($oldQuestions as $oq) {
            if (!empty($oq['imagem'])) $oldImages[] = $oq['imagem'];
        }

        // Cleanup: delete old images that are not present in $keptOldImages
        foreach ($oldImages as $oldImg) {
            if (empty($oldImg)) continue;
            if (in_array($oldImg, $keptOldImages, true)) continue;
            $f = rtrim($quizUploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($oldImg);
            if (is_file($f)) {@unlink($f);} 
        }

        // If textual changes occurred, clear pontuações for this quiz
        if (!empty($shouldClearScores)) {
            $clr = $conexao->prepare('DELETE FROM pontuacoes WHERE quiz_id = ?');
            $clr->bind_param('i', $quizIdFromPost);
            $clr->execute();
            $clr->close();
        }

        $conexao->commit();
        echo json_encode(['success' => true, 'message' => 'Quiz atualizado com sucesso.', 'quiz_id' => $quizIdFromPost, 'scores_cleared' => !empty($shouldClearScores)]);
        exit;
    } else {
        // INSERT new quiz
        if (empty($coverPath)) {
            $coverPath = $placeholder;
        }
        $stmt = $conexao->prepare('INSERT INTO quizzes (titulo, descricao, capa, categorias_id, criador_id, status, criado_em) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->bind_param('sssiii', $title, $description, $coverPath, $categoryId, $userId, $status);
        $stmt->execute();
        $quizId = $conexao->insert_id;
        $stmt->close();

        // Insert questions into `perguntas` table (quiz_id, posicao, texto, imagem, op1..op4, correta)
        $qStmt = $conexao->prepare('INSERT INTO perguntas (quiz_id, posicao, texto, imagem, op1, op2, op3, op4, correta, criado_em) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');

        foreach ($questions as $i => $q) {
            $pos = $i + 1;
            $qText = $q['question'] ?? '';
            $opts = $q['options'] ?? [];
            $opts = array_pad($opts, 4, '');
            $correct = isset($q['correct']) ? (int) $q['correct'] : 0;

            $imgKey = 'question_image_' . $i;
            $imgPath = $questionImages[$imgKey] ?? null;

            $qStmt->bind_param('iissssssi', $quizId, $pos, $qText, $imgPath, $opts[0], $opts[1], $opts[2], $opts[3], $correct);
            $qStmt->execute();
        }
        $qStmt->close();

        $conexao->commit();

        echo json_encode(['success' => true, 'message' => 'Quiz salvo com sucesso.', 'quiz_id' => $quizId, 'scores_cleared' => false]);
        exit;
    }
} catch (Exception $e) {
    if (isset($conexao) && $conexao instanceof mysqli && $conexao->errno) {
        // try rollback if in transaction
        try {
            $conexao->rollback();
        } catch (Exception $ee) {
        }
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar quiz: ' . $e->getMessage()]);
    exit;
}
