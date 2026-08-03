<?php
require_once __DIR__ . '/../../config/database.php';

class Admin {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getMetrics() {
        $metrics = [];
        
        $metrics['total_candidates'] = $this->conn->query("SELECT COUNT(*) FROM users WHERE role_id IN (5,6)")->fetchColumn();
        $metrics['tsu_students'] = $this->conn->query("SELECT COUNT(*) FROM users WHERE role_id = 5")->fetchColumn();
        $metrics['external_candidates'] = $this->conn->query("SELECT COUNT(*) FROM users WHERE role_id = 6")->fetchColumn();
        $metrics['online_regs'] = $this->conn->query("SELECT COUNT(*) FROM users WHERE registration_method = 'ONLINE' AND role_id IN (5,6)")->fetchColumn();
        $metrics['physical_regs'] = $this->conn->query("SELECT COUNT(*) FROM users WHERE registration_method = 'PHYSICAL' AND role_id IN (5,6)")->fetchColumn();
        
        // Revenue
        $metrics['form_sales'] = $this->conn->query("SELECT COALESCE(SUM(form_fee_paid), 0) FROM enrollments WHERE payment_status = 'paid'")->fetchColumn();
        
        // Basic Programme (id=1)
        $metrics['basic_tsu'] = $this->conn->query("SELECT COALESCE(SUM(e.amount_paid), 0) FROM enrollments e JOIN users u ON e.user_id = u.id WHERE e.programme_id = 1 AND u.type = 'tsu_student' AND e.payment_status = 'paid'")->fetchColumn();
        $metrics['basic_external'] = $this->conn->query("SELECT COALESCE(SUM(e.amount_paid), 0) FROM enrollments e JOIN users u ON e.user_id = u.id WHERE e.programme_id = 1 AND u.type = 'external' AND e.payment_status = 'paid'")->fetchColumn();
        
        // Professional Programme (id=2)
        $metrics['prof_tsu'] = $this->conn->query("SELECT COALESCE(SUM(e.amount_paid), 0) FROM enrollments e JOIN users u ON e.user_id = u.id WHERE e.programme_id = 2 AND u.type = 'tsu_student' AND e.payment_status = 'paid'")->fetchColumn();
        $metrics['prof_external'] = $this->conn->query("SELECT COALESCE(SUM(e.amount_paid), 0) FROM enrollments e JOIN users u ON e.user_id = u.id WHERE e.programme_id = 2 AND u.type = 'external' AND e.payment_status = 'paid'")->fetchColumn();
        
        $metrics['total_revenue'] = $metrics['form_sales'] + $metrics['basic_tsu'] + $metrics['basic_external'] + $metrics['prof_tsu'] + $metrics['prof_external'];

        return $metrics;
    }

    public function getAllUsers() {
        $query = "SELECT u.id, u.first_name, u.last_name, u.email, u.created_at, r.name as role_name 
                  FROM users u 
                  JOIN roles r ON u.role_id = r.id 
                  ORDER BY u.created_at DESC";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll();
    }

    public function getFacilitators() {
        $query = "SELECT id, first_name, last_name, email, created_at FROM users WHERE role_id = 4 ORDER BY created_at DESC";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll();
    }

    public function getAllEnrollments() {
        $query = "SELECT e.id as enrollment_id, u.first_name, u.last_name, u.email, u.reg_number, 
                         c.name as course_name, c.id as course_id, p.name as prog_name, e.status, e.enrolled_at 
                  FROM enrollments e 
                  JOIN users u ON e.user_id = u.id 
                  LEFT JOIN courses c ON e.course_id = c.id
                  LEFT JOIN programmes p ON e.programme_id = p.id
                  ORDER BY e.enrolled_at DESC";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createFacilitator($data) {
        // Check if email exists
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindValue(':email', $data['email']);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return false;
        }

        $query = "INSERT INTO users (role_id, first_name, last_name, email, password_hash, type) 
                  VALUES (4, :first_name, :last_name, :email, :password_hash, 'external')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':first_name', htmlspecialchars(strip_tags($data['first_name'])));
        $stmt->bindValue(':last_name', htmlspecialchars(strip_tags($data['last_name'])));
        $stmt->bindValue(':email', htmlspecialchars(strip_tags($data['email'])));
        
        $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt->bindValue(':password_hash', $password_hash);
        
        return $stmt->execute();
    }

    public function deleteUser($id) {
        // Hard delete user, but protect Super Admin (role_id = 1)
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = :id AND role_id != 1");
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public function addProgramme($data) {
        $query = "INSERT INTO departments (faculty, name) VALUES (:faculty, :name)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':faculty', htmlspecialchars(strip_tags($data['faculty'])));
        $stmt->bindValue(':name', htmlspecialchars(strip_tags($data['name'])));
        return $stmt->execute();
    }

    public function deleteProgramme($id) {
        $stmt = $this->conn->prepare("DELETE FROM departments WHERE id = :id");
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public function addSkill($data) {
        // Generate a random SKL code
        $code = "SKL-NEW-" . rand(100, 9999);
        $query = "INSERT INTO courses (programme_id, course_code, name, description) VALUES (:programme_id, :course_code, :name, 'Custom added skill')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':programme_id', $data['programme_id']);
        $stmt->bindValue(':course_code', $code);
        $stmt->bindValue(':name', htmlspecialchars(strip_tags($data['name'])));
        return $stmt->execute();
    }

    public function deleteSkill($id) {
        $stmt = $this->conn->prepare("DELETE FROM courses WHERE id = :id");
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}
