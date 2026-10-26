let sequence = 0;

function controls(form, name) {
    const bracketName = name.replace(/\.([^.]+)/g, '[$1]');
    return [...form.elements].filter((input) => input.name === name || input.name === bracketName);
}

export function clearFieldError(input) {
    const id = input.dataset.fieldErrorId;
    if (id) {
        input.ownerDocument.getElementById(id)?.remove();
        const remaining = (input.getAttribute('aria-describedby') || '').split(/\s+/).filter((value) => value && value !== id);
        if (remaining.length) input.setAttribute('aria-describedby', remaining.join(' '));
        else input.removeAttribute('aria-describedby');
        delete input.dataset.fieldErrorId;
    }
    input.removeAttribute('aria-invalid');
    input.classList.remove('border-red-400');
}

export function showFieldError(input, message) {
    clearFieldError(input);
    const error = document.createElement('p');
    error.id = `field-error-${++sequence}`;
    error.dataset.formFieldError = input.name;
    error.className = 'mt-1 text-sm text-red-600 dark:text-red-300';
    error.textContent = message;
    input.dataset.fieldErrorId = error.id;
    input.setAttribute('aria-invalid', 'true');
    input.classList.add('border-red-400');
    input.setAttribute('aria-describedby', [input.getAttribute('aria-describedby'), error.id].filter(Boolean).join(' '));
    const anchor = input.type === 'checkbox' || input.type === 'radio' ? (input.closest('label') || input) : input;
    anchor.insertAdjacentElement('afterend', error);
}

export function showFormErrors(form, errors) {
    form.querySelector('[data-form-errors]')?.remove();
    form.querySelectorAll('[aria-invalid="true"]').forEach(clearFieldError);
    form.querySelectorAll('[data-form-field-error]').forEach((message) => message.remove());
    let first;
    const general = [];
    Object.entries(errors || {}).forEach(([name, messages]) => {
        const message = (Array.isArray(messages) ? messages : [messages]).filter(Boolean).join(' ');
        const fields = controls(form, name).filter((input) => input.type !== 'hidden' && !input.disabled);
        if (!fields.length) { general.push(message); return; }
        fields.forEach((input) => showFieldError(input, message));
        first ||= fields[0];
    });
    if (general.length) {
        const summary = document.createElement('p');
        summary.dataset.formErrors = '';
        summary.setAttribute('role', 'alert');
        summary.className = 'mb-4 text-sm text-red-600 dark:text-red-300';
        summary.textContent = [...new Set(general)].join(' ');
        form.prepend(summary);
    }
    form.dispatchEvent(new CustomEvent('form:errors', { detail: { first, errors } }));
    first?.focus();
}

function nativeMessage(input) {
    const labelNode = input.labels?.[0]?.cloneNode(true);
    labelNode?.querySelectorAll('input, select, textarea, [data-form-field-error]').forEach((node) => node.remove());
    const label = labelNode?.textContent.replace(/\s*\*\s*$/, '').trim() || input.name;
    const validity = input.validity;
    if (validity.valueMissing) return input.type === 'checkbox' ? 'Debes aceptar esta autorización para continuar.' : `Completa el campo ${label}.`;
    if (validity.typeMismatch && input.type === 'email') return 'Ingresa un correo electrónico válido.';
    if (validity.tooShort) return `Ingresa al menos ${input.minLength} caracteres.`;
    if (validity.tooLong) return `Ingresa como máximo ${input.maxLength} caracteres.`;
    if (validity.patternMismatch && input.name === 'ruc') return 'El RUC debe tener exactamente 11 dígitos, sin espacios ni guiones.';
    return input.validationMessage || `Revisa el campo ${label}.`;
}

let firstInvalid;
document.addEventListener('invalid', (event) => {
    const input = event.target;
    if (!input.closest('form[data-ajax-form]')) return;
    event.preventDefault();
    showFieldError(input, nativeMessage(input));
    if (firstInvalid) return;
    firstInvalid = input;
    setTimeout(() => { firstInvalid?.focus(); firstInvalid = null; }, 0);
}, true);

for (const event of ['input', 'change']) document.addEventListener(event, ({ target }) => {
    if (!target.matches?.('input, select, textarea') || !target.closest('form') || target.getAttribute('aria-invalid') !== 'true') return;
    if (target.validity.valid) clearFieldError(target);
    else showFieldError(target, nativeMessage(target));
});

function hydrateErrors() {
    document.querySelectorAll('template[data-validation-errors]').forEach((template) => {
        const errors = JSON.parse(template.content.textContent);
        const forms = [...document.querySelectorAll('form')];
        const form = forms.find((candidate) => Object.keys(errors).some((name) => controls(candidate, name).length)) || forms[0];
        if (form) showFormErrors(form, errors);
        template.remove();
    });
}
document.addEventListener('DOMContentLoaded', hydrateErrors);
document.addEventListener('livewire:navigated', hydrateErrors);
if (document.readyState !== 'loading') hydrateErrors();
