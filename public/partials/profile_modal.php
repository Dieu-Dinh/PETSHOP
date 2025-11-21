<div id="profileModal" class="profile-modal">
    <div class="profile-modal-content">
        <span id="closeProfile" class="close">&times;</span>
        <h2>Thông tin cá nhân</h2>

        <div class="profile-group">
            <label>Họ tên:</label>
            <input type="text" id="pfName" readonly value="<?= htmlspecialchars($currentUser['first_name'] ?? '') ?>">
        </div>

        <div class="profile-group">
            <label>Email:</label>
            <input type="text" id="pfEmail" readonly value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>">
        </div>

        <div class="profile-group">
            <label>Số điện thoại:</label>
            <input type="text" id="pfPhone" readonly value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>">
        </div>

        <div class="profile-group">
            <label>Địa chỉ:</label>
            <input type="text" id="pfAddress" readonly value="<?= htmlspecialchars($currentUser['address'] ?? '') ?>">
        </div>
    </div>
</div>

<link rel="stylesheet" href="assets/css/profile_model.css">

<script src="assets/js/profile_modal.js"></script>
