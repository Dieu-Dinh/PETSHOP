<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
        if (session_status() === PHP_SESSION_NONE) {
            session_start(); // Bắt đầu session nếu chưa có
        }
    }

    // 🟢 Hiển thị trang đăng nhập
    public function showLoginForm() {
        include __DIR__ . '/../../public/login.php';
    }

    // 🟢 Xử lý đăng nhập (phân quyền)
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $user = $this->userModel->login($email, $password);

            if ($user) {
                // Lưu thông tin user vào session
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];

                // 🧭 Phân quyền điều hướng
                if ($user['role'] === 'admin') {
                    // Admin dashboard sits in the admin folder at /PETSHOP/admin/
                    header('Location: /PETSHOP/admin/index.php');
                } else {
                    // Regular users should land on the public index (relative)
                    header('Location: index.php');
                }
                exit;
            } else {
                $error = "Email hoặc mật khẩu không đúng.";
                include __DIR__ . '/../../public/login.php';
            }
        }
    }

    // 🟢 Hiển thị trang đăng ký
    public function showRegisterForm() {
        include __DIR__ . '/../../public/register.php';
    }

    // 🟢 Xử lý đăng ký
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $phone = trim($_POST['phone']);

            // Kiểm tra email trùng
            if ($this->userModel->existsByEmail($email)) {
                $error = "Email đã tồn tại!";
                include __DIR__ . '/../../public/register.php';
                return;
            }

            // Thêm người dùng mới (role mặc định là customer)
            $success = $this->userModel->register($email, $password, $first_name, $last_name, $phone);

            if ($success) {
                header('Location: login.php?registered=1');
                exit;
            } else {
                $error = "Đăng ký thất bại, vui lòng thử lại.";
                include __DIR__ . '/../../public/register.php';
            }
        }
    }

    // 🟢 Đăng xuất
    public function logout() {
    session_destroy();
    // Redirect to public login page (relative to public folder)
    header('Location: login.php');
        exit;
    }
}
