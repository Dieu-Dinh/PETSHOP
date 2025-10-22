<?php
require_once __DIR__ . '/app/config/database.php'; // Gọi file DB của bạn (nơi có $pdo)

try {
    // Thông tin admin mặc định
    $email = 'admin@petshop.local';
    $password = 'admin123'; // Mật khẩu mặc định
    $first_name = 'Admin';
    $last_name = 'Petshop';
    $role = 'admin';

    // Kiểm tra trùng email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        echo "⚠️ Tài khoản admin đã tồn tại: $email";
        exit;
    }

    // Mã hóa mật khẩu
    $hash = password_hash($password, PASSWORD_BCRYPT);

    // Tạo tài khoản admin
    $insert = $pdo->prepare("
        INSERT INTO users (email, password_hash, first_name, last_name, role, is_active, created_at)
        VALUES (:email, :hash, :first_name, :last_name, :role, 1, NOW())
    ");

    $ok = $insert->execute([
        ':email' => $email,
        ':hash' => $hash,
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':role' => $role
    ]);

    if ($ok) {
        echo "✅ Tạo tài khoản admin thành công!\n";
        echo "Email: $email\n";
        echo "Mật khẩu: $password\n";
    } else {
        echo "❌ Lỗi khi tạo admin.";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage();
}




// Email: admin@petshop.local
// Mật khẩu: admin123