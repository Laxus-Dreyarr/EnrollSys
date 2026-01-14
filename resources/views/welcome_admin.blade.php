<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="bingbot" content="noarchive">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="application-title" content="EnrollSys">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="#101126">
    <meta name="theme-color" content="#101126">
    <title>EnrollSys - Student Enrollment System</title>
    <link rel="website icon" href="{{ asset('img/logo.png') }}">
    <!-- Bootstrap CSS -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
     <link href="{{ asset('style/bootstrap.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <!-- <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"> -->
     <link href="{{ asset('style/google-fonts.css') }}" rel="stylesheet">
    <!-- Custom CSS -->
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
                        <button class="btn btn-primary btn-lg me-3" data-bs-toggle="modal" data-bs-target="#loginModal" style="background-color: maroon; border-color: maroon">Login</button>
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

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Admin Login</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="loginForm">
                        @csrf
                        <div class="mb-3">
                            <label for="loginEmail" class="form-label">EVSUmail</label>
                            <input type="email" class="form-control" id="loginEmail" name="loginEmail" placeholder="username@evsu.edu.ph" required>
                            <div class="invalid-feedback">You don't have an account</div>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="loginPassword" name="loginPassword" required>
                            <div class="invalid-feedback">Wrong password</div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="showPassword">
                            <label class="form-check-label" for="showPassword">Show Password</label>
                        </div>
                        <button style="background-color: rgb(138, 30, 30); border-color: maroon" type="submit" class="btn btn-primary w-100" id="submitBtn">Login</button>
                        <div class="mb-3success-message" id="successMessage"></div>
                    </form>
                    <div class="text-center mt-3">
                        <a href="#" class="text-muted" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal" data-bs-dismiss="modal">Forgot password?</a>
                    </div>
                </div>
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
                                <p>Password must contain:</p>
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
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>
                </div>
                <div class="modal-footer justify-content-center">
                    <p>Already have an account? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login</a></p>
                </div>
            </div>
        </div>
    </div>

        <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="forgotPasswordForm">
                        @csrf
                        <div class="mb-3">
                            <label for="resetEmail" class="form-label">EVSUmail</label>
                            <input type="email" class="form-control" id="resetEmail" placeholder="username@evsu.edu.ph" required>
                            <div class="invalid-feedback">Please enter a valid EVSUmail address</div>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="newPassword" required>
                            <div class="password-requirements">
                                <p>Password must contain:</p>
                                <ul>
                                    <li id="reset-req-length"><i class="fas fa-circle"></i> At least 8 characters</li>
                                    <li id="reset-req-uppercase"><i class="fas fa-circle"></i> At least one uppercase letter</li>
                                    <li id="reset-req-lowercase"><i class="fas fa-circle"></i> At least one lowercase letter</li>
                                    <li id="reset-req-number"><i class="fas fa-circle"></i> At least one number</li>
                                    <li id="reset-req-special"><i class="fas fa-circle"></i> At least one special character</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirmPassword" required>
                            <div class="invalid-feedback">Passwords do not match</div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="showPassword2">
                            <label class="form-check-label" for="showPassword2">Show Password</label>
                        </div>
                        <button style="background-color: rgb(138, 30, 30); border-color: maroon" type="submit" class="btn btn-primary w-100" id="sendCodeBtn">Send Verification Code</button>
                    </form>
                </div>
                <div class="modal-footer justify-content-center">
                    <p>Remember your password? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" data-bs-dismiss="modal">Login</a></p>
                </div>
            </div>
        </div>
    </div>

        <!-- Verification Code Modal -->
    <div class="modal fade" id="verificationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Verify Your Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="verification-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <p>We've sent a verification code to <span id="emailSentTo" class="fw-bold">user@evsu.edu.ph</span></p>
                    </div>
                    <form id="verificationForm">
                        <div class="mb-4">
                            <label for="verificationCode" class="form-label">Verification Code</label>
                            <div class="verification-inputs d-flex justify-content-between">
                                <input type="text" class="form-control verification-code" maxlength="1" required>
                                <input type="text" class="form-control verification-code" maxlength="1" required>
                                <input type="text" class="form-control verification-code" maxlength="1" required>
                                <input type="text" class="form-control verification-code" maxlength="1" required>
                                <input type="text" class="form-control verification-code" maxlength="1" required>
                                <input type="text" class="form-control verification-code" maxlength="1" required>
                            </div>
                            <div class="invalid-feedback">Please enter the 6-digit code</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-3" id="verifyCodeBtn">Verify Code</button>
                        <div class="text-center">
                            <p class="mb-0">Didn't receive the code? <a href="#" id="resendCode">Resend</a></p>
                            <small class="text-muted" id="countdown">(60s)</small>
                        </div>
                    </form>
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
     <!-- <script src="javaScript/jquery-3.6.0.min.js"></script> -->
     <script src="{{asset('js/jquery.js')}}"></script>
    <!-- Custom JS -->
     <script src="{{asset('js/function/index.js')}}"></script>
     <script>
        // Status bar configuration for Median.co app
        function configureMedianStatusBar() {
            // Check if running in Median app
            if (navigator.userAgent.indexOf('median') > -1) {
                // Function to set status bar based on current theme
                function setStatusBarForTheme() {
                    const isDarkTheme = document.body.classList.contains('dark-theme');
                    const bodyBackgroundColor = getComputedStyle(document.body).backgroundColor;
                    
                    // Convert RGB to hex format (RRGGBB)
                    const rgbToHex = (rgb) => {
                        const values = rgb.match(/\d+/g);
                        if (!values) return '101126'; // fallback dark color
                        
                        const hex = values.slice(0, 3).map(x => {
                            const hex = parseInt(x).toString(16);
                            return hex.length === 1 ? '0' + hex : hex;
                        }).join('');
                        
                        return hex;
                    };
                    
                    const backgroundColor = rgbToHex(bodyBackgroundColor);
                    const theme = isDarkTheme ? 'dark' : 'light';
                    
                    // Set status bar properties
                    median.statusbar.set({
                        'style': theme, // 'light' or 'dark' text/icons
                        'color': backgroundColor, // Match body background color
                        'overlay': true, // Content extends under status bar
                        'blur': false // iOS only
                    });
                }
                
                // Set initial status bar
                setStatusBarForTheme();
                
                // Listen for theme changes
                const themeToggleBtn = document.getElementById('themeToggle');
                if (themeToggleBtn) {
                    themeToggleBtn.addEventListener('click', () => {
                        // Wait a moment for theme to be applied
                        setTimeout(setStatusBarForTheme, 100);
                    });
                }
                
                // Optional: Use Median's helper function (if available)
                try {
                    if (typeof median_match_statusbar_to_body_background_color === 'function') {
                        median_match_statusbar_to_body_background_color();
                    }
                } catch (e) {
                    console.log('Median helper function not available');
                }
            }
        }

        // Call when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            configureMedianStatusBar();
        });

        // Also call when Median library is ready
        if (typeof median_library_ready !== 'undefined') {
            median_library_ready = function() {
                configureMedianStatusBar();
            };
        }
     </script>
</body>
</html>