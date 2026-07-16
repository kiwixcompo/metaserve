<?php
require_once __DIR__ . '/../config/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}
require_once __DIR__ . '/../includes/header.php';
?>
<section class="py-5 mt-5 bg-light min-vh-100">
    <div class="container">
        <h2 class="fw-bold text-dark mb-4"><i class="fa-solid fa-file-invoice-dollar text-primary-custom me-2"></i> Accounts Dashboard</h2>
        <div class="clean-card p-4">
            <h5 class="fw-bold">Pending Tellers for Approval</h5>
            <div class="alert alert-warning mt-3"><i class="fa-solid fa-clock me-2"></i> No pending offline tellers to approve at this moment.</div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
