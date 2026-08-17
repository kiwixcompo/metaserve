<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [5, 6])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT e.*, c.name as course_name, p.name as prog_name 
                        FROM enrollments e 
                        JOIN courses c ON e.course_id = c.id 
                        JOIN programmes p ON e.programme_id = p.id 
                        WHERE e.user_id = ? AND e.payment_status = 'paid' 
                        ORDER BY e.enrolled_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5 mt-5 bg-light min-vh-100">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">My Payment Receipts</h3>
            <a href="index.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Dashboard</a>
        </div>

        <div class="card border-0 shadow-sm p-4">
            <?php if (empty($receipts)): ?>
                <div class="text-center py-5">
                    <i class="fa-solid fa-receipt text-muted fs-1 mb-3 opacity-50"></i>
                    <p class="text-muted">You have no successful payments yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Receipt No.</th>
                                <th>Programme / Course</th>
                                <th>Amount Paid</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($receipts as $receipt): ?>
                            <tr>
                                <td><?= date('M j, Y', strtotime($receipt['enrolled_at'])) ?></td>
                                <td class="font-monospace text-muted"><?= htmlspecialchars($receipt['receipt_number']) ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($receipt['course_name']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($receipt['prog_name']) ?></div>
                                </td>
                                <td class="fw-bold text-success">₦<?= number_format($receipt['amount_paid'] + $receipt['form_fee_paid'], 2) ?></td>
                                <td>
                                    <a href="receipt.php?id=<?= $receipt['id'] ?>" class="btn btn-sm btn-outline-primary">View Receipt</a>
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
