<?php
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

require_once __DIR__ . '/../../../../conexao.php';

$userId = (int) $_SESSION['usuario_id'];
$quizId = isset($_POST['quiz_id']) ? (int) $_POST['quiz_id'] : 0;

if ($quizId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do quiz inválido']);
    exit;
}

// prepare images directory
$dataImagesDir = realpath(__DIR__ . '/../../images');
if ($dataImagesDir === false) {
    $dataImagesDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'images';
}
$uploadDir = rtrim($dataImagesDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    if (!isset($conexao) || !$conexao instanceof mysqli) {
        throw new Exception('Conexão ao banco de dados não disponível');
    }

    // verify quiz and ownership
    $stmt = $conexao->prepare('SELECT id, criador_id, capa FROM quizzes WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $quizId);
    $stmt->execute();
    $res = $stmt->get_result();
    $quiz = $res->fetch_assoc();
    $stmt->close();

    if (!$quiz) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Quiz não encontrado']);
        exit;
    }

    if ((int)$quiz['criador_id'] !== $userId) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Permissão negada']);
        exit;
    }

    $conexao->begin_transaction();

    // fetch perguntas images
    $pstmt = $conexao->prepare('SELECT imagem FROM perguntas WHERE quiz_id = ?');
    $pstmt->bind_param('i', $quizId);
    $pstmt->execute();
    $pres = $pstmt->get_result();
    $imgList = [];
    while ($row = $pres->fetch_assoc()) {
        if (!empty($row['imagem'])) $imgList[] = $row['imagem'];
    }
    $pstmt->close();

    // delete perguntas
    $d1 = $conexao->prepare('DELETE FROM perguntas WHERE quiz_id = ?');
    $d1->bind_param('i', $quizId);
    $d1->execute();
    $d1->close();

    // delete pontuações relacionadas
    $d2 = $conexao->prepare('DELETE FROM pontuacoes WHERE quiz_id = ?');
    $d2->bind_param('i', $quizId);
    $d2->execute();
    $d2->close();

    // delete quiz
    $d3 = $conexao->prepare('DELETE FROM quizzes WHERE id = ?');
    $d3->bind_param('i', $quizId);
    $d3->execute();
    $d3->close();

    // attempt to remove files (question images and cover)
    foreach ($imgList as $img) {
        $file = $uploadDir . basename($img);
        if (is_file($file)) {@unlink($file);} 
    }
    if (!empty($quiz['capa'])) {
        $coverFile = $uploadDir . basename($quiz['capa']);
        if (is_file($coverFile)) {@unlink($coverFile);} 
    }

    $conexao->commit();

    echo json_encode(['success' => true, 'message' => 'Quiz excluído com sucesso']);
    exit;
} catch (Throwable $e) {
    if (isset($conexao) && $conexao instanceof mysqli) {
        try { $conexao->rollback(); } catch (Throwable $ee) {}
    }
    error_log('[deleteQuiz] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir quiz']);
    exit;
}

?>
