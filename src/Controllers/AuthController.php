<?php
// src/Controllers/AuthController.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function handleRegister($postData) {
        $errors = [];
        
        // 1. Basic Validation
        if (empty($postData['first_name']) || empty($postData['last_name']) || empty($postData['email']) || empty($postData['password']) || empty($postData['type'])) {
            $errors[] = "Please fill in all required fields.";
        }

        if (!filter_var($postData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

        if ($this->userModel->emailExists($postData['email'])) {
            $errors[] = "Email is already registered.";
        }

        // 2. TSU Student vs External Candidate Logic
        // Determine Role ID based on type (5 = Student, 6 = External Candidate)
        $role_id = 6; // Default to external
        
        if ($postData['type'] === 'tsu_student') {
            $role_id = 5;
        } elseif ($postData['type'] === 'external') {
            $role_id = 6;
        } else {
            $errors[] = "Invalid user type selected.";
        }

        // Generate Registration Number
        if (empty($errors)) {
            $prefix = ($postData['type'] === 'tsu_student') ? 'MIT/INT/' : 'MIT/EXT/';
            $year = date('Y');
            do {
                $rand = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
                $reg_number = $prefix . $year . '/' . $rand;
            } while ($this->userModel->regNumberExists($reg_number));
            
            $postData['reg_number'] = $reg_number;
        }

        // 3. Return errors if any
        if (!empty($errors)) {
            return ['status' => 'error', 'errors' => $errors];
        }

        $postData['role_id'] = $role_id;
        
        // 4. Register the user
        $userId = $this->userModel->register($postData);

        if ($userId) {
            // Auto login after successful registration
            $userData = $this->userModel->login($postData['email'], $postData['password']);
            $this->setSession($userData);
            return ['status' => 'success', 'redirect' => $this->getDashboardRoute($userData['role_id'])];
        } else {
            return ['status' => 'error', 'errors' => ['Failed to register. Please try again.']];
        }
    }

    public function handleLogin($email, $password) {
        if (empty($email) || empty($password)) {
            return ['status' => 'error', 'message' => 'Email and password are required.'];
        }

        $userData = $this->userModel->login($email, $password);

        if ($userData) {
            $this->setSession($userData);
            return ['status' => 'success', 'redirect' => $this->getDashboardRoute($userData['role_id'])];
        } else {
            return ['status' => 'error', 'message' => 'Invalid email or password.'];
        }
    }

    private function setSession($userData) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['role_id'] = $userData['role_id'];
        $_SESSION['first_name'] = $userData['first_name'];
        $_SESSION['last_name'] = $userData['last_name'];
        $_SESSION['email'] = $userData['email'];
        $_SESSION['type'] = $userData['type'];
        $_SESSION['reg_number'] = $userData['reg_number'] ?? null;
    }

    // RBAC: Route to specific dashboard based on role ID
    private function getDashboardRoute($role_id) {
        switch ($role_id) {
            case 1: // Super Admin
                return BASE_URL . 'admin/';
            case 2: // Head of Admin/Accounts
                return BASE_URL . 'accounts/';
            case 3: // Programme Coordinator
                return BASE_URL . 'coordinator/';
            case 4: // Facilitator
                return BASE_URL . 'facilitator/';
            case 5: // Student (TSU)
            case 6: // External Candidate
                return BASE_URL . 'student/';
            case 7: // University Management
                return BASE_URL . 'management/';
            default:
                return BASE_URL;
        }
    }
    
    public function logout() {
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

// Simple router for GET direct actions
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $controller = new AuthController();
    $controller->logout();
}
