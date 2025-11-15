<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../../conexao.php';

$mensagem_erro = "";
$mensagem_sucesso = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $novo_usuario_input = $_POST['username'];
    $novo_email_input = $_POST['email'];
    $nova_senha_input = $_POST['password'];
    $confirmar_senha_input = $_POST['confirm-password'];

    if ($nova_senha_input !== $confirmar_senha_input) {
        $mensagem_erro = "As senhas não coincidem.";
    } else {

        $senha_hash_para_db = password_hash($nova_senha_input, PASSWORD_DEFAULT);


        $stmt_check_user = $conexao->prepare("SELECT id FROM usuarios WHERE nome = ?");
        $stmt_check_user->bind_param("s", $novo_usuario_input);
        $stmt_check_user->execute();
        $resultado_check_user = $stmt_check_user->get_result();


        $stmt_check_email = $conexao->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_check_email->bind_param("s", $novo_email_input);
        $stmt_check_email->execute();
        $resultado_check_email = $stmt_check_email->get_result();

        if ($resultado_check_user->num_rows > 0) {
            $mensagem_erro = "Nome de usuário já existe. Escolha outro.";
        } elseif ($resultado_check_email->num_rows > 0) {
            $mensagem_erro = "Este email já está cadastrado. Tente outro.";
        } else {

            $stmt_insert = $conexao->prepare("INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)");

            $stmt_insert->bind_param("sss", $novo_usuario_input, $novo_email_input, $senha_hash_para_db);

            if ($stmt_insert->execute()) {
                $mensagem_sucesso = "Cadastro realizado com sucesso! Faça login.";
            } else {
                $mensagem_erro = "Erro ao cadastrar: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt_check_user->close();
        $stmt_check_email->close();
    }
}

$conexao->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriviaBox - Crie Sua Conta</title>
    <link rel="stylesheet" href="../../styles/bootstrap_styles/bootstrap.css">
    <link rel="stylesheet" href="../../styles/auth-page.css">
    <link id="favicon" rel="shortcut icon" href="../images/LogoWhite.svg" type="image/x-icon">
</head>

<body class="auth-page">
    <main>
        <div class="auth-container">
            <div class="auth-card">
                <h2>Crie Sua Conta TriviaBox</h2>
                <hr class="divisor">

                <?php if ($mensagem_erro): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $mensagem_erro; ?>
                    </div>
                <?php endif; ?>
                <?php if ($mensagem_sucesso): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $mensagem_sucesso; ?>
                    </div>
                <?php endif; ?>

                <form action="signup.php" method="POST">
                    <div class="mb-3 input-with-icon">
                        <label for="username" class="form-label fw-bold">*Usuário:</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                        <button type="button" class="input-info" aria-label="Ajuda usuário"
                            data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right"
                            data-bs-content="3-20 caracteres; letras e números; sem espaços.">
                            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="mb-3 input-with-icon">
                        <label for="email" class="form-label fw-bold">*Email:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                        <button type="button" class="input-info" aria-label="Ajuda email"
                            data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right"
                            data-bs-content="Informe um email válido (ex: voce@exemplo.com).">
                            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="mb-3 input-with-icon has-two-icons">
                        <label for="password" class="form-label fw-bold">*Senha:</label>
                        <input type="password" class="form-control input-password" id="password" name="password" required>
                        <button type="button" class="input-toggle" aria-label="Mostrar senha">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="input-info" aria-label="Ajuda senha"
                            data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right"
                            data-bs-content="Mínimo 8 caracteres, inclua 1 letra maiúscula e 1 número.">
                            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="mb-3 input-with-icon">
                        <label for="confirm-password" class="form-label fw-bold">*Confirmar Senha:</label>
                        <input type="password" class="form-control" id="confirm-password" name="confirm-password" required>
                        <button type="button" class="input-info" aria-label="Ajuda senha"
                            data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right"
                            data-bs-content="Mínimo 8 caracteres, inclua 1 letra maiúscula e 1 número.">
                            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                        </button>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold">Cadastrar</button>
                </form>
                <p class="mt-3">Já tem uma conta? <a href="./login.php">Entrar.</a></p>
            </div>
        </div>
    </main>
</body>

<script src="../../scripts/bootstrap_scripts/bootstrap.bundle.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.forEach(function(el) {
            new bootstrap.Popover(el);
        });
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
<script src="https://kit.fontawesome.com/02f69001e4.js" crossorigin="anonymous"></script>
<script src="./../../scripts/matchMedia.js"></script>

</html>