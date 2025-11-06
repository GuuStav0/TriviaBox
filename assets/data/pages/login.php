<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriviaBox - Acesse Sua Conta</title>
    <link rel="stylesheet" href="../../styles/bootstrap_styles/bootstrap.css">
    <link rel="stylesheet" href="../../styles/auth-page.css">
    <link rel="shortcut icon" href="../images/LogoWhite.svg" type="image/x-icon">
</head>

<body class="auth-page">
    <main>
        <section class="auth-container">
            <div class="auth-card">
                <h2>Entrar</h2>
                <hr class="divisor">
                <form action="#" method="POST">
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
<script src="https://kit.fontawesome.com/02f69001e4.js" crossorigin="anonymous"></script>

</html>