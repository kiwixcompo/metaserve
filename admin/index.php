<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Models/Admin.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$adminModel = new Admin();
$metrics = $adminModel->getMetrics();
$allUsers = $adminModel->getAllUsers();
$facilitators = $adminModel->getFacilitators();

// Fetch Data for new tabs
$db = new Database();
$conn = $db->getConnection();
$deptsStmt = $conn->query("SELECT * FROM departments ORDER BY faculty, name");
$allDepts = $deptsStmt->fetchAll(PDO::FETCH_ASSOC);

$coursesStmt = $conn->query("SELECT c.*, p.name as prog_name FROM courses c JOIN programmes p ON c.programme_id = p.id ORDER BY c.name");
$allCourses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

$activeTab = $_GET['tab'] ?? 'overview';

require_once __DIR__ . '/../includes/header.php';
?>
<section class="py-5 mt-5 bg-light min-vh-100">
    <div class="container">
        <h2 class="fw-bold text-dark mb-4"><i class="fa-solid fa-shield-halved text-primary-custom me-2"></i> Super Administrator</h2>
        
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0"><i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($_SESSION['success_msg']); unset($_SESSION['success_msg']); ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0"><i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($_SESSION['error_msg']); unset($_SESSION['error_msg']); ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <div class="row gy-4">
            <div class="col-md-3">
                <div class="list-group clean-card border-0 shadow-sm" id="adminTabs" role="tablist">
                    <a href="#overview" class="list-group-item list-group-item-action fw-bold <?= $activeTab === 'overview' ? 'active' : 'text-muted' ?>" data-bs-toggle="tab" role="tab" style="<?= $activeTab === 'overview' ? 'background-color: var(--primary-color); border-color: var(--primary-color); color: white !important;' : '' ?>"><i class="fa-solid fa-gauge me-2"></i> Overview</a>
                    <a href="#users" class="list-group-item list-group-item-action fw-bold <?= $activeTab === 'users' ? 'active' : 'text-muted' ?>" data-bs-toggle="tab" role="tab" style="<?= $activeTab === 'users' ? 'background-color: var(--primary-color); border-color: var(--primary-color); color: white !important;' : '' ?>"><i class="fa-solid fa-users me-2"></i> Manage Users</a>
                    <a href="#facilitators" class="list-group-item list-group-item-action fw-bold <?= $activeTab === 'facilitators' ? 'active' : 'text-muted' ?>" data-bs-toggle="tab" role="tab" style="<?= $activeTab === 'facilitators' ? 'background-color: var(--primary-color); border-color: var(--primary-color); color: white !important;' : '' ?>"><i class="fa-solid fa-chalkboard-user me-2"></i> Facilitators</a>
                    <a href="#programmes" class="list-group-item list-group-item-action fw-bold <?= $activeTab === 'programmes' ? 'active' : 'text-muted' ?>" data-bs-toggle="tab" role="tab" style="<?= $activeTab === 'programmes' ? 'background-color: var(--primary-color); border-color: var(--primary-color); color: white !important;' : '' ?>"><i class="fa-solid fa-building-columns me-2"></i> Programmes</a>
                    <a href="#skills" class="list-group-item list-group-item-action fw-bold <?= $activeTab === 'skills' ? 'active' : 'text-muted' ?>" data-bs-toggle="tab" role="tab" style="<?= $activeTab === 'skills' ? 'background-color: var(--primary-color); border-color: var(--primary-color); color: white !important;' : '' ?>"><i class="fa-solid fa-laptop-code me-2"></i> ICT Skills</a>
                    <a href="#settings" class="list-group-item list-group-item-action fw-bold <?= $activeTab === 'settings' ? 'active' : 'text-muted' ?>" data-bs-toggle="tab" role="tab" style="<?= $activeTab === 'settings' ? 'background-color: var(--primary-color); border-color: var(--primary-color); color: white !important;' : '' ?>"><i class="fa-solid fa-gear me-2"></i> Settings</a>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="tab-content">
                    
                    <!-- Overview Tab -->
                    <div class="tab-pane fade <?= $activeTab === 'overview' ? 'show active' : '' ?>" id="overview" role="tabpanel">
                        <div class="row gy-4">
                            <div class="col-md-6">
                                <div class="clean-card p-4 border-start border-4 border-primary shadow-sm h-100">
                                    <h6 class="text-muted mb-2 text-uppercase fw-bold">Total System Users</h6>
                                    <h2 class="fw-bold text-dark mb-0"><?= number_format($metrics['total_users']) ?></h2>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="clean-card p-4 border-start border-4 shadow-sm h-100" style="border-color: var(--secondary-color) !important;">
                                    <h6 class="text-muted mb-2 text-uppercase fw-bold">Active Facilitators</h6>
                                    <h2 class="fw-bold text-dark mb-0"><?= number_format($metrics['active_facilitators']) ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Users Tab -->
                    <div class="tab-pane fade <?= $activeTab === 'users' ? 'show active' : '' ?>" id="users" role="tabpanel">
                        <div class="clean-card p-4 shadow-sm">
                            <h5 class="fw-bold text-dark mb-4">All Registered Users</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Registered</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($allUsers as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($user['role_name']) ?></span></td>
                                            <td class="small text-muted"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                            <td>
                                                <?php if($user['role_name'] !== 'Super Administrator'): ?>
                                                    <a href="<?= BASE_URL ?>src/Controllers/AdminController.php?action=delete_user&id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to permanently delete this user?');"><i class="fa-solid fa-trash"></i></a>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-secondary" disabled><i class="fa-solid fa-shield"></i></button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Facilitators Tab -->
                    <div class="tab-pane fade <?= $activeTab === 'facilitators' ? 'show active' : '' ?>" id="facilitators" role="tabpanel">
                        <div class="clean-card p-4 mb-4 shadow-sm d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold text-dark mb-0">Facilitator Management</h5>
                            <button class="btn btn-primary-custom px-4" data-bs-toggle="modal" data-bs-target="#addFacilitatorModal">
                                <i class="fa-solid fa-user-plus me-2"></i> Add Facilitator
                            </button>
                        </div>
                        
                        <div class="clean-card p-4 shadow-sm">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Email Address</th>
                                            <th>Date Added</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($facilitators)): ?>
                                            <tr><td colspan="3" class="text-center text-muted py-4">No facilitators found.</td></tr>
                                        <?php endif; ?>
                                        <?php foreach($facilitators as $fac): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($fac['first_name'] . ' ' . $fac['last_name']) ?></td>
                                            <td><?= htmlspecialchars($fac['email']) ?></td>
                                            <td class="small text-muted"><?= date('M j, Y', strtotime($fac['created_at'])) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Programmes Tab -->
                    <div class="tab-pane fade <?= $activeTab === 'programmes' ? 'show active' : '' ?>" id="programmes" role="tabpanel">
                        <div class="clean-card p-4 mb-4 shadow-sm d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold text-dark mb-0">Academic Programmes</h5>
                            <button class="btn btn-primary-custom px-4" data-bs-toggle="modal" data-bs-target="#addProgrammeModal">
                                <i class="fa-solid fa-plus me-2"></i> Add Programme
                            </button>
                        </div>
                        <div class="clean-card p-4 shadow-sm">
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Faculty</th>
                                            <th>Programme Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($allDepts as $dept): ?>
                                        <tr>
                                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($dept['faculty']) ?></span></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($dept['name']) ?></td>
                                            <td>
                                                <a href="<?= BASE_URL ?>src/Controllers/AdminController.php?action=delete_programme&id=<?= $dept['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this programme? All related skill mappings will be removed.');"><i class="fa-solid fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Skills Tab -->
                    <div class="tab-pane fade <?= $activeTab === 'skills' ? 'show active' : '' ?>" id="skills" role="tabpanel">
                        <div class="clean-card p-4 mb-4 shadow-sm d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold text-dark mb-0">ICT Skills / Courses</h5>
                            <button class="btn btn-primary-custom px-4" data-bs-toggle="modal" data-bs-target="#addSkillModal">
                                <i class="fa-solid fa-plus me-2"></i> Add Skill
                            </button>
                        </div>
                        <div class="clean-card p-4 shadow-sm">
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Skill Code</th>
                                            <th>Skill Name</th>
                                            <th>Master Programme</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($allCourses as $c): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($c['course_code']) ?></span></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($c['name']) ?></td>
                                            <td class="small text-muted"><?= htmlspecialchars($c['prog_name']) ?></td>
                                            <td>
                                                <a href="<?= BASE_URL ?>src/Controllers/AdminController.php?action=delete_skill&id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this skill?');"><i class="fa-solid fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Settings Tab -->
                    <div class="tab-pane fade <?= $activeTab === 'settings' ? 'show active' : '' ?>" id="settings" role="tabpanel">
                        <div class="clean-card p-4 shadow-sm">
                            <h5 class="fw-bold text-dark mb-4">System Settings</h5>
                            <div class="alert alert-info border-0"><i class="fa-solid fa-gear me-2"></i> Settings functionality will be implemented in the configuration module.</div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add Facilitator Modal -->
<div class="modal fade" id="addFacilitatorModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content clean-card border-0">
      <form action="<?= BASE_URL ?>src/Controllers/AdminController.php?action=add_facilitator" method="POST">
      <div class="modal-header border-bottom border-light">
        <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-user-plus text-primary-custom me-2"></i> Add Facilitator</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="alert alert-info small border-0 bg-primary-light text-primary-custom"><i class="fa-solid fa-circle-info me-2"></i> Their role will automatically be assigned as 'Facilitator'.</div>
        <div class="mb-3">
            <label class="form-label text-muted small fw-bold">First Name</label>
            <input type="text" name="first_name" class="form-control clean-form-control" required placeholder="Jane">
        </div>
        <div class="mb-3">
            <label class="form-label text-muted small fw-bold">Last Name</label>
            <input type="text" name="last_name" class="form-control clean-form-control" required placeholder="Doe">
        </div>
        <div class="mb-3">
            <label class="form-label text-muted small fw-bold">Email Address</label>
            <input type="email" name="email" class="form-control clean-form-control" required placeholder="jane.doe@metaserve.com">
        </div>
        <div class="mb-3">
            <label class="form-label text-muted small fw-bold">Temporary Password</label>
            <input type="text" name="password" class="form-control clean-form-control" value="Password@123" required>
        </div>
      </div>
      <div class="modal-footer border-top border-light">
        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary-custom px-4">Create Account</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Programme Modal -->
<div class="modal fade" id="addProgrammeModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content clean-card border-0">
      <form action="<?= BASE_URL ?>src/Controllers/AdminController.php?action=add_programme" method="POST">
      <div class="modal-header border-bottom border-light">
        <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-building-columns text-primary-custom me-2"></i> Add Academic Programme</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="mb-3">
            <label class="form-label text-muted small fw-bold">Faculty Name</label>
            <input type="text" name="faculty" class="form-control clean-form-control" required placeholder="e.g. FACULTY OF ENGINEERING">
        </div>
        <div class="mb-3">
            <label class="form-label text-muted small fw-bold">Programme Name</label>
            <input type="text" name="name" class="form-control clean-form-control" required placeholder="e.g. B.Eng. Civil Engineering">
        </div>
      </div>
      <div class="modal-footer border-top border-light">
        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary-custom px-4">Add Programme</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Skill Modal -->
<div class="modal fade" id="addSkillModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content clean-card border-0">
      <form action="<?= BASE_URL ?>src/Controllers/AdminController.php?action=add_skill" method="POST">
      <div class="modal-header border-bottom border-light">
        <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-laptop-code text-primary-custom me-2"></i> Add ICT Skill</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="mb-3">
            <label class="form-label text-muted small fw-bold">Skill / Software Name</label>
            <input type="text" name="name" class="form-control clean-form-control" required placeholder="e.g. AutoCAD Civil 3D">
        </div>
        <div class="mb-3">
            <label class="form-label text-muted small fw-bold">Master Programme</label>
            <select name="programme_id" class="form-select clean-form-control" required>
                <option value="1">Metaserve Students' ICT Skills Liberation Programme</option>
            </select>
        </div>
      </div>
      <div class="modal-footer border-top border-light">
        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary-custom px-4">Add Skill</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
// Simple script to handle dynamic tab coloring for Bootstrap list-group
document.querySelectorAll('#adminTabs .list-group-item').forEach(function(el) {
    el.addEventListener('click', function() {
        document.querySelectorAll('#adminTabs .list-group-item').forEach(function(item) {
            item.style.backgroundColor = '';
            item.style.borderColor = '';
            item.classList.remove('text-white');
            item.classList.add('text-muted');
            // Remove inline !important color
            item.setAttribute('style', '');
        });
        this.style.backgroundColor = 'var(--primary-color)';
        this.style.borderColor = 'var(--primary-color)';
        this.style.color = 'white';
        this.setAttribute('style', 'background-color: var(--primary-color); border-color: var(--primary-color); color: white !important;');
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
