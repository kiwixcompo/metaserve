<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Controllers/AuthController.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL);
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();
    $result = $auth->handleLogin($_POST['email'], $_POST['password']);
    
    if ($result['status'] === 'success') {
        header("Location: " . $result['redirect']);
        exit;
    } else {
        $error = $result['message'];
    }
}
require_once __DIR__ . '/includes/header.php';
?>

<section class="py-5 mt-5" style="min-height: 85vh; display: flex; align-items: center; background: linear-gradient(135deg, rgba(30,86,49,0.05) 0%, rgba(118,186,27,0.1) 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="clean-card p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 shadow-sm bg-primary-light" style="width: 70px; height: 70px;">
                            <i class="fa-solid fa-user-lock fs-2 text-primary-custom"></i>
                        </div>
                        <h3 class="text-dark fw-bold">Welcome Back</h3>
                        <p class="text-muted">Sign in to your Metaserve dashboard</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 p-3 mb-4 d-flex align-items-center shadow-sm" style="background: #fee2e2; color: #991b1b; border-radius: 8px;">
                            <i class="fa-solid fa-circle-exclamation fs-4 me-3"></i> 
                            <div><?= htmlspecialchars($error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
                        
                        <!-- Autofill Feature for Testing -->
                        <div class="mb-4 text-center p-3 rounded-3 border" style="background-color: rgba(30, 86, 49, 0.05); border-color: rgba(30, 86, 49, 0.1) !important;">
                            <label class="form-label text-primary-custom small fw-bold text-uppercase mb-2"><i class="fa-solid fa-vial me-2"></i> Quick Test Accounts (Demo)</label>
                            <select class="form-select clean-form-control" onchange="autofillDemo(this.value)">
                                <option value="" class="text-muted">-- Select Role to Autofill --</option>
                                <option value="admin@metaserve.com">Super Admin</option>
                                <option value="accounts@metaserve.com">Head of Accounts</option>
                                <option value="coordinator@metaserve.com">Programme Coordinator</option>
                                <option value="facilitator@metaserve.com">Facilitator</option>
                                <option value="student@metaserve.com">Student (TSU)</option>
                                <option value="external@metaserve.com">External Candidate</option>
                                <option value="management@metaserve.com">University Management</option>
                            </select>
                        </div>
                        <script>
                        function autofillDemo(email) {
                            if (email) {
                                document.querySelector('input[name="email"]').value = email;
                                document.querySelector('input[name="password"]').value = 'Password@123';
                            }
                        }
                        </script>
                        
                        <div class="mb-4">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control clean-form-control border-start-0 ps-0" required placeholder="name@example.com">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-key"></i></span>
                                <input type="password" name="password" class="form-control clean-form-control border-start-0 ps-0" required placeholder="••••••••">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rememberMe">
                                <label class="form-check-label text-muted" for="rememberMe">Remember me</label>
                            </div>
                            <a href="#" class="text-decoration-none small text-primary-custom fw-bold">Forgot Password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100 py-3 mb-4 fw-bold fs-5">Sign In <i class="fa-solid fa-right-to-bracket ms-2"></i></button>
                    </form>
                    
                    <div class="text-center">
                        <span class="text-muted">Don't have an account?</span> 
                        <a href="register.php" class="text-decoration-none fw-bold ms-1 text-primary-custom">Register Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
