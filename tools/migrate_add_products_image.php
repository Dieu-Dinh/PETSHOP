<?php
// tools/migrate_add_products_image.php
// Run: php .\tools\migrate_add_products_image.php
require_once __DIR__ . '/../app/config/database.php';

if (!isset($pdo) || !$pdo) {
    echo "ERROR: Database connection not available. Check app/config/database.php\n";
    exit(1);
}

try {
    $dbName = null;
    // try to get DB name from PDO DSN
    $attributes = $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
} catch (Exception $e) {
    // ignore
}

// Check if column exists
$stmt = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'image'");
$stmt->execute();
$exists = $stmt->fetch(PDO::FETCH_ASSOC);

if ($exists) {
    echo "Column 'image' already exists on 'products'. Nothing to do.\n";
    exit(0);
}

// Add column
try {
    echo "Adding column 'image' to 'products'...\n";
    $pdo->exec("ALTER TABLE products ADD COLUMN image VARCHAR(255) NULL AFTER status");
    echo "Done. Column added.\n";
} catch (Exception $e) {
    echo "Failed to add column: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);
