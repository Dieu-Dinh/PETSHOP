<?php
require_once __DIR__ . '/../config/database.php';

class Product {
    protected $pdo;

    public function __construct($pdoInstance = null) {
        $this->pdo = $pdoInstance ?? $GLOBALS['pdo'] ?? null;
    }

    // Get active products with optional limit
    public function getActive($limit = 12) {
        if (!$this->pdo) return [];
        $sql = "SELECT p.id, p.name, p.slug, p.price, pi.url AS image
                FROM products p
                LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
                WHERE p.status = 'active' LIMIT :lim";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

function getActiveProducts($limit = 12) {
    $p = new Product($GLOBALS['pdo'] ?? null);
    return $p->getActive($limit);
}
