document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('app-sidebar');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    if (sidebar && toggle && backdrop) {
        const open = () => {
            sidebar.classList.remove('hidden');
            backdrop.classList.remove('hidden');
        };
        const close = () => {
            sidebar.classList.add('hidden');
            backdrop.classList.add('hidden');
        };
        toggle.addEventListener('click', open);
        backdrop.addEventListener('click', close);
    }

    const userMenu = document.querySelector('[data-user-menu]');
    const userMenuToggle = document.querySelector('[data-user-menu-toggle]');
    const userMenuPanel = document.querySelector('[data-user-menu-panel]');

    if (userMenu && userMenuToggle && userMenuPanel) {
        const closeMenu = () => {
            userMenuPanel.classList.add('hidden');
            userMenuToggle.setAttribute('aria-expanded', 'false');
        };

        userMenuToggle.addEventListener('click', (event) => {
            event.stopPropagation();
            const willOpen = userMenuPanel.classList.contains('hidden');
            userMenuPanel.classList.toggle('hidden');
            userMenuToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });

        document.addEventListener('click', (event) => {
            if (!userMenu.contains(event.target)) closeMenu();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeMenu();
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
