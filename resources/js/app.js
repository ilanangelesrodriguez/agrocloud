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

    const empresaId = document.querySelector('meta[name="empresa-id"]')?.content;

    if (empresaId && window.Echo) {
        window.Echo.private(`empresa.${empresaId}.security`)
            .listen('.security.updated', (event) => {
                window.dispatchEvent(new CustomEvent('agrocloud:security-updated', { detail: event }));
            });
    }
});

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
