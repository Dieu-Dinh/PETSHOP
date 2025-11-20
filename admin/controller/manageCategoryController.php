<?php
/**
 * CategoryController
 * Xử lý CRUD danh mục dành cho Admin.
 */

require_once __DIR__ . '/../../app/models/Category.php';

class CategoryController
{
    private $pdo;

    public function __construct()
    {
        require_once __DIR__ . '/../../app/config/database.php';
        $this->pdo = $GLOBALS['pdo'];

        if (!$this->pdo) {
            die(json_encode(['status' => false, 'msg' => 'Không thể kết nối Database']));
        }
    }

    /**
     * Lấy toàn bộ danh mục
     */
    public function list()
    {
        $sql = "SELECT id, name, description, is_active
                FROM categories
                ORDER BY id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $data = $stmt->fetchAll();

        return [
            'status' => true,
            'data'   => $data
        ];
    }

    /**
     * Thêm danh mục mới
     */
    public function add($name, $description, $active)
    {
        if (empty($name)) {
            return ['status' => false, 'msg' => 'Tên danh mục không được để trống'];
        }

        // Kiểm tra trùng tên
        $check = $this->pdo->prepare("SELECT id FROM categories WHERE name = :name");
        $check->execute([':name' => $name]);

        if ($check->fetch()) {
            return ['status' => false, 'msg' => 'Tên danh mục đã tồn tại'];
        }

        $sql = "INSERT INTO categories (name, description, is_active)
                VALUES (:name, :description, :active)";

        $stmt = $this->pdo->prepare($sql);

        $ok = $stmt->execute([
            ':name'        => $name,
            ':description' => $description,
            ':active'      => (int)$active
        ]);

        return [
            'status' => $ok,
            'msg' => $ok ? 'Thêm danh mục thành công' : 'Thêm thất bại'
        ];
    }

    /**
     * Cập nhật danh mục
     */
    public function update($id, $name, $description, $active)
    {
        if (empty($name)) {
            return ['status' => false, 'msg' => 'Tên danh mục không được để trống'];
        }

        $sql = "UPDATE categories
                SET name = :name,
                    description = :description,
                    is_active = :active
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);

        $ok = $stmt->execute([
            ':id'          => (int)$id,
            ':name'        => $name,
            ':description' => $description,
            ':active'      => (int)$active
        ]);

        return [
            'status' => $ok,
            'msg' => $ok ? 'Cập nhật thành công' : 'Cập nhật thất bại'
        ];
    }

    /**
     * Xóa danh mục
     */
    public function delete($id)
    {
        $sql = "DELETE FROM categories WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        $ok = $stmt->execute([':id' => (int)$id]);

        return [
            'status' => $ok,
            'msg' => $ok ? 'Xóa thành công' : 'Xóa thất bại'
        ];
    }
}
