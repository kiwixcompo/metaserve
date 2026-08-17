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

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>">
            <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Metaserve Logo" onerror="this.src='https://via.placeholder.com/40x40?text=M';">
            <div class="d-flex flex-column ms-2">
                <span class="fs-5 fw-bold" style="line-height: 1.2;">Metaserve Info Tech</span>
                <span style="font-size: 0.75rem; color: #6c757d; font-weight: normal; margin-top: 2px;">The Hackathon Hub</span>
            </div>
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
                    <?php 
                        $dash_url = BASE_URL;
                        switch($_SESSION['role_id'] ?? 0) {
                            case 1: $dash_url .= 'admin/'; break;
                            case 2: $dash_url .= 'accounts/'; break;
                            case 3: $dash_url .= 'coordinator/'; break;
                            case 4: $dash_url .= 'facilitator/'; break;
                            case 5:
                            case 6: $dash_url .= 'student/'; break;
                            case 7: $dash_url .= 'management/'; break;
                        }
                    ?>
                    <li class="nav-item"><a class="nav-link px-3" href="<?= $dash_url ?>"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>profile.php"><i class="fa-solid fa-user-circle me-1"></i> My Profile</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>src/Controllers/AuthController.php?action=logout">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item ms-lg-3"><a class="btn btn-outline-custom btn-sm px-4 py-2 me-2" href="<?= BASE_URL ?>login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-primary-custom btn-sm px-4 py-2" href="<?= BASE_URL ?>register.php">Register Now</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
