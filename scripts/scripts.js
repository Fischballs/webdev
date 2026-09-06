/**
 * scripts.js
 * Miguel Gutib — shared front-end behavior
 */

// ---- Log In / Sign Up tab toggle (signin_signup.php) ----
document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('.mg-tab-btn, [data-tab-link]');
    const panels = document.querySelectorAll('.mg-tab-panel');
    if (!panels.length) return; // not on the login/signup page

    const activeClasses = ['bg-[var(--mg-btn)]', 'text-mg-cream-ink', 'border-mg-gold-line'];
    const inactiveClasses = ['text-mg-gold-soft', 'border-mg-line'];

    function showTab(name) {
        panels.forEach(p => p.classList.toggle('hidden', p.dataset.panel !== name));
        document.querySelectorAll('.mg-tab-btn').forEach(btn => {
            const isActive = btn.dataset.tab === name;
            activeClasses.forEach(c => btn.classList.toggle(c, isActive));
            inactiveClasses.forEach(c => btn.classList.toggle(c, !isActive));
        });
    }

    tabButtons.forEach(el => {
        el.addEventListener('click', () => {
            const name = el.dataset.tab || el.dataset.tabLink;
            showTab(name);
        });
    });
});
