<?php
session_start();

// 1. VERIFICAÇÃO DE AUTENTICAÇÃO
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = (int) $_SESSION['usuario_id'];
$nome_do_usuario = $_SESSION['usuario_nome'] ?? 'Usuário'; 

// Variáveis de Exibição com valor padrão em caso de falha no DB
$criado_em_formatado = 'N/A';
$avatar_url = './../images/default-avatar.png'; 
$user_email = 'E-mail não carregado'; 

// Variáveis para Estatísticas (iniciadas em 0/vazio)
$stats = [
    'quizzes_criados' => 0,
    'total_pontuacao' => 0, 
    'quizzes_concluidos' => 0, 
    'maior_acerto' => 0, 
];
$last_quizzes = []; 

// Variáveis para mensagens de status (Recebidas de update_profile.php)
$status_message = $_GET['message'] ?? '';
$status_type = $_GET['status'] ?? ''; // 'success' ou 'error'

try {
    // 2. INCLUSÃO DA CONEXÃO COM O BANCO
    // ✅ CAMINHO CORRIGIDO: subindo 3 níveis (pages/auth/ -> raiz)
    require_once __DIR__ . '/../../../conexao.php'; 

    if (isset($conexao) && $conexao instanceof mysqli) {
        
        $stmt = $conexao->prepare('SELECT id, nome, email, criado_em FROM usuarios WHERE id = ? LIMIT 1');
        
        if (!$stmt) {
             throw new Exception("Falha ao preparar a query de usuário: " . $conexao->error);
        }

        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $usuario = $res->fetch_assoc(); 
        $stmt->close();
        
        if ($usuario) {
            // ✅ CORREÇÃO 1 & 2: Atribuição correta do E-mail e Data
            $user_email = htmlspecialchars($usuario['email'] ?? 'E-mail não encontrado', ENT_QUOTES, 'UTF-8'); 
            $criado_em_formatado = isset($usuario['criado_em']) ? date('d/m/Y', strtotime($usuario['criado_em'])) : 'N/A';
            
            $nome_do_usuario = htmlspecialchars($usuario['nome']);
            $avatar_url = $usuario['avatar_url'] ?? $avatar_url; 

            // B. BUSCAR ESTATÍSTICAS: Quizzes Criados
            $q_stmt = $conexao->prepare('SELECT COUNT(id) as total FROM quizzes WHERE criador_id = ? AND status = 1');
            $q_stmt->bind_param('i', $usuario_id);
            $q_stmt->execute();
            $stats['quizzes_criados'] = $q_stmt->get_result()->fetch_assoc()['total'];
            $q_stmt->close();
            
            // B2. BUSCAR ESTATÍSTICAS: Pontuação Total, Quizzes Concluídos e Maior Acerto
            $p_stmt = $conexao->prepare('
                SELECT 
                    COUNT(DISTINCT quiz_id) AS quizzes_concluidos, 
                    SUM(pontuacao) AS total_pontuacao,
                    MAX((pontuacao / total_perguntas) * 100) AS maior_acerto 
                FROM pontuacoes 
                WHERE usuario_id = ?
            ');
            $p_stmt->bind_param('i', $usuario_id);
            $p_stmt->execute();
            $p_res = $p_stmt->get_result()->fetch_assoc();
            $p_stmt->close();

            $stats['total_pontuacao'] = (int) ($p_res['total_pontuacao'] ?? 0);
            $stats['quizzes_concluidos'] = (int) ($p_res['quizzes_concluidos'] ?? 0);
            // Formatação do maior acerto para 2 casas decimais
            $stats['maior_acerto'] = (float) round($p_res['maior_acerto'] ?? 0, 2); 

            // C. BUSCAR ÚLTIMOS QUIZZES CRIADOS
            $lq_stmt = $conexao->prepare('SELECT id, titulo, capa, criado_em FROM quizzes WHERE criador_id = ? AND status = 1 ORDER BY criado_em DESC LIMIT 4');
            $lq_stmt->bind_param('i', $usuario_id);
            $lq_stmt->execute();
            $lq_res = $lq_stmt->get_result();
            while ($row = $lq_res->fetch_assoc()) {
                 $row['capa'] = $row['capa'] ?? './../data/images/default-cover.svg'; 
                 $last_quizzes[] = $row;
            }
            $lq_stmt->close();
        } 
        
        $conexao->close();
    } else {
        $status_message = "Erro de conexão: Variável \$conexao não disponível.";
        $status_type = 'error';
    }
} catch (Throwable $e) {
    $status_type = 'error';
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriviaBox - Perfil de <?php echo htmlspecialchars($nome_do_usuario); ?></title>
    <link rel="stylesheet" href="../../styles/bootstrap_styles/bootstrap.css">
    <link rel="stylesheet" href="../../styles/profile-page.css">
    <link id="favicon" rel="shortcut icon" href="../images/LogoWhite.svg" type="image/x-icon">
    <script src="https://kit.fontawesome.com/02f69001e4.js" crossorigin="anonymous"></script>
</head>

<body>
    <?php include_once './elements/header.php'; ?>

    <main class="container profile-content">
        <div class="row w-100 justify-content-center">
            <div class="col-lg-10 col-xl-8">
                
                <?php if (!empty($status_message)): ?>
                    <div class="alert alert-<?php echo $status_type === 'success' ? 'success' : 'danger'; ?> text-center mt-3" role="alert">
                        <?php echo htmlspecialchars($status_message); ?>
                    </div>
                <?php endif; ?>

                <div class="card profile-card">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-3 text-center">
                            <img src="<?php echo htmlspecialchars($avatar_url); ?>" class="profile-avatar mb-3">
                        </div>
                        
                        <div class="col-md-9">
                            <h2 class="profile-username text-md-start text-center"><?php echo htmlspecialchars($nome_do_usuario); ?></h2>
                            <p class="profile-member-since text-md-start text-center">Membro desde: *<?php echo htmlspecialchars($criado_em_formatado); ?>*</p>
                            
                            <form action="update_profile.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">E-mail</label>
                                    <p class="form-control-plaintext fw-bold">
                                        <?php echo $user_email; ?>
                                    </p>
                                    <input type="hidden" name="email" value="<?php echo $user_email; ?>">
                                </div>
                                <div class="mb-4">
                                    <label for="profileNewPassword" class="form-label">Nova Senha (deixe em branco para manter)</label>
                                    <div class="input-with-icon has-toggle">
                                        <button type="button" class="input-toggle" aria-label="Mostrar senha"><i class="fas fa-eye"></i></button>
                                        <input type="password" class="form-control" id="profileNewPassword" name="new-password">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                            </form>
                        </div>
                    </div>
                </div>

                <h3 class="mt-5 mb-3 text-white text-center">Minhas Estatísticas</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo number_format($stats['quizzes_criados'], 0, ',', '.'); ?></div>
                        <div class="stat-label">Quizzes Criados</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value"><?php echo number_format($stats['total_pontuacao'], 0, ',', '.'); ?></div> 
                        <div class="stat-label">Pontuação Total</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value"><?php echo number_format($stats['quizzes_concluidos'], 0, ',', '.'); ?></div> 
                        <div class="stat-label">Quizzes Concluídos</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-value"><?php echo number_format($stats['maior_acerto'], 0) . '%'; ?></div> 
                        <div class="stat-label">Maior Acerto</div>
                    </div>
                </div>
                
                <h3 class="mt-5 mb-4 text-white text-center">Meus Quizzes Mais Recentes</h3>
                <div class="row">
                    <?php if (!empty($last_quizzes)): ?>
                        <?php foreach ($last_quizzes as $quiz): ?>
                            <div class="col-md-3 col-sm-6 mb-4">
                                <a href="create_quiz.php?id=<?php echo $quiz['id']; ?>" class="card text-decoration-none h-100 shadow-sm">
                                    <img src="<?php echo htmlspecialchars($quiz['capa']); ?>" class="card-img-top" alt="Capa do Quiz" style="height: 120px; object-fit: cover;">
                                    <div class="card-body">
                                        <h5 class="card-title text-truncate"><?php echo htmlspecialchars($quiz['titulo']); ?></h5>
                                        <p class="card-text small text-muted">Criado em: <?php echo date('d/m/y', strtotime($quiz['criado_em'])); ?></p>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-white-50">Você ainda não criou nenhum quiz. <a href="create_quiz.php" class="text-white fw-bold">Comece agora!</a></p>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>

    <?php include_once './elements/footer.php'; // Ajuste o caminho se necessário ?>
    
    <script src="../../scripts/bootstrap_scripts/bootstrap.bundle.js"></script>
    <script src="./../../scripts/matchMedia.js"></script> 
    <script>
        // Lógica do botão de mostrar/ocultar senha
        document.addEventListener('DOMContentLoaded', function() {
            var toggles = [].slice.call(document.querySelectorAll('.input-toggle'));
            toggles.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var wrapper = btn.closest('.input-with-icon');
                    if (!wrapper) return;
                    var input = wrapper.querySelector('input');
                    if (!input) return;
                    var icon = btn.querySelector('i');
                    if (input.type === 'password') {
                        input.type = 'text';
                        if (icon) {
                            icon.classList.remove('fa-eye');
                            icon.classList.add('fa-eye-slash');
                        }
                        btn.setAttribute('aria-label', 'Ocultar senha');
                    } else {
                        input.type = 'password';
                        if (icon) {
                            icon.classList.remove('fa-eye-slash');
                            icon.classList.add('fa-eye');
                        }
                        btn.setAttribute('aria-label', 'Mostrar senha');
                    }
                });
            });
        });
    </script>
</body>

</html>