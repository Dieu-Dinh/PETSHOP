<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$items = $data['items'] ?? [];
if (empty($items) || !is_array($items)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No items provided']);
    exit;
}

$subtotal = floatval($data['subtotal'] ?? 0);
$shipping = floatval($data['shipping_fee'] ?? 0);
$tax = floatval($data['tax'] ?? 0);
$discount = floatval($data['discount'] ?? 0);
$total = floatval($data['total'] ?? ($subtotal + $shipping + $tax - $discount));
$payment_method = in_array($data['payment_method'] ?? 'cod', ['cod','bank','credit_card','momo','vnpay']) ? $data['payment_method'] : 'cod';

$userId = $_SESSION['user']['id'] ?? null;

$orderData = [
    'user_id' => $userId,
    'subtotal' => $subtotal,
    'shipping_fee' => $shipping,
    'tax' => $tax,
    'discount' => $discount,
    'total' => $total,
    'payment_method' => $payment_method,
    'notes' => $data['notes'] ?? ''
];

try {
    $orderId = Order::create($pdo, $orderData, $items);

    // clear cart: if logged-in, remove cart items in DB; otherwise clear session
    if ($userId) {
        // remove cart rows for this user's cart
        $stmt = $pdo->prepare("SELECT id FROM carts WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $cartRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($cartRow) {
            $cartId = $cartRow['id'];
            $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?")->execute([$cartId]);
        }
    } else {
        // guest: remove session cart
        if (isset($_SESSION['cart'])) unset($_SESSION['cart']);
        // also remove cart record matching session id if exists
        $sessionId = session_id();
        $stmt = $pdo->prepare("SELECT id FROM carts WHERE session_id = ? AND user_id IS NULL LIMIT 1");
        $stmt->execute([$sessionId]);
        $c = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($c) {
            $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?")->execute([$c['id']]);
            $pdo->prepare("DELETE FROM carts WHERE id = ?")->execute([$c['id']]);
        }
    }

    echo json_encode(['success' => true, 'order_id' => $orderId]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not create order', 'error' => $e->getMessage()]);
}
