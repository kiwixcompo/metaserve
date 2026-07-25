<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [5, 6])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Fetch available programmes and their modules (courses)
$stmt = $conn->query("SELECT * FROM programmes WHERE is_active = 1");
$programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT c.* FROM courses c JOIN programmes p ON c.programme_id = p.id WHERE p.is_active = 1 ORDER BY c.programme_id, c.name ASC");
$allCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$modulesByProg = [];
foreach($allCourses as $c) {
    $modulesByProg[$c['programme_id']][] = $c;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_item'])) {
    $item = $_POST['selected_item'];
    $prog_id = null;
    
    if (strpos($item, 'prog_') === 0) {
        $prog_id = str_replace('prog_', '', $item);
    }
    
    if ($prog_id) {
        // Check if already enrolled in this exact option
        $stmt = $conn->prepare("SELECT id FROM enrollments WHERE user_id = :uid AND programme_id = :pid");
        $stmt->execute(['uid' => $_SESSION['user_id'], 'pid' => $prog_id]);
        
        if ($stmt->rowCount() > 0) {
            $error = "You are already enrolled or have a pending enrollment in this programme.";
        } else {
            // Enroll
            $stmt = $conn->prepare("INSERT INTO enrollments (user_id, programme_id, status) VALUES (:uid, :pid, 'pending')");
            $stmt->execute(['uid' => $_SESSION['user_id'], 'pid' => $prog_id]);
            
            $_SESSION['success_msg'] = "Successfully enrolled! Please complete your payment to access the courses.";
            header("Location: index.php");
            exit();
        }
    } else {
        $error = "Invalid selection.";
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .prog-card { transition: all 0.3s ease; border-radius: 12px; border: 1px solid #eee; background: #fff; cursor: pointer; }
    .prog-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); border-color: var(--primary-color); }
    .prog-icon { width: 60px; height: 60px; border-radius: 50%; background: var(--primary-light); color: var(--primary-color); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem; }
    .module-list { max-height: 250px; overflow-y: auto; }
</style>

<section class="py-5 mt-5 bg-light min-vh-100">
    <div class="container">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1"><i class="fa-solid fa-graduation-cap text-primary-custom me-2"></i> Enroll in a Course</h2>
                <p class="text-muted mb-0">Select a course below to preview its curriculum and enroll.</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i> Dashboard</a>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger shadow-sm"><i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <?php foreach($programmes as $prog): ?>
                <?php 
                    $progModules = $modulesByProg[$prog['id']] ?? []; 
                    $icon = ($prog['id'] == 1) ? 'fa-computer' : 'fa-code';
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="prog-card p-4 h-100 d-flex flex-column" data-bs-toggle="modal" data-bs-target="#previewModal<?= $prog['id'] ?>">
                        <div class="prog-icon"><i class="fa-solid <?= $icon ?>"></i></div>
                        <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($prog['name']) ?></h5>
                        <p class="text-muted small flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= htmlspecialchars($prog['description']) ?>
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <span class="fw-bold text-primary-custom">&#8358;<?= number_format($prog['cost'], 0) ?></span>
                            <span class="badge bg-light text-dark"><i class="fa-regular fa-clock me-1"></i> <?= $prog['duration_weeks'] ?> Weeks</span>
                        </div>
                        
                        <button class="btn btn-primary-custom w-100 mt-3 fw-bold">Preview & Enroll</button>
                    </div>
                </div>

                <!-- Preview Modal -->
                <div class="modal fade" id="previewModal<?= $prog['id'] ?>" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                      <div class="modal-header bg-light border-0 p-4">
                        <h4 class="modal-title fw-bold text-dark" id="exampleModalLabel"><?= htmlspecialchars($prog['name']) ?></h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body p-4">
                        
                        <div class="mb-4">
                            <h6 class="fw-bold text-primary-custom text-uppercase mb-2">About This Course</h6>
                            <p class="text-muted" style="line-height: 1.6; font-size: 1.05rem;"><?= nl2br(htmlspecialchars($prog['description'])) ?></p>
                        </div>
                        
                        <div class="d-flex gap-3 mb-4">
                            <div class="bg-light rounded p-3 text-center flex-fill border">
                                <i class="fa-solid fa-coins text-secondary-custom fs-4 mb-2 d-block"></i>
                                <span class="fw-bold fs-5 text-dark">&#8358;<?= number_format($prog['cost'], 0) ?></span>
                                <div class="small text-muted">Tuition Fee</div>
                            </div>
                            <div class="bg-light rounded p-3 text-center flex-fill border">
                                <i class="fa-solid fa-calendar-week text-primary-custom fs-4 mb-2 d-block"></i>
                                <span class="fw-bold fs-5 text-dark"><?= $prog['duration_weeks'] ?> Weeks</span>
                                <div class="small text-muted">Duration</div>
                            </div>
                            <div class="bg-light rounded p-3 text-center flex-fill border">
                                <i class="fa-solid fa-layer-group text-dark fs-4 mb-2 d-block"></i>
                                <span class="fw-bold fs-5 text-dark"><?= count($progModules) ?></span>
                                <div class="small text-muted">Modules</div>
                            </div>
                        </div>

                        <div>
                            <h6 class="fw-bold text-dark text-uppercase mb-3"><i class="fa-solid fa-book-open me-2 text-primary-custom"></i> Curriculum Modules</h6>
                            <div class="module-list pe-2">
                                <?php if(empty($progModules)): ?>
                                    <div class="alert alert-secondary border-0">No modules uploaded yet.</div>
                                <?php else: ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach($progModules as $mod): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0 py-3">
                                                <div>
                                                    <span class="badge bg-secondary-subtle text-dark border me-2"><?= htmlspecialchars($mod['course_code']) ?></span>
                                                    <span class="fw-bold text-dark"><?= htmlspecialchars($mod['name']) ?></span>
                                                </div>
                                                <i class="fa-solid fa-check text-success"></i>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>

                      </div>
                      <div class="modal-footer border-0 bg-light p-4">
                        <form method="POST" action="enroll.php" class="w-100 d-flex gap-2">
                            <input type="hidden" name="selected_item" value="prog_<?= $prog['id'] ?>">
                            <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary-custom flex-grow-1 py-2 fw-bold fs-5">Confirm Enrollment <i class="fa-solid fa-arrow-right ms-2"></i></button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
