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
        $this->pdo = $pdo;
        // session structure: $_SESSION['user']['id'] used across the app
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $this->isLoggedIn = isset($_SESSION['user']['id']);
        $this->userId = $_SESSION['user']['id'] ?? null;
        $this->sessionId = session_id() ?: null;
    }

    /**  Lấy danh sách sản phẩm trong giỏ */
    public function getCartItems()
    {
        // If DB not available, fall back to session cart if present
        if (!$this->pdo) {
            return $_SESSION['cart'] ?? [];
        }

        // We'll collect items into a map keyed by product_id and sum quantities
        $itemsMap = [];

        // 1) Items saved for the logged-in user (if any)
        if ($this->isLoggedIn) {
            $userCartId = $this->getCartIdForUser(false);
            if ($userCartId) {
                $ustmt = $this->pdo->prepare(
                    "SELECT ci.id, p.id AS product_id, p.name, COALESCE(ci.price_snapshot, p.price) AS price, p.image, ci.quantity, ci.cart_id\n                     FROM cart_items ci\n                     LEFT JOIN products p ON ci.product_id = p.id\n                     WHERE ci.cart_id = ?"
                );
                $ustmt->execute([$userCartId]);
                $uitems = $ustmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($uitems as $it) {
                    $pid = $it['product_id'];
                    if (!isset($itemsMap[$pid])) {
                        $itemsMap[$pid] = $it;
                    } else {
                        $itemsMap[$pid]['quantity'] += $it['quantity'];
                    }
                }
            }
        }

        // 2) Items saved for the current session (guest cart) — include them too
        if ($this->sessionId) {
            $sessCartId = $this->getCartIdForSession(false);
            if ($sessCartId) {
                $sstmt = $this->pdo->prepare(
                    "SELECT ci.id, p.id AS product_id, p.name, COALESCE(ci.price_snapshot, p.price) AS price, p.image, ci.quantity, ci.cart_id\n                     FROM cart_items ci\n                     LEFT JOIN products p ON ci.product_id = p.id\n                     WHERE ci.cart_id = ?"
                );
                $sstmt->execute([$sessCartId]);
                $sitems = $sstmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($sitems as $it) {
                    $pid = $it['product_id'];
                    if (!isset($itemsMap[$pid])) {
                        $itemsMap[$pid] = $it;
                    } else {
                        $itemsMap[$pid]['quantity'] += $it['quantity'];
                    }
                }
            }
        }

        // Convert map to indexed array with clean keys
        $result = [];
        foreach ($itemsMap as $pid => $row) {
            $result[] = [
                'id' => $row['id'],
                'product_id' => $row['product_id'],
                'name' => $row['name'],
                'price' => $row['price'],
                'image' => $row['image'],
                'quantity' => $row['quantity']
            ];
        }
        return $result;
    }

    /**  Thêm sản phẩm vào giỏ */
    public function addToCart($productId, $quantity = 1)
    {
        // If no DB connection, fall back to session cart behavior
        if (!$this->pdo) {
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]['quantity'] += $quantity;
            } else {
                $stmt = $this->pdo->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
                $stmt->execute([$productId]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($product) {
                    $_SESSION['cart'][$productId] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'image' => $product['image'],
                        'quantity' => $quantity
                    ];
                }
            }
            return;
        }

        // Use carts + cart_items schema: ensure we have a cart row for current context
        $cartId = $this->getCurrentCartId(true);
        if (!$cartId) return;

        // If item exists in cart_items for this cart_id + product_id, update quantity
        $stmt = $this->pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $stmt->execute([$cartId, $productId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($item) {
            $newQty = $item['quantity'] + $quantity;
            $update = $this->pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
            $update->execute([$newQty, $item['id']]);
        } else {
            // obtain current product price as snapshot
            $pstmt = $this->pdo->prepare("SELECT price FROM products WHERE id = ?");
            $pstmt->execute([$productId]);
            $prod = $pstmt->fetch(PDO::FETCH_ASSOC);
            $priceSnapshot = $prod['price'] ?? null;

            $insert = $this->pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price_snapshot) VALUES (?, ?, ?, ?)");
            $insert->execute([$cartId, $productId, $quantity, $priceSnapshot]);
        }
    }

    /**  Xóa sản phẩm khỏi giỏ */
    public function removeItem($id)
    {
        if (!$this->pdo) {
            unset($_SESSION['cart'][$id]);
            return;
        }

        $cartId = $this->getCurrentCartId(false);
        if (!$cartId) return;

        $stmt = $this->pdo->prepare("DELETE FROM cart_items WHERE id = ? AND cart_id = ?");
        $stmt->execute([$id, $cartId]);
    }

    /**  Tính tổng tiền các sản phẩm được chọn */
    public function calculateTotal($selectedIds = [])
    {
        $total = 0;
        $items = $this->getCartItems();
        foreach ($items as $item) {
            if (empty($selectedIds) || in_array($item['id'], $selectedIds)) {
                $price = $item['price'] ?? 0;
                $qty = $item['quantity'] ?? 0;
                $total += $price * $qty;
            }
        }
        return $total;
    }

    /**
     * Helpers for cart id management
     */
    private function getCurrentCartId($createIfMissing = false)
    {
        if (!$this->pdo) return null;

        if ($this->isLoggedIn) {
            return $this->getCartIdForUser($createIfMissing);
        }

        return $this->getCartIdForSession($createIfMissing);
    }

    private function getCartIdForUser($createIfMissing = false)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM carts WHERE user_id = ? LIMIT 1");
        $stmt->execute([$this->userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) return $row['id'];

        if ($createIfMissing) {
            $ins = $this->pdo->prepare("INSERT INTO carts (user_id, session_id) VALUES (?, ?) ");
            $ins->execute([$this->userId, $this->sessionId]);
            return $this->pdo->lastInsertId();
        }
        return null;
    }

    private function getCartIdForSession($createIfMissing = false)
    {
        if (!$this->sessionId) return null;
        $stmt = $this->pdo->prepare("SELECT id FROM carts WHERE session_id = ? LIMIT 1");
        $stmt->execute([$this->sessionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) return $row['id'];

        if ($createIfMissing) {
            $ins = $this->pdo->prepare("INSERT INTO carts (user_id, session_id) VALUES (NULL, ?) ");
            $ins->execute([$this->sessionId]);
            return $this->pdo->lastInsertId();
        }
        return null;
    }

    /** Move items from a session-based cart (by session_id) into the user's cart */
    public function mergeSessionCartToUser($sessionId, $userId)
    {
        if (!$this->pdo) return;
        if (!$sessionId || !$userId) return;

        // find session cart
        $stmt = $this->pdo->prepare("SELECT id FROM carts WHERE session_id = ? LIMIT 1");
        $stmt->execute([$sessionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return;
        $sessionCartId = $row['id'];

        // find or create user cart
        $this->userId = $userId;
        $userCartId = $this->getCartIdForUser(true);

        // move items
        $stmt = $this->pdo->prepare("SELECT product_id, quantity, price_snapshot FROM cart_items WHERE cart_id = ?");
        $stmt->execute([$sessionCartId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($items as $it) {
            $productId = $it['product_id'];
            $qty = $it['quantity'];

            // if exists in user cart, update quantity, else insert
            $check = $this->pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
            $check->execute([$userCartId, $productId]);
            $existing = $check->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $newQ = $existing['quantity'] + $qty;
                $up = $this->pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
                $up->execute([$newQ, $existing['id']]);
            } else {
                $ins = $this->pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity, price_snapshot) VALUES (?, ?, ?, ?)");
                $ins->execute([$userCartId, $productId, $qty, $it['price_snapshot']]);
            }
        }

        // remove session cart items and cart row
        $del = $this->pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $del->execute([$sessionCartId]);
        $delc = $this->pdo->prepare("DELETE FROM carts WHERE id = ?");
        $delc->execute([$sessionCartId]);
    }
}
?>
