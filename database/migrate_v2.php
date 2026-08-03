<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

echo "<h2>Metaserve Database Migration V2 (Paystack & Workflow)</h2>";
echo "<pre>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully...\n";

    // 1. Update Users Table
    echo "Checking `users` table for new workflow fields...\n";
    $checkCol = $conn->query("SHOW COLUMNS FROM users LIKE 'registration_method'");
    if ($checkCol->rowCount() == 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN registration_method ENUM('ONLINE', 'PHYSICAL') DEFAULT 'ONLINE'");
        $conn->exec("ALTER TABLE users ADD COLUMN form_purchased TINYINT(1) DEFAULT 0");
        $conn->exec("ALTER TABLE users ADD COLUMN form_number VARCHAR(50) DEFAULT NULL");
        echo "✓ Added workflow fields (registration_method, form_purchased, form_number) to users.\n";
    } else {
        echo "✓ Workflow fields already exist in users table.\n";
    }

    // 2. Update Enrollments Table
    echo "Checking `enrollments` table for payment fields...\n";
    $checkCol = $conn->query("SHOW COLUMNS FROM enrollments LIKE 'payment_status'");
    if ($checkCol->rowCount() == 0) {
        $conn->exec("ALTER TABLE enrollments ADD COLUMN payment_status ENUM('pending', 'partial', 'paid', 'exempted') DEFAULT 'pending'");
        $conn->exec("ALTER TABLE enrollments ADD COLUMN amount_paid DECIMAL(10,2) DEFAULT 0.00");
        $conn->exec("ALTER TABLE enrollments ADD COLUMN receipt_number VARCHAR(50) DEFAULT NULL");
        $conn->exec("ALTER TABLE enrollments ADD COLUMN form_fee_paid DECIMAL(10,2) DEFAULT 0.00");
        echo "✓ Added payment fields to enrollments.\n";
    } else {
        echo "✓ Payment fields already exist in enrollments table.\n";
    }

    echo "\n<strong style='color: green;'>Migration V2 Complete!</strong>\n";
    echo "You can now safely delete this file if you wish.";

} catch (PDOException $e) {
    echo "\n<strong style='color: red;'>Migration Failed! Error:</strong> " . $e->getMessage();
}

echo "</pre>";
?>
