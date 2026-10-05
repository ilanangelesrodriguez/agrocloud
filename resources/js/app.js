document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('app-sidebar');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    if (!sidebar || !toggle || !backdrop) {
        return;
    }

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
});
