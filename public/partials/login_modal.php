<div id="loginModal" class="login-modal">
    <div class="login-modal-content">
        <span id="closeLogin" class="close">&times;</span>
        <h2>Đăng nhập</h2>

        <?php if (isset($error)) echo "<p style='color:red'>" . htmlspecialchars($error) . "</p>"; ?>

        <div id="loginError" class="login-error" style="color:red;margin-bottom:8px;display:none;"></div>

        <form method="POST" action="auth.php?action=login" class="login-modal-form" id="loginModalForm">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Mật khẩu</label>
            <input type="password" name="password" required>

            <div style="margin-top:12px; display:flex; gap:8px; align-items:center;">
                <button type="submit" class="btn-primary">Đăng nhập</button>
                <a href="register.php" class="link-muted">Chưa có tài khoản? Đăng ký</a>
            </div>
        </form>
    </div>

    <link rel="stylesheet" href="assets/css/login_modal.css">
    <script src="assets/js/login_modal.js"></script>
</div>
