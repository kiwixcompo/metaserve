<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [5, 6])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$enrollment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$db = new Database();
$conn = $db->getConnection();

// Validate enrollment belongs to student
$stmt = $conn->prepare("
    SELECT e.*, p.name as prog_name, c.name as explicit_course_name 
    FROM enrollments e 
    JOIN programmes p ON e.programme_id = p.id 
    LEFT JOIN courses c ON e.course_id = c.id
    WHERE e.id = ? AND e.user_id = ?
");
$stmt->execute([$enrollment_id, $_SESSION['user_id']]);
$enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$enrollment) {
    echo "<div class='container mt-5 py-5'><div class='alert alert-danger'>Invalid enrollment record.</div></div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit();
}

$enrollmentName = $enrollment['explicit_course_name'] ?? $enrollment['prog_name'];

// Fetch the courses applicable to this enrollment
if ($enrollment['course_id']) {
    // Single course
    $cStmt = $conn->prepare("SELECT id as course_id, name as course_name, course_code FROM courses WHERE id = ?");
    $cStmt->execute([$enrollment['course_id']]);
    $courses = $cStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Entire programme
    $cStmt = $conn->prepare("SELECT id as course_id, name as course_name, course_code FROM courses WHERE programme_id = ? ORDER BY name ASC");
    $cStmt->execute([$enrollment['programme_id']]);
    $courses = $cStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch assessments for this enrollment
$aStmt = $conn->prepare("SELECT course_id, score, remarks, graded_at, f.first_name as fac_first, f.last_name as fac_last 
                         FROM assessments a 
                         LEFT JOIN users f ON a.facilitator_id = f.id 
                         WHERE enrollment_id = ?");
$aStmt->execute([$enrollment_id]);
$assessments = [];
foreach($aStmt->fetchAll(PDO::FETCH_ASSOC) as $ass) {
    $assessments[$ass['course_id']] = $ass;
}

require_once __DIR__ . '/../includes/header.php';
?>
<section class="py-5 mt-5 bg-light min-vh-100">
    <div class="container">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark mb-0"><i class="fa-solid fa-layer-group text-primary-custom me-2"></i> Course Modules & Grades</h2>
            <a href="index.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i> Back to Dashboard</a>
        </div>

        <div class="clean-card p-4 mb-4 border-start border-4 border-primary">
            <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($enrollmentName) ?></h4>
            <p class="text-muted mb-0">Status: <span class="badge bg-success"><?= ucfirst($enrollment['status']) ?></span></p>
        </div>

        <div class="row gy-4">
            <?php foreach ($courses as $c): ?>
                <?php 
                $ass = $assessments[$c['course_id']] ?? null; 
                $hasGrade = $ass && $ass['score'] !== null;
                $hasRemarks = $ass && !empty($ass['remarks']);
                ?>
                <div class="col-md-6">
                    <div class="clean-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-light text-dark border mb-2"><?= htmlspecialchars($c['course_code']) ?></span>
                                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($c['course_name']) ?></h5>
                            </div>
                            <?php if ($hasGrade): ?>
                                <div class="bg-success-subtle text-success rounded px-3 py-2 text-center">
                                    <div class="fs-4 fw-bold"><?= number_format($ass['score'], 1) ?>%</div>
                                    <div class="small">Score</div>
                                </div>
                            <?php else: ?>
                                <div class="bg-light text-muted rounded px-3 py-2 text-center border">
                                    <div class="fs-4 fw-bold">-</div>
                                    <div class="small">Pending</div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <hr class="text-muted opacity-25">

                        <div class="mt-3">
                            <h6 class="fw-bold text-dark small text-uppercase">Facilitator Feedback</h6>
                            <?php if ($hasRemarks): ?>
                                <div class="p-3 bg-light rounded border border-light mt-2">
                                    <p class="mb-2 text-dark"><i class="fa-solid fa-quote-left text-primary-custom opacity-50 me-2"></i><?= nl2br(htmlspecialchars($ass['remarks'])) ?></p>
                                    <small class="text-muted d-block text-end">
                                        - <?= htmlspecialchars($ass['fac_first'] . ' ' . $ass['fac_last']) ?> 
                                        <br> (<?= date('M j, Y', strtotime($ass['graded_at'])) ?>)
                                    </small>
                                </div>
                            <?php else: ?>
                                <p class="text-muted small mt-2 fst-italic">No remarks available yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
