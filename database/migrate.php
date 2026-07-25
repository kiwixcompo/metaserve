<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

echo "<h2>Metaserve Database Migration Script</h2>";
echo "<pre>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully...\n";

    // 1. Create settings table
    echo "Checking `settings` table...\n";
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
    
    // Populate default settings
    $sqlInsertSettings = "INSERT IGNORE INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES
    (1, 'slider_image_1', 'assets/images/slider1.jpg'),
    (2, 'slider_image_2', 'assets/images/slider2.jpg'),
    (3, 'slider_image_3', 'assets/images/slider3.jpg'),
    (4, 'contact_admin_phone', '09055875069, 0806 486 6016'),
    (5, 'contact_tech_phone', '08082768855'),
    (6, 'paystack_public_key', 'pk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'),
    (7, 'paystack_secret_key', 'sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');";
    $conn->exec($sqlInsertSettings);
    echo "✓ Settings table verified and populated.\n";

    // 2. Check course_id in enrollments
    echo "Checking `course_id` column in `enrollments` table...\n";
    $checkCol = $conn->query("SHOW COLUMNS FROM enrollments LIKE 'course_id'");
    if ($checkCol->rowCount() == 0) {
        $conn->exec("ALTER TABLE enrollments ADD COLUMN course_id int DEFAULT NULL AFTER programme_id");
        $conn->exec("ALTER TABLE enrollments ADD CONSTRAINT fk_enrollment_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE");
        echo "✓ Added `course_id` column to enrollments.\n";
    } else {
        echo "✓ `course_id` column already exists.\n";
    }

    // 3. Fix UNIQUE KEY user_id on enrollments
    echo "Checking constraints and indexes on `enrollments`...\n";
    
    // Check if the old unique index `user_id` exists
    $checkIndex = $conn->query("SHOW INDEX FROM enrollments WHERE Key_name = 'user_id'");
    if ($checkIndex->rowCount() > 0) {
        echo "Found outdated unique index 'user_id', attempting to remove...\n";
        // Drop foreign key first
        try {
            $conn->exec("ALTER TABLE enrollments DROP FOREIGN KEY enrollments_ibfk_1");
        } catch(PDOException $e) {
            echo "  - FK enrollments_ibfk_1 not found or already dropped. Proceeding...\n";
        }
        
        // Drop old index
        $conn->exec("ALTER TABLE enrollments DROP INDEX user_id");
        
        // Add new basic index for user_id to support the FK
        $conn->exec("ALTER TABLE enrollments ADD INDEX idx_user_id (user_id)");
        
        // Re-add foreign key
        $conn->exec("ALTER TABLE enrollments ADD CONSTRAINT enrollments_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
        echo "✓ Safely removed old unique constraint and rebuilt foreign key.\n";
    } else {
        echo "✓ Old unique constraint not found or already removed.\n";
    }

    // 4. Add unique_user_course constraint
    $checkUnique = $conn->query("SHOW INDEX FROM enrollments WHERE Key_name = 'unique_user_course'");
    if ($checkUnique->rowCount() == 0) {
        // We might have invalid duplicates if the user managed to mess up their data while the constraint was broken. Ignore them to prevent the script from crashing.
        $conn->exec("ALTER IGNORE TABLE enrollments ADD UNIQUE KEY unique_user_course (user_id, course_id)");
        echo "✓ Added new unique constraint (user_id, course_id).\n";
    } else {
        echo "✓ New unique constraint already exists.\n";
    }

    echo "\n<strong style='color: green;'>Migration Complete! All database structures are up to date.</strong>\n";
    echo "You can now safely delete this file if you wish.";

} catch (PDOException $e) {
    echo "\n<strong style='color: red;'>Migration Failed! Error:</strong> " . $e->getMessage();
}

echo "</pre>";
?>
