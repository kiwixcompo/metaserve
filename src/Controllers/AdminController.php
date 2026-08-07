<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Models/Admin.php';
require_once __DIR__ . '/../Models/Settings.php';

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

    public function addStaffUser($data) {
        if ($this->adminModel->createStaffUser($data)) {
            $_SESSION['success_msg'] = "Staff account created successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to create staff account. The email might already be in use.";
        }
        header("Location: " . BASE_URL . "admin/index.php?tab=users");
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

    public function bulkUsersAction($postData) {
        if (empty($postData['user_ids']) || empty($postData['bulk_action'])) {
            $_SESSION['error_msg'] = "Please select at least one user and an action.";
            header("Location: " . BASE_URL . "admin/index.php?tab=users");
            exit();
        }

        $action = $postData['bulk_action'];
        $userIds = $postData['user_ids'];
        $optionalData = ($action === 'change_password') ? ($postData['new_password'] ?? null) : null;

        if ($this->adminModel->bulkUsersAction($action, $userIds, $optionalData)) {
            $_SESSION['success_msg'] = "Successfully applied action to " . count($userIds) . " user(s).";
        } else {
            $_SESSION['error_msg'] = "Failed to apply bulk action.";
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

    public function updateSettings($postData, $fileData) {
        $settingsModel = new Settings();
        
        // Handle text fields
        $fields = [
            'contact_admin_phone', 'contact_tech_phone', 
            'paystack_public_key', 'paystack_secret_key',
            'social_facebook', 'social_twitter', 'social_instagram', 'social_linkedin'
        ];
        foreach ($fields as $field) {
            if (isset($postData[$field])) {
                // Do not overwrite paystack keys with empty strings (since they are masked in the UI)
                if (($field === 'paystack_public_key' || $field === 'paystack_secret_key') && $postData[$field] === '') {
                    continue;
                }
                $settingsModel->updateSetting($field, $postData[$field]);
            }
        }

        // Handle file uploads for slider images
        $uploadDir = UPLOAD_DIR . 'slider/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $imageFields = ['hero_image', 'slider_image_1', 'slider_image_2', 'slider_image_3'];
        foreach ($imageFields as $field) {
            if (isset($fileData[$field]) && $fileData[$field]['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $fileData[$field]['tmp_name'];
                $fileName = time() . '_' . basename($fileData[$field]['name']);
                $destPath = $uploadDir . $fileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    // Save relative path to DB
                    $relativePath = 'uploads/slider/' . $fileName;
                    $settingsModel->updateSetting($field, $relativePath);
                }
            }
        }

        $_SESSION['success_msg'] = "System settings updated successfully.";
        header("Location: " . BASE_URL . "admin/index.php?tab=settings");
        exit();
    }

    public function deleteEnrollment($id) {
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("DELETE FROM enrollments WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success_msg'] = "Student un-enrolled successfully.";
        header("Location: " . BASE_URL . "admin/index.php?tab=enrollments");
        exit();
    }

    public function bulkChangeCourse($postData) {
        if (empty($postData['enrollment_ids']) || empty($postData['new_course_id'])) {
            $_SESSION['error_msg'] = "Please select at least one enrollment and a new course.";
            header("Location: " . BASE_URL . "admin/index.php?tab=enrollments");
            exit();
        }

        $db = new Database();
        $conn = $db->getConnection();

        // Get the programme_id for the new course to keep pricing aligned
        $stmt = $conn->prepare("SELECT programme_id FROM courses WHERE id = ?");
        $stmt->execute([$postData['new_course_id']]);
        $prog_id = $stmt->fetchColumn();

        $ids = implode(',', array_map('intval', $postData['enrollment_ids']));
        $stmt = $conn->prepare("UPDATE enrollments SET course_id = ?, programme_id = ? WHERE id IN ($ids)");
        $stmt->execute([$postData['new_course_id'], $prog_id]);

        $_SESSION['success_msg'] = "Successfully updated " . count($postData['enrollment_ids']) . " enrollments to the new course.";
        header("Location: " . BASE_URL . "admin/index.php?tab=enrollments");
        exit();
    }
}

// Router Logic
if (isset($_GET['action'])) {
    $controller = new AdminController();
    
    if ($_GET['action'] === 'add_staff' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->addStaffUser($_POST);
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
    elseif ($_GET['action'] === 'delete_enrollment' && isset($_GET['id'])) {
        $controller->deleteEnrollment($_GET['id']);
    }
    elseif ($_GET['action'] === 'bulk_change_course' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->bulkChangeCourse($_POST);
    }
    elseif ($_GET['action'] === 'bulk_users_action' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->bulkUsersAction($_POST);
    }
    elseif ($_GET['action'] === 'update_settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->updateSettings($_POST, $_FILES);
    }
}
