<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [5, 6])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Handle Payment Bypass
if (isset($_GET['action']) && $_GET['action'] == 'bypass_payment') {
    $stmt = $conn->prepare("UPDATE enrollments SET status = 'active' WHERE user_id = :uid AND status = 'pending'");
    $stmt->execute(['uid' => $_SESSION['user_id']]);
    $_SESSION['success_msg'] = "Test Mode: Your enrollments have been auto-approved!";
    header("Location: index.php");
    exit();
}

// Fetch Enrollments
$stmt = $conn->prepare("SELECT e.*, p.name as prog_name, c.name as course_name FROM enrollments e JOIN programmes p ON e.programme_id = p.id LEFT JOIN courses c ON e.course_id = c.id WHERE e.user_id = :uid");
$stmt->execute(['uid' => $_SESSION['user_id']]);
$enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$hasPending = false;
$activeEnrollments = [];
foreach($enrollments as $e) {
    if($e['status'] === 'pending') $hasPending = true;
    if($e['status'] === 'active') $activeEnrollments[] = $e;
}

require_once __DIR__ . '/../includes/header.php';
?>
<section class="py-5 mt-5 bg-light min-vh-100">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark"><i class="fa-solid fa-graduation-cap text-primary-custom me-2"></i> Student Dashboard</h2>
        </div>
        
        <div class="row gy-4">
            <!-- Sidebar Profile -->
            <div class="col-md-4">
                <div class="clean-card p-4 text-center">
                    <div class="rounded-circle bg-primary-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fa-solid fa-user fs-1 text-primary-custom"></i>
                    </div>
                    <h5 class="fw-bold text-dark"><?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?></h5>
                    <p class="text-muted mb-2"><?= htmlspecialchars($_SESSION['email']) ?></p>
                    
                    <?php if (!empty($_SESSION['reg_number'])): ?>
                        <div class="mb-3">
                            <span class="badge bg-secondary px-3 py-2 border rounded-pill">
                                Reg No: <?= htmlspecialchars($_SESSION['reg_number']) ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill border border-success">Active Account</span>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <div class="col-md-8">
                <div class="clean-card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-book-open me-2 text-secondary-custom"></i> My Learning Path</h5>
                        <a href="<?= BASE_URL ?>student/enroll.php" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-plus me-1"></i> Enroll in New Course</a>
                    </div>
                    
                    <?php if (isset($_SESSION['success_msg'])): ?>
                        <div class="alert alert-success border-0"><i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($_SESSION['success_msg']); unset($_SESSION['success_msg']); ?></div>
                    <?php endif; ?>

                    <?php if ($hasPending): ?>
                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center p-4" style="border-radius: 12px;">
                        <i class="fa-solid fa-circle-info fs-2 me-4 text-warning"></i>
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1 text-dark">Unlock Your Courses</h6>
                            <p class="mb-0 small text-dark opacity-75">You have pending enrollments. Complete your payment to access your mapped courses and modules.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-2">
                        <a href="<?= BASE_URL ?>payment.php" class="btn btn-primary-custom px-4 py-2"><i class="fa-solid fa-credit-card me-2"></i> Proceed to Payment</a>
                        <a href="index.php?action=bypass_payment" class="btn btn-outline-danger px-4 py-2" onclick="return confirm('Use Test Bypass?');"><i class="fa-solid fa-unlock me-2"></i> Test Bypass (Auto-Approve)</a>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($activeEnrollments)): ?>
                        <div class="mt-4">
                            <h6 class="fw-bold text-muted mb-3">Active Enrollments</h6>
                            <ul class="list-group list-group-flush border-top border-bottom">
                                <?php foreach($activeEnrollments as $active): ?>
                                    <li class="list-group-item px-0 py-3 d-flex align-items-center border-0 border-bottom border-light">
                                        <div class="rounded-circle bg-success-subtle text-success p-2 me-3"><i class="fa-solid fa-check"></i></div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($active['course_name'] ?? $active['prog_name']) ?></h6>
                                            <small class="text-muted">Enrolled: <?= date('M j, Y', strtotime($active['enrolled_at'])) ?></small>
                                        </div>
                                        <a href="modules.php?id=<?= $active['id'] ?>" class="btn btn-sm btn-light">View Modules & Grades</a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!$hasPending && empty($activeEnrollments)): ?>
                        <div class="text-center py-4">
                            <i class="fa-solid fa-graduation-cap text-muted fs-1 mb-3 opacity-50"></i>
                            <p class="text-muted">You have not enrolled in any programmes yet.</p>
                            <a href="<?= BASE_URL ?>student/enroll.php" class="btn btn-primary-custom">Enroll Now</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="clean-card p-4">
                    <h5 class="fw-bold text-dark mb-4"><i class="fa-solid fa-chart-line me-2 text-primary-custom"></i> Recent Assessments</h5>
                    <div class="text-center py-4">
                        <i class="fa-solid fa-folder-open text-muted fs-1 mb-3 opacity-50"></i>
                        <p class="text-muted">No assessment records found. Complete your payment to start learning.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
