<?php
require_once __DIR__ . '/../config/database.php';

class Category {
    protected $pdo;

    public function __construct($pdoInstance = null) {
        $this->pdo = $pdoInstance ?? $GLOBALS['pdo'] ?? null;
    }

    // Get active categories
    public function allActive($limit = 50) {
        if (!$this->pdo) return [];
        $stmt = $this->pdo->prepare("SELECT id, name, slug FROM categories WHERE is_active=1 ORDER BY sort_order ASC LIMIT :lim");
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Optional simple helper for procedural usage
function getActiveCategories($limit = 50) {
    $c = new Category($GLOBALS['pdo'] ?? null);
    return $c->allActive($limit);
}
