<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../app/controllers/CartController.php';

$controller = new CartController();

// Nếu có action xóa sản phẩm
if (isset($_GET['action']) && $_GET['action'] === 'remove') {
    $controller->remove();
    exit;
}

// Lấy danh sách sản phẩm trong giỏ
$cartItems = $controller->index();
$cartMessage = $_SESSION['message'] ?? null;
unset($_SESSION['message']);
?>

<link rel="stylesheet" href="/PETSHOP/public/assets/css/cart.css" />

<div class="cart-container">
    <h2 class="cart-title">🛒 Giỏ hàng của bạn</h2>

    <?php if (!empty($cartMessage)): ?>
        <div class="alert"><?= htmlspecialchars($cartMessage) ?></div>
    <?php endif; ?>

    <?php if (!empty($cartItems)): ?>
        <form id="cart-form">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Chọn</th>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Tổng</th>
                        <th>Xóa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr class="cart-row" data-id="<?= htmlspecialchars($item['id']) ?>" data-total="<?= ($item['price'] * $item['quantity']) ?>">
                            <td><input type="checkbox" class="select-item" value="<?= htmlspecialchars($item['id']) ?>"></td>
                            <td>
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                <?php else: ?>
                                    <div style="width:70px;height:70px;background:#eee;line-height:70px;">No Image</div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= number_format($item['price'], 0, ',', '.') ?> đ</td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> đ</td>
                            <td>
                                <a href="index.php?page=cart&action=remove&id=<?= htmlspecialchars($item['id']) ?>" class="btn-remove">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="total-section">
                Tổng tiền: <span id="total-price">0</span> đ
            </div>

            <div class="cart-actions">
                <button type="button" class="btn-checkout">Thanh toán</button>
            </div>
        </form>
    <?php else: ?>
        <p style="text-align:center;">🛍 Giỏ hàng của bạn đang trống.</p>
    <?php endif; ?>
</div>
<!-- cart page uses global cart.js (delegated handlers) to update totals -->
