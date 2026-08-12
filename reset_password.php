<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$msg = '';
$token = $_GET['token'] ?? '';
$isValid = false;
$user_id = null;

if (!empty($token)) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $isValid = true;
        $user_id = $user['id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isValid) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || empty($confirm)) {
        $msg = '<div class="alert alert-danger">Please fill all fields.</div>';
    } elseif ($password !== $confirm) {
        $msg = '<div class="alert alert-danger">Passwords do not match.</div>';
    } elseif (strlen($password) < 8) {
        $msg = '<div class="alert alert-danger">Password must be at least 8 characters long.</div>';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->execute([$hash, $user_id]);
        
        $msg = '<div class="alert alert-success">Your password has been successfully reset! You can now log in.</div>';
        $isValid = false; // hide form
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
                            <i class="fa-solid fa-unlock-keyhole fs-2 text-primary-custom"></i>
                        </div>
                        <h3 class="text-dark fw-bold">Reset Password</h3>
                    </div>

                    <?= $msg ?>

                    <?php if ($isValid): ?>
                    <form method="POST" action="reset_password.php?token=<?= htmlspecialchars($token) ?>">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control clean-form-control" required minlength="8" placeholder="••••••••">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control clean-form-control" required minlength="8" placeholder="••••••••">
                        </div>
                        
                        <button type="submit" class="btn btn-primary-custom w-100 py-3 fw-bold fs-5">Reset Password</button>
                    </form>
                    <?php elseif (empty($msg)): ?>
                        <div class="alert alert-danger">Invalid or expired password reset link. Please request a new one.</div>
                        <div class="text-center mt-3"><a href="forgot_password.php" class="btn btn-outline-primary">Request New Link</a></div>
                    <?php endif; ?>
                    
                    <div class="text-center mt-4">
                        <a href="login.php" class="text-decoration-none fw-bold text-muted"><i class="fa-solid fa-arrow-left me-1"></i> Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
