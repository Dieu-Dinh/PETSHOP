<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../models/Cart.php';

class CartController
{
    private $cart;

    public function __construct()
    {
        $this->cart = new Cart();
    }

    /** 🛒 Hiển thị giỏ hàng */
    public function index()
    {
        $cartItems = $this->cart->getCartItems();
        // return cart items so caller (view) can render them
        return $this->cart->getCartItems();
    }

    /** ➕ Thêm sản phẩm vào giỏ */
    public function add($redirect = 'index.php?page=cart')
    {
        if (!isset($_POST['product_id'])) {
            die('Thiếu ID sản phẩm');
        }

        $productId = $_POST['product_id'];
        $quantity = $_POST['quantity'] ?? 1;

        // add to cart (DB or session handled by model)
        $this->cart->addToCart($productId, $quantity);

        // try to load product name for message (best effort)
        $productName = null;
        $prodPath = __DIR__ . '/../models/Product.php';
        if (file_exists($prodPath)) {
            require_once $prodPath;
            try {
                $pm = new Product();
                $p = $pm->getProductById($productId);
                if ($p && !empty($p['name'])) $productName = $p['name'];
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $message = ($productName ? $productName . ' đã thêm vào giỏ hàng.' : 'Đã thêm sản phẩm vào giỏ hàng!');

        // If request is AJAX (fetch), return JSON and do not redirect
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (!empty($_POST['ajax']) && $_POST['ajax'] == '1');
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $message]);
            exit;
        }

        // Thêm thông báo thành công (flash)
        $_SESSION['message'] = $message;

        // redirect back to caller
        header("Location: $redirect");
        exit;
    }

    /** ❌ Xóa sản phẩm khỏi giỏ */
    public function remove($redirect = 'index.php?page=cart')
    {
        if (!isset($_GET['id'])) {
            die('Thiếu ID sản phẩm');
        }

        $id = $_GET['id'];
        $this->cart->removeItem($id);
        header("Location: $redirect");
        exit;
    }

    /** 💰 Tính tổng tiền (dùng cho AJAX) */
    public function total()
    {
        $selected = $_POST['selected'] ?? [];
        $total = $this->cart->calculateTotal($selected);

        echo json_encode(['total' => $total]);
    }
}

/** 🔄 Bộ định tuyến đơn giản theo ?action=... */
$controller = new CartController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'index':
        $controller->index();
        break;
    case 'add':
        $controller->add();
        break;
    case 'remove':
        $controller->remove();
        break;
    case 'total':
        $controller->total();
        break;
    default:
        echo "Không tìm thấy action.";
        break;
}
