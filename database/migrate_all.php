<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

echo "<h2>Metaserve Database Catch-up Migration</h2>";
echo "<pre>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully...\n";

    // 1. Check for `dob` and other fields from users_registration_migration
    echo "Checking `users` table for extended registration fields...\n";
    $checkCol = $conn->query("SHOW COLUMNS FROM users LIKE 'dob'");
    if ($checkCol->rowCount() == 0) {
        $conn->exec("ALTER TABLE `users`
            ADD COLUMN `dob` DATE NULL AFTER `last_name`,
            ADD COLUMN `gender` ENUM('Male', 'Female') NULL AFTER `dob`,
            ADD COLUMN `nationality` VARCHAR(100) NULL AFTER `gender`,
            ADD COLUMN `state_of_origin` VARCHAR(100) NULL AFTER `nationality`,
            ADD COLUMN `lga` VARCHAR(100) NULL AFTER `state_of_origin`,
            ADD COLUMN `alt_phone` VARCHAR(20) NULL AFTER `phone`,
            ADD COLUMN `department_id` INT NULL AFTER `reg_number`,
            ADD COLUMN `level` VARCHAR(20) NULL AFTER `department_id`,
            ADD COLUMN `highest_qualification` VARCHAR(100) NULL AFTER `level`,
            ADD COLUMN `occupation` VARCHAR(100) NULL AFTER `highest_qualification`,
            ADD COLUMN `faculty_interest` VARCHAR(150) NULL AFTER `occupation`,
            ADD COLUMN `how_did_you_hear` VARCHAR(150) NULL AFTER `faculty_interest`,
            ADD COLUMN `why_join` TEXT NULL AFTER `how_did_you_hear`,
            ADD COLUMN `registration_id` VARCHAR(50) NULL UNIQUE AFTER `why_join`;");
        echo "✓ Added extended registration fields to users table.\n";
    } else {
        echo "✓ Extended registration fields already exist.\n";
    }

    // 2. Check for email verification fields
    echo "Checking `users` table for email verification and status fields...\n";
    $checkCol = $conn->query("SHOW COLUMNS FROM users LIKE 'email_verified'");
    if ($checkCol->rowCount() == 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER email");
        $conn->exec("ALTER TABLE users ADD COLUMN verification_token VARCHAR(255) DEFAULT NULL AFTER email_verified");
        $conn->exec("UPDATE users SET email_verified = 1 WHERE role_id = 1 OR role_id = 2");
        echo "✓ Added email verification fields to users table.\n";
    } else {
        echo "✓ Email verification fields already exist.\n";
    }

    $checkCol = $conn->query("SHOW COLUMNS FROM users LIKE 'is_active'");
    if ($checkCol->rowCount() == 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER verification_token");
        echo "✓ Added is_active status field to users table.\n";
    } else {
        echo "✓ is_active field already exists.\n";
    }
    
    // 3. Check for settings unique key (if they haven't run migrate.php before)
    // Create settings table if not exists
    $sqlSettings = "CREATE TABLE IF NOT EXISTS `settings` (
      `id` int NOT NULL AUTO_INCREMENT,
      `setting_key` varchar(100) NOT NULL,
      `setting_value` text,
      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $conn->exec($sqlSettings);

    echo "\n<strong style='color: green;'>All missing columns have been successfully added!</strong>\n";
    echo "You can now safely test the registration page.";

} catch (PDOException $e) {
    echo "\n<strong style='color: red;'>Migration Failed! Error:</strong> " . $e->getMessage();
}

echo "</pre>";
?>
