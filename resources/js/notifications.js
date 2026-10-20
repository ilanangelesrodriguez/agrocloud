/**
 * Notifications: private Echo channel + HTTP polling fallback.
 * One global listener set, so Livewire Navigate does not duplicate subscriptions.
 */
let subscribed = null;
let timer = null;
let fetching = false;

const root = () => document.querySelector('[data-notifications-root]');
const iconCount = (count) => count > 99 ? '99+' : String(count);

function updateBadge(container, count) {
    const badge = container.querySelector('[data-notifications-badge]');
    if (!badge) return;
    badge.textContent = iconCount(count);
    badge.classList.toggle('hidden', count === 0);
    container.querySelector('[data-notifications-toggle]')?.setAttribute('aria-label', count > 0 ? `${count} notificaciones sin leer` : 'Abrir notificaciones');
}

function renderRows(container, notifications) {
    const list = container.querySelector('[data-notifications-list]');
    if (!list) return;
    list.replaceChildren();
    if (!notifications.length) {
        const empty = document.createElement('p');
        empty.className = 'px-3 py-6 text-center text-sm text-slate-500';
        empty.textContent = 'No tienes notificaciones todavía.';
        list.append(empty);
        return;
    }
    notifications.forEach((n) => {
        const link = document.createElement('a');
        link.href = n.url;
        link.className = 'block rounded-xl p-3 transition hover:bg-slate-100 dark:hover:bg-slate-800 ' + (n.leida ? 'opacity-75' : 'bg-brand-50/50 dark:bg-brand-900/20');
        link.setAttribute('data-notification-item', '');
        const heading = document.createElement('div');
        heading.className = 'flex flex-wrap items-center justify-between gap-2';
        const title = document.createElement('strong');
        title.className = 'min-w-0 break-words text-sm text-slate-900 dark:text-slate-100';
        title.textContent = n.titulo;
        const stamp = document.createElement('span');
        stamp.className = 'text-xs text-slate-500';
        stamp.textContent = n.fecha || '';
        heading.append(title, stamp);
        const desc = document.createElement('p');
        desc.className = 'break-words mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-300';
        desc.textContent = n.mensaje;
        link.append(heading, desc);
        list.append(link);
    });
}

async function refresh() {
    const container = root();
    if (!container || fetching || document.visibilityState === 'hidden') return;
    fetching = true;
    try {
        const response = await fetch(container.dataset.notificationsFeed, {
            credentials: 'same-origin',
            headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            cache: 'no-store',
        });
        if (!response.ok) return;
        const body = await response.json();
        const current = root();
        if (!current || current.dataset.notificationsUser !== container.dataset.notificationsUser) return;
        updateBadge(current, Number(body.no_leidas || 0));
        renderRows(current, body.notificaciones || []);
    } catch (error) {
        // El historial se mantiene en la base de datos; se reintenta en el siguiente ciclo.
        console.warn('No se pudo actualizar la bandeja de notificaciones.', error);
    } finally {
        fetching = false;
    }
}

function init() {
    const container = root();
    if (!container) {
        if (subscribed && window.Echo) window.Echo.leave(subscribed);
        subscribed = null;
        return;
    }
    const companyId = container.dataset.notificationsCompany;
    const userId = container.dataset.notificationsUser;
    const channel = userId ? (companyId ? `empresa.${companyId}.usuario.${userId}.notifications` : `plataforma.usuario.${userId}.notifications`) : null;
    if (window.Echo && subscribed !== channel) {
        if (subscribed) window.Echo.leave(subscribed);
        subscribed = channel;
        if (channel) window.Echo.private(channel).listen('.notifications.changed', refresh);
    }
    if (!timer) timer = window.setInterval(refresh, 45000);
    refresh();
}

document.addEventListener('click', (event) => {
    const container = root();
    if (!container) return;
    const toggle = event.target.closest('[data-notifications-toggle]');
    const panel = container.querySelector('[data-notifications-panel]');
    if (event.target.closest('[data-notifications-close]')) {
        panel?.classList.add('hidden');
        const button = container.querySelector('[data-notifications-toggle]');
        button?.setAttribute('aria-expanded', 'false');
        button?.focus();
    } else if (toggle && container.contains(toggle)) {
        const open = panel?.classList.toggle('hidden') === false;
        toggle.setAttribute('aria-expanded', String(open));
        if (open) refresh();
    } else if (!container.contains(event.target)) {
        panel?.classList.add('hidden');
        container.querySelector('[data-notifications-toggle]')?.setAttribute('aria-expanded', 'false');
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        const panel = root()?.querySelector('[data-notifications-panel]');
        const wasOpen = panel && !panel.classList.contains('hidden');
        panel?.classList.add('hidden');
        if (wasOpen) root()?.querySelector('[data-notifications-toggle]')?.focus();
        root()?.querySelector('[data-notifications-toggle]')?.setAttribute('aria-expanded', 'false');
    }
});
document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') refresh(); });
window.addEventListener('focus', refresh);
document.addEventListener('DOMContentLoaded', init);
document.addEventListener('livewire:navigated', init);
if (document.readyState !== 'loading') init();
