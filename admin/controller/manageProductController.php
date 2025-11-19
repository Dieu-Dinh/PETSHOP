<?php
// Product model is in app/models
require_once __DIR__ . '/../../app/models/Product.php';

require_once __DIR__ . '/../../app/models/Product.php';

class ManageProductController {
    private $productModel;

    public function __construct() {
        $this->productModel = new Product();
    }

    public function getAllProducts() {
        return $this->productModel->getAllProducts();
    }

    public function deleteProduct($id) {
        return $this->productModel->deleteProduct($id);
    }

    public function toggleStatus($id, $status) {
        return $this->productModel->toggleStatus($id, $status);
    }

    public function updateProduct($id, $data) {
        return $this->productModel->updateProduct($id, $data);
    }

    public function createProduct($data) {
        return $this->productModel->createProduct($data);
    }
}


// --- Xử lý AJAX ---
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $controller = new ManageProductController();

    switch ($_GET['action']) {
        case 'list':
            $data = $controller->getAllProducts();
            echo json_encode(['status'=>'success','data'=>$data]);
            break;

        case 'delete':
            $id = (int)($_GET['id'] ?? 0);
            $ok = $controller->deleteProduct($id);
            echo json_encode(['status'=>$ok?'success':'error']);
            break;

        case 'toggleStatus':
            $id = (int)($_GET['id'] ?? 0);
            $status = $_GET['status'] ?? 'disabled';
            $ok = $controller->toggleStatus($id,$status);
            echo json_encode(['status'=>$ok?'success':'error']);
            break;

        case 'create':
            $data = json_decode(file_get_contents('php://input'),true);
            $ok = $controller->createProduct($data);
            echo json_encode(['status'=>$ok?'success':'error']);
            break;

        case 'update':
            $id = (int)($_GET['id'] ?? 0);
            $data = json_decode(file_get_contents('php://input'),true);
            $ok = $controller->updateProduct($id,$data);
            echo json_encode(['status'=>$ok?'success':'error']);
            break;

        default:
            echo json_encode(['status'=>'error','message'=>'Action không hợp lệ']);
    }
    exit;
}
