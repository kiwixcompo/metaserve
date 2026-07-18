<?php require_once __DIR__ . '/includes/header.php'; ?>

<!-- Page Header -->
<section class="py-5 bg-light mt-5">
    <div class="container py-4 text-center">
        <h1 class="display-5 fw-bold text-dark mb-3">Contact Us</h1>
        <p class="lead text-muted mb-0">We are here to help you kickstart your tech journey</p>
    </div>
</section>

<!-- Contact Content Section -->
<section class="py-5 bg-white">
    <div class="container py-5">
        <div class="row align-items-center gy-5 justify-content-center">
            
            <div class="col-lg-5 pe-lg-5">
                <h6 class="text-primary-custom text-uppercase fw-bold mb-2">Get In Touch</h6>
                <h2 class="text-dark fw-bold mb-4">We'd love to hear from you</h2>
                <p class="text-muted mb-5" style="font-size: 1.1rem; line-height: 1.8;">
                    Have questions about enrollment, course mappings, or our facilities? Reach out to our support team today and we will get back to you as soon as possible.
                </p>
                
                <ul class="list-unstyled mb-0 d-flex flex-column gap-4">
                    <li class="d-flex align-items-start">
                        <div class="bg-light p-3 rounded-circle shadow-sm me-3 text-primary-custom d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                            <i class="fa-solid fa-location-dot fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Our Location</h5>
                            <p class="text-muted mb-0">Metaserve Info Tech Ltd<br>Taraba State, Nigeria</p>
                        </div>
                    </li>
                    <li class="d-flex align-items-start">
                        <div class="bg-light p-3 rounded-circle shadow-sm me-3 text-primary-custom d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                            <i class="fa-solid fa-envelope fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Email Address</h5>
                            <p class="text-muted mb-0">info@metaserve.com.ng<br>support@metaserve.com.ng</p>
                        </div>
                    </li>
                    <li class="d-flex align-items-start">
                        <div class="bg-light p-3 rounded-circle shadow-sm me-3 text-primary-custom d-flex align-items-center justify-content-center" style="width: 55px; height: 55px;">
                            <i class="fa-solid fa-phone fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Phone Numbers</h5>
                            <p class="text-muted mb-0">
                                <strong>Admin/help desk:</strong> 09055875069, 0806 486 6016<br>
                                <strong>Technical Support:</strong> 08082768855<br>
                                <span class="text-muted small">(Mon - Fri, 8AM - 5PM)</span>
                            </p>
                        </div>
                    </li>
                </ul>
            </div>
            
            <div class="col-lg-6">
                <div class="clean-card p-4 p-md-5 bg-white border shadow-sm rounded-4">
                    <h4 class="fw-bold text-dark mb-4">Send us a message</h4>
                    <form action="#" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" class="form-control clean-form-control" placeholder="John Doe" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" class="form-control clean-form-control" placeholder="john@example.com" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Subject</label>
                                <input type="text" class="form-control clean-form-control" placeholder="How can we help you?" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Message</label>
                                <textarea class="form-control clean-form-control" rows="5" placeholder="Write your message here..." required></textarea>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="button" class="btn btn-primary-custom w-100 py-3 fw-bold fs-6">Send Message <i class="fa-solid fa-paper-plane ms-2"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
