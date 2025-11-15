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
    echo json_encode(['success' => false, 'message' => 'Campos obrigatórios ausentes (título, descrição, categoria ou perguntas).']);
    exit;
}

$questions = json_decode($questionsJson, true);
if (!is_array($questions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Formato de perguntas inválido.']);
    exit;
}

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

    // Insert quiz into your schema (quizzes table uses: titulo, descricao, capa, categorias_id, criador_id)
    $status = 1;
    // ensure capa is not null - use placeholder if none uploaded
    if (empty($coverPath)) {
        $coverPath = 'placeholder-cover.png';
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

    echo json_encode(['success' => true, 'message' => 'Quiz salvo com sucesso.', 'quiz_id' => $quizId]);
    exit;
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
