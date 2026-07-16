<?php
require_once __DIR__ . '/../../config/database.php';

class Report {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Retrieve Total Revenue from all approved payments
    public function getTotalRevenue() {
        $query = "SELECT SUM(amount) as total_revenue FROM payments WHERE status = 'approved'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total_revenue'] ? $result['total_revenue'] : 0.00;
    }

    // Retrieve Total Active Enrollments across all programmes
    public function getTotalActiveEnrollments() {
        $query = "SELECT COUNT(*) as total_enrollments FROM enrollments WHERE status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total_enrollments'];
    }

    // Retrieve detailed statistics grouped by Programme for the Admin Dashboard
    public function getEnrollmentStatsByProgramme() {
        $query = "SELECT p.name as programme_name, COUNT(e.id) as student_count, SUM(py.amount) as revenue
                  FROM programmes p
                  LEFT JOIN enrollments e ON p.id = e.programme_id AND e.status = 'active'
                  LEFT JOIN payments py ON e.id = py.enrollment_id AND py.status = 'approved'
                  GROUP BY p.id
                  ORDER BY student_count DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
