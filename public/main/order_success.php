<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>

<div class="container">
    <div class="order-success card" style="padding:24px; margin:28px auto; max-width:760px;">
        <h2>Đặt hàng thành công</h2>
        <p>Đơn hàng của bạn đã được tạo. Mã đơn: <strong><?php echo isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '—'; ?></strong></p>
        <p>Cám ơn bạn đã mua hàng. Chúng tôi sẽ liên hệ để xác nhận đơn.</p>
        <p><a href="index.php">Quay về trang chủ</a> • <a href="index.php?page=orders">Xem đơn hàng của tôi</a></p>
    </div>
</div>
