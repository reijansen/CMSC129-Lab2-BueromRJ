import './bootstrap';

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
    document.addEventListener('DOMContentLoaded', setupPasswordToggles);
} else {
    setupPasswordToggles();
}
