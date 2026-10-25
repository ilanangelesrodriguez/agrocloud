import { showFieldError, clearFieldError } from './form-validation';

const requests = new WeakMap();
const completedNames = new WeakMap();
const filling = new WeakSet();
const automaticAttempts = new WeakMap();

function fillName(input, value) {
    filling.add(input);
    input.value = value;
    completedNames.set(input, value);
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
    clearFieldError(input);
    filling.delete(input);
}

async function lookup(panel, automatic = false) {
    const form = panel.closest('form');
    const input = form?.querySelector('[name="ruc"]');
    const name = form?.querySelector(`[name="${panel.dataset.nameField}"]`);
    const value = input ? input.value.trim() : panel.dataset.rucValue;
    const button = panel.querySelector('[data-ruc-query]');
    const label = panel.querySelector('[data-ruc-label]');
    const status = panel.querySelector('[data-ruc-status]');
    const apply = panel.querySelector('[data-ruc-apply]');
    if (button.disabled) return;
    if (!/^[0-9]{11}$/.test(value)) {
        if (automatic) return;
        status.textContent = 'Ingresa los 11 dígitos del RUC para continuar.';
        if (input) { showFieldError(input, status.textContent); input.focus(); }
        return;
    }
    if (automatic && automaticAttempts.get(panel) === value) return;
    automaticAttempts.set(panel, value);
    const controller = new AbortController();
    const request = { controller };
    requests.set(panel, request);
    apply.hidden = true;
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    label.textContent = 'Consultando…';
    status.textContent = 'Buscando la razón social asociada al RUC…';
    const timer = setTimeout(() => controller.abort(), 10000);
    try {
        const url = new URL(panel.dataset.endpoint, location.origin);
        url.searchParams.set('ruc', value);
        url.searchParams.set('razon_social', name?.value || panel.dataset.nameValue || '');
        const options = { headers: { Accept: 'application/json' }, signal: controller.signal };
        if (panel.dataset.method === 'POST') {
            options.method = 'POST';
            options.headers['Content-Type'] = 'application/json';
            options.headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content || '';
            options.body = JSON.stringify(Object.fromEntries(url.searchParams));
            url.search = '';
        }
        const response = await fetch(url, options);
        if (!panel.isConnected || requests.get(panel) !== request) return;
        if (response.status === 429) {
            status.textContent = 'La consulta alcanzó su límite temporal. Puedes completar los datos manualmente.';
            return;
        }
        if (!response.ok) throw new Error('lookup');
        const result = await response.json();
        if (!panel.isConnected || requests.get(panel) !== request || (input && input.value.trim() !== value)) return;
        request.result = result;
        if (input && result.resultado === 'no_encontrado') showFieldError(input, result.mensaje);
        else if (input) clearFieldError(input);
        let message = result.mensaje;
        if (name && result.razon_social) {
            const canComplete = !name.value.trim() || completedNames.get(name) === name.value;
            if (canComplete) {
                fillName(name, result.razon_social);
                message = 'Razón social completada. Revisa los datos antes de continuar.';
            } else if (name.value.trim() !== result.razon_social) {
                message = `Razón social encontrada: ${result.razon_social}. Puedes utilizarla o conservar el texto ingresado.`;
                apply.hidden = false;
            }
        }
        status.textContent = [message, result.estado && `Estado: ${result.estado}`, result.condicion && `Condición: ${result.condicion}`].filter(Boolean).join(' · ');
    } catch {
        if (panel.isConnected && requests.get(panel) === request) status.textContent = 'No pudimos completar la consulta. Ingresa la razón social manualmente o vuelve a intentarlo.';
    } finally {
        clearTimeout(timer);
        if (requests.get(panel) === request) {
            button.disabled = false;
            button.removeAttribute('aria-busy');
            label.textContent = 'Consultar de nuevo';
        }
    }
}

document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-ruc-query], [data-ruc-apply]');
    if (!button) return;
    const panel = button.closest('[data-ruc-lookup]');
    if (button.matches('[data-ruc-apply]')) {
        const result = requests.get(panel)?.result;
        const form = panel.closest('form');
        const input = form?.querySelector('[name="ruc"]');
        const name = form?.querySelector(`[name="${panel.dataset.nameField}"]`);
        if (name && result?.ruc === input?.value.trim()) {
            fillName(name, result.razon_social);
            button.hidden = true;
            panel.querySelector('[data-ruc-status]').textContent = 'Razón social actualizada con los datos encontrados.';
        }
        return;
    }
    return lookup(panel);
});

document.addEventListener('blur', ({ target }) => {
    if (!target.matches?.('[name="ruc"]')) return;
    target.closest('form')?.querySelectorAll('[data-ruc-lookup]').forEach((panel) => lookup(panel, true));
}, true);

document.addEventListener('input', ({ target }) => {
    if (filling.has(target) || !target.matches?.('[name="ruc"], [name="razon_social"], [name="nombre_razon_social"]')) return;
    target.closest('form')?.querySelectorAll('[data-ruc-lookup]').forEach((panel) => {
        requests.get(panel)?.controller.abort();
        requests.delete(panel);
        const button = panel.querySelector('[data-ruc-query]');
        button.disabled = false;
        button.removeAttribute('aria-busy');
        panel.querySelector('[data-ruc-label]').textContent = 'Completar con RUC';
        panel.querySelector('[data-ruc-apply]').hidden = true;
        panel.querySelector('[data-ruc-status]').textContent = target.name === 'ruc'
            ? 'La razón social se completa al terminar de ingresar el RUC.'
            : 'Puedes consultar el RUC para contrastar la razón social ingresada.';
    });
});
