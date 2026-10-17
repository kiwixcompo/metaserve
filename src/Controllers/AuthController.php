<?php
// src/Controllers/AuthController.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function handleRegister($postData, $isAccountManager = false) {
        $errors = [];
        
        // Split full_name
        $nameParts = explode(' ', trim($postData['full_name'] ?? ''));
        $postData['first_name'] = $nameParts[0] ?? '';
        unset($nameParts[0]);
        $postData['last_name'] = !empty($nameParts) ? implode(' ', $nameParts) : '';

        // 1. Basic Validation
        if (empty($postData['first_name']) || empty($postData['password']) || empty($postData['type'])) {
            $errors[] = "Please fill in all required fields.";
        }

        $email = trim($postData['email'] ?? '');
        if (!$isAccountManager && empty($email)) {
            $errors[] = "Email address is required.";
        }

        if (!empty($email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Invalid email format.";
            } elseif ($this->userModel->emailExists($email)) {
                $errors[] = "Email is already registered.";
            }
        } else {
            $postData['email'] = null;
        }

        // 2. TSU Student vs External Candidate Logic
        $role_id = 6; // Default to external
        if ($postData['type'] === 'tsu_student') {
            $role_id = 5;
        } elseif ($postData['type'] === 'external') {
            $role_id = 6;
        } else {
            $errors[] = "Invalid user type selected.";
        }

        // Generate Registration ID (e.g. MSV-TSU-XXXX)
        $prefix = ($postData['type'] === 'tsu_student') ? 'MSV-TSU-' : 'MSV-EXT-';
        $postData['registration_id'] = $prefix . date('Y') . '-' . strtoupper(substr(uniqid(), -5));

        // Generate Reg Number (old logic for reg_number fallback)
        if ($postData['type'] === 'tsu_student' && empty($postData['reg_number'])) {
            // TSU students must provide matric number. If not, error.
            $errors[] = "Matriculation number is required for TSU students.";
        } elseif ($postData['type'] === 'external') {
            // Generate reg number for external if we want to keep it or just leave null
            $postData['reg_number'] = null;
        }

        // 3. Return errors if any
        if (!empty($errors)) {
            return ['status' => 'error', 'errors' => $errors];
        }

        // Verification token & email verified state
        if ($isAccountManager) {
            $postData['email_verified'] = 1;
            $postData['verification_token'] = null;
        } else {
            $postData['email_verified'] = 0;
            $postData['verification_token'] = bin2hex(random_bytes(16));
        }
        $postData['role_id'] = $role_id;
        
        // 4. Register the user
        $userId = $this->userModel->register($postData);

        if ($userId) {
            // Send Verification Email only if public registration with email provided
            if (!$isAccountManager && !empty($postData['email'])) {
                require_once __DIR__ . '/../Models/EmailService.php';
                $emailService = new \App\Models\EmailService();
                $emailService->sendVerificationEmail($postData['email'], $postData['first_name'], $postData['verification_token']);
            }

            return ['status' => 'success', 'redirect' => BASE_URL . 'register.php?success=registered', 'user_id' => $userId, 'registration_id' => $postData['registration_id']];
        } else {
            return ['status' => 'error', 'errors' => ['Failed to register. Please try again.']];
        }
    }

    public function handleLogin($identifier, $password) {
        $identifier = trim($identifier ?? '');
        if (empty($identifier) || empty($password)) {
            return ['status' => 'error', 'message' => 'Email/Registration ID and password are required.'];
        }

        $userData = $this->userModel->login($identifier, $password);

        if ($userData) {
            // Check if account is active
            if (isset($userData['is_active']) && $userData['is_active'] == 0) {
                return ['status' => 'error', 'message' => 'Your account has been deactivated. Please contact the administrator.'];
            }

            // Check if email is verified (only enforced if an email address exists)
            if (!empty($userData['email']) && isset($userData['email_verified']) && $userData['email_verified'] == 0) {
                return ['status' => 'error', 'message' => 'Please verify your email address before logging in. Check your inbox for the verification link.'];
            }

            $this->setSession($userData);
            return ['status' => 'success', 'redirect' => $this->getDashboardRoute($userData['role_id'])];
        } else {
            return ['status' => 'error', 'message' => 'Invalid credentials.'];
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
