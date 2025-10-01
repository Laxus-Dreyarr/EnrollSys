// Name validation function
function isValidName(name) {
    // Check if name has at least 2 characters
    if (name.length < 2) {
        return false;
    }
    
    // Check if name contains only letters, spaces, hyphens, and apostrophes
    const nameRegex = /^[A-Za-z\s\-']+$/;
    return nameRegex.test(name);
}

// Email validation function
function isValidEmail(email) {
    // Check EVSUmail format
    const emailRegex = /^[^\s@]+@evsu\.edu\.ph$/;
    return emailRegex.test(email);
}

// Show success animation
function showSuccessAnimation() {
    const modalBody = document.querySelector('#registerModal .modal-body');
    modalBody.innerHTML = `
        <div class="success-animation">
            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
            </svg>
            <div class="success-message">
                Registration Successful!<br>
                Welcome to EnrollSys.
            </div>
        </div>
    `;
    
    // Close modal after 3 seconds
    setTimeout(function() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
        modal.hide();
        
        // Reset form for next registration
        document.getElementById('registerForm').reset();
        
        // Restore original form content
        modalBody.innerHTML = document.getElementById('registerForm').outerHTML;
        
        // Reattach event listeners
        attachRegistrationEventListeners();
    }, 3000);
}

// Attach event listeners to registration form
function attachRegistrationEventListeners() {
    const registerForm = document.getElementById('registerForm');
    const givenName = document.getElementById('givenName');
    const lastName = document.getElementById('lastName');
    const middleName = document.getElementById('middleName');
    const registerEmail = document.getElementById('registerEmail');
    
    // Real-time name validation
    [givenName, lastName, middleName].forEach(field => {
        if (field) {
            field.addEventListener('blur', function() {
                if (this.value.trim() !== '' && !isValidName(this.value)) {
                    this.classList.add('is-invalid');
                    this.nextElementSibling.textContent = 'Name must contain only letters and be at least 2 characters long.';
                } else {
                    this.classList.remove('is-invalid');
                }
            });
        }
    });
    
    // Real-time email validation
    if (registerEmail) {
        registerEmail.addEventListener('blur', function() {
            if (!isValidEmail(this.value)) {
                this.classList.add('is-invalid');
                this.nextElementSibling.textContent = 'Please enter a valid EVSUmail address (username@evsu.edu.ph).';
            } else {
                // Check if email exists
                checkEmailExists(this.value);
            }
        });
    }
    
    // Form submission handler
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const givenNameVal = givenName.value.trim();
            const lastNameVal = lastName.value.trim();
            const middleNameVal = middleName.value.trim();
            const emailVal = registerEmail.value.trim();
            const passwordVal = document.getElementById('registerPassword').value;
            
            // Validate names
            let isValid = true;
            
            if (!isValidName(givenNameVal)) {
                givenName.classList.add('is-invalid');
                givenName.nextElementSibling.textContent = 'Given name must contain only letters and be at least 2 characters long.';
                isValid = false;
            }
            
            if (!isValidName(lastNameVal)) {
                lastName.classList.add('is-invalid');
                lastName.nextElementSibling.textContent = 'Last name must contain only letters and be at least 2 characters long.';
                isValid = false;
            }
            
            if (middleNameVal && !isValidName(middleNameVal)) {
                middleName.classList.add('is-invalid');
                middleName.nextElementSibling.textContent = 'Middle name must contain only letters and be at least 2 characters long.';
                isValid = false;
            }
            
            // Validate email
            if (!isValidEmail(emailVal)) {
                registerEmail.classList.add('is-invalid');
                registerEmail.nextElementSibling.textContent = 'Please enter a valid EVSUmail address (username@evsu.edu.ph).';
                isValid = false;
            }
            
            // Check password match
            const repeatPassword = document.getElementById('repeatPassword');
            if (passwordVal !== repeatPassword.value) {
                repeatPassword.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                return;
            }
            
            // Submit form via AJAX
            const formData = new FormData();
            formData.append('action', 'register');
            formData.append('givenName', givenNameVal);
            formData.append('lastName', lastNameVal);
            formData.append('middleName', middleNameVal);
            formData.append('email', emailVal);
            formData.append('password', passwordVal);
            
            fetch('/exe/student', {
                method: 'POST',
                body: formData,
                _token: $('meta[name="csrf-token"]').attr('content')
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessAnimation();
                } else {
                    // alert('Registration failed: ' + data.message);
                }
            })
            .catch(error => {
                // console.error('Error:', error);
                // alert('Registration failed. Please try again.');
            });
        });
    }
}

let emailTimeout;
// Check if email exists
function checkEmailExists(email) {

    // Clear previous timeout
    clearTimeout(emailTimeout);

    // Debounce the API call
    emailTimeout = setTimeout(() => {
        const formData = new FormData();
        formData.append('action', 'check_email');
        formData.append('email', email);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        fetch('/exe/student', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            const emailField = document.getElementById('registerEmail');
            if (data.exists) {
                emailField.classList.add('is-invalid');
                emailField.nextElementSibling.textContent = 'This email is already registered!';
            } else {
                emailField.classList.remove('is-invalid');
            }
        })
        .catch(error => {
            console.error('Error checking email:', error);
        });
        
    }, 500); // 500ms debounce delay
    
    

    clearTimeout(emailTimeout);
    emailTimeout = setTimeout(() => {
        // Your fetch code here
    }, 500);
}


// Login form handling
function handleLogin() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Get DOM elements correctly
                const emailEl = document.getElementById('loginEmail');
                const passwordEl = document.getElementById('loginPassword');
                const submitBtn = document.getElementById('submitBtn');
                
                // Reset previous states
                $('.is-invalid').removeClass('is-invalid');
                $('#successMessage').text('');
                
                // Get values
                let email = $("#loginEmail").val();
                let password = $("#loginPassword").val();
                
                // Disable submit button during AJAX call
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...';

                // Your actual AJAX code should look like this:
                $.ajax({
                    url: 'exe/student_login.php',
                    method: 'POST',
                    data: {email: email, password: password},
                    success: function(response) {
                        // Re-enable the button
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Login';
                        
                        if(response == '1'){
                            emailEl.classList.add('is-invalid', 'shake');
                            setTimeout(() => emailEl.classList.remove('shake'), 500);
                        } else if(response == '2'){
                            passwordEl.classList.add('is-invalid', 'shake');
                            setTimeout(() => passwordEl.classList.remove('shake'), 500);
                        } else if(response == '0'){
                            alert('Your account has been suspended. Please contact the administrator for assistance.');
                        } else if(response == '3'){
                            showLoginSuccess();                            
                                    
                        }
                    },
                    error: function() {
                        // Re-enable the button on error too
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Login';
                        alert('An error occurred during login. Please try again.');
                    }
                });
            
            
            
        });
    }
    
}

// Show login success animation
function showLoginSuccess(user) {
    const modalBody = document.querySelector('#loginModal .modal-body');
    modalBody.innerHTML = `
        <div class="success-animation">
            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
            </svg>
            <div class="success-message">
                Welcome back, ${user.firstname} ${user.lastname}!<br>
                ID: ${user.id}<br>
                Redirecting to your dashboard...
            </div>
        </div>
    `;
    
    // Close modal and redirect after 3 seconds
    setTimeout(function() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
        modal.hide();
        
        // Redirect to student dashboard
        window.location.href = 'st_dashboard.php';
    }, 3000);
}


function showSuccessAlert() {
            const alertContainer = document.getElementById('alertContainer');
            
            const alertEl = document.createElement('div');
            alertEl.className = 'alert-success-custom';
            alertEl.innerHTML = `
                <div class="alert-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div class="alert-content">
                    <h4>Login Successful!</h4>
                    <p>Welcome back! You've successfully signed in to your account.</p>
                </div>
                <div class="alert-progress">
                    <div class="alert-progress-bar"></div>
                </div>
            `;
            
            alertContainer.appendChild(alertEl);
            
            // Trigger animation
            setTimeout(() => {
                alertEl.classList.add('show');
            }, 10);
            
            // Remove alert after 3 seconds
            setTimeout(() => {
                alertEl.classList.remove('show');
                
                // Remove element after transition
                setTimeout(() => {
                    alertContainer.removeChild(alertEl);
                }, 500);
            window.location.href = 'st_dashboard.php';
        }, 1000);
}


document.addEventListener('DOMContentLoaded', function() {


    // Theme Toggle Functionality
    const themeToggleBtn = document.getElementById('themeToggle');
    const body = document.body;
    
    // Check for saved theme preference or use preferred color scheme
    const savedTheme = localStorage.getItem('theme') || 
                      (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    
    // Apply the saved theme
    if (savedTheme === 'dark') {
        body.classList.add('dark-theme');
        themeToggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
    }
    
    // Theme toggle button click event
    themeToggleBtn.addEventListener('click', function() {
        body.classList.toggle('dark-theme');
        
        if (body.classList.contains('dark-theme')) {
            localStorage.setItem('theme', 'dark');
            themeToggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
        } else {
            localStorage.setItem('theme', 'light');
            themeToggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
        }
    });
    
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
    
    // Back to top button
    const backToTopBtn = document.querySelector('.back-to-top');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            backToTopBtn.classList.add('active');
        } else {
            backToTopBtn.classList.remove('active');
        }
    });
    
    backToTopBtn.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Counter animation for stats
    const counters = document.querySelectorAll('.counter');
    const speed = 200;
    
    function animateCounters() {
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const increment = target / speed;
            
            if (count < target) {
                counter.innerText = Math.ceil(count + increment);
                setTimeout(animateCounters, 1);
            } else {
                counter.innerText = target;
            }
        });
    }
    
    // Start counter animation when stats section is in view
    const statsSection = document.querySelector('.stats-section');
    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            animateCounters();
            observer.unobserve(statsSection);
        }
    }, { threshold: 0.5 });
    
    observer.observe(statsSection);

    // Line to attach registration event listeners
    attachRegistrationEventListeners();
    
    // Form validation for registration
    const registerForm = document.getElementById('registerForm');
    const registerEmail = document.getElementById('registerEmail');
    const registerPassword = document.getElementById('registerPassword');
    const repeatPassword = document.getElementById('repeatPassword');
    
    if (registerForm) {
        // EVSUmail validation
        registerEmail.addEventListener('input', function() {
            if (!this.value.endsWith('@evsu.edu.ph')) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
        
        // Password validation
        registerPassword.addEventListener('input', function() {
            const password = this.value;
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };
            
            // Update requirement indicators
            document.getElementById('req-length').classList.toggle('valid', requirements.length);
            document.getElementById('req-uppercase').classList.toggle('valid', requirements.uppercase);
            document.getElementById('req-lowercase').classList.toggle('valid', requirements.lowercase);
            document.getElementById('req-number').classList.toggle('valid', requirements.number);
            document.getElementById('req-special').classList.toggle('valid', requirements.special);
        });
        
        // Repeat password validation
        repeatPassword.addEventListener('input', function() {
            if (this.value !== registerPassword.value) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
        
        // Form submission
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate all fields
            let isValid = true;
            
            // Check EVSUmail
            if (!registerEmail.value.endsWith('@evsu.edu.ph')) {
                registerEmail.classList.add('is-invalid');
                isValid = false;
            }
            
            // Check password match
            if (registerPassword.value !== repeatPassword.value) {
                repeatPassword.classList.add('is-invalid');
                isValid = false;
            }
            
            // Check required fields
            const requiredFields = this.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });
            
        });
    }
    

    // Add login handling
    // handleLogin(); this is the past function
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const loginBtn = document.getElementById('loginBtn');
    const loginSpinner = document.getElementById('loginSpinner');
    const errorAlert = document.getElementById('errorAlert');
    const successAlert = document.getElementById('successAlert');

    // Handle form submission
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form values
                const email = emailInput.value.trim();
                const password = passwordInput.value;

                // Reset previous states
                $('.is-invalid').removeClass('is-invalid');
            
                
                // Show loading state
                loginBtn.disabled = true;
                loginSpinner.style.display = 'none';
                loginBtn.querySelector('span').textContent = 'Logging in...';
                
                // Create FormData
                const formData = new FormData();
                formData.append('email', email);
                formData.append('password', password);
                
                // Send login request
                fetch('exe/student_login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    if (data === '3') {
                       showSuccessAlert();
                    } else if (data === '2') {
                        passwordInput.nextElementSibling.textContent = 'Incorrect password. Please try again.';
                        passwordInput.classList.add('is-invalid', 'shake');
                        setTimeout(() => passwordInput.classList.remove('shake'), 500);
                    } else if (data === '1') {
                        emailInput.nextElementSibling.textContent = 'Email not found. Please check your EVSUmail address.';
                        emailInput.classList.add('is-invalid', 'shake');
                        setTimeout(() => emailInput.classList.remove('shake'), 500);
                    } else if (data === '0') {
                        showError('Your account has been suspended. Please contact support.');
                    } else {
                        showError('An unexpected error occurred. Please try again.');
                    }
                })
                .catch(error => {
                    showError('Network error. Please check your connection and try again.');
                    console.error('Login error:', error);
                })
                .finally(() => {
                    // Reset button state
                    loginBtn.disabled = false;
                    loginSpinner.style.display = 'none';
                    loginBtn.querySelector('span').textContent = 'Login';
                });
            });
            
            // Helper functions
            function showError(message) {
                errorAlert.textContent = message;
                errorAlert.style.display = 'block';
                successAlert.style.display = 'none';
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    errorAlert.style.display = 'none';
                }, 5000);
            }
            
            function showSuccess(message) {
                successAlert.textContent = message;
                successAlert.style.display = 'block';
                errorAlert.style.display = 'none';
            }
        
    
    // Modal animation
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            const modalDialog = this.querySelector('.modal-dialog');
            modalDialog.style.opacity = '50';
            modalDialog.style.transform = 'translateY(0px)';
            
            setTimeout(() => {
                modalDialog.style.transition = 'all 0.3s ease';
                modalDialog.style.opacity = '50';
                modalDialog.style.transform = 'translateY(20px)';
            }, 10);
        });
    });
    
    // Switch between login and register modals
    const loginModalLinks = document.querySelectorAll('[data-bs-target="#loginModal"]');
    const registerModalLinks = document.querySelectorAll('[data-bs-target="#registerModal"]');
    
    loginModalLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.hasAttribute('data-bs-dismiss')) {
                const currentModal = bootstrap.Modal.getInstance(document.querySelector('.modal.show'));
                currentModal.hide();
            }
        });
    });
    
    registerModalLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.hasAttribute('data-bs-dismiss')) {
                const currentModal = bootstrap.Modal.getInstance(document.querySelector('.modal.show'));
                currentModal.hide();
            }
        });
    });
    
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Editable content box functions
    window.copyBox = function(boxId) {
        const box = document.querySelector(`.editable-box[data-box-id="${boxId}"]`);
        if (box) {
            const boxContent = box.innerHTML;
            navigator.clipboard.writeText(box.outerHTML)
                .then(() => {
                    // Show copied feedback
                    const originalText = box.querySelector('.btn-copy i').className;
                    box.querySelector('.btn-copy i').className = 'fas fa-check';
                    setTimeout(() => {
                        box.querySelector('.btn-copy i').className = originalText;
                    }, 2000);
                })
                .catch(err => {
                    console.error('Failed to copy box: ', err);
                });
        }
    };
    
    window.deleteBox = function(boxId) {
        if (confirm('Are you sure you want to delete this content box?')) {
            const box = document.querySelector(`.editable-box[data-box-id="${boxId}"]`);
            if (box) {
                box.style.transform = 'scale(0)';
                setTimeout(() => {
                    box.remove();
                }, 300);
            }
        }
    };
    
    // Animate elements on scroll
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.feature-card, .about-image, .contact-form, .contact-info');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.2;
            
            if (elementPosition < screenPosition) {
                element.classList.add('fade-in-up');
            }
        });
    };
    
    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll(); // Run once on page load
    
    // Parallax effect
    window.addEventListener('scroll', function() {
        const scrollPosition = window.pageYOffset;
        // First background picture
        const parallaxElements = document.querySelectorAll('.parallax');

        // Second background picture
        const parallaxElements2 = document.querySelectorAll('.parallax2');
        
        // parallaxElements.forEach(element => {
        //     // First background picture
        //     element.style.backgroundPositionX = scrollPosition * 1.5 + 'px';
        // });
        
        // Second background picture
        parallaxElements2.forEach(element => {
            // First background picture
            element.style.backgroundPositionY = scrollPosition * 0.5 + 'px';
        });
    });


    // Show/Hide password functionality for registration form
    const passwordInput_reg = document.getElementById('registerPassword');
    const passwordInput_reg_rep = document.getElementById('repeatPassword');
    const showPasswordCheckbox = document.getElementById('register_show_password');
    // Toggle password visibility when checkbox is clicked
    showPasswordCheckbox.addEventListener('change', function() {
        if (this.checked) {
            passwordInput_reg.type = 'text';
            passwordInput_reg_rep.type = 'text';
        } else {
            passwordInput_reg.type = 'password';
            passwordInput_reg_rep.type = 'password';
        }
    });


    // Show/Hide password functionality for login form
    const passwordInput_log = document.getElementById('password');
    const showPasswordCheck = document.getElementById('show_login_password');
    // Toggle password visibility when checkbox is clicked
    showPasswordCheck.addEventListener('change', function() {
        if (this.checked) {
            passwordInput_log.type = 'text';
        } else {
            passwordInput_log.type = 'password';
        }
    });



});