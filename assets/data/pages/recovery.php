<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriviaBox - Recuperar Senha</title>
    <link rel="stylesheet" href="../../styles/bootstrap_styles/bootstrap.css">
    <link rel="stylesheet" href="../../styles/auth-page.css">
    <link rel="shortcut icon" href="../images/LogoWhite.svg" type="image/x-icon">
</head>

<body class="auth-page recovery-page">
    <main>
        <div class="recovery-container">
            <h1 class="fw-bold">Recuperar Senha</h1>
            <p>Insira seu e-mail para redefinir sua senha.</p>
            <hr class="divisor">
            <form action="#" method="POST">
                <div class="mb-3 input-with-icon">
                    <label for="email" class="form-label fw-bold">*Email:</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Digite seu e-mail." required>
                    <button type="button" class="input-info" aria-label="Ajuda email"
                        data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right"
                        data-bs-content="Informe um email válido (ex: voce@exemplo.com).">
                        <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary fw-bold">Entrar</button>
                </div>
            </form>
            <p class="mt-5">Voltar para o <a href="./login.php">Login.</a></p>
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
    });
</script>
<script src="https://kit.fontawesome.com/02f69001e4.js" crossorigin="anonymous"></script>

</html>