<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
	echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
	exit();
}

require_once './../../../../conexao.php';

$usuario_id = (int) $_SESSION['usuario_id'];

$quiz_id = isset($_POST['quiz_id']) ? (int) $_POST['quiz_id'] : 0;
$pontuacao = isset($_POST['pontuacao']) ? (int) $_POST['pontuacao'] : null;

if ($quiz_id <= 0 || $pontuacao === null) {
	echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
	exit();
}

// Do not register zero scores
if ($pontuacao === 0) {
	echo json_encode(['success' => true, 'action' => 'ignored', 'message' => 'Pontuação zero não registrada']);
	exit();
}

try {
	if (!isset($conexao) || !($conexao instanceof mysqli)) {
		throw new Exception('Erro na conexão com o banco de dados');
	}

	// Check if there is already a score for this user and quiz
	$check = $conexao->prepare('SELECT id, pontuacao FROM pontuacoes WHERE usuario_id = ? AND quiz_id = ? LIMIT 1');
	if (!$check) throw new Exception($conexao->error);
	$check->bind_param('ii', $usuario_id, $quiz_id);
	$check->execute();
	$cres = $check->get_result();
	$existing = $cres->fetch_assoc();
	$check->close();

	if ($existing && isset($existing['id'])) {
		// existing best
		$existingId = (int) $existing['id'];
		$existingBest = isset($existing['pontuacao']) ? (int)$existing['pontuacao'] : 0;
		if ($pontuacao > $existingBest) {
			// update existing record with new higher score
			$ustmt = $conexao->prepare('UPDATE pontuacoes SET pontuacao = ?, `data` = CURRENT_TIMESTAMP() WHERE id = ?');
			if (!$ustmt) throw new Exception($conexao->error);
			$ustmt->bind_param('ii', $pontuacao, $existingId);
			$ok = $ustmt->execute();
			if (!$ok) throw new Exception($ustmt->error ?: 'Erro ao atualizar pontuação');
			echo json_encode(['success' => true, 'action' => 'updated', 'id' => $existingId, 'best' => $pontuacao]);
			exit();
		} else {
			// keep existing best
			echo json_encode(['success' => true, 'action' => 'kept', 'id' => $existingId, 'best' => $existingBest]);
			exit();
		}
	} else {
		// insert new
		$stmt = $conexao->prepare('INSERT INTO pontuacoes (usuario_id, quiz_id, pontuacao) VALUES (?, ?, ?)');
		if (!$stmt) throw new Exception($conexao->error);
		$stmt->bind_param('iii', $usuario_id, $quiz_id, $pontuacao);
		$ok = $stmt->execute();
		if (!$ok) {
			throw new Exception($stmt->error ?: 'Erro ao inserir pontuação');
		}
		echo json_encode(['success' => true, 'action' => 'inserted', 'id' => $conexao->insert_id, 'best' => $pontuacao]);
		exit();
	}
} catch (Throwable $e) {
	// log server error for debugging
	error_log('[submitScore] ' . $e->getMessage());
	// also include POST context for debugging (avoid logging sensitive data)
	error_log('[submitScore] POST: ' . json_encode(array_intersect_key($_POST, array_flip(['quiz_id','pontuacao']))));

	// return a safe JSON error message
	echo json_encode(['success' => false, 'message' => 'Erro interno ao salvar pontuação. Verifique logs.']);
	exit();
}

