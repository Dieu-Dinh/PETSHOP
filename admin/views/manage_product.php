<section class="manage-section">
    <link rel="stylesheet" href="assets/css/manageProduct.css">

    <h2>📦 Quản lý sản phẩm</h2>

    <!-- Header: tìm kiếm & thao tác -->
    <div class="product-header">
        <input type="text" id="product-search" placeholder="🔍 Tìm kiếm sản phẩm...">

        <div class="product-actions">
            <button id="btn-add" class="btn-action add">➕ Thêm</button>
            <button id="btn-refresh" class="btn-action refresh">🔄 Làm mới</button>
        </div>
    </div>

    <!-- Table -->
    <table class="admin-table">
        <thead>
            <tr>
                <th>Ảnh</th>
                <th>Tên</th>
                <th>SKU</th>
                <th>Danh mục</th>
                <th>Giá</th>
                <th>Tồn kho</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>

        <tbody id="product-table-body">
            <tr>
                <td colspan="8" class="loading">Đang tải dữ liệu...</td>
            </tr>
        </tbody>
    </table>

    <!-- JS xử lý -->
    <script src="assets/js/manage_product.js"></script>
</section>
