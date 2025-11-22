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
    
    // AJAX submit for modal login to show inline errors instead of redirect
    const loginForm = document.getElementById('loginModalForm');
    const loginError = document.getElementById('loginError');
    if (loginForm) {
        loginForm.addEventListener('submit', async (ev) => {
            ev.preventDefault();
            if (loginError) { loginError.style.display = 'none'; loginError.textContent = ''; }
            const submitBtn = loginForm.querySelector('button[type="submit"]');
            if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Đang đăng nhập...'; }

            const formData = new FormData(loginForm);
            try {
                const res = await fetch(loginForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                // Try parse JSON — server will return JSON for AJAX requests
                const json = await res.json().catch(() => null);
                if (json && json.success) {
                    // success: if server suggests redirect (e.g. admin), follow it; otherwise reload
                    modal.style.display = 'none';
                    if (json.redirect) {
                        window.location.href = json.redirect;
                        return;
                    }
                    window.location.reload();
                    return;
                }

                // show error message returned by server or fallback
                const message = (json && json.message) ? json.message : 'Email hoặc mật khẩu không đúng.';
                if (loginError) { loginError.textContent = message; loginError.style.display = 'block'; }
            } catch (err) {
                console.error('Login request failed', err);
                if (loginError) { loginError.textContent = 'Lỗi kết nối. Vui lòng thử lại.'; loginError.style.display = 'block'; }
            } finally {
                if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Đăng nhập'; }
            }
        });
    }
});
