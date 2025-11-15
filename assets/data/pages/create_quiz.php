<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$nome_do_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TriviaBox - Criar Quiz</title>
    <link id="favicon" rel="shortcut icon" href="./../images/LogoBlack.svg" type="image/x-icon">
    <link rel="stylesheet" href="./../../styles/bootstrap_styles/bootstrap.css">
    <link rel="stylesheet" href="./../../styles/create-quiz.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>
    <?php include_once './elements/header.php'; ?>

    <main class="container create-quiz-container my-4">
        <div class="row" id="createQuizLayoutRow">
            <div class="col-12 col-lg-8 mx-auto form-column">
                <div class="card form-card shadow-sm">
                    <div class="card-body">
                        <form id="createQuizForm" action="create_quiz.php" method="POST" enctype="multipart/form-data">
                            <div class="row g-3">
                        <div class="col-12">
                            <label for="quiz_title" class="form-label">Título do Quiz*</label>
                            <input type="text" id="quiz_title" name="quiz_title" class="form-control form-control-lg" placeholder="Ex: Curiosidades do Mundo" required>
                        </div>

                        <div class="col-12">
                            <label for="quiz_cover" class="form-label">Imagem principal*</label>
                            <input type="file" id="quiz_cover" name="quiz_cover" accept="image/*" class="form-control form-control-sm">
                            <div class="form-text">Esta imagem será usada como capa do quiz.</div>
                        </div>

                        <div class="col-12">
                            <label for="quiz_description" class="form-label">Descrição*</label>
                            <textarea id="quiz_description" name="quiz_description" class="form-control" rows="3" placeholder="Escreva uma descrição curta e atrativa..."></textarea>
                        </div>

                        <div class="col-12">
                            <label for="quiz_category_search" class="form-label">Categoria</label>
                            <div class="position-relative">
                                <input type="text" id="quiz_category_search" class="form-control" placeholder="Comece a digitar para buscar categorias..." autocomplete="off">
                                <input type="hidden" id="quiz_category" name="categoria_id" value="">
                                <div id="quizCategorySuggestions" class="category-suggestions d-none" role="listbox"></div>
                            </div>
                            <div class="form-text">Digite para buscar a categoria (busca incremental).</div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0">Perguntas*</h5>
                                <button type="button" id="addQuestionBtn" class="btn btn-outline-primary btn-sm"><i class="fas fa-plus me-1"></i> Adicionar pergunta</button>
                            </div>

                            <div id="questionsContainer" class="questions-container">
                                <!-- Perguntas adicionadas via JS aparecerão aqui -->
                            </div>
                        </div>

                        <div class="col-12 d-flex gap-2 justify-content-end">
                            <button type="button" id="saveDraftBtn" class="btn btn-secondary">Salvar rascunho</button>
                            <button type="submit" class="btn btn-primary">Publicar Quiz</button>
                        </div>
                    </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- preview removed -->
        </div>
    </main>
    <?php include_once './elements/footer.php'; ?>

    <script src="./../../scripts/bootstrap_scripts/bootstrap.bundle.js"></script>
    <script src="./../../scripts/matchMedia.js"></script>
    <script>
        (function() {
            const addQuestionBtn = document.getElementById('addQuestionBtn');
            const questionsContainer = document.getElementById('questionsContainer');
            let qIndex = 0;

            function createQuestionCard(index) {
                const wrapper = document.createElement('div');
                wrapper.className = 'question-card card mb-3';
                wrapper.innerHTML = `
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-0">Pergunta ${index + 1}</h6>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary move-up">&#8593;</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary move-down">&#8595;</button>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-question">Remover</button>
                            </div>
                        </div>
                        <div class="mb-2">
                            <input type="text" name="question_${index}" class="form-control" placeholder="Digite a pergunta...">
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Imagem (opcional)</label>
                            <input type="file" name="q${index}_image" accept="image/*" class="form-control form-control-sm">
                        </div>
                        <div class="row g-2">
                            <div class="col-12 col-md-6"><input type="text" name="q${index}_opt1" class="form-control" placeholder="Opção 1"></div>
                            <div class="col-12 col-md-6"><input type="text" name="q${index}_opt2" class="form-control" placeholder="Opção 2"></div>
                            <div class="col-12 col-md-6"><input type="text" name="q${index}_opt3" class="form-control" placeholder="Opção 3"></div>
                            <div class="col-12 col-md-6"><input type="text" name="q${index}_opt4" class="form-control" placeholder="Opção 4"></div>
                        </div>
                        <div class="form-text mt-2">Selecione a opção correta:</div>
                        <div class="d-flex gap-2 mt-1">
                            <label class="btn btn-outline-secondary btn-sm"><input type="radio" name="q${index}_correct" value="1"> 1</label>
                            <label class="btn btn-outline-secondary btn-sm"><input type="radio" name="q${index}_correct" value="2"> 2</label>
                            <label class="btn btn-outline-secondary btn-sm"><input type="radio" name="q${index}_correct" value="3"> 3</label>
                            <label class="btn btn-outline-secondary btn-sm"><input type="radio" name="q${index}_correct" value="4"> 4</label>
                        </div>
                    </div>
                `;

                // attach handlers
                wrapper.querySelector('.remove-question').addEventListener('click', () => wrapper.remove());
                wrapper.querySelector('.move-up').addEventListener('click', () => {
                    const prev = wrapper.previousElementSibling;
                    if (prev) questionsContainer.insertBefore(wrapper, prev);
                });
                wrapper.querySelector('.move-down').addEventListener('click', () => {
                    const next = wrapper.nextElementSibling;
                    if (next) questionsContainer.insertBefore(next, wrapper);
                });

                return wrapper;
            }

            addQuestionBtn.addEventListener('click', () => {
                const card = createQuestionCard(qIndex++);
                questionsContainer.appendChild(card);
                card.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            });
        })();
    </script>
    <script src="../../scripts/quizCRUD.js"></script>
</body>

</html>