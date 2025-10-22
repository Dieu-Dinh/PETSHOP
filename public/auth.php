<?php
// Simple public endpoint to forward auth requests to the controller.
require_once __DIR__ . '/../app/controllers/AuthController.php';

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$controller = new AuthController();

switch ($action) {
    case 'login':
        $controller->login();
        break;
    case 'register':
        $controller->register();
        break;
    case 'logout':
        $controller->logout();
        break;
    case 'showLogin':
        $controller->showLoginForm();
        break;
    case 'showRegister':
        $controller->showRegisterForm();
        break;
    default:
        // If no action, redirect to public login page (relative path)
        header('Location: login.php');
        exit;
}
