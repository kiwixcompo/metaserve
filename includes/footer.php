<?php
if (!isset($settingsData) || !is_array($settingsData)) {
    if (file_exists(__DIR__ . '/../src/Models/Settings.php')) {
        require_once __DIR__ . '/../src/Models/Settings.php';
        $settingsModel = new Settings();
        $settingsData = $settingsModel->getAllSettings();
    } else {
        $settingsData = [];
    }
}
?>
<footer class="py-5 mt-5" style="background: #ffffff; border-top: 1px solid var(--border-color); position: relative; z-index: 1;">
    <div class="container">
        <div class="row gy-4">
            <div class="col-md-4">
                <div class="d-flex align-items-center mb-3">
                    <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Metaserve Logo" style="height: 40px; margin-right: 10px;" onerror="this.src='https://via.placeholder.com/40x40?text=M';">
                    <div>
                        <h5 class="mb-0 fw-bold text-primary-custom" style="line-height: 1.2;">Metaserve Info Tech</h5>
                        <small class="text-muted d-block" style="font-size: 0.85rem; margin-top: 2px;">The Hackathon Hub</small>
                    </div>
                </div>
                <p class="text-muted" style="font-size: 0.95rem; line-height: 1.7;">Empowering the next generation with world-class digital skills and technological education.</p>
            </div>
            <div class="col-md-2 offset-md-2">
                <h6 class="text-dark mb-4 fw-bold">Quick Links</h6>
                <ul class="list-unstyled">
                    <li><a href="<?= BASE_URL ?>index.php" class="text-muted text-decoration-none d-block mb-2 hover-text-primary">Home</a></li>
                    <li><a href="<?= BASE_URL ?>courses.php" class="text-muted text-decoration-none d-block mb-2 hover-text-primary">Courses</a></li>
                    <li><a href="<?= BASE_URL ?>login.php" class="text-muted text-decoration-none d-block mb-2 hover-text-primary">Login</a></li>
                    <li><a href="<?= BASE_URL ?>register.php" class="text-muted text-decoration-none d-block mb-2 hover-text-primary">Register</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="text-dark mb-4 fw-bold">Connect With Us</h6>
                <div class="d-flex gap-3">
                    <?php if(!empty($settingsData['social_facebook'])): ?>
                    <a href="<?= htmlspecialchars($settingsData['social_facebook']) ?>" target="_blank" class="btn btn-outline-custom d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; padding: 0;"><i class="fa-brands fa-facebook-f"></i></a>
                    <?php endif; ?>
                    <?php if(!empty($settingsData['social_twitter'])): ?>
                    <a href="<?= htmlspecialchars($settingsData['social_twitter']) ?>" target="_blank" class="btn btn-outline-custom d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; padding: 0;"><i class="fa-brands fa-twitter"></i></a>
                    <?php endif; ?>
                    <?php if(!empty($settingsData['social_instagram'])): ?>
                    <a href="<?= htmlspecialchars($settingsData['social_instagram']) ?>" target="_blank" class="btn btn-outline-custom d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; padding: 0;"><i class="fa-brands fa-instagram"></i></a>
                    <?php endif; ?>
                    <?php if(!empty($settingsData['social_linkedin'])): ?>
                    <a href="<?= htmlspecialchars($settingsData['social_linkedin']) ?>" target="_blank" class="btn btn-outline-custom d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; padding: 0;"><i class="fa-brands fa-linkedin-in"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="text-center mt-5 pt-4" style="border-top: 1px solid var(--border-color);">
            <p class="text-muted mb-0" style="font-size: 0.85rem;">
                &copy; <?= date('Y') ?> Metaserve Info Tech Ltd. All rights reserved.
            </p>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="<?= BASE_URL ?>assets/js/bootstrap.bundle.min.js"></script>
<!-- jQuery (For AJAX calls in registration) -->
<script src="<?= BASE_URL ?>assets/js/jquery-3.7.1.min.js"></script>
</body>
</html>
