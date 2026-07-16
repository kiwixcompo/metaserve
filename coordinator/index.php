<?php
require_once __DIR__ . '/../config/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../src/Models/Coordinator.php';

$coordinatorModel = new Coordinator();
$facilitators = $coordinatorModel->getFacilitators();
$courses = $coordinatorModel->getAllCourses();
$assignments = $coordinatorModel->getAllAssignments();

$activeTab = $_GET['tab'] ?? 'overview';
?>
<section class="py-5 mt-5 bg-light min-vh-100">
    <div class="container">
        <h2 class="fw-bold text-dark mb-4"><i class="fa-solid fa-list-check text-primary-custom me-2"></i> Programme Coordinator Dashboard</h2>

        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0"><i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($_SESSION['success_msg']); unset($_SESSION['success_msg']); ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0"><i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($_SESSION['error_msg']); unset($_SESSION['error_msg']); ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row gy-4">
            <div class="col-md-3">
                <div class="list-group clean-card border-0 shadow-sm" id="coordTabs" role="tablist">
                    <a href="#overview" class="list-group-item list-group-item-action fw-bold <?= $activeTab === 'overview' ? 'active' : 'text-muted' ?>" data-bs-toggle="tab" role="tab" style="<?= $activeTab === 'overview' ? 'background-color: var(--primary-color); border-color: var(--primary-color); color: white !important;' : '' ?>"><i class="fa-solid fa-gauge me-2"></i> Overview</a>
                    <a href="#assignments" class="list-group-item list-group-item-action fw-bold <?= $activeTab === 'assignments' ? 'active' : 'text-muted' ?>" data-bs-toggle="tab" role="tab" style="<?= $activeTab === 'assignments' ? 'background-color: var(--primary-color); border-color: var(--primary-color); color: white !important;' : '' ?>"><i class="fa-solid fa-chalkboard-user me-2"></i> Assign Facilitators</a>
                </div>
            </div>

            <div class="col-md-9">
                <div class="tab-content">
                    
                    <!-- Overview Tab -->
                    <div class="tab-pane fade <?= $activeTab === 'overview' ? 'show active' : '' ?>" id="overview" role="tabpanel">
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="clean-card p-4 border-start border-4 border-primary shadow-sm h-100">
                                    <h6 class="text-muted mb-2 text-uppercase fw-bold">Active Facilitators</h6>
                                    <h2 class="fw-bold text-dark mb-0"><?= count($facilitators) ?></h2>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="clean-card p-4 border-start border-4 border-secondary shadow-sm h-100">
                                    <h6 class="text-muted mb-2 text-uppercase fw-bold">Active Course Assignments</h6>
                                    <h2 class="fw-bold text-dark mb-0"><?= count($assignments) ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assignments Tab -->
                    <div class="tab-pane fade <?= $activeTab === 'assignments' ? 'show active' : '' ?>" id="assignments" role="tabpanel">
                        
                        <div class="clean-card p-4 mb-4">
                            <h5 class="fw-bold mb-4">Assign Course to Facilitator</h5>
                            <form action="<?= BASE_URL ?>src/Controllers/CoordinatorController.php" method="POST">
                                <input type="hidden" name="action" value="assign_course">
                                
                                <div class="row gy-4">
                                    <div class="col-12">
                                        <label class="form-label text-muted small fw-bold">Select Facilitator</label>
                                        <select name="facilitator_id" class="form-select clean-form-control" required>
                                            <option value="">-- Choose Facilitator --</option>
                                            <?php foreach ($facilitators as $fac): ?>
                                                <option value="<?= $fac['id'] ?>"><?= htmlspecialchars($fac['first_name'] . ' ' . $fac['last_name']) ?> (<?= htmlspecialchars($fac['email']) ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label text-muted small fw-bold">Select Course(s) to Assign</label>
                                        <div class="input-group mb-2">
                                            <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                                            <input type="text" class="form-control border-start-0 ps-0" id="coordCourseSearch" placeholder="Search courses, codes, or programmes...">
                                        </div>
                                        <div class="border rounded p-3 bg-white overflow-auto custom-scrollbar" style="max-height: 250px;">
                                            <?php foreach ($courses as $c): ?>
                                                <div class="form-check mb-2 coord-course-item">
                                                    <input class="form-check-input coord-course-checkbox" type="checkbox" name="course_ids[]" value="<?= $c['id'] ?>" id="course_<?= $c['id'] ?>" data-name="<?= htmlspecialchars($c['name']) ?>" data-code="<?= htmlspecialchars($c['course_code']) ?>">
                                                    <label class="form-check-label d-block" style="cursor: pointer;" for="course_<?= $c['id'] ?>">
                                                        <span class="fw-bold coord-course-name"><?= htmlspecialchars($c['name']) ?></span> 
                                                        (<span class="coord-course-code"><?= htmlspecialchars($c['course_code']) ?></span>) 
                                                        - <small class="text-muted coord-course-prog"><?= htmlspecialchars($c['programme_name']) ?></small>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-muted small fw-bold">Selected Courses</label>
                                        <div class="border rounded p-3 bg-light d-flex flex-column" style="height: 295px;">
                                            <ul id="selectedCoursesList" class="list-unstyled mb-0 overflow-auto flex-grow-1 custom-scrollbar">
                                                <li class="text-muted small text-center mt-4 coord-empty-msg">No courses selected yet.</li>
                                            </ul>
                                            <div class="mt-3 pt-3 border-top">
                                                <button type="submit" class="btn btn-primary-custom w-100 py-2"><i class="fa-solid fa-check me-2"></i> Assign Selected</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="clean-card p-4">
                            <h5 class="fw-bold mb-4">Current Assignments</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Facilitator</th>
                                            <th>Course / Skill</th>
                                            <th>Programme</th>
                                            <th>Assigned On</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($assignments) > 0): ?>
                                            <?php foreach ($assignments as $a): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-primary-light text-primary-custom rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                                                <?= strtoupper(substr($a['first_name'], 0, 1) . substr($a['last_name'], 0, 1)) ?>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold"><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="fw-bold text-dark"><?= htmlspecialchars($a['course_name']) ?></span><br>
                                                        <small class="text-muted"><?= htmlspecialchars($a['course_code']) ?></small>
                                                    </td>
                                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($a['programme_name']) ?></span></td>
                                                    <td><small class="text-muted"><?= date('M j, Y g:i A', strtotime($a['assigned_at'])) ?></small></td>
                                                    <td class="text-end">
                                                        <form action="<?= BASE_URL ?>src/Controllers/CoordinatorController.php" method="POST" onsubmit="return confirm('Revoke this assignment?');" class="d-inline">
                                                            <input type="hidden" name="action" value="remove_assignment">
                                                            <input type="hidden" name="assignment_id" value="<?= $a['assignment_id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Revoke"><i class="fa-solid fa-trash"></i></button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5" class="text-center text-muted py-4">No courses have been assigned to any facilitators yet.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('coordCourseSearch');
    const courseItems = document.querySelectorAll('.coord-course-item');
    const checkboxes = document.querySelectorAll('.coord-course-checkbox');
    const selectedList = document.getElementById('selectedCoursesList');
    const emptyMsg = selectedList.querySelector('.coord-empty-msg');

    // Handle Search Filtering
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            courseItems.forEach(item => {
                const name = item.querySelector('.coord-course-name').textContent.toLowerCase();
                const code = item.querySelector('.coord-course-code').textContent.toLowerCase();
                const prog = item.querySelector('.coord-course-prog').textContent.toLowerCase();
                
                if (name.includes(term) || code.includes(term) || prog.includes(term)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // Handle Checkbox Selection & Sidebar Update
    function updateSidebar() {
        const selected = Array.from(checkboxes).filter(cb => cb.checked);
        
        // Clear all except empty message
        Array.from(selectedList.children).forEach(child => {
            if (!child.classList.contains('coord-empty-msg')) {
                child.remove();
            }
        });

        if (selected.length === 0) {
            emptyMsg.style.display = 'block';
        } else {
            emptyMsg.style.display = 'none';
            selected.forEach(cb => {
                const name = cb.getAttribute('data-name');
                const code = cb.getAttribute('data-code');
                
                const li = document.createElement('li');
                li.className = 'mb-2 p-2 bg-white border rounded shadow-sm d-flex justify-content-between align-items-center';
                li.innerHTML = `
                    <div>
                        <div class="fw-bold text-dark small" style="line-height:1.2;">${name}</div>
                        <small class="text-muted" style="font-size:0.75rem;">${code}</small>
                    </div>
                    <button type="button" class="btn btn-sm text-danger p-0 ms-2 coord-remove-btn" data-target="${cb.id}">
                        <i class="fa-solid fa-times"></i>
                    </button>
                `;
                selectedList.appendChild(li);
            });
        }
    }

    // Listen to checkbox changes
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSidebar);
    });

    // Listen to remove buttons in sidebar
    selectedList.addEventListener('click', function(e) {
        const btn = e.target.closest('.coord-remove-btn');
        if (btn) {
            const targetId = btn.getAttribute('data-target');
            const targetCb = document.getElementById(targetId);
            if (targetCb) {
                targetCb.checked = false;
                updateSidebar();
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
