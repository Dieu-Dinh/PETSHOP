<?php
require_once __DIR__ . '/../../app/models/User.php';
header('Content-Type: application/json');

$userModel = new User();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // 🟢 Lấy danh sách tất cả người dùng
    case 'GET':
        try {
            $users = $userModel->getAllUsers();
            echo json_encode($users);
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
            echo json_encode(['error' => 'Thiếu thông tin bắt buộc']);
            exit;
        }

        $email = trim($data['email']);
        $password = password_hash($data['password'], PASSWORD_BCRYPT);
        $first_name = trim($data['first_name'] ?? '');
        $last_name = trim($data['last_name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $role = $data['role'] ?? 'customer';

        try {
            $pdo = $userModel->pdo; // Dùng kết nối DB của model
            $stmt = $pdo->prepare("
                INSERT INTO users (email, password_hash, first_name, last_name, phone, role)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $ok = $stmt->execute([$email, $password, $first_name, $last_name, $phone, $role]);
            echo json_encode(['success' => $ok]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    // 🟠 Cập nhật thông tin người dùng
    case 'PUT':
        parse_str($_SERVER['QUERY_STRING'], $params);
        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Thiếu ID người dùng']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Thiếu dữ liệu PUT']);
            exit;
        }

        try {
            $fields = [];
            $values = [];

            foreach (['first_name', 'last_name', 'phone', 'role', 'is_active'] as $field) {
                if (isset($data[$field])) {
                    $fields[] = "$field = ?";
                    $values[] = $data[$field];
                }
            }

            if (empty($fields)) {
                http_response_code(400);
                echo json_encode(['error' => 'Không có trường nào để cập nhật']);
                exit;
            }

            $values[] = $id;
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $userModel->pdo->prepare($sql);
            $ok = $stmt->execute($values);

            echo json_encode(['success' => $ok]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
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
            $stmt = $userModel->pdo->prepare("DELETE FROM users WHERE id = ?");
            $ok = $stmt->execute([$id]);
            echo json_encode(['success' => $ok]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    // 🚫 Nếu dùng phương thức không hợp lệ
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Phương thức không được hỗ trợ']);
}
