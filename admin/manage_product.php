<?php
// include the admin controller
require_once __DIR__ . '/controller/manageProductController.php';
$controller = new ManageProductController();
$products = $controller->getAllProducts();
?>
<link rel="stylesheet" href="assets/css/manageProduct.css">
<div class="content-header">
    <h2>🛒 Quản lý sản phẩm</h2>
    <button id="btn-add-product" class="btn-primary">+ Thêm sản phẩm</button>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Ảnh</th>
            <th>Tên sản phẩm</th>
            <th>Giá</th>
            <th>Danh mục</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($products as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['id']) ?></td>
            <td>
                <?php if ($p['image']): ?>
                    <img src="<?= htmlspecialchars($p['image']) ?>" width="50" height="50" style="border-radius:8px;object-fit:cover;">
                <?php else: ?>
                    <span style="color:#888;">Không có</span>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= number_format($p['price'], 0, ',', '.') ?>đ</td>
            <td><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
            <td>
                <span class="<?= $p['status'] === 'active' ? 'status-active' : 'status-disabled' ?>">
                    <?= $p['status'] === 'active' ? 'Hoạt động' : 'Ẩn' ?>
                </span>
            </td>
            <td>
                <button class="btn-edit" data-id="<?= $p['id'] ?>">Sửa</button>
                <button class="btn-delete" data-id="<?= $p['id'] ?>">Xóa</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
            if (confirm("Bạn có chắc muốn xóa sản phẩm này không?")) {
            // call controller in admin/controller via relative path
            fetch(`controller/manageProductController.php?action=delete&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                });
        }
    });
});
</script>
