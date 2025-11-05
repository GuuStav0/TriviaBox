<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriviaBox</title>
    <link rel="stylesheet" href="./assets/styles/bootstrap_styles/bootstrap.css">
    <link rel="stylesheet" href="./assets/styles/landing-page.css">
    <link rel="shortcut icon" href="./assets/data/images/LogoWhite.svg" type="image/x-icon">
</head>

<body>
    <main>
        <header class="page-header">
            <div class="logo-container">
                <img src="./assets/data/images/LogoBlack.svg" alt="logo_black" class="logo">
                <h1>TriviaBox</h1>
            </div>
            <div class="auth-buttons">
                <button type="button" class="btn btn-dark">Login</button>
                <button type="button" class="btn btn-primary">Sign-up</button>
            </div>
        </header>
        <div id="LandingCarousel" class="carousel slide w-100" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="caroussel-text z-1">
                    <h2>Desafie sua Mente com Quizes Épicos</h2>
                    <h5>Compita com amigos, suba nos placares de líderes e prove seu conhecimento em milhares de tópicos. De curiosidades a quebra-cabeças, temos tudo!</h5>
                </div>
                <div class="carousel-item active z-0">
                    <img src="https://placehold.co/1920x1080" class="caroussel_img d-block" alt="...">
                </div>
                <div class="carousel-item z-0">
                    <img src="https://placehold.co/1920x1080" class="caroussel_img d-block" alt="...">
                </div>
                <div class="carousel-item z-0">
                    <img src="https://placehold.co/1920x1080" class="caroussel_img d-block" alt="...">
                </div>
            </div>
        </div>
        <section class="landing-content">
            <h2>Por que Escolher TriviaBox?</h2>
            <p>Descubra as razões pelas quais somos a melhor plataforma de quizzes!</p>
            <div class="description-cards">
                <div class="content-item">
                    <div style="width: 50px; height: 50px; background-color: #c4ffb5ff; border-radius: 25%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3>Variedade de Tópicos</h3>
                    <p>Explore uma vasta gama de categorias, desde história e ciência até cultura pop e esportes. Sempre há algo novo para aprender!</p>
                </div>
                <div class="content-item">
                    <div style="width: 50px; height: 50px; background-color: #ffe7b3ff; border-radius: 25%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3>Competições em Tempo Real</h3>
                    <p>Participe de quizzes ao vivo contra jogadores de todo o mundo. Teste suas habilidades sob pressão e veja onde você se destaca!</p>
                </div>
                <div class="content-item">
                    <div style="width: 50px; height: 50px; background-color: #b3d9ffff; border-radius: 25%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-pencil-alt"></i>
                    </div>
                    <h3>Crie Seus Próprios Quizzes</h3>
                    <p>Tem uma paixão por um tópico específico? Crie seus próprios quizzes e desafie a comunidade TriviaBox!</p>
                </div>
            </div>
        </section>
        <section class="landing-explain" aria-labelledby="how-it-works-title">
            <div class="explain-container">
                <h2 id="how-it-works-title">Como Jogar?</h2>
                <p class="lead">Comece a jogar em três etapas simples</p>

                <div class="explain-steps" role="list">
                    <div class="step" role="listitem">
                        <div class="step-badge">01</div>
                        <h4 class="step-title">Cadastre-se Gratuitamente</h4>
                        <p class="step-desc">Crie sua conta em segundos e escolha seus tópicos favoritos.</p>
                    </div>

                    <div class="step" role="listitem">
                        <div class="step-badge">02</div>
                        <h4 class="step-title">Escolha um Quiz</h4>
                        <p class="step-desc">Navegue pelas categorias ou participe de uma partida multiplayer ao vivo instantaneamente.</p>
                    </div>

                    <div class="step" role="listitem">
                        <div class="step-badge">03</div>
                        <h4 class="step-title">Jogue e Ganhe</h4>
                        <p class="step-desc">Responda perguntas, ganhe pontos e suba nas classificações!</p>
                    </div>
                </div>
            </div>
        </section>
        <section class="start-playing" role="region" aria-labelledby="start-playing-title">
            <div class="start-container">
                <h2 id="start-playing-title">Pronto para Testar Seu Conhecimento?</h2>
                <p class="start-lead">Junte-se a comunidade de jogadores que já estão desfrutando da melhor experiência de quiz online.</p>
                <div class="start-cta-wrap">
                    <a class="btn start-cta" href="#">Comece a Jogar Gratuitamente</a>
                </div>
            </div>
        </section>
        <footer class="bg-dark text-center text-white">
            <div class="container p-4 pb-0">
                <section class="mb-4">
                    <!-- Facebook -->
                    <a class="btn btn-outline-light btn-floating m-1" href="#!" role="button">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <!-- Twitter -->
                    <a class="btn btn-outline-light btn-floating m-1" href="#!" role="button">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <!-- Google -->
                    <a class="btn btn-outline-light btn-floating m-1" href="#!" role="button">
                        <i class="fab fa-google"></i>
                    </a>
                    <!-- Instagram -->
                    <a class="btn btn-outline-light btn-floating m-1" href="#!" role="button">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <!-- Linkedin -->
                    <a class="btn btn-outline-light btn-floating m-1" href="#!" role="button">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <!-- Github -->
                    <a class="btn btn-outline-light btn-floating m-1" href="#!" role="button">
                        <i class="fab fa-github"></i>
                    </a>
                </section>
            </div>
            <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
                &#169; 2025 Copyright:
                <a class="text-white" href="">TriviaBox</a>
            </div>
        </footer>
    </main>
</body>
<script src="./assets/scripts/bootstrap_scripts/bootstrap.bundle.js"></script>
<script src="https://kit.fontawesome.com/02f69001e4.js" crossorigin="anonymous"></script>

</html>