<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Ensure the browser interprets pages as UTF-8
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/Category.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/controllers/CartController.php';
require_once __DIR__ . '/../app/components/product_cart.php';

$currentUser = !empty($_SESSION['user']['id']) ? (new User())->findById($_SESSION['user']['id']) : null;
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
    <link rel="stylesheet" href="assets/css/category.css">
    <link rel="stylesheet" href="assets/css/product_card.css">
    <link rel="stylesheet" href="assets/css/product_filter.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <?php // Include profile & login modal partials so their markup, CSS and scripts are available site-wide ?>
    <?php include 'partials/profile_modal.php'; ?>
    <?php include 'partials/login_modal.php'; ?>

    <div class="container page-grid">
        <?php include 'partials/sidebar.php'; ?>

        <main id="main-content" class="main-content">
            <?php
                if (isset($_GET['page']) && $_GET['page'] === 'cart') {
                    include 'main/cart.php';
                } elseif (isset($_GET['page']) && $_GET['page'] === 'checkout') {
                    include 'main/checkout.php';
                } elseif (isset($_GET['page']) && $_GET['page'] === 'products') {
                    include 'main/product.php';
                } elseif (isset($_GET['page']) && $_GET['page'] === 'order_success') {
                    include 'main/order_success.php';
                } elseif (isset($_GET['page']) && (($_GET['page'] === 'gioithieu') || ($_GET['page'] === 'gioithieu.html'))) {
                    include 'main/gioithieu.html';
                } elseif (isset($_GET['page']) && (($_GET['page'] === 'chinhSachDoiTra') || ($_GET['page'] === 'chinhSachDoiTra.html'))) {
                    include 'main/chinhSachDoiTra.html';
                } elseif (isset($_GET['id'])) {
                    include 'main/product_detail.php';
                } else {
                    include 'main/hero.php';
                    include 'main/featured_products.php';
                    include 'main/blog_tip.php';
                }
            ?>
        </main>
    </div>

    <?php include 'partials/footer.php'; ?>

    <script src="assets/js/index.js"></script>
    <script src="assets/js/logout_confirm.js"></script>
    <script src="assets/js/product_filter.js"></script>
    <script src="assets/js/productRender.js"></script>
    <script src="assets/js/productAction.js"></script>
    <script src="assets/js/cart.js"></script>
    <script src="assets/js/category.js"></script>
    <script src="assets/js/checkout.js"></script>
</body>
</html>
