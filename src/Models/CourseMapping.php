<?php
// src/Models/CourseMapping.php
require_once __DIR__ . '/../../config/database.php';

class CourseMapping {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Fetch all departments to populate the frontend select dropdown
    public function getDepartments() {
        $query = "SELECT id, name, faculty FROM departments ORDER BY faculty ASC, name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Intelligent mapping query: Fetch courses linked to a department
    public function getSuggestedCourses($department_id) {
        // We join mapping_rules with courses and programmes to get full details
        $query = "
            SELECT 
                c.id as course_id, 
                c.name as course_name, 
                c.course_code, 
                c.description,
                p.name as programme_name,
                p.cost,
                m.priority_level
            FROM mapping_rules m
            JOIN courses c ON m.course_id = c.id
            JOIN programmes p ON c.programme_id = p.id
            WHERE m.department_id = :department_id
            AND p.is_active = 1
            ORDER BY m.priority_level DESC, c.name ASC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':department_id', $department_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Fetch all active courses (if an external candidate or if no mapping exists)
    public function getAllActiveCourses() {
        $query = "
            SELECT 
                c.id as course_id, 
                c.name as course_name, 
                c.course_code,
                p.name as programme_name,
                p.cost
            FROM courses c
            JOIN programmes p ON c.programme_id = p.id
            WHERE p.is_active = 1
            ORDER BY p.name ASC, c.name ASC
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getAllActiveProgrammes() {
        $query = "SELECT id as programme_id, name as programme_name, description, cost, duration_weeks FROM programmes WHERE is_active = 1 ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
