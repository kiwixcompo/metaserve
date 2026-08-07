<?php
require_once __DIR__ . '/../config/database.php';

echo "<h2>Starting Database Migration v3 (Email Verification)</h2>";

$db = new Database();
$conn = $db->getConnection();

try {
    // 1. Add email verification columns to users table
    echo "Checking users table for email verification columns...<br>";
    $stmt = $conn->query("SHOW COLUMNS FROM users LIKE 'email_verified'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER email");
        echo "Added 'email_verified' column.<br>";
    } else {
        echo "'email_verified' column already exists.<br>";
    }

    $stmt = $conn->query("SHOW COLUMNS FROM users LIKE 'verification_token'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN verification_token VARCHAR(255) DEFAULT NULL AFTER email_verified");
        echo "Added 'verification_token' column.<br>";
    } else {
        echo "'verification_token' column already exists.<br>";
    }

    // Optional: set existing super admins to verified so they don't get locked out
    $conn->exec("UPDATE users SET email_verified = 1 WHERE role_id = 1 OR role_id = 2");
    echo "Set existing Admin accounts to verified to prevent lockout.<br>";

    echo "<h3 style='color: green;'>Migration Completed Successfully!</h3>";
    echo "<a href='../index.php'>Go Back</a>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Migration Failed: " . $e->getMessage() . "</h3>";
}
