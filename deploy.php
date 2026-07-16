<?php
/**
 * Automated Deployment Script for Metaserve
 * This script pulls the latest changes from GitHub when triggered.
 */

// Define your secret token (this must match the token in sync.bat)
$secret_token = 'metaserve_deploy_2026';

// Check if the provided token is correct
if (!isset($_GET['token']) || $_GET['token'] !== $secret_token) {
    header('HTTP/1.0 403 Forbidden');
    die('Forbidden: Invalid or missing token.');
}

// Ensure the script executes in the directory where it's placed (the root of the project)
$project_dir = __DIR__;

// Command to execute
// Note: 2>&1 redirects stderr to stdout so we can see error messages in the output
$command = "cd " . escapeshellarg($project_dir) . " && git pull origin main 2>&1";

// Execute the command
exec($command, $output, $return_var);

// Output the result
echo "<h2>Deployment Execution Result:</h2>";
echo "<pre>";
if ($return_var === 0) {
    echo "SUCCESS: Git pull executed successfully.\n\n";
} else {
    echo "ERROR: Git pull failed with code {$return_var}.\n\n";
}
echo implode("\n", $output);
echo "</pre>";
?>
