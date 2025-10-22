<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Product model
 * Handles operations related to the `products` table.
 */
class Product
{
    private $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Lấy sản phẩm đang hoạt động (active).
     * @param int $limit
     * @return array
     */

    public function getAllProducts()
    {
        $sql = "
            SELECT p.*, 
                   c.name AS category_name,
                   (SELECT url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.created_at DESC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Thêm sản phẩm mới */
    public function createProduct($data)
    {
        $sql = "INSERT INTO products (sku, name, slug, category_id, base_price, price, stock_quantity, status)
                VALUES (:sku, :name, :slug, :category_id, :base_price, :price, :stock_quantity, :status)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    /** Cập nhật sản phẩm */
    public function updateProduct($id, $data)
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    /** Xóa sản phẩm */
    public function deleteProduct($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /** Thay đổi trạng thái (ẩn/hiện) */
    public function toggleStatus($id, $status)
    {
        $stmt = $this->pdo->prepare("UPDATE products SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }
    
    public function getActiveProducts($limit = 12)
    {
        $sql = "SELECT p.id, p.name, p.slug, p.price, p.base_price, p.category_id,
                       p.featured, p.status, p.stock_status,
                       (SELECT url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
                FROM products p
                WHERE p.status = 'active'
                ORDER BY p.featured DESC, p.created_at DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy sản phẩm theo ID.
     */
    public function getProductById($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.*, 
                   (SELECT url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
            FROM products p
            WHERE p.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách sản phẩm theo danh mục.
     */
    public function getProductsByCategory($categoryId, $limit = 20)
    {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.name, p.slug, p.price, 
                   (SELECT url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
            FROM products p
            WHERE p.category_id = :cid AND p.status = 'active'
            ORDER BY p.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':cid', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

/**
 * Hàm tiện ích — để gọi nhanh trong controller / view.
 */
function getActiveProducts($limit = 12)
{
    $model = new Product();
    return $model->getActiveProducts($limit);
}
