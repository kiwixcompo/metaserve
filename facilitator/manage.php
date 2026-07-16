<?php
require_once __DIR__ . '/../config/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 4) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../src/Models/FacilitatorModel.php';

$facilitatorModel = new FacilitatorModel();
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

$course = $facilitatorModel->getCourseDetails($course_id);
if (!$course) {
    echo "<div class='container mt-5 py-5'><div class='alert alert-danger'>Invalid course.</div></div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit();
}

$students = $facilitatorModel->getStudentsForCourse($course_id, $_SESSION['user_id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_grades') {
    $successCount = 0;
    foreach ($_POST['grades'] as $enrollment_id => $data) {
        $score = $data['score'] ?? null;
        $remarks = $data['remarks'] ?? '';
        
        // Only update if score or remarks are provided
        if ($score !== '' || $remarks !== '') {
            if ($facilitatorModel->saveAssessment($enrollment_id, $course_id, $_SESSION['user_id'], $score, $remarks)) {
                $successCount++;
            }
        }
    }
    $_SESSION['success_msg'] = "Successfully updated $successCount student records.";
    header("Location: manage.php?course_id=" . $course_id);
    exit();
}

?>
<section class="py-5 mt-5 bg-light min-vh-100">
    <div class="container">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark mb-0"><i class="fa-solid fa-users-viewfinder text-primary-custom me-2"></i> Manage Students</h2>
            <a href="index.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i> Back to Dashboard</a>
        </div>

        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0"><i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($_SESSION['success_msg']); unset($_SESSION['success_msg']); ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="clean-card p-4 mb-4 border-start border-4 border-primary">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($course['name']) ?></h4>
                    <p class="text-muted mb-0"><span class="badge bg-light text-dark border me-2"><?= htmlspecialchars($course['course_code']) ?></span> <?= htmlspecialchars($course['programme_name']) ?></p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="bg-primary-light text-primary-custom rounded px-3 py-2 d-inline-block">
                        <span class="fw-bold fs-5"><?= count($students) ?></span>
                        <span class="text-muted small d-block">Total Students</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="clean-card p-4">
            <?php if (count($students) > 0): ?>
                <form action="manage.php?course_id=<?= $course_id ?>" method="POST">
                    <input type="hidden" name="action" value="save_grades">
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Reg Number</th>
                                    <th style="width: 150px;">Score (%)</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $s): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($s['email']) ?></small>
                                        </td>
                                        <td>
                                            <?php if ($s['reg_number']): ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($s['reg_number']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted small">Not Assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" max="100" name="grades[<?= $s['enrollment_id'] ?>][score]" class="form-control text-center clean-form-control" placeholder="0-100" value="<?= htmlspecialchars($s['score'] ?? '') ?>">
                                        </td>
                                        <td>
                                            <input type="text" name="grades[<?= $s['enrollment_id'] ?>][remarks]" class="form-control clean-form-control" placeholder="Add remarks..." value="<?= htmlspecialchars($s['remarks'] ?? '') ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary-custom px-4 py-2"><i class="fa-solid fa-save me-2"></i> Save Grades</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fa-solid fa-users text-muted mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                    <h5 class="text-muted fw-bold">No students found</h5>
                    <p class="text-muted small">There are currently no active students enrolled in this course or its parent programme.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
