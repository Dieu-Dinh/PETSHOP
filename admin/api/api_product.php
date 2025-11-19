<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../app/models/Product.php';

$product = new Product();

/** Helper để trả JSON nhanh */
function response($status, $message, $data = null)
{
    echo json_encode([
        "status" => $status,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

// Đường dẫn root của ảnh
define('PRODUCT_IMAGE_URL', '/PETSHOP/public/images/products/'); // dùng để trả cho UI
// admin/api is located at admin/api -> public is two levels up (PETSHOP/public)
define('PRODUCT_IMAGE_DIR', __DIR__ . '/../../public/images/products/');

/**
 * Normalize image value from DB into a usable URL for the admin UI.
 * Handles cases where DB stores:
 * - a bare filename (e.g. 'file.jpg')
 * - a relative path like 'images/products/file.jpg'
 * - an absolute path starting with '/'
 * - a full URL starting with 'http(s)://'
 */
function normalizeImageUrl($val)
{
    if (empty($val)) return '/PETSHOP/admin/assets/images/no_image.png';
    $v = trim($val);
    if (preg_match('#^https?://#i', $v)) return $v;
    if (strpos($v, '/') === 0) return $v; // root-relative or already absolute

    // If value contains a path segment like images/products/..., extract the basename
    // so we don't accidentally duplicate directories when prefixing PRODUCT_IMAGE_URL.
    if (stripos($v, 'images/') !== false) {
        $base = basename($v);
        return PRODUCT_IMAGE_URL . $base;
    }

    // treat as bare filename
    return PRODUCT_IMAGE_URL . $v;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;

/**
 * ============================
 *          ROUTES
 * ============================
 */
if ($method === 'GET') {

    /** 1️⃣ Lấy danh sách sản phẩm */
    if ($action === 'list') {
        $data = $product->getAllProducts();

        // Thêm đường dẫn đầy đủ cho ảnh
        foreach ($data as &$item) {
            $item['image'] = normalizeImageUrl($item['image'] ?? null);
        }

        response("success", "Product list loaded", $data);
    }

    /** 2️⃣ Lấy chi tiết sản phẩm */
    if ($action === 'detail') {
        $id = $_GET['id'] ?? null;
        if (!$id) response("error", "Missing product ID");

        $item = $product->getProductById($id);
        if (!$item) response("error", "Product not found");

        $item['images'] = $product->getProductImages($id);

        // Thêm đường dẫn ảnh
        $item['image'] = normalizeImageUrl($item['image'] ?? null);

        response("success", "Product detail", $item);
    }

    response("error", "Invalid GET action");
}

/**
 * ============================
 *           POST API
 * ============================
 */
if ($method === 'POST') {
    $input = $_POST; // dữ liệu form submit

    /** 3️⃣ Tạo sản phẩm mới */
    if ($action === 'create') {
        if (empty($input['name']) || empty($input['sku'])) {
            response("error", "Missing required fields: name, sku");
        }

        $input['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $input['name'])));

        // =========================
        // Upload ảnh nếu có
        // =========================
        $origName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $origName = $_FILES['image']['name'];
            $ext = pathinfo($origName, PATHINFO_EXTENSION);
            $newFileName = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

            if (!is_dir(PRODUCT_IMAGE_DIR)) {
                mkdir(PRODUCT_IMAGE_DIR, 0755, true);
            }

            $uploadPath = rtrim(PRODUCT_IMAGE_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $newFileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                // store only filename in products.image, but store full relative path in product_images
                $input['image'] = $newFileName;
                $input['image_relative'] = 'images/products/' . $newFileName;
            } else {
                error_log("[api_product] move_uploaded_file failed. tmp=" . ($_FILES['image']['tmp_name'] ?? '') . " target=" . $uploadPath);
                response("error", "Upload ảnh thất bại (move failed)");
            }
        }

        $productId = $product->createProduct([
            ':sku'            => $input['sku'],
            ':name'           => $input['name'],
            ':slug'           => $input['slug'],
            ':category_id'    => $input['category_id'] ?? null,
            ':base_price'     => $input['base_price'] ?? 0,
            ':price'          => $input['price'] ?? 0,
            ':stock_quantity' => $input['stock_quantity'] ?? 0,
            ':status'         => $input['status'] ?? 'active',
            ':image'          => $input['image'] ?? null,
        ]);

        if ($productId) {
            // If an image was uploaded, also insert to product_images (mark primary)
            if (!empty($input['image'])) {
                $rel = $input['image_relative'] ?? ('images/products/' . $input['image']);
                $product->addProductImage($productId, $rel, 1, $origName);
            }
            $resp = ['id' => $productId];
            if (!empty($input['image'])) {
                $resp['image_url'] = normalizeImageUrl($input['image_relative'] ?? ('images/products/' . $input['image']));
            }
            response("success", "Product created successfully", $resp);
        }

        response("error", "Failed to create product");
    }

    /** 4️⃣ Cập nhật sản phẩm */
    if ($action === 'update') {
        $id = $_POST['id'] ?? null;
        if (!$id) response("error", "Missing product ID");

        unset($input['id']);

        if (!empty($input['name'])) {
            $input['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $input['name'])));
        }

        // =========================
        // Upload ảnh nếu có
        // =========================
        $origName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $origName = $_FILES['image']['name'];
            $ext = pathinfo($origName, PATHINFO_EXTENSION);
            $newFileName = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

            if (!is_dir(PRODUCT_IMAGE_DIR)) {
                mkdir(PRODUCT_IMAGE_DIR, 0755, true);
            }

            $uploadPath = rtrim(PRODUCT_IMAGE_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $newFileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $input['image'] = $newFileName;
                $input['image_relative'] = 'images/products/' . $newFileName;
            } else {
                error_log("[api_product] move_uploaded_file failed. tmp=" . ($_FILES['image']['tmp_name'] ?? '') . " target=" . $uploadPath);
                response("error", "Upload ảnh thất bại (move failed)");
            }
        }

        // Ensure we only pass DB columns to updateProduct. Remove helper keys
        // like `image_relative` which do not exist in `products` table.
        if (isset($input['image_relative'])) unset($input['image_relative']);

        $success = $product->updateProduct($id, $input);

        if ($success) {
            // If a new image was uploaded, record it in product_images and keep products.image updated
            if (!empty($input['image'])) {
                $rel = $input['image_relative'] ?? ('images/products/' . $input['image']);
                $product->addProductImage($id, $rel, 1, $origName);
            }
            $resp = ['id' => $id];
            if (!empty($input['image'])) $resp['image_url'] = normalizeImageUrl($input['image_relative'] ?? ('images/products/' . $input['image']));
            response("success", "Product updated", $resp);
        } else {
            response("error", "Update failed");
        }
    }

    /** 5️⃣ Xóa sản phẩm */
    if ($action === 'delete') {
        $id = $input['id'] ?? null;
        if (!$id) response("error", "Missing product ID");

        $success = $product->deleteProduct($id);

        if ($success) response("success", "Product deleted");
        response("error", "Delete failed");
    }

    /** 6️⃣ Đổi trạng thái (active / disabled) */
    if ($action === 'status') {
        $id = $input['id'] ?? null;
        $status = $input['status'] ?? null;

        if (!$id || !$status) response("error", "Missing fields");

        $success = $product->toggleStatus($id, $status);

        if ($success) response("success", "Status updated");
        response("error", "Failed to update status");
    }

    response("error", "Invalid POST action");
}

/** Nếu không khớp GET/POST */
response("error", "Invalid request method");
