<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

/**
 * ProductController
 * Xử lý logic liên quan đến sản phẩm — gọi Model và trả dữ liệu cho View.
 */
class ProductController
{
    private $productModel;
    private $categoryModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }

    /** 
     * Hiển thị tất cả sản phẩm (trang quản trị hoặc danh sách chính)
     */
    public function index()
    {
        $products = $this->productModel->getAllProducts();
        include __DIR__ . '/../../public/views/product_list.php';
    }

    /**
     * Hiển thị chi tiết sản phẩm
     */
    public function show($id)
    {
        $product = $this->productModel->getProductById($id);

        if (!$product) {
            die('❌ Sản phẩm không tồn tại!');
        }

        $relatedProducts = $this->productModel->getRelatedProducts($product['category_id'], $product['id']);

        include __DIR__ . '/../../public/views/product_detail.php';
    }

    /**
     * Tạo sản phẩm mới (xử lý khi submit form thêm)
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'sku' => $_POST['sku'],
                'name' => $_POST['name'],
                'slug' => $_POST['slug'],
                'category_id' => $_POST['category_id'],
                'base_price' => $_POST['base_price'],
                'price' => $_POST['price'],
                'stock_quantity' => $_POST['stock_quantity'],
                'status' => $_POST['status'] ?? 'inactive'
            ];

            if ($this->productModel->createProduct($data)) {
                header('Location: index.php?controller=product&action=index');
                exit;
            } else {
                echo "❌ Thêm sản phẩm thất bại.";
            }
        } else {
            $categories = $this->categoryModel->getAllCategories();
            include __DIR__ . '/../../public/views/product_form.php';
        }
    }

    /**
     * Cập nhật sản phẩm
     */
    public function edit($id)
    {
        $product = $this->productModel->getProductById($id);

        if (!$product) {
            die('Sản phẩm không tồn tại.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'],
                'slug' => $_POST['slug'],
                'category_id' => $_POST['category_id'],
                'base_price' => $_POST['base_price'],
                'price' => $_POST['price'],
                'stock_quantity' => $_POST['stock_quantity'],
                'status' => $_POST['status'] ?? 'inactive'
            ];

            if ($this->productModel->updateProduct($id, $data)) {
                header('Location: index.php?controller=product&action=index');
                exit;
            } else {
                echo "❌ Cập nhật sản phẩm thất bại.";
            }
        } else {
            $categories = $this->categoryModel->getAllCategories();
            include __DIR__ . '/../../public/views/product_form.php';
        }
    }

    /**
     * Xóa sản phẩm
     */
    public function delete($id)
    {
        if ($this->productModel->deleteProduct($id)) {
            header('Location: index.php?controller=product&action=index');
            exit;
        } else {
            echo "❌ Xóa thất bại.";
        }
    }

    /**
     * Tìm kiếm sản phẩm theo từ khóa
     */
    public function search()
    {
        $keyword = $_GET['q'] ?? '';
        $products = $this->productModel->searchProducts($keyword);
        include __DIR__ . '/../../public/views/product_search.php';
    }
}
