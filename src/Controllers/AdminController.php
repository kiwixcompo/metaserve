<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Models/Admin.php';

// Security: Ensure only Super Admin (Role 1) can access this controller
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

class AdminController {
    private $adminModel;

    public function __construct() {
        $this->adminModel = new Admin();
    }

    public function addFacilitator($data) {
        if ($this->adminModel->createFacilitator($data)) {
            $_SESSION['success_msg'] = "Facilitator account created successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to create facilitator. The email might already be in use.";
        }
        header("Location: " . BASE_URL . "admin/index.php?tab=facilitators");
        exit();
    }

    public function deleteUser($id) {
        if ($this->adminModel->deleteUser($id)) {
            $_SESSION['success_msg'] = "User permanently deleted.";
        } else {
            $_SESSION['error_msg'] = "Failed to delete user. Cannot delete Super Admins.";
        }
        header("Location: " . BASE_URL . "admin/index.php?tab=users");
        exit();
    }

    public function addProgramme($data) {
        if ($this->adminModel->addProgramme($data)) {
            $_SESSION['success_msg'] = "Programme added successfully.";
        } else {
            $_SESSION['error_msg'] = "Failed to add programme.";
        }
        header("Location: " . BASE_URL . "admin/index.php?tab=programmes");
        exit();
    }

    public function deleteProgramme($id) {
        if ($this->adminModel->deleteProgramme($id)) {
            $_SESSION['success_msg'] = "Programme deleted successfully.";
        } else {
            $_SESSION['error_msg'] = "Failed to delete programme.";
        }
        header("Location: " . BASE_URL . "admin/index.php?tab=programmes");
        exit();
    }

    public function addSkill($data) {
        if ($this->adminModel->addSkill($data)) {
            $_SESSION['success_msg'] = "ICT Skill added successfully.";
        } else {
            $_SESSION['error_msg'] = "Failed to add skill.";
        }
        header("Location: " . BASE_URL . "admin/index.php?tab=skills");
        exit();
    }

    public function deleteSkill($id) {
        if ($this->adminModel->deleteSkill($id)) {
            $_SESSION['success_msg'] = "ICT Skill deleted successfully.";
        } else {
            $_SESSION['error_msg'] = "Failed to delete skill.";
        }
        header("Location: " . BASE_URL . "admin/index.php?tab=skills");
        exit();
    }
}

// Router Logic
if (isset($_GET['action'])) {
    $controller = new AdminController();
    
    if ($_GET['action'] === 'add_facilitator' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->addFacilitator($_POST);
    }
    elseif ($_GET['action'] === 'delete_user' && isset($_GET['id'])) {
        $controller->deleteUser($_GET['id']);
    }
    elseif ($_GET['action'] === 'add_programme' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->addProgramme($_POST);
    }
    elseif ($_GET['action'] === 'delete_programme' && isset($_GET['id'])) {
        $controller->deleteProgramme($_GET['id']);
    }
    elseif ($_GET['action'] === 'add_skill' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->addSkill($_POST);
    }
    elseif ($_GET['action'] === 'delete_skill' && isset($_GET['id'])) {
        $controller->deleteSkill($_GET['id']);
    }
}
