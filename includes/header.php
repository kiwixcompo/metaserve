<?php require_once __DIR__ . '/../config/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Digital Skills Portal for Metaserve Info Tech Ltd - Bridging the gap in digital literacy.">
    <title><?= SITE_NAME ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>assets/images/logo.png" type="image/png">
    
    <!-- Bootstrap 5 CSS -->
    <link href="<?= BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<!-- Client Preview / Development Banner -->
<div style="position: fixed; bottom: 30px; left: 30px; background: linear-gradient(135deg, #1e5631, #4c9a2a); color: white; padding: 12px 24px; border-radius: 50px; font-weight: bold; font-size: 14px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); z-index: 9999; display: flex; align-items: center; gap: 12px; border: 2px solid rgba(255,255,255,0.2); animation: pulse-dev 2s infinite;">
    <i class="fa-solid fa-person-digging fs-4"></i>
    <div>
        <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.85; margin-bottom: 2px;">Client Preview Mode</div>
        <div style="margin: 0; line-height: 1;">Development in Progress</div>
    </div>
</div>
<style>
@keyframes pulse-dev {
    0% { box-shadow: 0 0 0 0 rgba(76, 154, 42, 0.6); }
    70% { box-shadow: 0 0 0 12px rgba(76, 154, 42, 0); }
    100% { box-shadow: 0 0 0 0 rgba(76, 154, 42, 0); }
}
</style><!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>">
            <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Metaserve Logo" onerror="this.src='https://via.placeholder.com/40x40?text=M';">
            <span>Metaserve Portal</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>about.php">About</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>courses.php">Courses</a></li>
                <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>contact.php">Contact</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>src/Controllers/AuthController.php?action=logout">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item ms-lg-3"><a class="btn btn-outline-custom btn-sm px-4 py-2 me-2" href="<?= BASE_URL ?>login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-primary-custom btn-sm px-4 py-2" href="<?= BASE_URL ?>register.php">Register Now</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
