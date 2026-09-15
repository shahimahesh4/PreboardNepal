import './bootstrap';

const initialiseStudentMenu = () => {
    const toggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('#student-sidebar');
    const backdrop = document.querySelector('.sidebar-backdrop');

    if (! toggle || ! sidebar || ! backdrop || toggle.dataset.ready === 'true') {
        return;
    }

    toggle.dataset.ready = 'true';
    const closeButton = sidebar.querySelector('.sidebar-close');
    const links = sidebar.querySelectorAll('a');
    const mobileLayout = window.matchMedia('(max-width: 900px)');

    const setOpen = (open) => {
        document.body.classList.toggle('student-menu-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.querySelector('.sr-only').textContent = open ? 'Close navigation menu' : 'Open navigation menu';
        sidebar.inert = mobileLayout.matches && ! open;

        if (open) {
            closeButton?.focus();
        } else if (document.activeElement === closeButton) {
            toggle.focus();
        }
    };

    toggle.addEventListener('click', () => setOpen(! document.body.classList.contains('student-menu-open')));
    closeButton?.addEventListener('click', () => setOpen(false));
    backdrop.addEventListener('click', () => setOpen(false));
    links.forEach((link) => link.addEventListener('click', () => setOpen(false)));
    mobileLayout.addEventListener('change', () => {
        document.body.classList.remove('student-menu-open');
        toggle.setAttribute('aria-expanded', 'false');
        sidebar.inert = mobileLayout.matches;
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && document.body.classList.contains('student-menu-open')) {
            setOpen(false);
        }
    });
    sidebar.inert = mobileLayout.matches;
};

document.addEventListener('DOMContentLoaded', initialiseStudentMenu);
document.addEventListener('livewire:navigated', initialiseStudentMenu);
