<?php
session_start();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/src/Models/User.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$userModel = new User();
$userId = $_SESSION['user_id'];
$user = $userModel->getUserById($userId);

// Handle form submission
$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $updateData = [
        'id' => $userId,
        'first_name' => trim($_POST['first_name']),
        'last_name' => trim($_POST['last_name']),
        'phone' => trim($_POST['phone']),
        'alt_phone' => trim($_POST['alt_phone']),
        'email' => trim($_POST['email']),
        'password' => $_POST['password'] ?? ''
    ];

    // Check if email changed and if it already exists
    if (strtolower($updateData['email']) !== strtolower($user['email'])) {
        if ($userModel->emailExists($updateData['email'])) {
            $error_msg = "That email address is already in use by another account.";
        }
    }

    if (empty($error_msg)) {
        if ($userModel->updateProfile($updateData)) {
            $success_msg = "Profile updated successfully!";
            // Update session data
            $_SESSION['first_name'] = $updateData['first_name'];
            $_SESSION['last_name'] = $updateData['last_name'];
            $_SESSION['email'] = $updateData['email'];
            
            // Refresh local user data to display
            $user = $userModel->getUserById($userId);
        } else {
            $error_msg = "Failed to update profile. Please try again.";
        }
    }
}
?>

<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="clean-card shadow-sm border-0 p-4">
                <div class="text-center mb-4">
                    <div class="bg-primary-light d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 80px; height: 80px;">
                        <i class="fa-solid fa-user-circle text-primary-custom" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">My Profile</h3>
                    <p class="text-muted small">Update your personal information</p>
                </div>

                <?php if ($success_msg): ?>
                    <div class="alert alert-success alert-dismissible fade show border-0" role="alert">
                        <i class="fa-solid fa-check-circle me-2"></i> <?= htmlspecialchars($success_msg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                    <div class="alert alert-danger alert-dismissible fade show border-0" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($error_msg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="profile.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">First Name</label>
                            <input type="text" name="first_name" class="form-control clean-form-control" required value="<?= htmlspecialchars($user['first_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Last Name</label>
                            <input type="text" name="last_name" class="form-control clean-form-control" required value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
                        </div>
                        
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Contact Information</h6>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label text-muted small fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control clean-form-control" required value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                            <div class="form-text text-danger small"><i class="fa-solid fa-circle-info"></i> Changing this will update your login email.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Phone Number</label>
                            <input type="text" name="phone" class="form-control clean-form-control" required value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Alt Phone (Optional)</label>
                            <input type="text" name="alt_phone" class="form-control clean-form-control" value="<?= htmlspecialchars($user['alt_phone'] ?? '') ?>">
                        </div>

                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Security</h6>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label text-muted small fw-bold">New Password</label>
                            <input type="password" name="password" class="form-control clean-form-control" placeholder="Leave blank to keep current password">
                            <div class="form-text small">Only fill this out if you want to change your password.</div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top text-end">
                        <a href="javascript:history.back()" class="btn btn-outline-custom me-2">Go Back</a>
                        <button type="submit" name="update_profile" class="btn btn-primary-custom px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
