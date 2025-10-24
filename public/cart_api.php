<?php
// Public API for cart actions (AJAX-friendly)
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/Cart.php';
require_once __DIR__ . '/../app/models/Product.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? 'index';
$cart = new Cart();

try {
    switch ($action) {
        case 'add':
            $productId = $_POST['product_id'] ?? null;
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            if (!$productId) {
                echo json_encode(['success' => false, 'message' => 'Thiếu product_id']);
                exit;
            }

            $cart->addToCart($productId, $quantity);

            // get product name for friendly message
            $pm = new Product();
            $p = $pm->getProductById($productId);
            $name = $p['name'] ?? 'Sản phẩm';

            echo json_encode(['success' => true, 'message' => $name . ' đã thêm vào giỏ hàng.']);
            exit;

        case 'remove':
            $id = $_REQUEST['id'] ?? null; // cart_items.id expected
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Thiếu id']);
                exit;
            }
            $cart->removeItem($id);
            echo json_encode(['success' => true, 'message' => 'Đã xóa sản phẩm khỏi giỏ.']);
            exit;

        case 'total':
            // selected[] expected in POST
            $selected = $_POST['selected'] ?? [];
            // normalize values to ints
            $selected = array_map('intval', (array)$selected);
            $total = $cart->calculateTotal($selected);
            echo json_encode(['success' => true, 'total' => $total]);
            exit;

        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
            exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi server', 'error' => $e->getMessage()]);
    exit;
}
