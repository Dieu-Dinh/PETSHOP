    <?php
    require_once __DIR__ . '/../app/config/database.php';

    if (!isset($_GET['id'])) {
        echo "<p>Không tìm thấy sản phẩm.</p>";
        exit;
    }

    $product_id = intval($_GET['id']);
    $stmt = $pdo->prepare("
        SELECT p.*, b.name AS brand_name, c.name AS category_name
        FROM products p
        LEFT JOIN brands b ON p.brand_id = b.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>

    <div class="product-detail">
        <?php if ($product): ?>
            <div class="product-detail-container">
                <!-- Hình ảnh sản phẩm -->
                <div class="product-detail-image">
                    <img src="<?= htmlspecialchars($product['image'] ?? 'assets/img/no-image.png') ?>" 
                        alt="<?= htmlspecialchars($product['name']) ?>">
                </div>

                <!-- Thông tin sản phẩm -->
                <div class="product-detail-info">
                    <h2 class="product-title"><?= htmlspecialchars($product['name']) ?></h2>

                    <p class="sku">Mã sản phẩm (SKU): <strong><?= htmlspecialchars($product['sku']) ?></strong></p>
                    <p class="brand">Thương hiệu: <strong><?= htmlspecialchars($product['brand_name'] ?? 'Không rõ') ?></strong></p>
                    <p class="category">Danh mục: <strong><?= htmlspecialchars($product['category_name'] ?? 'Chưa phân loại') ?></strong></p>

                    <p class="product-price" id="unit-price" data-price="<?= $product['price'] ?>">
                        Giá: <strong><?= number_format($product['price'], 0, ',', '.') ?> đ</strong>
                        <?php if ($product['price'] < $product['base_price']): ?>
                            <span class="old-price"><?= number_format($product['base_price'], 0, ',', '.') ?> đ</span>
                        <?php endif; ?>
                    </p>

                    <!-- Trạng thái kho -->
                    <p class="stock-status">
                        Tình trạng:
                        <?php if ($product['stock_status'] === 'in_stock'): ?>
                            <span class="in-stock">Còn hàng (<?= $product['stock_quantity'] ?> sản phẩm)</span>
                        <?php elseif ($product['stock_status'] === 'preorder'): ?>
                            <span class="preorder">Đặt trước</span>
                        <?php else: ?>
                            <span class="out-of-stock">Hết hàng</span>
                        <?php endif; ?>
                    </p>

                    <!-- Mô tả ngắn -->
                    <?php if (!empty($product['short_description'])): ?>
                        <p class="short-desc"><?= nl2br(htmlspecialchars($product['short_description'])) ?></p>
                    <?php endif; ?>

                    <!-- Kích thước & trọng lượng -->
                    <div class="product-dimensions">
                        <p>Kích thước: <?= $product['length'] ?> × <?= $product['width'] ?> × <?= $product['height'] ?> cm</p>
                        <p>Trọng lượng: <?= $product['weight'] ?> kg</p>
                    </div>

                    <!-- Ô nhập số lượng -->
                    <div class="quantity-box">
                        <label for="quantity">Số lượng:</label>
                        <input type="number" id="quantity" name="quantity" min="1" max="<?= $product['stock_quantity'] ?>" value="1">
                    </div>

                    <p class="total-price">
                        Tổng tiền: <span id="total"><?= number_format($product['price'], 0, ',', '.') ?></span> đ
                    </p>

                    <!-- Nút thao tác -->
                    <div class="actions">
                        <form method="post" action="cart_add.php">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <input type="hidden" name="quantity" id="cartQuantity" value="1">
                            <button class="btn-cart" type="submit">🛒 Thêm vào giỏ</button>
                        </form>

                        <form method="post" action="checkout_now.php">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <input type="hidden" name="quantity" id="buyQuantity" value="1">
                            <button class="btn-buy" type="submit">⚡ Mua ngay</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mô tả chi tiết -->
            <?php if (!empty($product['long_description'])): ?>
                <div class="product-long-desc">
                    <h3>Mô tả chi tiết</h3>
                    <p><?= nl2br(htmlspecialchars($product['long_description'])) ?></p>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p>Không tìm thấy sản phẩm.</p>
        <?php endif; ?>
    </div>

    <script>
    const qtyInput = document.getElementById('quantity');
    const totalEl = document.getElementById('total');
    const priceEl = document.getElementById('unit-price');
    const cartQty = document.getElementById('cartQuantity');
    const buyQty = document.getElementById('buyQuantity');

    qtyInput.addEventListener('input', () => {
        const price = parseFloat(priceEl.dataset.price);
        const qty = Math.max(1, parseInt(qtyInput.value) || 1);
        const total = price * qty;
        totalEl.textContent = total.toLocaleString('vi-VN');
        cartQty.value = qty;
        buyQty.value = qty;
    });
    </script>
