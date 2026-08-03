<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/Settings.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$reference = $_GET['reference'] ?? null;
$enrollment_id = $_GET['enrollment_id'] ?? null;

if (!$reference || !$enrollment_id) {
    $_SESSION['error_msg'] = "Invalid payment reference.";
    header("Location: " . BASE_URL . "student/index.php");
    exit();
}

$settingsModel = new Settings();
$settingsData = $settingsModel->getAllSettings();
$secretKey = $settingsData['paystack_secret_key'] ?? '';

// Verify with Paystack
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer " . $secretKey,
        "Cache-Control: no-cache",
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    $_SESSION['error_msg'] = "cURL Error #:" . $err;
    header("Location: checkout.php?id=" . $enrollment_id);
    exit();
}

$result = json_decode($response);
if (!$result->status) {
    $_SESSION['error_msg'] = "Payment verification failed: " . $result->message;
    header("Location: checkout.php?id=" . $enrollment_id);
    exit();
}

if ('success' === $result->data->status) {
    // Payment was successful
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("UPDATE enrollments SET payment_status = 'paid', status = 'active', receipt_number = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$reference, $enrollment_id, $_SESSION['user_id']]);
    
    $_SESSION['success_msg'] = "Payment successful! You are now fully enrolled in the course.";
    header("Location: " . BASE_URL . "student/index.php");
    exit();
} else {
    $_SESSION['error_msg'] = "Payment was not successful. Please try again.";
    header("Location: checkout.php?id=" . $enrollment_id);
    exit();
}
