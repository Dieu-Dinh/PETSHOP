<?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
<link rel="stylesheet" href="assets/css/login.css">
<form method="POST" action="auth.php?action=login">
    <h2>Đăng nhập</h2>
    <label>Email</label><br>
    <input type="email" name="email" required><br>
    <label>Mật khẩu</label><br>
    <input type="password" name="password" required><br><br>
    <button type="submit">Đăng nhập</button>
    <a href="register.php">Chưa có tài khoản? Đăng ký</a>
</form>

