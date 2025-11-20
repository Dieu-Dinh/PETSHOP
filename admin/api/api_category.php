<?php
header("Content-Type: application/json; charset=UTF-8");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

// Load Controller
require_once __DIR__ . "/../controller/manageCategoryController.php";

$controller = new CategoryController();

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'list':
        echo json_encode($controller->list());
        break;

    case 'add':
        $name        = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $active      = $_POST['is_active'] ?? 1;

        echo json_encode(
            $controller->add($name, $description, $active)
        );
        break;

    case 'update':
        $id          = $_POST['id'] ?? null;
        $name        = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $active      = $_POST['is_active'] ?? 1;

        echo json_encode(
            $controller->update($id, $name, $description, $active)
        );
        break;

    case 'delete':
        $id = $_POST['id'] ?? null;

        echo json_encode(
            $controller->delete($id)
        );
        break;

    default:
        echo json_encode([
            'status' => false,
            'msg' => 'Invalid action'
        ]);
        break;
}
