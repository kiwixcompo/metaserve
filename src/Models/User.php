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
                  (role_id, first_name, last_name, email, password_hash, phone, type, reg_number,
                   dob, gender, nationality, state_of_origin, lga, alt_phone, department_id, level,
                   highest_qualification, occupation, faculty_interest, how_did_you_hear, why_join, registration_id, verification_token) 
                  VALUES (:role_id, :first_name, :last_name, :email, :password_hash, :phone, :type, :reg_number,
                   :dob, :gender, :nationality, :state_of_origin, :lga, :alt_phone, :department_id, :level,
                   :highest_qualification, :occupation, :faculty_interest, :how_did_you_hear, :why_join, :registration_id, :verification_token)";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':role_id', $data['role_id']);
        $stmt->bindValue(':first_name', htmlspecialchars(strip_tags($data['first_name'])));
        $stmt->bindValue(':last_name', htmlspecialchars(strip_tags($data['last_name'])));
        $stmt->bindValue(':email', htmlspecialchars(strip_tags($data['email'])));
        $stmt->bindValue(':password_hash', password_hash($data['password'], PASSWORD_BCRYPT));
        $stmt->bindValue(':phone', htmlspecialchars(strip_tags($data['phone'])));
        $stmt->bindValue(':type', $data['type']);
        $stmt->bindValue(':reg_number', $data['reg_number'] ?? null);

        // New fields
        $stmt->bindValue(':dob', $data['dob'] ?? null);
        $stmt->bindValue(':gender', $data['gender'] ?? null);
        $stmt->bindValue(':nationality', htmlspecialchars(strip_tags($data['nationality'] ?? '')));
        $stmt->bindValue(':state_of_origin', htmlspecialchars(strip_tags($data['state_of_origin'] ?? '')));
        $stmt->bindValue(':lga', htmlspecialchars(strip_tags($data['lga'] ?? '')));
        $stmt->bindValue(':alt_phone', htmlspecialchars(strip_tags($data['alt_phone'] ?? '')));
        
        $stmt->bindValue(':department_id', empty($data['department_id']) ? null : $data['department_id']);
        $stmt->bindValue(':level', $data['level'] ?? null);
        $stmt->bindValue(':highest_qualification', htmlspecialchars(strip_tags($data['highest_qualification'] ?? '')));
        $stmt->bindValue(':occupation', htmlspecialchars(strip_tags($data['occupation'] ?? '')));
        
        $stmt->bindValue(':faculty_interest', htmlspecialchars(strip_tags($data['faculty_interest'] ?? '')));
        $stmt->bindValue(':how_did_you_hear', htmlspecialchars(strip_tags($data['how_did_you_hear'] ?? '')));
        $stmt->bindValue(':why_join', htmlspecialchars(strip_tags($data['why_join'] ?? '')));
        $stmt->bindValue(':registration_id', $data['registration_id'] ?? null);
        $stmt->bindValue(':verification_token', $data['verification_token'] ?? null);

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

    public function verifyEmail($token) {
        $query = "SELECT id, email, first_name, email_verified FROM " . $this->table . " WHERE verification_token = :token LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            
            if ($user['email_verified'] == 1) {
                return 'already_verified';
            }

            // Update user to verified but DO NOT clear token to prevent email scanner false negatives on user click
            $updateQuery = "UPDATE " . $this->table . " SET email_verified = 1 WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':id', $user['id']);
            $updateStmt->execute();
            return $user;
        }
        return false;
    }
    public function getUserById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($data) {
        $updates = [
            "first_name = :first_name",
            "last_name = :last_name",
            "phone = :phone",
            "alt_phone = :alt_phone",
            "email = :email"
        ];
        
        if (!empty($data['password'])) {
            $updates[] = "password_hash = :password_hash";
        }
        
        $query = "UPDATE " . $this->table . " SET " . implode(", ", $updates) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindValue(':id', $data['id']);
        $stmt->bindValue(':first_name', htmlspecialchars(strip_tags($data['first_name'])));
        $stmt->bindValue(':last_name', htmlspecialchars(strip_tags($data['last_name'])));
        $stmt->bindValue(':phone', htmlspecialchars(strip_tags($data['phone'])));
        $stmt->bindValue(':alt_phone', htmlspecialchars(strip_tags($data['alt_phone'] ?? '')));
        $stmt->bindValue(':email', htmlspecialchars(strip_tags($data['email'])));
        
        if (!empty($data['password'])) {
            $stmt->bindValue(':password_hash', password_hash($data['password'], PASSWORD_BCRYPT));
        }
        
        return $stmt->execute();
    }
}
