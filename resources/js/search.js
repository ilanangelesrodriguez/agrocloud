const errorMessage = 'Escribe un término para buscar.';

export function validateSearchForm(form) {
    let firstInvalid;
    const hasFilters = [...form.querySelectorAll('select[name]')].some((input) => input.tagName === 'SELECT' && input.value !== '' && !input.disabled);
    form.querySelectorAll('[data-search-input]').forEach((input) => {
        const message = !input.value.trim() && !hasFilters ? errorMessage
            : input.value.trim().length > 150 ? 'La búsqueda no puede superar los 150 caracteres.' : '';
        input.setAttribute('aria-invalid', String(Boolean(message)));
        const field = input.closest?.('[data-search-field]') || input.parentElement;
        let error = field.querySelector('[data-search-error]');
        if (message) {
            if (!error) {
                error = document.createElement('p');
                error.dataset.searchError = '';
                error.id = `search-error-${++errorId}`;
                error.className = 'mt-1.5 text-xs text-red-600 dark:text-red-300';
                error.setAttribute('role', 'alert');
                field.append(error);
                input.setAttribute('aria-describedby', [input.getAttribute('aria-describedby'), error.id].filter(Boolean).join(' '));
            }
            error.textContent = message;
            firstInvalid ||= input;
        } else if (error) {
            clearError(input, error);
        }
    });
    firstInvalid?.focus();
    return !firstInvalid;
}

let errorId = 0;
function clearError(input, error) {
    const describedBy = (input.getAttribute('aria-describedby') || '').split(' ').filter((id) => id !== error.id).join(' ');
    if (describedBy) input.setAttribute('aria-describedby', describedBy);
    else input.removeAttribute('aria-describedby');
    input.removeAttribute('aria-invalid');
    error.remove();
}

// Captura antes de Livewire y de los formularios AJAX para evitar cualquier petición vacía.
document.addEventListener('submit', (event) => {
    if (!validateSearchForm(event.target)) {
        event.preventDefault();
        event.stopImmediatePropagation();
    }
}, true);
document.addEventListener('input', (event) => {
    const input = event.target;
    if (!input.matches('[data-search-input]')) return;
    const field = input.closest?.('[data-search-field]') || input.parentElement;
    const error = field.querySelector('[data-search-error]');
    if (error) clearError(input, error);
});
