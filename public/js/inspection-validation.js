/** Guardar revisiones y hallazgos sin perder el contexto de la página. */
(() => {
    'use strict';
    if (window.agrocloudInitValidation) { window.agrocloudInitValidation(); return; }
    const initialized = new WeakMap();
    const initialize = () => {
    const root = document.querySelector('[data-validation-workspace]');
    if (!root || (initialized.has(root) && !initialized.get(root).signal.aborted)) return;
    const lifecycle = new AbortController();
    initialized.set(root, lifecycle);
    const on = (element, type, callback, options = {}) => element?.addEventListener(type, callback, { ...options, signal: lifecycle.signal });
    document.addEventListener('livewire:navigating', () => lifecycle.abort(), { once: true });
    const notification = root.querySelector('[data-validation-message]');

    function show(message, error = false) {
        document.dispatchEvent(new CustomEvent('agrocloud:toast', { detail: { message, type: error ? 'error' : 'success' } }));
        notification.className = `rounded-xl border px-4 py-3 text-sm ${error
            ? 'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300'
            : 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200'}`;
        notification.textContent = message;
    }

    async function submit(form) {
        const response = await fetch(form.action, {
            method: 'POST', credentials: 'same-origin', signal: lifecycle.signal,
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')?.value || '' },
            body: new FormData(form),
        });
        if (!(response.headers.get('content-type') || '').includes('application/json')) throw new Error('La sesión expiró o el servidor no devolvió JSON.');
        const data = await response.json();
        if (!response.ok || !data.ok) throw new Error(data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'No se pudo guardar.'));
        return data;
    }

    const photos = [...root.querySelectorAll('[data-review-photo]')];
    const navigation = root.querySelector('[data-review-navigation]');
    let photoIndex = 0;
    const choose = index => {
        photoIndex = Math.max(0, Math.min(photos.length - 1, index));
        photos.forEach((photo, n) => { photo.hidden = n !== photoIndex; });
        if (!photos.length) return;
        navigation.querySelector('[data-review-select]').value = photos[photoIndex].dataset.reviewPhoto;
        navigation.querySelector('[data-review-position]').textContent = `${photoIndex + 1} / ${photos.length}`;
        navigation.querySelector('[data-review-prev]').disabled = photoIndex === 0;
        navigation.querySelector('[data-review-next]').disabled = photoIndex === photos.length - 1;
        const evidence = root.querySelector('[name=id_evidencia]'); if (evidence) evidence.value = photos[photoIndex].dataset.reviewPhoto;
    };
    if (photos.length && navigation) {
        navigation.classList.remove('hidden'); navigation.classList.add('flex');
        on(navigation.querySelector('[data-review-prev]'), 'click', () => choose(photoIndex - 1));
        on(navigation.querySelector('[data-review-next]'), 'click', () => choose(photoIndex + 1));
        on(navigation.querySelector('[data-review-select]'), 'change', event => choose(photos.findIndex(photo => photo.dataset.reviewPhoto === event.target.value)));
        choose(photos.findIndex(photo => photo.querySelector('[data-review-current="0"]')));
    }

    on(root, 'submit', async event => {
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
                entry.append(strong, document.createTextNode(` — ${data.hallazgo.observaciones} · ${String(data.hallazgo.naturaleza || '').replaceAll('_', ' ')} · Evidencia #${data.hallazgo.evidencia_id}`));
                list.append(entry);
                root.querySelector('[data-hallazgos-empty]')?.classList.add('hidden');
                form.reset();
            }
            const evidenceId = data.evidencia_id || data.hallazgo?.evidencia_id;
            const review = [...root.querySelectorAll('[data-image-review]')].find(item => item.dataset.imageReview === String(evidenceId));
            review?.querySelector('[data-review-changed]')?.classList.remove('hidden');
            if (review) {
                review.dataset.reviewCurrent = '0';
                const conclusion = review.querySelector('[name=conclusion]');
                if (conclusion && data.conclusion) conclusion.value = data.conclusion;
                const proposed = review.querySelector('[data-proposed-conclusion]');
                if (proposed && data.conclusion) proposed.textContent = data.conclusion === 'CON_HALLAZGOS' ? 'Con hallazgos confirmados o registrados manualmente.' : 'Sin hallazgos registrados. Revisa personalmente la imagen antes de confirmarlo.';
                review.querySelectorAll('[name=revision_completa], [name=imagen_pertinente]').forEach(input => { input.checked = false; });
            }
            const reviewed = root.querySelectorAll('[data-review-current="1"]').length;
            if (root.querySelector('[data-review-check-count]')) root.querySelector('[data-review-check-count]').textContent = String(reviewed);
            if (root.querySelector('[data-human-reviewed]')) root.querySelector('[data-human-reviewed]').textContent = String(reviewed);
            if (root.querySelector('[data-human-progress]')) root.querySelector('[data-human-progress]').value = reviewed;
            const photoOption = [...(navigation?.querySelector('[data-review-select]')?.options || [])].find(option => option.value === String(evidenceId));
            if (photoOption) photoOption.textContent = `Evidencia #${evidenceId} · Pendiente`;
            choose(photoIndex);
            const finalize = root.querySelector('[data-review-finalize]');
            if (finalize) finalize.disabled = finalize.dataset.reviewCovered !== '1' || reviewed !== Number(finalize.dataset.reviewTotal) || reviewed === 0;
            show(data.message || 'Información guardada.');
        } catch (error) {
            if (error.name === 'AbortError' || !root.isConnected) return;
            show(error.message || 'No se pudo guardar.', true);
        } finally {
            button.disabled = false;
            button.textContent = label;
        }
    });
    };
    window.agrocloudInitValidation = initialize;
    document.addEventListener('livewire:navigated', initialize);
    initialize();
})();
