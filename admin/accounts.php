<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Only allow Role 2 (Head of Accounts) or Role 1 (Super Admin)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Handle form submission for adding a physical candidate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_physical') {
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $type = $_POST['type'] ?? 'tsu_student';
    $formNumber = $_POST['form_number'] ?? '';
    
    // Auto-generate password and reg ID
    $password = "Physical123!"; // Default password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $regId = "PHY-" . strtoupper(substr(uniqid(), -6));
    
    $role_id = ($type === 'tsu_student') ? 5 : 6;
    
    try {
        $stmt = $conn->prepare("INSERT INTO users (role_id, first_name, last_name, email, password_hash, type, registration_id, registration_method, form_purchased, form_number) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, 'PHYSICAL', 1, ?)");
        $stmt->execute([$role_id, $firstName, $lastName, $email, $passwordHash, $type, $regId, $formNumber]);
        
        $_SESSION['success_msg'] = "Candidate added successfully! Default Password: Physical123!";
    } catch(PDOException $e) {
        $_SESSION['error_msg'] = "Error adding candidate. Email or Form Number might be duplicate.";
    }
    
    header("Location: accounts.php");
    exit();
}

// Fetch all physical registrations
$stmt = $conn->query("SELECT id, first_name, last_name, email, type, form_number, created_at FROM users WHERE registration_method = 'PHYSICAL' ORDER BY created_at DESC");
$physicalCandidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4 mt-5">
    <div class="row">
        <div class="col-md-2">
            <!-- Sidebar -->
            <div class="list-group list-group-flush shadow-sm rounded-3">
                <?php if($_SESSION['role_id'] == 1): ?>
                    <a href="index.php" class="list-group-item list-group-item-action text-muted"><i class="fa-solid fa-arrow-left me-2"></i> Back to Main Admin</a>
                <?php endif; ?>
                <a href="#" class="list-group-item list-group-item-action active fw-bold"><i class="fa-solid fa-file-invoice-dollar me-2"></i> Physical Forms</a>
            </div>
        </div>
        
        <div class="col-md-10">
            <h3 class="fw-bold text-dark mb-4">Accounts Department</h3>
            
            <?php if(isset($_SESSION['success_msg'])): ?>
                <div class="alert alert-success shadow-sm"><i class="fa-solid fa-check-circle me-2"></i> <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['error_msg'])): ?>
                <div class="alert alert-danger shadow-sm"><i class="fa-solid fa-triangle-exclamation me-2"></i> <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?></div>
            <?php endif; ?>

            <div class="clean-card p-4 shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Upload Physical Candidate</h5>
                </div>
                <form method="POST" action="accounts.php" class="row g-3">
                    <input type="hidden" name="action" value="add_physical">
                    
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-bold">First Name</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-bold">Last Name</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-bold">Candidate Type</label>
                        <select name="type" class="form-select" required>
                            <option value="tsu_student">TSU Student</option>
                            <option value="external">External Candidate</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-bold">Physical Form Number</label>
                        <input type="text" name="form_number" class="form-control" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-custom w-100"><i class="fa-solid fa-upload me-2"></i> Upload Record</button>
                    </div>
                </form>
            </div>

            <div class="clean-card p-4 shadow-sm">
                <h5 class="fw-bold mb-3">Recently Uploaded Candidates</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Form Number</th>
                                <th>Candidate Name</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Date Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($physicalCandidates as $c): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($c['form_number']) ?></span></td>
                                <td class="fw-bold"><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></td>
                                <td><?= htmlspecialchars($c['email']) ?></td>
                                <td><?= ($c['type'] === 'tsu_student') ? 'TSU Student' : 'External' ?></td>
                                <td class="small text-muted"><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
