<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Read the schema file
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    
    if (!$sql) {
        die("Could not read database/schema.sql file.");
    }
    
    // Execute the SQL commands
    $conn->exec($sql);
    
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; background: #f9f9f9;'>";
    echo "<h1 style='color: #28a745;'>✅ Database Installation Successful!</h1>";
    echo "<p>All required tables (roles, users, departments, programmes, courses, etc.) have been successfully created on the live server.</p>";
    echo "<hr>";
    echo "<p><strong>Next Step:</strong> You need to create the default Admin and Test accounts.</p>";
    echo "<a href='seed_users.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Seed Default Users</a>";
    echo "<p style='margin-top: 20px; color: #dc3545; font-size: 14px;'>⚠️ <strong>IMPORTANT:</strong> For security reasons, please delete this file (install_db.php) and seed_users.php from the server after you are done.</p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #f5c6cb; border-radius: 8px; background: #f8d7da; color: #721c24;'>";
    echo "<h1>❌ Database Installation Failed</h1>";
    echo "<p><strong>Error Details:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
