<?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
<link rel="stylesheet" href="assets/css/register.css">
<form method="POST" action="auth.php?action=register">
    <h2>Đăng ký</h2>
    <label>Họ</label><br>
    <input type="text" name="last_name" required><br>
    <label>Tên</label><br>
    <input type="text" name="first_name" required><br>
    <label>Email</label><br>
    <input type="email" name="email" required><br>
    <label>Mật khẩu</label><br>
    <input type="password" name="password" required><br>
    <label>Số điện thoại</label><br>
    <input type="text" name="phone"><br><br>
    <button type="submit">Đăng ký</button>
    <a href="login.php">Đã có tài khoản? Đăng nhập</a>
</form>

