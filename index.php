<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="bingbot" content="noarchive">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="application-title" content="EnrollSys">
    <meta name="color-scheme" content="#101126">
    <meta name="theme-color" content="#101126">
    <title>EnrollSys - Student Enrollment System</title>
    <link rel="website icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
     <link href="style/bootstrap.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <!-- <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"> -->
     <link href="style/google-fonts.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">

</head>
<body class="light-theme">
    <!-- Theme Toggle -->
    <div class="theme-toggle-container">
        <button id="themeToggle" class="theme-toggle-btn">
            <i class="fas fa-moon"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap floating"></i>
                <span class="logo-text">Enroll</span><span class="logo-highlight">Sys</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span>☰</span>
                <!-- <span class="navbar-toggler-icon"></span> -->
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <button class="btn btn-primary btn-login" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <button class="btn btn-outline-primary btn-register" data-bs-toggle="modal" data-bs-target="#registerModal">Register</button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Parallax -->
    <section class="hero-section parallax">
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1 class="hero-title">Welcome to <span>EnrollSys</span></h1>
                    <p class="hero-subtitle">Your seamless gateway to academic enrollment and management</p>
                    <div class="hero-buttons">
                        <button class="btn btn-primary btn-lg me-3" data-bs-toggle="modal" data-bs-target="#registerModal">Get Started</button>
                        <button class="btn btn-outline-light btn-lg">Learn More</button>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block">
                    <div class="hero-illustration">
                        <div class="floating-element"></div>
                        <div class="floating-element"></div>
                        <div class="floating-element"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="container">
            <h2 class="section-title text-center" id="keyF1">Key Features</h2>
            <p class="section-subtitle text-center" id="keyF2">Discover what makes EnrollSys the perfect choice for your academic journey</p>
            
            <div class="row g-4">
                <!-- Editable Content Box 1 -->
                <div class="col-md-4 editable-box" data-box-id="feature1">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3>24/7 Access</h3>
                        <p>Access the enrollment system anytime, anywhere with our cloud-based platform.</p>
                        <div class="box-actions">
                            <button class="btn-copy" onclick="copyBox('feature1')"><i class="fas fa-copy"></i></button>
                            <button class="btn-delete" onclick="deleteBox('feature1')"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
                
                <!-- Editable Content Box 2 -->
                <div class="col-md-4 editable-box" data-box-id="feature2">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3>Mobile Friendly</h3>
                        <p>Fully responsive design that works perfectly on all devices from desktop to mobile.</p>
                        <div class="box-actions">
                            <button class="btn-copy" onclick="copyBox('feature2')"><i class="fas fa-copy"></i></button>
                            <button class="btn-delete" onclick="deleteBox('feature2')"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
                
                <!-- Editable Content Box 3 -->
                <div class="col-md-4 editable-box" data-box-id="feature3">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Secure Platform</h3>
                        <p>Enterprise-grade security to protect your personal and academic information.</p>
                        <div class="box-actions">
                            <button class="btn-copy" onclick="copyBox('feature3')"><i class="fas fa-copy"></i></button>
                            <button class="btn-delete" onclick="deleteBox('feature3')"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about-image">
                        <img src="img/bg1.jpeg" alt="About EnrollSys" class="img-fluid rounded">
                        <div class="image-overlay"></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <h2 class="section-title">About EnrollSys</h2>
                    <p>EnrollSys is a state-of-the-art student enrollment system designed to streamline the academic registration process for Eastern Visayas State University.</p>
                    <p>Our platform offers a seamless, intuitive experience for students to manage their academic journey from enrollment to graduation.</p>
                    <ul class="about-features">
                        <li><i class="fas fa-check-circle"></i> Easy course registration</li>
                        <li><i class="fas fa-check-circle"></i> Real-time schedule management</li>
                        <li><i class="fas fa-check-circle"></i> Academic progress tracking</li>
                        <li><i class="fas fa-check-circle"></i> Secure document submission</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section with Parallax -->
    <section class="stats-section parallax2">
        <div class="stats-overlay"></div>
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="stat-item">
                        <h3 class="counter" data-target="12500">0</h3>
                        <p>Students Enrolled</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <h3 class="counter" data-target="350">0</h3>
                        <p>Courses Offered</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <h3 class="counter" data-target="98">0</h3>
                        <p>Success Rate</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <h3 class="counter" data-target="24">0</h3>
                        <p>Support Hours</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section">
        <div class="container">
            <h2 class="section-title text-center">Contact Us</h2>
            <p class="section-subtitle text-center">Have questions? Get in touch with our support team</p>
            
            <div class="row">
                <div class="col-lg-6">
                    <form class="contact-form">
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Your Name">
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Your Email">
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" placeholder="Subject">
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" rows="5" placeholder="Your Message"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
                <div class="col-lg-6">
                    <div class="contact-info">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="info-content">
                                <h4>Location</h4>
                                <p>Eastern Visayas State University, Ormoc City, Leyte</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div class="info-content">
                                <h4>Phone</h4>
                                <p>+63 946 493 0641</p>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="info-content">
                                <h4>Email</h4>
                                <p>enrollsys.evsu.edu.ph</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="footer-about">
                        <h4>EnrollSys</h4>
                        <p>The premier student enrollment system for Eastern Visayas State University, designed to make academic management simple and efficient.</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="footer-links">
                        <h4>Quick Links</h4>
                        <ul>
                            <li><a href="#">Home</a></li>
                            <li><a href="#features">Features</a></li>
                            <li><a href="#about">About</a></li>
                            <li><a href="#contact">Contact</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="footer-links">
                        <h4>Resources</h4>
                        <ul>
                            <li><a href="#">Help Center</a></li>
                            <li><a href="#">FAQs</a></li>
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Terms of Service</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="footer-newsletter">
                        <h4>Newsletter</h4>
                        <p>Subscribe to our newsletter for the latest updates and announcements.</p>
                        <form class="newsletter-form">
                            <input type="email" placeholder="Your Email">
                            <button type="submit"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 EnrollSys. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Student Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="loginForm">
                        <div class="mb-3">
                            <label for="loginEmail" class="form-label">EVSUmail</label>
                            <input type="email" class="form-control" id="email" placeholder="username@evsu.edu.ph" required>
                            <div class="invalid-feedback"></div>
                            <!-- Add error message container -->
                            <div id="loginEmailError" class="text-danger mt-1 small" style="display: none;"></div>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" required>
                            <div class="invalid-feedback"></div>
                            <!-- Add error message container -->
                            <div id="loginPasswordError" class="text-danger mt-1 small" style="display: none;"></div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="show_login_password">
                            <label class="form-check-label" for="show_login_password">Show Password</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="loginBtn">Login
                        <span class="spinner-border spinner-border-sm" style="display: none;" id="loginSpinner"></span>
                        </button>
                        <div class="mb-3success-message" id="successMessage"></div>
                    </form>
                    <div class="text-center mt-3">
                        <a href="#" class="text-muted">Forgot password?</a>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <p>Don't have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">Register</a></p>
                </div>
                <div class="alert-container" id="alertContainer"></div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Student Registration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="registerForm">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="givenName" class="form-label">Given Name</label>
                                <input type="text" class="form-control" id="givenName" required>
                                <div class="invalid-feedback">Please enter your given name</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lastName" required>
                                <div class="invalid-feedback">Please enter your last name</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="middleName" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middleName">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="registerEmail" class="form-label">EVSUmail</label>
                            <input type="email" class="form-control" id="registerEmail" placeholder="username@evsu.edu.ph" required>
                            <div class="invalid-feedback">Please enter a valid EVSUmail address (@evsu.edu.ph)</div>
                        </div>
                        <div class="mb-3">
                            <label for="registerPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="registerPassword" required>
                            <div class="password-requirements">
                                <p>(Optional)Password must contain:</p>
                                <ul>
                                    <li id="req-length"><i class="fas fa-circle"></i> At least 8 characters</li>
                                    <li id="req-uppercase"><i class="fas fa-circle"></i> At least one uppercase letter</li>
                                    <li id="req-lowercase"><i class="fas fa-circle"></i> At least one lowercase letter</li>
                                    <li id="req-number"><i class="fas fa-circle"></i> At least one number</li>
                                    <li id="req-special"><i class="fas fa-circle"></i> At least one special character</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="repeatPassword" class="form-label">Repeat Password</label>
                            <input type="password" class="form-control" id="repeatPassword" required>
                            <div class="invalid-feedback">Passwords do not match</div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="register_show_password">
                            <label class="form-check-label" for="register_show_password">Show Password</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>
                </div>
                <div class="modal-footer justify-content-center">
                    <p>Already have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top"><i class="fas fa-arrow-up"></i></a>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
     <script src="javascript/jquery.js"></script>
    <!-- <script src="script.js"></script> -->
    <script src="javaScript/function/index_student.js"></script>
</body>
</html>