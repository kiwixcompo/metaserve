<?php
// Simple deployment script to pull from GitHub

$token = 'metaserve_deploy_2026';

if (!isset($_GET['token']) || $_GET['token'] !== $token) {
    header("HTTP/1.0 403 Forbidden");
    die("Access Denied");
}

echo "<pre>\n";
echo "Starting deployment...\n";

// Execute git pull
// 2>&1 redirects stderr to stdout so we can see the full output
$output = shell_exec('git pull origin main 2>&1');
echo htmlspecialchars($output);

echo "\nRunning database migrations...\n";
try {
    require_once __DIR__ . '/config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    
    // Ensure email column allows NULL
    $conn->exec("ALTER TABLE `users` MODIFY COLUMN `email` VARCHAR(150) NULL");
    echo "✓ Checked and updated `users.email` to allow NULL.\n";

    // Ensure email_verified column exists
    $checkCol = $conn->query("SHOW COLUMNS FROM users LIKE 'email_verified'");
    if ($checkCol->rowCount() == 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER email");
        $conn->exec("ALTER TABLE users ADD COLUMN verification_token VARCHAR(255) DEFAULT NULL AFTER email_verified");
        $conn->exec("UPDATE users SET email_verified = 1 WHERE role_id = 1 OR role_id = 2");
        echo "✓ Added email_verified and verification_token columns.\n";
    }

    // Ensure is_active column exists
    $checkActive = $conn->query("SHOW COLUMNS FROM users LIKE 'is_active'");
    if ($checkActive->rowCount() == 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER verification_token");
        echo "✓ Added is_active column.\n";
    }
} catch (Exception $e) {
    echo "Migration warning/error: " . $e->getMessage() . "\n";
}

echo "\nDeployment finished.\n";
echo "</pre>";
?>
