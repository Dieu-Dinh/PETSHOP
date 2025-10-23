<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: /PETSHOP/public/login.php");
    exit();
}
$admin = $_SESSION['user']['email'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang quản trị | PetShop</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2 class="logo">🐾 PetShop</h2>
            <ul class="menu">
                <li><a href="#" class="menu-link active" data-page="dashboard">📊 Bảng điều khiển</a></li>
                <li><a href="#" class="menu-link" data-page="manage_user">👤 Quản lý người dùng</a></li>
                <li><a href="#" class="menu-link" data-page="manage_product">🛒 Quản lý sản phẩm</a></li>
                <li><a href="#" class="menu-link" data-page="manage_order">📦 Quản lý đơn hàng</a></li>
            </ul>
            <a href="/PETSHOP/public/auth.php?action=logout" class="logout-btn">🚪 Đăng xuất</a>
        </aside>

        <!-- Main -->
        <main class="main-content" id="main-content">
            <div class="top-bar">
                <input type="text" id="global-search" placeholder="🔍 Tìm kiếm...">
                <span class="admin-email"><?= htmlspecialchars($admin) ?></span>
            </div>
            <!-- Nội dung sẽ được load ở đây -->
            <div id="page-content"></div>
        </main>
    </div>

    <script>
        // Khi bấm vào menu thì load nội dung vào #main-content
        document.querySelectorAll('.menu-link').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const page = e.target.getAttribute('data-page');

                // Gọi tới PHP partial tương ứng
                fetch(`${page}.php`)
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('page-content').innerHTML = html;
                        // Cập nhật active link
                        document.querySelectorAll('.menu-link').forEach(l => l.classList.remove('active'));
                        e.target.classList.add('active');
                    })
                    .catch(err => {
                        document.getElementById('page-content').innerHTML = "<p>Lỗi tải dữ liệu.</p>";
                    });
            });
        });

        // Mặc định load dashboard khi vào trang
        window.addEventListener('DOMContentLoaded', () => {
            fetch('dashboard.php')
                .then(res => res.text())
                .then(html => document.getElementById('page-content').innerHTML = html);
        });
    </script>
</body>
</html>
