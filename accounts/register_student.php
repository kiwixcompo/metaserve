<?php
require_once __DIR__ . '/../config/config.php';

// Only allow Head of Accounts (role_id 2)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->query("SELECT * FROM departments ORDER BY name ASC");
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT * FROM programmes WHERE is_active = 1");
$programmes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();
    $result = $auth->handleRegister($_POST);
    
    if ($result['status'] === 'success') {
        // Mark user as having purchased a physical form (bypasses the 2k admin charge)
        $conn->query("UPDATE users SET form_purchased = 1 WHERE id = " . (int)$result['user_id']);

        // Handle enrollment
        if (isset($_POST['programme_id'])) {
            $prog_id = $_POST['programme_id'];
            $course_id = $_POST['course_id'] ?? null;
            $course_area = isset($_POST['course_area']) ? implode(', ', $_POST['course_area']) : null;
            if(isset($_POST['course_area_other']) && !empty($_POST['course_area_other'])) {
                $course_area .= ($course_area ? ', ' : '') . $_POST['course_area_other'];
            }
            
            $stmt = $conn->prepare("SELECT id FROM enrollments WHERE user_id = ? AND programme_id = ?");
            $stmt->execute([$result['user_id'], $prog_id]);
            
            if ($stmt->rowCount() == 0) {
                $stmt = $conn->prepare("INSERT INTO enrollments (user_id, programme_id, course_id, status) VALUES (?, ?, ?, 'pending')");
                $stmt->execute([$result['user_id'], $prog_id, $course_id]);
            }
        }
        
        header("Location: " . BASE_URL . "accounts/register_student.php?success=registered");
        exit;
    } else {
        $errors = $result['errors'];
    }
}
require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .step-indicator { width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; background: #e5e7eb; color: #6b7280; font-weight: bold; margin-right: 10px; transition: all 0.3s; border: 2px solid transparent; }
    .step-active { background: var(--primary-color); color: white; box-shadow: 0 0 15px rgba(30, 86, 49, 0.3); }
    .form-step { display: none; }
    .form-step.active { display: block; animation: fadeIn 0.4s ease-out forwards; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    .form-control.clean-form-control, .form-select.clean-form-control { border-radius: 8px; border: 1px solid #d1d5db; padding: 10px 15px; }
    .form-control.clean-form-control:focus, .form-select.clean-form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 0.25rem rgba(30, 86, 49, 0.25); }
    .btn-primary-custom { background-color: var(--primary-color); border-color: var(--primary-color); color: white; border-radius: 8px; }
    .btn-primary-custom:hover { background-color: var(--secondary-color); border-color: var(--secondary-color); }
</style>

<section class="py-5 mt-5" style="background: linear-gradient(135deg, rgba(30,86,49,0.03) 0%, rgba(118,186,27,0.05) 100%); min-height: 85vh;">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="clean-card p-4 p-md-5 bg-white shadow-sm" style="border-radius: 15px; border-top: 5px solid var(--primary-color);">
                    <div class="text-center mb-4">
                        <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Logo" style="height: 60px; margin-bottom: 20px;">
                        <h2 class="text-dark fw-bold mb-1">STUDENTS' ICT SKILLS LIBERATION PROGRAMME</h2>
                        <h4 class="text-muted fw-bold">REGISTRATION FORM</h4>
                        <p class="text-muted small mt-2">A collaborative initiative between Metaserve Info Tech Ltd and Taraba State University, Jalingo.</p>
                    </div>

                    <?php if (isset($_GET['success']) && $_GET['success'] === 'registered'): ?>
                        <div class="alert alert-success border-0 p-5 mb-4 shadow-sm text-center" style="background: #e6f4ea; color: #1e5631; border-radius: 12px;">
                            <i class="fa-solid fa-envelope-circle-check" style="font-size: 3rem; margin-bottom: 15px;"></i>
                            <h4 class="fw-bold mb-3">Registration Successful!</h4>
                            <p class="mb-0 fs-5">We've sent a verification link to your email address.</p>
                            <p class="text-muted mt-2">Please check your inbox (and spam folder) and click the link to verify your account and gain access to your dashboard.</p>
                            <a href="<?= BASE_URL ?>login.php" class="btn btn-primary-custom mt-4 px-4 py-2">Go to Login</a>
                        </div>
                    <?php else: ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger border-0 p-4 mb-4 shadow-sm" style="background: #fee2e2; color: #991b1b; border-radius: 12px;">
                            <h6 class="fw-bold mb-2">Registration Failed</h6>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Step Indicators -->
                    <div class="d-flex justify-content-between mb-5 position-relative px-2 px-md-5">
                        <div class="position-absolute w-100" style="height: 2px; background: #e5e7eb; z-index: 0; top: 20px; left: 0;"></div>
                        
                        <div class="text-center position-relative z-1" style="background: #fff; padding: 0 10px; cursor: pointer;" onclick="goToStep(1)">
                            <div class="step-indicator step-active" id="ind-1">1</div>
                            <div class="mt-2 d-none d-sm-block text-dark small fw-bold">Personal</div>
                        </div>
                        <div class="text-center position-relative z-1" style="background: #fff; padding: 0 10px; cursor: pointer;" onclick="goToStep(2)">
                            <div class="step-indicator" id="ind-2">2</div>
                            <div class="mt-2 d-none d-sm-block text-muted small fw-bold">Academic</div>
                        </div>
                        <div class="text-center position-relative z-1" style="background: #fff; padding: 0 10px; cursor: pointer;" onclick="goToStep(3)">
                            <div class="step-indicator" id="ind-3">3</div>
                            <div class="mt-2 d-none d-sm-block text-muted small fw-bold">Programme</div>
                        </div>
                        <div class="text-center position-relative z-1" style="background: #fff; padding: 0 10px; cursor: pointer;" onclick="goToStep(4)">
                            <div class="step-indicator" id="ind-4">4</div>
                            <div class="mt-2 d-none d-sm-block text-muted small fw-bold">Additional</div>
                        </div>
                    </div>

                    <form id="registerForm" method="POST" action="register.php">
                        
                        <div class="text-center mb-4 p-3 rounded" style="background: var(--primary-color); color: white;">
                            <h6 class="mb-2 fw-bold">PLEASE SELECT APPLICANT CATEGORY</h6>
                            <div class="d-flex justify-content-center gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="typeTSU" value="tsu_student" onchange="toggleCategoryFields()">
                                    <label class="form-check-label fw-bold" for="typeTSU">TSU STUDENT (Taraba State University)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="typeExt" value="external" onchange="toggleCategoryFields()">
                                    <label class="form-check-label fw-bold" for="typeExt">EXTERNAL CANDIDATE (Non-TSU Student)</label>
                                </div>
                            </div>
                        </div>

                        <!-- Full form container, hidden until category is selected -->
                        <div id="fullFormContainer" style="display: none;">

                        <!-- STEP 1: Personal Info -->
                        <div class="form-step active" id="step-1">
                            <h5 class="text-white p-2 rounded mb-4 fw-bold" style="background: var(--primary-color);">1. PERSONAL INFORMATION</h5>
                            
                            <div class="row gy-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Full Name (as in your ID) *</label>
                                    <input type="text" name="full_name" class="form-control clean-form-control" required placeholder="John Doe">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Date of Birth *</label>
                                    <input type="date" name="dob" class="form-control clean-form-control" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold d-block">Gender *</label>
                                    <div class="form-check form-check-inline mt-2">
                                        <input class="form-check-input" type="radio" name="gender" id="genderM" value="Male" required>
                                        <label class="form-check-label" for="genderM">Male</label>
                                    </div>
                                    <div class="form-check form-check-inline mt-2">
                                        <input class="form-check-input" type="radio" name="gender" id="genderF" value="Female" required>
                                        <label class="form-check-label" for="genderF">Female</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Nationality *</label>
                                    <select name="nationality" id="nationality" class="form-select clean-form-control" required onchange="toggleNigerianFields()">
                                        <option value="">Select Nationality...</option>
                                        <option value="Nigerian">Nigerian</option>
                                        <option value="Non-Nigerian">Non-Nigerian</option>
                                    </select>
                                </div>

                                <div class="col-md-6 nigerian-only" style="display:none;">
                                    <label class="form-label fw-bold">State of Origin *</label>
                                    <select name="state_of_origin" id="state_of_origin" class="form-select clean-form-control" onchange="populateLGA()">
                                        <option value="">Select State...</option>
                                    </select>
                                </div>
                                <div class="col-md-6 nigerian-only" style="display:none;">
                                    <label class="form-label fw-bold">Local Government Area *</label>
                                    <select name="lga" id="lga" class="form-select clean-form-control">
                                        <option value="">Select LGA...</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phone Number *</label>
                                    <input type="tel" name="phone" class="form-control clean-form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Alternative Phone Number</label>
                                    <input type="tel" name="alt_phone" class="form-control clean-form-control">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Email Address *</label>
                                    <input type="email" name="email" class="form-control clean-form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Password *</label>
                                    <input type="password" name="password" id="password" class="form-control clean-form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Confirm Password *</label>
                                    <input type="password" id="confirm_password" class="form-control clean-form-control" required>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-primary-custom px-5 py-2" onclick="nextStep(2)">Next <i class="fa-solid fa-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- STEP 2: Academic Info -->
                        <div class="form-step" id="step-2">
                            <h5 class="text-white p-2 rounded mb-4 fw-bold" style="background: var(--primary-color);">2. ACADEMIC / BACKGROUND INFORMATION</h5>
                            
                            <!-- TSU Student Fields -->
                            <div id="tsuFields" class="row gy-3">
                                <h6 class="fw-bold text-success mb-2 border-bottom pb-2">For TSU Students:</h6>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Matriculation Number *</label>
                                    <input type="text" name="reg_number" id="reg_number" class="form-control clean-form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Department *</label>
                                    <select name="department_id" id="department_id" class="form-select clean-form-control">
                                        <option value="">Select Department...</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?> (<?= htmlspecialchars($dept['faculty']) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold d-block">Level *</label>
                                    <?php $levels = ['100', '200', '300', '400', '500', 'PG']; ?>
                                    <?php foreach($levels as $lvl): ?>
                                        <div class="form-check form-check-inline mt-2">
                                            <input class="form-check-input tsu-level" type="radio" name="level" id="lvl<?= $lvl ?>" value="<?= $lvl ?>">
                                            <label class="form-check-label" for="lvl<?= $lvl ?>"><?= $lvl ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- External Candidate Fields -->
                            <div id="extFields" class="row gy-3" style="display: none;">
                                <h6 class="fw-bold text-primary mb-2 border-bottom pb-2">For External Candidates:</h6>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Highest Qualification *</label>
                                    <input type="text" name="highest_qualification" id="highest_qualification" class="form-control clean-form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Occupation / Profession *</label>
                                    <input type="text" name="occupation" id="occupation" class="form-control clean-form-control">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary px-5 py-2" onclick="prevStep(1)"><i class="fa-solid fa-arrow-left me-2"></i> Back</button>
                                <button type="button" class="btn btn-primary-custom px-5 py-2" onclick="nextStep(3)">Next <i class="fa-solid fa-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- STEP 3: Programme Selection -->
                        <div class="form-step" id="step-3">
                            <h5 class="text-white p-2 rounded mb-4 fw-bold" style="background: var(--primary-color);">3. PROGRAMME & COURSE SELECTION</h5>
                            
                            <div class="row gy-4">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold mb-3">Choose Programme Type *</label>
                                    
                                    <ul class="nav nav-pills nav-fill gap-2 p-1 bg-light border rounded-pill mb-4" id="programmeTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active rounded-pill fw-bold" id="mandatory-tab" data-bs-toggle="tab" data-bs-target="#mandatory" type="button" role="tab" onclick="setProgramme(1)">Digital Literacy (Mandatory)</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link rounded-pill fw-bold" id="professional-tab" data-bs-toggle="tab" data-bs-target="#professional" type="button" role="tab" onclick="setProgramme(2)">Professional Upskilling</button>
                                        </li>
                                    </ul>
                                    
                                    <input type="hidden" name="programme_id" id="programme_id" value="">
                                    <?php
                                        // Fetch programme IDs dynamically
                                        $prog1_id = 1; $prog2_id = 2;
                                        foreach($programmes as $p) {
                                            if (stripos($p['name'], 'Mandatory') !== false || stripos($p['name'], 'Digital Literacy') !== false) {
                                                $prog1_id = $p['id'];
                                            } else if (stripos($p['name'], 'Professional') !== false) {
                                                $prog2_id = $p['id'];
                                            }
                                        }
                                    ?>
                                    
                                    <div class="tab-content" id="programmeTabsContent">
                                        <div class="tab-pane fade show active" id="mandatory" role="tabpanel">
                                            <div class="alert alert-info border-0 shadow-sm" style="background-color: rgba(13, 110, 253, 0.1); color: #084298;"><i class="fa-solid fa-circle-info me-2"></i> Mandatory courses for TSU students. Cost is calculated at checkout.</div>
                                            <label class="form-label fw-bold">Select Mandatory Course *</label>
                                            <select name="course_id_mandatory" id="course_id_mandatory" class="form-select clean-form-control course-select">
                                                <option value="">Choose a course...</option>
                                                <?php 
                                                    $stmt1 = $conn->prepare("SELECT * FROM courses WHERE programme_id = ? AND is_active = 1 ORDER BY name ASC");
                                                    $stmt1->execute([$prog1_id]);
                                                    while($c = $stmt1->fetch()): 
                                                ?>
                                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="tab-pane fade" id="professional" role="tabpanel">
                                            <div class="alert alert-success border-0 shadow-sm" style="background-color: rgba(25, 135, 84, 0.1); color: #0f5132;"><i class="fa-solid fa-star me-2"></i> Specialized professional courses. Cost is calculated at checkout.</div>
                                            <label class="form-label fw-bold">Select Professional Course *</label>
                                            <select name="course_id_professional" id="course_id_professional" class="form-select clean-form-control course-select">
                                                <option value="">Choose a course...</option>
                                                <?php 
                                                    $stmt2 = $conn->prepare("SELECT * FROM courses WHERE programme_id = ? AND is_active = 1 ORDER BY name ASC");
                                                    $stmt2->execute([$prog2_id]);
                                                    while($c = $stmt2->fetch()): 
                                                ?>
                                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <script>
                                        // Set default to prog1 on load
                                        document.addEventListener("DOMContentLoaded", function() {
                                            document.getElementById('programme_id').value = '<?= $prog1_id ?>';
                                        });
                                        
                                        function setProgramme(type) {
                                            document.getElementById('programme_id').value = (type === 1) ? '<?= $prog1_id ?>' : '<?= $prog2_id ?>';
                                        }
                                    </script>
                                </div>

                                <div class="col-md-6 mt-5">
                                    <label class="form-label fw-bold">Faculty / Field of Interest *</label>
                                    <input type="text" name="faculty_interest" id="faculty_interest" class="form-control clean-form-control" placeholder="e.g. Engineering, Education, etc." required>
                                </div>
                                <div class="col-md-6 mt-5">
                                    <label class="form-label fw-bold d-block">Preferred ICT Skills / Course Area (Select up to 3):</label>
                                    <?php $areas = ['Data Analysis', 'Web Development', 'Cloud Computing', 'Digital Marketing']; ?>
                                    <div class="row">
                                    <?php foreach($areas as $area): ?>
                                        <div class="col-6">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input course-area-cb" type="checkbox" name="course_area[]" value="<?= $area ?>" id="area_<?= str_replace(' ', '', $area) ?>">
                                                <label class="form-check-label" for="area_<?= str_replace(' ', '', $area) ?>"><?= $area ?></label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                        <div class="col-12 mt-2">
                                            <div class="form-check d-flex align-items-center gap-2">
                                                <input class="form-check-input course-area-cb" type="checkbox" id="area_other">
                                                <label class="form-check-label mb-0" for="area_other">Others (Specify):</label>
                                                <input type="text" name="course_area_other" id="course_area_other" class="form-control form-control-sm w-50" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-outline-secondary px-5 py-2" onclick="prevStep(2)"><i class="fa-solid fa-arrow-left me-2"></i> Back</button>
                                <button type="button" class="btn btn-primary-custom px-5 py-2" onclick="nextStep(4)">Next <i class="fa-solid fa-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- STEP 4: Additional Information -->
                        <div class="form-step" id="step-4">
                            <h5 class="text-white p-2 rounded mb-4 fw-bold" style="background: var(--primary-color);">4. ADDITIONAL INFORMATION</h5>
                            
                            <div class="row gy-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold d-block">How did you hear about this programme? *</label>
                                    <?php $hears = ['University Announcement', 'Social Media', 'Metaserve Website', 'Friend / Colleague', 'Other']; ?>
                                    <div class="row">
                                        <?php foreach($hears as $h): ?>
                                        <div class="col-6">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="radio" name="how_did_you_hear" id="hear_<?= str_replace([' ','/'], '', $h) ?>" value="<?= $h ?>" <?= $h=='University Announcement'?'required':'' ?>>
                                                <label class="form-check-label" for="hear_<?= str_replace([' ','/'], '', $h) ?>"><?= $h ?></label>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Why do you want to join this programme? *</label>
                                    <textarea name="why_join" id="why_join" class="form-control clean-form-control" rows="4" required></textarea>
                                </div>
                                
                                <div class="col-md-12 mt-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="declaration" required>
                                        <label class="form-check-label fw-bold" for="declaration">
                                            I hereby declare that the information provided above is true and accurate to the best of my knowledge.
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn btn-outline-secondary px-5 py-2" onclick="prevStep(3)"><i class="fa-solid fa-arrow-left me-2"></i> Back</button>
                                <button type="button" class="btn btn-success px-5 py-2 fw-bold" onclick="submitForm()">Complete Registration <i class="fa-solid fa-check ms-2"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    const statesData = {
        "Abia": ["Aba North", "Aba South", "Arochukwu", "Bende", "Ikwuano", "Isiala-Ngwa North", "Isiala-Ngwa South", "Isuikwato", "Obi Nwa", "Ohafia", "Osisioma", "Ngwa", "Ugwunagbo", "Ukwa East", "Ukwa West", "Umuahia North", "Umuahia South", "Umu-Neochi"],
        "Adamawa": ["Demsa", "Fufore", "Ganaye", "Gireri", "Gombi", "Guyuk", "Hong", "Jada", "Lamurde", "Madagali", "Maiha", "Mayo-Belwa", "Michika", "Mubi North", "Mubi South", "Numan", "Shelleng", "Song", "Toungo", "Yola North", "Yola South"],
        "Anambra": ["Aguata", "Anambra East", "Anambra West", "Anaocha", "Awka North", "Awka South", "Ayamelum", "Dunukofia", "Ekwusigo", "Idemili North", "Idemili south", "Ihiala", "Njikoka", "Nnewi North", "Nnewi South", "Ogbaru", "Onitsha North", "Onitsha South", "Orumba North", "Orumba South", "Oyi"],
        "Akwa Ibom": ["Abak", "Eastern Obolo", "Eket", "Esit Eket", "Essien Udim", "Etim Ekpo", "Etinan", "Ibeno", "Ibesikpo Asutan", "Ibiono Ibom", "Ika", "Ikono", "Ikot Abasi", "Ikot Ekpene", "Ini", "Itu", "Mbo", "Mkpat Enin", "Nsit Atai", "Nsit Ibom", "Nsit Ubium", "Obot Akara", "Okobo", "Onna", "Oron", "Oruk Anam", "Udung Uko", "Ukanafun", "Uruan", "Urue-Offong/Oruko", "Uyo"],
        "Bauchi": ["Alkaleri", "Bauchi", "Bogoro", "Damban", "Darazo", "Dass", "Ganjuwa", "Giade", "Itas/Gadau", "Jama'are", "Katagum", "Kirfi", "Misau", "Ningi", "Shira", "Tafawa-Balewa", "Toro", "Warji", "Zaki"],
        "Bayelsa": ["Brass", "Ekeremor", "Kolokuma/Opokuma", "Nembe", "Ogbia", "Sagbama", "Southern Jaw", "Yenegoa"],
        "Benue": ["Ado", "Agatu", "Apa", "Buruku", "Gboko", "Guma", "Gwer East", "Gwer West", "Katsina-Ala", "Konshisha", "Kwande", "Logo", "Makurdi", "Obi", "Ogbadibo", "Oju", "Okpokwu", "Ohimini", "Oturkpo", "Tarka", "Ukum", "Ushongo", "Vandeikya"],
        "Borno": ["Abadam", "Askira/Uba", "Bama", "Bayo", "Biu", "Chibok", "Damboa", "Dikwa", "Gubio", "Guzamala", "Gwoza", "Hawul", "Jere", "Kaga", "Kala/Balge", "Konduga", "Kukawa", "Kwaya Kusar", "Mafa", "Magumeri", "Maiduguri", "Marte", "Mobbar", "Monguno", "Ngala", "Nganzai", "Shani"],
        "Cross River": ["Akpabuyo", "Odukpani", "Akamkpa", "Biase", "Abi", "Ikom", "Yarkur", "Odubra", "Boki", "Ogoja", "Yala", "Obanliku", "Obudu", "Calabar South", "Etung", "Bekwara", "Bakassi", "Calabar Municipality"],
        "Delta": ["Oshimili", "Aniocha", "Aniocha South", "Ika South", "Ika North-East", "Ndokwa West", "Ndokwa East", "Isoko south", "Isoko North", "Bomadi", "Burutu", "Ughelli South", "Ughelli North", "Ethiope West", "Ethiope East", "Sapele", "Okpe", "Warri North", "Warri South", "Uvwie", "Udu", "Warri Central", "Ukwani", "Oshimili North", "Patani"],
        "Ebonyi": ["Edda", "Afikpo", "Onicha", "Ohaozara", "Abakaliki", "Ishielu", "lkwo", "Ezza", "Ezza South", "Ohaukwu", "Ebonyi", "Ivo"],
        "Enugu": ["Enugu South", "Igbo-Eze South", "Enugu North", "Nkanu", "Udi Agwu", "Oji-River", "Ezeagu", "IgboEze North", "Isi-Uzo", "Nsukka", "Igbo-Ekiti", "Uzo-Uwani", "Enugu Eas", "Aninri", "Nkanu East", "Udenu"],
        "Edo": ["Esan North-East", "Esan Central", "Esan West", "Egor", "Ukpoba", "Central", "Etsako Central", "Igueben", "Oredo", "Ovia SouthWest", "Ovia South-East", "Orhionwon", "Uhunmwonde", "Etsako East", "Esan South-East"],
        "Ekiti": ["Ado", "Ekiti-East", "Ekiti-West", "Emure/Ise/Orun", "Ekiti South-West", "Ikere", "Irepodun", "Ijero", "Ido/Osi", "Oye", "Ikole", "Moba", "Gbonyin", "Efon", "Ise/Orun", "Ilejemeje"],
        "FCT": ["Abaji", "Abuja Municipal", "Bwari", "Gwagwalada", "Kuje", "Kwali"],
        "Gombe": ["Akko", "Balanga", "Billiri", "Dukku", "Kaltungo", "Kwami", "Shomgom", "Funakaye", "Gombe", "Nafada/Bajoga", "Yamaltu/Delta"],
        "Imo": ["Aboh-Mbaise", "Ahiazu-Mbaise", "Ehime-Mbano", "Ezinihitte", "Ideato North", "Ideato South", "Ihitte/Uboma", "Ikeduru", "Isiala Mbano", "Isu", "Mbaitoli", "Ngor-Okpala", "Njaba", "Nwangele", "Nkwerre", "Obowo", "Oguta", "Ohaji/Egbema", "Okigwe", "Orlu", "Orsu", "Oru East", "Oru West", "Owerri-Municipal", "Owerri North", "Owerri West"],
        "Jigawa": ["Auyo", "Babura", "Birni Kudu", "Biriniwa", "Buji", "Dutse", "Gagarawa", "Garki", "Gumel", "Guri", "Gwaram", "Gwiwa", "Hadejia", "Jahun", "Kafin Hausa", "Kaugama Kazaure", "Kiri Kasamma", "Kiyawa", "Maigatari", "Malam Madori", "Miga", "Ringim", "Roni", "Sule-Tankarkar", "Taura", "Yankwashi"],
        "Kaduna": ["Birni-Gwari", "Chikun", "Giwa", "Igabi", "Ikara", "jaba", "Jema'a", "Kachia", "Kaduna North", "Kaduna South", "Kagarko", "Kajuru", "Kaura", "Kauru", "Kubau", "Kudan", "Lere", "Makarfi", "Sabon-Gari", "Sanga", "Soba", "Zango-Kataf", "Zaria"],
        "Kano": ["Ajingi", "Albasu", "Bagwai", "Bebeji", "Bichi", "Bunkure", "Dala", "Dambatta", "Dawakin Kudu", "Dawakin Tofa", "Doguwa", "Fagge", "Gabasawa", "Garko", "Garum", "Mallam", "Gaya", "Gezawa", "Gwale", "Gwarzo", "Kabo", "Kano Municipal", "Karaye", "Kibiya", "Kiru", "kumbotso", "Ghari", "Kura", "Madobi", "Makoda", "Minjibir", "Nasarawa", "Rano", "Rimin Gado", "Rogo", "Shanono", "Sumaila", "Takali", "Tarauni", "Tofa", "Tsanyawa", "Tudun Wada", "Ungogo", "Warawa", "Wudil"],
        "Katsina": ["Bakori", "Batagarawa", "Batsari", "Baure", "Bindawa", "Charanchi", "Dandume", "Danja", "Dan Musa", "Daura", "Dutsi", "Dutsin-Ma", "Faskari", "Funtua", "Ingawa", "Jibia", "Kafur", "Kaita", "Kankara", "Kankia", "Katsina", "Kurfi", "Kusada", "Mai'Adua", "Malumfashi", "Mani", "Mashi", "Matazuu", "Musawa", "Rimi", "Sabuwa", "Safana", "Sandamu", "Zango"],
        "Kebbi": ["Aleiro", "Arewa-Dandi", "Argungu", "Augie", "Bagudo", "Birnin Kebbi", "Bunza", "Dandi", "Fakai", "Gwandu", "Jega", "Kalgo", "Koko/Besse", "Maiyama", "Ngaski", "Sakaba", "Shanga", "Suru", "Wasagu/Danko", "Yauri", "Zuru"],
        "Kogi": ["Adavi", "Ajaokuta", "Ankpa", "Bassa", "Dekina", "Ibaji", "Idah", "Igalamela-Odolu", "Ijumu", "Kabba/Bunu", "Kogi", "Lokoja", "Mopa-Muro", "Ofu", "Ogori/Mangongo", "Okehi", "Okene", "Olamabolo", "Omala", "Yagba East", "Yagba West"],
        "Kwara": ["Asa", "Baruten", "Edu", "Ekiti", "Ifelodun", "Ilorin East", "Ilorin West", "Irepodun", "Isin", "Kaiama", "Moro", "Offa", "Oke-Ero", "Oyun", "Pategi"],
        "Lagos": ["Agege", "Ajeromi-Ifelodun", "Alimosho", "Amuwo-Odofin", "Apapa", "Badagry", "Epe", "Eti-Osa", "Ibeju/Lekki", "Ifako-Ijaye", "Ikeja", "Ikorodu", "Kosofe", "Lagos Island", "Lagos Mainland", "Mushin", "Ojo", "Oshodi-Isolo", "Shomolu", "Surulere"],
        "Nasarawa": ["Akwanga", "Awe", "Doma", "Karu", "Keana", "Keffi", "Kokona", "Lafia", "Nasarawa", "Nasarawa-Eggon", "Obi", "Toto", "Wamba"],
        "Niger": ["Agaie", "Agwara", "Bida", "Borgu", "Bosso", "Chanchaga", "Edati", "Gbako", "Gurara", "Katcha", "Kontagora", "Lapai", "Lavun", "Magama", "Mariga", "Mashegu", "Mokwa", "Muya", "Pailoro", "Rafi", "Rijau", "Shiroro", "Suleja", "Tafa", "Wushishi"],
        "Ogun": ["Abeokuta North", "Abeokuta South", "Ado-Odo/Ota", "Yewa North", "Yewa South", "Ewekoro", "Ifo", "Ijebu East", "Ijebu North", "Ijebu North East", "Ijebu Ode", "Ikenne", "Imeko-Afon", "Ipokia", "Obafemi-Owode", "Ogun Waterside", "Odeda", "Odogbolu", "Remo North", "Shagamu"],
        "Ondo": ["Akoko North East", "Akoko North West", "Akoko South Akure East", "Akoko South West", "Akure North", "Akure South", "Ese-Odo", "Idanre", "Ifedore", "Ilaje", "Ile-Oluji", "Okeigbo", "Irele", "Odigbo", "Okitipupa", "Ondo East", "Ondo West", "Ose", "Owo"],
        "Osun": ["Aiyedade", "Aiyedire", "Atakumosa East", "Atakumosa West", "Boluwaduro", "Boripe", "Ede North", "Ede South", "Egbedore", "Ejigbo", "Ife Central", "Ife East", "Ife North", "Ife South", "Ifedayo", "Ifelodun", "Ila", "Ilesha East", "Ilesha West", "Irepodun", "Irewole", "Isokan", "Iwo", "Obokun", "Odo-Otin", "Ola-Oluwa", "Olorunda", "Oriade", "Orolu", "Osogbo"],
        "Oyo": ["Afijio", "Akinyele", "Atiba", "Atisbo", "Egbeda", "Ibadan Central", "Ibadan North", "Ibadan North West", "Ibadan South East", "Ibadan South West", "Ibarapa Central", "Ibarapa East", "Ibarapa North", "Ido", "Irepo", "Iseyin", "Itesiwaju", "Iwajowa", "Kajola", "Lagelu Ogbomosho North", "Ogbomosho South", "Ogo Oluwa", "Olorunsogo", "Oluyole", "Ona-Ara", "Orelope", "Ori Ire", "Oyo East", "Oyo West", "Saki East", "Saki West", "Surulere"],
        "Plateau": ["Barikin Ladi", "Bassa", "Bokkos", "Jos East", "Jos North", "Jos South", "Kanam", "Kanke", "Langtang North", "Langtang South", "Mangu", "Mikang", "Pankshin", "Qua'an Pan", "Riyom", "Shendam", "Wase"],
        "Rivers": ["Abua/Odual", "Ahoada East", "Ahoada West", "Akuku Toru", "Andoni", "Asari-Toru", "Bonny", "Degema", "Emohua", "Eleme", "Etche", "Gokana", "Ikwerre", "Khana", "Obio/Akpor", "Ogba/Egbema/Ndoni", "Ogu/Bolo", "Okrika", "Omumma", "Opobo/Nkoro", "Oyigbo", "Port-Harcourt", "Tai"],
        "Sokoto": ["Binji", "Bodinga", "Dange-shnsi", "Gada", "Goronyo", "Gudu", "Gawabawa", "Illela", "Isa", "Kware", "kebbe", "Rabah", "Sabon birni", "Shagari", "Silame", "Sokoto North", "Sokoto South", "Tambuwal", "Tqngaza", "Tureta", "Wamako", "Wurno", "Yabo"],
        "Taraba": ["Ardo-kola", "Bali", "Donga", "Gashaka", "Cassol", "Ibi", "Jalingo", "Karin-Lamido", "Kurmi", "Lau", "Sardauna", "Takum", "Ussa", "Wukari", "Yorro", "Zing"],
        "Yobe": ["Bade", "Bursari", "Damaturu", "Fika", "Fune", "Geidam", "Gujba", "Gulani", "Jakusko", "Karasuwa", "Karawa", "Machina", "Nangere", "Nguru Potiskum", "Tarmua", "Yunusari", "Yusufari"],
        "Zamfara": ["Anka", "Bakura", "Birnin Magaji", "Bukkuyum", "Bungudu", "Gummi", "Gusau", "Kaura", "Namoda", "Maradun", "Maru", "Shinkafi", "Talata Mafara", "Tsafe", "Zurmi"]
    };

    function toggleCategoryFields() {
        const typeElem = document.querySelector('input[name="type"]:checked');
        if(!typeElem) return;
        
        document.getElementById('fullFormContainer').style.display = 'block';
        
        const type = typeElem.value;
        const tsuFields = document.getElementById('tsuFields');
        const extFields = document.getElementById('extFields');
        
        const regInput = document.getElementById('reg_number');
        const deptInput = document.getElementById('department_id');
        const tsuLevels = document.querySelectorAll('.tsu-level');
        const hqInput = document.getElementById('highest_qualification');
        const occInput = document.getElementById('occupation');

        const progSelect = document.getElementById('programme_id');

        if (type === 'tsu_student') {
            tsuFields.style.display = 'flex';
            extFields.style.display = 'none';
            regInput.required = true;
            deptInput.required = true;
            tsuLevels[0].required = true;
            hqInput.required = false;
            occInput.required = false;

            // Update prices for TSU (Base Cost)
            Array.from(progSelect.options).forEach(opt => {
                if (opt.value !== "") {
                    const baseCost = parseFloat(opt.getAttribute('data-base'));
                    opt.text = opt.text.split(' - ')[0] + ' - ₦' + baseCost.toLocaleString('en-US', {minimumFractionDigits: 2});
                }
            });
        } else {
            tsuFields.style.display = 'none';
            extFields.style.display = 'flex';
            regInput.required = false;
            deptInput.required = false;
            tsuLevels[0].required = false;
            hqInput.required = true;
            occInput.required = true;

            // Update prices for External (Base Cost * 2.5)
            Array.from(progSelect.options).forEach(opt => {
                if (opt.value !== "") {
                    const baseCost = parseFloat(opt.getAttribute('data-base'));
                    const extCost = baseCost * 2.5;
                    opt.text = opt.text.split(' - ')[0] + ' - ₦' + extCost.toLocaleString('en-US', {minimumFractionDigits: 2});
                }
            });
        }
    }

    function toggleNigerianFields() {
        const nationality = document.getElementById('nationality').value;
        const nigerianFields = document.querySelectorAll('.nigerian-only');
        const stateSelect = document.getElementById('state_of_origin');
        const lgaSelect = document.getElementById('lga');
        
        if (nationality === 'Nigerian') {
            nigerianFields.forEach(el => el.style.display = 'block');
            stateSelect.required = true;
            lgaSelect.required = true;
            
            // Populate states if empty
            if(stateSelect.options.length <= 1) {
                for (const state in statesData) {
                    const option = document.createElement('option');
                    option.value = state;
                    option.text = state;
                    stateSelect.add(option);
                }
            }
        } else {
            nigerianFields.forEach(el => el.style.display = 'none');
            stateSelect.required = false;
            lgaSelect.required = false;
        }
    }

    function populateLGA() {
        const state = document.getElementById('state_of_origin').value;
        const lgaSelect = document.getElementById('lga');
        
        // Clear previous options
        lgaSelect.innerHTML = '<option value="">Select LGA...</option>';
        
        if (state && statesData[state]) {
            statesData[state].forEach(lga => {
                const option = document.createElement('option');
                option.value = lga;
                option.text = lga;
                lgaSelect.add(option);
            });
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Limit course area checkboxes to 3
        const cbs = document.querySelectorAll('.course-area-cb');
        cbs.forEach(cb => {
            cb.addEventListener('change', function() {
                const checked = document.querySelectorAll('.course-area-cb:checked').length;
                if(checked >= 3) {
                    cbs.forEach(c => { if(!c.checked) c.disabled = true; });
                } else {
                    cbs.forEach(c => { if(c.id !== 'area_other' || (c.id === 'area_other' && c.checked)) c.disabled = false; else if(c.id === 'area_other') c.disabled = false; });
                }
            });
        });

        const otherCb = document.getElementById('area_other');
        const otherText = document.getElementById('course_area_other');
        otherCb.addEventListener('change', function() {
            otherText.disabled = !this.checked;
            if(this.checked) otherText.focus();
            else otherText.value = '';
        });
    });

    function nextStep(step) {
        if(step === 2) {
            let pass = document.getElementById('password').value;
            let conf = document.getElementById('confirm_password').value;
            let nationality = document.getElementById('nationality').value;
            let formValid = true;
            
            ['full_name', 'dob', 'nationality', 'phone', 'email'].forEach(f => {
                if(!document.querySelector(`[name="${f}"]`).value) formValid = false;
            });
            
            if (nationality === 'Nigerian') {
                if(!document.getElementById('state_of_origin').value) formValid = false;
                if(!document.getElementById('lga').value) formValid = false;
            }
            
            if(!document.querySelector(`input[name="gender"]:checked`)) formValid = false;

            if(!formValid || !pass) {
                alert('Please fill in all required fields in Step 1.');
                return;
            }
            if (pass !== conf) {
                alert('Passwords do not match!');
                return;
            }
        }
        
        if(step === 3) {
            const type = document.querySelector('input[name="type"]:checked').value;
            if(type === 'tsu_student') {
                if(!document.getElementById('reg_number').value || !document.getElementById('department_id').value || !document.querySelector('input[name="level"]:checked')) {
                    alert('Please complete all TSU Student fields.');
                    return;
                }
            } else {
                if(!document.getElementById('highest_qualification').value || !document.getElementById('occupation').value) {
                    alert('Please complete all External Candidate fields.');
                    return;
                }
            }
        }

        if(step === 4) {
            let progId = document.getElementById('programme_id').value;
            let courseValid = false;
            if (progId == 1 && document.getElementById('course_id_mandatory').value) courseValid = true;
            if (progId == 2 && document.getElementById('course_id_professional').value) courseValid = true;
            
            if(!courseValid || !document.getElementById('faculty_interest').value) {
                alert('Please select a course and fill in your Faculty / Field of Interest.');
                return;
            }
        }

        document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.step-indicator').forEach(el => el.classList.remove('step-active'));
        
        document.getElementById(`step-${step}`).classList.add('active');
        document.getElementById(`ind-${step}`).classList.add('step-active');
        for(let i=1; i<step; i++) {
            document.getElementById(`ind-${i}`).style.background = 'var(--primary-color)';
            document.getElementById(`ind-${i}`).style.color = 'white';
        }
    }

    function goToStep(step) {
        document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.step-indicator').forEach(el => el.classList.remove('step-active'));
        
        document.getElementById(`step-${step}`).classList.add('active');
        document.getElementById(`ind-${step}`).classList.add('step-active');
        
        // Color completed steps indicator
        for(let i=1; i<=4; i++) {
            if(i < step) {
                document.getElementById(`ind-${i}`).style.background = 'var(--primary-color)';
                document.getElementById(`ind-${i}`).style.color = 'white';
            } else if (i > step) {
                document.getElementById(`ind-${i}`).style.background = '#e5e7eb';
                document.getElementById(`ind-${i}`).style.color = '#6b7280';
            }
        }
    }

    function prevStep(step) {
        goToStep(step);
    }

    function submitForm() {
        // Full Validation before submit
        
        // Step 1 Check
        let pass = document.getElementById('password').value;
        let conf = document.getElementById('confirm_password').value;
        let nationality = document.getElementById('nationality').value;
        let step1Valid = true;
        ['full_name', 'dob', 'nationality', 'phone', 'email'].forEach(f => {
            if(!document.querySelector(`[name="${f}"]`).value) step1Valid = false;
        });
        if (nationality === 'Nigerian') {
            if(!document.getElementById('state_of_origin').value) step1Valid = false;
            if(!document.getElementById('lga').value) step1Valid = false;
        }
        if(!document.querySelector(`input[name="gender"]:checked`)) step1Valid = false;
        if(!step1Valid || !pass || pass !== conf) {
            alert('Please fill in all required fields in Step 1 properly (Passwords must match).');
            goToStep(1);
            return;
        }

        // Step 2 Check
        const typeElem = document.querySelector('input[name="type"]:checked');
        if(!typeElem) { alert('Please select Applicant Category.'); return; }
        const type = typeElem.value;
        if(type === 'tsu_student') {
            if(!document.getElementById('reg_number').value || !document.getElementById('department_id').value || !document.querySelector('input[name="level"]:checked')) {
                alert('Please complete all TSU Student fields in Step 2.');
                goToStep(2);
                return;
            }
        } else {
            if(!document.getElementById('highest_qualification').value || !document.getElementById('occupation').value) {
                alert('Please complete all External Candidate fields in Step 2.');
                goToStep(2);
                return;
            }
        }

        // Step 3 Check
        let progId = document.getElementById('programme_id').value;
        let courseValid = false;
        if (progId == 1 && document.getElementById('course_id_mandatory').value) courseValid = true;
        if (progId == 2 && document.getElementById('course_id_professional').value) courseValid = true;
        
        if(!courseValid || !document.getElementById('faculty_interest').value) {
            alert('Please select a course and fill in your Faculty / Field of Interest in Step 3.');
            goToStep(3);
            return;
        }

        // Step 4 Check
        if(!document.querySelector('input[name="how_did_you_hear"]:checked')) {
            alert('Please select how you heard about us in Step 4.');
            goToStep(4);
            return;
        }
        if(!document.getElementById('why_join').value) {
            alert('Please tell us why you want to join in Step 4.');
            goToStep(4);
            return;
        }
        if(!document.getElementById('declaration').checked) {
            alert('You must accept the declaration in Step 4 to proceed.');
            goToStep(4);
            return;
        }

        // Set the actual course_id based on selected programme tab
        let selectedCourse = (progId == 1) ? document.getElementById('course_id_mandatory').value : document.getElementById('course_id_professional').value;
        
        let courseInput = document.getElementById('final_course_id');
        if(!courseInput) {
            courseInput = document.createElement('input');
            courseInput.type = 'hidden';
            courseInput.name = 'course_id';
            courseInput.id = 'final_course_id';
            document.getElementById('registerForm').appendChild(courseInput);
        }
        courseInput.value = selectedCourse;

        // All good, submit!
        document.getElementById('registerForm').submit();
    }
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
