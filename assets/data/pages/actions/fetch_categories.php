<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../../conexao.php';

$q = trim($_GET['q'] ?? '');
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 25;
if ($limit <= 0 || $limit > 100) $limit = 25;

$results = [];
try {
    if (!isset($conexao) || !$conexao instanceof mysqli) throw new Exception('DB connection not found');

    if ($q === '') {
        // return most used or first N categories to help user (ordered by name)
        $stmt = $conexao->prepare('SELECT id, nome FROM categorias ORDER BY nome LIMIT ?');
        $stmt->bind_param('i', $limit);
    } else {
        $like = '%' . $q . '%';
        $stmt = $conexao->prepare('SELECT id, nome FROM categorias WHERE nome LIKE ? ORDER BY nome LIMIT ?');
        $stmt->bind_param('si', $like, $limit);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $results[] = $row;
    $stmt->close();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

echo json_encode(['success' => true, 'data' => $results]);
exit;
