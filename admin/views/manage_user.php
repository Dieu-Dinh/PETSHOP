<?php
require_once __DIR__ . '/../../app/models/User.php';
$userModel = new User();
$users = $userModel->getAllUsers() ?: [];
?>

<section class="manage-section">
    <link rel="stylesheet" href="assets/css/manageUser.css">

    <h2>👤 Quản lý người dùng</h2>

    <div class="user-header">
        <input type="text" id="user-search" placeholder="🔍 Tìm kiếm người dùng...">

        <div class="user-actions">
            <button id="btn-add" class="btn-action add">➕ Thêm</button>
            <button id="btn-refresh" class="btn-action refresh">🔄 Làm mới</button>
        </div>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Họ</th>
                <th>Tên</th>
                <th>Vai trò</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['first_name']) ?></td>
                    <td><?= htmlspecialchars($user['last_name']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><?= $user['is_active'] ? '✅' : '❌' ?></td>
                    <td>
                        <button class="btn-edit" data-id="<?= $user['id'] ?>">Sửa</button>
                        <button class="btn-delete" data-id="<?= $user['id'] ?>">Xóa</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
