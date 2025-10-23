<?php
require_once __DIR__ . '/../config/database.php';

class Category
{
    private $pdo;

    /**
     * Constructor accepts optional PDO. If not provided, it will fall back to the
     * global $pdo initialized by app/config/database.php.
     */
    public function __construct($pdo = null)
    {
        if ($pdo instanceof PDO) {
            $this->pdo = $pdo;
            return;
        }

        // Ensure database config is loaded and read the global $pdo
        if (!isset($GLOBALS['pdo'])) {
            $cfg = __DIR__ . '/../config/database.php';
            if (file_exists($cfg)) {
                require_once $cfg;
            }
        }

        $this->pdo = $GLOBALS['pdo'] ?? null;
    }

    public function getActiveCategories($limit = 50)
    {
        $sql = "SELECT id, name, slug, description, parent_id
                FROM categories
                WHERE is_active = 1
                ORDER BY sort_order ASC, name ASC
                LIMIT :limit";
        if (!$this->pdo) {
            // DB not available -> return empty array for graceful degradation
            return [];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategoryById($id)
    {
        if (!$this->pdo) return null;
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getSubcategories($parentId)
    {
        if (!$this->pdo) return [];
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE parent_id = :pid AND is_active = 1 ORDER BY sort_order ASC");
        $stmt->execute([':pid' => $parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

function getActiveCategories($limit = 50)
{
    global $pdo;
    $model = new Category($pdo);
    return $model->getActiveCategories($limit);
}
