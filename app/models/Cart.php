<?php
require_once __DIR__ . '/../config/database.php';

class Cart
{
    private $pdo;
    private $isLoggedIn;
    private $userId;
    private $sessionId;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo ?? null;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->isLoggedIn = isset($_SESSION['user']['id']);
        $this->userId = $_SESSION['user']['id'] ?? null;
        $this->sessionId = session_id();
    }

    /** 🧺 Lấy danh sách sản phẩm trong giỏ hàng */
    public function getCartItems()
    {
        // Nếu không có DB, fallback qua session
        if (!$this->pdo) {
            return $_SESSION['cart'] ?? [];
        }

        $cartId = $this->getCurrentCartId(false);
        if (!$cartId) return [];

        $stmt = $this->pdo->prepare("
            SELECT ci.id, p.id AS product_id, p.name, 
                   COALESCE(ci.price_snapshot, p.price) AS price, 
                   p.image, ci.quantity
            FROM cart_items ci
            LEFT JOIN products p ON ci.product_id = p.id
            WHERE ci.cart_id = ?
        ");
        $stmt->execute([$cartId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** ➕ Thêm sản phẩm vào giỏ hàng */
    public function addToCart($productId, $quantity = 1)
    {
        // Nếu không có DB (chạy offline)
        if (!$this->pdo) {
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$productId] = [
                    'id' => $productId,
                    'name' => "Sản phẩm #$productId",
                    'price' => 0,
                    'image' => '',
                    'quantity' => $quantity
                ];
            }
            return true;
        }

        // Nếu có DB, thêm vào bảng
        $cartId = $this->getCurrentCartId(true);
        if (!$cartId) return false;

        // Kiểm tra nếu sản phẩm đã có trong giỏ
        $stmt = $this->pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $stmt->execute([$cartId, $productId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $newQty = $existing['quantity'] + $quantity;
            $update = $this->pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            $update->execute([$newQty, $existing['id']]);
        } else {
            $pstmt = $this->pdo->prepare("SELECT price FROM products WHERE id = ?");
            $pstmt->execute([$productId]);
            $product = $pstmt->fetch(PDO::FETCH_ASSOC);
            $priceSnapshot = $product['price'] ?? 0;

            $insert = $this->pdo->prepare("
                INSERT INTO cart_items (cart_id, product_id, quantity, price_snapshot)
                VALUES (?, ?, ?, ?)
            ");
            $insert->execute([$cartId, $productId, $quantity, $priceSnapshot]);
        }

        return true;
    }

    /** ❌ Xóa sản phẩm khỏi giỏ hàng */
    public function removeItem($itemId)
    {
        if (!$this->pdo) {
            unset($_SESSION['cart'][$itemId]);
            return;
        }

        $cartId = $this->getCurrentCartId(false);
        if (!$cartId) return;

        $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE id = ? AND cart_id = ?");
        $stmt->execute([$itemId, $cartId]);
    }

    /** 💰 Tính tổng tiền */
    public function calculateTotal()
    {
        $items = $this->getCartItems();
        $total = 0;

        foreach ($items as $item) {
            $total += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
        }

        return $total;
    }

    /** 🧩 Helpers */
    private function getCurrentCartId($createIfMissing = false)
    {
        return $this->isLoggedIn
            ? $this->getCartIdForUser($createIfMissing)
            : $this->getCartIdForSession($createIfMissing);
    }

    private function getCartIdForUser($createIfMissing = false)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM carts WHERE user_id = ? LIMIT 1");
        $stmt->execute([$this->userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) return $row['id'];
        if (!$createIfMissing) return null;

        $ins = $this->pdo->prepare("INSERT INTO carts (user_id, session_id) VALUES (?, ?)");
        $ins->execute([$this->userId, $this->sessionId]);
        return $this->pdo->lastInsertId();
    }

    private function getCartIdForSession($createIfMissing = false)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM carts WHERE session_id = ? LIMIT 1");
        $stmt->execute([$this->sessionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) return $row['id'];
        if (!$createIfMissing) return null;

        $ins = $this->pdo->prepare("INSERT INTO carts (user_id, session_id) VALUES (NULL, ?)");
        $ins->execute([$this->sessionId]);
        return $this->pdo->lastInsertId();
    }
}
