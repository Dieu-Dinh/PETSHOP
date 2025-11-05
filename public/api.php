<?php
// file này làm trung gian xử lý các yêu cầu API AJAX liên quan đến sản phẩm trong admin panel 
?>

<?php
// Cho phép CORS (tùy chọn, dùng nếu gọi AJAX từ domain khác)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Xử lý preflight request của trình duyệt (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Nhận tên API từ query string (vd: api.php?api=cart&action=add)
$api = $_GET['api'] ?? '';
$api = preg_replace('/[^a-zA-Z0-9_]/', '', $api); // chống chèn đường dẫn độc hại

// Xác định file API thật
$apiFile = __DIR__ . "/../app/api/{$api}_api.php";

// Kiểm tra file có tồn tại không
if (!file_exists($apiFile)) {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => "API '{$api}' không tồn tại."
    ]);
    exit;
}

// Include file API tương ứng
require_once $apiFile;
