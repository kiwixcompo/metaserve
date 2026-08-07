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

echo "\nDeployment finished.\n";
echo "</pre>";
?>
