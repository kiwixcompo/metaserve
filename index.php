<?php 
require_once __DIR__ . '/includes/header.php';

$enrollLink = BASE_URL . "register.php";
if (isset($_SESSION['user_id'])) {
    if (in_array($_SESSION['role_id'], [5, 6])) {
        $enrollLink = BASE_URL . "student/enroll.php";
    } else {
        $enrollLink = BASE_URL . "login.php"; // Redirects to correct dashboard via AuthController logic or they can just use the navbar
    }
}
?>
<!-- Comprehensive Hero Section with Generated Image -->
<section class="hero-section position-relative overflow-hidden bg-light" style="padding: 100px 0;">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6 text-center text-lg-start z-1">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="badge bg-primary-light text-primary-custom px-3 py-2 rounded-pill mb-4 shadow-sm" style="font-size: 0.9rem; border: 1px solid rgba(30, 86, 49, 0.2);">
                        <i class="fa-solid fa-user-check me-2"></i> Logged In
                    </span>
                    <h1 class="hero-title display-4 fw-bold text-dark mb-3">Welcome back to your <span class="text-primary-custom">Learning Journey</span>.</h1>
                    <p class="lead text-muted mb-5 pe-lg-5" style="font-size: 1.2rem; line-height: 1.8;">
                        Continue exploring your aligned ICT skills or dive right back into your enrolled courses to master the digital future.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                        <?php 
                            $dashLink = BASE_URL . 'student/index.php';
                            if ($_SESSION['role_id'] == 1) $dashLink = BASE_URL . 'admin/index.php';
                        ?>
                        <a href="<?= $dashLink ?>" class="btn btn-primary-custom btn-lg px-5 py-3 shadow-sm d-flex align-items-center justify-content-center">Go to Dashboard <i class="fa-solid fa-arrow-right ms-2"></i></a>
                        <a href="<?= BASE_URL ?>courses.php" class="btn btn-outline-secondary btn-lg px-5 py-3">Explore More Courses</a>
                    </div>
                <?php else: ?>
                    <span class="badge bg-primary-light text-primary-custom px-3 py-2 rounded-pill mb-4 shadow-sm" style="font-size: 0.9rem; border: 1px solid rgba(30, 86, 49, 0.2);">
                        <i class="fa-solid fa-rocket me-2"></i> Launch Your Career in Tech
                    </span>
                    <h1 class="hero-title display-4 fw-bold text-dark mb-3">Master the <span class="text-primary-custom">Digital Future</span> with Metaserve.</h1>
                    <p class="lead text-muted mb-5 pe-lg-5" style="font-size: 1.2rem; line-height: 1.8;">
                        Join our comprehensive training programs. We intelligently map industry-standard ICT skills to your academic discipline, giving you the ultimate competitive edge in today's fast-paced digital economy.
                    </p>
                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start">
                        <a href="<?= $enrollLink ?>" class="btn btn-primary-custom btn-lg px-5 py-3 shadow-sm d-flex align-items-center justify-content-center">Start Learning <i class="fa-solid fa-arrow-right ms-2"></i></a>
                        <a href="<?= BASE_URL ?>courses.php" class="btn btn-outline-secondary btn-lg px-5 py-3">Explore Courses</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-6 position-relative z-1 text-center">
                <div class="position-relative d-inline-block rounded-4 overflow-hidden shadow-lg border border-4 border-white" style="transform: perspective(1000px) rotateY(-5deg); transition: transform 0.5s;">
                    <img src="<?= BASE_URL ?>assets/images/banner.jpg" alt="Metaserve Banner" class="img-fluid" style="border-radius: 15px;">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Us Section -->
<section class="py-5 bg-white" id="about">
    <div class="container py-5">
        <div class="row align-items-center gy-5 justify-content-center">
            <div class="col-lg-6 pe-lg-5">
                <h6 class="text-primary-custom text-uppercase fw-bold mb-2">About Metaserve</h6>
                <h2 class="text-dark fw-bold mb-4 display-6">Empowering the Next Generation of Digital Leaders</h2>
                <p class="text-muted mb-4" style="font-size: 1.1rem; line-height: 1.8;">
                    <strong>Metaserve Info Tech Ltd</strong> is a premier technology and digital skills training institute. We specialize in bridging the critical gap between academic theory and practical industry requirements. By intelligently mapping industry-standard ICT skills to specific academic disciplines, we ensure our students—whether from the university or the general public—are fully equipped for the modern digital economy.
                </p>
                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="d-flex align-items-start">
                        <i class="fa-solid fa-graduation-cap text-primary-custom fs-4 me-3 mt-1"></i>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Tailored Curriculum</h5>
                            <p class="text-muted small mb-0">Our courses are dynamically aligned with your specific field of study to maximize career impact.</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start">
                        <i class="fa-solid fa-certificate text-primary-custom fs-4 me-3 mt-1"></i>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Verified Certifications</h5>
                            <p class="text-muted small mb-0">Earn globally recognized, tamper-proof certificates upon successful completion of your training.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="clean-card p-5 bg-light border-0 shadow-sm rounded-4">
                    <h4 class="fw-bold text-dark mb-4 border-bottom pb-3"><i class="fa-solid fa-headset text-primary-custom me-2"></i> Contact Us</h4>
                    <p class="text-muted mb-4">Have questions about enrollment, course mappings, or our facilities? Reach out to our support team today.</p>
                    
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-4">
                        <li class="d-flex align-items-start">
                            <div class="bg-white p-3 rounded-circle shadow-sm me-3 text-primary-custom d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-location-dot fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Our Location</h6>
                                <p class="text-muted small mb-0">Metaserve Info Tech Ltd<br>Taraba State, Nigeria</p>
                            </div>
                        </li>
                        <li class="d-flex align-items-start">
                            <div class="bg-white p-3 rounded-circle shadow-sm me-3 text-primary-custom d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-envelope fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Email Address</h6>
                                <p class="text-muted small mb-0">info@metaserve.com.ng<br>support@metaserve.com.ng</p>
                            </div>
                        </li>
                        <li class="d-flex align-items-start">
                            <div class="bg-white p-3 rounded-circle shadow-sm me-3 text-primary-custom d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-phone fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Phone Numbers</h6>
                                <p class="text-muted small mb-0">
                                    <strong>Admin/help desk:</strong> 09055875069, 0806 486 6016<br>
                                    <strong>Technical Support:</strong> 08082768855<br>
                                    <span class="text-muted" style="font-size: 0.8rem;">(Mon - Fri, 8AM - 5PM)</span>
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Intelligent Mapping Features Section -->
<section class="py-5" id="courses" style="background-color: var(--bg-light);">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h6 class="text-primary-custom text-uppercase fw-bold mb-2">Our Methodology</h6>
            <h2 class="text-dark fw-bold display-6 mb-3">Intelligent Skill Mapping</h2>
            <p class="text-muted mx-auto" style="font-size: 1.1rem; max-width: 600px;">We don't just teach technology; we map the perfect tech skills to your specific university department to maximize your career trajectory.</p>
        </div>
        
        <div class="row gy-4 mt-2">
            <div class="col-md-4">
                <div class="clean-card h-100 text-center p-5 bg-white border-0 shadow-sm rounded-4">
                    <div class="fs-1 text-primary-custom mb-4 d-inline-block p-4 rounded-circle shadow-sm" style="background: rgba(30, 86, 49, 0.05);"><i class="fa-solid fa-calculator"></i></div>
                    <h5 class="text-dark fw-bold mb-3">Accounting & Finance</h5>
                    <p class="text-muted text-sm mb-4">We map you to Advanced Excel, Data Analytics, and QuickBooks.</p>
                    <a href="<?= $enrollLink ?>" class="text-primary-custom fw-bold text-decoration-none">Enroll Now <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="clean-card h-100 text-center p-5 bg-white border-0 shadow-sm rounded-4 position-relative" style="transform: translateY(-20px); border-bottom: 4px solid var(--primary-color) !important;">
                    <div class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-primary-custom px-3 py-2">Most Popular</div>
                    <div class="fs-1 text-secondary-custom mb-4 d-inline-block p-4 rounded-circle shadow-sm mt-3" style="background: rgba(118, 186, 27, 0.05);"><i class="fa-solid fa-laptop-code"></i></div>
                    <h5 class="text-dark fw-bold mb-3">Computer Science</h5>
                    <p class="text-muted text-sm mb-4">Master Full-Stack Web Development, Cyber Security, and AI integration.</p>
                    <a href="<?= $enrollLink ?>" class="text-secondary-custom fw-bold text-decoration-none">Enroll Now <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="clean-card h-100 text-center p-5 bg-white border-0 shadow-sm rounded-4">
                    <div class="fs-1 text-warning mb-4 d-inline-block p-4 rounded-circle shadow-sm" style="background: rgba(255, 193, 7, 0.05);"><i class="fa-solid fa-leaf"></i></div>
                    <h5 class="text-dark fw-bold mb-3">Agriculture & Science</h5>
                    <p class="text-muted text-sm mb-4">Learn GIS Mapping, Data Analysis, and Modern Agri-Tech software.</p>
                    <a href="<?= $enrollLink ?>" class="text-warning fw-bold text-decoration-none">Enroll Now <i class="fa-solid fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Certification Section -->
<section class="py-5 bg-dark text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #111827 0%, #1E5631 100%);">
    <div class="container py-5">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6">
                <h2 class="fw-bold mb-4 display-5 text-white">Earn Verified Digital Certificates</h2>
                <p class="lead mb-4 text-light opacity-75" style="line-height: 1.8;">
                    Upon successful completion of your assessments, you will instantly receive a globally recognized, digitally verifiable certificate to attach to your CV and LinkedIn profile.
                </p>
                <div class="d-flex align-items-center mb-4">
                    <i class="fa-solid fa-shield-halved fs-2 text-warning me-3"></i>
                    <p class="mb-0 fw-bold">100% Verifiable & Tamper-Proof</p>
                </div>
                <a href="<?= $enrollLink ?>" class="btn btn-warning text-dark btn-lg px-5 py-3 fw-bold shadow">Get Certified</a>
            </div>
            <div class="col-lg-6 text-center">
                <img src="<?= BASE_URL ?>assets/images/cert.png" alt="Digital Certificate" class="img-fluid rounded-4 shadow-lg border border-2 border-secondary" style="transform: rotate(2deg); max-height: 400px;">
            </div>
        </div>
    </div>
</section>

<!-- Our ICT Facilities Scrolling Carousel -->
<section class="py-5 bg-white">
    <div class="container-fluid px-0">
        <div class="text-center mb-4">
            <h6 class="text-primary-custom text-uppercase fw-bold mb-2">Our Facilities</h6>
            <h2 class="text-dark fw-bold display-6">State-of-the-Art Learning Environment</h2>
        </div>
        <style>
            .bottom-scrolling-wrapper { overflow: hidden; white-space: nowrap; position: relative; width: 100%; padding: 20px 0; }
            .bottom-scrolling-track { display: inline-block; animation: scrollLeftBottom 30s linear infinite; }
            .bottom-scrolling-track img { height: 400px; width: auto; display: inline-block; margin-right: 20px; border-radius: 15px; object-fit: cover; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
            @keyframes scrollLeftBottom { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
        </style>
        <div class="bottom-scrolling-wrapper">
            <div class="bottom-scrolling-track">
                <img src="<?= BASE_URL ?>assets/images/slide1.jpg" alt="Facility 1">
                <img src="<?= BASE_URL ?>assets/images/slide2.jpg" alt="Facility 2">
                <img src="<?= BASE_URL ?>assets/images/slide3.jpg" alt="Facility 3">
                <img src="<?= BASE_URL ?>assets/images/slide4.jpg" alt="Facility 4">
                <!-- Duplicate for infinite scroll -->
                <img src="<?= BASE_URL ?>assets/images/slide1.jpg" alt="Facility 1">
                <img src="<?= BASE_URL ?>assets/images/slide2.jpg" alt="Facility 2">
                <img src="<?= BASE_URL ?>assets/images/slide3.jpg" alt="Facility 3">
                <img src="<?= BASE_URL ?>assets/images/slide4.jpg" alt="Facility 4">
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
