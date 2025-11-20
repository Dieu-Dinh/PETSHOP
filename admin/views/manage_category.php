<section class="manage-section">
    <div class="header">
        <h2>Quản lý danh mục sản phẩm</h2>
        <button id="btn-add" class="btn btn-primary">+ Thêm danh mục</button>
    </div>

    <table class="table-category" id="table-category">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên danh mục</th>
                <th>Mô tả</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</section>

<!-- MODAL -->
<div class="modal" id="modal-category">
    <div class="modal-content">
        <h3 id="modal-title">Thêm danh mục</h3>

        <input type="hidden" id="cat-id">

        <label>Tên danh mục</label>
        <input type="text" id="cat-name" class="form-control">

        <label>Mô tả</label>
        <textarea id="cat-description" class="form-control"></textarea>

        <label>Trạng thái</label>
        <select id="cat-active" class="form-control">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>

        <div class="modal-footer">
            <button id="btn-save" class="btn btn-success">Lưu</button>
            <button id="btn-close" class="btn btn-secondary">Đóng</button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="assets/css/manageCategory.css">
<script src="assets/js/manage_category.js"></script>
