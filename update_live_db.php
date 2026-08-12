<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h1>Applying Database Updates to Live Server...</h1>";

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->beginTransaction();

    // 1. Add reset token columns to users table if they don't exist
    try {
        $conn->query("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL AFTER password_hash, ADD COLUMN reset_expires DATETIME NULL AFTER reset_token");
        echo "<p style='color:green;'>Added password reset columns successfully.</p>";
    } catch (PDOException $e) {
        // Ignore if columns already exist
        echo "<p style='color:orange;'>Password reset columns already exist or skipped.</p>";
    }

    // 2. Update Programmes
    $conn->query("UPDATE programmes SET name = 'Digital Literacy & Computer Appreciation (Mandatory)', cost = 20000 WHERE id = 1");
    $conn->query("UPDATE programmes SET name = 'Professional Upskilling Programme', cost = 40000 WHERE id = 2");
    
    // Ensure both are active
    $conn->query("UPDATE programmes SET is_active = 1 WHERE id IN (1,2)");
    
    echo "<p style='color:green;'>Programmes updated successfully.</p>";
    
    $conn->commit();
    echo "<h2 style='color:green;'>All updates applied! You can safely delete this file (update_live_db.php).</h2>";
    echo "<a href='" . BASE_URL . "login.php'>Go to Login</a>";
    
} catch (Exception $e) {
    $conn->rollBack();
    echo "<h2 style='color:red;'>Failed to update database: " . $e->getMessage() . "</h2>";
}
?>
