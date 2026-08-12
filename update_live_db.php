<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h1>Applying Course Updates to Live Server...</h1>";

$db = new Database();
$conn = $db->getConnection();

try {
    // We will dynamically ensure the two programmes exist and are named correctly.
    $stmt = $conn->query("SELECT id FROM programmes ORDER BY id ASC");
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($ids) == 0) {
        // No programmes exist? Insert both.
        $conn->query("INSERT INTO programmes (name, cost, is_active) VALUES ('Digital Literacy & Computer Appreciation (Mandatory)', 20000, 1)");
        $prog1 = $conn->lastInsertId();
        
        $conn->query("INSERT INTO programmes (name, cost, is_active) VALUES ('Professional Upskilling Programme', 40000, 1)");
        $prog2 = $conn->lastInsertId();
    } else if (count($ids) == 1) {
        // Only one exists. Update it to be Mandatory, insert the second as Professional.
        $prog1 = $ids[0];
        $conn->query("UPDATE programmes SET name = 'Digital Literacy & Computer Appreciation (Mandatory)', cost = 20000 WHERE id = $prog1");
        
        $conn->query("INSERT INTO programmes (name, cost, is_active) VALUES ('Professional Upskilling Programme', 40000, 1)");
        $prog2 = $conn->lastInsertId();
    } else {
        // Two or more exist. Use the first two.
        $prog1 = $ids[0];
        $prog2 = $ids[1];
        $conn->query("UPDATE programmes SET name = 'Digital Literacy & Computer Appreciation (Mandatory)', cost = 20000 WHERE id = $prog1");
        $conn->query("UPDATE programmes SET name = 'Professional Upskilling Programme', cost = 40000 WHERE id = $prog2");
    }

    echo "<p style='color:green;'>Programmes ensured and renamed successfully. (Mandatory ID: $prog1, Professional ID: $prog2)</p>";

    // 1. Move all courses to Professional EXCEPT the first 3
    $conn->query("UPDATE courses SET programme_id = $prog2 WHERE id NOT IN (1, 2, 3)");
    echo "<p style='color:green;'>Moved existing courses to Professional category.</p>";
    
    // 2. Check if the 4th mandatory course exists, if not insert it
    $stmt = $conn->query("SELECT id FROM courses WHERE name = 'Computer Hardware and Peripheral Devices'");
    if ($stmt->rowCount() == 0) {
        $conn->query("INSERT INTO courses (name, programme_id) VALUES ('Computer Hardware and Peripheral Devices', $prog1)");
        echo "<p style='color:green;'>Inserted 'Computer Hardware and Peripheral Devices' as Mandatory.</p>";
    } else {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $conn->query("UPDATE courses SET programme_id = $prog1 WHERE id = " . (int)$row['id']);
        echo "<p style='color:green;'>Updated 'Computer Hardware and Peripheral Devices' to Mandatory.</p>";
    }

    echo "<h2 style='color:green;'>All updates applied! You can safely delete this file (update_live_db.php).</h2>";
    echo "<a href='" . BASE_URL . "login.php'>Go to Login</a>";
    
} catch (Exception $e) {
    echo "<h2 style='color:red;'>Failed to update database: " . $e->getMessage() . "</h2>";
}
?>
