<?php
require_once '../database/conexao.php';
// Seu código PHP de conexão, se houver
?>

<!DOCTYPE html>
<html lang="pt-BR">

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
        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>
            
            <div class="carousel-inner">
                
                <div class="carousel-item active">
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="text-center text-white p-5">
                            <h1 class="display-4 fw-bold">Bem-vindo(a) de Volta</h1>
                            <p class="lead mt-3">Sua jornada de conhecimento começa aqui.</p>
                        </div>
                    </div>
                </div>
                
                <div class="carousel-item">
                    <img src="caminho/para/sua/imagem-produto.jpg" class="d-block w-100" alt="Design unibody. Alumínio forjado a quente. Capacidade excepcional.">
                    <div class="carousel-caption d-none d-md-block text-center">
                        <h5 class="fs-4 fw-bold">Design unibody. Alumínio forjado a quente.</h5>
                        <p>Capacidade excepcional.</p>
                    </div>
                </div>
                
                <div class="carousel-item">
                    <img src="caminho/para/outra/imagem.jpg" class="d-block w-100" alt="Descrição da Imagem">
                </div>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
        
        <section class="auth-container">
            <div class="auth-card">
                <div class="text-center mb-3 auth-logo">
                    <img src="../images/LogoBlack.svg" alt="TriviaBox logo" style="height:64px; max-width:100%; pointer-events: none;" />
                </div>
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