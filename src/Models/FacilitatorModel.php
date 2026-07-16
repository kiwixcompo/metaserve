<?php
// src/Models/FacilitatorModel.php
require_once __DIR__ . '/../../config/database.php';

class FacilitatorModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get all students taking a specific course (either explicitly enrolled in the course, or enrolled in the parent programme)
    public function getStudentsForCourse($course_id, $facilitator_id) {
        // Ensure this facilitator is actually assigned to this course
        $check = "SELECT id FROM facilitator_courses WHERE course_id = ? AND facilitator_id = ?";
        $stmt = $this->conn->prepare($check);
        $stmt->execute([$course_id, $facilitator_id]);
        if ($stmt->rowCount() == 0) return []; // Not authorized

        // Get the parent programme of the course
        $progStmt = $this->conn->prepare("SELECT programme_id FROM courses WHERE id = ?");
        $progStmt->execute([$course_id]);
        $prog_id = $progStmt->fetchColumn();

        if (!$prog_id) return [];

        // Fetch students
        // Students are taking this course if they are enrolled in the course explicitly (course_id matches)
        // OR if they are enrolled in the programme (course_id IS NULL)
        $query = "
            SELECT 
                u.id as user_id, 
                u.first_name, 
                u.last_name, 
                u.email,
                u.reg_number,
                e.id as enrollment_id,
                a.id as assessment_id,
                a.score,
                a.remarks
            FROM enrollments e
            JOIN users u ON e.user_id = u.id
            LEFT JOIN assessments a ON (a.enrollment_id = e.id AND a.course_id = ?)
            WHERE e.course_id = ?
              AND e.status IN ('active', 'completed')
            ORDER BY u.last_name ASC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$course_id, $course_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourseDetails($course_id) {
        $stmt = $this->conn->prepare("SELECT c.*, p.name as programme_name FROM courses c JOIN programmes p ON c.programme_id = p.id WHERE c.id = ?");
        $stmt->execute([$course_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveAssessment($enrollment_id, $course_id, $facilitator_id, $score, $remarks) {
        // Check if an assessment already exists
        $check = "SELECT id FROM assessments WHERE enrollment_id = ? AND course_id = ?";
        $stmt = $this->conn->prepare($check);
        $stmt->execute([$enrollment_id, $course_id]);

        if ($score === '') $score = null;

        if ($stmt->rowCount() > 0) {
            // Update
            $update = "UPDATE assessments SET score = ?, remarks = ?, graded_at = CURRENT_TIMESTAMP WHERE enrollment_id = ? AND course_id = ?";
            $ustmt = $this->conn->prepare($update);
            return $ustmt->execute([$score, $remarks, $enrollment_id, $course_id]);
        } else {
            // Insert
            $insert = "INSERT INTO assessments (enrollment_id, course_id, facilitator_id, score, remarks, graded_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
            $istmt = $this->conn->prepare($insert);
            return $istmt->execute([$enrollment_id, $course_id, $facilitator_id, $score, $remarks]);
        }
    }
}
