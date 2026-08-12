<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT id, first_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            $stmt->execute([$token, $expires, $user['id']]);
            
            $resetLink = BASE_URL . "reset_password.php?token=" . $token;
            $email_body = "Hello " . $user['first_name'] . ",\n\nWe received a request to reset your password. Click the link below to reset it:\n$resetLink\n\nIf you did not request this, you can safely ignore this email.\n\nThanks,\nMetaserve Team";
            $headers = "From: info@metaserve.com.ng\r\n";
            
            mail($email, "Password Reset Request", $email_body, $headers);
            
            $msg = '<div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i> If that email is registered, a password reset link has been sent to it. Please check your inbox (and spam folder).</div>';
        } else {
            // For security, do not reveal if the email exists or not
            $msg = '<div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i> If that email is registered, a password reset link has been sent to it. Please check your inbox (and spam folder).</div>';
        }
    } else {
        $msg = '<div class="alert alert-danger">Invalid email format.</div>';
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="py-5 mt-5" style="min-height: 85vh; display: flex; align-items: center; background: linear-gradient(135deg, rgba(30,86,49,0.05) 0%, rgba(118,186,27,0.1) 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="clean-card p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 shadow-sm bg-primary-light" style="width: 70px; height: 70px;">
                            <i class="fa-solid fa-key fs-2 text-primary-custom"></i>
                        </div>
                        <h3 class="text-dark fw-bold">Forgot Password</h3>
                        <p class="text-muted">Enter your email to reset your password.</p>
                    </div>

                    <?= $msg ?>

                    <form method="POST" action="forgot_password.php">
                        <div class="mb-4">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control clean-form-control border-start-0 ps-0" required placeholder="name@example.com">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary-custom w-100 py-3 mb-4 fw-bold fs-5">Send Reset Link <i class="fa-solid fa-paper-plane ms-2"></i></button>
                    </form>
                    
                    <div class="text-center">
                        <a href="login.php" class="text-decoration-none fw-bold text-muted"><i class="fa-solid fa-arrow-left me-1"></i> Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
