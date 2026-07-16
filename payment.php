<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Controllers/PaymentController.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// MOCK DATA for the demo (In production, fetch from the database based on session)
$enrollment_id = 1; 
$amount = 15000; // 15,000 NGN

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentController = new PaymentController();
    $method = $_POST['payment_method'] ?? '';
    
    if ($method === 'paystack') {
        $result = $paymentController->initializePaystack($_SESSION['email'], $amount, $enrollment_id, $_SESSION['user_id']);
        if ($result['status'] === 'success') {
            header("Location: " . $result['authorization_url']);
            exit();
        } else {
            $error = $result['message'];
        }
    } elseif ($method === 'offline') {
        if (isset($_FILES['teller']) && $_FILES['teller']['error'] == 0) {
            $result = $paymentController->uploadTeller($_FILES['teller'], $amount, $enrollment_id, $_SESSION['user_id']);
            if ($result['status'] === 'success') {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        } else {
            $error = "Please upload a valid teller or receipt image.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="py-5 mt-5 bg-light min-vh-100 d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="clean-card p-4 p-md-5 shadow-sm border-0 bg-white">
                    <h3 class="fw-bold text-dark text-center mb-4"><i class="fa-solid fa-credit-card text-primary-custom me-2"></i> Course Enrollment Payment</h3>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0"><i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 text-center p-5">
                            <i class="fa-solid fa-circle-check fs-1 text-success mb-3"></i>
                            <h4 class="fw-bold">Teller Uploaded!</h4>
                            <p class="mb-0"><?= htmlspecialchars($success) ?></p>
                            <a href="<?= BASE_URL ?>student/" class="btn btn-outline-success mt-4">Return to Dashboard</a>
                        </div>
                    <?php else: ?>
                    
                    <div class="alert alert-info border-0 shadow-sm mb-4 bg-primary-light text-dark p-4 rounded-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-5 fw-bold text-muted">Amount Due:</span>
                            <span class="fs-2 fw-bold text-primary-custom">₦<?= number_format($amount) ?></span>
                        </div>
                    </div>

                    <ul class="nav nav-pills nav-justified mb-4 gap-2" id="paymentTabs" role="tablist">
                      <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold py-3 border border-light" id="paystack-tab" data-bs-toggle="tab" data-bs-target="#paystack" type="button" role="tab"><i class="fa-solid fa-bolt text-warning me-2"></i> Paystack (Instant)</button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold py-3 border border-light" id="offline-tab" data-bs-toggle="tab" data-bs-target="#offline" type="button" role="tab"><i class="fa-solid fa-building-columns text-secondary me-2"></i> Offline Teller</button>
                      </li>
                    </ul>

                    <div class="tab-content mt-4" id="paymentTabsContent">
                      <!-- Paystack Tab -->
                      <div class="tab-pane fade show active" id="paystack" role="tabpanel">
                          <form method="POST" action="payment.php">
                              <input type="hidden" name="payment_method" value="paystack">
                              <div class="text-center p-4 bg-light rounded-3 border mb-4">
                                  <img src="https://paystack.com/assets/payment/img/paystack-badge-cards-ngn.png" alt="Paystack Secure" style="max-height: 40px;" class="mb-3">
                                  <p class="text-muted small mb-0">You will be securely redirected to Paystack to complete your instant payment using Card, Transfer, or USSD.</p>
                              </div>
                              <button type="submit" class="btn btn-primary-custom w-100 py-3 fw-bold fs-5 shadow-sm">Pay ₦<?= number_format($amount) ?> Now <i class="fa-solid fa-arrow-right ms-2"></i></button>
                          </form>
                      </div>
                      
                      <!-- Offline Tab -->
                      <div class="tab-pane fade" id="offline" role="tabpanel">
                          <form method="POST" action="payment.php" enctype="multipart/form-data">
                              <input type="hidden" name="payment_method" value="offline">
                              <div class="alert alert-secondary border-0 small mb-4">
                                <i class="fa-solid fa-circle-info me-2 text-secondary-custom"></i> Please pay into the company account below and upload your bank teller or transfer receipt here for administrative approval.
                              </div>
                              
                              <div class="mb-4">
                                  <label class="form-label fw-bold text-dark small text-uppercase">Metaserve Bank Details</label>
                                  <div class="p-3 bg-light rounded-3 border border-2">
                                      <p class="mb-1 text-muted small">Bank Name:</p>
                                      <h6 class="fw-bold">First Bank of Nigeria</h6>
                                      <p class="mb-1 mt-3 text-muted small">Account Name:</p>
                                      <h6 class="fw-bold">Metaserve Info Tech Ltd</h6>
                                      <p class="mb-1 mt-3 text-muted small">Account Number:</p>
                                      <h5 class="fw-bold text-primary-custom font-monospace tracking-wide">2038475639</h5>
                                  </div>
                              </div>
                              
                              <div class="mb-4">
                                  <label class="form-label fw-bold text-dark">Upload Scanned Receipt / Teller *</label>
                                  <input class="form-control clean-form-control p-3" type="file" name="teller" accept=".jpg,.jpeg,.png,.pdf" required>
                                  <div class="form-text small mt-2"><i class="fa-solid fa-paperclip me-1"></i> Max size: 5MB. Allowed formats: JPG, PNG, PDF.</div>
                              </div>
                              
                              <button type="submit" class="btn btn-secondary-custom w-100 py-3 fw-bold fs-5 text-white shadow-sm" style="background-color: var(--secondary-color); border: none;">Submit Teller for Verification <i class="fa-solid fa-upload ms-2"></i></button>
                          </form>
                      </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
