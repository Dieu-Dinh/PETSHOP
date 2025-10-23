<?php
require_once __DIR__ . '/../app/config/database.php';

if (!isset($pdo) || !$pdo) {
    echo "PDO is null or DB connection failed.\n";
    exit(0);
}

try {
    $stmt = $pdo->query("SELECT COUNT(*) AS c FROM categories WHERE is_active = 1");
    $cat = $stmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0;

    $stmt = $pdo->query("SELECT COUNT(*) AS c FROM products WHERE status = 'active'");
    $prod = $stmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0;

    echo "DB OK\nActive categories: " . $cat . "\nActive products: " . $prod . "\n";
} catch (Exception $e) {
    echo "DB query error: " . $e->getMessage() . "\n";
}
