<?php
if (session_status() === PHP_SESSION_NONE) session_start();
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
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <div class="container page-grid">
        <?php include 'partials/sidebar.php'; ?>

        <main id="main-content" class="main-content">
            <?php
                if (isset($_GET['page']) && $_GET['page'] === 'cart') {
                    include 'main/cart.php';
                } elseif (isset($_GET['id'])) {
                    include 'main/product_detail.php';
                } else {
                    include 'main/hero.php';
                    include 'main/featured_products.php';
                }
            ?>
        </main>
    </div>

    <?php include 'partials/footer.php'; ?>

    <script src="assets/js/index.js"></script>
    <script src="assets/js/productRender.js"></script>
    <script src="assets/js/productAction.js"></script>
    <script src="assets/js/category.js"></script>
</body>
</html>
