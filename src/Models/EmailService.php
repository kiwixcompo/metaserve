<?php
namespace App\Models;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../includes/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../includes/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../includes/PHPMailer/src/SMTP.php';

class EmailService {
    private $mailer;
    private $fromEmail = 'info@metaserve.com.ng';
    private $fromName = 'Metaserve Digital Skills';

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->setup();
    }

    private function setup() {
        // SMTP Configuration
        $this->mailer->isSMTP();
        $this->mailer->Host       = 'mail.metaserve.com.ng'; 
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = 'info@metaserve.com.ng';
        $this->mailer->Password   = 'k{GsFN19aCyk@jZS';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mailer->Port       = 465;

        $this->mailer->setFrom($this->fromEmail, $this->fromName);
        $this->mailer->isHTML(true);
    }

    public function sendVerificationEmail($toEmail, $firstName, $token) {
        try {
            $this->mailer->addAddress($toEmail);
            $this->mailer->Subject = 'Verify Your Email - Metaserve Digital Skills';
            
            $verifyLink = BASE_URL . 'verify_email.php?token=' . $token;

            $this->mailer->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;'>
                    <h2 style='color: #1e5631; text-align: center;'>Welcome to Metaserve!</h2>
                    <p>Hello $firstName,</p>
                    <p>Thank you for registering with the Metaserve Digital Skills platform. To complete your registration and gain access to your dashboard, please verify your email address by clicking the button below:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$verifyLink' style='background-color: #1e5631; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 16px;'>Verify My Email Address</a>
                    </div>
                    <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
                    <p style='word-break: break-all; color: #555;'><a href='$verifyLink'>$verifyLink</a></p>
                    <hr style='border-top: 1px solid #eee; margin-top: 30px;'>
                    <p style='font-size: 12px; color: #888; text-align: center;'>If you did not create an account, no further action is required.</p>
                </div>
            ";
            
            $this->mailer->AltBody = "Hello $firstName,\n\nPlease verify your email address by visiting this link:\n$verifyLink\n\nThank you,\nMetaserve Digital Skills";

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Email sending failed to $toEmail. Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    public function sendWelcomeEmail($toEmail, $firstName) {
        try {
            $this->mailer->addAddress($toEmail);
            $this->mailer->Subject = 'Welcome to Metaserve Digital Skills!';
            
            $this->mailer->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;'>
                    <h2 style='color: #1e5631; text-align: center;'>Email Verified!</h2>
                    <p>Hello $firstName,</p>
                    <p>Your email has been successfully verified. You can now log in to your dashboard to complete your enrollment, view your courses, and access learning materials.</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . BASE_URL . "login.php' style='background-color: #1e5631; color: white; padding: 12px 25px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 16px;'>Login to Dashboard</a>
                    </div>
                    <p>We are excited to have you on board!</p>
                </div>
            ";
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Email sending failed to $toEmail. Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }
}
