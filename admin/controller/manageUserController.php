<?php
// Correct path to the User model in app/models
require_once __DIR__ . '/../../app/models/User.php';

class ManageUserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function index() {
        return $this->userModel->getAllUsers();
    }

    public function delete($id) {
        return $this->userModel->deleteUser($id);
    }

    public function toggleActive($id, $status) {
        return $this->userModel->toggleActive($id, $status);
    }
}
