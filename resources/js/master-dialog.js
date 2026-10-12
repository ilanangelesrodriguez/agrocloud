import { showToast } from './feedback';

let context = null;

function showError(dialog, message) {
    const feedback = dialog.querySelector('[data-master-dialog-error]');
    feedback.textContent = message;
    feedback.hidden = false;
}

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-master-create]');
    const dialog = document.querySelector('[data-master-dialog]');
    if (event.target.closest('[data-master-dialog-cancel]')) dialog?.close();
    if (!button || !dialog || context) return;
    const select = button.parentElement.querySelector('select[data-master-kind]');
    const form = select?.closest('form');
    if (!select || !form) return;
    const product = form.querySelector('[data-master-kind="producto"]');
    const parent = select.dataset.masterKind === 'variedad' ? product?.value : '';
    if (select.dataset.masterKind === 'variedad' && !parent) {
        showToast('Primero selecciona un producto para agregar su variedad.', 'error');
        product?.focus();
        return;
    }
    context = { button, select, form, parent, url: button.dataset.masterUrl, busy: false };
    const modalForm = dialog.querySelector('form');
    modalForm.reset();
    dialog.querySelector('[data-master-dialog-error]').hidden = true;
    dialog.querySelector('[data-master-dialog-title]').textContent = `Agregar opción · ${button.dataset.masterTitle}`;
    dialog.querySelector('[data-master-scope-message]').textContent = button.dataset.masterScope === 'global' ? 'Disponible para todas las empresas.' : 'Disponible solo para tu empresa.';
    const caption = dialog.querySelector('[data-master-parent]');
    caption.classList.toggle('hidden', !parent);
    caption.textContent = parent ? `Producto: ${parent}` : '';
    dialog.querySelector('[data-master-code-field]').hidden = select.dataset.masterKind !== 'unidad';
    dialog.showModal();
    dialog.querySelector('[name="nombre"]').focus();
});

document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!form.matches('[data-master-inline-form]')) return;
    event.preventDefault();
    if (!context || context.busy) return;
    const current = context;
    const dialog = form.closest('dialog');
    const name = form.elements.nombre.value.trim();
    if (!name) {
        showError(dialog, 'Escribe el nombre de la opción.');
        form.elements.nombre.focus();
        return;
    }
    current.busy = true;
    const submit = form.querySelector('[type="submit"]');
    const cancel = form.querySelector('[data-master-dialog-cancel]');
    submit.disabled = cancel.disabled = true;
    submit.textContent = 'Guardando…';
    dialog.querySelector('[data-master-dialog-error]').hidden = true;
    try {
        const body = new FormData(form);
        body.set('nombre', name);
        if (current.parent) body.set('parent_name', current.parent);
        const response = await fetch(current.url, {
            method: 'POST', credentials: 'same-origin', body,
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.errors ? Object.values(result.errors).flat().join(' ') : result.message || 'No se pudo guardar la opción.');
        if (context !== current || !current.select.isConnected) return;
        const option = document.createElement('option');
        option.value = option.textContent = result.option.nombre;
        option.dataset.parent = result.option.parent || '';
        current.select.append(option);
        current.select.disabled = false;
        current.select.value = option.value;
        current.select.dispatchEvent(new CustomEvent('master:added', { detail: option }));
        current.select.dispatchEvent(new Event('input', { bubbles: true }));
        current.select.dispatchEvent(new Event('change', { bubbles: true }));
        current.focusTarget = current.select;
        dialog.close();
        showToast('Opción creada y seleccionada.');
    } catch (error) {
        if (context === current) showError(dialog, error.message || 'No se pudo guardar. Inténtalo nuevamente.');
    } finally {
        current.busy = false;
        submit.disabled = cancel.disabled = false;
        submit.textContent = 'Guardar y seleccionar';
    }
});

document.addEventListener('cancel', (event) => {
    if (event.target.matches('[data-master-dialog]') && context?.busy) event.preventDefault();
}, true);
document.addEventListener('close', (event) => {
    if (!event.target.matches('[data-master-dialog]')) return;
    (context?.focusTarget || context?.button)?.focus();
    context = null;
}, true);
document.addEventListener('livewire:navigating', () => {
    document.querySelector('[data-master-dialog]')?.close();
    context = null;
});
