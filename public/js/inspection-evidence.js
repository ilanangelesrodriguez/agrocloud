/**
 * Captura y análisis IA sin recargas: mejora progresiva sobre los formularios Laravel.
 * No incluye claves ni hace peticiones desde el navegador a FastAPI.
 */
(() => {
    'use strict';
    if (window.agrocloudInitEvidence) { window.agrocloudInitEvidence(); return; }
    const initialized = new WeakMap();
    const initialize = () => {

    const root = document.querySelector('[data-evidence-workspace]');
    if (!root || (initialized.has(root) && !initialized.get(root).signal.aborted)) return;
    const lifecycle = new AbortController();
    initialized.set(root, lifecycle);
    const on = (element, type, callback, options = {}) => element?.addEventListener(type, callback, { ...options, signal: lifecycle.signal });
    document.addEventListener('livewire:navigating', () => lifecycle.abort(), { once: true });

    const list = root.querySelector('[data-evidence-list]');
    const empty = root.querySelector('[data-evidence-empty]');
    const notification = root.querySelector('[data-workspace-message]');
    const total = root.querySelector('[data-evidence-count]');
    const analyzed = root.querySelector('[data-analyzed-count]');
    const MAX_SERVER_BYTES = 3 * 1024 * 1024;
    const TARGET_BYTES = Math.floor(2.5 * 1024 * 1024);
    const uploadForms = [...root.querySelectorAll('[data-upload-form]')];
    let uploadBusy = false;

    function notify(message, type = 'success') {
        if (type !== 'info') document.dispatchEvent(new CustomEvent('agrocloud:toast', { detail: { message, type } }));
        notification.textContent = message;
        notification.className = `rounded-xl border px-4 py-3 text-sm ${type === 'success'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200'
            : type === 'info'
                ? 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-900 dark:bg-blue-950/50 dark:text-blue-200'
                : 'border-red-200 bg-red-50 text-red-800 dark:border-red-900 dark:bg-red-950/50 dark:text-red-200'}`;
    }

    function readableBytes(bytes) {
        return `${(bytes / 1024 / 1024).toFixed(2)} MiB`;
    }

    async function optimizeImage(file) {
        if (!/^image\/(jpeg|png|webp)$/.test(file.type)) {
            throw new Error('Elige una imagen JPG, PNG o WebP.');
        }
        // Fast path: archivos pequeños se conservan íntegros.
        if (file.size <= TARGET_BYTES) return file;
        if (!window.createImageBitmap || !HTMLCanvasElement.prototype.toBlob) {
            throw new Error('Tu navegador no admite optimización. Selecciona una foto menor de 3 MiB.');
        }

        let bitmap;
        try {
            bitmap = await createImageBitmap(file);
        } catch (_) {
            throw new Error('No se pudo abrir la imagen para optimizarla. Prueba con una foto JPG.');
        }
        try {
            let scale = Math.min(1, 1920 / Math.max(bitmap.width, bitmap.height));
            let quality = 0.84;
            let blob = null;
            for (let attempt = 0; attempt < 9; attempt++) {
                const canvas = document.createElement('canvas');
                canvas.width = Math.max(1, Math.round(bitmap.width * scale));
                canvas.height = Math.max(1, Math.round(bitmap.height * scale));
                const context = canvas.getContext('2d', { alpha: false });
                if (!context) throw new Error('No fue posible preparar la imagen.');
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);
                context.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
                blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', quality));
                if (!blob) throw new Error('No se pudo comprimir la fotografía.');
                if (blob.size <= TARGET_BYTES) break;
                if (quality > 0.60) quality -= 0.08;
                else scale *= 0.80;
            }
            if (!blob || blob.size > MAX_SERVER_BYTES) {
                throw new Error('La fotografía aún supera 3 MiB. Prueba con una de menor resolución.');
            }
            return new File([blob], (file.name.replace(/\.[^.]+$/, '') || 'evidencia') + '.jpg', {
                type: 'image/jpeg', lastModified: Date.now(),
            });
        } finally {
            if (bitmap && typeof bitmap.close === 'function') bitmap.close();
        }
    }

    async function sendForm(form, formData) {
        const token = form.querySelector('input[name="_token"]')?.value;
        const response = await fetch(form.action, {
            method: 'POST', signal: lifecycle.signal,
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token || '' },
            body: formData,
        });
        if (response.redirected && new URL(response.url).pathname.includes('/login')) {
            throw new Error('Tu sesión caducó. Inicia sesión de nuevo.');
        }
        const type = response.headers.get('content-type') || '';
        if (!type.includes('application/json')) {
            throw new Error('El servidor no devolvió JSON. Comprueba la sesión y el servicio.');
        }
        const data = await response.json();
        if (!response.ok || !data.ok) {
            const validation = data.errors ? Object.values(data.errors).flat().join(' ') : '';
            const error = new Error(validation || data.message || `Error HTTP ${response.status}`);
            error.responseData = data;
            error.status = response.status;
            throw error;
        }
        return data;
    }

    function replaceEvidence(data, isNew = false) {
        if (!data.html || !data.evidencia_id) return;
        const existing = [...list.querySelectorAll('[data-evidencia-id]')]
            .find(element => element.dataset.evidenciaId === String(data.evidencia_id));
        const template = document.createElement('template');
        template.innerHTML = data.html.trim();
        const next = template.content.firstElementChild;
        if (!next) return;
        if (existing) existing.replaceWith(next);
        else if (isNew) list.prepend(next);
        else list.append(next);
        if (empty) empty.classList.toggle('hidden', list.children.length > 0);
        const cards = [...list.querySelectorAll('[data-evidencia-id]')].filter(card => card.dataset.usable !== '0');
        const count = cards.length;
        const done = cards.filter(card => card.dataset.analyzed === '1').length;
        if (total) total.textContent = String(count);
        if (analyzed) analyzed.textContent = String(done);
        const progress = root.querySelector('[data-analysis-progress]');
        if (progress) { progress.max = Math.max(1, count); progress.value = done; }
        const progressLabel = root.querySelector('[data-analysis-progress-label]');
        if (progressLabel) progressLabel.textContent = `${done} de ${count} fotografías analizadas con IA. La revisión humana es una etapa independiente.`;

        const reviewNext = root.querySelector('[data-validation-next]');
        if (reviewNext && count > 0) { reviewNext.removeAttribute('aria-disabled'); reviewNext.removeAttribute('tabindex'); reviewNext.classList.remove('pointer-events-none', 'opacity-50'); reviewNext.querySelector('[data-validation-next-label]').textContent = 'Continuar revisión humana'; }
        const covered = new Set(cards.map(card => card.dataset.frutoId).filter(Boolean)).size;
        const fruitTotal = Number(root.querySelector('[data-fruit-total]')?.textContent || 0);
        const human = cards.filter(card => card.dataset.reviewed === '1').length;
        if (root.querySelector('[data-fruit-covered]')) root.querySelector('[data-fruit-covered]').textContent = String(covered);
        if (root.querySelector('[data-fruit-missing]')) root.querySelector('[data-fruit-missing]').textContent = String(Math.max(0, fruitTotal - covered));
        if (root.querySelector('[data-human-reviewed]')) root.querySelector('[data-human-reviewed]').textContent = String(human);
        if (root.querySelector('[data-human-total]')) root.querySelector('[data-human-total]').textContent = String(count);
        const fruitProgress = root.querySelector('[data-fruit-progress]'); if (fruitProgress) fruitProgress.value = covered;
        const humanProgress = root.querySelector('[data-human-progress]'); if (humanProgress) { humanProgress.max = Math.max(1, count); humanProgress.value = human; }
        root.querySelectorAll('[data-fruit-pick]:not([data-fruit-next])').forEach(button => { button.hidden = cards.some(card => card.dataset.frutoNumero === button.dataset.fruitPick); });
    }

    function clearPreview(form) {
        const image = form.querySelector('[data-preview-image]');
        if (image && image.dataset.objectUrl) {
            URL.revokeObjectURL(image.dataset.objectUrl);
            delete image.dataset.objectUrl;
        }
        const preview = form.querySelector('[data-upload-preview]');
        if (preview) preview.classList.add('hidden');
        if (image) image.removeAttribute('src');
    }

    for (const form of uploadForms) {
        on(form.querySelector('[data-file-input]'), 'change', () => {
            clearPreview(form);
            const file = form.querySelector('[data-file-input]')?.files?.[0];
            if (!file) return;
            const image = form.querySelector('[data-preview-image]');
            const preview = form.querySelector('[data-upload-preview]');
            if (image && preview) {
                image.dataset.objectUrl = URL.createObjectURL(file);
                image.src = image.dataset.objectUrl;
                preview.classList.remove('hidden');
                const meta = form.querySelector('[data-preview-meta]');
                if (meta) meta.textContent = `${file.name} · ${readableBytes(file.size)}`;
            }
        });
    }

    function useFile(form, files) {
        if (!files?.length) return;
        if (uploadBusy || root.dataset.analysisBusy === '1') { notify('Espera a que termine el proceso actual.', 'info'); return; }
        if (files.length !== 1) { notify('Selecciona una fotografía por vez para conservar su vínculo con el fruto.', 'error'); return; }
        const input = form.querySelector('[data-file-input]');
        try { const transfer = new DataTransfer(); transfer.items.add(files[0]); input.files = transfer.files; input.dispatchEvent(new Event('change', { bubbles: true })); }
        catch (_) { notify('Utiliza el selector de archivos de tu navegador.', 'error'); }
    }
    for (const form of uploadForms) {
        on(form.querySelector('[data-camera-input]'), 'change', event => useFile(form, event.target.files));
        const dropzone = form.querySelector('[data-photo-dropzone]');
        on(dropzone, 'dragover', event => { event.preventDefault(); dropzone.classList.add('ring-2', 'ring-emerald-500'); });
        on(dropzone, 'dragleave', () => dropzone.classList.remove('ring-2', 'ring-emerald-500'));
        on(dropzone, 'drop', event => { event.preventDefault(); dropzone.classList.remove('ring-2', 'ring-emerald-500'); useFile(form, event.dataTransfer.files); });
    }
    on(root, 'click', event => {
        const fruit = event.target.closest('[data-fruit-pick]');
        if (fruit) { const input = root.querySelector('[name=numero_fruto]'); if (input) { input.value = fruit.dataset.fruitPick; input.dispatchEvent(new Event('input', { bubbles: true })); input.focus(); } return; }
        const button = event.target.closest('[data-photo-view]');
        if (!button) return;
        const card = button.closest('[data-evidencia-id]');
        card.querySelectorAll('[data-photo-panel]').forEach(panel => panel.classList.toggle('hidden', panel.dataset.photoPanel !== button.dataset.photoView));
        card.querySelectorAll('[data-photo-view]').forEach(control => control.setAttribute('aria-pressed', String(control === button)));
    });

    on(root, 'submit', async event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.matches('[data-upload-form]')) {
            event.preventDefault();
            if (uploadBusy || root.dataset.analysisBusy === '1') return;
            const file = form.querySelector('[data-file-input]')?.files?.[0];
            if (!file) return;
            uploadBusy = true;
            const buttons = uploadForms.flatMap(f => [...f.querySelectorAll('[data-upload-button]')]);
            buttons.forEach(button => { button.disabled = true; });
            const progress = form.querySelector('[data-upload-progress]');
            if (progress) { progress.textContent = 'Preparando fotografía…'; progress.classList.remove('hidden'); }
            let automaticAnalysis = null;
            try {
                const optimized = await optimizeImage(file);
                if (optimized.size > MAX_SERVER_BYTES) throw new Error('La imagen supera el límite de 3 MiB.');
                const payload = new FormData(form);
                payload.set('foto', optimized, optimized.name);
                if (progress) progress.textContent = `Subiendo ${readableBytes(optimized.size)}…`;
                notify('Guardando evidencia; no cierres esta pestaña.', 'info');
                const result = await sendForm(form, payload);
                replaceEvidence(result, true);
                automaticAnalysis = [...list.querySelectorAll('[data-evidencia-id]')]
                    .find(card => card.dataset.evidenciaId === String(result.evidencia_id))?.querySelector('[data-ia-form]');
                form.reset();
                const pending = result.progreso?.pendientes?.[0];
                const fruit = form.elements.numero_fruto;
                if (fruit) fruit.value = pending?.numero || 1;
                const next = root.querySelector('[data-fruit-next]');
                if (next) { next.disabled = !pending; next.dataset.fruitPick = pending?.numero || ''; }
                clearPreview(form);
                notify(result.message || 'Fotografía guardada.');
                if (progress) progress.textContent = 'Fotografía guardada correctamente.';
            } catch (error) {
                if (error.name === 'AbortError' || !root.isConnected) return;
                notify(error.message || 'No se pudo guardar la fotografía.', 'error');
                if (progress) progress.textContent = 'Revisa el mensaje y vuelve a intentarlo.';
            } finally {
                uploadBusy = false;
                buttons.forEach(button => { button.disabled = false; });
            }
            if (automaticAnalysis?.isConnected && root.dataset.analysisBusy !== '1') automaticAnalysis.requestSubmit();
            return;
        }

        if (form.matches('[data-ia-form]')) {
            event.preventDefault();
            const button = form.querySelector('button[type="submit"]');
            if (!button || button.disabled) return;
            if (root.dataset.analysisBusy === '1') return;
            root.dataset.analysisBusy = '1';
            const label = button.textContent;
            const analysisButtons = [...root.querySelectorAll('[data-ia-form] button[type="submit"]')];
            const uploadButtons = [...root.querySelectorAll('[data-upload-button]')];
            const fileInputs = [...root.querySelectorAll('[data-file-input]')];
            const previousDisabled = new Map([...analysisButtons, ...uploadButtons, ...fileInputs].map(control => [control, control.disabled]));
            analysisButtons.forEach(control => { control.disabled = true; });
            uploadButtons.forEach(control => { control.disabled = true; });
            fileInputs.forEach(control => { control.disabled = true; });
            button.disabled = true;
            button.textContent = 'Analizando… puede tardar unos minutos';
            button.classList.add('animate-pulse');
            notify('Solicitando análisis a AgroCloud AI. La fotografía se procesa en el servidor.', 'info');
            try {
                const result = await sendForm(form, new FormData(form));
                replaceEvidence(result);
                notify(result.message || 'Análisis completado.');
            } catch (error) {
                if (error.name === 'AbortError' || !root.isConnected) return;
                if (error.responseData?.html) replaceEvidence(error.responseData);
                notify(error.message || 'Fallo el análisis. Puedes reintentar.', 'error');
                if (button.isConnected) {
                    button.disabled = false;
                    button.textContent = label;
                    button.classList.remove('animate-pulse');
                }
            } finally {
                previousDisabled.forEach((disabled, control) => { if (control.isConnected) control.disabled = disabled; });
                root.dataset.analysisBusy = '0';
            }
        }
    });
    };
    window.agrocloudInitEvidence = initialize;
    document.addEventListener('livewire:navigated', initialize);
    initialize();
})();
