<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [5, 6])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: " . BASE_URL . "student/index.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT e.*, c.name as course_name, p.name as prog_name, u.first_name, u.last_name, u.email 
                        FROM enrollments e 
                        JOIN courses c ON e.course_id = c.id 
                        JOIN programmes p ON e.programme_id = p.id 
                        JOIN users u ON e.user_id = u.id 
                        WHERE e.id = ? AND e.user_id = ? AND e.payment_status = 'paid'");
$stmt->execute([$id, $_SESSION['user_id']]);
$enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$enrollment) {
    $_SESSION['error_msg'] = "Receipt not found or payment incomplete.";
    header("Location: " . BASE_URL . "student/index.php");
    exit();
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5 mt-5 bg-light min-vh-100">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold mb-0">Payment Receipt</h3>
                    <div>
                        <button onclick="window.print()" class="btn btn-primary-custom me-2"><i class="fa-solid fa-print me-1"></i> Print</button>
                        <a href="receipts.php" class="btn btn-outline-secondary">All Receipts</a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm p-4 p-md-5" id="receipt-card">
                    <div class="text-center border-bottom pb-4 mb-4">
                        <h4 class="fw-bold text-dark mb-1">Metaserve Info Tech Ltd</h4>
                        <p class="text-muted mb-0">The Hackathon Hub</p>
                    </div>

                    <div class="row mb-4">
                        <div class="col-6">
                            <h6 class="text-muted fw-bold mb-1">Billed To:</h6>
                            <p class="mb-0 fw-bold"><?= htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']) ?></p>
                            <p class="mb-0 text-muted small"><?= htmlspecialchars($enrollment['email']) ?></p>
                        </div>
                        <div class="col-6 text-end">
                            <h6 class="text-muted fw-bold mb-1">Receipt Number:</h6>
                            <p class="mb-0 fw-bold"><?= htmlspecialchars($enrollment['receipt_number']) ?></p>
                            <h6 class="text-muted fw-bold mb-1 mt-3">Date:</h6>
                            <p class="mb-0 fw-bold"><?= date('F j, Y', strtotime($enrollment['enrolled_at'])) ?></p>
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table border">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($enrollment['course_name']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($enrollment['prog_name']) ?></div>
                                    </td>
                                    <td class="text-end align-middle">₦<?= number_format($enrollment['amount_paid'], 2) ?></td>
                                </tr>
                                <?php if ($enrollment['form_fee_paid'] > 0): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold">Administrative / Form Fee</div>
                                    </td>
                                    <td class="text-end align-middle">₦<?= number_format($enrollment['form_fee_paid'], 2) ?></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="text-end fw-bold">Total Paid:</td>
                                    <td class="text-end fw-bold fs-5 text-success">₦<?= number_format($enrollment['amount_paid'] + $enrollment['form_fee_paid'], 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="text-center text-muted small mt-4 pt-4 border-top">
                        <p class="mb-1">Thank you for your payment!</p>
                        <p class="mb-0">For any inquiries, please contact our support team.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    @media print {
        body * { visibility: hidden; }
        #receipt-card, #receipt-card * { visibility: visible; }
        #receipt-card { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none !important; border: none !important; }
        .btn, nav, footer { display: none !important; }
    }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
