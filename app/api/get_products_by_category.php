<?php
// app/api/get_products_by_category.php

// Trả JSON và ngăn cache (tùy cần)
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

try {
    require_once __DIR__ . '/../config/database.php'; // sửa đường dẫn nếu cần
    require_once __DIR__ . '/../models/Product.php';
    require_once __DIR__ . '/../models/Category.php';

    // Lấy category_id (ưu tiên GET). Nếu ko có => trả all hoặc error tùy yêu cầu.
    $categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;

    // Validate
    if ($categoryId <= 0) {
        // Nếu bạn muốn trả tất cả sản phẩm khi không truyền id, đổi logic ở đây.
        echo json_encode([
            'success' => false,
            'message' => 'Tham số category_id không hợp lệ.'
        ]);
        exit;
    }

    $productModel = new Product($GLOBALS['pdo'] ?? null);
    $categoryModel = new Category($GLOBALS['pdo'] ?? null);

    $category = $categoryModel->getCategoryById($categoryId);
    if (!$category) {
        echo json_encode([
            'success' => false,
            'message' => 'Danh mục không tồn tại.'
        ]);
        exit;
    }

    // Lấy sản phẩm, có thể truyền limit trong query string nếu muốn
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 50;
    if ($limit <= 0 || $limit > 200) $limit = 50;

    $products = $productModel->getProductsByCategory($categoryId, $limit);

    // Chuẩn hóa output: chỉ trả các trường cần thiết (an toàn hơn)
    $out = [];
    foreach ($products as $p) {
        $out[] = [
            'id' => (int)$p['id'],
            'name' => $p['name'],
            'slug' => $p['slug'] ?? null,
            'price' => isset($p['price']) ? (float)$p['price'] : null,
            'image' => isset($p['image']) ? $p['image'] : null, // URL hoặc null
            'category_id' => (int)$p['category_id'],
            'stock_status' => $p['stock_status'] ?? null,
        ];
    }

    echo json_encode([
        'success' => true,
        'category' => ['id' => (int)$category['id'], 'name' => $category['name']],
        'count' => count($out),
        'products' => $out
    ]);
    exit;

} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi server.',
        'error' => $ex->getMessage()
    ]);
    exit;
}
