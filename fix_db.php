<?php
/**
 * Database Cleanup Script
 * Run this script to fix the incorrect records in the live database.
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h2>Database Cleanup Script</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Fix 1: Reset amount_paid and form_fee_paid for pending enrollments
    // Prior to our fix, the checkout page updated the enrollment with the amount BEFORE payment was verified.
    $stmt1 = $conn->prepare("UPDATE enrollments SET amount_paid = 0, form_fee_paid = 0 WHERE payment_status = 'pending'");
    $stmt1->execute();
    $rowCount1 = $stmt1->rowCount();
    echo "<p>Successfully reset amounts for <strong>{$rowCount1}</strong> pending enrollments.</p>";

    // Optional: Add any other cleanup queries here

    echo "<p style='color: green;'><strong>Cleanup complete! You can safely delete this script.</strong></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
}
?>
