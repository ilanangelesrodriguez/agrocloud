import { showToast } from './feedback';

let request;
let trigger;
let dirty = false;
let scrollStyle;
let destination;
function discardWarning(visible) {
    const drawer = panel();
    if (!drawer) return;
    drawer.querySelector('[data-workspace-discard]').hidden = !visible;
    drawer.querySelector('[data-workspace-content]').inert = visible;
    drawer.querySelector('header').inert = visible;
}
const panel = () => document.querySelector('[data-workspace-drawer]');
const busy = () => Boolean(panel()?.querySelector('[data-ajax-submitting="true"]'));
function close(force = false) {
    const drawer = panel();
    if (!drawer?.open || (!force && busy())) return;
    if (!force && dirty) {
        discardWarning(true);
        drawer.querySelector('[data-workspace-keep]').focus();
        return;
    }
    dirty = false;
    request?.abort();
    drawer.close();
    drawer.querySelector('[data-workspace-content]').replaceChildren();
    discardWarning(false);
    document.documentElement.style.overflow = scrollStyle || '';
    if (trigger?.isConnected) trigger.focus();
}
async function open(link) {
    const drawer = panel();
    if (!drawer || drawer.open) return;
    trigger = link;
    destination = null;
    dirty = false;
    request?.abort();
    request = new AbortController();
    const current = request;
    const content = drawer.querySelector('[data-workspace-content]');
    drawer.querySelector('#workspace-drawer-title').textContent = link.dataset.workspaceTitle || 'Revisar información';
    content.textContent = 'Cargando información…';
    content.setAttribute('aria-busy', 'true');
    scrollStyle = document.documentElement.style.overflow;
    document.documentElement.style.overflow = 'hidden';
    drawer.showModal();
    try {
        const response = await fetch(link.href, { credentials: 'same-origin', signal: current.signal, headers: { Accept: 'text/html', 'X-Workspace-Drawer': 'true' } });
        if (response.redirected && new URL(response.url).pathname.includes('/login')) { close(true); window.Livewire?.navigate(response.url); return; }
        if (!response.ok) throw new Error(response.status === 403 ? 'No tienes permiso para esta operación.' : 'No se pudo abrir la información. Actualiza el listado e inténtalo de nuevo.');
        if (!(response.headers.get('content-type') || '').includes('text/html')) throw new Error('No se pudo cargar el formulario.');
        const html = new DOMParser().parseFromString(await response.text(), 'text/html');
        html.querySelectorAll('script').forEach(script => script.remove());
        if (!drawer.open || current !== request) return;
        content.replaceChildren(...html.body.childNodes);
        window.agrocloudInitLocation?.();
        weightDifference(content.querySelector('[data-declared-weight]'));
        content.querySelector('input:not([type=hidden]), select, textarea, a, button')?.focus({ preventScroll: true });
    } catch (error) {
        if (error.name !== 'AbortError') { close(true); showToast(error.message, 'error'); }
    } finally { if (current === request) content.removeAttribute('aria-busy'); }
}
function weightDifference(form = document.querySelector('[data-declared-weight]')) {
    if (!form) return;
    const input = form.elements.namedItem('peso_recibido_kg');
    const declared = Number(form.dataset.declaredWeight);
    const valid = input.value !== '' && Number.isFinite(input.valueAsNumber);
    const difference = valid ? Math.round(input.valueAsNumber * 100) - Math.round(declared * 100) : 0;
    const changed = valid && difference !== 0;
    form.elements.namedItem('observaciones').required = changed;
    const label = form.querySelector('[data-weight-note-required]');
    if (label) label.hidden = !changed;
    const message = form.querySelector('[data-weight-difference]');
    message.textContent = valid ? `Diferencia: ${(difference / 100).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} kg. ${difference !== 0 ? 'Explica esta variación en las observaciones.' : 'El peso coincide con el declarado.'}` : 'Ingresa el peso recibido para comparar.';
    message.classList.toggle('text-amber-700', changed);
    message.classList.toggle('dark:text-amber-300', changed);
}
document.addEventListener('click', (event) => {
    if (event.target.closest('[data-workspace-close]')) { close(); return; }
    if (event.target.closest('[data-workspace-keep]')) { discardWarning(false); destination = null; panel().querySelector('[data-workspace-close]').focus(); return; }
    if (event.target.closest('[data-workspace-abandon]')) { const next = destination; destination = null; close(true); if (next) window.Livewire?.navigate(next); return; }
    const link = event.target.closest('a[data-workspace-link]');
    if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || !panel()) return;
    event.preventDefault();
    document.querySelectorAll('[data-producer-actions][open]').forEach(menu => { menu.open = false; });
    void open(link);
});
document.addEventListener('cancel', event => { if (event.target.matches('[data-workspace-drawer]')) { event.preventDefault(); close(); } }, true);
for (const type of ['input', 'change']) document.addEventListener(type, event => {
    const form = event.target.closest('form[data-declared-weight]');
    if (form) weightDifference(form);
    if (event.target.closest('[data-workspace-drawer]') && event.isTrusted) dirty = true;
});
document.addEventListener('workspace:saved', event => {
    const result = event.detail;
    if (panel()?.open && result.item && trigger?.dataset.workspaceTarget) {
        const select = document.getElementById(trigger.dataset.workspaceTarget);
        if (select) {
            let option = [...select.options].find(item => item.value === String(result.item.id));
            if (!option) { option = new Option(result.item.label, result.item.id); select.add(option); }
            option.textContent = result.item.label;
            select.value = String(result.item.id);
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
    if (event.target.closest('[data-workspace-drawer]')) close(true);
    document.querySelectorAll('[data-producer-actions][open]').forEach(menu => { menu.open = false; });
    showToast(result.message || 'Cambios guardados.');
    window.Livewire?.dispatch(`${result.module}-actualizados`);
});
document.addEventListener('livewire:navigate', event => {
    if (!panel()?.open) return;
    if (busy()) { event.preventDefault(); showToast('Espera a que termine el guardado.', 'info'); return; }
    if (dirty) { event.preventDefault(); destination = event.detail.url.toString(); close(); }
});
document.addEventListener('livewire:navigating', () => close(true));
window.addEventListener('beforeunload', event => { if (dirty && panel()?.open) { event.preventDefault(); event.returnValue = ''; } });

document.addEventListener('livewire:navigated', () => weightDifference());
if (document.readyState !== 'loading') weightDifference();
else document.addEventListener('DOMContentLoaded', () => weightDifference());
document.addEventListener('toggle', (event) => {
    const menu = event.target;
    if (!menu.matches?.('[data-producer-actions]') || !menu.open) return;
    document.querySelectorAll('[data-producer-actions][open]').forEach(other => { if (other !== menu) other.open = false; });
}, true);
document.addEventListener('click', event => {
    if (!event.target.closest('[data-producer-actions]')) document.querySelectorAll('[data-producer-actions][open]').forEach(menu => { menu.open = false; });
});
document.addEventListener('keydown', event => {
    if (event.key !== 'Escape' || panel()?.open) return;
    document.querySelectorAll('[data-producer-actions][open]').forEach(menu => { menu.open = false; menu.querySelector('summary')?.focus(); });
});
