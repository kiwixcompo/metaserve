<?php
require_once __DIR__ . '/../config/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 7) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}
require_once __DIR__ . '/../includes/header.php';
?>
<section class="py-5 mt-5 bg-light min-vh-100">
    <div class="container">
        <h2 class="fw-bold text-dark mb-4"><i class="fa-solid fa-building-columns text-primary-custom me-2"></i> University Management Dashboard</h2>
        
        <div class="row gy-4">
            <div class="col-md-6">
                <div class="clean-card p-4 text-center">
                    <h5 class="text-muted">Total Revenue</h5>
                    <h2 class="fw-bold text-primary-custom mt-2">₦0.00</h2>
                </div>
            </div>
            <div class="col-md-6">
                <div class="clean-card p-4 text-center">
                    <h5 class="text-muted">Active Enrollments</h5>
                    <h2 class="fw-bold text-secondary-custom mt-2">0</h2>
                </div>
            </div>
        </div>
        
        <div class="clean-card p-4 mt-4 text-center">
            <h5 class="fw-bold mb-3">Export Reports</h5>
            <a href="<?= BASE_URL ?>src/Controllers/ReportController.php?action=export_csv" class="btn btn-outline-custom me-2"><i class="fa-solid fa-file-csv me-2"></i> Download CSV Report</a>
            <a href="<?= BASE_URL ?>src/Controllers/ReportController.php?action=export_pdf" class="btn btn-outline-danger"><i class="fa-solid fa-file-pdf me-2"></i> Download PDF Report</a>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
