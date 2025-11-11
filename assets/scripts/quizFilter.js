(function () {
	if (window.__quizFilterInit) return;
	window.__quizFilterInit = true;

	document.addEventListener('DOMContentLoaded', function () {
		const quizNameFilter = document.getElementById('quizNameFilter');
		const quizItems = document.querySelectorAll('.quiz-item');
		const checkboxSelector = '.category-checkbox';
		const categoryCheckboxes = document.querySelectorAll(checkboxSelector);
		const categorySelect = document.getElementById('quizCategoryFilter'); // opcional (compatibilidade)

		function getSelectedCategories() {
			if (categoryCheckboxes && categoryCheckboxes.length) {
				return Array.from(document.querySelectorAll(`${checkboxSelector}:checked`)).map(cb => cb.value);
			}
			if (categorySelect) {
				return categorySelect.value ? [categorySelect.value] : [];
			}
			return [];
		}

		// Pagination state
		const ITEMS_PER_PAGE = 30;
		let currentPage = 1;

		function filterQuizzes() {
			const nameVal = (quizNameFilter?.value || '').toLowerCase();
			const selectedCats = getSelectedCategories();

			// collect matched items
			const matched = [];
			quizItems.forEach(item => {
				const itemName = (item.dataset.name || '').toLowerCase();
				const itemCatRaw = (item.dataset.category || '');
				const itemCats = itemCatRaw.split(',').map(s => s.trim().toLowerCase()).filter(Boolean);
				const nameMatch = !nameVal || itemName.includes(nameVal);
				let catMatch = true;
				if (selectedCats.length) {
					catMatch = selectedCats.every(sc => itemCats.includes(sc.toLowerCase()));
				}
				if (nameMatch && catMatch) matched.push(item);
			});

			// reset display for all items
			quizItems.forEach(i => i.style.display = 'none');

			// pagination calculations
			const total = matched.length;
			const totalPages = Math.max(1, Math.ceil(total / ITEMS_PER_PAGE));
			if (currentPage > totalPages) currentPage = totalPages;
			const start = (currentPage - 1) * ITEMS_PER_PAGE;
			const end = start + ITEMS_PER_PAGE;
			const pageItems = matched.slice(start, end);
			pageItems.forEach(i => i.style.display = '');

			renderPagination(totalPages, currentPage);

			// show/hide no-results message
			const noResults = document.getElementById('noResults');
			if (noResults) {
				noResults.style.display = total > 0 ? 'none' : '';
			}
		}

		function renderPagination(totalPages, activePage) {
			const container = document.getElementById('quizPagination');
			if (!container) return;
			container.innerHTML = '';
			if (totalPages <= 1) return;

			const makePageItem = (label, page, disabled, active) => {
				const li = document.createElement('li');
				li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
				const a = document.createElement('a');
				a.className = 'page-link';
				a.href = '#';
				a.dataset.page = page;
				a.textContent = label;
				a.addEventListener('click', function (e) {
					e.preventDefault();
					if (disabled) return;
					currentPage = page;
					filterQuizzes();
					const quizListEl = document.getElementById('quizList');
					if (quizListEl) quizListEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
				});
				li.appendChild(a);
				return li;
			};

			// Prev
			container.appendChild(makePageItem('Anterior', Math.max(1, activePage - 1), activePage === 1, false));

			// page numbers (show up to 7 pages, center on active)
			const maxButtons = 7;
			let startPage = 1;
			let endPage = totalPages;
			if (totalPages > maxButtons) {
				const half = Math.floor(maxButtons / 2);
				startPage = Math.max(1, activePage - half);
				endPage = startPage + maxButtons - 1;
				if (endPage > totalPages) {
					endPage = totalPages;
					startPage = endPage - maxButtons + 1;
				}
			}
			for (let p = startPage; p <= endPage; p++) {
				container.appendChild(makePageItem(String(p), p, false, p === activePage));
			}

			// Next
			container.appendChild(makePageItem('Próximo', Math.min(totalPages, activePage + 1), activePage === totalPages, false));
		}

		// Listeners
		if (quizNameFilter) quizNameFilter.addEventListener('input', filterQuizzes);
		if (categoryCheckboxes && categoryCheckboxes.length) {
			categoryCheckboxes.forEach(cb => {
				cb.addEventListener('change', function () {
					const lbl = cb.closest('label');
					if (lbl) {
						if (cb.checked) lbl.classList.add('selected');
						else lbl.classList.remove('selected');
					}
				});
			});
		}

		const applyBtn = document.getElementById('applyCategoriesBtn');
		if (applyBtn) {
			applyBtn.addEventListener('click', function () {
				filterQuizzes();
			});
		}
		if (categorySelect) categorySelect.addEventListener('change', filterQuizzes);

		filterQuizzes();
	});
})();