<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Models/Payment.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$paymentModel = new Payment();
$pendingTellers = $paymentModel->getPendingTellers();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'approve_teller') {
    require_once __DIR__ . '/../src/Controllers/PaymentController.php';
    $paymentController = new PaymentController();
    $paymentController->approveTeller($_POST['reference'], $_SESSION['user_id']);
    $_SESSION['success_msg'] = "Teller approved successfully!";
    header("Location: " . BASE_URL . "accounts/index.php");
    exit();
}

require_once __DIR__ . '/../includes/header.php';
?>
<section class="py-5 mt-5 bg-light min-vh-100">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark mb-0"><i class="fa-solid fa-file-invoice-dollar text-primary-custom me-2"></i> Accounts Dashboard</h2>
            <a href="<?= BASE_URL ?>accounts/register_student.php" class="btn btn-primary-custom fw-bold"><i class="fa-solid fa-user-plus me-2"></i>Register New Student</a>
        </div>
        
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0"><i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($_SESSION['success_msg']); unset($_SESSION['success_msg']); ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0"><i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($_SESSION['error_msg']); unset($_SESSION['error_msg']); ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        
        <div class="clean-card p-4">
            <h5 class="fw-bold mb-4">Pending Tellers for Approval</h5>
            
            <?php if (empty($pendingTellers)): ?>
                <div class="alert alert-warning"><i class="fa-solid fa-clock me-2"></i> No pending offline tellers to approve at this moment.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Programme</th>
                                <th>Amount</th>
                                <th>Reference</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingTellers as $teller): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($teller['first_name'] . ' ' . $teller['last_name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($teller['email']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($teller['programme_name']) ?></td>
                                <td class="fw-bold">&#8358;<?= number_format($teller['amount'], 2) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($teller['reference']) ?></span></td>
                                <td><?= date('M j, Y H:i', strtotime($teller['created_at'])) ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="<?= BASE_URL ?>uploads/tellers/<?= htmlspecialchars($teller['teller_path']) ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fa-solid fa-eye"></i> View</a>
                                        <form method="POST" action="index.php" style="display:inline;">
                                            <input type="hidden" name="action" value="approve_teller">
                                            <input type="hidden" name="reference" value="<?= htmlspecialchars($teller['reference']) ?>">
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this payment?');"><i class="fa-solid fa-check"></i> Approve</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
