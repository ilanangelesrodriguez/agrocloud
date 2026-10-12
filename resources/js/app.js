import { showToast, collectFlashToasts, queueToasts, confirmForm } from './feedback';
import './search';
import './masters';
import './master-dialog';

let appListeners;
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
    if (empresaId && window.Echo) {
        window.Echo.private(`empresa.${empresaId}.security`)
            .listen('.security.updated', (event) => {
                window.dispatchEvent(new CustomEvent('agrocloud:security-updated', { detail: event }));
            });
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
        const method = (form.method || 'GET').toUpperCase();
        const formData = new FormData(form);
        if (event.submitter?.name) formData.append(event.submitter.name, event.submitter.value);
        const requestUrl = new URL(form.action, window.location.href);
        const requestOptions = {
            method,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json, text/html',
                'X-Requested-With': 'XMLHttpRequest',
            },
        };

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
            form.querySelector('[data-form-errors]')?.remove();
            const errors = document.createElement('div');
            errors.dataset.formErrors = '';
            errors.setAttribute('role', 'alert');
            errors.tabIndex = -1;
            errors.className = 'rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200';
            Object.values(result.errors || { message: [result.message] }).flat().forEach((message) => {
                const item = document.createElement('p');
                item.textContent = message;
                errors.append(item);
            });
            form.prepend(errors);
            errors.focus();
            return;
        }
        if (!response.ok) {
            throw new Error(`El servidor respondió con ${response.status}.`);
        }

        if (typeof window.Livewire?.navigate !== 'function') {
            throw new Error('Livewire Navigate no está disponible.');
        }
        if (response.headers.get('content-type')?.includes('application/json')) {
            const result = await response.json();
            const destination = new URL(result.redirect, window.location.href);
            if (!result.redirect || destination.origin !== window.location.origin) throw new Error('Destino inválido.');
            navigating = true;
            window.Livewire.navigate(destination.href);
        } else {
            const html = new DOMParser().parseFromString(await response.text(), 'text/html');
            queueToasts(collectFlashToasts(html));
            navigating = true;
            window.Livewire.navigate(response.url);
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
import './theme';
