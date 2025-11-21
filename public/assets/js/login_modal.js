document.addEventListener('DOMContentLoaded', () => {
    const openBtn = document.getElementById('btnOpenLogin');
    const modal = document.getElementById('loginModal');
    const closeBtn = document.getElementById('closeLogin');

    if (!modal) return;

    if (openBtn) {
        openBtn.addEventListener('click', (e) => {
            // preserve fallback: if JS present, prevent navigation and show modal
            e.preventDefault();
            modal.style.display = 'block';
            // focus first input for convenience
            const first = modal.querySelector('input[name="email"]');
            if (first) first.focus();
        });
    }

    if (closeBtn) closeBtn.addEventListener('click', () => modal.style.display = 'none');

    window.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });
});
