<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="bingbot" content="noarchive">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5"> -->
    <meta name="application-title" content="EnrollSys">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#101126">
    <meta name="msapplication-navbutton-color" content="#101126">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>enrollsys evsu</title>
    <link rel="website icon" href="{{ asset('img/evsu-logo.png') }}">
    <!-- Bootstrap CSS -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
     <link href="{{ asset('style/bootstrap.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <!-- <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"> -->
     <link href="{{ asset('style/google-fonts.css') }}" rel="stylesheet">
    <!-- Custom CSS -->
    <!-- <link rel="stylesheet" href="../css/style.css"> -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

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
                <!-- <i class="fas fa-graduation-cap floating"></i> -->
                 <i class="logo">
                    <img src="{{ asset('img/evsu-logo.png') }}" alt="">
                 </i>
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
                    <!-- <li class="nav-item ms-lg-3">
                        <button style="background-color: rgb(138, 30, 30); border-color: maroon" class="btn btn-primary btn-login" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <button style="border-color: maroon;" class="btn btn-outline-primary btn-register" data-bs-toggle="modal" data-bs-target="#registerModal">Register</button>
                    </li> -->
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
                        <button id="_register" class="btn btn-primary btn-lg me-3" data-bs-toggle="modal" data-bs-target="#registerModal" style="background-color: maroon; border-color: maroon">Register</button>
                        <button id="_login" data-bs-toggle="modal" data-bs-target="#loginModal" class="btn btn-outline-light btn-lg">Login</button>
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
                        <li><i class="fas fa-check-circle"></i> Real-time status tracking</li>
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
                        @csrf
                        <div class="mb-3">
                            <input id="q_name" type="text" class="form-control" placeholder="Your Name" required>
                        </div>
                        <div class="mb-3">
                            <input id="q_email" type="email" class="form-control" placeholder="Your Email" required>
                        </div>
                        <div style="display: none;" class="mb-3">
                            <input id="q_subject" type="text" class="form-control" placeholder="Subject">
                        </div>
                        <div class="mb-3">
                            <textarea id="q_ms" class="form-control" rows="5" placeholder="Your Message"></textarea>
                        </div>
                        <button id="sendBtn" type="button" onclick="send_question()" style="background-color: maroon; border-color: rgb(146, 54, 54)" class="btn btn-primary">
                            <span id="btnText">Send Message</span>
                            <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        </button>
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
                                <p>carljames.duallo.evsu.edu.ph</p>
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
                            <a href="https://www.facebook.com/JPCSEVSUOCC"><i class="fab fa-facebook-f"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 EnrollSys. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Enhanced Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
        <div id="des_md" class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-sign-in-alt"></i>
                        Student Login <span class="text-muted" style="font-size: 0.8rem; "></span>
                    </h5>
                    <h5 class="modal-title">
                        <i id="_fa-times-circle" class='fas fa-times-circle' data-bs-dismiss="modal"></i>
                    </h5>
                    <!-- <button type="button" class="btn-close btn-close-enhanced" data-bs-dismiss="modal" aria-label="Close"></button> -->
                </div>
                <div class="modal-body">
                    <form id="loginForm">
                        <div class="form-group-enhanced">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i>
                                Email
                            </label>
                            <div class="input-group-enhanced">
                                <input style="width: 100%;" type="email" class="form-control-enhanced" id="email" placeholder="your email address..." required>
                                <div id="loginEmailError" class="text-danger mt-1 small"></div>
                            </div>
                        </div>
                        
                        <div class="form-group-enhanced">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i>
                                Password
                            </label>
                            <div class="input-group-enhanced">
                                <input style="width: 100%;" type="password" class="form-control-enhanced" id="password" placeholder="Enter your password" required>
                                <div id="loginPasswordError" class="text-danger mt-1 small" style="display: none;"></div>
                            </div>
                        </div>
                        
                        <div class="form-check-enhanced">
                            <input type="checkbox" class="form-check-input-enhanced" id="show_login_password">
                            <label class="form-check-label-enhanced" for="show_login_password">
                                Show Password
                            </label>
                        </div>
                        
                        <!-- <button type="submit" class="btn btn-enhanced btn-enhanced-primary w-100" id="loginBtn">
                            <span id="loginBtnText">Login to Account</span>
                            <span class="spinner-border spinner-border-sm" style="display: none;" id="loginSpinner"></span>
                        </button> -->

                        <button type="submit" class="btn btn-primary-enhanced w-100" id="loginBtn">
                            <span class="btn-text">Login Account</span>
                            <i class="fas fa-arrow-right btn-icon"></i>
                        </button>

                        
                        <div class="mb-3 success-message" id="successMessage"></div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="/forgot_acc_student" class="text-muted" style="font-size: 0.9rem;">
                            <i class="fas fa-question-circle"></i>
                            Forgot your password?
                        </a>
                    </div>
                </div>
                <div class="modal-footer-enhanced">
                    <p>Don't have an account? 
                        <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" data-bs-dismiss="modal">
                            Create Account
                        </a>
                    </p>
                </div>
                <div class="alert-container" id="alertContainer"></div>
            </div>
        </div>
    </div>

    <!-- Enhanced Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true" style="touch-action: pan-y;">
        <div id="_unscroll" class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header-enhanced">
                    <div class="header-content">
                        <div class="icon-container">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="header-text">
                            <h5 class="modal-title">Create Student Account</h5>
                            <p class="modal-subtitle">Fill in your details to get started</p>
                        </div>
                        <h5 class="modal-title">
                            <i id="_fa-times-circle" class='fas fa-times-circle' data-bs-dismiss="modal"></i>
                        </h5>
                    </div>
                    <!-- <button style="float: right; top: 5px; background-color: white; color: red; font-size: 24px" type="button" class="btn-close btn-close-enhanced" data-bs-dismiss="modal" aria-label="Close">×</button> -->
                </div>
                
                <!-- Modal Body -->
                <div class="modal-body">
                    <form id="registerForm" class="enhanced-form">
                        <!-- Personal Information Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <h6><i class="fas fa-user-circle"></i> Personal Information</h6>
                            </div>
                            <div class="form-grid responsive-grid">
                                <div class="form-group-enhanced">
                                    <label for="birthDate" class="form-label">
                                        <i class="fas fa-calendar-alt"></i>
                                        Student Number
                                    </label>
                                    <div class="input-with-icon">
                                        <input type="text" class="form-control-enhanced" id="studentNo" required>
                                        <i class='fas fa-user-graduate input-icon-right'></i>
                                    </div>
                                </div>

                                <div class="form-group-enhanced">
                                    <label for="birthDate" class="form-label">
                                        <i class="fas fa-calendar-alt"></i>
                                        Birth Date
                                    </label>
                                    <div class="input-with-icon">
                                        <input type="date" class="form-control-enhanced" id="birthDate" required>
                                        <i class="fas fa-calendar input-icon-right"></i>
                                    </div>
                                </div>
                                
                                <div class="form-group-enhanced">
                                    <label for="gender" class="form-label">
                                        <i class="fas fa-venus-mars"></i>
                                        Sex
                                    </label>
                                    <div class="custom-select">
                                        <select class="form-control-enhanced" id="sex" required>
                                            <option id="_option" value="" disabled selected>Select gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                        <i class="fas fa-chevron-down select-arrow"></i>
                                    </div>
                                </div>

                                <div class="form-group-enhanced">
                                    <label for="gender" class="form-label">
                                        <i class="fas fa-user"></i>
                                        Status
                                    </label>
                                    <div class="custom-select">
                                        <select class="form-control-enhanced" id="status" required>
                                            <option value="" disabled selected>Status</option>
                                            <option value="Single">Single</option>
                                            <option value="Married">Married</option>
                                        </select>
                                        <i class="fas fa-chevron-down select-arrow"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <h6><i class="fas fa-home"></i> Address Information</h6>
                            </div>
                            <div class="address-grid">
                                <div class="form-group-enhanced">
                                    <label for="houseStreet" class="form-label">
                                        <i class="fas fa-map-marker-alt"></i>
                                        House No. / Street
                                    </label>
                                    <input type="text" class="form-control-enhanced" id="houseStreet" placeholder="e.g., 123 Main St" required>
                                </div>

                                <div class="form-group-enhanced">
                                    <label for="gender" class="form-label">
                                        <i class="fas fa-user"></i>
                                        Region
                                    </label>
                                    <div class="custom-select">
                                        <select class="form-control-enhanced" id="region" onchange="loadProvinces(this.value)" required>
                                            <option value="">Select Region</option>
                                            @foreach(App\Helpers\PSGC::getRegions() as $region)
                                                <option value="{{ $region['designation'] }}">{{ $region['name'] }}</option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-chevron-down select-arrow"></i>
                                    </div>
                                </div>

                                <div class="form-group-enhanced">
                                    <label for="gender" class="form-label">
                                        <i class="fas fa-user"></i>
                                        Province
                                    </label>
                                    <div class="custom-select">
                                        <select class="form-control-enhanced" id="province" onchange="loadMunicipalities(this.value)" required>
                                            <option value="">Select Province</option>
                                        </select>
                                        <i class="fas fa-chevron-down select-arrow"></i>
                                    </div>
                                </div>

                                <div class="form-group-enhanced">
                                    <label for="gender" class="form-label">
                                        <i class="fas fa-user"></i>
                                        Municipality/City
                                    </label>
                                    <div class="custom-select">
                                        <select class="form-control-enhanced" id="municipality" onchange="loadBarangays(this.value)" required>
                                            <option value="">Select Municipality/City</option>
                                        </select>
                                        <i class="fas fa-chevron-down select-arrow"></i>
                                    </div>
                                </div>

                                <div class="form-group-enhanced">
                                    <label for="gender" class="form-label">
                                        <i class="fas fa-user"></i>
                                        Barangay
                                    </label>
                                    <div class="custom-select">
                                        <select class="form-control-enhanced" id="barangay" required>
                                            <option value="">Select Barangay</option>
                                        </select>
                                        <i class="fas fa-chevron-down select-arrow"></i>
                                    </div>
                                </div>
                                
                                <div class="form-group-enhanced">
                                    <label for="zipCode" class="form-label">
                                        <i class="fas fa-mail-bulk"></i>
                                        Zip Code
                                    </label>
                                    <div class="custom-select">
                                        <select class="form-control-enhanced" id="zip-code" required>
                                        </select>
                                        <i class="fas fa-chevron-down select-arrow"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Information Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <h6><i class="fas fa-user-lock"></i> Account Information</h6>
                            </div>
                            
                            <div class="form-group-enhanced">
                                <label for="registerEmail" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Email Address
                                </label>
                                <div class="input-with-icon">
                                    <input type="email" class="form-control-enhanced" id="registerEmail" placeholder="your email address..." required>
                                    <!-- <span class="email-domain">@evsu.edu.ph</span> -->
                                </div>
                                <div id="RloginEmailError" class="form-hint">Must use valid EVSU email address</div>
                            </div>
                            
                            <div class="form-group-enhanced">
                                <label for="registerPassword" class="form-label">
                                    <i class="fas fa-lock"></i>
                                    Password
                                </label>
                                <div class="password-input-container">
                                    <input type="password" class="form-control-enhanced" id="registerPassword" placeholder="Create a strong password" required>
                                    <button id="register_show_password" type="button" class="toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                
                                <!-- Password Strength Meter -->
                                <div class="password-strength-enhanced">
                                    <div class="strength-header">
                                        <span>Password Strength:</span>
                                        <span class="strength-text" id="passwordStrengthText">Weak</span>
                                    </div>
                                    <div class="strength-meter">
                                        <div class="strength-meter-fill" id="passwordStrengthBar"></div>
                                    </div>
                                </div>
                                
                                <!-- Password Requirements -->
                                <div class="password-requirements-grid">
                                    <div class="requirement-item" id="req-length">
                                        <i class="fas fa-circle"></i>
                                        <span>8+ characters</span>
                                    </div>
                                    <div class="requirement-item" id="req-uppercase">
                                        <i class="fas fa-circle"></i>
                                        <span>Uppercase letter</span>
                                    </div>
                                    <div class="requirement-item" id="req-lowercase">
                                        <i class="fas fa-circle"></i>
                                        <span>Lowercase letter</span>
                                    </div>
                                    <div class="requirement-item" id="req-number">
                                        <i class="fas fa-circle"></i>
                                        <span>Number</span>
                                    </div>
                                    <div class="requirement-item" id="req-special">
                                        <i class="fas fa-circle"></i>
                                        <span>Special character</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group-enhanced">
                                <label for="repeatPassword" class="form-label">
                                    <i class="fas fa-redo"></i>
                                    Confirm Password
                                </label>
                                <div class="password-input-container">
                                    <input type="password" class="form-control-enhanced" id="repeatPassword" placeholder="Re-enter your password" required>
                                    <button id="register_show_password2" type="button" class="toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="password-match" id="passwordMatchIndicator">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Passwords match</span>
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Submit -->
                        <div class="form-section">
                            <div class="terms-container">
                                <div class="form-check-enhanced">
                                    <input type="checkbox" class="form-check-input-enhanced" id="termsAgreement" required>
                                    <label class="form-check-label-enhanced" for="termsAgreement">
                                        I agree to the <a href="#" class="terms-link">Terms of Service</a> and <a href="#" class="terms-link">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary-enhanced w-100">
                                <span class="btn-text">Create Account</span>
                                <i class="fas fa-arrow-right btn-icon"></i>
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Modal Footer -->
                <div class="modal-footer-enhanced">
                    <p class="footer-text">
                        Already have an account?
                        <a href="#" class="footer-link" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">
                            Sign In Here
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="#" id="_home" class="footer-link" data-bs-dismiss="modal">
                            ← back
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Code Modal -->
    <div class="modal fade" id="verificationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-shield-alt"></i>
                        Email Verification
                    </h5>
                    <button type="button" class="btn-close btn-close-enhanced" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="verification-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Verify Your Email</h4>
                        <p class="text-muted" id="verificationEmailText">
                            We've sent a 6-digit verification code to your email.
                        </p>
                    </div>

                    <form id="verificationForm">
                        <input type="hidden" id="verificationEmail" name="email">
                        
                        <div class="form-group-enhanced">
                            <label for="verificationCode" class="form-label">
                                <i class="fas fa-key"></i>
                                Verification Code
                            </label>
                            <div class="input-group-enhanced">
                                <input type="text" class="form-control-enhanced text-center" id="verificationCode" 
                                    placeholder="Enter 6-digit code" maxlength="6" required>
                                <i class="form-icon fas fa-shield-alt"></i>
                            </div>
                            <div class="invalid-feedback">Please enter the 6-digit verification code</div>
                        </div>

                        <div class="text-center mb-3">
                            <small class="text-muted" id="timerText">
                                Code expires in: <span id="countdown">10:00</span>
                            </small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-enhanced btn-enhanced-outline w-50" id="resendCodeBtn" disabled>
                                <span id="resendText">Resend Code</span>
                                <span class="spinner-border spinner-border-sm" style="display: none;" id="resendSpinner"></span>
                            </button>
                            <button onclick="verif()" type="submit" class="btn btn-enhanced btn-enhanced-primary w-50" id="verifyBtn">
                                <span id="verifyBtnText">Verify & Register</span>
                                <span class="spinner-border spinner-border-sm" style="display: none;" id="verifySpinner"></span>
                            </button>
                        </div>
                    </form>

                    <div class="alert alert-success mt-3" style="display: none;" id="successAlert">
                        <i class="fas fa-check-circle"></i> Verification successful! Redirecting...
                    </div>

                    <div class="alert alert-danger mt-3" style="display: none;" id="errorAlert">
                        <i class="fas fa-exclamation-circle"></i> <span id="errorMessage"></span>
                    </div>
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
     <script src="{{asset('js/jquery.js')}}"></script>
    <!-- <script src="script.js"></script> -->
     <script src="{{asset('js/function/index_student.js')}}"></script>
    <script src="{{asset('js/sweetalert2.js')}}"></script>
    <script src="{{ asset('js/psgc/psgc-handler.js') }}"></script>
    <script src="{{ asset('js/psgc/smart-zip-codes.js') }}"></script>
    <script>
        // Initialize smart zip codes
        smartZipCodes.load();
        
        function loadProvinces(regionDesignation) {
            const provinceSelect = document.getElementById('province');
            provinceSelect.innerHTML = '<option value="">Select Province</option>';
            
            // Clear dependent fields
            document.getElementById('municipality').innerHTML = '<option value="">Select Municipality/City</option>';
            document.getElementById('barangay').innerHTML = '<option value="">Select Barangay</option>';
            document.getElementById('zip-code').innerHTML = '<option value="">Select Zip Code</option>';
            
            if (regionDesignation) {
                const provinces = psgc.provinces.findByRegion(regionDesignation);
                provinces.forEach(province => {
                    const option = document.createElement('option');
                    option.value = province.name;
                    option.textContent = province.name;
                    provinceSelect.appendChild(option);
                });
            }
        }

        function loadMunicipalities(provinceName) {
            const municipalitySelect = document.getElementById('municipality');
            municipalitySelect.innerHTML = '<option value="">Select Municipality/City</option>';
            
            // Clear dependent fields
            document.getElementById('barangay').innerHTML = '<option value="">Select Barangay</option>';
            document.getElementById('zip-code').innerHTML = '<option value="">Select Zip Code</option>';
            
            if (provinceName) {
                const municipalities = psgc.municipalities.findByProvince(provinceName);
                municipalities.forEach(municipality => {
                    const option = document.createElement('option');
                    option.value = municipality.name;
                    option.textContent = `${municipality.name} ${municipality.city ? '(City)' : ''}`;
                    municipalitySelect.appendChild(option);
                });
            }
        }

        function loadBarangays(municipalityName) {
            const barangaySelect = document.getElementById('barangay');
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            
            // Clear zip code
            document.getElementById('zip-code').innerHTML = '<option value="">Select Zip Code</option>';
            
            if (municipalityName) {
                const barangays = psgc.barangays.findByMunicipality(municipalityName);
                barangays.forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay.name;
                    option.textContent = barangay.name;
                    barangaySelect.appendChild(option);
                });
                
                // Load zip codes for this municipality
                loadZipCodes(municipalityName);
            }
        }

        function loadZipCodes(municipalityName) {
            const zipSelect = document.getElementById('zip-code');
            zipSelect.innerHTML = '<option value="">Select Zip Code</option>';
            
            if (municipalityName) {
                // Get municipality data to check if it's a city
                const municipality = psgc.municipalities.find(municipalityName);
                const isCity = municipality ? municipality.city : false;
                
                // Get zip codes for this municipality
                const zipCodes = smartZipCodes.getZipCodesForMunicipality(municipalityName, isCity);
                
                if (zipCodes.length > 0) {
                    zipCodes.forEach(zipCode => {
                        const option = document.createElement('option');
                        option.value = zipCode;
                        option.textContent = zipCode;
                        zipSelect.appendChild(option);
                    });
                } else {
                    // If no zip codes found, show a message
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'No zip code available';
                    option.disabled = true;
                    zipSelect.appendChild(option);
                }
            }
        }

        setTimeout(() => {
            console.log('Testing mapping...');
            console.log('Ormoc zip codes:', smartZipCodes.getZipCodesForMunicipality('Ormoc', true));
            console.log('Baybay zip codes:', smartZipCodes.getZipCodesForMunicipality('Baybay', true));
            console.log('Mandaluyong zip codes:', smartZipCodes.getZipCodesForMunicipality('Mandaluyong', true));
        }, 2000);
    </script>
    <!-- <script>
        // Wait for data to load
        setTimeout(() => {
            console.log('All Regions:', psgc.regions.all());
            console.log('NCR:', psgc.regions.find('National Capital Region'));
            console.log('Regions with "Visayas":', psgc.regions.filter('visayas'));
            
            // Example: Get all provinces in NCR
            console.log('Provinces in NCR:', psgc.provinces.findByRegion('NCR'));
        }, 1000);

        function loadProvinces(regionDesignation) {
            const provinceSelect = document.getElementById('province');
            provinceSelect.innerHTML = '<option value="">Select Province</option>';
            
            if (regionDesignation) {
                const provinces = psgc.provinces.findByRegion(regionDesignation);
                provinces.forEach(province => {
                    const option = document.createElement('option');
                    option.value = province.name;
                    option.textContent = province.name;
                    provinceSelect.appendChild(option);
                });
            }
        }

        function loadMunicipalities(provinceName) {
            const municipalitySelect = document.getElementById('municipality');
            municipalitySelect.innerHTML = '<option value="">Select Municipality/City</option>';
            
            if (provinceName) {
                const municipalities = psgc.municipalities.findByProvince(provinceName);
                municipalities.forEach(municipality => {
                    const option = document.createElement('option');
                    option.value = municipality.name;
                    option.textContent = `${municipality.name} ${municipality.city ? '(City)' : ''}`;
                    municipalitySelect.appendChild(option);
                });
            }
        }

        function loadBarangays(municipalityName) {
            const barangaySelect = document.getElementById('barangay');
            barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
            
            if (municipalityName) {
                const barangays = psgc.barangays.findByMunicipality(municipalityName);
                barangays.forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay.code;
                    option.textContent = barangay.name;
                    barangaySelect.appendChild(option);
                });
            }
        }
    </script> -->
    <script src="{{asset('js/sweetalert3.js')}}"></script>
        <script>
        // Function to get the dominant background color from an element
        function getBackgroundColor(element) {
            // Get computed style
            const style = getComputedStyle(element);
            let backgroundColor = style.backgroundColor;
            
            // If background is transparent, check parent elements up to html
            if (backgroundColor === 'rgba(0, 0, 0, 0)' || backgroundColor === 'transparent') {
                let currentElement = element.parentElement;
                while (currentElement && currentElement !== document.documentElement) {
                    const parentBg = getComputedStyle(currentElement).backgroundColor;
                    if (parentBg !== 'rgba(0, 0, 0, 0)' && parentBg !== 'transparent') {
                        backgroundColor = parentBg;
                        break;
                    }
                    currentElement = currentElement.parentElement;
                }
            }
            
            return backgroundColor;
        }

        // Convert RGB/RGBA to hex format (RRGGBB or AARRGGBB)
        function colorToHex(color) {
            // Handle named colors
            if (color === 'transparent') return '00000000';
            
            // Parse RGB/RGBA
            const match = color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
            
            if (!match) {
                // Try hex format
                if (color.startsWith('#')) {
                    const hex = color.substring(1);
                    if (hex.length === 3) {
                        // Convert #RGB to #RRGGBB
                        return hex.split('').map(c => c + c).join('');
                    }
                    if (hex.length === 6 || hex.length === 8) {
                        return hex;
                    }
                }
                return '101126'; // Fallback to your dark theme color
            }
            
            const r = parseInt(match[1]).toString(16).padStart(2, '0');
            const g = parseInt(match[2]).toString(16).padStart(2, '0');
            const b = parseInt(match[3]).toString(16).padStart(2, '0');
            
            // Handle alpha channel
            if (match[4]) {
                const alpha = Math.round(parseFloat(match[4]) * 255).toString(16).padStart(2, '0');
                return alpha + r + g + b; // AARRGGBB format
            }
            
            return r + g + b; // RRGGBB format
        }

        // Determine if color is dark or light
        function getTextStyleForColor(hexColor) {
            // Remove alpha if present (first 2 characters)
            const rgbHex = hexColor.length === 8 ? hexColor.substring(2) : hexColor;
            
            // Convert to RGB
            const r = parseInt(rgbHex.substring(0, 2), 16);
            const g = parseInt(rgbHex.substring(2, 4), 16);
            const b = parseInt(rgbHex.substring(4, 6), 16);
            
            // Calculate relative luminance (WCAG formula)
            const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
            
            // Use dark text on light backgrounds, light text on dark backgrounds
            return luminance > 0.5 ? 'dark' : 'light';
        }

        // Function to set status bar based on current background
        function setDynamicStatusBar() {
            // Check if running in Median app
            if (navigator.userAgent.indexOf('median') > -1 && typeof median !== 'undefined') {
                // Get body background color
                const bgColor = getBackgroundColor(document.body);
                
                // Convert to hex
                const hexColor = colorToHex(bgColor);
                
                // Determine text style
                const textStyle = getTextStyleForColor(hexColor);
                
                console.log('Detected background:', {
                    original: bgColor,
                    hex: hexColor,
                    textStyle: textStyle
                });
                
                // Set status bar
                median.statusbar.set({
                    'style': textStyle,
                    'color': hexColor,
                    'overlay': false,
                    'blur': true
                });
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial status bar
            setDynamicStatusBar();
            
            // Listen for theme toggle
            const themeToggleBtn = document.getElementById('themeToggle');
            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', function() {
                    // Wait for theme to change and re-render
                    setTimeout(setDynamicStatusBar, 150);
                });
            }
            
            // Also update when CSS transitions complete
            document.body.addEventListener('transitionend', function(e) {
                if (e.propertyName.includes('background') || e.propertyName.includes('color')) {
                    setDynamicStatusBar();
                }
            });
        });

        // Median library ready callback
        function median_library_ready() {
            setDynamicStatusBar();
        }
        
        // Optional: Also update on window resize (in case layout changes affect background)
        window.addEventListener('resize', setDynamicStatusBar);
    </script>
</body>
</html>