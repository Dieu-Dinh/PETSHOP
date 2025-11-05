<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

// Đảm bảo đường dẫn tới model chính xác
require_once __DIR__ . '/../models/Cart.php';

// Tạo instance
$cart = new Cart();

// Lấy dữ liệu request (GET hoặc POST)
$action = strtolower($_REQUEST['action'] ?? '');
$productId = (int)($_REQUEST['product_id'] ?? $_REQUEST['id'] ?? 0);
$quantity = max(1, (int)($_REQUEST['quantity'] ?? 1));

function json($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    switch ($action) {
        case 'add':
            if ($productId <= 0) json(['status' => 'error', 'message' => 'Thiếu ID sản phẩm']);
            $cart->addToCart($productId, $quantity);
            json(['status' => 'success', 'message' => 'Đã thêm vào giỏ hàng!']);

        case 'remove':
            if ($productId <= 0) json(['status' => 'error', 'message' => 'Thiếu ID sản phẩm']);
            $cart->removeItem($productId);
            json(['status' => 'success', 'message' => 'Đã xóa sản phẩm khỏi giỏ hàng!']);

        case 'list':
            $items = $cart->getCartItems();
            json(['status' => 'success', 'data' => $items]);

        default:
            json(['status' => 'error', 'message' => 'Hành động không hợp lệ']);
    }
} catch (Exception $e) {
    json(['status' => 'error', 'message' => 'Lỗi server: ' . $e->getMessage()]);
}
