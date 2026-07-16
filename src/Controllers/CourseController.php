<?php
// src/Controllers/CourseController.php
require_once __DIR__ . '/../Models/CourseMapping.php';

class CourseController {
    private $mappingModel;

    public function __construct() {
        $this->mappingModel = new CourseMapping();
    }

    /**
     * This function is designed to be called via AJAX (Vanilla JS or jQuery)
     * during the multi-step registration process when a user selects their Academic Department.
     */
    public function fetchSuggestedCourses($department_id) {
        header('Content-Type: application/json');

        $programmes = $this->mappingModel->getAllActiveProgrammes();

        if (empty($department_id)) {
            // For external candidates or empty selection, pull all active courses
            $allCourses = $this->mappingModel->getAllActiveCourses();
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'courses' => $allCourses,
                    'programmes' => $programmes
                ],
                'message' => 'All available courses and programmes loaded.'
            ]);
            return;
        }

        // Fetch intelligent suggestions based on DB mapping
        $suggestedCourses = $this->mappingModel->getSuggestedCourses($department_id);

        if (count($suggestedCourses) > 0) {
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'courses' => $suggestedCourses,
                    'programmes' => $programmes
                ],
                'message' => 'Suggested courses and programmes loaded successfully.'
            ]);
        } else {
            // Fallback: If no specific mapping exists, load all courses
            $allCourses = $this->mappingModel->getAllActiveCourses();
            echo json_encode([
                'status' => 'info',
                'data' => [
                    'courses' => $allCourses,
                    'programmes' => $programmes
                ],
                'message' => 'No specific mapped courses found. Showing all available options.'
            ]);
        }
    }
}

// Simple API router for AJAX calls (e.g., accessed via POST to CourseController.php?action=get_suggestions)
if (isset($_GET['action']) && $_GET['action'] == 'get_suggestions' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller = new CourseController();
    $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
    $controller->fetchSuggestedCourses($department_id);
}
