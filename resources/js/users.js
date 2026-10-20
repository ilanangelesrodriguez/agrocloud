import { showToast } from './feedback';

let controller;
let trigger;
let photoUrl;
const drawer = () => document.querySelector('[data-user-drawer]');
const closeActions = () => document.querySelectorAll('[data-user-actions][open]').forEach((menu) => { menu.open = false; });
function cleanup() {
    controller?.abort();
    controller = null;
    if (photoUrl) URL.revokeObjectURL(photoUrl);
    photoUrl = null;
}
function closeDrawer() {
    const panel = drawer();
    if (panel?.querySelector('form[data-ajax-submitting="true"]')) return;
    panel?.close();
}

document.addEventListener('click', async (event) => {
    if (event.target.closest('[data-user-drawer-close]')) { closeDrawer(); return; }
    const link = event.target.closest('a[data-user-drawer-link]');
    if (!link || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
    const panel = drawer();
    if (!panel) return;
    event.preventDefault();
    closeActions();
    cleanup();
    trigger = link.closest('[data-user-actions]')?.querySelector('summary') || link;
    controller = new AbortController();
    const current = controller;
    const content = panel.querySelector('[data-user-drawer-content]');
    panel.querySelector('#user-drawer-title').textContent = link.dataset.userDrawerTitle || 'Usuario';
    content.textContent = 'Cargando información…';
    content.setAttribute('aria-busy', 'true');
    panel.showModal();
    try {
        const response = await fetch(link.href, { credentials: 'same-origin', headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' }, signal: current.signal });
        if (response.redirected && new URL(response.url).pathname.includes('/login')) {
            panel.close();
            window.Livewire.navigate(response.url);
            return;
        }
        if (!response.ok) throw new Error(response.status === 403 ? 'No tienes permiso para abrir esta opción.' : 'No se pudo cargar el usuario. Vuelve a intentarlo.');
        const html = new DOMParser().parseFromString(await response.text(), 'text/html');
        const section = html.querySelector('form[data-user-editor-form]') || html.querySelector('[data-user-detail]');
        if (!section) throw new Error('No se pudo abrir la información del usuario.');
        section.querySelectorAll('script').forEach((script) => script.remove());
        if (section.matches('form')) {
            section.dataset.userDrawerForm = '';
            section.dataset.userStoredPhoto = section.querySelector('[data-profile-image]')?.getAttribute('src') || '';
            const accountState = section.querySelector('input[type="checkbox"][name="estado"]');
            section.dataset.userInitialActive = String(section.querySelector('input[name="_method"]')?.value === 'PUT' && Boolean(accountState?.checked));
            section.querySelectorAll('[class*="xl:grid-cols-"]').forEach((grid) => { grid.classList.remove(...[...grid.classList].filter((name) => name.startsWith('xl:grid-cols-'))); });
            section.querySelectorAll('aside').forEach((aside) => aside.classList.remove('xl:sticky', 'xl:top-24'));
        }
        section.querySelectorAll('a').forEach((anchor) => {
            if (anchor.getAttribute('href') && new URL(anchor.href).pathname === '/usuarios') {
                anchor.removeAttribute('wire:navigate');
                anchor.removeAttribute('href');
                anchor.setAttribute('role', 'button');
                anchor.setAttribute('tabindex', '0');
                anchor.dataset.userDrawerClose = '';
            }
        });
        if (current !== controller) return;
        content.replaceChildren(section);
        section.querySelector('input:not([type="hidden"]):not([type="file"]), select, button')?.focus();
    } catch (error) {
        if (error.name === 'AbortError') return;
        content.textContent = error.message;
        showToast(error.message, 'error');
    } finally {
        if (current === controller) content.removeAttribute('aria-busy');
    }
});

document.addEventListener('click', (event) => {
    if (!event.target.closest('[data-user-actions]')) closeActions();
});
document.addEventListener('toggle', (event) => {
    if (!event.target.matches?.('[data-user-actions]') || !event.target.open) return;
    document.querySelectorAll('[data-user-actions][open]').forEach((menu) => { if (menu !== event.target) menu.open = false; });
}, true);
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeActions();
    if ((event.key === 'Enter' || event.key === ' ') && event.target.matches('a[data-user-drawer-close]')) { event.preventDefault(); closeDrawer(); }
});
document.addEventListener('cancel', (event) => {
    if (event.target.matches?.('[data-user-drawer]') && event.target.querySelector('form[data-ajax-submitting="true"]')) event.preventDefault();
}, true);
document.addEventListener('close', (event) => {
    if (!event.target.matches?.('[data-user-drawer]')) return;
    cleanup();
    event.target.querySelector('[data-user-drawer-content]')?.replaceChildren();
    trigger?.isConnected && trigger.focus();
}, true);
document.addEventListener('change', (event) => {
    const input = event.target;
    if (!input.matches('[data-user-drawer] [data-profile-input]')) return;
    const form = input.closest('form');
    const file = input.files?.[0];
    if (!file) return;
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) {
        input.value = '';
        if (photoUrl) URL.revokeObjectURL(photoUrl);
        photoUrl = null;
        const image = form.querySelector('[data-profile-image]');
        if (image) {
            if (form.dataset.userStoredPhoto) image.src = form.dataset.userStoredPhoto;
            else image.removeAttribute('src');
            image.classList.toggle('hidden', !form.dataset.userStoredPhoto);
        }
        form.querySelector('[data-profile-empty]')?.classList.toggle('hidden', Boolean(form.dataset.userStoredPhoto));
        showToast('Selecciona una imagen JPG, PNG o WEBP de hasta 2 MB.', 'error');
        return;
    }
    if (photoUrl) URL.revokeObjectURL(photoUrl);
    photoUrl = URL.createObjectURL(file);
    const image = form.querySelector('[data-profile-image]');
    if (image) { image.src = photoUrl; image.classList.remove('hidden'); }
    form.querySelector('[data-profile-empty]')?.classList.add('hidden');
});
document.addEventListener('user:saved', (event) => {
    drawer()?.close();
    closeActions();
    showToast(event.detail.message);
    window.Livewire?.dispatch('users-saved');
});
document.addEventListener('livewire:navigating', () => { cleanup(); drawer()?.close(); });

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!form.matches('[data-user-drawer-form]')) return;
    const state = form.querySelector('input[type="checkbox"][name="estado"]');
    if (form.dataset.userInitialActive === 'true' && state && !state.checked) {
        form.dataset.confirm = 'Esta persona dejará de tener acceso a la plataforma. Sus registros se conservarán. ¿Quieres guardar la cuenta como inactiva?';
        form.dataset.confirmTitle = '¿Desactivar esta cuenta?';
        form.dataset.confirmLabel = 'Guardar y desactivar';
    } else {
        delete form.dataset.confirm;
        delete form.dataset.confirmTitle;
        delete form.dataset.confirmLabel;
    }
}, true);
