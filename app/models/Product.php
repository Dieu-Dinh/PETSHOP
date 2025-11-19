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

    /**
     * Normalize an image value from DB into a public-facing URL.
     * - If full URL, return as-is
     * - If root-relative (starts with '/'), return as-is
     * - If bare filename or 'images/...' path, return '/PETSHOP/public/images/products/<basename>'
     */
    private function normalizeImageUrl($val)
    {
        if (empty($val)) return '/PETSHOP/public/images/no_image.png';
        $v = trim($val);
        if (preg_match('#^https?://#i', $v)) return $v;
        if (strpos($v, '/') === 0) return $v;
        // if contains images/ prefix, use basename
        if (stripos($v, 'images/') !== false) {
            $base = basename($v);
            return '/PETSHOP/public/images/products/' . $base;
        }
        return '/PETSHOP/public/images/products/' . basename($v);
    }

    /** Lấy toàn bộ sản phẩm */
    public function getAllProducts()
    {
        $sql = "
            SELECT p.*, 
                   c.name AS category_name,
                   COALESCE(p.image,
                       (SELECT url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1)
                   ) AS image
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.created_at DESC
        ";
        if (!$this->pdo) return [];
        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // normalize image URLs for UI
        foreach ($rows as &$r) {
            $r['image'] = $this->normalizeImageUrl($r['image'] ?? null);
        }
        return $rows;
    }

    /** Thêm sản phẩm mới */
    public function createProduct($data)
    {
        $sql = "INSERT INTO products (sku, name, slug, category_id, base_price, price, stock_quantity, status, image)
            VALUES (:sku, :name, :slug, :category_id, :base_price, :price, :stock_quantity, :status, :image)";
        if (!$this->pdo) return false;
        $stmt = $this->pdo->prepare($sql);
        $ok = $stmt->execute($data);
        if (!$ok) return false;
        // return the inserted product id
        $id = (int)$this->pdo->lastInsertId();
        return $id > 0 ? $id : true;
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
                       COALESCE(p.image,
                           (SELECT url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1)
                       ) AS image
                FROM products p
                WHERE p.status = 'active'
                ORDER BY p.featured DESC, p.created_at DESC
                LIMIT :limit";

        if (!$this->pdo) return [];
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['image'] = $this->normalizeImageUrl($r['image'] ?? null);
        }
        return $rows;
    }

    /** Lấy sản phẩm theo ID */
    public function getProductById($id)
    {
        if (!$this->pdo) return null;

    $sql = "
        SELECT p.*,
               b.name AS brand_name,
               c.name AS category_name,
               COALESCE(p.image,
                   (SELECT pi.url
                    FROM product_images pi
                    WHERE pi.product_id = p.id
                    ORDER BY pi.is_primary DESC, pi.sort_order ASC
                    LIMIT 1)
               ) AS image
        FROM products p
        LEFT JOIN brands b ON p.brand_id = b.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = :id
        LIMIT 1
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $row['image'] = $this->normalizeImageUrl($row['image'] ?? null);
    }
    return $row;
    }

    /**
     * Trả về mảng ảnh cho sản phẩm (url, alt_text, is_primary)
     */
    public function getProductImages($productId)
    {
        if (!$this->pdo) return [];
        $stmt = $this->pdo->prepare("SELECT url, alt_text, is_primary FROM product_images WHERE product_id = :pid ORDER BY is_primary DESC, sort_order ASC");
        $stmt->execute([':pid' => $productId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['url'] = $this->normalizeImageUrl($r['url'] ?? null);
        }
        return $rows;
    }

    /**
     * Add an image row for a product. If is_primary=1, demote other primary images.
     */
    public function addProductImage($productId, $url, $is_primary = 1, $alt_text = null)
    {
        if (!$this->pdo) return false;
        try {
            $this->pdo->beginTransaction();
            if ($is_primary) {
                $stmt = $this->pdo->prepare("UPDATE product_images SET is_primary = 0 WHERE product_id = :pid");
                $stmt->execute([':pid' => $productId]);
            }

            $stmt = $this->pdo->prepare("INSERT INTO product_images (product_id, url, alt_text, is_primary, sort_order) VALUES (:pid, :url, :alt, :iprimary, 0)");
            $stmt->execute([
                ':pid' => $productId,
                ':url' => $url,
                ':alt' => $alt_text,
                ':iprimary' => $is_primary ? 1 : 0,
            ]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            return false;
        }
    }

    /** Lấy sản phẩm theo danh mục */
    public function getProductsByCategory($categoryId, $limit = 20)
    {
        if (!$this->pdo) return [];
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.name, p.slug, p.price, p.category_id, p.stock_status,
       COALESCE(p.image,
           (SELECT url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1)
       ) AS image
            FROM products p
            WHERE p.category_id = :cid AND p.status = 'active'
            ORDER BY p.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':cid', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['image'] = $this->normalizeImageUrl($r['image'] ?? null);
        }
        return $rows;
    }

    /** Lấy sản phẩm liên quan cùng danh mục */
    public function getRelatedProducts($categoryId, $excludeId, $limit = 4)
    {
                if (!$this->pdo) return [];
                $stmt = $this->pdo->prepare("
                        SELECT p.id, p.name, p.slug, p.price, 
                                     COALESCE(p.image,
                                         (SELECT url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1)
                                     ) AS image
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
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as &$r) {
                    $r['image'] = $this->normalizeImageUrl($r['image'] ?? null);
                }
                return $rows;
    }

    /** Tìm kiếm sản phẩm theo từ khóa */
    public function searchProducts($keyword, $limit = 20)
    {
                if (!$this->pdo) return [];
                $stmt = $this->pdo->prepare("
                        SELECT p.id, p.name, p.slug, p.price,
                                     COALESCE(p.image,
                                         (SELECT url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1)
                                     ) AS image
                        FROM products p
                        WHERE p.status = 'active'
                            AND (p.name LIKE :kw OR p.short_description LIKE :kw)
                        ORDER BY p.created_at DESC
                        LIMIT :limit
                ");
                $stmt->bindValue(':kw', "%$keyword%", PDO::PARAM_STR);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as &$r) {
                    $r['image'] = $this->normalizeImageUrl($r['image'] ?? null);
                }
                return $rows;
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
