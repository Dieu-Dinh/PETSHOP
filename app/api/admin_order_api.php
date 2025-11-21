<?php
// Admin API for orders (GET list, GET details, POST actions)
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

// quick admin guard: ensure logged-in and role=admin
if (empty($_SESSION['user']['id']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Forbidden']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

function json($d){ echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; }

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id > 0) {
            // fetch order details, items, payments, address
            $stmt = $pdo->prepare("SELECT o.*, u.email AS user_email, u.first_name, u.last_name
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = ? LIMIT 1");
            $stmt->execute([$id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$order) json(['success'=>false,'message'=>'Order not found']);

            $it = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $it->execute([$id]);
            $items = $it->fetchAll(PDO::FETCH_ASSOC);

            $pay = $pdo->prepare("SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC");
            $pay->execute([$id]);
            $payments = $pay->fetchAll(PDO::FETCH_ASSOC);

            json(['success'=>true,'data'=>['order'=>$order,'items'=>$items,'payments'=>$payments]]);
        } else {
            // list orders (simple, latest first)
            $stmt = $pdo->query("SELECT o.id,o.order_number,o.user_id,o.total_amount,o.payment_status,o.status,o.placed_at,u.first_name,u.email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.placed_at DESC LIMIT 200");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json(['success'=>true,'data'=>$rows]);
        }
    }

    if ($method === 'POST') {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) json(['success'=>false,'message'=>'Invalid JSON']);

        $action = $data['action'] ?? '';
        if ($action === 'update_status') {
            $orderId = intval($data['order_id'] ?? 0);
            $status = $data['status'] ?? '';
            $allowed = ['pending','confirmed','packing','shipped','delivered','cancelled','returned'];
            if ($orderId <= 0 || !in_array($status,$allowed)) json(['success'=>false,'message'=>'Invalid data']);
            $u = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
            $u->execute([$status,$orderId]);
            json(['success'=>true]);
        }

        if ($action === 'cancel') {
            $orderId = intval($data['order_id'] ?? 0);
            if ($orderId <= 0) json(['success'=>false,'message'=>'Invalid order']);
            $u = $pdo->prepare("UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
            $u->execute([$orderId]);
            json(['success'=>true]);
        }

        json(['success'=>false,'message'=>'Unknown action']);
    }

    http_response_code(405);
    json(['success'=>false,'message'=>'Method not allowed']);

} catch (PDOException $e) {
    http_response_code(500);
    json(['success'=>false,'message'=>$e->getMessage()]);
}
