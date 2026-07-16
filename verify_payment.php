<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Controllers/PaymentController.php';

// Redirect to home if accessed without reference
if (!isset($_GET['reference'])) {
    header("Location: " . BASE_URL);
    exit();
}

$reference = $_GET['reference'];
$paymentController = new PaymentController();

// Verify the transaction with Paystack API
$is_valid = $paymentController->verifyPaystack($reference);

require_once __DIR__ . '/includes/header.php';
?>

<section class="py-5 mt-5" style="background: var(--bg-light); min-height: 80vh; display: flex; align-items: center;">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <?php if ($is_valid): ?>
                    <!-- Success View -->
                    <div class="clean-card border-0 p-5 shadow-sm" style="background: #ffffff; border-top: 4px solid var(--secondary-color) !important;">
                        <i class="fa-solid fa-circle-check" style="font-size: 5rem; color: var(--secondary-color);"></i>
                        <h2 class="fw-bold mt-4 text-dark">Payment Successful!</h2>
                        <p class="text-muted mt-3 mb-4" style="font-size: 1.1rem;">
                            Your transaction (<strong><?= htmlspecialchars($reference) ?></strong>) has been verified. 
                            Your enrollment is now fully active. You can now access your learning materials and assessments.
                        </p>
                        <a href="<?= BASE_URL ?>student/" class="btn btn-primary-custom px-5 py-3 fs-5">Go to Student Dashboard</a>
                    </div>
                <?php else: ?>
                    <!-- Failed View -->
                    <div class="clean-card border-0 p-5 shadow-sm" style="background: #ffffff; border-top: 4px solid #dc3545 !important;">
                        <i class="fa-solid fa-circle-xmark" style="font-size: 5rem; color: #dc3545;"></i>
                        <h2 class="fw-bold mt-4 text-dark">Payment Verification Failed</h2>
                        <p class="text-muted mt-3 mb-4" style="font-size: 1.1rem;">
                            We could not verify your transaction (<strong><?= htmlspecialchars($reference) ?></strong>). 
                            If you were debited, please contact our support team. Otherwise, you can try paying again.
                        </p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="<?= BASE_URL ?>payment.php" class="btn btn-primary-custom">Try Again</a>
                            <a href="<?= BASE_URL ?>support.php" class="btn btn-outline-custom">Contact Support</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
