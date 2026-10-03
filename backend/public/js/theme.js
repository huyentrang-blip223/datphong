document.addEventListener('DOMContentLoaded', function () {
    const button = document.querySelector('[data-theme-toggle]');
    if (!button) return;

    const applyLabel = function () {
        button.textContent = document.documentElement.dataset.theme === 'forest' ? 'Forest' : 'Light';
    };

    button.addEventListener('click', function () {
        const next = document.documentElement.dataset.theme === 'forest' ? 'light' : 'forest';
        document.documentElement.dataset.theme = next;
        localStorage.setItem('dt07-theme', next);
        applyLabel();
    });

    applyLabel();
});
