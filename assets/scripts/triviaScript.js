try {
    (function() {
    const QUESTIONS = window.__TRIVIA_QUESTIONS__ || [];
    const totalEl = document.getElementById('totalQuestions');
    const currentEl = document.getElementById('currentIndex');
    const questionTextEl = document.getElementById('questionText');
    const imageEl = document.getElementById('questionImage');
    const imagePlaceholder = document.getElementById('imagePlaceholder');
    const answersListEl = document.getElementById('answersList');
    const resultPanel = document.getElementById('resultPanel');
    const scoreText = document.getElementById('scoreText');
    const progressDotsEl = document.getElementById('progressDots');
    const quizCounterText = document.getElementById('quizCounterText');
    const quizPlayEl = document.getElementById('quizPlay');
    const startBtn = document.getElementById('startQuizBtn');
    const QUIZ_ID = window.__TRIVIA_ID__ || null;
    const flashContainer = document.getElementById('flashContainer');

    function showNotification(message, type = 'success', timeout = 4000) {
        if (!flashContainer) {
            // fallback to alert for debugging if container missing
            try { alert(message); } catch (e) { console.log(message); }
            return;
        }
        const wrapper = document.createElement('div');
        wrapper.className = `alert alert-${type} alert-dismissible fade show`;
        wrapper.setAttribute('role', 'alert');
        wrapper.innerHTML = `${message}`;

        // add dismiss button
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn-close';
        btn.setAttribute('data-bs-dismiss', 'alert');
        btn.setAttribute('aria-label', 'Close');
        wrapper.appendChild(btn);

        // insert and auto-remove
        flashContainer.appendChild(wrapper);
        if (timeout > 0) {
            setTimeout(() => {
                try { wrapper.classList.remove('show'); } catch (e) {}
                try { flashContainer.removeChild(wrapper); } catch (e) {}
            }, timeout);
        }
    }

    let currentIdx = 0;
    let score = 0;
    let started = false;
    let scoreSubmitted = false; // prevent duplicate submissions

    function resolveImagePath(img) {
        if (!img) return '../images/placeholder-cover.png';
        if (img === 'placeholder-cover.png') return '../images/placeholder-cover.png';
        if (/^https?:\/\//.test(img)) return img;
        if (img.indexOf('..') === 0) return img;
        if (img.indexOf('images/') === 0) return '../' + img;
        return '../images/uploads/' + img;
    }

    function renderNoQuestions() {
        questionTextEl.textContent = 'Nenhuma pergunta disponível.';
        answersListEl.innerHTML = '';
        imageEl.src = '../images/placeholder-cover.png';
        if (startBtn) startBtn.style.display = 'none';
        if (progressDotsEl) progressDotsEl.classList.add('d-none');
        if (quizCounterText) quizCounterText.classList.add('d-none');
    }

    function showQuestion(i) {
        if (!QUESTIONS || !QUESTIONS.length) {
            renderNoQuestions();
            return;
        }
        const q = QUESTIONS[i];
        currentIdx = i;
        totalEl.textContent = QUESTIONS.length;
        currentEl.textContent = i + 1;
        // update counter text overlay
        if (quizCounterText) quizCounterText.textContent = `Pergunta ${i + 1} de ${QUESTIONS.length}`;

        // ensure progress dots exist
        if (progressDotsEl && progressDotsEl.children.length !== QUESTIONS.length) {
            // create dots
            progressDotsEl.innerHTML = '';
            for (let d = 0; d < QUESTIONS.length; d++) {
                const dot = document.createElement('div');
                dot.className = 'progress-dot';
                dot.dataset.index = d;
                progressDotsEl.appendChild(dot);
            }
        }
        // mark active dot
        if (progressDotsEl) {
            Array.from(progressDotsEl.children).forEach((el, idx) => {
                el.classList.toggle('active', idx === i);
            });
        }
        const qText = q.texto || q.question || ('Pergunta ' + (i + 1));
        questionTextEl.textContent = qText;

        // decide if we have an image to show
        const imgField = (q.imagem || q.image || '').toString().trim();
        const hasImage = imgField && imgField !== 'placeholder-cover.png';
        if (hasImage) {
            // show actual image and lower question text
            imageEl.style.display = '';
            imagePlaceholder.classList.add('d-none');
            questionTextEl.style.display = '';
            imageEl.src = resolveImagePath(imgField);
        } else {
            // no image: hide img tag and show question text inside the image box
            imageEl.style.display = 'none';
            imagePlaceholder.textContent = qText;
            imagePlaceholder.classList.remove('d-none');
            // hide the duplicated question text below the image box
            questionTextEl.style.display = 'none';
        }

        // build answers
        const opts = [q.op1, q.op2, q.op3, q.op4];
        answersListEl.innerHTML = '';
        opts.forEach((opt, index) => {
            const btn = document.createElement('button');
            btn.className = 'btn btn-outline-primary text-start';
            btn.style.padding = '12px';
            btn.dataset.index = index + 1;
            btn.innerHTML = `<span class="fw-semibold me-2">${index + 1})</span> ${opt || '—'}`;
            btn.addEventListener('click', onAnswerClick);
            answersListEl.appendChild(btn);
        });
    }

    function onAnswerClick(e) {
        const btn = e.currentTarget;
        // prevent double clicks
        if (btn.disabled) return;
        // disable all
        const buttons = Array.from(answersListEl.querySelectorAll('button'));
        buttons.forEach(b => b.disabled = true);

        const selected = parseInt(btn.dataset.index, 10);
        const q = QUESTIONS[currentIdx];
        const correct = parseInt(q.correta || q.correct || 0, 10);
        if (selected === correct) {
            score++;
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');
            // mark progress dot as correct
            if (progressDotsEl && progressDotsEl.children[currentIdx]) {
                progressDotsEl.children[currentIdx].classList.add('correct');
            }
        } else {
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-danger');
            // highlight correct
            const correctBtn = buttons.find(b => parseInt(b.dataset.index, 10) === correct);
            if (correctBtn) {
                correctBtn.classList.remove('btn-outline-primary');
                correctBtn.classList.add('btn-success');
            }
            // mark progress dot as wrong
            if (progressDotsEl && progressDotsEl.children[currentIdx]) {
                progressDotsEl.children[currentIdx].classList.add('wrong');
            }
        }

        // move to next after short delay
        setTimeout(() => {
            const next = currentIdx + 1;
            if (next < QUESTIONS.length) {
                showQuestion(next);
            } else {
                // finished
                answersListEl.innerHTML = '';
                // show cover in the image area
                const cover = (quizPlayEl && quizPlayEl.dataset && quizPlayEl.dataset.cover) ? quizPlayEl.dataset.cover : (QUESTIONS && QUESTIONS.length ? (QUESTIONS[0].imagem || 'placeholder-cover.png') : 'placeholder-cover.png');
                imagePlaceholder.classList.add('d-none');
                imageEl.style.display = '';
                imageEl.src = resolveImagePath(cover);

                // show total correct in the question area
                questionTextEl.style.display = '';
                questionTextEl.textContent = `Você acertou ${score} de ${QUESTIONS.length} perguntas.`;

                // keep result panel for additional info
                resultPanel.style.display = 'block';
                scoreText.textContent = `Você acertou ${score} de ${QUESTIONS.length} perguntas.`;

                // submit score to server (once) - prevent duplicates
                if (!scoreSubmitted) {
                    scoreSubmitted = true;
                    try {
                        const fd = new FormData();
                        fd.append('quiz_id', QUIZ_ID);
                        fd.append('pontuacao', score);
                        // send and inspect raw response for debugging
                        fetch('./actions/submitScore.php', { method: 'POST', body: fd })
                            .then(r => {
                                console.debug('[submitScore] response status', r.status, 'content-type', r.headers.get('content-type'));
                                return r.text().then(txt => ({ ok: r.ok, status: r.status, text: txt }));
                            })
                            .then(resp => {
                                // try parse JSON
                                let data = null;
                                try { data = JSON.parse(resp.text); } catch (e) {
                                    console.warn('[submitScore] response not JSON', resp.text);
                                    // show raw server body
                                    showNotification('Erro ao salvar pontuação. Resposta do servidor: ' + (resp.text || `HTTP ${resp.status}`), 'danger', 10000);
                                    return;
                                }

                                if (data && data.success) {
                                    const action = data.action || 'inserted';
                                    const best = typeof data.best !== 'undefined' ? data.best : null;
                                    if (action === 'updated') {
                                        showNotification('Pontuação atualizada com sucesso.', 'success', 4000);
                                    } else if (action === 'inserted') {
                                        showNotification('Pontuação salva com sucesso.', 'success', 4000);
                                    } else if (action === 'kept') {
                                        showNotification('Pontuação não superou sua melhor marca: ' + (best !== null ? best : '') , 'info', 5000);
                                    } else if (action === 'ignored') {
                                        showNotification('Pontuação zero não registrada.', 'info', 4000);
                                    }

                                    // update client-side indicators when inserted/updated
                                    if (action === 'inserted' || action === 'updated') {
                                        // update data attribute
                                        try {
                                            if (quizPlayEl) quizPlayEl.dataset.userScore = best;
                                            // update question text area to show user's best
                                            if (questionTextEl) questionTextEl.style.display = '', questionTextEl.textContent = `Sua pontuação: ${best} de ${QUESTIONS.length} perguntas.`;
                                            // change start button text to Refazer Quiz
                                            if (startBtn) startBtn.textContent = 'Refazer Quiz';
                                            // ensure badge exists
                                            let badge = document.getElementById('userCompletedBadge');
                                            if (!badge && quizPlayEl) {
                                                const imgBox = quizPlayEl.querySelector('.quiz-image');
                                                if (imgBox) {
                                                    badge = document.createElement('div');
                                                    badge.id = 'userCompletedBadge';
                                                    badge.className = 'position-absolute';
                                                    badge.style.top = '12px';
                                                    badge.style.left = '12px';
                                                    badge.innerHTML = '<span class="badge bg-success">Concluído</span>';
                                                    imgBox.appendChild(badge);
                                                }
                                            }
                                        } catch (err) { console.warn('Erro ao atualizar DOM após salvar pontuação', err); }
                                    }
                                } else {
                                    const msg = (data && data.message) ? data.message : 'Falha ao salvar pontuação.';
                                    const debug = data && data.debug ? '\n' + data.debug : '';
                                    showNotification(msg + debug, 'danger', 10000);
                                    console.warn('Pontuação não salva', data);
                                }
                            })
                            .catch(err => {
                                showNotification('Erro ao salvar pontuação. Ver console para detalhes.', 'danger', 10000);
                                console.error('Erro ao salvar pontuação (fetch)', err);
                            });
                    } catch (err) {
                        console.error('Erro ao preparar envio de pontuação', err);
                    }
                } else {
                    // already submitted -- optional: show a tiny notice
                    showNotification('Pontuação já enviada.', 'info', 2500);
                }
            }
        }, 700);
    }

    // initial state: show cover and Start button if there are questions
    function initCoverState() {
        // read cover from data attribute
        const cover = (quizPlayEl && quizPlayEl.dataset && quizPlayEl.dataset.cover) ? quizPlayEl.dataset.cover : '';
        imageEl.src = resolveImagePath(cover || (QUESTIONS && QUESTIONS.length ? (QUESTIONS[0].imagem || 'placeholder-cover.png') : 'placeholder-cover.png'));
        // detect user score from data attribute (if any)
        const rawUserScore = (quizPlayEl && quizPlayEl.dataset && typeof quizPlayEl.dataset.userScore !== 'undefined') ? quizPlayEl.dataset.userScore : '';
        const USER_SCORE = (rawUserScore === '' || rawUserScore === null) ? null : (isNaN(parseInt(rawUserScore, 10)) ? null : parseInt(rawUserScore, 10));

        // hide answers until start; show user's previous score if present
        answersListEl.innerHTML = '';
        if (USER_SCORE !== null) {
            // show user's score below the cover
            questionTextEl.style.display = '';
            questionTextEl.textContent = `Sua pontuação: ${USER_SCORE} de ${QUESTIONS.length} perguntas.`;
            // change button text to allow retake
            if (startBtn) startBtn.textContent = 'Refazer Quiz';
        } else {
            questionTextEl.style.display = 'none';
        }
        if (startBtn) {
            startBtn.style.display = '';
            startBtn.addEventListener('click', function() {
                // start quiz
                started = true;
                // show progress and counter
                if (progressDotsEl) progressDotsEl.classList.remove('d-none');
                if (quizCounterText) quizCounterText.classList.remove('d-none');
                // hide start button
                startBtn.style.display = 'none';
                // show first question
                if (QUESTIONS && QUESTIONS.length) {
                    showQuestion(0);
                } else {
                    renderNoQuestions();
                }
            });
        }
    }

    if (QUESTIONS && QUESTIONS.length) {
        initCoverState();
    } else {
        renderNoQuestions();
    }
})();
} catch (e) {
    console.error('triviaScript error', e);
    try { if (window && window.alert) window.alert('Erro no script do quiz: ' + (e && e.message)); } catch (ignored) {}
}
