let pendingToasts = [];
let confirmation = null;

/**
 * Los <dialog>.showModal() se dibujan en la top layer del navegador.
 * Un toast con z-index:999999 fuera de esa capa seguirá quedando debajo.
 * Crear el host dentro del modal activo evita ese problema sin alterar
 * la semántica del dialog, el foco ni el bloqueo de la página.
 */
function toastContainer() {
    const globalHost = document.querySelector('[data-toast-container]');
    if (!globalHost) return null;

    const dialogs = [...document.querySelectorAll('dialog:modal')];
    if (!dialogs.length) return globalHost;

    // El foco normalmente está dentro del dialog en la top layer superior.
    // Esto también cubre el diálogo de confirmación abierto sobre un drawer.
    const focusedDialog = document.activeElement?.closest?.('dialog:modal');
    const topDialog = focusedDialog || dialogs[dialogs.length - 1];
    let localHost = topDialog.querySelector('[data-modal-toast-container]');
    if (!localHost) {
        localHost = document.createElement('div');
        localHost.setAttribute('data-modal-toast-container', '');
        localHost.setAttribute('aria-live', 'polite');
        localHost.setAttribute('aria-atomic', 'false');
        localHost.className = 'pointer-events-none fixed bottom-4 right-4 z-[100] flex w-[calc(100%-2rem)] max-w-sm max-h-[calc(100dvh-2rem)] flex-col gap-3 overflow-y-auto pb-[env(safe-area-inset-bottom)]';
        topDialog.append(localHost);
    }
    return localHost;
}

export function showToast(message, type = 'success') {
    const container = toastContainer();
    if (!container) return;
    const toast = document.createElement('div');
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
    toast.className = `pointer-events-auto flex items-start gap-3 rounded-xl border p-4 text-sm shadow-lg ${type === 'error'
        ? 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200'
        : 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200'}`;
    const messages = [...new Set((Array.isArray(message) ? message : String(message).split('\n')).map((value) => String(value).trim()).filter(Boolean))];
    const text = document.createElement(messages.length > 1 ? 'ul' : 'p');
    text.className = 'min-w-0 flex-1 whitespace-pre-line break-words';
    if (messages.length > 1) {
        text.classList.add('list-disc', 'space-y-2', 'pl-4');
        messages.forEach((value) => {
            const item = document.createElement('li');
            item.textContent = value;
            text.append(item);
        });
    } else text.textContent = messages[0] || '';
    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'shrink-0 rounded px-1 font-semibold';
    close.setAttribute('aria-label', 'Cerrar notificación');
    close.textContent = '×';
    close.addEventListener('click', () => toast.remove());
    toast.append(text, close);
    while (container.children.length >= 3) container.firstElementChild.remove();
    container.append(toast);
    let timer;
    const startTimer = () => { timer = window.setTimeout(() => toast.remove(), type === 'error' ? 10000 : 6000); };
    const stopTimer = () => window.clearTimeout(timer);
    toast.addEventListener('mouseenter', stopTimer);
    toast.addEventListener('mouseleave', startTimer);
    toast.addEventListener('focusin', stopTimer);
    toast.addEventListener('focusout', startTimer);
    startTimer();
}

export function collectFlashToasts(root) {
    return [...root.querySelectorAll('[data-flash-toast]')].map((element) => ({
        message: element.content.textContent.trim(), type: element.dataset.flashToast,
    }));
}

export function queueToasts(toasts) {
    pendingToasts.push(...toasts);
}

function initializeFeedback() {
    const messages = [...pendingToasts, ...collectFlashToasts(document)];
    pendingToasts = [];
    const seen = new Set();
    messages.forEach(({ message, type }) => {
        const key = `${type}:${message}`;
        if (!seen.has(key)) showToast(message, type);
        seen.add(key);
    });
    document.querySelectorAll('[data-flash-toast]').forEach((element) => element.remove());
}

export function confirmForm(form, submitter) {
    const dialog = document.querySelector('[data-confirm-dialog]');
    if (!dialog || confirmation) return;
    confirmation = { form, submitter, focus: document.activeElement };
    dialog.querySelector('[data-confirm-description]').textContent = form.dataset.confirm;
    dialog.querySelector('#confirm-title').textContent = form.dataset.confirmTitle || 'Confirmar eliminación';
    dialog.querySelector('[data-confirm-accept]').textContent = form.dataset.confirmLabel || 'Eliminar';
    const accept = dialog.querySelector('[data-confirm-accept]');
    const primary = form.dataset.confirmTone === 'primary';
    accept.classList.toggle('bg-red-600', !primary);
    accept.classList.toggle('hover:bg-red-700', !primary);
    accept.classList.toggle('bg-brand-600', primary);
    accept.classList.toggle('hover:bg-brand-700', primary);
    dialog.showModal();
}

// Al cerrar un dialog, traslada los mensajes aún visibles al host global.
// Evita perder errores al cerrar drawers rápidamente y conserva sus temporizadores.
document.addEventListener('close', (event) => {
    if (!event.target.matches?.('dialog')) return;
    const localHost = event.target.querySelector('[data-modal-toast-container]');
    if (!localHost) return;
    // Si aún hay otro modal (p. ej. drawer bajo confirmación), usar su
    // top layer en vez de enviar el mensaje detrás al contenedor global.
    const destination = toastContainer();
    if (destination && destination !== localHost) {
        while (localHost.firstElementChild) {
            while (destination.children.length >= 3) destination.firstElementChild.remove();
            destination.append(localHost.firstElementChild);
        }
    }
    localHost.remove();
}, true);

document.addEventListener('click', (event) => {
    const dialog = document.querySelector('[data-confirm-dialog]');
    if (event.target.closest('[data-confirm-cancel]')) dialog?.close();
    if (event.target.closest('[data-confirm-accept]') && confirmation) {
        const { form, submitter } = confirmation;
        dialog.close();
        form.dataset.confirmed = 'true';
        form.requestSubmit(submitter || undefined);
        delete form.dataset.confirmed;
    }
});
document.addEventListener('close', (event) => {
    if (!event.target.matches('[data-confirm-dialog]')) return;
    confirmation?.focus?.focus();
    confirmation = null;
}, true);
document.addEventListener('livewire:navigating', () => {
    document.querySelector('[data-confirm-dialog]')?.close();
    confirmation = null;
});
document.addEventListener('DOMContentLoaded', initializeFeedback);
document.addEventListener('livewire:navigated', initializeFeedback);
