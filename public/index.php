<!--Trang chính của website petshop -->
<?php
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/Category.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/User.php';

if (session_status() === PHP_SESSION_NONE) session_start();
$currentUser = null;
if (!empty($_SESSION['user']['id'])) {
    $userModel = new User();
    $currentUser = $userModel->findById($_SESSION['user']['id']);
}

$categories = getActiveCategories(50);
$products = getActiveProducts(12);

$posts = [];
if (isset($pdo) && $pdo) {
    $stmt = $pdo->query("SELECT id, title, slug, excerpt, featured_image, published_at FROM blog_posts WHERE status='published' ORDER BY published_at DESC LIMIT 3");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Petshop - Trang chủ</title>
    <link rel="stylesheet" href="assets/css/style.css" />
</head>

<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">🐾 PETSHOP</a>
        <form class="search-form" action="product.php" method="get">
            <input name="q" type="search" placeholder="Tìm kiếm sản phẩm..." />
            <button type="submit">Tìm</button>
        </form>
        <nav class="top-nav">
            <a href="index.php">Trang chủ</a>
            <a href="product.php">Sản phẩm</a>
            <a href="contact.php">Liên hệ</a>
            <a href="#" class="icon-cart">Giỏ hàng</a>
            <?php if ($currentUser): ?>
                <a href="profile.php" class="icon-user">Xin chào, <?= htmlspecialchars($currentUser['first_name'] ?? $currentUser['email']) ?></a>
                <a href="auth.php?action=logout">Đăng xuất</a>
            <?php else: ?>
                <a href="login.php" class="icon-user">Đăng nhập</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<div class="container page-grid">
    <!-- Sidebar -->
    <aside class="sidebar left-sidebar">
        <h3>Danh mục</h3>
        <ul class="categories">
            <?php foreach ($categories as $cat): ?>
                <li><a href="product.php?category=<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <!-- Main content -->
    <main id="main-content" class="main-content"> 
    <?php if (isset($_GET['id'])): ?>
        <?php include 'product_detail.php'; ?>
    <?php else: ?>
        <section class="hero">
            <div class="hero-banner">🐶 Giao hàng hỏa tốc - Ưu đãi cực lớn!</div>
        </section>

        <section class="products">
            <h2>Sản phẩm nổi bật</h2>
            <div class="product-grid">
                <?php foreach ($products as $p): ?>
                    <article class="product">
                        <a href="?id=<?= htmlspecialchars($p['id']) ?>" class="product-link" data-id="<?= htmlspecialchars($p['id']) ?>">
                            <div class="thumb">
                                <?php if (!empty($p['image'])): ?>
                                    <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" />
                                <?php else: ?>
                                    <div class="no-image">Không có ảnh</div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <h3 class="title"><?= htmlspecialchars($p['name']) ?></h3>
                        <div class="price"><?= number_format($p['price'], 0, ',', '.') ?> đ</div>

                        <div class="actions">
                            <form action="cart_add.php" method="post">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($p['id']) ?>">
                                <button type="submit" class="btn-cart">🛒 Thêm vào giỏ</button>
                            </form>

                            <form action="checkout_now.php" method="post">
                                <input type="hidden" name="product_id" value="<?= htmlspecialchars($p['id']) ?>">
                                <button type="submit" class="btn-buy">⚡ Mua ngay</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

    <!-- Xoa Right sidebar r-->
</div>

<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-col">
            <h4>Về chúng tôi</h4>
            <p>Công ty TNHH Petshop Việt Nam</p>
        </div>
        <div class="footer-col">
            <h4>Chính sách</h4>
            <ul>
                <li><a href="#">Điều khoản</a></li>
                <li><a href="#">Chính sách đổi trả</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Liên hệ</h4>
            <p>Email: order@petshop.vn</p>
        </div>
    </div>
    <div class="copyright">© <span id="year"></span> PETSHOP</div>
</footer>

<script src="assets/js/index.js"></script>
</body>
</html>
