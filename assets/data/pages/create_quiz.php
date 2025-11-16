<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

 $nome_do_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
// current user id for permission checks
$usuario_id = isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : 0;
// Edit mode: load quiz and its perguntas when id provided
$editQuiz = null;
$editQuestions = [];
$editQuizId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($editQuizId > 0) {
    try {
        require_once __DIR__ . '/../../../conexao.php';
        if (isset($conexao) && $conexao instanceof mysqli) {
            $qstmt = $conexao->prepare('SELECT id, titulo, descricao, capa, categorias_id, criador_id FROM quizzes WHERE id = ? LIMIT 1');
            $qstmt->bind_param('i', $editQuizId);
            $qstmt->execute();
            $qres = $qstmt->get_result();
            $editQuiz = $qres->fetch_assoc();
            $qstmt->close();
            if ($editQuiz) {
                // attempt to resolve category name for display
                if (!empty($editQuiz['categorias_id'])) {
                    $cstmt = $conexao->prepare('SELECT id, nome FROM categorias WHERE id = ? LIMIT 1');
                    if ($cstmt) {
                        $cid = (int)$editQuiz['categorias_id'];
                        $cstmt->bind_param('i', $cid);
                        $cstmt->execute();
                        $cres = $cstmt->get_result();
                        $cRow = $cres->fetch_assoc();
                        if ($cRow) $editQuiz['categoria_nome'] = $cRow['nome'];
                        $cstmt->close();
                    }
                }
                $pstmt = $conexao->prepare('SELECT id, posicao, texto, imagem, op1, op2, op3, op4, correta FROM perguntas WHERE quiz_id = ? ORDER BY posicao ASC');
                $pstmt->bind_param('i', $editQuizId);
                $pstmt->execute();
                $pres = $pstmt->get_result();
                while ($prow = $pres->fetch_assoc()) {
                    $editQuestions[] = $prow;
                }
                $pstmt->close();
            }
        }
    } catch (Throwable $e) {
        // ignore errors here; page will simply be in create mode
        $editQuiz = null;
        $editQuestions = [];
    }
}
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
                            <?php if (!empty($editQuiz) && isset($editQuiz['criador_id']) && (int)$editQuiz['criador_id'] === $usuario_id): ?>
                                <input type="hidden" name="quiz_id" value="<?= (int)$editQuizId ?>">
                            <?php endif; ?>
                            <div class="row g-3">
                        <div class="col-12">
                            <label for="quiz_title" class="form-label">Título do Quiz*</label>
                            <input type="text" id="quiz_title" name="quiz_title" class="form-control form-control-lg" placeholder="Ex: Curiosidades do Mundo" required value="<?= isset($editQuiz['titulo']) ? htmlspecialchars($editQuiz['titulo']) : '' ?>">
                        </div>

                        <div class="col-12">
                            <label for="quiz_cover" class="form-label">Imagem principal*</label>
                            <input type="file" id="quiz_cover" name="quiz_cover" accept="image/*" class="form-control form-control-sm">
                            <div class="form-text">Esta imagem será usada como capa do quiz.</div>
                            <?php if (!empty($editQuiz['capa'])): ?>
                                <div class="small mt-1 text-muted">Capa atual: <strong><?= htmlspecialchars(basename($editQuiz['capa'])) ?></strong></div>
                                <input type="hidden" name="existing_cover" value="<?= htmlspecialchars(basename($editQuiz['capa'])) ?>">
                                <div id="coverPreview" class="mt-2">
                                    <img id="coverPreviewImg" src="<?= '../images/uploads/' . htmlspecialchars(basename($editQuiz['capa'])) ?>" alt="Capa atual" class="img-thumbnail" style="max-width:180px;">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label for="quiz_description" class="form-label">Descrição*</label>
                            <textarea id="quiz_description" name="quiz_description" class="form-control" rows="3" placeholder="Escreva uma descrição curta e atrativa..."><?= isset($editQuiz['descricao']) ? htmlspecialchars($editQuiz['descricao']) : '' ?></textarea>
                        </div>

                        <div class="col-12">
                            <label for="quiz_category_search" class="form-label">Categoria</label>
                            <div class="position-relative">
                                <input type="text" id="quiz_category_search" class="form-control" placeholder="Comece a digitar para buscar categorias..." autocomplete="off" value="<?= isset($editQuiz['categoria_nome']) ? htmlspecialchars($editQuiz['categoria_nome']) : '' ?>">
                                <input type="hidden" id="quiz_category" name="categoria_id" value="<?= isset($editQuiz['categorias_id']) ? (int)$editQuiz['categorias_id'] : '' ?>">
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
                                <?php if (!empty($editQuestions) && isset($editQuiz['criador_id']) && (int)$editQuiz['criador_id'] === $usuario_id): ?>
                                    <?php foreach ($editQuestions as $i => $pq): ?>
                                        <div class="question-card card mb-3">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="mb-0">Pergunta <?= ($i + 1) ?></h6>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary move-up">&#8593;</button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary move-down">&#8595;</button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-question">Remover</button>
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <input type="text" name="question_<?= $i ?>" class="form-control" placeholder="Digite a pergunta..." value="<?= htmlspecialchars($pq['texto']) ?>">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label small">Imagem (opcional)</label>
                                                    <input type="file" name="q<?= $i ?>_image" accept="image/*" class="form-control form-control-sm q-image-input">
                                                    <?php if (!empty($pq['imagem'])): ?>
                                                        <div class="small mt-1 text-muted">Imagem atual: <strong><?= htmlspecialchars(basename($pq['imagem'])) ?></strong></div>
                                                        <input type="hidden" name="existing_qimage_<?= $i ?>" value="<?= htmlspecialchars(basename($pq['imagem'])) ?>">
                                                    <?php endif; ?>
                                                    <div class="qimage-preview mt-2">
                                                        <?php if (!empty($pq['imagem'])): ?>
                                                            <img src="<?= '../images/uploads/' . htmlspecialchars(basename($pq['imagem'])) ?>" alt="Imagem pergunta" class="img-thumbnail" style="max-width:140px;">
                                                        <?php else: ?>
                                                            <img src="#" alt="" class="img-thumbnail d-none" style="max-width:140px;">
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-12 col-md-6"><input type="text" name="q<?= $i ?>_opt1" class="form-control" placeholder="Opção 1" value="<?= htmlspecialchars($pq['op1']) ?>"></div>
                                                    <div class="col-12 col-md-6"><input type="text" name="q<?= $i ?>_opt2" class="form-control" placeholder="Opção 2" value="<?= htmlspecialchars($pq['op2']) ?>"></div>
                                                    <div class="col-12 col-md-6"><input type="text" name="q<?= $i ?>_opt3" class="form-control" placeholder="Opção 3" value="<?= htmlspecialchars($pq['op3']) ?>"></div>
                                                    <div class="col-12 col-md-6"><input type="text" name="q<?= $i ?>_opt4" class="form-control" placeholder="Opção 4" value="<?= htmlspecialchars($pq['op4']) ?>"></div>
                                                </div>
                                                <div class="form-text mt-2">Selecione a opção correta:</div>
                                                <div class="d-flex gap-2 mt-1">
                                                    <label class="btn btn-outline-secondary btn-sm"><input type="radio" name="q<?= $i ?>_correct" value="1" <?= ((int)$pq['correta'] === 1) ? 'checked' : '' ?>> 1</label>
                                                    <label class="btn btn-outline-secondary btn-sm"><input type="radio" name="q<?= $i ?>_correct" value="2" <?= ((int)$pq['correta'] === 2) ? 'checked' : '' ?>> 2</label>
                                                    <label class="btn btn-outline-secondary btn-sm"><input type="radio" name="q<?= $i ?>_correct" value="3" <?= ((int)$pq['correta'] === 3) ? 'checked' : '' ?>> 3</label>
                                                    <label class="btn btn-outline-secondary btn-sm"><input type="radio" name="q<?= $i ?>_correct" value="4" <?= ((int)$pq['correta'] === 4) ? 'checked' : '' ?>> 4</label>
                                                </div>
                                                <input type="hidden" name="existing_question_id_<?= $i ?>" value="<?= (int)$pq['id'] ?>">
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-12 d-flex gap-2 justify-content-end">
                            <?php if (!empty($editQuiz) && isset($editQuiz['criador_id']) && (int)$editQuiz['criador_id'] === $usuario_id): ?>
                                <button type="button" id="deleteQuizBtn" class="btn btn-danger">Deletar Quiz</button>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary">Publicar Quiz</button>
                        </div>
                    </div>
                            </div>
                        </form>
                        <!-- Edit warning modal: only shown when editing a quiz -->
                        <?php if (!empty($editQuiz) && isset($editQuiz['criador_id']) && (int)$editQuiz['criador_id'] === $usuario_id): ?>
                        <div class="modal fade" id="editWarningModal" tabindex="-1" aria-labelledby="editWarningModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editWarningModalLabel">Aviso importante</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Ao editar o <strong>texto da pergunta</strong>, o <strong>texto de uma resposta</strong> ou alterar qual é a <strong>resposta correta</strong>, o placar deste quiz será <strong>zerado</strong>.</p>
                                        <p>Alterar apenas imagens (capa ou imagens das perguntas) <strong>não</strong> afetará o placar.</p>
                                        <p>Deseja continuar com a atualização?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="button" id="editWarnContinue" class="btn btn-primary">Continuar e salvar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
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
            let qIndex = <?= (!empty($editQuestions) ? count($editQuestions) : 0) ?>;

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
                            <input type="file" name="q${index}_image" accept="image/*" class="form-control form-control-sm q-image-input">
                            <div class="qimage-preview mt-2"><img src="#" alt="" class="img-thumbnail d-none" style="max-width:140px;"></div>
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

                // attach preview handler for the new card's file input
                const fileInputNew = wrapper.querySelector('.q-image-input');
                const previewImgNew = wrapper.querySelector('.qimage-preview img');
                if (fileInputNew && previewImgNew) {
                    fileInputNew.addEventListener('change', (ev) => {
                        const f = ev.target.files && ev.target.files[0];
                        if (!f) {
                            previewImgNew.src = '#';
                            previewImgNew.classList.add('d-none');
                            return;
                        }
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImgNew.src = e.target.result;
                            previewImgNew.classList.remove('d-none');
                        };
                        reader.readAsDataURL(f);
                    });
                }

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

            // attach handlers to any server-rendered question cards (remove/move)
            (function attachExistingCardHandlers() {
                const existing = Array.from(document.querySelectorAll('.question-card'));
                existing.forEach(card => {
                    const removeBtn = card.querySelector('.remove-question');
                    const up = card.querySelector('.move-up');
                    const down = card.querySelector('.move-down');
                    if (removeBtn) removeBtn.addEventListener('click', () => card.remove());
                    if (up) up.addEventListener('click', () => {
                        const prev = card.previousElementSibling;
                        if (prev) questionsContainer.insertBefore(card, prev);
                    });
                    if (down) down.addEventListener('click', () => {
                        const next = card.nextElementSibling;
                        if (next) questionsContainer.insertBefore(next, card);
                    });
                    // attach preview handler for file input inside this card
                    const fileInput = card.querySelector('.q-image-input');
                    const previewImg = card.querySelector('.qimage-preview img');
                    if (fileInput && previewImg) {
                        fileInput.addEventListener('change', (ev) => {
                            const f = ev.target.files && ev.target.files[0];
                            if (!f) {
                                previewImg.src = '#';
                                previewImg.classList.add('d-none');
                                return;
                            }
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                previewImg.src = e.target.result;
                                previewImg.classList.remove('d-none');
                            };
                            reader.readAsDataURL(f);
                        });
                    }
                });
            })();
            // cover preview handler
            (function coverPreviewHandler() {
                const coverInput = document.getElementById('quiz_cover');
                const coverImg = document.getElementById('coverPreviewImg');
                if (!coverInput) return;
                coverInput.addEventListener('change', (ev) => {
                    const f = ev.target.files && ev.target.files[0];
                    if (!f) {
                        if (coverImg) {
                            coverImg.src = '#';
                            coverImg.classList.add('d-none');
                        }
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // ensure preview container exists
                        let imgEl = document.getElementById('coverPreviewImg');
                        if (!imgEl) {
                            const wrapper = document.createElement('div');
                            wrapper.id = 'coverPreview';
                            wrapper.className = 'mt-2';
                            imgEl = document.createElement('img');
                            imgEl.id = 'coverPreviewImg';
                            imgEl.className = 'img-thumbnail';
                            imgEl.style.maxWidth = '180px';
                            wrapper.appendChild(imgEl);
                            coverInput.parentNode.appendChild(wrapper);
                        }
                        imgEl.src = e.target.result;
                        imgEl.classList.remove('d-none');
                    };
                    reader.readAsDataURL(f);
                });
            })();
            // delete button handler (only present in edit mode)
            (function deleteHandler() {
                const delBtn = document.getElementById('deleteQuizBtn');
                if (!delBtn) return;
                delBtn.addEventListener('click', () => {
                    if (!confirm('Tem certeza que deseja excluir este quiz? Esta ação é irreversível.')) return;
                    // disable button
                    delBtn.disabled = true;
                    const fd = new FormData();
                    fd.append('quiz_id', '<?= (int)$editQuizId ?>');
                    fetch('./actions/deleteQuiz.php', { method: 'POST', body: fd })
                        .then(r => r.json())
                        .then(json => {
                            if (json && json.success) {
                                alert(json.message || 'Quiz excluído');
                                window.location.href = 'home.php';
                            } else {
                                alert((json && json.message) ? json.message : 'Falha ao excluir quiz');
                                delBtn.disabled = false;
                            }
                        })
                        .catch(err => {
                            console.error('Erro ao excluir quiz', err);
                            alert('Erro ao excluir quiz. Veja console para detalhes.');
                            delBtn.disabled = false;
                        });
                });
            })();
        })();
    </script>
    <?php if (!empty($editQuestions)): ?>
    <script>
        window.__ORIGINAL_QUESTIONS__ = <?php
            $norm = [];
            foreach ($editQuestions as $pq) {
                $norm[] = [
                    'question' => $pq['texto'] ?? '',
                    'options' => [ $pq['op1'] ?? '', $pq['op2'] ?? '', $pq['op3'] ?? '', $pq['op4'] ?? '' ],
                    'correct' => isset($pq['correta']) ? (int)$pq['correta'] : 0
                ];
            }
            echo json_encode($norm, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        ?>;
    </script>
    <?php else: ?>
    <script>window.__ORIGINAL_QUESTIONS__ = null;</script>
    <?php endif; ?>
    <script src="../../scripts/quizCRUD.js"></script>
</body>

</html>