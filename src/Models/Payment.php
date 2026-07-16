<?php
require_once __DIR__ . '/../../config/database.php';

class Payment {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Log a new payment attempt (Paystack or Offline Teller)
    public function createPayment($data) {
        $query = "INSERT INTO payments (enrollment_id, user_id, amount, reference, method, teller_path, status)
                  VALUES (:enrollment_id, :user_id, :amount, :reference, :method, :teller_path, :status)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':enrollment_id', $data['enrollment_id']);
        $stmt->bindValue(':user_id', $data['user_id']);
        $stmt->bindValue(':amount', $data['amount']);
        $stmt->bindValue(':reference', $data['reference']);
        $stmt->bindValue(':method', $data['method']);
        
        // Teller path is null if using Paystack
        $teller_path = isset($data['teller_path']) ? $data['teller_path'] : null;
        $stmt->bindValue(':teller_path', $teller_path);
        
        $stmt->bindValue(':status', $data['status']);

        return $stmt->execute();
    }

    // Update payment status (e.g. after Paystack Webhook or Admin Teller Approval)
    public function updatePaymentStatus($reference, $status, $approvedBy = null) {
        $query = "UPDATE payments SET status = :status";
        
        if ($status === 'approved') {
            $query .= ", paid_at = CURRENT_TIMESTAMP, approved_by = :approved_by";
        }
        $query .= " WHERE reference = :reference";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':reference', $reference);
        
        if ($status === 'approved') {
            $stmt->bindValue(':approved_by', $approvedBy);
        }

        if($stmt->execute()) {
             // Business Logic: If payment is approved, automatically mark enrollment as 'active'
             if ($status === 'approved') {
                 $q2 = "UPDATE enrollments SET status = 'active' WHERE id = (SELECT enrollment_id FROM payments WHERE reference = :ref LIMIT 1)";
                 $stmt2 = $this->conn->prepare($q2);
                 $stmt2->bindValue(':ref', $reference);
                 $stmt2->execute();
             }
             return true;
        }
        return false;
    }

    // Fetch all pending manual teller uploads for the Admin to review
    public function getPendingTellers() {
        $query = "SELECT p.*, u.first_name, u.last_name, u.email, pr.name as programme_name
                  FROM payments p
                  JOIN users u ON p.user_id = u.id
                  JOIN enrollments e ON p.enrollment_id = e.id
                  JOIN programmes pr ON e.programme_id = pr.id
                  WHERE p.method = 'offline_teller' AND p.status = 'pending'
                  ORDER BY p.created_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
