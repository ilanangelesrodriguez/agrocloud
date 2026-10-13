/**
 * Captura y análisis IA sin recargas: mejora progresiva sobre los formularios Laravel.
 * No incluye claves ni hace peticiones desde el navegador a FastAPI.
 */
(() => {
    'use strict';
    if (window.agrocloudInitEvidence) { window.agrocloudInitEvidence(); return; }
    const initialize = () => {

    const root = document.querySelector('[data-evidence-workspace]');
    if (!root || root.dataset.inspectionReady === 'true') return;
    root.dataset.inspectionReady = 'true';

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
            method: 'POST',
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
        if (typeof data.evidencias_count === 'number' && total) {
            total.textContent = String(data.evidencias_count);
        }
        if (analyzed) analyzed.textContent = String(list.querySelectorAll('[data-evidencia-id][data-analyzed="1"]').length);
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
        form.querySelector('[data-file-input]')?.addEventListener('change', () => {
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

    root.addEventListener('submit', async event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.matches('[data-upload-form]')) {
            event.preventDefault();
            if (uploadBusy) return;
            const file = form.querySelector('[data-file-input]')?.files?.[0];
            if (!file) return;
            uploadBusy = true;
            const buttons = uploadForms.flatMap(f => [...f.querySelectorAll('[data-upload-button]')]);
            buttons.forEach(button => { button.disabled = true; });
            const progress = form.querySelector('[data-upload-progress]');
            if (progress) { progress.textContent = 'Preparando fotografía…'; progress.classList.remove('hidden'); }
            try {
                const optimized = await optimizeImage(file);
                if (optimized.size > MAX_SERVER_BYTES) throw new Error('La imagen supera el límite de 3 MiB.');
                const payload = new FormData(form);
                payload.set('foto', optimized, optimized.name);
                if (progress) progress.textContent = `Subiendo ${readableBytes(optimized.size)}…`;
                notify('Guardando evidencia; no cierres esta pestaña.', 'info');
                const result = await sendForm(form, payload);
                replaceEvidence(result, true);
                form.reset();
                clearPreview(form);
                notify(result.message || 'Fotografía guardada.');
                if (progress) progress.textContent = 'Fotografía guardada correctamente.';
            } catch (error) {
                notify(error.message || 'No se pudo guardar la fotografía.', 'error');
                if (progress) progress.textContent = 'Revisa el mensaje y vuelve a intentarlo.';
            } finally {
                uploadBusy = false;
                buttons.forEach(button => { button.disabled = false; });
            }
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
                if (error.responseData?.html) replaceEvidence(error.responseData);
                notify(error.message || 'Fallo el análisis. Puedes reintentar.', 'error');
                if (button.isConnected) {
                    button.disabled = false;
                    button.textContent = label;
                    button.classList.remove('animate-pulse');
                }
            } finally {
                analysisButtons.forEach(control => { control.disabled = false; });
                uploadButtons.forEach(control => { control.disabled = false; });
                fileInputs.forEach(control => { control.disabled = false; });
                root.dataset.analysisBusy = '0';
            }
        }
    });
    };
    window.agrocloudInitEvidence = initialize;
    document.addEventListener('livewire:navigated', initialize);
    initialize();
})();
