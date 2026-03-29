import './bootstrap';

const setupRequiredFieldMarkers = () => {
    const requiredControls = document.querySelectorAll('input[required], select[required], textarea[required]');

    requiredControls.forEach((control) => {
        if (!control.id || control.type === 'hidden') {
            return;
        }

        const label = document.querySelector(`label[for="${control.id}"]`);
        if (!label || label.querySelector('[data-required-asterisk]')) {
            return;
        }

        const marker = document.createElement('span');
        marker.textContent = ' *';
        marker.className = 'text-red-600';
        marker.setAttribute('data-required-asterisk', 'true');
        marker.setAttribute('aria-hidden', 'true');

        label.appendChild(marker);
    });
};

const setupPasswordToggles = () => {
    const toggleGroups = document.querySelectorAll('[data-password-toggle]');

    toggleGroups.forEach((group) => {
        const input = group.querySelector('input[type="password"], input[type="text"]');
        const button = group.querySelector('[data-password-toggle-button]');
        const showIcon = group.querySelector('[data-icon-show]');
        const hideIcon = group.querySelector('[data-icon-hide]');

        if (!input || !button) {
            return;
        }

        button.addEventListener('click', () => {
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            showIcon?.classList.toggle('hidden', isHidden);
            hideIcon?.classList.toggle('hidden', !isHidden);
        });
    });
};

const setupDrawer = () => {
    const drawer = document.querySelector('[data-drawer]');
    const overlay = document.querySelector('[data-drawer-overlay]');
    const mainContent = document.querySelector('[data-main-content]');
    const openButtons = document.querySelectorAll('[data-drawer-open]');
    const closeButtons = document.querySelectorAll('[data-drawer-close]');
    const labelElements = document.querySelectorAll('[data-drawer-label]');
    const title = document.querySelector('[data-drawer-title]');
    const desktopQuery = window.matchMedia('(min-width: 1024px)');

    if (!drawer || !overlay) {
        return;
    }

    let isOpen = false;

    const applyDrawerState = () => {
        if (desktopQuery.matches) {
            drawer.classList.remove('-translate-x-full');
            drawer.classList.toggle('lg:w-72', isOpen);
            drawer.classList.toggle('lg:w-16', !isOpen);
            drawer.dataset.collapsed = (!isOpen).toString();
            mainContent?.classList.toggle('lg:ml-56', isOpen);
            title?.classList.toggle('lg:hidden', !isOpen);
            labelElements.forEach((label) => {
                label.classList.toggle('lg:hidden', !isOpen);
            });
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        } else {
            drawer.classList.remove('lg:w-72');
            drawer.classList.remove('lg:w-16');
            drawer.dataset.collapsed = 'false';
            mainContent?.classList.remove('lg:ml-56');
            drawer.classList.toggle('-translate-x-full', !isOpen);
            overlay.classList.toggle('hidden', !isOpen);
            document.body.classList.toggle('overflow-hidden', isOpen);
        }

        openButtons.forEach((button) => {
            button.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
            button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    };

    const openDrawer = () => {
        isOpen = true;
        applyDrawerState();
    };

    const closeDrawer = () => {
        isOpen = false;
        applyDrawerState();
    };

    const toggleDrawer = () => {
        isOpen = !isOpen;
        applyDrawerState();
    };

    openButtons.forEach((button) => {
        button.addEventListener('click', toggleDrawer);
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeDrawer);
    });

    overlay.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeDrawer();
        }
    });

    window.addEventListener('resize', () => {
        if (!desktopQuery.matches) {
            closeDrawer();
            return;
        }

        applyDrawerState();
    });
    applyDrawerState();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setupDrawer();
        setupRequiredFieldMarkers();
        setupPasswordToggles();
    });
} else {
    setupDrawer();
    setupRequiredFieldMarkers();
    setupPasswordToggles();
}
