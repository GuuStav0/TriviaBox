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
			// Preferir checkboxes; se não houver, usar select (se presente)
			if (categoryCheckboxes && categoryCheckboxes.length) {
				return Array.from(document.querySelectorAll(`${checkboxSelector}:checked`)).map(cb => cb.value);
			}
			if (categorySelect) {
				return categorySelect.value ? [categorySelect.value] : [];
			}
			return [];
		}

		function filterQuizzes() {
			const nameVal = (quizNameFilter?.value || '').toLowerCase();
			const selectedCats = getSelectedCategories();

			quizItems.forEach(item => {
				const itemName = (item.dataset.name || '').toLowerCase();
				const itemCat = item.dataset.category || '';
				const nameMatch = !nameVal || itemName.includes(nameVal);
				const catMatch = !selectedCats.length || selectedCats.includes(itemCat);
				item.style.display = (nameMatch && catMatch) ? '' : 'none';
			});
		}

		// Listeners
		if (quizNameFilter) quizNameFilter.addEventListener('input', filterQuizzes);
		if (categoryCheckboxes && categoryCheckboxes.length) {
			categoryCheckboxes.forEach(cb => cb.addEventListener('change', filterQuizzes));
		}
		if (categorySelect) categorySelect.addEventListener('change', filterQuizzes);

		// Primeira execução
		filterQuizzes();
	});
})();