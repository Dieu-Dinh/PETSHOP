<?php
// Expects $cartItems to be set by caller
?>

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
                        <tr>
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

<script>
// Cập nhật tổng tiền khi tick chọn sản phẩm
document.querySelectorAll('.select-item').forEach(chk => {
    chk.addEventListener('change', () => {
        const selected = Array.from(document.querySelectorAll('.select-item:checked')).map(i => i.value);

        fetch('cart_api.php?action=total', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'selected[]=' + selected.join('&selected[]=')
        })
        .then(res => res.json())
        .then(data => {
            const el = document.getElementById('total-price');
            if (el) el.textContent = new Intl.NumberFormat('vi-VN').format(data.total);
        });
    });
});
</script>
