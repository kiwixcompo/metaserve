<?php
// Phase 2: Centralized Configuration

// Global Error Logging Configuration
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');
// Note: If error.log is deleted, PHP will automatically recreate it upon the next error.

// Base URL (Change for production)
define('BASE_URL', 'http://localhost/metaserve/');
define('SITE_NAME', 'Digital Skills Portal - Metaserve Info Tech Ltd');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Set your DB password
define('DB_NAME', 'digital_skills_db');

// Paystack API Configuration
define('PAYSTACK_PUBLIC_KEY', 'pk_test_xxxxxxxxxxxxxxxxxxxxxxxx');
define('PAYSTACK_SECRET_KEY', 'your_paystack_secret_key_here');

// File Upload Configurations
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('PASSPORT_DIR', UPLOAD_DIR . 'passports/');
define('TELLER_DIR', UPLOAD_DIR . 'tellers/');

// Ensure directories exist
if (!is_dir(PASSPORT_DIR)) mkdir(PASSPORT_DIR, 0777, true);
if (!is_dir(TELLER_DIR)) mkdir(TELLER_DIR, 0777, true);

// Start Session globally
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
