<?php
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Kiểm tra thông tin bắt buộc
    if ($name === '' || $email === '' || $message === '') {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ.";
    } else {
        $to = "dieudinh2005@gmail.com";
        $mailSubject = "📩 Liên hệ từ khách hàng: $name";

        $mailContent = "Họ tên: $name\n";
        $mailContent .= "Email: $email\n";
        $mailContent .= "Chủ đề: $subject\n";
        $mailContent .= "-------------------------\n";
        $mailContent .= "Nội dung:\n$message\n";

        // Headers an toàn
        $headers = "From: no-reply@yourdomain.com\r\n";  // email server của bạn
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // Gửi email
        if (@mail($to, $mailSubject, $mailContent, $headers)) {
            $success = "Gửi thành công! Chúng tôi sẽ phản hồi sớm nhất.";
        } else {
            $lastError = error_get_last();
            $error = "Không thể gửi email. Vui lòng thử lại sau.";
            if ($lastError) {
                $error .= " Lỗi: " . $lastError['message'];
            }
        }
    }
}
?>

<link rel="stylesheet" href="assets/css/contact.css" />

<main class="main-content">
    <section class="contact-container">
        <h2>Liên hệ với chúng tôi</h2>
        <p class="contact-note">Nếu bạn cần hỗ trợ, hãy liên hệ trực tiếp:</p>

        <div class="contact-info">
            <p><strong>Email:</strong> <a href="mailto:dieudinh2005@gmail.com">dieudinh2005@gmail.com</a></p>
            <p><strong>Số điện thoại:</strong> <a href="tel:0358493756">0358 493 756</a></p>
        </div>

        <?php if ($success): ?>
            <p class="success-msg"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="error-msg"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form action="" method="POST" class="contact-form">
            <div class="form-group">
                <label>Họ và tên *</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($name ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Chủ đề</label>
                <input type="text" name="subject" value="<?= htmlspecialchars($subject ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Nội dung *</label>
                <textarea name="message" required><?= htmlspecialchars($message ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn-submit">Gửi liên hệ</button>
        </form>
    </section>
</main>
