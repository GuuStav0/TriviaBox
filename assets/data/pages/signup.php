<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriviaBox - Crie Sua Conta</title>
    <link rel="stylesheet" href="../../styles/bootstrap_styles/bootstrap.css">
    <link rel="stylesheet" href="../../styles/auth-page.css">
    <link rel="shortcut icon" href="../images/LogoWhite.svg" type="image/x-icon">
</head>

<body class="auth-page">
    <main>
        <div class="auth-container">
            <div class="auth-card">
                <h2>Crie Sua Conta TriviaBox</h2>
                <hr class="divisor">
                <form action="#" method="POST">
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

</html>