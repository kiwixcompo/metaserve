<?php
// src/Models/Coordinator.php
require_once __DIR__ . '/../../config/database.php';

class Coordinator {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get all facilitators
    public function getFacilitators() {
        $query = "SELECT u.id, u.first_name, u.last_name, u.email, u.phone 
                  FROM users u 
                  JOIN roles r ON u.role_id = r.id 
                  WHERE r.name = 'Facilitator' 
                  ORDER BY u.first_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all active courses
    public function getAllCourses() {
        $query = "SELECT c.id, c.course_code, c.name, p.name as programme_name 
                  FROM courses c
                  JOIN programmes p ON c.programme_id = p.id
                  WHERE p.is_active = 1
                  ORDER BY p.name ASC, c.name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all course assignments for all facilitators
    public function getAllAssignments() {
        $query = "SELECT fc.id as assignment_id, u.first_name, u.last_name, c.name as course_name, c.course_code, p.name as programme_name, fc.assigned_at
                  FROM facilitator_courses fc
                  JOIN users u ON fc.facilitator_id = u.id
                  JOIN courses c ON fc.course_id = c.id
                  JOIN programmes p ON c.programme_id = p.id
                  ORDER BY fc.assigned_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Assign a course to a facilitator
    public function assignCourse($facilitator_id, $course_id) {
        // Check if already assigned
        $check = "SELECT id FROM facilitator_courses WHERE facilitator_id = :fid AND course_id = :cid";
        $stmt = $this->conn->prepare($check);
        $stmt->execute(['fid' => $facilitator_id, 'cid' => $course_id]);
        if ($stmt->rowCount() > 0) return false;

        $query = "INSERT INTO facilitator_courses (facilitator_id, course_id) VALUES (:fid, :cid)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute(['fid' => $facilitator_id, 'cid' => $course_id]);
    }

    // Remove an assignment
    public function removeAssignment($assignment_id) {
        $query = "DELETE FROM facilitator_courses WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute(['id' => $assignment_id]);
    }
}
