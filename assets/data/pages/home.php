<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriviaBox</title>
    <link id="favicon" rel="shortcut icon" href="./../images/LogoBlack.svg" type="image/x-icon">
    <link rel="stylesheet" href="./../../styles/bootstrap_styles/bootstrap.css">
    <link rel="stylesheet" href="./../../styles/home-page.css">
</head>

<body>
    <header class="page-header">
        <div class="logo-container">
            <img src="./../images/LogoBlack.svg" alt="logo_black" class="logo">
            <h1>TriviaBox</h1>
        </div>
        <div class="auth-buttons">
            <button type="button" class="btn btn-dark" onclick="location.href='./login.php'">Login</button>
            <button type="button" class="btn btn-primary" onclick="location.href='./signup.php'">Sign-up</button>
        </div>
    </header>
    <main>
        <div id="LandingCarousel" class="carousel slide w-100" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="caroussel-text z-1">
                    <h2>Desafie sua Mente com Quizes Épicos</h2>
                    <h5>Compita com amigos, suba nos placares de líderes e prove seu conhecimento em milhares de
                        tópicos. De curiosidades a quebra-cabeças, temos tudo!</h5>
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
        <section class="quiz-list-section container my-5">
            <div class="row mb-3">
                <div class="col-12 mb-2">
                    <input type="text" id="quizNameFilter" class="form-control" placeholder="Filtrar por nome">
                </div>
                <div class="col-12">
                    <button class="btn btn-outline-secondary w-100 d-flex justify-content-between align-items-center"
                        type="button" data-bs-toggle="collapse" data-bs-target="#categoryCollapse" aria-expanded="false"
                        aria-controls="categoryCollapse">
                        Categorias <span class="small">Selecionar</span>
                    </button>
                    <div id="categoryCollapse" class="collapse mt-2">
                        <div class="d-flex flex-wrap gap-2" id="categoryTags">
                            <label class="btn btn-sm btn-outline-primary mb-2">
                                <input type="checkbox" class="category-checkbox" value="História" autocomplete="off">
                                História
                            </label>
                            <label class="btn btn-sm btn-outline-primary mb-2">
                                <input type="checkbox" class="category-checkbox" value="Ciência" autocomplete="off">
                                Ciência
                            </label>
                            <label class="btn btn-sm btn-outline-primary mb-2">
                                <input type="checkbox" class="category-checkbox" value="Esportes" autocomplete="off">
                                Esportes
                            </label>
                            <label class="btn btn-sm btn-outline-primary mb-2">
                                <input type="checkbox" class="category-checkbox" value="Entretenimento"
                                    autocomplete="off"> Entretenimento
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div id="quizList" class="row">
                <!-- Lista de quizzes -->
                <div class="col-md-4 mb-3 quiz-item" data-name="História Mundial" data-category="História">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">História Mundial</h5>
                            <p class="card-text">Teste seus conhecimentos sobre eventos históricos.</p>
                            <a href="#" class="btn btn-primary">Fazer Quiz</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3 quiz-item" data-name="Física Básica" data-category="Ciência">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Física Básica</h5>
                            <p class="card-text">Desafie-se com perguntas sobre física.</p>
                            <a href="#" class="btn btn-primary">Fazer Quiz</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3 quiz-item" data-name="Futebol Mundial" data-category="Esportes">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Futebol Mundial</h5>
                            <p class="card-text">Quanto você sabe sobre futebol?</p>
                            <a href="#" class="btn btn-primary">Fazer Quiz</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
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
</body>
<script src="../../scripts/bootstrap_scripts/bootstrap.bundle.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="./../../scripts/matchMedia.js"></script>
<script src="./../../scripts/quizFilter.js"></script>

</html>