<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Category model
 * Handles operations related to the `categories` table.
 */
class Category
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Lấy danh sách danh mục đang hoạt động.
     * @param int $limit Giới hạn số lượng trả về.
     * @return array
     */
    public function getActiveCategories($limit = 50)
    {
        $sql = "SELECT id, name, slug, description, parent_id
                FROM categories
                WHERE is_active = 1
                ORDER BY sort_order ASC, name ASC
                LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin một danh mục theo ID.
     */
    public function getCategoryById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách danh mục con của một danh mục cha.
     */
    public function getSubcategories($parentId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE parent_id = :pid AND is_active = 1 ORDER BY sort_order ASC");
        $stmt->execute([':pid' => $parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

/**
 * Hàm tiện ích — để gọi nhanh trong controller / view.
 */
function getActiveCategories($limit = 50)
{
    $model = new Category();
    return $model->getActiveCategories($limit);
}
