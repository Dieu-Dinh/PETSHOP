<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table = "users";

    public function __construct() {
        // Use the shared $pdo from config/database.php
        $this->conn = $GLOBALS['pdo'] ?? null;
    }

    // 🔹 Đăng ký người dùng mới
    public function register($email, $password, $first_name, $last_name, $phone = null) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $query = "INSERT INTO {$this->table} 
                  (email, password_hash, first_name, last_name, phone, is_active)
                  VALUES (:email, :password_hash, :first_name, :last_name, :phone, 1)";

    if (!$this->conn) return false;
    $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $passwordHash);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':phone', $phone);

        return $stmt->execute();
    }

    // 🔹 Đăng nhập
    public function login($email, $password) {
        $query = "SELECT * FROM {$this->table} WHERE email = :email AND is_active = 1 LIMIT 1";
    if (!$this->conn) return false;
    $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Cập nhật thời gian đăng nhập
            $this->updateLastLogin($user['id']);
            return $user;
        }
        return false;
    }

    // 🔹 Cập nhật thời gian đăng nhập
    private function updateLastLogin($id) {
        $query = "UPDATE {$this->table} SET last_login_at = NOW() WHERE id = :id";
    if (!$this->conn) return false;
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    }

    // 🔹 Tìm user theo ID
    public function findById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 Kiểm tra email đã tồn tại
    public function existsByEmail($email) {
        $query = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email";
    if (!$this->conn) return false;
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
    }

    // 🔹 Cập nhật thông tin hồ sơ
    public function updateProfile($id, $first_name, $last_name, $phone, $profile_avatar = null) {
        $query = "UPDATE {$this->table}
                  SET first_name = :first_name,
                      last_name = :last_name,
                      phone = :phone,
                      profile_avatar = :profile_avatar,
                      updated_at = NOW()
                  WHERE id = :id";

        if (!$this->conn) return false;
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':profile_avatar', $profile_avatar);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }
}
