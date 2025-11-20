<?php
require_once __DIR__ . '/../../app/models/Product.php';
require_once __DIR__ . '/../../app/components/product_cart.php';
require_once __DIR__ . '/../../app/models/Category.php';

$productModel = new Product();

// --- Lấy từ khóa tìm kiếm
$q = trim($_GET['q'] ?? '');

// --- Lấy kiểu sort
$sort = $_GET['sort'] ?? 'default';

// --- Lấy category filter
$category = $_GET['category'] ?? '';

/*
 Sort Options:
    - price_asc  : giá tăng dần
    - price_desc : giá giảm dần
    - name_asc   : tên A-Z
    - name_desc  : tên Z-A
*/

// Nếu có tìm kiếm
if ($q !== '') {
    $products = $productModel->searchProducts($q, 200);
} else {
    $products = $productModel->getAllProducts();
}

// Nếu có filter theo category (nếu Product model không có method getProductsByCategory,
// ta lọc mảng kết quả ở đây)
if ($category !== '') {
    $cid = (int)$category;
    $products = array_values(array_filter($products, function($p) use ($cid) {
        if (isset($p['category_id'])) return (int)$p['category_id'] === $cid;
        if (isset($p['category'])) return (int)$p['category'] === $cid;
        return false;
    }));
}

// Sắp xếp theo yêu cầu
switch ($sort) {
    case "price_asc":
        usort($products, fn($a,$b) => $a['price'] - $b['price']);
        break;
    case "price_desc":
        usort($products, fn($a,$b) => $b['price'] - $a['price']);
        break;
    case "name_asc":
        usort($products, fn($a,$b) => strcmp($a['name'], $b['name']));
        break;
    case "name_desc":
        usort($products, fn($a,$b) => strcmp($b['name'], $a['name']));
        break;
}

?>
<link rel="stylesheet" href="../assets/css/product_card.css" />
<link rel="stylesheet" href="../assets/css/product_filter.css" />

<main class="main-content">

    <section class="product-list-page">

        <h2 class="page-title">Tất cả sản phẩm</h2>

        <!-- FORM TÌM KIẾM + SORT -->
        <form id="filterForm" class="product-filter">

            <div class="search-group">
                <input type="text" name="q"
                       placeholder="Tìm kiếm sản phẩm..."
                       value="<?= htmlspecialchars($q) ?>">
                <button type="submit" class="btn-search">Tìm</button>
            </div>

                <!-- Category filter -->
                <?php $categories = getActiveCategories(100); ?>
                <select name="category" id="categorySelect">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>" <?= ($category !== '' && (int)$category===(int)$cat['id'])? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>

            <select name="sort" id="sortSelect">
                <option value="default"     <?= $sort=='default' ? 'selected' : '' ?>>Mặc định</option>
                <option value="price_asc"   <?= $sort=='price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                <option value="price_desc"  <?= $sort=='price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
                <option value="name_asc"    <?= $sort=='name_asc' ? 'selected' : '' ?>>Tên A-Z</option>
                <option value="name_desc"   <?= $sort=='name_desc' ? 'selected' : '' ?>>Tên Z-A</option>
            </select>

            <!-- button moved into search-group for layout -->
        </form>

        <!-- DANH SÁCH SẢN PHẨM -->
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <?php renderProductCard($product, ["show_buttons" => true]); ?>
            <?php endforeach; ?>
        </div>

    </section>
</main>

<!-- product scripts are loaded globally in public/index.php so they run even when this partial is injected -->
