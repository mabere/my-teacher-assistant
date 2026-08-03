document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('[data-app-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const closeButton = document.querySelector('[data-sidebar-close]');

    if (!sidebar || !backdrop || !toggle) {
        return;
    }

    const closeSidebar = () => {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
        backdrop.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        toggle.setAttribute('aria-expanded', 'false');
    };

    const openSidebar = () => {
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
        backdrop.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        toggle.setAttribute('aria-expanded', 'true');
    };

    toggle.addEventListener('click', openSidebar);
    closeButton?.addEventListener('click', closeSidebar);
    backdrop.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSidebar();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            closeSidebar();
        }
    });
});
