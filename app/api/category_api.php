<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Category.php';

// Khởi tạo model
$categoryModel = new Category();

// Lấy danh sách danh mục
$categories = $categoryModel->getActiveCategories();

// Trả về JSON
echo json_encode($categories, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;
