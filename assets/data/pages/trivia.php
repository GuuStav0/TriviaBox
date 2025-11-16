<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$nome_do_usuario = $_SESSION['usuario_nome'];
// current user id
$usuario_id = isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : 0;

// fetch quiz id from GET
$quizId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$quiz = null;
$questions = [];
if ($quizId > 0) {
    try {
        require_once __DIR__ . '/../../../conexao.php';
            if (isset($conexao) && $conexao instanceof mysqli) {
            $stmt = $conexao->prepare('SELECT q.id, q.titulo, q.descricao, q.capa, q.criador_id, u.nome AS criador_nome FROM quizzes q LEFT JOIN usuarios u ON q.criador_id = u.id WHERE q.id = ? AND q.status = 1');
            $stmt->bind_param('i', $quizId);
            $stmt->execute();
            $res = $stmt->get_result();
            $quiz = $res->fetch_assoc();
            $stmt->close();

            if ($quiz) {
                $qstmt = $conexao->prepare('SELECT id, posicao, texto, imagem, op1, op2, op3, op4, correta FROM perguntas WHERE quiz_id = ? ORDER BY posicao ASC');
                $qstmt->bind_param('i', $quizId);
                $qstmt->execute();
                $qres = $qstmt->get_result();
                while ($row = $qres->fetch_assoc()) {
                    $questions[] = $row;
                }
                $qstmt->close();
                    // fetch top scores for this quiz (join with usuarios)
                    $scores = [];
                    $sstmt = $conexao->prepare('SELECT p.pontuacao, p.data, u.nome FROM pontuacoes p JOIN usuarios u ON p.usuario_id = u.id WHERE p.quiz_id = ? ORDER BY p.pontuacao DESC, p.data DESC LIMIT 20');
                    if ($sstmt) {
                        $sstmt->bind_param('i', $quizId);
                        $sstmt->execute();
                        $sres = $sstmt->get_result();
                        while ($srow = $sres->fetch_assoc()) {
                            $scores[] = $srow;
                        }
                        $sstmt->close();
                    }
                    // fetch current user's score for this quiz (if any)
                    $userScore = null;
                    if ($usuario_id) {
                        $us = $conexao->prepare('SELECT pontuacao FROM pontuacoes WHERE usuario_id = ? AND quiz_id = ? LIMIT 1');
                        if ($us) {
                            $us->bind_param('ii', $usuario_id, $quizId);
                            $us->execute();
                            $ures = $us->get_result();
                            $row = $ures->fetch_assoc();
                            if ($row && isset($row['pontuacao'])) $userScore = (int)$row['pontuacao'];
                            $us->close();
                        }
                    }
            }
        }
    } catch (Throwable $e) {
        // ignore — $quiz null will show error
    }
}

$questionsJson = json_encode($questions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriviaBox</title>
    <link id="favicon" rel="shortcut icon" href="./../images/LogoBlack.svg" type="image/x-icon">
    <link rel="stylesheet" href="./../../styles/bootstrap_styles/bootstrap.css">
    <link rel="stylesheet" href="./../../styles/trivia-page.css">
</head>

<body>
    <?php include_once './elements/header.php'; ?>

    <main class="container my-4">
        <?php if (!$quiz): ?>
            <div class="alert alert-warning">Quiz não encontrado ou indisponível.</div>
        <?php else: ?>
            <div class="row">
                <div class="col-12 col-lg-8">
                    <div id="quizPlay" class="card shadow-sm" data-cover="<?= htmlspecialchars($quiz['capa'] ?? 'placeholder-cover.png') ?>" data-user-score="<?= isset($userScore) ? (int)$userScore : '' ?>">
                        <div class="card-body">
                            <div id="flashContainer" class="mb-2"></div>
                            <div id="progressDots" class="progress-dots mb-2 d-none" aria-hidden="true"></div>
                            <div id="quizCounterText" class="quiz-counter-text small text-dark mb-2 text-center d-none" aria-hidden="true"></div>
                            <div class="quiz-image mb-3 text-center" data-cover="<?= htmlspecialchars($quiz['capa'] ?? 'placeholder-cover.png') ?>">
                                <img id="questionImage" src="../images/placeholder-cover.png" alt="Capa" class="img-fluid" style="max-height:360px; object-fit:cover; width:100%;">
                                <div id="imagePlaceholder" class="image-placeholder d-none"></div>
                                <?php if (isset($userScore) && $userScore !== null): ?>
                                    <div id="userCompletedBadge" class="position-absolute" style="top:12px; left:12px;">
                                        <span class="badge bg-success">Concluído</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="text-center mb-3">
                                <button id="startQuizBtn" class="btn btn-primary">Iniciar Quiz</button>
                            </div>
                            <div class="quiz-question">
                                <h4 id="questionText">Carregando pergunta...</h4>
                            </div>

                            <div id="answersList" class="mt-3 d-grid gap-2">
                                <!-- Answer buttons injected here -->
                            </div>
                            <div id="quizProgress" class="mt-3 text-muted small">Pergunta <span id="currentIndex">0</span> de <span id="totalQuestions">0</span></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <h5 class="card-title mb-0"><?= htmlspecialchars($quiz['titulo']) ?></h5>
                                <?php if (isset($quiz['criador_id']) && (int)$quiz['criador_id'] === $usuario_id): ?>
                                    <a href="./create_quiz.php?id=<?= $quizId ?>" class="btn btn-sm btn-outline-secondary ms-2">Editar</a>
                                <?php endif; ?>
                            </div>
                            <p class="card-text text-muted"><?= htmlspecialchars($quiz['descricao']) ?></p>
                            <?php if (!empty($quiz['criador_nome'])): ?>
                                <div class="mb-2 small text-muted">Criado por: <strong><?= htmlspecialchars($quiz['criador_nome']) ?></strong></div>
                            <?php endif; ?>
                            <div class="mb-2"><strong>Total:</strong> <?= count($questions) ?> perguntas</div>
                            <div id="resultPanel" style="display:none;">
                                <h6>Quiz finalizado</h6>
                                <p id="scoreText"></p>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title">Placar — Top 20 jogadores</h6>
                            <?php if (!empty($scores)): ?>
                                <ol class="list-group list-group-numbered">
                                    <?php foreach ($scores as $sc): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?= htmlspecialchars($sc['nome']) ?></strong>
                                                <div class="small text-muted"><?= htmlspecialchars(date('d/m H:i', strtotime($sc['data']))) ?></div>
                                            </div>
                                            <span class="badge bg-primary rounded-pill"><?= (int)$sc['pontuacao'] ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>
                            <?php else: ?>
                                <div class="text-muted small">Nenhuma pontuação registrada ainda.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
    <?php include_once './elements/footer.php'; ?>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="../../scripts/bootstrap_scripts/bootstrap.bundle.js"></script>
    <script src="./../../scripts/matchMedia.js"></script>
    <script>
        window.__TRIVIA_QUESTIONS__ = <?php echo $questionsJson ?: '[]'; ?>;
        window.__TRIVIA_ID__ = <?php echo json_encode($quizId); ?>;
    </script>
    <script src="../../scripts/triviaScript.js"></script>
</body>

</html>