<?php
// Product model is in app/models
require_once __DIR__ . '/../../app/models/Product.php';

class ManageProductController
{
    private $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    /** Lấy tất cả sản phẩm (cho admin) */
    public function getAllProducts()
    {
        return $this->productModel->getAllProducts();
    }

    /** Xóa sản phẩm */
    public function deleteProduct($id)
    {
        return $this->productModel->deleteProduct($id);
    }

    /** Bật / tắt trạng thái sản phẩm */
    public function toggleStatus($id, $status)
    {
        return $this->productModel->toggleStatus($id, $status);
    }

    /** Cập nhật sản phẩm */
    public function updateProduct($id, $data)
    {
        return $this->productModel->updateProduct($id, $data);
    }

    /** Thêm mới sản phẩm */
    public function createProduct($data)
    {
        return $this->productModel->createProduct($data);
    }
}

// --- Xử lý AJAX ---
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $controller = new ManageProductController();

    switch ($_GET['action']) {
        case 'getAll':
            echo json_encode($controller->getAllProducts());
            break;

        case 'delete':
            $id = (int)$_GET['id'];
            $ok = $controller->deleteProduct($id);
            echo json_encode(['success' => $ok]);
            break;

        case 'toggleStatus':
            $id = (int)$_GET['id'];
            $status = $_GET['status'] ?? 'disabled';
            $ok = $controller->toggleStatus($id, $status);
            echo json_encode(['success' => $ok]);
            break;
    }
    exit;
}
