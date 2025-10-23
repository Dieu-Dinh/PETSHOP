<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Product model
 * Handles operations related to the `products` table.
 */
class Product
{
    private $pdo;

    /**
     * Constructor accepts an optional PDO. Falls back to global $pdo if not provided.
     */
    public function __construct($pdo = null)
    {
        if ($pdo instanceof PDO) {
            $this->pdo = $pdo;
            return;
        }

        if (!isset($GLOBALS['pdo'])) {
            $cfg = __DIR__ . '/../config/database.php';
            if (file_exists($cfg)) {
                require_once $cfg;
            }
        }

        $this->pdo = $GLOBALS['pdo'] ?? null;
    }

    /** Lấy toàn bộ sản phẩm */
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
        if (!$this->pdo) return [];
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Thêm sản phẩm mới */
    public function createProduct($data)
    {
        $sql = "INSERT INTO products (sku, name, slug, category_id, base_price, price, stock_quantity, status)
                VALUES (:sku, :name, :slug, :category_id, :base_price, :price, :stock_quantity, :status)";
        if (!$this->pdo) return false;
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
        if (!$this->pdo) return false;
        $stmt = $this->pdo->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    /** Xóa sản phẩm */
    public function deleteProduct($id)
    {
        if (!$this->pdo) return false;
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /** Thay đổi trạng thái (ẩn/hiện) */
    public function toggleStatus($id, $status)
    {
        if (!$this->pdo) return false;
        $stmt = $this->pdo->prepare("UPDATE products SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    /** Lấy danh sách sản phẩm đang hoạt động */
    public function getActiveProducts($limit = 12)
    {
        $sql = "SELECT p.id, p.name, p.slug, p.price, p.base_price, p.category_id,
                       p.featured, p.status, p.stock_status,
                       (SELECT url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
                FROM products p
                WHERE p.status = 'active'
                ORDER BY p.featured DESC, p.created_at DESC
                LIMIT :limit";

        if (!$this->pdo) return [];
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Lấy sản phẩm theo ID */
    public function getProductById($id)
    {
        if (!$this->pdo) return null;
        $stmt = $this->pdo->prepare("
            SELECT p.*, 
                   (SELECT url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
            FROM products p
            WHERE p.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Lấy sản phẩm theo danh mục */
    public function getProductsByCategory($categoryId, $limit = 20)
    {
        if (!$this->pdo) return [];
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

    /** Lấy sản phẩm liên quan cùng danh mục */
    public function getRelatedProducts($categoryId, $excludeId, $limit = 4)
    {
                if (!$this->pdo) return [];
                $stmt = $this->pdo->prepare("
                        SELECT p.id, p.name, p.slug, p.price, 
                                     (SELECT url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
                        FROM products p
                        WHERE p.category_id = :cid 
                            AND p.id != :excludeId
                            AND p.status = 'active'
                        ORDER BY p.created_at DESC
                        LIMIT :limit
                ");
                $stmt->bindValue(':cid', $categoryId, PDO::PARAM_INT);
                $stmt->bindValue(':excludeId', $excludeId, PDO::PARAM_INT);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Tìm kiếm sản phẩm theo từ khóa */
    public function searchProducts($keyword, $limit = 20)
    {
                if (!$this->pdo) return [];
                $stmt = $this->pdo->prepare("
                        SELECT p.id, p.name, p.slug, p.price,
                                     (SELECT url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) AS image
                        FROM products p
                        WHERE p.status = 'active'
                            AND (p.name LIKE :kw OR p.short_description LIKE :kw)
                        ORDER BY p.created_at DESC
                        LIMIT :limit
                ");
                $stmt->bindValue(':kw', "%$keyword%", PDO::PARAM_STR);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();

                return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Giảm số lượng tồn kho sau khi mua */
    public function decreaseStock($productId, $quantity)
    {
        if (!$this->pdo) return false;
        $stmt = $this->pdo->prepare("
            UPDATE products 
            SET stock_quantity = GREATEST(stock_quantity - :qty, 0)
            WHERE id = :id
        ");
        return $stmt->execute([':qty' => $quantity, ':id' => $productId]);
    }
}

/**
 * 🧩 Các hàm tiện ích (nằm ngoài class)
 */
function getActiveProducts($limit = 12)
{
    $model = new Product();
    return $model->getActiveProducts($limit);
}

/**
 * Helper: Lấy sản phẩm theo ID (global helper để các view gọi trực tiếp)
 */
function getProductById($id)
{
    $model = new Product();
    return $model->getProductById($id);
}

/**
 * Helper: Lấy sản phẩm liên quan (cùng danh mục)
 */
function getRelatedProducts($categoryId, $excludeId, $limit = 4)
{
    $model = new Product();
    return $model->getRelatedProducts($categoryId, $excludeId, $limit);
}