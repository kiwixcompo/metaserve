<?php
// src/Controllers/CoordinatorController.php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Models/Coordinator.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure only Programme Coordinator has access
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $coordinatorModel = new Coordinator();
    $action = $_POST['action'];

    if ($action === 'assign_course') {
        $facilitator_id = intval($_POST['facilitator_id']);
        $course_ids = $_POST['course_ids'] ?? [];

        if (empty($facilitator_id) || empty($course_ids)) {
            $_SESSION['error_msg'] = "Please select a facilitator and at least one course.";
        } else {
            $successCount = 0;
            foreach ($course_ids as $cid) {
                if ($coordinatorModel->assignCourse($facilitator_id, intval($cid))) {
                    $successCount++;
                }
            }
            if ($successCount > 0) {
                $_SESSION['success_msg'] = "Successfully assigned {$successCount} course(s).";
            } else {
                $_SESSION['error_msg'] = "Courses were already assigned or an error occurred.";
            }
        }
        header("Location: " . BASE_URL . "coordinator/index.php?tab=assignments");
        exit();
    }

    if ($action === 'remove_assignment') {
        $assignment_id = intval($_POST['assignment_id']);
        if ($coordinatorModel->removeAssignment($assignment_id)) {
            $_SESSION['success_msg'] = "Assignment revoked successfully.";
        } else {
            $_SESSION['error_msg'] = "Failed to revoke assignment.";
        }
        header("Location: " . BASE_URL . "coordinator/index.php?tab=assignments");
        exit();
    }
}
