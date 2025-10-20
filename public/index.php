<!--Trang chinh của website petshop -->
<?php
// Use app config and models (do not open a new PDO here; use existing database loader)
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/Category.php';
require_once __DIR__ . '/../app/models/Product.php';
require_once __DIR__ . '/../app/models/User.php';

// Start session and get current user (if any)
if (session_status() === PHP_SESSION_NONE) session_start();
$currentUser = null;
if (!empty($_SESSION['user']['id'])) {
    $userModel = new User();
    $currentUser = $userModel->findById($_SESSION['user']['id']);
}

// Get data via models
$categories = getActiveCategories(50);
$products = getActiveProducts(12);

// Posts: simple inline query using $pdo from config; keep it light here
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
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Petshop - Home</title>
        <!-- Main stylesheet: edit styles in public/assets/css/style.css -->
        <link rel="stylesheet" href="assets/css/style.css" />
    </head>
    <body>
        <!-- Header / Navbar -->
        <header class="site-header">
            <div class="container header-inner">
                <a class="brand" href="index.php">PETSHOP</a>
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

        <!-- Page layout: left sidebar, main content, right (optional) -->
        <div class="container page-grid">
            <!-- Left sidebar / Categories -->
            <aside class="sidebar left-sidebar">
                <h3>Danh mục</h3>
                        <ul class="categories">
                            <?php if (!empty($categories)): ?>
                                <?php foreach($categories as $cat): ?>
                                    <li><a href="product.php?category=<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li>Không có danh mục</li>
                            <?php endif; ?>
                        </ul>
            </aside>

            <!-- Main content -->
            <main class="main-content">
                <!-- Hero / Carousel placeholder -->
                <section class="hero">
                    <!-- TODO: server: insert slider / featured banner markup here -->
                    <div class="hero-banner">Giao hàng hỏa tốc - Banner</div>
                </section>

                <!-- Promotions / Quick links -->
                <section class="quick-links">
                    <!-- TODO: server: insert promo boxes -->
                    <div class="promo">Khuyến mãi</div>
                    <div class="promo">Deal hôm nay</div>
                    <div class="promo">Thương hiệu</div>
                </section>

                <!-- Products grid -->
                <section class="products">
                    <h2>Sản phẩm nổi bật</h2>
                                <div class="product-grid">
                                    <?php if (!empty($products)): ?>
                                        <?php foreach($products as $p): ?>
                                            <article class="product">
                                                <a href="product_detail.php?id=<?= htmlspecialchars($p['id']) ?>">
                                                    <div class="thumb">
                                                        <?php if (!empty($p['image'])): ?>
                                                            <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="max-width:100%;height:100%;object-fit:contain;border-radius:6px" />
                                                        <?php else: ?>
                                                            <div style="height:100%;display:flex;align-items:center;justify-content:center;color:#999">No image</div>
                                                        <?php endif; ?>
                                                    </div>
                                                </a>
                                                <h3 class="title"><?= htmlspecialchars($p['name']) ?></h3>
                                                <div class="price"><?= isset($p['price']) ? number_format($p['price'],0,',','.') . 'đ' : 'Liên hệ' ?></div>
                                            </article>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div>Không có sản phẩm nào</div>
                                    <?php endif; ?>
                                </div>
                </section>

                <!-- Blog / news -->
                <section class="blog">
                    <h2>Tin tức</h2>
                                <div class="blog-list">
                                    <?php if (!empty($posts)): ?>
                                        <?php foreach($posts as $post): ?>
                                            <article class="post">
                                                <h4><a href="/blog.php?id=<?= htmlspecialchars($post['id']) ?>"><?= htmlspecialchars($post['title']) ?></a></h4>
                                                <p><?= htmlspecialchars(mb_substr($post['excerpt'] ?? '', 0, 200)) ?></p>
                                            </article>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div>Không có bài viết</div>
                                    <?php endif; ?>
                                </div>
                </section>
            </main>

            <!-- Right sidebar (optional) -->
            <aside class="sidebar right-sidebar">
                <h3>Thông tin</h3>
                <div class="widget">Hotline: 0902.848.949</div>
                <div class="widget">Giỏ hàng: <span id="cart-count">0</span></div>
            </aside>
        </div>

        <!-- Footer -->
        <footer class="site-footer">
            <div class="container footer-inner">
                <div class="footer-col">
                    <h4>Về chúng tôi</h4>
                    <p>Công ty TNHH Petshop Vietnam</p>
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

        <script>
            // Small helper: current year
            document.getElementById('year').textContent = new Date().getFullYear();
            // TODO: add small scripts for cart, search suggestions, etc.
        </script>
    </body>
</html>

