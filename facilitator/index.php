<?php
require_once __DIR__ . '/../config/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 4) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("
    SELECT c.id as course_id, c.name as course_name, c.course_code, p.name as programme_name, fc.assigned_at
    FROM facilitator_courses fc
    JOIN courses c ON fc.course_id = c.id
    JOIN programmes p ON c.programme_id = p.id
    WHERE fc.facilitator_id = ?
    ORDER BY p.name ASC, c.name ASC
");
$stmt->execute([$_SESSION['user_id']]);
$assignedCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<section class="py-5 mt-5 bg-light min-vh-100">
    <div class="container">
        <h2 class="fw-bold text-dark mb-4"><i class="fa-solid fa-chalkboard-user text-primary-custom me-2"></i> Facilitator Dashboard</h2>
        
        <div class="row gy-4">
            <div class="col-md-3">
                <div class="clean-card p-4 text-center border-start border-4 border-primary shadow-sm h-100">
                    <div class="bg-primary-light text-primary-custom rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                        <i class="fa-solid fa-book-open-reader"></i>
                    </div>
                    <h6 class="text-muted mb-1 text-uppercase fw-bold">Assigned Courses</h6>
                    <h2 class="fw-bold text-dark mb-0"><?= count($assignedCourses) ?></h2>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="clean-card p-4">
                    <h5 class="fw-bold mb-4">My Assigned Courses / Skills</h5>
                    
                    <?php if (count($assignedCourses) > 0): ?>
                        <div class="row gy-3">
                            <?php foreach ($assignedCourses as $course): ?>
                                <div class="col-md-6">
                                    <div class="border rounded p-3 bg-white h-100 shadow-sm" style="border-left: 4px solid var(--primary-color) !important;">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="fw-bold text-dark mb-0"><?= htmlspecialchars($course['course_name']) ?></h6>
                                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($course['course_code']) ?></span>
                                        </div>
                                        <p class="text-muted small mb-3"><i class="fa-solid fa-building-columns me-1"></i> <?= htmlspecialchars($course['programme_name']) ?></p>
                                        <div class="d-flex justify-content-between align-items-center mt-auto border-top pt-3">
                                            <small class="text-muted">Assigned: <?= date('M j, Y', strtotime($course['assigned_at'])) ?></small>
                                            <a href="manage.php?course_id=<?= $course['id'] ?? $course['course_id'] ?>" class="btn btn-sm btn-outline-custom">Manage Students <i class="fa-solid fa-arrow-right ms-1"></i></a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mt-3"><i class="fa-solid fa-info-circle me-2"></i> You have not been assigned any courses yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
