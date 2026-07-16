<?php
require_once __DIR__ . '/config/database.php';

$database = new Database();
$conn = $database->getConnection();

$password_hash = password_hash('Password@123', PASSWORD_BCRYPT);

$users = [
    [1, 'Super', 'Admin', 'admin@metaserve.com', 'external', null],
    [2, 'Head', 'Accounts', 'accounts@metaserve.com', 'external', null],
    [3, 'Programme', 'Coordinator', 'coordinator@metaserve.com', 'external', null],
    [4, 'Lead', 'Facilitator', 'facilitator@metaserve.com', 'external', null],
    [5, 'TSU', 'Student', 'student@metaserve.com', 'tsu_student', 'TSU/2026/001'],
    [6, 'External', 'Candidate', 'external@metaserve.com', 'external', null],
    [7, 'University', 'Management', 'management@metaserve.com', 'external', null],
];

foreach ($users as $u) {
    // Check if exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$u[3]]);
    if ($check->rowCount() == 0) {
        $stmt = $conn->prepare("INSERT INTO users (role_id, first_name, last_name, email, password_hash, type, reg_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$u[0], $u[1], $u[2], $u[3], $password_hash, $u[4], $u[5]]);
        echo "Created account for: " . $u[3] . "\n";
    } else {
        echo "Account already exists: " . $u[3] . "\n";
    }
}
echo "Seeding Complete.\n";
