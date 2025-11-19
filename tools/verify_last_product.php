<?php
// tools/verify_last_product.php
// Usage (PowerShell):
//   php .\tools\verify_last_product.php
// This script prints the last product, its product_images rows, and checks
// whether the image files exist on disk (in public/images/products/ and other candidates).

require_once __DIR__ . '/../app/config/database.php';

if (!isset($pdo) || !$pdo) {
    echo "ERROR: Database connection not available. Check app/config/database.php\n";
    exit(1);
}

try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 1");
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "DB query failed: " . $e->getMessage() . "\n";
    exit(1);
}

if (!$prod) {
    echo "No products found in `products` table.\n";
    exit(0);
}

echo "== Last product (products table) ==\n";
foreach ($prod as $k => $v) {
    echo "$k: $v\n";
}

$productId = $prod['id'];

// Fetch product_images rows
$stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = :pid ORDER BY is_primary DESC, sort_order ASC");
$stmt->execute([':pid' => $productId]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n== product_images rows for product_id={$productId} ==\n";
if (!$images) {
    echo "(no rows found in product_images)\n";
} else {
    foreach ($images as $i => $row) {
        echo "-- row #$i --\n";
        foreach ($row as $k => $v) {
            echo "  $k: $v\n";
        }
    }
}

// Filesystem checks
$publicDir = realpath(__DIR__ . '/../public');
$imgDir = $publicDir . '/images/products';

echo "\n== Filesystem checks ==\n";
// Helper to test candidates
$testCandidate = function($candidate) {
    $exists = file_exists($candidate);
    echo sprintf("%s => %s\n", $candidate, $exists ? 'FOUND' : 'MISSING');
};

// Check products.image
$prodImage = $prod['image'] ?? null;
if ($prodImage) {
    echo "Product.image: $prodImage\n";
    $candidates = [
        $imgDir . '/' . basename($prodImage),
        $publicDir . '/' . ltrim($prodImage, '/'),
        __DIR__ . '/' . ltrim($prodImage, '/'),
        $prodImage,
    ];
    foreach ($candidates as $c) $testCandidate($c);
} else {
    echo "products.image is empty\n";
}

// Check each product_images.url
if ($images) {
    foreach ($images as $idx => $row) {
        $url = $row['url'] ?? '';
        echo "\nproduct_images[{$idx}].url: $url\n";
        $candidates = [
            $imgDir . '/' . basename($url),
            $publicDir . '/' . ltrim($url, '/'),
            __DIR__ . '/' . ltrim($url, '/'),
            $url,
        ];
        foreach ($candidates as $c) $testCandidate($c);
    }
}

echo "\nDone. If files are MISSING, please verify uploads saved to public/images/products/ and that filenames in DB match the saved filenames.\n";

exit(0);
