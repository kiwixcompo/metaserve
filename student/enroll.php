<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [5, 6])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Fetch available programmes for reference if needed
$stmt = $conn->query("SELECT * FROM programmes WHERE is_active = 1");
$programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
$progDict = array_column($programmes, null, 'id');

// Fetch all active courses
$stmt = $conn->query("SELECT c.* FROM courses c JOIN programmes p ON c.programme_id = p.id WHERE p.is_active = 1 ORDER BY c.name ASC");
$allCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_item'])) {
    $item = $_POST['selected_item'];
    $prog_id = null;
    $course_id = null;
    
    if (strpos($item, 'course_') === 0) {
        $course_id = str_replace('course_', '', $item);
        // Find parent programme
        $stmt = $conn->prepare("SELECT programme_id FROM courses WHERE id = ?");
        $stmt->execute([$course_id]);
        $prog_id = $stmt->fetchColumn();
    }
    
    if ($prog_id && $course_id) {
        // Check if already enrolled in this exact course
        $stmt = $conn->prepare("SELECT id FROM enrollments WHERE user_id = :uid AND course_id = :cid");
        $stmt->execute(['uid' => $_SESSION['user_id'], 'cid' => $course_id]);
        
        if ($stmt->rowCount() > 0) {
            $error = "You are already enrolled or have a pending enrollment in this course.";
        } else {
            // Enroll
            $stmt = $conn->prepare("INSERT INTO enrollments (user_id, programme_id, course_id, status) VALUES (:uid, :pid, :cid, 'pending')");
            $stmt->execute(['uid' => $_SESSION['user_id'], 'pid' => $prog_id, 'cid' => $course_id]);
            
            $_SESSION['success_msg'] = "Successfully enrolled in the course! Please complete your payment to access the modules.";
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

        <div class="row mb-4">
            <div class="col-md-6 offset-md-3">
                <div class="input-group input-group-lg shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                    <input type="text" id="courseSearch" class="form-control border-start-0" placeholder="Search for a skill or software (e.g. Adobe, Python)...">
                </div>
            </div>
        </div>

        <div class="row g-4" id="courseGrid">
            <?php foreach($allCourses as $course): ?>
                <?php 
                    $parentProg = $progDict[$course['programme_id']];
                ?>
                <div class="col-md-6 col-lg-4 course-item" data-name="<?= strtolower(htmlspecialchars($course['name'])) ?>" data-desc="<?= strtolower(htmlspecialchars($course['description'])) ?>">
                    <div class="prog-card p-4 h-100 d-flex flex-column" data-bs-toggle="modal" data-bs-target="#previewModal<?= $course['id'] ?>">
                        <div class="prog-icon"><i class="fa-solid fa-laptop-code"></i></div>
                        <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($course['name']) ?></h5>
                        <p class="text-muted small flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= htmlspecialchars($course['description']) ?>
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top" style="min-width: 0;">
                            <span class="badge bg-light text-dark text-truncate w-100 text-start" title="<?= htmlspecialchars($parentProg['name']) ?>"><i class="fa-solid fa-layer-group me-1"></i> <?= htmlspecialchars($parentProg['name']) ?></span>
                        </div>
                        
                        <button class="btn btn-primary-custom w-100 mt-3 fw-bold">Preview & Enroll</button>
                    </div>
                </div>

                <!-- Preview Modal -->
                <div class="modal fade" id="previewModal<?= $course['id'] ?>" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                      <div class="modal-header bg-light border-0 p-4">
                        <h4 class="modal-title fw-bold text-dark" id="exampleModalLabel"><?= htmlspecialchars($course['name']) ?></h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body p-4">
                        
                        <div class="mb-4">
                            <h6 class="fw-bold text-primary-custom text-uppercase mb-2">About This Skill</h6>
                            <p class="text-muted" style="line-height: 1.6; font-size: 1.05rem;"><?= nl2br(htmlspecialchars($course['description'])) ?></p>
                        </div>
                        
                        <div class="d-flex gap-3 mb-4">
                            <div class="bg-light rounded p-3 text-center flex-fill border">
                                <i class="fa-solid fa-coins text-secondary-custom fs-4 mb-2 d-block"></i>
                                <span class="fw-bold fs-5 text-dark">&#8358;<?= number_format($parentProg['cost'], 0) ?></span>
                                <div class="small text-muted">Enrollment Fee</div>
                            </div>
                            <div class="bg-light rounded p-3 text-center flex-fill border">
                                <i class="fa-solid fa-calendar-week text-primary-custom fs-4 mb-2 d-block"></i>
                                <span class="fw-bold fs-5 text-dark"><?= $parentProg['duration_weeks'] ?> Weeks</span>
                                <div class="small text-muted">Programme Duration</div>
                            </div>
                        </div>

                        <div class="alert alert-info border-0">
                            <i class="fa-solid fa-circle-info me-2"></i> This skill is part of the <strong><?= htmlspecialchars($parentProg['name']) ?></strong>. The enrollment fee covers your full participation.
                        </div>

                      </div>
                      <div class="modal-footer border-0 bg-light p-4">
                        <form method="POST" action="enroll.php" class="w-100 d-flex gap-2">
                            <input type="hidden" name="selected_item" value="course_<?= $course['id'] ?>">
                            <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary-custom flex-grow-1 py-2 fw-bold fs-5">Confirm Enrollment <i class="fa-solid fa-arrow-right ms-2"></i></button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="d-flex justify-content-center mt-5">
            <nav>
                <ul class="pagination pagination-lg" id="paginationControls">
                    <!-- Pagination injected via JS -->
                </ul>
            </nav>
        </div>

    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('courseSearch');
    const courseItems = Array.from(document.querySelectorAll('.course-item'));
    const paginationControls = document.getElementById('paginationControls');
    
    let filteredItems = [...courseItems];
    let currentPage = 1;
    const itemsPerPage = 12;

    function renderGrid() {
        // Hide all
        courseItems.forEach(item => item.style.display = 'none');
        
        // Show only current page of filtered
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        
        const pageItems = filteredItems.slice(start, end);
        pageItems.forEach(item => item.style.display = 'block');
        
        renderPagination();
    }

    function renderPagination() {
        paginationControls.innerHTML = '';
        const totalPages = Math.ceil(filteredItems.length / itemsPerPage);
        
        if (totalPages <= 1) return;
        
        // Prev button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
        prevLi.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPage > 1) { currentPage--; renderGrid(); }
        });
        paginationControls.appendChild(prevLi);
        
        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${currentPage === i ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.addEventListener('click', (e) => {
                e.preventDefault();
                currentPage = i;
                renderGrid();
            });
            paginationControls.appendChild(li);
        }
        
        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
        nextLi.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPage < totalPages) { currentPage++; renderGrid(); }
        });
        paginationControls.appendChild(nextLi);
    }

    searchInput.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        filteredItems = courseItems.filter(item => {
            const name = item.getAttribute('data-name');
            const desc = item.getAttribute('data-desc');
            return name.includes(term) || desc.includes(term);
        });
        currentPage = 1;
        renderGrid();
    });

    // Initial render
    renderGrid();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
