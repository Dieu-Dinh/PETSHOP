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

            // Detect AJAX / fetch requests (X-Requested-With) or JSON accept
            $isAjax = false;
            $hdr = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
            if (strtolower($hdr) === 'xmlhttprequest') $isAjax = true;
            $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
            if (strpos($accept, 'application/json') !== false) $isAjax = true;

            if ($user) {
                // If role is admin, store admin identity in a separate session cookie
                if (($user['role'] ?? '') === 'admin') {
                    // Preserve current public session name and id
                    $publicName = session_name();
                    $publicId = session_id();
                    // write and close current public session
                    session_write_close();

                    // Start admin session under a different cookie name
                    session_name('ADMINSESSID');
                    session_start();
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ];
                    // ensure admin session saved
                    session_write_close();

                    // restore public session
                    session_name($publicName);
                    session_id($publicId);
                    session_start();
                } else {
                    // Regular user: keep identity in the public session
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ];

                    // Merge any guest session cart into user's cart
                    require_once __DIR__ . '/../models/Cart.php';
                    $cart = new Cart();
                    if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $pid => $item) {
                            $qty = $item['quantity'] ?? 1;
                            $cart->addToCart($pid, $qty);
                        }
                        unset($_SESSION['cart']);
                    }
                    $sessId = session_id();
                    if ($sessId) {
                        $cart->mergeSessionCartToUser($sessId, $user['id']);
                    }
                }

                // 🧭 Phân quyền điều hướng / response
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    $resp = ['success' => true, 'user' => ['id' => $user['id'], 'email' => $user['email'], 'role' => $user['role']]];
                    // suggest redirect for admin users so modal login can navigate directly
                    if (($user['role'] ?? '') === 'admin') {
                        $resp['redirect'] = '/PETSHOP/admin/index.php';
                    } else {
                        $resp['redirect'] = 'index.php';
                    }
                    echo json_encode($resp);
                    exit;
                } else {
                    if ($user['role'] === 'admin') {
                        header('Location: /PETSHOP/admin/index.php');
                    } else {
                        header('Location: index.php');
                    }
                    exit;
                }
            } else {
                // login failed
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(401);
                    echo json_encode(['success' => false, 'message' => 'Email hoặc mật khẩu không đúng.']);
                    exit;
                }

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
    header('Location: index.php');
        exit;
    }
}
