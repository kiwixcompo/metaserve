<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Controllers/AuthController.php';
require_once __DIR__ . '/src/Models/CourseMapping.php';

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL);
    exit();
}

$courseModel = new CourseMapping();
$departments = $courseModel->getDepartments();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();
    $result = $auth->handleRegister($_POST);
    
    if ($result['status'] === 'success') {
        // Handle enrollment
        if (isset($_POST['selected_item'])) {
            require_once __DIR__ . '/config/database.php';
            $db = new Database();
            $conn = $db->getConnection();
            $item = $_POST['selected_item'];
            $prog_id = null;
            $course_id = null;
            
            if (strpos($item, 'course_') === 0) {
                $course_id = str_replace('course_', '', $item);
                $stmt = $conn->prepare("SELECT programme_id FROM courses WHERE id = ?");
                $stmt->execute([$course_id]);
                $prog_id = $stmt->fetchColumn();
            } else if (strpos($item, 'prog_') === 0) {
                $prog_id = str_replace('prog_', '', $item);
            }

            if ($prog_id) {
                // Ensure no duplicate enrollment
                if ($course_id) {
                    $stmt = $conn->prepare("SELECT id FROM enrollments WHERE user_id = ? AND programme_id = ? AND course_id = ?");
                    $stmt->execute([$_SESSION['user_id'], $prog_id, $course_id]);
                } else {
                    $stmt = $conn->prepare("SELECT id FROM enrollments WHERE user_id = ? AND programme_id = ? AND course_id IS NULL");
                    $stmt->execute([$_SESSION['user_id'], $prog_id]);
                }
                
                if ($stmt->rowCount() == 0) {
                    if ($course_id) {
                        $stmt = $conn->prepare("INSERT INTO enrollments (user_id, programme_id, course_id, status) VALUES (?, ?, ?, 'pending')");
                        $stmt->execute([$_SESSION['user_id'], $prog_id, $course_id]);
                    } else {
                        $stmt = $conn->prepare("INSERT INTO enrollments (user_id, programme_id, status) VALUES (?, ?, 'pending')");
                        $stmt->execute([$_SESSION['user_id'], $prog_id]);
                    }
                }
            }
        }
        
        header("Location: " . $result['redirect']);
        exit;
    } else {
        $errors = $result['errors'];
    }
}
require_once __DIR__ . '/includes/header.php';
?>

<style>
    .step-indicator { width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; background: #e5e7eb; color: #6b7280; font-weight: bold; margin-right: 10px; transition: all 0.3s; border: 2px solid transparent; cursor: pointer; }
    .step-active { background: var(--primary-color); color: white; box-shadow: 0 0 15px rgba(30, 86, 49, 0.3); }
    .form-step { display: none; }
    .form-step.active { display: block; animation: fadeIn 0.4s ease-out forwards; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    .course-card { background: #fff; border: 2px solid var(--border-color); border-radius: 12px; padding: 20px; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .course-card:hover { border-color: rgba(30, 86, 49, 0.3); background: rgba(30, 86, 49, 0.02); transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .course-card.selected { border-color: var(--primary-color); background: rgba(30, 86, 49, 0.05); box-shadow: 0 8px 20px rgba(30, 86, 49, 0.15); }
    
    /* Hide radio button visually but keep it accessible */
    .course-card input[type="radio"] { opacity: 0; position: absolute; }
    
    /* Custom Scrollbar for Course Container */
    .custom-scrollbar::-webkit-scrollbar { width: 8px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<section class="py-5 mt-5" style="background: linear-gradient(135deg, rgba(30,86,49,0.03) 0%, rgba(118,186,27,0.05) 100%); min-height: 85vh;">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="clean-card p-4 p-md-5 bg-white">
                    <div class="text-center mb-5">
                        <h2 class="text-dark fw-bold display-6">Create Your Account</h2>
                        <p class="text-muted" style="font-size: 1.1rem;">Join the Metaserve Digital Skills Portal today</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger border-0 p-4 mb-4 d-flex align-items-start shadow-sm" style="background: #fee2e2; color: #991b1b; border-radius: 12px;">
                            <i class="fa-solid fa-triangle-exclamation fs-3 me-3 mt-1"></i>
                            <div>
                                <h6 class="fw-bold mb-2">Registration Failed</h6>
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Step Indicators -->
                    <div class="d-flex justify-content-between mb-5 position-relative px-2 px-md-5">
                        <div class="position-absolute w-100" style="height: 2px; background: #e5e7eb; z-index: 0; top: 20px; left: 0;"></div>
                        
                        <div class="text-center position-relative z-1" style="background: #fff; padding: 0 10px; border-radius: 20px; cursor: pointer;" onclick="previewStep(1)">
                            <div class="step-indicator step-active" id="ind-1"><i class="fa-solid fa-user"></i></div>
                            <div class="mt-2 d-none d-sm-block text-dark small fw-bold">Personal</div>
                        </div>
                        <div class="text-center position-relative z-1" style="background: #fff; padding: 0 10px; border-radius: 20px; cursor: pointer;" onclick="previewStep(2)">
                            <div class="step-indicator" id="ind-2"><i class="fa-solid fa-graduation-cap"></i></div>
                            <div class="mt-2 d-none d-sm-block text-muted small fw-bold">Academic</div>
                        </div>
                        <div class="text-center position-relative z-1" style="background: #fff; padding: 0 10px; border-radius: 20px; cursor: pointer;" onclick="previewStep(3)">
                            <div class="step-indicator" id="ind-3"><i class="fa-solid fa-laptop-code"></i></div>
                            <div class="mt-2 d-none d-sm-block text-muted small fw-bold">Programme</div>
                        </div>
                    </div>

                    <form id="registerForm" method="POST" action="register.php">
                        <!-- STEP 1: Personal Info -->
                        <div class="form-step active" id="step-1">
                            <h4 class="text-dark mb-4 fw-bold"><i class="fa-regular fa-id-card me-2 text-primary-custom"></i> Personal Information</h4>
                            <hr style="border-color: var(--border-color); margin-bottom: 30px;">
                            
                            <div class="row gy-4">
                                <div class="col-md-6">
                                    <label class="form-label">First Name *</label>
                                    <input type="text" name="first_name" class="form-control clean-form-control" required placeholder="John">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name *</label>
                                    <input type="text" name="last_name" class="form-control clean-form-control" required placeholder="Doe">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" name="email" class="form-control clean-form-control" required placeholder="john@example.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number *</label>
                                    <input type="tel" name="phone" class="form-control clean-form-control" required placeholder="08012345678">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Password *</label>
                                    <input type="password" name="password" id="password" class="form-control clean-form-control" required placeholder="••••••••">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm Password *</label>
                                    <input type="password" id="confirm_password" class="form-control clean-form-control" required placeholder="••••••••">
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-5">
                                <button type="button" class="btn btn-primary-custom px-5 py-2" onclick="nextStep(2)">Next Step <i class="fa-solid fa-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- STEP 2: Academic Info -->
                        <div class="form-step" id="step-2">
                            <h4 class="text-dark mb-4 fw-bold"><i class="fa-solid fa-graduation-cap me-2 text-secondary-custom"></i> Academic Status</h4>
                            <hr style="border-color: var(--border-color); margin-bottom: 30px;">
                            
                            <div class="mb-4">
                                <label class="form-label">Are you a TSU Student or External Candidate? *</label>
                                <select name="type" id="userType" class="form-select clean-form-control py-3" required onchange="toggleStudentFields()">
                                    <option value="" class="text-muted">Select your status...</option>
                                    <option value="tsu_student" class="text-dark">Internal (Taraba State University Student)</option>
                                    <option value="external" class="text-dark">External Candidate</option>
                                </select>
                            </div>

                            <div id="studentFields" style="display: none;" class="mt-4 p-4 bg-light rounded border border-light">
                                <div class="row gy-4">
                                    <!-- Reg number auto-assigned -->
                                    <div class="col-md-12">
                                        <label class="form-label">Academic Department *</label>
                                        <select name="department_id" id="departmentSelect" class="form-select clean-form-control">
                                            <option value="" class="text-muted">Select your department...</option>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?= $dept['id'] ?>" class="text-dark"><?= htmlspecialchars($dept['name']) ?> (<?= htmlspecialchars($dept['faculty']) ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="mt-2 text-muted small"><i class="fa-solid fa-lightbulb text-secondary-custom me-1"></i> Selecting this will help our AI map the most relevant ICT courses for your discipline.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn btn-outline-custom" onclick="prevStep(1)"><i class="fa-solid fa-arrow-left me-2"></i> Back</button>
                                <button type="button" class="btn btn-primary-custom px-5" onclick="goToStep3()">Next Step <i class="fa-solid fa-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- STEP 3: Programme Selection -->
                        <div class="form-step" id="step-3">
                            <h4 class="text-dark mb-4 fw-bold"><i class="fa-solid fa-laptop-code me-2 text-primary-custom"></i> Intelligent Programme & Course Selection</h4>
                            <hr style="border-color: var(--border-color); margin-bottom: 30px;">
                            
                            <!-- Suggestions Container -->
                            <div id="courseLoader" class="text-center py-5" style="display: none;">
                                <div class="spinner-grow text-primary-custom" role="status" style="width: 3rem; height: 3rem;"></div>
                                <h5 class="text-dark mt-4 fw-bold">Analyzing your profile...</h5>
                                <p class="text-muted mb-0">Mapping the best courses for your success.</p>
                            </div>

                            <div id="coursesContainer" class="mb-4">
                                <!-- Populated via AJAX -->
                            </div>

                            <div class="d-flex justify-content-between mt-5">
                                <button type="button" class="btn btn-outline-custom" onclick="prevStep(2)"><i class="fa-solid fa-arrow-left me-2"></i> Back</button>
                                <button type="button" class="btn btn-primary-custom px-5 fw-bold bg-primary-custom" onclick="validateAndSubmit()">Complete Registration <i class="fa-solid fa-check ms-2"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Multi-step logic
    function nextStep(step) {
        if(step === 2) {
            let pass = document.getElementById('password').value;
            let conf = document.getElementById('confirm_password').value;
            let email = document.querySelector('input[name="email"]').value;
            let fname = document.querySelector('input[name="first_name"]').value;
            
            if(!fname || !email || !pass) {
                alert('Please fill in all required fields.');
                return;
            }
            if (pass !== conf) {
                alert('Passwords do not match!');
                return;
            }
        }

        document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
        document.getElementById('step-' + step).classList.add('active');
        
        document.querySelectorAll('.step-indicator').forEach(el => {
            el.classList.remove('step-active');
            el.nextElementSibling.classList.remove('text-dark');
            el.nextElementSibling.classList.add('text-muted');
        });
        
        for(let i=1; i<=step; i++) {
            let ind = document.getElementById('ind-' + i);
            ind.classList.add('step-active');
            ind.nextElementSibling.classList.remove('text-muted');
            ind.nextElementSibling.classList.add('text-dark');
        }
    }

    function previewStep(step) {
        // Just switch the view without validation so users can preview
        document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
        document.getElementById('step-' + step).classList.add('active');
        
        document.querySelectorAll('.step-indicator').forEach(el => {
            el.classList.remove('step-active');
            el.nextElementSibling.classList.remove('text-dark');
            el.nextElementSibling.classList.add('text-muted');
        });
        
        for(let i=1; i<=step; i++) {
            let ind = document.getElementById('ind-' + i);
            ind.classList.add('step-active');
            ind.nextElementSibling.classList.remove('text-muted');
            ind.nextElementSibling.classList.add('text-dark');
        }
        
        if (step === 3) {
            loadCourses();
        }
    }

    function validateAndSubmit() {
        // Validate Step 1
        let fname = document.querySelector('input[name="first_name"]').value;
        let email = document.querySelector('input[name="email"]').value;
        let pass = document.getElementById('password').value;
        let conf = document.getElementById('confirm_password').value;
        
        if(!fname || !email || !pass) {
            alert('Please fill in all required personal information fields.');
            previewStep(1);
            return;
        }
        if (pass !== conf) {
            alert('Passwords do not match!');
            previewStep(1);
            return;
        }

        // Validate Step 2
        const type = document.getElementById('userType').value;
        if (!type) {
            alert('Please select your academic status.');
            previewStep(2);
            return;
        }
        if (type === 'tsu_student') {
            const deptId = document.getElementById('departmentSelect').value;
            if (!deptId) {
                alert('Please fill in your Department.');
                previewStep(2);
                return;
            }
        }

        // Validate Step 3
        const selectedItem = document.querySelector('input[name="selected_item"]:checked');
        if (!selectedItem) {
            alert('Please select a programme or course to complete registration.');
            previewStep(3);
            return;
        }

        // All good, submit the form
        document.getElementById('registerForm').submit();
    }

    function prevStep(step) {
        document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
        document.getElementById('step-' + step).classList.add('active');
        
        document.getElementById('ind-' + (step + 1)).classList.remove('step-active');
        document.getElementById('ind-' + (step + 1)).nextElementSibling.classList.add('text-muted');
        document.getElementById('ind-' + (step + 1)).nextElementSibling.classList.remove('text-dark');
    }

    // Toggle fields based on student type
    function toggleStudentFields() {
        const type = document.getElementById('userType').value;
        const studentFields = document.getElementById('studentFields');
        
        if (type === 'tsu_student') {
            studentFields.style.display = 'block';
        } else {
            studentFields.style.display = 'none';
        }
    }

    // Transition to Step 3 and fetch courses
    function goToStep3() {
        const type = document.getElementById('userType').value;
        if (!type) {
            alert('Please select your academic status first.');
            return;
        }

        const deptId = document.getElementById('departmentSelect').value;
        if (type === 'tsu_student' && !deptId) {
            alert('Please select your academic department.');
            return;
        }
        
        nextStep(3);
        loadCourses();
    }
    
    function loadCourses() {
        const type = document.getElementById('userType').value;
        const deptId = document.getElementById('departmentSelect').value;
        
        document.getElementById('coursesContainer').innerHTML = '';
        document.getElementById('courseLoader').style.display = 'block';

        $.ajax({
            url: 'src/Controllers/CourseController.php?action=get_suggestions',
            type: 'POST',
            data: { department_id: type === 'tsu_student' ? deptId : 0 },
            success: function(response) {
                document.getElementById('courseLoader').style.display = 'none';
                let html = '';
                
                if (response.status === 'success' || response.status === 'info') {
                    if (response.data) {
                        html += `
                        <div class="mb-4 position-relative">
                            <i class="fa-solid fa-search position-absolute text-muted" style="left: 15px; top: 18px;"></i>
                            <input type="text" id="courseSearch" class="form-control clean-form-control py-3 ps-5" placeholder="Search for programmes or specific skills..." onkeyup="filterCourses()">
                        </div>
                        <div style="max-height: 450px; overflow-y: auto; overflow-x: hidden; padding-right: 5px;" class="custom-scrollbar">
                        `;
                        
                        // 1. Render Programmes
                        if (response.data.programmes && response.data.programmes.length > 0) {
                            html += '<h5 class="text-dark fw-bold mb-3 mt-2 filter-header"><i class="fa-solid fa-graduation-cap text-secondary-custom me-2"></i> Full Academic Programmes</h5>';
                            html += '<div class="row gy-3 mb-4 filter-group">';
                            response.data.programmes.forEach((prog, index) => {
                                html += `
                                <div class="col-md-6 filter-item">
                                    <label class="course-card d-block w-100 h-100 position-relative">
                                        <div class="position-absolute top-0 end-0 p-3">
                                            <div class="rounded-circle border border-2 border-secondary d-flex align-items-center justify-content-center" style="width:24px; height:24px; border-color: #d1d5db !important;">
                                                <i class="fa-solid fa-check text-white d-none check-icon"></i>
                                            </div>
                                        </div>
                                        <input type="radio" name="selected_item" value="prog_${prog.programme_id}" required>
                                        
                                        <div class="d-flex flex-column h-100">
                                            <div class="mb-3">
                                                <span class="badge bg-secondary text-white border mb-2 px-2 py-1">Programme</span>
                                                <h5 class="text-dark fw-bold mb-1 lh-base item-title">${prog.programme_name}</h5>
                                                <p class="text-muted small mb-0 item-desc">${prog.duration_weeks} Weeks Duration</p>
                                            </div>
                                            <div class="mt-auto pt-3 border-top border-light">
                                                <span class="fs-5 fw-bold text-primary-custom">₦${parseFloat(prog.cost).toLocaleString()}</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>`;
                            });
                            html += '</div>';
                        }
                        
                        // 2. Render Courses
                        if (response.data.courses && response.data.courses.length > 0) {
                            if (response.status === 'success') {
                                html += '<h5 class="text-dark mb-3 d-flex align-items-center fw-bold mt-4 filter-header"><i class="fa-solid fa-wand-magic-sparkles text-primary-custom me-2"></i> AI Recommended Courses For You</h5>';
                            } else {
                                html += '<h5 class="text-dark fw-bold mb-3 mt-4 filter-header"><i class="fa-solid fa-book-open text-primary-custom me-2"></i> Individual Courses / Skills</h5>';
                            }
                            
                            html += '<div class="row gy-3 filter-group">';
                            response.data.courses.forEach((course, index) => {
                                let isTop = course.priority_level && course.priority_level > 1 ? '<span class="badge bg-primary-custom ms-2" style="font-size: 0.7rem;">Top Match</span>' : '';
                                html += `
                                <div class="col-md-6 filter-item">
                                    <label class="course-card d-block w-100 h-100 position-relative">
                                        <div class="position-absolute top-0 end-0 p-3">
                                            <div class="rounded-circle border border-2 border-secondary d-flex align-items-center justify-content-center" style="width:24px; height:24px; border-color: #d1d5db !important;">
                                                <i class="fa-solid fa-check text-white d-none check-icon"></i>
                                            </div>
                                        </div>
                                        <input type="radio" name="selected_item" value="course_${course.course_id}" required>
                                        
                                        <div class="d-flex flex-column h-100">
                                            <div class="mb-3">
                                                <span class="badge bg-light text-secondary-custom border mb-2 px-2 py-1">${course.course_code}</span>
                                                <h5 class="text-dark fw-bold mb-1 lh-base item-title">${course.course_name} ${isTop}</h5>
                                                <p class="text-muted small mb-0 item-desc">Part of: ${course.programme_name}</p>
                                            </div>
                                            <div class="mt-auto pt-3 border-top border-light">
                                                <span class="fs-5 fw-bold text-primary-custom">₦${parseFloat(course.cost).toLocaleString()}</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>`;
                            });
                            html += '</div>';
                        }
                        html += '</div>'; // close scroll container
                    } else {
                        html = '<div class="alert alert-info bg-light border-info text-info p-4"><i class="fa-solid fa-circle-info fs-4 d-block mb-2"></i>' + response.message + '</div>';
                    }
                } else {
                    html = '<div class="alert alert-danger p-4">Error loading courses. Please try again.</div>';
                }
                
                document.getElementById('coursesContainer').innerHTML = html;
                
                // Add click event for highlighting and showing checkmark
                document.querySelectorAll('.course-card').forEach(card => {
                    card.addEventListener('click', function() {
                        document.querySelectorAll('.course-card').forEach(c => {
                            c.classList.remove('selected');
                            c.querySelector('.check-icon').classList.add('d-none');
                            c.querySelector('.rounded-circle').style.backgroundColor = 'transparent';
                            c.querySelector('.rounded-circle').style.borderColor = '#d1d5db';
                        });
                        this.classList.add('selected');
                        this.querySelector('input[type="radio"]').checked = true;
                        this.querySelector('.check-icon').classList.remove('d-none');
                        this.querySelector('.rounded-circle').style.backgroundColor = 'var(--primary-color)';
                        this.querySelector('.rounded-circle').style.borderColor = 'var(--primary-color)';
                    });
                });
            }
        });
    }

    function filterCourses() {
        const input = document.getElementById('courseSearch').value.toLowerCase();
        const items = document.querySelectorAll('.filter-item');
        const groups = document.querySelectorAll('.filter-group');
        const headers = document.querySelectorAll('.filter-header');
        
        items.forEach(item => {
            const title = item.querySelector('.item-title').innerText.toLowerCase();
            const desc = item.querySelector('.item-desc') ? item.querySelector('.item-desc').innerText.toLowerCase() : '';
            if (title.includes(input) || desc.includes(input)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
        
        // Hide headers/groups if they are empty
        groups.forEach((group, index) => {
            const visibleItems = Array.from(group.querySelectorAll('.filter-item')).filter(i => i.style.display !== 'none');
            if (visibleItems.length === 0) {
                group.style.display = 'none';
                if(headers[index]) headers[index].style.display = 'none';
            } else {
                group.style.display = 'flex';
                if(headers[index]) headers[index].style.display = 'block';
            }
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
