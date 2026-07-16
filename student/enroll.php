<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [5, 6])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Fetch available programmes
$stmt = $conn->query("SELECT * FROM programmes WHERE is_active = 1");
$programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all active courses
$stmt = $conn->query("SELECT c.*, p.name as prog_name FROM courses c JOIN programmes p ON c.programme_id = p.id WHERE p.is_active = 1");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_item'])) {
    $item = $_POST['selected_item'];
    $prog_id = null;
    
    if (strpos($item, 'course_') === 0) {
        $course_id = str_replace('course_', '', $item);
        $stmt = $conn->prepare("SELECT programme_id FROM courses WHERE id = ?");
        $stmt->execute([$course_id]);
        $prog_id = $stmt->fetchColumn();
    } else if (strpos($item, 'prog_') === 0) {
        $prog_id = str_replace('prog_', '', $item);
    }
    
    if ($prog_id) {
        // Check if already enrolled in this exact option
        if (isset($course_id)) {
            $stmt = $conn->prepare("SELECT id FROM enrollments WHERE user_id = :uid AND programme_id = :pid AND course_id = :cid");
            $stmt->execute(['uid' => $_SESSION['user_id'], 'pid' => $prog_id, 'cid' => $course_id]);
        } else {
            $stmt = $conn->prepare("SELECT id FROM enrollments WHERE user_id = :uid AND programme_id = :pid AND course_id IS NULL");
            $stmt->execute(['uid' => $_SESSION['user_id'], 'pid' => $prog_id]);
        }
        
        if ($stmt->rowCount() > 0) {
            $error = "You are already enrolled or have a pending enrollment in this programme/course.";
        } else {
            // Enroll
            if (isset($course_id)) {
                $stmt = $conn->prepare("INSERT INTO enrollments (user_id, programme_id, course_id, status) VALUES (:uid, :pid, :cid, 'pending')");
                $stmt->execute(['uid' => $_SESSION['user_id'], 'pid' => $prog_id, 'cid' => $course_id]);
            } else {
                $stmt = $conn->prepare("INSERT INTO enrollments (user_id, programme_id, status) VALUES (:uid, :pid, 'pending')");
                $stmt->execute(['uid' => $_SESSION['user_id'], 'pid' => $prog_id]);
            }
            
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

<section class="py-5 mt-5 bg-light min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="clean-card p-5 mt-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary-light text-primary-custom rounded-circle mb-4" style="width: 80px; height: 80px;">
                        <i class="fa-solid fa-plus fs-1"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-3">Enroll in a New Programme</h3>
                    <p class="text-muted mb-4">Select a programme or individual course from the list below to add it to your learning path.</p>
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-4 text-start">
                            <label class="form-label text-muted small fw-bold">Select Course or Programme</label>
                            <select name="selected_item" class="form-select clean-form-control py-3" required>
                                <option value="">-- Select an Option --</option>
                                <optgroup label="Full Academic Programmes">
                                    <?php foreach($programmes as $prog): ?>
                                        <option value="prog_<?= $prog['id'] ?>"><?= htmlspecialchars($prog['name']) ?> (&#8358;<?= number_format($prog['cost'], 2) ?>)</option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="Individual Courses & Skills">
                                    <?php foreach($courses as $course): ?>
                                        <option value="course_<?= $course['id'] ?>"><?= htmlspecialchars($course['name']) ?> - <?= htmlspecialchars($course['course_code']) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary-custom btn-lg w-100 py-3 shadow-sm">Confirm Enrollment</button>
                    </form>
                    
                    <div class="mt-4">
                        <a href="index.php" class="text-muted text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
