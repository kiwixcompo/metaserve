<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h1>Applying Course Updates to Live Server...</h1>";

$db = new Database();
$conn = $db->getConnection();

try {
    // 1. Move all courses to Professional (programme_id = 2) EXCEPT the first 3
    $conn->query("UPDATE courses SET programme_id = 2 WHERE id NOT IN (1, 2, 3)");
    echo "<p style='color:green;'>Moved existing courses to Professional category.</p>";
    
    // 2. Check if the 4th mandatory course exists, if not insert it
    $stmt = $conn->query("SELECT id FROM courses WHERE name = 'Computer Hardware and Peripheral Devices'");
    if ($stmt->rowCount() == 0) {
        $conn->query("INSERT INTO courses (name, programme_id) VALUES ('Computer Hardware and Peripheral Devices', 1)");
        echo "<p style='color:green;'>Inserted 'Computer Hardware and Peripheral Devices' as Mandatory.</p>";
    } else {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $conn->query("UPDATE courses SET programme_id = 1 WHERE id = " . (int)$row['id']);
        echo "<p style='color:green;'>Updated 'Computer Hardware and Peripheral Devices' to Mandatory.</p>";
    }

    echo "<h2 style='color:green;'>All updates applied! You can safely delete this file (update_live_db.php).</h2>";
    echo "<a href='" . BASE_URL . "login.php'>Go to Login</a>";
    
} catch (Exception $e) {
    echo "<h2 style='color:red;'>Failed to update database: " . $e->getMessage() . "</h2>";
}
?>
