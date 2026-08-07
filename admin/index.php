<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Models/Admin.php';
require_once __DIR__ . '/../src/Models/Settings.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$adminModel = new Admin();
$metrics = $adminModel->getMetrics();
$allUsers = $adminModel->getAllUsers();
$facilitators = $adminModel->getFacilitators();
$allEnrollments = $adminModel->getAllEnrollments();

$settingsModel = new Settings();
$settingsData = $settingsModel->getAllSettings();

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
                    <a href="#enrollments" class="list-group-item list-group-item-action fw-bold <?= $activeTab === 'enrollments' ? 'active' : 'text-muted' ?>" data-bs-toggle="tab" role="tab" style="<?= $activeTab === 'enrollments' ? 'background-color: var(--primary-color); border-color: var(--primary-color); color: white !important;' : '' ?>"><i class="fa-solid fa-graduation-cap me-2"></i> Enrollments</a>
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
                        
                        <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">Registration Statistics</h5>
                        <div class="row gy-3 mb-4">
                            <div class="col-md-4">
                                <div class="clean-card p-3 border-start border-4 border-primary shadow-sm h-100">
                                    <h6 class="text-muted mb-1 small text-uppercase fw-bold">Total Candidates</h6>
                                    <h3 class="fw-bold text-dark mb-0"><?= number_format($metrics['total_candidates'] ?? 0) ?></h3>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="clean-card p-3 border-start border-4 border-info shadow-sm h-100">
                                    <h6 class="text-muted mb-1 small text-uppercase fw-bold">TSU Students</h6>
                                    <h3 class="fw-bold text-dark mb-0"><?= number_format($metrics['tsu_students'] ?? 0) ?></h3>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="clean-card p-3 border-start border-4 border-secondary shadow-sm h-100">
                                    <h6 class="text-muted mb-1 small text-uppercase fw-bold">External Candidates</h6>
                                    <h3 class="fw-bold text-dark mb-0"><?= number_format($metrics['external_candidates'] ?? 0) ?></h3>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="clean-card p-3 border-start border-4 shadow-sm h-100" style="border-color: #198754 !important;">
                                    <h6 class="text-muted mb-1 small text-uppercase fw-bold">Online Registrations</h6>
                                    <h3 class="fw-bold text-dark mb-0"><?= number_format($metrics['online_regs'] ?? 0) ?></h3>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="clean-card p-3 border-start border-4 shadow-sm h-100" style="border-color: #ffc107 !important;">
                                    <h6 class="text-muted mb-1 small text-uppercase fw-bold">Physical Form Registrations</h6>
                                    <h3 class="fw-bold text-dark mb-0"><?= number_format($metrics['physical_regs'] ?? 0) ?></h3>
                                </div>
                            </div>
                        </div>

                        <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">Revenue Breakdown</h5>
                        <div class="row gy-3 mb-4">
                            <div class="col-md-4">
                                <div class="clean-card p-3 bg-light border-0 shadow-sm h-100">
                                    <h6 class="text-muted mb-1 small fw-bold">Form Sales (Admin Charge)</h6>
                                    <h4 class="fw-bold text-primary-custom mb-0">&#8358;<?= number_format($metrics['form_sales'] ?? 0, 2) ?></h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="clean-card p-3 bg-light border-0 shadow-sm h-100">
                                    <h6 class="text-muted mb-1 small fw-bold">Basic (TSU)</h6>
                                    <h4 class="fw-bold text-dark mb-0">&#8358;<?= number_format($metrics['basic_tsu'] ?? 0, 2) ?></h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="clean-card p-3 bg-light border-0 shadow-sm h-100">
                                    <h6 class="text-muted mb-1 small fw-bold">Basic (External)</h6>
                                    <h4 class="fw-bold text-dark mb-0">&#8358;<?= number_format($metrics['basic_external'] ?? 0, 2) ?></h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="clean-card p-3 bg-light border-0 shadow-sm h-100">
                                    <h6 class="text-muted mb-1 small fw-bold">Professional (TSU)</h6>
                                    <h4 class="fw-bold text-dark mb-0">&#8358;<?= number_format($metrics['prof_tsu'] ?? 0, 2) ?></h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="clean-card p-3 bg-light border-0 shadow-sm h-100">
                                    <h6 class="text-muted mb-1 small fw-bold">Professional (External)</h6>
                                    <h4 class="fw-bold text-dark mb-0">&#8358;<?= number_format($metrics['prof_external'] ?? 0, 2) ?></h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="clean-card p-3 border-0 shadow-sm h-100" style="background: rgba(30, 86, 49, 0.1);">
                                    <h6 class="text-primary-custom mb-1 small text-uppercase fw-bold">Total Revenue</h6>
                                    <h3 class="fw-bold text-primary-custom mb-0">&#8358;<?= number_format($metrics['total_revenue'] ?? 0, 2) ?></h3>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <!-- Users Tab -->
                    <div class="tab-pane fade <?= $activeTab === 'users' ? 'show active' : '' ?>" id="users" role="tabpanel">
                        <div class="clean-card p-4 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold text-dark mb-0">All Registered Users</h5>
                            </div>
                            
                            <!-- Filters -->
                            <div class="row g-3 mb-4 bg-light p-3 rounded border">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Search User</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white"><i class="fa-solid fa-search"></i></span>
                                        <input type="text" id="userSearchInput" class="form-control" placeholder="Search by name or email...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted">Filter by Role</label>
                                    <select id="userRoleFilter" class="form-select form-select-sm">
                                        <option value="all">All Roles</option>
                                        <option value="Super Administrator">Super Administrator</option>
                                        <option value="Head of Admin/Accounts">Head of Admin/Accounts</option>
                                        <option value="Programme Coordinator">Programme Coordinator</option>
                                        <option value="Facilitator">Facilitator</option>
                                        <option value="Student">Student (TSU)</option>
                                        <option value="External Candidate">External Candidate</option>
                                        <option value="University Management">University Management</option>
                                    </select>
                                </div>
                                <div class="col-md-4 text-end d-flex align-items-end justify-content-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-download me-1"></i> Export Data
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="exportUsersToCSV(event)"><i class="fa-solid fa-file-excel text-success me-2"></i> Excel (CSV)</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="exportUsersToPDF(event)"><i class="fa-solid fa-file-pdf text-danger me-2"></i> PDF Document</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <form action="<?= BASE_URL ?>src/Controllers/AdminController.php?action=bulk_users_action" method="POST" id="bulkUsersForm">
                                <div class="row align-items-center mb-3 bg-light p-3 rounded mx-0 border" id="bulkUsersToolbar" style="display: none;">
                                    <div class="col-md-auto">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAllUsersHeader">
                                            <label class="form-check-label fw-bold" for="selectAllUsersHeader">Select All</label>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <select name="bulk_action" class="form-select form-select-sm" id="bulkActionSelect" required onchange="handleBulkActionSelect(this)">
                                            <option value="">-- Bulk Actions --</option>
                                            <option value="deactivate">Deactivate Accounts (Block Login)</option>
                                            <option value="activate">Activate Accounts (Allow Login)</option>
                                            <option value="verify_email">Force Verify Emails</option>
                                            <option value="change_password">Change Passwords</option>
                                            <option value="delete">Delete Permanently</option>
                                        </select>
                                    </div>
                                    <!-- Hidden password field, shown by JS when needed -->
                                    <div class="col-md-3" id="bulkPasswordContainer" style="display: none;">
                                        <input type="text" name="new_password" class="form-control form-control-sm" placeholder="Type new password...">
                                    </div>
                                    <div class="col-md-auto text-end">
                                        <button type="submit" class="btn btn-sm btn-primary-custom" onclick="return confirmBulkAction();"><i class="fa-solid fa-bolt me-1"></i> Apply Action</button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="usersTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 40px;">
                                                    <input class="form-check-input" type="checkbox" id="selectAllUsers">
                                                </th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Registered</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($allUsers as $user): ?>
                                            <tr class="user-row <?= ($user['is_active'] == 0) ? 'table-danger' : '' ?>" data-role="<?= htmlspecialchars($user['role_name']) ?>">
                                                <td>
                                                    <?php if($user['role_name'] !== 'Super Administrator'): ?>
                                                        <input class="form-check-input user-checkbox" type="checkbox" name="user_ids[]" value="<?= $user['id'] ?>">
                                                    <?php endif; ?>
                                                </td>
                                                <td class="user-name">
                                                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                                    <?php if($user['is_active'] == 0): ?>
                                                        <span class="badge bg-danger ms-1" style="font-size: 0.65em;">Deactivated</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="user-email"><?= htmlspecialchars($user['email']) ?></td>
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
                            </form>
                        </div>
                    </div>
                    
                    <!-- Enrollments Tab -->
                    <div class="tab-pane fade <?= $activeTab === 'enrollments' ? 'show active' : '' ?>" id="enrollments" role="tabpanel">
                        <div class="clean-card p-4 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="fw-bold text-dark mb-0">Student Enrollments</h5>
                            </div>
                            
                            <form action="<?= BASE_URL ?>src/Controllers/AdminController.php?action=bulk_change_course" method="POST" id="bulkCourseForm">
                                <div class="row align-items-center mb-3 bg-light p-3 rounded mx-0 border">
                                    <div class="col-md-auto">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAllEnrollments">
                                            <label class="form-check-label fw-bold" for="selectAllEnrollments">Select All</label>
                                        </div>
                                    </div>
                                    <div class="col-md">
                                        <select name="new_course_id" class="form-select form-select-sm" required>
                                            <option value="">-- Move Selected to New Course --</option>
                                            <?php foreach($allCourses as $c): ?>
                                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-auto text-end">
                                        <button type="submit" class="btn btn-sm btn-primary-custom" onclick="return confirm('Change course for all selected enrollments?');"><i class="fa-solid fa-right-left me-1"></i> Apply Change</button>
                                    </div>
                                </div>
                            
                                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th style="width: 40px;"></th>
                                                <th>Student</th>
                                                <th>Course / Programme</th>
                                                <th>Status</th>
                                                <th>Enrolled Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(empty($allEnrollments)): ?>
                                                <tr><td colspan="6" class="text-center text-muted py-4">No enrollments found.</td></tr>
                                            <?php endif; ?>
                                            <?php foreach($allEnrollments as $enr): ?>
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input enrollment-checkbox" type="checkbox" name="enrollment_ids[]" value="<?= $enr['enrollment_id'] ?>">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($enr['first_name'] . ' ' . $enr['last_name']) ?></div>
                                                    <div class="small text-muted"><?= htmlspecialchars($enr['email']) ?></div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary-light text-primary-custom border me-1"><i class="fa-solid fa-laptop-code"></i> <?= htmlspecialchars($enr['course_name'] ?? 'None') ?></span>
                                                    <br><span class="small text-muted"><?= htmlspecialchars($enr['prog_name']) ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $enr['status'] == 'active' ? 'success' : ($enr['status'] == 'completed' ? 'primary' : 'warning') ?>"><?= ucfirst($enr['status']) ?></span>
                                                </td>
                                                <td class="small text-muted"><?= date('M j, Y', strtotime($enr['enrolled_at'])) ?></td>
                                                <td>
                                                    <a href="<?= BASE_URL ?>src/Controllers/AdminController.php?action=delete_enrollment&id=<?= $enr['enrollment_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to completely un-enroll this student from this course?');"><i class="fa-solid fa-user-xmark me-1"></i> Un-enroll</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                            
                            <script>
                            document.getElementById('selectAllEnrollments').addEventListener('change', function() {
                                const checkboxes = document.querySelectorAll('.enrollment-checkbox');
                                checkboxes.forEach(cb => cb.checked = this.checked);
                            });
                            </script>
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
                            <form action="<?= BASE_URL ?>src/Controllers/AdminController.php?action=update_settings" method="POST" enctype="multipart/form-data">
                                <h6 class="fw-bold mt-4 mb-3">Homepage Images (Leave blank to keep current)</h6>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Hero Image (Top Section)</label>
                                    <input type="file" name="hero_image" class="form-control clean-form-control img-upload-input" accept="image/*" onchange="previewImage(this)">
                                    <div class="mt-2 preview-wrapper" style="<?= empty($settingsData['hero_image']) ? 'display: none;' : '' ?>">
                                        <img src="<?= empty($settingsData['hero_image']) ? '' : BASE_URL . $settingsData['hero_image'] ?>" style="height: 50px; border-radius: 5px; cursor: zoom-in;" onclick="openImageModal(this.src)">
                                    </div>
                                </div>
                                
                                <h6 class="fw-bold mt-4 mb-3">Slider Images (Bottom Section)</h6>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Slider Image 1</label>
                                    <input type="file" name="slider_image_1" class="form-control clean-form-control img-upload-input" accept="image/*" onchange="previewImage(this)">
                                    <div class="mt-2 preview-wrapper" style="<?= empty($settingsData['slider_image_1']) ? 'display: none;' : '' ?>">
                                        <img src="<?= empty($settingsData['slider_image_1']) ? '' : BASE_URL . $settingsData['slider_image_1'] ?>" style="height: 50px; border-radius: 5px; cursor: zoom-in;" onclick="openImageModal(this.src)">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Slider Image 2</label>
                                    <input type="file" name="slider_image_2" class="form-control clean-form-control img-upload-input" accept="image/*" onchange="previewImage(this)">
                                    <div class="mt-2 preview-wrapper" style="<?= empty($settingsData['slider_image_2']) ? 'display: none;' : '' ?>">
                                        <img src="<?= empty($settingsData['slider_image_2']) ? '' : BASE_URL . $settingsData['slider_image_2'] ?>" style="height: 50px; border-radius: 5px; cursor: zoom-in;" onclick="openImageModal(this.src)">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Slider Image 3</label>
                                    <input type="file" name="slider_image_3" class="form-control clean-form-control img-upload-input" accept="image/*" onchange="previewImage(this)">
                                    <div class="mt-2 preview-wrapper" style="<?= empty($settingsData['slider_image_3']) ? 'display: none;' : '' ?>">
                                        <img src="<?= empty($settingsData['slider_image_3']) ? '' : BASE_URL . $settingsData['slider_image_3'] ?>" style="height: 50px; border-radius: 5px; cursor: zoom-in;" onclick="openImageModal(this.src)">
                                    </div>
                                </div>

                                <h6 class="fw-bold mt-4 mb-3">Contact Details</h6>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Admin/Help Desk Phone</label>
                                    <input type="text" name="contact_admin_phone" class="form-control clean-form-control" value="<?= htmlspecialchars($settingsData['contact_admin_phone'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Technical Support Phone</label>
                                    <input type="text" name="contact_tech_phone" class="form-control clean-form-control" value="<?= htmlspecialchars($settingsData['contact_tech_phone'] ?? '') ?>">
                                </div>

                                <h6 class="fw-bold mt-4 mb-3">Paystack Configuration</h6>
                                <div class="alert alert-info small py-2 mb-3">Leave blank to keep the current key unchanged.</div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Public Key</label>
                                    <input type="text" name="paystack_public_key" class="form-control clean-form-control" placeholder="<?= empty($settingsData['paystack_public_key']) ? 'Enter Public Key' : substr($settingsData['paystack_public_key'], 0, 8) . str_repeat('*', max(0, strlen($settingsData['paystack_public_key']) - 8)) ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Secret Key</label>
                                    <input type="password" name="paystack_secret_key" class="form-control clean-form-control" placeholder="<?= empty($settingsData['paystack_secret_key']) ? 'Enter Secret Key' : substr($settingsData['paystack_secret_key'], 0, 8) . str_repeat('*', 24) ?>">
                                </div>

                                <h6 class="fw-bold mt-4 mb-3">Social Media Links</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small fw-bold"><i class="fa-brands fa-facebook text-primary me-1"></i> Facebook URL</label>
                                        <input type="url" name="social_facebook" class="form-control clean-form-control" placeholder="https://facebook.com/..." value="<?= htmlspecialchars($settingsData['social_facebook'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small fw-bold"><i class="fa-brands fa-twitter text-info me-1"></i> Twitter (X) URL</label>
                                        <input type="url" name="social_twitter" class="form-control clean-form-control" placeholder="https://twitter.com/..." value="<?= htmlspecialchars($settingsData['social_twitter'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small fw-bold"><i class="fa-brands fa-instagram text-danger me-1"></i> Instagram URL</label>
                                        <input type="url" name="social_instagram" class="form-control clean-form-control" placeholder="https://instagram.com/..." value="<?= htmlspecialchars($settingsData['social_instagram'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted small fw-bold"><i class="fa-brands fa-linkedin text-primary me-1"></i> LinkedIn URL</label>
                                        <input type="url" name="social_linkedin" class="form-control clean-form-control" placeholder="https://linkedin.com/..." value="<?= htmlspecialchars($settingsData['social_linkedin'] ?? '') ?>">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary-custom px-4 mt-3">Save Settings</button>
                            </form>
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

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-header border-0 pb-0">
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center pt-0">
        <img id="expandedImg" src="" class="img-fluid rounded shadow-lg" style="max-height: 80vh;">
      </div>
    </div>
  </div>
</div>

<script>
function previewImage(input) {
    const wrapper = input.nextElementSibling;
    const img = wrapper.querySelector('img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            wrapper.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function openImageModal(src) {
    document.getElementById('expandedImg').src = src;
    var myModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
    myModal.show();
}
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

// User filtering logic
function filterUsers() {
    const searchVal = document.getElementById('userSearchInput').value.toLowerCase();
    const roleVal = document.getElementById('userRoleFilter').value;
    const rows = document.querySelectorAll('.user-row');
    
    rows.forEach(row => {
        const name = row.querySelector('.user-name').textContent.toLowerCase();
        const email = row.querySelector('.user-email').textContent.toLowerCase();
        const role = row.getAttribute('data-role');
        
        const matchesSearch = name.includes(searchVal) || email.includes(searchVal);
        const matchesRole = (roleVal === 'all') || (role === roleVal);
        
        if (matchesSearch && matchesRole) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

document.getElementById('userSearchInput')?.addEventListener('input', filterUsers);
document.getElementById('userRoleFilter')?.addEventListener('change', filterUsers);

// Export to CSV
function exportUsersToCSV(e) {
    e.preventDefault();
    let csv = [];
    const rows = document.querySelectorAll('#usersTable tr');
    
    // Header
    const cols = rows[0].querySelectorAll('th');
    let rowData = [];
    for (let j = 1; j < cols.length - 1; j++) { // Skip Checkbox and Action columns
        rowData.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
    }
    csv.push(rowData.join(','));
    
    // Data rows
    for (let i = 1; i < rows.length; i++) {
        if (rows[i].style.display !== 'none') {
            const tds = rows[i].querySelectorAll('td');
            let tData = [];
            for (let j = 1; j < tds.length - 1; j++) { // Skip Checkbox and Action columns
                tData.push('"' + tds[j].innerText.replace(/"/g, '""') + '"');
            }
            csv.push(tData.join(','));
        }
    }
    
    // Download
    const csvFile = new Blob([csv.join('\n')], {type: 'text/csv'});
    const downloadLink = document.createElement('a');
    downloadLink.download = 'metaserve_users.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
}

// Export to PDF
function exportUsersToPDF(e) {
    e.preventDefault();
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    doc.text("Metaserve Users Report", 14, 15);
    
    const rows = document.querySelectorAll('#usersTable tr');
    let head = [];
    let body = [];
    
    // Header
    const cols = rows[0].querySelectorAll('th');
    for (let j = 1; j < cols.length - 1; j++) {
        head.push(cols[j].innerText);
    }
    
    // Data rows
    for (let i = 1; i < rows.length; i++) {
        if (rows[i].style.display !== 'none') {
            const tds = rows[i].querySelectorAll('td');
            let tData = [];
            for (let j = 1; j < tds.length - 1; j++) {
                tData.push(tds[j].innerText);
            }
            body.push(tData);
        }
    }
    
    doc.autoTable({
        head: [head],
        body: body,
        startY: 20,
        theme: 'striped',
        styles: { fontSize: 9 }
    });
    
    doc.save('metaserve_users.pdf');
}
// Bulk User Actions Logic
const selectAllUsersHeader = document.getElementById('selectAllUsersHeader');
const selectAllUsers = document.getElementById('selectAllUsers');
const userCheckboxes = document.querySelectorAll('.user-checkbox');
const bulkUsersToolbar = document.getElementById('bulkUsersToolbar');
const bulkActionSelect = document.getElementById('bulkActionSelect');
const bulkPasswordContainer = document.getElementById('bulkPasswordContainer');

function updateBulkToolbarVisibility() {
    const anyChecked = Array.from(userCheckboxes).some(cb => cb.checked);
    bulkUsersToolbar.style.display = anyChecked ? 'flex' : 'none';
}

if (selectAllUsersHeader && selectAllUsers) {
    selectAllUsersHeader.addEventListener('change', function() {
        selectAllUsers.checked = this.checked;
        userCheckboxes.forEach(cb => {
            if(cb.closest('tr').style.display !== 'none') {
                cb.checked = this.checked;
            }
        });
        updateBulkToolbarVisibility();
    });

    selectAllUsers.addEventListener('change', function() {
        selectAllUsersHeader.checked = this.checked;
        userCheckboxes.forEach(cb => {
            if(cb.closest('tr').style.display !== 'none') {
                cb.checked = this.checked;
            }
        });
        updateBulkToolbarVisibility();
    });

    userCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkToolbarVisibility);
    });
}

function handleBulkActionSelect(select) {
    if (select.value === 'change_password') {
        bulkPasswordContainer.style.display = 'block';
        bulkPasswordContainer.querySelector('input').setAttribute('required', 'required');
    } else {
        bulkPasswordContainer.style.display = 'none';
        bulkPasswordContainer.querySelector('input').removeAttribute('required');
    }
}

function confirmBulkAction() {
    const action = bulkActionSelect.options[bulkActionSelect.selectedIndex].text;
    if (!bulkActionSelect.value) {
        alert("Please select a bulk action.");
        return false;
    }
    return confirm("Are you sure you want to " + action + " for all selected users?");
}
</script>

<!-- PDF Export Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.1/jspdf.plugin.autotable.min.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
