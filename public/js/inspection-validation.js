/** Guardar revisiones y hallazgos sin perder el contexto de la página. */
(() => {
    'use strict';
    const root = document.querySelector('[data-validation-workspace]');
    if (!root) return;
    const notification = root.querySelector('[data-validation-message]');

    function show(message, error = false) {
        notification.className = `rounded-xl border px-4 py-3 text-sm ${error
            ? 'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300'
            : 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200'}`;
        notification.textContent = message;
    }

    async function submit(form) {
        const response = await fetch(form.action, {
            method: 'POST', credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')?.value || '' },
            body: new FormData(form),
        });
        if (!(response.headers.get('content-type') || '').includes('application/json')) throw new Error('La sesión expiró o el servidor no devolvió JSON.');
        const data = await response.json();
        if (!response.ok || !data.ok) throw new Error(data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'No se pudo guardar.'));
        return data;
    }

    root.addEventListener('submit', async event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('[data-validation-form], [data-hallazgo-form]')) return;
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        if (button.disabled) return;
        button.disabled = true;
        const label = button.textContent;
        button.textContent = 'Guardando…';
        try {
            const data = await submit(form);
            if (form.matches('[data-validation-form]')) {
                const row = form.closest('[data-validation-row]');
                row.dataset.saved = '1';
                const state = row.querySelector('[data-validation-state]');
                if (state) state.textContent = data.confirmada ? 'Confirmada y guardada' : 'Descartada y guardada';
                const count = root.querySelector('[data-validation-count]');
                if (count) count.textContent = String(root.querySelectorAll('[data-validation-row][data-saved="1"]').length);
            } else if (data.hallazgo) {
                const list = root.querySelector('[data-hallazgos-list]');
                const entry = document.createElement('p');
                entry.className = 'rounded-lg bg-slate-50 p-3 text-sm text-slate-700 dark:bg-slate-800 dark:text-slate-200';
                const strong = document.createElement('strong');
                strong.textContent = data.hallazgo.clase;
                entry.append(strong, document.createTextNode(` — ${data.hallazgo.observaciones}`));
                list.append(entry);
                root.querySelector('[data-hallazgos-empty]')?.classList.add('hidden');
                form.reset();
            }
            show(data.message || 'Información guardada.');
        } catch (error) {
            show(error.message || 'No se pudo guardar.', true);
        } finally {
            button.disabled = false;
            button.textContent = label;
        }
    });
})();
