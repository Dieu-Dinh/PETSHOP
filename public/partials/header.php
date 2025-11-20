<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">🐾 PETSHOP</a>
        <form class="search-form" action="index.php?page=products" method="get">
            <input name="q" type="search" placeholder="Tìm kiếm sản phẩm..." />
            <button type="submit">Tìm</button>
        </form>
        <nav class="top-nav">
            <a href="index.php">Trang chủ</a>
            <a href="index.php?page=products" class="ajax-nav">Sản phẩm</a>
            <a href="contact.php">Liên hệ</a>
            <a href="index.php?page=cart" class="icon-cart">🛒 Giỏ hàng</a>
            <?php if ($currentUser): ?>
                <a href="profile.php" class="icon-user">Xin chào, <?= htmlspecialchars($currentUser['first_name'] ?? $currentUser['email']) ?></a>
                <a href="auth.php?action=logout">Đăng xuất</a>
            <?php else: ?>
                <a href="login.php" class="icon-user">Đăng nhập</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
