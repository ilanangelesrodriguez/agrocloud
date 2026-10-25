import { showToast } from './feedback';

const errorMessage = 'Escribe un término para buscar.';

export function validateSearchForm(form) {
    let firstInvalid;
    const messages = new Set();
    const hasFilters = [...form.querySelectorAll('select[name]')].some((input) => input.tagName === 'SELECT' && input.value !== '' && !input.disabled);
    form.querySelectorAll('[data-search-input]').forEach((input) => {
        const message = !input.value.trim() && !hasFilters ? errorMessage
            : input.value.trim().length > 150 ? 'La búsqueda no puede superar los 150 caracteres.' : '';
        input.setAttribute('aria-invalid', String(Boolean(message)));
        const field = input.closest?.('[data-search-field]') || input.parentElement;
        const error = field.querySelector('[data-search-error]');
        if (error) clearError(input, error);
        input.setAttribute('aria-invalid', String(Boolean(message)));
        if (message) {
            messages.add(message);
            firstInvalid ||= input;
        }

    });
    if (messages.size) showToast([...messages].join(' '), 'error');
    firstInvalid?.focus();
    return !firstInvalid;
}

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
    else input.removeAttribute('aria-invalid');
});
