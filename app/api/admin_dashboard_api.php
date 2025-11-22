<?php
// Admin dashboard API: KPIs + recent orders
// Use admin session namespace so admin area uses a separate cookie from public
if (session_status() === PHP_SESSION_NONE) {
    session_name('ADMINSESSID');
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

// Admin guard
if (empty($_SESSION['user']['id']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

function json($d) { echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; }

try {
    // KPIs
    // 1) revenue last 7 days
    $stmt = $pdo->prepare("SELECT IFNULL(SUM(total_amount),0) FROM orders WHERE placed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->execute(); $rev7 = (float)$stmt->fetchColumn();

    // 2) revenue last 30 days
    $stmt = $pdo->prepare("SELECT IFNULL(SUM(total_amount),0) FROM orders WHERE placed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute(); $rev30 = (float)$stmt->fetchColumn();

    // 3) orders today
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(placed_at) = DATE(NOW())");
    $stmt->execute(); $orders_today = (int)$stmt->fetchColumn();

    // 4) new customers in last 30 days
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute(); $new_customers = (int)$stmt->fetchColumn();

    // 5) low stock count (products with stock_quantity <= 5)
    // Products table may have column `stock_quantity` or `stock` — try a couple of names
    $lowStock = 0;
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 5");
        $lowStock = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= 5");
            $lowStock = (int)$stmt->fetchColumn();
        } catch (Exception $e2) {
            $lowStock = 0;
        }
    }

    // recent orders (latest 8)
    $stmt = $pdo->query("SELECT o.id,o.order_number,o.total_amount,o.payment_status,o.status,o.placed_at,u.first_name,u.email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.placed_at DESC LIMIT 8");
    $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // revenue series last 7 days (date, total)
    $stmt = $pdo->prepare("SELECT DATE(placed_at) as d, IFNULL(SUM(total_amount),0) as total FROM orders
        WHERE placed_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(placed_at)
        ORDER BY DATE(placed_at) ASC");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // build full 7-day series including zeros for missing days
    $series = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $series[$date] = 0.0;
    }
    foreach ($rows as $r) {
        $d = $r['d'];
        $series[$d] = (float)$r['total'];
    }
    $revenue_series = array_map(function($k,$v){ return ['date'=>$k,'total'=>$v]; }, array_keys($series), $series);

    // orders count series last 7 days
    $stmt = $pdo->prepare("SELECT DATE(placed_at) as d, COUNT(*) as cnt FROM orders
        WHERE placed_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(placed_at)
        ORDER BY DATE(placed_at) ASC");
    $stmt->execute();
    $rows2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $series2 = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $series2[$date] = 0;
    }
    foreach ($rows2 as $r) {
        $d = $r['d'];
        $series2[$d] = (int)$r['cnt'];
    }
    $orders_series = array_map(function($k,$v){ return ['date'=>$k,'orders'=>$v]; }, array_keys($series2), $series2);

    json([
        'success' => true,
        'data' => [
            'kpis' => [
                'revenue_7' => $rev7,
                'revenue_30' => $rev30,
                'orders_today' => $orders_today,
                'new_customers' => $new_customers,
                'low_stock_count' => $lowStock
            ],
            'recent_orders' => $recent,
            'revenue_series_7' => array_values($revenue_series),
            'orders_series_7' => array_values($orders_series)
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    json(['success' => false, 'message' => $e->getMessage()]);
}
