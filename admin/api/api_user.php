<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/models/User.php';

header('Content-Type: application/json');

$userModel = new User();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // 🟢 Lấy danh sách tất cả người dùng
    case 'GET':
        try {
            if (isset($_GET['id'])) {
                $user = $userModel->findById($_GET['id']);
                echo json_encode($user ?: []);
            } else {
                $users = $userModel->getAllUsers();
                echo json_encode($users);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    // 🟡 Thêm người dùng mới
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data || !isset($data['email']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Thiếu thông tin bắt buộc']);
            exit;
        }

        $email = trim($data['email']);
        $password = password_hash($data['password'], PASSWORD_BCRYPT);
        $first_name = trim($data['first_name'] ?? '');
        $last_name = trim($data['last_name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $role = $data['role'] ?? 'customer';

        try {
            // ⚠️ Kiểm tra email đã tồn tại trước khi thêm
            if ($userModel->existsByEmail($email)) {
                http_response_code(409); // 409 = Conflict
                echo json_encode(['success' => false, 'error' => 'Email đã tồn tại trong hệ thống']);
                exit;
            }

            $pdo = $userModel->getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO users (email, password_hash, first_name, last_name, phone, role)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $ok = $stmt->execute([$email, $password, $first_name, $last_name, $phone, $role]);

            echo json_encode(['success' => $ok]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    // 🟠 Cập nhật thông tin người dùng
    case 'PUT':
        // Đọc id từ query string (ví dụ: ?id=6)
        parse_str($_SERVER['QUERY_STRING'] ?? '', $params);
        $id = $params['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu ID người dùng']);
            exit;
        }

        // Đọc dữ liệu JSON từ body
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!$data || !is_array($data)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Không nhận được dữ liệu PUT', 'raw' => $raw]);
            exit;
        }

        try {
            // 🔹 kiểm tra kết nối DB
            if (!isset($userModel->conn) || !$userModel->conn) {
                throw new Exception("Kết nối CSDL không tồn tại trong model User");
            }

            // Tạo danh sách trường cập nhật hợp lệ
            $fields = [];
            $values = [];

            foreach (['first_name', 'last_name', 'phone', 'role', 'is_active'] as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $values[] = $data[$field];
                }
            }

            if (empty($fields)) {
                echo json_encode(['success' => false, 'message' => 'Không có trường nào để cập nhật']);
                exit;
            }

            $values[] = $id;
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $userModel->conn->prepare($sql);
            $ok = $stmt->execute($values);

            echo json_encode(['success' => $ok, 'updated_id' => $id]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    // 🔴 Xóa người dùng
    case 'DELETE':
        parse_str($_SERVER['QUERY_STRING'], $params);
        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Thiếu ID người dùng']);
            exit;
        }

        try {
            $stmt = $userModel->conn->prepare("DELETE FROM users WHERE id = ?"); // ✅ sửa ở đây
            $ok = $stmt->execute([$id]);
            echo json_encode(['success' => $ok]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Phương thức không được hỗ trợ']);
}
