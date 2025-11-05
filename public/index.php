<!--Trang chính của website petshop -->
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Proxy để client gọi API trong app/api an toàn hơn
if (isset($_GET['__api']) && $_GET['__api'] === 'cart') {
    require_once __DIR__ . '/../app/api/cart_api.php';
    exit;
}
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/Category.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/controllers/CartController.php';
require_once __DIR__ . '/../app/components/product_cart.php';


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
    <?php if (isset($_GET['id'])): ?>
        <link rel="stylesheet" href="assets/css/product_detail.css" />
    <?php endif; ?>
    <?php if (isset($_GET['page']) && $_GET['page'] === 'cart'): ?>
        <link rel="stylesheet" href="assets/css/cart-modern.css" />
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/category.css">
    <link rel="stylesheet" href="assets/css/product_card.css">
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

<div class="container page-grid">
    <!-- Sidebar -->
    <aside class="sidebar left-sidebar">
        <h3>Danh mục</h3>
        <ul class="categories" id="category-list">
            <!-- Danh mục sẽ được load từ API bằng JS -->
        </ul>

    </aside>

    <!-- Main content -->
    <main id="main-content" class="main-content">
        <?php
            // Render cart inside the main content when requested
            if (isset($_GET['page']) && $_GET['page'] === 'cart') {
                include 'cart.php';

            } elseif (isset($_GET['id'])) {
                include 'product_detail.php';

            } else {
        ?>
        <section class="hero">
            <div class="hero-banner">🐶 Giao hàng hỏa tốc - Ưu đãi cực lớn!</div>
        </section>

        <section class="products">
            <h2>Sản phẩm nổi bật</h2>
            <div class="product-grid">
                <?php foreach ($products as $p): ?>
                    <?php renderProductCard($p); ?>
                <?php endforeach; ?>
            </div>
        </section>
        <?php } ?>
    </main>

    <!-- Xoa Right sidebar r  -->
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
<script src="assets/js/productRender.js"></script>
<script src="assets/js/productAction.js"></script>
<script src="assets/js/category.js"></script> <!-- Thêm file JS quản lý danh mục sản phẩm-->
</body>
</html>
