<?php
require_once __DIR__ . '/../../app/models/Cart.php';
require_once __DIR__ . '/../../app/config/database.php';

$cart = new Cart();
$items = $cart->getCartItems();
$subtotal = 0;
foreach ($items as $it) {
    $subtotal += ($it['price'] ?? 0) * ($it['quantity'] ?? 1);
}
?>

<link rel="stylesheet" href="/PETSHOP/public/assets/css/checkout.css">

<div class="checkout-page">
    <div class="checkout-grid container">
        <div class="checkout-left">
            <section class="cart-list">
                <h2>Giỏ hàng</h2>
                <?php if (empty($items)): ?>
                    <p>Giỏ hàng trống.</p>
                <?php else: ?>
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Số lượng</th>
                                <th>Giá</th>
                                <th>Tổng phụ</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $row): ?>
                            <tr class="cart-row" data-price="<?= htmlspecialchars($row['price']) ?>" data-qty="<?= htmlspecialchars($row['quantity']) ?>">
                                <td class="prod-name"><?= htmlspecialchars($row['name']) ?></td>
                                <td class="prod-qty"><?= htmlspecialchars($row['quantity']) ?></td>
                                <td class="prod-price"><?= number_format($row['price'], 0, ',', '.') ?> đ</td>
                                <td class="prod-subtotal"><?= number_format(($row['price'] * $row['quantity']), 0, ',', '.') ?> đ</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

            <section class="address-form">
                <h3>Địa chỉ giao hàng</h3>
                <form id="checkoutAddress">
                    <div class="form-row">
                        <label>Họ tên</label>
                        <input type="text" name="fullname" id="fullname" required>
                    </div>

                    <div class="form-row">
                        <label>Số điện thoại</label>
                        <input type="tel" name="phone" id="phone" required>
                    </div>

                    <div class="form-row">
                        <label>Tỉnh / Huyện / Phường</label>
                        <select id="province" name="province">
                            <option value="">-- Chọn tỉnh/thành --</option>
                            <option value="Hanoi">Hà Nội</option>
                            <option value="HCM">TP HCM</option>
                            <option value="Danang">Đà Nẵng</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label>Địa chỉ cụ thể</label>
                        <input type="text" name="address" id="address" required>
                    </div>
                </form>
            </section>

            <section class="payment-methods">
                <h3>Phương thức thanh toán</h3>
                <form id="paymentForm">
                    <label><input type="radio" name="payment" value="cod" checked> COD (Trả khi nhận hàng)</label>
                    <label><input type="radio" name="payment" value="bank"> Chuyển khoản ngân hàng</label>
                    <label><input type="radio" name="payment" value="momo"> Ví MoMo</label>
                    <label><input type="radio" name="payment" value="vnpay"> VNPAY</label>
                    <label><input type="radio" name="payment" value="card"> Thẻ tín dụng</label>
                </form>
            </section>
        </div>

        <aside class="checkout-right">
            <div class="order-summary">
                <h3>Đơn hàng</h3>
                <div class="summary-row"><span>Tổng giá trị giỏ hàng:</span> <span id="summarySubtotal"><?= number_format($subtotal, 0, ',', '.') ?> đ</span></div>
                <div class="summary-row">
                    <span>Phí ship:</span>
                    <select id="shippingSelect">
                        <option value="30000" selected>Giao tiêu chuẩn: 30.000 đ</option>
                        <option value="50000">Giao nhanh: 50.000 đ</option>
                        <option value="0">Nhận tại cửa hàng: Miễn phí</option>
                    </select>
                </div>
                <div class="summary-row">
                    <span>Thuế (VAT):</span>
                    <span id="summaryTax">0 đ</span>
                </div>

                <div class="summary-row coupon-row">
                    <input type="text" id="couponCode" placeholder="Mã giảm giá">
                    <button id="applyCoupon" class="btn-apply">Áp dụng</button>
                </div>

                <div class="summary-row total-row"><strong>Tổng thanh toán:</strong> <strong id="summaryTotal"><?= number_format($subtotal, 0, ',', '.') ?> đ</strong></div>

                <div style="margin-top:12px">
                    <button id="placeOrder" class="btn-primary full">Đặt hàng</button>
                </div>
            </div>
        </aside>
    </div>
</div>

<script>
// Small inline config: initial subtotal in cents-like integer
window.checkoutInitialSubtotal = <?= (int)$subtotal ?>;
</script>
