    <header class="page-header">
        <div class="logo-container">
            <img src="./../images/LogoBlack.svg" alt="logo_black" class="logo">
            <h1>TriviaBox</h1>
        </div>
        <div class="auth-buttons d-flex align-items-center">
            <!-- Desktop / show full welcome dropdown -->
            <div class="dropdown d-none d-md-block">
                <a class="nav-link dropdown-toggle text-dark fw-bold p-0 me-3" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Bem-vindo, <?php echo htmlspecialchars($nome_do_usuario); ?>!
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="./home.php">Inicio</a></li>
                    <li><a class="dropdown-item" href="./profile.php">Perfil</a></li>
                    <li><a class="dropdown-item" href="./create_quiz.php">Criar Quiz</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-danger" href="./logout.php">Sair</a></li>
                </ul>
            </div>

            <!-- Mobile / hamburger toggler opens right offcanvas -->
            <div class="d-block d-md-none">
                <button class="btn btn-outline-secondary p-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#authOffcanvas" aria-controls="authOffcanvas">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Offcanvas for mobile auth menu (slides from right) -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="authOffcanvas" aria-labelledby="authOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="authOffcanvasLabel">Bem-vindo, <?php echo htmlspecialchars($nome_do_usuario); ?>!</h5>
            <button type="button" class="btn-close text-reset ms-auto" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
        </div>
        <div class="offcanvas-body">
            <div class="d-grid gap-2">
                <a class="btn btn-primary offcanvas-btn" href="./home.php">Início</a>
                <a class="btn btn-primary offcanvas-btn" href="./profile.php">Perfil</a>
                <a class="btn btn-primary offcanvas-btn" href="./create_quiz.php">Criar Quiz</a>
                <form action="./logout.php" method="post">
                    <button type="submit" class="btn btn-danger offcanvas-btn offcanvas-logout">Sair</button>
                </form>
            </div>
        </div>
    </div>