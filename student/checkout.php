<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Models/Settings.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$enrollment_id = $_GET['id'] ?? null;
if (!$enrollment_id) {
    $_SESSION['error_msg'] = "Invalid enrollment.";
    header("Location: " . BASE_URL . "student/index.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Fetch enrollment details
$stmt = $conn->prepare("SELECT e.*, c.name as course_name, p.name as prog_name 
                        FROM enrollments e 
                        JOIN courses c ON e.course_id = c.id 
                        JOIN programmes p ON e.programme_id = p.id 
                        WHERE e.id = ? AND e.user_id = ?");
$stmt->execute([$enrollment_id, $_SESSION['user_id']]);
$enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$enrollment) {
    $_SESSION['error_msg'] = "Enrollment not found.";
    header("Location: " . BASE_URL . "student/index.php");
    exit();
}

if ($enrollment['payment_status'] === 'paid') {
    $_SESSION['success_msg'] = "You have already paid for this course.";
    header("Location: " . BASE_URL . "student/index.php");
    exit();
}

// Get User info for payment calculation
$stmt = $conn->prepare("SELECT type, email, first_name, last_name, form_purchased FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Pricing Logic
$prog_fee = 0;
if ($enrollment['programme_id'] == 1) { // Basic
    $prog_fee = ($user['type'] === 'tsu_student') ? 20000 : 50000;
} else { // Professional
    $prog_fee = ($user['type'] === 'tsu_student') ? 40000 : 100000;
}

$admin_charge = ($user['form_purchased']) ? 0 : 2000;
$total_to_pay = $prog_fee + $admin_charge;

// Update the db with amounts if not set yet
$stmt = $conn->prepare("UPDATE enrollments SET amount_paid = ?, form_fee_paid = ? WHERE id = ?");
$stmt->execute([$prog_fee, $admin_charge, $enrollment_id]);

// If total is 0 somehow (maybe exempted), just mark as paid
if ($total_to_pay == 0) {
    $stmt = $conn->prepare("UPDATE enrollments SET payment_status = 'paid' WHERE id = ?");
    $stmt->execute([$enrollment_id]);
    $_SESSION['success_msg'] = "Enrollment successful!";
    header("Location: " . BASE_URL . "student/index.php");
    exit();
}

$settingsModel = new Settings();
$settingsData = $settingsModel->getAllSettings();
$publicKey = $settingsData['paystack_public_key'] ?? '';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="clean-card shadow-lg border-0 rounded-4 p-5 text-center">
                <i class="fa-solid fa-credit-card fs-1 text-primary-custom mb-3"></i>
                <h3 class="fw-bold mb-4">Complete Your Payment</h3>
                
                <div class="text-start bg-light p-4 rounded-3 mb-4 border">
                    <h5 class="fw-bold border-bottom pb-2 mb-3">Order Summary</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Course:</span>
                        <span class="fw-bold"><?= htmlspecialchars($enrollment['course_name']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Programme:</span>
                        <span class="fw-bold"><?= htmlspecialchars($enrollment['prog_name']) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Programme Fee:</span>
                        <span class="fw-bold">&#8358;<?= number_format($prog_fee, 2) ?></span>
                    </div>
                    <?php if ($admin_charge > 0): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Service/Admin Charge:</span>
                        <span class="fw-bold">&#8358;<?= number_format($admin_charge, 2) ?></span>
                    </div>
                    <?php else: ?>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Physical Form Purchased:</span>
                        <span class="fw-bold">Verified (-&#8358;2,000)</span>
                    </div>
                    <?php endif; ?>
                    <hr class="border-primary-custom border-2 opacity-50">
                    <div class="d-flex justify-content-between fs-5 text-dark">
                        <span class="fw-bold">Total to Pay:</span>
                        <span class="fw-bold text-primary-custom">&#8358;<?= number_format($total_to_pay, 2) ?></span>
                    </div>
                </div>

                <form id="paymentForm">
                    <button type="submit" class="btn btn-primary-custom btn-lg w-100 fw-bold shadow-sm" onclick="payWithPaystack(event)">
                        Pay &#8358;<?= number_format($total_to_pay, 2) ?> Now
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
function payWithPaystack(e) {
  e.preventDefault();
  
  let handler = PaystackPop.setup({
    key: '<?= htmlspecialchars($publicKey) ?>',
    email: '<?= htmlspecialchars($user['email']) ?>',
    amount: <?= $total_to_pay * 100 ?>, // Paystack expects Kobo
    currency: 'NGN',
    metadata: {
       custom_fields: [
          {
              display_name: "Enrollment ID",
              variable_name: "enrollment_id",
              value: "<?= $enrollment_id ?>"
          }
       ]
    },
    callback: function(response){
      // Verify payment on the server
      window.location.href = "verify_payment.php?reference=" + response.reference + "&enrollment_id=<?= $enrollment_id ?>";
    },
    onClose: function(){
      alert('Payment window closed. Please complete payment to fully register.');
    }
  });
  
  handler.openIframe();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
