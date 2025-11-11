<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriviaBox</title>
    <link rel="stylesheet" href="./../../styles/bootstrap_styles/bootstrap.css">
    <link rel="stylesheet" href="./../../styles/home-page.css">
    <link id="favicon" rel="shortcut icon" href="./../images/LogoWhite.svg" type="image/x-icon">
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
        <!-- Featured quizzes scroller (cards menores) -->
        <section class="featured-quizzes container my-3">
            <h3 class="featured-header mb-3">Destaques</h3>
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-secondary me-2 featured-prev" type="button" aria-label="Anterior">&lsaquo;</button>
                <div class="featured-viewport flex-grow-1">
                    <div class="featured-track d-flex gap-3 py-2">
                        <!-- featured cards -->
                        <div class="card featured-card">
                            <img src="https://placehold.co/320x180" class="card-img-top" alt="Destaque 1">
                            <div class="card-body p-2">
                                <h6 class="card-title mb-1">Quiz em Destaque 1</h6>
                                <small class="text-muted">História</small>
                            </div>
                        </div>
                        <div class="card featured-card">
                            <img src="https://placehold.co/320x180" class="card-img-top" alt="Destaque 2">
                            <div class="card-body p-2">
                                <h6 class="card-title mb-1">Quiz em Destaque 2</h6>
                                <small class="text-muted">Ciência</small>
                            </div>
                        </div>
                        <div class="card featured-card">
                            <img src="https://placehold.co/320x180" class="card-img-top" alt="Destaque 3">
                            <div class="card-body p-2">
                                <h6 class="card-title mb-1">Quiz em Destaque 3</h6>
                                <small class="text-muted">Esportes</small>
                            </div>
                        </div>
                        <div class="card featured-card">
                            <img src="https://placehold.co/320x180" class="card-img-top" alt="Destaque 4">
                            <div class="card-body p-2">
                                <h6 class="card-title mb-1">Quiz em Destaque 4</h6>
                                <small class="text-muted">Entretenimento</small>
                            </div>
                        </div>
                        <div class="card featured-card">
                            <img src="https://placehold.co/320x180" class="card-img-top" alt="Destaque 5">
                            <div class="card-body p-2">
                                <h6 class="card-title mb-1">Quiz em Destaque 5</h6>
                                <small class="text-muted">Curiosidades</small>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-outline-secondary ms-2 featured-next" type="button" aria-label="Próximo">&rsaquo;</button>
            </div>
            <hr>
        </section>
        <section class="quiz-list-section container my-5">
            <div class="filter-bar mb-3 p-3 rounded shadow-sm bg-white">
                <div class="row align-items-center gx-2">
                    <div class="col-12 col-md-6 mb-2 mb-md-0">
                        <div class="input-group visually-enhanced-search">
                            <span class="input-group-text bg-transparent border-0 pe-2"><i class="fas fa-search"></i></span>
                            <input type="text" id="quizNameFilter" class="form-control form-control-lg border-0" placeholder="Filtrar por nome">
                        </div>
                    </div>
                    <div class="col-12 col-md-6 text-md-end">
                        <button class="btn btn-outline-secondary filter-toggle shadow-sm" type="button" data-bs-toggle="modal" data-bs-target="#categoryModal">
                            <i class="fas fa-tags me-2"></i> Categorias <span class="small ms-2">Selecionar</span>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Category selection modal -->
            <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="categoryModalLabel">Categorias</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex flex-wrap gap-2" id="categoryTags">
                                <label class="chip">
                                    <input type="checkbox" class="category-checkbox" value="História" autocomplete="off">
                                    História
                                </label>
                                <label class="chip">
                                    <input type="checkbox" class="category-checkbox" value="Ciência" autocomplete="off">
                                    Ciência
                                </label>
                                <label class="chip">
                                    <input type="checkbox" class="category-checkbox" value="Esportes" autocomplete="off">
                                    Esportes
                                </label>
                                <label class="chip">
                                    <input type="checkbox" class="category-checkbox" value="Entretenimento" autocomplete="off"> Entretenimento
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="button" class="btn btn-primary" id="applyCategoriesBtn" data-bs-dismiss="modal">Aplicar</button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="quizList" class="row">
                <!-- Lista de quizzes -->
                <div class="col-md-4 mb-3 quiz-item" data-name="História Mundial" data-category="História">
                    <div class="card">
                        <img src="https://placehold.co/640x360" class="card-img-top" alt="História Mundial">
                        <div class="card-body">
                            <h5 class="card-title">História Mundial</h5>
                            <p class="card-text">Teste seus conhecimentos sobre eventos históricos.</p>
                            <a href="#" class="btn btn-primary">Fazer Quiz</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3 quiz-item" data-name="Física Básica" data-category="Ciência">
                    <div class="card">
                        <img src="https://placehold.co/640x360" class="card-img-top" alt="Física Básica">
                        <div class="card-body">
                            <h5 class="card-title">Física Básica</h5>
                            <p class="card-text">Desafie-se com perguntas sobre física.</p>
                            <a href="#" class="btn btn-primary">Fazer Quiz</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3 quiz-item" data-name="Futebol Mundial" data-category="Esportes">
                    <div class="card">
                        <img src="https://placehold.co/640x360" class="card-img-top" alt="Futebol Mundial">
                        <div class="card-body">
                            <h5 class="card-title">Futebol Mundial</h5>
                            <p class="card-text">Quanto você sabe sobre futebol?</p>
                            <a href="#" class="btn btn-primary">Fazer Quiz</a>
                        </div>
                    </div>
                </div>
                <div id="noResults" class="col-12 text-center mt-4" style="display:none;">
                    <div class="p-4 border rounded bg-light">
                        <h5>Nenhum quiz encontrado</h5>
                        <p class="mb-0">Nenhum quiz corresponde à sua busca ou categorias selecionadas. Tente alterar os filtros.</p>
                    </div>
                </div>
            </div>
            <!-- Pagination controls -->
            <nav aria-label="Quizzes pagination" class="mt-4">
                <ul class="pagination justify-content-center" id="quizPagination">
                    <!-- pages injected here -->
                </ul>
            </nav>
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
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="../../scripts/bootstrap_scripts/bootstrap.bundle.js"></script>
<script src="./../../scripts/matchMedia.js"></script>
<script src="./../../scripts/quizFilter.js"></script>
<script>
    (function() {
        const track = document.querySelector('.featured-track');
        const prevBtn = document.querySelector('.featured-prev');
        const nextBtn = document.querySelector('.featured-next');

        if (!track || !prevBtn || !nextBtn) return;

        track.addEventListener('click', (e) => {
            const card = e.target.closest('.featured-card');
            if (!card) return;
            const link = card.getAttribute('data-link') || '#';
            if (link && link !== '#') {
                window.location.href = link;
            } else {
                const name = card.querySelector('.card-title')?.textContent || card.getAttribute('data-name');
                console.log('Featured card clicked:', name);
            }
        });

        function getGapPx() {
            const styles = getComputedStyle(track);
            return parseFloat(styles.columnGap || styles.gap) || 12;
        }

        function animateNext() {
            if (track.dataset.animating === 'true') return;
            const first = track.querySelector('.featured-card');
            if (!first) return;
            const cardWidth = first.getBoundingClientRect().width;
            const gap = getGapPx();
            const shift = Math.round(cardWidth + gap);

            const clone = first.cloneNode(true);
            track.appendChild(clone);

            track.style.transition = 'transform 350ms ease';
            track.style.transform = `translateX(-${shift}px)`;
            track.dataset.animating = 'true';

            track.addEventListener('transitionend', function handler() {
                track.style.transition = 'none';
                track.style.transform = '';

                if (track.contains(first)) track.removeChild(first);

                track.getBoundingClientRect();
                setTimeout(() => {
                    track.style.transition = '';
                }, 0);
                track.dataset.animating = 'false';
            }, {
                once: true
            });
        }

        function animatePrev() {
            if (track.dataset.animating === 'true') return;
            const cards = track.querySelectorAll('.featured-card');
            if (!cards.length) return;
            const last = cards[cards.length - 1];
            const first = cards[0];
            const cardWidth = last.getBoundingClientRect().width;
            const gap = getGapPx();
            const shift = Math.round(cardWidth + gap);

            const clone = last.cloneNode(true);
            track.insertBefore(clone, first);

            track.style.transition = 'none';
            track.style.transform = `translateX(-${shift}px)`;
            track.getBoundingClientRect();

            track.style.transition = 'transform 350ms ease';
            track.style.transform = 'translateX(0)';
            track.dataset.animating = 'true';

            track.addEventListener('transitionend', function handler() {
                const currentCards = track.querySelectorAll('.featured-card');
                const originalLast = currentCards[currentCards.length - 1];
                if (originalLast && originalLast !== clone) {
                    track.removeChild(originalLast);
                }

                track.style.transition = 'none';
                track.style.transform = '';
                track.getBoundingClientRect();
                setTimeout(() => {
                    track.style.transition = '';
                }, 0);
                track.dataset.animating = 'false';
            }, {
                once: true
            });
        }

        nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            animateNext();
        });
        prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            animatePrev();
        });

        prevBtn.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                animatePrev();
            }
        });
        nextBtn.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                animateNext();
            }
        });
    })();
</script>

</html>