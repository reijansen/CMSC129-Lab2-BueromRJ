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

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setupRequiredFieldMarkers();
        setupPasswordToggles();
    });
} else {
    setupRequiredFieldMarkers();
    setupPasswordToggles();
}
