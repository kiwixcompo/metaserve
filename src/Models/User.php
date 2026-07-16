<?php
// src/Models/User.php
require_once __DIR__ . '/../../config/database.php';

class User {
    private $conn;
    private $table = 'users';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Check if email already exists
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Check if Registration Number already exists
    public function regNumberExists($reg_number) {
        if (empty($reg_number)) return false;
        $query = "SELECT id FROM " . $this->table . " WHERE reg_number = :reg_number LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':reg_number', $reg_number);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Register a new user
    public function register($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (role_id, first_name, last_name, email, password_hash, phone, type, reg_number) 
                  VALUES (:role_id, :first_name, :last_name, :email, :password_hash, :phone, :type, :reg_number)";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize & Bind (using bindValue for direct values to prevent pass-by-reference notices)
        $stmt->bindValue(':role_id', $data['role_id']);
        $stmt->bindValue(':first_name', htmlspecialchars(strip_tags($data['first_name'])));
        $stmt->bindValue(':last_name', htmlspecialchars(strip_tags($data['last_name'])));
        $stmt->bindValue(':email', htmlspecialchars(strip_tags($data['email'])));
        
        // Hash the password securely using bcrypt
        $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt->bindValue(':password_hash', $password_hash);
        
        $stmt->bindValue(':phone', htmlspecialchars(strip_tags($data['phone'])));
        $stmt->bindValue(':type', $data['type']);
        
        // Handle Reg Number based on type
        $reg_number = (isset($data['reg_number']) && !empty($data['reg_number'])) ? htmlspecialchars(strip_tags($data['reg_number'])) : null;
        $stmt->bindValue(':reg_number', $reg_number);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Login a user
    public function login($email, $password) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            // Verify password hash
            if (password_verify($password, $row['password_hash'])) {
                return $row; // Success: return user data
            }
        }
        return false; // Failure
    }
}
