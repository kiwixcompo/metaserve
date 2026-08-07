<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Models/User.php';

$message = '';
$status = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $userModel = new User();
    
    $user = $userModel->verifyEmail($token);
    
    if ($user) {
        $message = "Your email address has been successfully verified! You can now log in to your dashboard.";
        $status = 'success';
        
        // Send Welcome Email
        require_once __DIR__ . '/src/Models/EmailService.php';
        $emailService = new \App\Models\EmailService();
        $emailService->sendWelcomeEmail($user['email'], $user['first_name']);
        
    } else {
        $message = "Invalid or expired verification link. If you have already verified your account, please log in.";
        $status = 'danger';
    }
} else {
    $message = "No verification token provided.";
    $status = 'warning';
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="py-5 mt-5" style="background: linear-gradient(135deg, rgba(30,86,49,0.03) 0%, rgba(118,186,27,0.05) 100%); min-height: 70vh; display: flex; align-items: center;">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="clean-card p-5 bg-white shadow-sm text-center" style="border-radius: 15px; border-top: 5px solid var(--primary-color);">
                    
                    <?php if ($status === 'success'): ?>
                        <div style="color: #1e5631; margin-bottom: 20px;">
                            <i class="fa-solid fa-circle-check" style="font-size: 5rem;"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-3">Email Verified!</h3>
                        <p class="text-muted fs-5 mb-4"><?= $message ?></p>
                        <a href="<?= BASE_URL ?>login.php" class="btn btn-primary-custom px-5 py-2 fs-5">Login Now</a>
                        
                    <?php elseif ($status === 'danger'): ?>
                        <div style="color: #dc3545; margin-bottom: 20px;">
                            <i class="fa-solid fa-circle-xmark" style="font-size: 5rem;"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-3">Verification Failed</h3>
                        <p class="text-muted fs-5 mb-4"><?= $message ?></p>
                        <a href="<?= BASE_URL ?>login.php" class="btn btn-outline-secondary px-5 py-2 fs-5">Go to Login</a>
                        
                    <?php else: ?>
                        <div style="color: #ffc107; margin-bottom: 20px;">
                            <i class="fa-solid fa-triangle-exclamation" style="font-size: 5rem;"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-3">Invalid Request</h3>
                        <p class="text-muted fs-5 mb-4"><?= $message ?></p>
                        <a href="<?= BASE_URL ?>index.php" class="btn btn-outline-secondary px-5 py-2 fs-5">Go to Homepage</a>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
