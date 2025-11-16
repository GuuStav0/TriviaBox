document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('createQuizForm');
  if (!form) return;

  // Alert container
  const alertContainer = document.createElement('div');
  alertContainer.id = 'formAlert';
  form.prepend(alertContainer);

  function showAlert(msg, type = 'danger') {
    alertContainer.innerHTML = `<div class="alert alert-${type}" role="alert">${msg}</div>`;
    alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
  function clearAlerts() { alertContainer.innerHTML = ''; }

  function collectQuestions() {
    const cards = Array.from(document.querySelectorAll('.question-card'));
    return cards.map((card, idx) => {
      const questionInput = card.querySelector('input[name^="question_"]');
      const questionText = questionInput ? questionInput.value.trim() : '';

      // Find option inputs inside this card (text inputs except the main question input)
      const textInputs = Array.from(card.querySelectorAll('input[type="text"]'));
      const options = textInputs.filter(i => i !== questionInput).map(i => i.value.trim());

      const correct = card.querySelector('input[type="radio"]:checked')?.value || '';
      const fileInput = card.querySelector('input[type="file"]');
      const hasImage = fileInput && fileInput.files && fileInput.files.length > 0;

      return {
        question: questionText,
        options,
        correct,
        hasImage,
        // fileInput element is kept for client-side append only; DO NOT JSON.stringify this field when sending questions_json
        _fileInputElement: fileInput || null
      };
    });
  }

  function validateForm() {
    const errors = [];
    const title = form.querySelector('#quiz_title')?.value.trim() || '';
    if (!title) errors.push('Título é obrigatório.');

    const desc = form.querySelector('#quiz_description')?.value.trim() || '';
    if (!desc) errors.push('Descrição é obrigatória.');

    const questions = collectQuestions();
    if (!questions.length) errors.push('Adicione ao menos uma pergunta.');

    // Category required (matches DB schema where quizzes.categorias_id is NOT NULL)
    const categoryVal = form.querySelector('#quiz_category')?.value || '';
    if (!categoryVal) errors.push('Selecione uma categoria.');

    questions.forEach((q, i) => {
      if (!q.question) errors.push(`Pergunta ${i + 1} está vazia.`);
      if (q.options.length < 4) errors.push(`Pergunta ${i + 1} precisa ter 4 opções.`);
      q.options.forEach((opt, oi) => {
        if (!opt) errors.push(`Pergunta ${i + 1}, opção ${oi + 1} está vazia.`);
      });
      if (!q.correct) errors.push(`Selecione a opção correta para a pergunta ${i + 1}.`);

      if (q.hasImage && q._fileInputElement) {
        const f = q._fileInputElement.files[0];
        if (f) {
          const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
          if (!allowed.includes(f.type)) errors.push(`A imagem da pergunta ${i + 1} deve ser JPG/PNG/WebP/GIF.`);
          if (f.size > 5 * 1024 * 1024) errors.push(`A imagem da pergunta ${i + 1} deve ter no máximo 5MB.`);
        }
      }
    });

    // Validate main cover image
    const cover = form.querySelector('#quiz_cover');
    if (cover && cover.files && cover.files[0]) {
      const f = cover.files[0];
      const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
      if (!allowed.includes(f.type)) errors.push('A imagem principal deve ser JPG/PNG/WebP/GIF.');
      if (f.size > 5 * 1024 * 1024) errors.push('A imagem principal deve ter no máximo 5MB.');
    }

    return errors;
  }

  async function submitForm(formData) {
    const submitBtn = form.querySelector('button[type="submit"]');
    let wasSuccessful = false;
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.dataset.orig = submitBtn.innerHTML;
      // show Bootstrap round spinner only
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
    }

    try {
      const res = await fetch('./actions/quizController.php', {
        method: 'POST',
        body: formData
      });
      const text = await res.text();
      let json = null;
      try { json = JSON.parse(text); } catch (e) { /* not JSON */ }

      if (res.ok && json && json.success) {
        wasSuccessful = true;
        showAlert(json.message || 'Quiz criado com sucesso.', 'success');
        if (json.scores_cleared) {
          showAlert('Aviso: alterações nas perguntas/respostas zeraram o placar deste quiz.', 'warning');
        }
        // restore label but keep disabled (not clickable)
        if (submitBtn) {
          submitBtn.innerHTML = submitBtn.dataset.orig || submitBtn.innerHTML;
          submitBtn.disabled = true; // ensure still disabled
        }
        // redirect to home.php after 4 seconds
        setTimeout(() => { window.location.href = 'home.php'; }, 4000);
      } else {
        const message = (json && json.message) || text || 'Erro ao criar quiz.';
        showAlert(message, 'danger');
      }
    } catch (err) {
      showAlert('Erro na requisição: ' + err.message, 'danger');
    } finally {
      // If not successful, restore button to original and re-enable so user can retry.
      if (!wasSuccessful && submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = submitBtn.dataset.orig || 'Publicar Quiz';
      }
      // If wasSuccessful we intentionally keep it disabled and label restored above, do not re-enable here.
    }
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearAlerts();
    const errors = validateForm();
    if (errors.length) {
      showAlert(errors.join('<br>'), 'danger');
      return;
    }

    const questions = collectQuestions();

    // If editing, detect textual changes (question text, options, correct) and warn user that these edits will reset the scoreboard
    const isEdit = !!form.querySelector('input[name="quiz_id"]');
    let needsWarn = false;
    try {
      const original = window.__ORIGINAL_QUESTIONS__ || null;
      if (isEdit && Array.isArray(original)) {
        // normalize current questions (ignore images)
        const normCurrent = questions.map(q => ({
          question: (q.question || '').trim(),
          options: (q.options || []).map(o => (o || '').trim()).slice(0,4).concat(['','','','']).slice(0,4),
          correct: parseInt(q.correct || 0, 10) || 0
        }));
        // normalize original (already server-provided) to ensure consistent ordering
        const normOrig = original.map(o => ({
          question: (o.question || '').trim(),
          options: (o.options || []).map(x => (x||'').trim()).slice(0,4).concat(['','','','']).slice(0,4),
          correct: parseInt(o.correct || 0, 10) || 0
        }));
        // detect differences: length or any textual difference
        if (normCurrent.length !== normOrig.length) needsWarn = true;
        else {
          for (let i = 0; i < normCurrent.length && !needsWarn; i++) {
            const a = normCurrent[i];
            const b = normOrig[i];
            if (a.question !== b.question) { needsWarn = true; break; }
            for (let j = 0; j < 4; j++) {
              if ((a.options[j] || '') !== (b.options[j] || '')) { needsWarn = true; break; }
            }
            if (a.correct !== b.correct) { needsWarn = true; break; }
          }
        }
      }
    } catch (err) { console.warn('Could not compute edit-diff', err); }

    if (isEdit && needsWarn) {
      const modalEl = document.getElementById('editWarningModal');
      if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        // show Bootstrap modal and wait for user confirmation
        const editModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
        const continueBtn = document.getElementById('editWarnContinue');
        // attach one-time handler
        const onContinue = () => {
          continueBtn.removeEventListener('click', onContinue);
          editModal.hide();
          // Re-collect current questions and build FormData now (so it reflects any last edits)
          const currentQuestions = collectQuestions();
          const fd2 = new FormData(form);
          // Ensure category is explicitly set (hidden input should exist, but set explicitly to be safe)
          const catEl = document.getElementById('quiz_category');
          if (catEl) fd2.set('categoria_id', catEl.value || '');
          const questionsForJson2 = currentQuestions.map(q => ({ question: q.question, options: q.options, correct: q.correct }));
          fd2.set('questions_json', JSON.stringify(questionsForJson2));
          // Append question images
          currentQuestions.forEach((q, i) => {
            if (q._fileInputElement && q._fileInputElement.files && q._fileInputElement.files[0]) {
              fd2.append(`question_image_${i}`, q._fileInputElement.files[0]);
            }
          });
          submitForm(fd2);
        };
        continueBtn.addEventListener('click', onContinue);
        editModal.show();
        return; // submission will happen from modal
      } else {
        // fallback to confirm
        const proceed = confirm('Atenção: editar o texto da pergunta, das opções ou alterar a resposta correta irá zerar o placar deste quiz. Alterar apenas imagens NÃO afeta o placar. Deseja continuar?');
        if (!proceed) return;
      }
    }

    // Build FormData now (no modal or modal was not needed)
    const fd = new FormData(form);
    // Ensure category explicitly present
    const catEl = document.getElementById('quiz_category');
    if (catEl) fd.set('categoria_id', catEl.value || '');
    const questionsForJson = questions.map(q => ({ question: q.question, options: q.options, correct: q.correct }));
    fd.set('questions_json', JSON.stringify(questionsForJson));
    // Append each question image file (if any) with a predictable key
    questions.forEach((q, i) => {
      if (q._fileInputElement && q._fileInputElement.files && q._fileInputElement.files[0]) {
        fd.append(`question_image_${i}`, q._fileInputElement.files[0]);
      }
    });

    await submitForm(fd);
  });

  // preview removed: no DOM updates needed

  // --- Category autocomplete ---
  (function() {
    const searchInput = document.getElementById('quiz_category_search');
    const hiddenInput = document.getElementById('quiz_category');
    const suggestions = document.getElementById('quizCategorySuggestions');
    if (!searchInput || !hiddenInput || !suggestions) return;

    let activeIndex = -1;
    let items = [];
    let controller = null;

    function setHidden(id, name) {
      hiddenInput.value = id || '';
      searchInput.value = name || '';
    }

    function clearSuggestions() {
      suggestions.innerHTML = '';
      suggestions.classList.add('d-none');
      activeIndex = -1;
      items = [];
    }

    function render(list) {
      suggestions.innerHTML = '';
      if (!list || !list.length) { clearSuggestions(); return; }
      const frag = document.createDocumentFragment();
      list.forEach((it, idx) => {
        const el = document.createElement('div');
        el.className = 'item small';
        el.tabIndex = 0;
        el.dataset.id = it.id;
        el.dataset.name = it.nome;
        el.textContent = it.nome;
        el.addEventListener('click', () => { setHidden(it.id, it.nome); clearSuggestions(); searchInput.focus(); });
        el.addEventListener('keydown', (ev) => { if (ev.key === 'Enter') { ev.preventDefault(); setHidden(it.id, it.nome); clearSuggestions(); } });
        frag.appendChild(el);
      });
      suggestions.appendChild(frag);
      suggestions.classList.remove('d-none');
      items = Array.from(suggestions.querySelectorAll('.item'));
      activeIndex = -1;
    }

    // Debounced fetch
    let debounceTimer = null;
    function fetchCategories(q) {
      if (controller) { controller.abort(); }
      controller = new AbortController();
      const signal = controller.signal;
      fetch(`./actions/fetch_categories.php?q=${encodeURIComponent(q)}&limit=30`, { signal })
        .then(r => r.json())
        .then(json => {
          if (!json || !json.success) return render([]);
          render(json.data || []);
        }).catch(err => {
          if (err.name === 'AbortError') return;
          console.error('Category fetch error', err);
          render([]);
        });
    }

    function onInput() {
      const v = searchInput.value.trim();
      hiddenInput.value = '';
      if (debounceTimer) clearTimeout(debounceTimer);
      if (!v) { clearSuggestions(); return; }
      debounceTimer = setTimeout(() => fetchCategories(v), 250);
    }

    searchInput.addEventListener('input', onInput);
    searchInput.addEventListener('blur', () => { setTimeout(clearSuggestions, 180); });

    searchInput.addEventListener('keydown', (e) => {
      if (!items.length) return;
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIndex = Math.min(activeIndex + 1, items.length - 1);
        items.forEach((it, i) => it.classList.toggle('active', i === activeIndex));
        items[activeIndex].scrollIntoView({block: 'nearest'});
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex = Math.max(activeIndex - 1, 0);
        items.forEach((it, i) => it.classList.toggle('active', i === activeIndex));
        items[activeIndex].scrollIntoView({block: 'nearest'});
      } else if (e.key === 'Enter') {
        e.preventDefault();
        if (activeIndex >= 0 && items[activeIndex]) {
          const it = items[activeIndex];
          setHidden(it.dataset.id, it.dataset.name);
          clearSuggestions();
        }
      }
    });

    // load initial suggestions (first N) to help users
    fetchCategories('');
  })();

});
