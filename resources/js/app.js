document.addEventListener('DOMContentLoaded', () => {
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

        toggle.addEventListener('click', () => sidebarOpen ? closeSidebar(true) : openSidebar());
        closeButton?.addEventListener('click', () => closeSidebar(true));
        backdrop.addEventListener('click', () => closeSidebar(true));

        // El menú nunca debe quedarse abierto con overlay al pasar a escritorio.
        const handleBreakpoint = () => closeSidebar(false);
        desktop.addEventListener('change', handleBreakpoint);

        sidebar.querySelectorAll('nav a').forEach((link) => {
            link.addEventListener('click', () => closeSidebar(false));
        });

        document.addEventListener('keydown', (event) => {
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
        collapseButton.addEventListener('click', () => {
            collapsed = !collapsed;
            applyCollapsed();
            try { window.localStorage.setItem(storageKey, String(collapsed)); } catch { /* opcional */ }
        });
    }

    if (userMenu && userMenuToggle && userMenuPanel) {
        userMenuToggle.addEventListener('click', (event) => {
            event.stopPropagation();
            const willOpen = userMenuPanel.classList.contains('hidden');
            userMenuPanel.classList.toggle('hidden');
            userMenuToggle.setAttribute('aria-expanded', String(willOpen));
        });
        document.addEventListener('click', (event) => {
            if (!userMenu.contains(event.target)) closeUserMenu();
        });
        document.addEventListener('keydown', (event) => {
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
});

import './echo';
