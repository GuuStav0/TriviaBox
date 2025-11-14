<?php
error_reporting(E_ALL); 
ini_set('display_errors', 1); 

include '../../../conexao.php'; 

$mensagem_erro = "";
$mensagem_sucesso = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_input = $_POST['username']; 
    $password_input = $_POST['password']; 


    $stmt = $conexao->prepare("SELECT id, nome, senha_hash FROM usuarios WHERE nome = ?");
    $stmt->bind_param("s", $username_input);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $linha = $resultado->fetch_assoc();

        if (password_verify($password_input, $linha['senha_hash'])) { 
            $mensagem_sucesso = "Login realizado com sucesso!";
            session_start();
            $_SESSION['usuario_id'] = $linha['id'];
            $_SESSION['usuario_nome'] = $linha['nome']; 
            
            
            header("Location: ./home.php"); 
            exit();
        } else {
            $mensagem_erro = "Senha incorreta.";
        }
    } else {
        $mensagem_erro = "Usuário não encontrado.";
    }
    $stmt->close();
}

$conexao->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriviaBox - Acesse Sua Conta</title>
    <link rel="stylesheet" href="../../styles/bootstrap_styles/bootstrap.css">
    <link rel="stylesheet" href="../../styles/auth-page.css">
    <link id="favicon" rel="shortcut icon" href="../images/LogoWhite.svg" type="image/x-icon">
</head>

<body class="auth-page">
    <main>
        <section class="auth-container">
            <div class="auth-card">
                <div class="text-center mb-3 auth-logo">
                    <img src="../images/LogoBlack.svg" alt="TriviaBox logo" style="height:64px; max-width:100%; pointer-events: none;" />
                </div>
                <h2>Entrar</h2>
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

                <form action="login.php" method="POST"> 
                    <div class="mb-3">
                        <label for="username" class="form-label fw-bold">Usuário:</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3 input-with-icon has-toggle">
                        <label for="password" class="form-label fw-bold">Senha:</label>
                        <input type="password" class="form-control input-password" id="password" name="password" required>
                        <button type="button" class="input-toggle" aria-label="Mostrar senha">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary fw-bold">Entrar</button>
                    </div>
                </form>
                <p class="mt-3">Não tem uma conta? <a href="./signup.php">Cadastre-se.</a></p>
                <p class="">Não lembra a Senha? <a href="./recovery.php">Recuperar Senha.</a></p>
            </div>
        </section>
    </main>
</body>
<script src="../../scripts/bootstrap_scripts/bootstrap.bundle.js"></script>
<script>
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
<script src="./../../scripts/bootstrap_scripts/bootstrap.bundle.js"></script>
<script src="https://kit.fontawesome.com/02f69001e4.js" crossorigin="anonymous"></script>
<script src="./../../scripts/matchMedia.js"></script>

</html>