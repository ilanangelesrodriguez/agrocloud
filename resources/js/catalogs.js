import { showToast } from './feedback';

const initialized = new WeakSet();
const releases = new Map();
let request;
let trigger;
const modal = () => document.querySelector('[data-catalog-editor]');

function initializeForm(form) {
    if (initialized.has(form)) return;
    initialized.add(form);
    const input = form.querySelector('[name="nombre"]');
    const parent = form.querySelector('[name="parent_id"]');
    const feedback = form.querySelector('[data-catalog-name-feedback]');
    if (!input || !feedback) return;
    let timer;
    let checking;
    const reset = () => { checking?.abort(); input.setCustomValidity(''); feedback.textContent = ''; };
    const check = () => {
        reset();
        if (parent) input.disabled = !parent.value;
        clearTimeout(timer);
        if (!input.value.trim() || input.disabled) return;
        timer = window.setTimeout(async () => {
            checking = new AbortController();
            const current = checking;
            const url = new URL(form.dataset.nameCheckUrl, location.href);
            url.searchParams.set('nombre', input.value.trim());
            if (parent?.value) url.searchParams.set('parent_id', parent.value);
            if (form.dataset.itemId) url.searchParams.set('item_id', form.dataset.itemId);
            try {
                const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' }, signal: current.signal });
                if (!response.ok) return;
                const result = await response.json();
                if (current !== checking || !form.isConnected) return;
                const message = result.exists ? 'Ya existe una opción con este nombre en el mismo alcance. Revisa el catálogo antes de continuar.' : '';
                input.setCustomValidity(message);
                feedback.textContent = message;
            } catch { /* La validación de guardado del servidor sigue siendo obligatoria. */ }
        }, 400);
    };
    input.addEventListener('input', check);
    parent?.addEventListener('change', check);
    if (parent) input.disabled = !parent.value;
    releases.set(form, () => { clearTimeout(timer); reset(); });
}
const initialize = () => document.querySelectorAll('[data-catalog-form]').forEach(initializeForm);

document.addEventListener('click', async (event) => {
    const panel = modal();
    if (event.target.closest('[data-catalog-close]')) {
        event.preventDefault();
        if (!panel?.querySelector('form[data-ajax-submitting="true"]')) panel?.close();
        return;
    }
    const link = event.target.closest('a[data-catalog-editor-link]');
    if (!link || !panel || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    event.preventDefault();
    request?.abort();
    request = new AbortController();
    const current = request;
    trigger = link;
    panel.querySelector('#catalog-editor-title').textContent = link.dataset.catalogTitle || 'Configurar opción';
    const content = panel.querySelector('[data-catalog-content]');
    content.textContent = 'Cargando formulario…';
    panel.showModal();
    try {
        const response = await fetch(link.href, { credentials: 'same-origin', headers: { Accept: 'text/html' }, signal: current.signal });
        if (response.redirected && new URL(response.url).pathname.includes('/login')) { panel.close(); window.Livewire.navigate(response.url); return; }
        if (!response.ok) throw new Error('No se pudo abrir el formulario. Comprueba tus permisos e inténtalo nuevamente.');
        const html = new DOMParser().parseFromString(await response.text(), 'text/html');
        const form = html.querySelector('[data-catalog-form]');
        if (!form) throw new Error('No se pudo cargar el formulario.');
        form.dataset.catalogModalForm = '';
        form.querySelectorAll('a').forEach((link) => {
            if (link.getAttribute('href') && new URL(link.href).pathname === new URL(form.dataset.catalogReturn).pathname) {
                const button = document.createElement('button'); button.type = 'button'; button.dataset.catalogClose = ''; button.className = link.className; button.textContent = link.textContent; link.replaceWith(button);
            }
        });
        if (current !== request) return;
        content.replaceChildren(form);
        initializeForm(form);
        form.querySelector('select, input:not([type="hidden"])')?.focus();
    } catch (error) {
        if (error.name === 'AbortError') return;
        content.textContent = error.message;
        showToast(error.message, 'error');
    }
});
document.addEventListener('cancel', (event) => { if (event.target.matches?.('[data-catalog-editor]') && event.target.querySelector('form[data-ajax-submitting="true"]')) event.preventDefault(); }, true);
document.addEventListener('close', (event) => {
    if (!event.target.matches?.('[data-catalog-editor]')) return;
    request?.abort(); request = null;
    const form = event.target.querySelector('form');
    releases.get(form)?.(); releases.delete(form);
    event.target.querySelector('[data-catalog-content]').replaceChildren();
    if (trigger?.isConnected) trigger.focus();
}, true);
document.addEventListener('catalog:saved', (event) => { modal()?.close(); showToast(event.detail.message); window.Livewire?.dispatch('masters-saved'); });
document.addEventListener('DOMContentLoaded', initialize);
document.addEventListener('livewire:navigated', initialize);
document.addEventListener('livewire:navigating', () => { request?.abort(); modal()?.close(); releases.forEach((release) => release()); releases.clear(); });
if (document.readyState !== 'loading') initialize();
