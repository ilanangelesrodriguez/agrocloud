import { showToast, collectFlashToasts, queueToasts, confirmForm } from './feedback';

document.addEventListener('agrocloud:toast', (event) => {
    const { message, type } = event.detail || {};
    if (message) showToast(message, ['success', 'error', 'info'].includes(type) ? type : 'success');
});
import './search';
import './ruc-lookup';
import './masters';
import './master-dialog';
import './users';
import './catalogs';
import './workspace-drawer';
import './operational-workspace';
import './quality-decision';
import './image-viewer';

import { showFormErrors } from './form-validation';

let appListeners;
let securitySubscribedChannel = null;
const initializeApp = () => {
    appListeners?.abort();
    appListeners = new AbortController();
    const on = (target, name, handler) => target?.addEventListener(name, handler, { signal: appListeners.signal });
    const shell = document.querySelector('.agrocloud-shell');
    const sidebar = document.getElementById('app-sidebar');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const closeButton = document.querySelector('[data-sidebar-close]');
    const collapseButton = document.querySelector('[data-sidebar-collapse-toggle]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const desktop = window.matchMedia('(min-width: 1024px)');
    let sidebarOpen = false;

    const userMenu = document.querySelector('[data-user-menu]');
    const userMenuToggle = document.querySelector('[data-user-menu-toggle]');
    const userMenuPanel = document.querySelector('[data-user-menu-panel]');

    const closeUserMenu = (restoreFocus = false) => {
        if (!userMenuToggle || !userMenuPanel || userMenuPanel.classList.contains('hidden')) return;
        userMenuPanel.classList.add('hidden');
        userMenuToggle.setAttribute('aria-expanded', 'false');
        if (restoreFocus) userMenuToggle.focus();
    };

    if (sidebar && toggle && backdrop) {
        const closeSidebar = (restoreFocus = false) => {
            if (!sidebarOpen) return;
            sidebarOpen = false;
            sidebar.classList.remove('visible', 'translate-x-0');
            sidebar.classList.add('invisible', '-translate-x-full');
            backdrop.classList.remove('visible', 'opacity-100', 'pointer-events-auto');
            backdrop.classList.add('invisible', 'opacity-0', 'pointer-events-none');
            document.body.classList.remove('overflow-hidden');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Abrir menú de navegación');
            if (restoreFocus && !desktop.matches) toggle.focus();
        };

        const openSidebar = () => {
            if (desktop.matches || sidebarOpen) return;
            closeUserMenu();
            sidebarOpen = true;
            sidebar.classList.remove('invisible', '-translate-x-full');
            sidebar.classList.add('visible', 'translate-x-0');
            backdrop.classList.remove('invisible', 'opacity-0', 'pointer-events-none');
            backdrop.classList.add('visible', 'opacity-100', 'pointer-events-auto');
            document.body.classList.add('overflow-hidden');
            toggle.setAttribute('aria-expanded', 'true');
            toggle.setAttribute('aria-label', 'Cerrar menú de navegación');
            (closeButton || sidebar).focus();
        };

        on(toggle, 'click', () => sidebarOpen ? closeSidebar(true) : openSidebar());
        on(closeButton, 'click', () => closeSidebar(true));
        on(backdrop, 'click', () => closeSidebar(true));

        // El menú nunca debe quedarse abierto con overlay al pasar a escritorio.
        const handleBreakpoint = () => closeSidebar(false);
        on(desktop, 'change', handleBreakpoint);

        sidebar.querySelectorAll('nav a').forEach((link) => {
            on(link, 'click', () => closeSidebar(false));
        });

        on(document, 'keydown', (event) => {
            if (!sidebarOpen || desktop.matches) return;
            if (event.key === 'Escape') {
                event.preventDefault();
                closeSidebar(true);
                return;
            }
            if (event.key !== 'Tab') return;
            const focusable = [...sidebar.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])')]
                .filter((element) => element.getClientRects().length > 0);
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    }

    // El estado compacto se conserva al visitar otras páginas del panel.
    if (shell && collapseButton) {
        const storageKey = 'agrocloud.sidebar.collapsed';
        let collapsed = false;
        try { collapsed = window.localStorage.getItem(storageKey) === 'true'; } catch { /* navegador sin almacenamiento */ }

        const applyCollapsed = () => {
            shell.dataset.sidebarCollapsed = String(collapsed);
            collapseButton.setAttribute('aria-pressed', String(collapsed));
            const label = collapsed ? 'Expandir menú lateral' : 'Contraer menú lateral';
            collapseButton.setAttribute('aria-label', label);
            collapseButton.title = label;
        };

        applyCollapsed();
        on(collapseButton, 'click', () => {
            collapsed = !collapsed;
            applyCollapsed();
            try { window.localStorage.setItem(storageKey, String(collapsed)); } catch { /* opcional */ }
        });
    }

    if (userMenu && userMenuToggle && userMenuPanel) {
        on(userMenuToggle, 'click', (event) => {
            event.stopPropagation();
            const willOpen = userMenuPanel.classList.contains('hidden');
            userMenuPanel.classList.toggle('hidden');
            userMenuToggle.setAttribute('aria-expanded', String(willOpen));
        });
        on(document, 'click', (event) => {
            if (!userMenu.contains(event.target)) closeUserMenu();
        });
        on(document, 'keydown', (event) => {
            if (event.key === 'Escape') closeUserMenu(true);
        });
    }

    const empresaId = document.querySelector('meta[name="empresa-id"]')?.content;
    const securityChannel = empresaId ? `empresa.${empresaId}.security` : null;
    if (window.Echo && securitySubscribedChannel !== securityChannel) {
        if (securitySubscribedChannel) window.Echo.leave(securitySubscribedChannel);
        securitySubscribedChannel = securityChannel;
        if (securityChannel) {
            window.Echo.private(securityChannel).listen('.security.updated', (event) => {
                window.dispatchEvent(new CustomEvent('agrocloud:security-updated', { detail: event }));
            });
        }
    }
};

document.addEventListener('DOMContentLoaded', initializeApp);
document.addEventListener('livewire:navigated', initializeApp);
if (document.readyState !== 'loading') initializeApp();

document.addEventListener('submit', async (event) => {
    if (event.defaultPrevented) return;
    const form = event.target.closest('form[data-ajax-form], form[data-confirm]');
    if (!form) return;

    event.preventDefault();
    if (form.dataset.ajaxSubmitting === 'true') return;
    if (form.dataset.confirm && form.dataset.confirmed !== 'true') {
        confirmForm(form, event.submitter);
        return;
    }
    form.dataset.ajaxSubmitting = 'true';
    const buttons = [...form.querySelectorAll('button[type="submit"], input[type="submit"]')]
        .filter((button) => !button.disabled);
    buttons.forEach((button) => {
        button.disabled = true;
    });
    let navigating = false;

    try {
        const method = (event.submitter?.getAttribute('formmethod') || form.method || 'GET').toUpperCase();
        const formData = new FormData(form);
        if (event.submitter?.name) formData.set(event.submitter.name, event.submitter.value);
        const requestUrl = new URL(event.submitter?.getAttribute('formaction') || form.action, window.location.href);
        const requestOptions = {
            method,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json, text/html',
                'X-Requested-With': 'XMLHttpRequest',
            },
        };

        if (form.matches('[data-workspace-drawer-form], [data-workspace-inline-form]')) requestOptions.headers['X-Workspace-Drawer'] = 'true';
        if (form.matches('[data-user-drawer-form], [data-user-inline-form]') || form.closest('[data-user-drawer]')) requestOptions.headers['X-User-Module'] = 'true';
        if (form.matches('[data-catalog-modal-form], [data-catalog-inline-form]')) requestOptions.headers['X-Master-Module'] = 'true';

        if (method === 'GET') {
            formData.forEach((value, key) => {
                if (typeof value === 'string' && value !== '') requestUrl.searchParams.set(key, value);
            });
        } else {
            requestOptions.body = formData;
        }

        const response = await fetch(requestUrl, {
            ...requestOptions,
        });

        if (response.status === 422 && response.headers.get('content-type')?.includes('application/json')) {
            const result = await response.json();
            showFormErrors(form, result.errors || { message: [result.message || 'Revisa los datos ingresados.'] });
            return;
        }
        if (!response.ok) {
            throw new Error(`El servidor respondió con ${response.status}.`);
        }

        if (response.headers.get('content-type')?.includes('application/json')) {
            const result = await response.json();
            if (result.updated && form.matches('[data-access-form]')) {
                const document = new DOMParser().parseFromString(result.html, 'text/html');
                const updated = document.querySelector('[data-access-review]');
                const current = form.closest('[data-access-review]');
                if (!updated || !current) throw new Error('Expediente no disponible.');
                current.replaceWith(updated);
                updated.focus({ preventScroll: true });
                showToast(result.message, result.tone || 'success');
                return;
            }
            if (result.saved && form.matches('[data-workspace-drawer-form], [data-workspace-inline-form]')) {
                form.dispatchEvent(new CustomEvent('workspace:saved', { bubbles: true, detail: result }));
                return;
            }
            if (result.saved && form.matches('[data-catalog-modal-form], [data-catalog-inline-form]')) {
                form.dispatchEvent(new CustomEvent('catalog:saved', { bubbles: true, detail: result }));
                return;
            }
            if (result.saved && form.matches('[data-user-drawer-form], [data-user-inline-form]')) {
                form.dispatchEvent(new CustomEvent('user:saved', { bubbles: true, detail: result }));
                return;
            }
            const destination = new URL(result.redirect, window.location.href);
            if (!result.redirect || destination.origin !== window.location.origin) throw new Error('Destino inválido.');
            navigating = true;
            window.Livewire.navigate(destination.href);
        } else {
            if (typeof window.Livewire?.navigate !== 'function') {
                throw new Error('Livewire Navigate no está disponible.');
            }
            const html = new DOMParser().parseFromString(await response.text(), 'text/html');
            queueToasts(collectFlashToasts(html));
            navigating = true;
            const destination = new URL(response.url);
            if (requestUrl.hash) destination.hash = requestUrl.hash;
            window.Livewire.navigate(destination.href);
        }
    } catch (error) {
        navigating = false;
        console.error('No se pudo enviar el formulario sin recargar la página.', error);
        showToast('No se pudo completar la operación. Inténtalo nuevamente.', 'error');
    } finally {
        if (!navigating) {
            form.dataset.ajaxSubmitting = 'false';
            buttons.forEach((button) => { button.disabled = false; });
        }
    }
});

import './echo';
import './notifications'; // AGROCLOUD_NOTIFICATIONS_INSTALLED
document.addEventListener('submit', (event) => {
    const form = event.target.closest?.('form[data-report-filter]');
    if (!form || !window.Livewire || event.defaultPrevented) return;
    event.preventDefault();
    const data = new FormData(form);
    if (data.get('desde') > data.get('hasta')) {
        showFormErrors(form, { hasta: ['La fecha final debe ser igual o posterior a la inicial.'] });
        return;
    }
    const url = new URL(form.action);
    url.search = new URLSearchParams(data).toString();
    window.Livewire.navigate(url.href);
});
import './theme';

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-password-toggle]');
    if (!button) return;
    const input = document.getElementById(button.getAttribute('aria-controls'));
    if (!input) return;
    const visible = input.type === 'password';
    input.type = visible ? 'text' : 'password';
    button.querySelector('[data-password-icon-show]')?.classList.toggle('hidden', visible);
    button.querySelector('[data-password-icon-hide]')?.classList.toggle('hidden', !visible);
    button.setAttribute('aria-pressed', String(visible));
    button.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
    button.title = visible ? 'Ocultar contraseña' : 'Mostrar contraseña';
});
document.addEventListener('livewire:navigating', () => {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = document.getElementById(button.getAttribute('aria-controls'));
        if (input) input.type = 'password';
    });
});

// Broken company logos fall back to the application mark, without a broken-image icon.
document.addEventListener('error', (event) => {
    const image = event.target;
    if (!image.matches?.('[data-company-logo]') || image.dataset.logoFailed) return;
    image.dataset.logoFailed = 'true';
    image.src = image.dataset.logoFallback;
    image.alt = 'AgroCloud AI';
}, true);

// El menú público vuelve a cerrarse al elegir una sección o pulsar Escape.
document.addEventListener('click', (event) => {
    const link = event.target.closest?.('[data-public-menu] a');
    link?.closest('[data-public-menu]')?.removeAttribute('open');
});
document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    const menu = document.querySelector('[data-public-menu][open]');
    if (!menu) return;
    menu.removeAttribute('open');
    menu.querySelector('summary')?.focus();
});
