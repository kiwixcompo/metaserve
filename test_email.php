<?php
// test_email.php
// A script to verify SMTP credentials before fully implementing email verification.

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/includes/PHPMailer/src/Exception.php';
require_once __DIR__ . '/includes/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/includes/PHPMailer/src/SMTP.php';

$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = $_POST['to_email'] ?? '';
    
    if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $status = 'danger';
    } else {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->SMTPDebug = 2;                      // Enable verbose debug output
            $mail->isSMTP();                                            
            $mail->Host       = 'mail.metaserve.com.ng'; // Try mail. domain
            $mail->SMTPAuth   = true;                                   
            $mail->Username   = 'info@metaserve.com.ng';                     
            $mail->Password   = 'k{GsFN19aCyk@jZS';                               
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Try implicit TLS
            $mail->Port       = 465;                                    

            // Recipients
            $mail->setFrom('info@metaserve.com.ng', 'Metaserve Digital Skills');
            $mail->addAddress($to);     

            // Content
            $mail->isHTML(true);                                  
            $mail->Subject = 'Test Email from Metaserve System';
            $mail->Body    = '<b>Hello!</b> This is a test email to verify that the SMTP credentials are correct.';
            $mail->AltBody = 'Hello! This is a test email to verify that the SMTP credentials are correct.';

            // Catch the debug output
            ob_start();
            $mail->send();
            $debugOutput = ob_get_clean();

            $message = 'Message has been sent successfully to ' . htmlspecialchars($to);
            $status = 'success';
        } catch (Exception $e) {
            $debugOutput = ob_get_clean() ?? '';
            $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            $status = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test SMTP Configuration</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #007bff; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; }
        button:hover { background-color: #0056b3; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .debug-box { background: #333; color: #fff; padding: 10px; font-family: monospace; white-space: pre-wrap; margin-top: 20px; border-radius: 4px; max-height: 400px; overflow-y: auto;}
    </style>
</head>
<body>
    <div class="container">
        <h2>Metaserve Email Test Tool</h2>
        <p>Send a test email using the configured SMTP credentials.</p>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $status ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="to_email">Send Test Email To:</label>
                <input type="email" id="to_email" name="to_email" placeholder="youremail@gmail.com" required>
            </div>
            <button type="submit">Send Test Email</button>
        </form>

        <?php if (!empty($debugOutput)): ?>
            <h4 style="margin-top: 20px;">SMTP Transaction Log:</h4>
            <div class="debug-box"><?= htmlspecialchars($debugOutput) ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
