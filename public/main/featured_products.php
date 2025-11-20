<section class="products">
    <h2>Sản phẩm nổi bật</h2>
    <div class="product-grid">
        <?php foreach ($products as $p): ?>
            <?php renderProductCard($p); ?>
        <?php endforeach; ?>
    </div>
</section>
