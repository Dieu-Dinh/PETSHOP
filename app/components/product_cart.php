<?php
/**
 * renderProductCard($product, $options = [])
 * $product: associative array có keys: id, name, price, image, slug, stock_status, ...
 * $options:
 *   - show_buttons: bool (default true) - hiển thị 2 nút
 *   - buy_now_url: string|null - nếu muốn nút "Mua ngay" chuyển tới trang checkout với product id
 */
function renderProductCard(array $product, array $options = []) {
    $showButtons = $options['show_buttons'] ?? true;
    $buyNowUrl = $options['buy_now_url'] ?? null;

    $id = htmlspecialchars($product['id'] ?? '');
    $name = htmlspecialchars($product['name'] ?? 'Không tên');
    $price = isset($product['price']) ? number_format($product['price'], 0, ',', '.') . ' ₫' : 'Liên hệ';
    $img = htmlspecialchars($product['image'] ?? 'assets/images/no-image.png');
    $slug = htmlspecialchars($product['slug'] ?? '');
    $stock = htmlspecialchars($product['stock_status'] ?? '');
    ?>
    <div class="product-card" data-id="<?= $id ?>">
        <a class="product-link" href="product.php?id=<?= $id ?>" title="<?= $name ?>">
            <div class="product-thumb">
                <img src="<?= $img ?>" alt="<?= $name ?>">
            </div>
        </a>

        <div class="product-body">
            <h3 class="product-title"><?= $name ?></h3>
            <div class="product-meta">
                <span class="product-price"><?= $price ?></span>
                <?php if ($stock): ?>
                    <span class="product-stock"><?= $stock ?></span>
                <?php endif; ?>
            </div>

            <?php if ($showButtons): ?>
                <div class="btn-group">
                    <button class="btn add-to-cart" data-id="<?= $id ?>">🛒 Thêm vào giỏ</button>
                    <?php if ($buyNowUrl): ?>
                        <a class="btn buy-now" href="<?= htmlspecialchars($buyNowUrl) ?>">⚡ Mua ngay</a>
                    <?php else: ?>
                        <button class="btn buy-now" data-id="<?= $id ?>">⚡ Mua ngay</button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
}
