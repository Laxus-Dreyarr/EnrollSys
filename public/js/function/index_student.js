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

// Password strength checker
function checkPasswordStrength(password) {
    let strength = 0;
    const feedback = {
        weak: { text: 'Weak', class: 'strength-weak', width: '33%' },
        medium: { text: 'Medium', class: 'strength-medium', width: '66%' },
        strong: { text: 'Strong', class: 'strength-strong', width: '100%' }
    };

    // Check password length
    if (password.length >= 8) strength++;
    
    // Check for uppercase letters
    if (/[A-Z]/.test(password)) strength++;
    
    // Check for lowercase letters
    if (/[a-z]/.test(password)) strength++;
    
    // Check for numbers
    if (/[0-9]/.test(password)) strength++;
    
    // Check for special characters
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;

    // Determine strength level
    if (password.length === 0) {
        return { level: 0, text: 'Password strength', class: '', width: '0%' };
    } else if (strength <= 2) {
        return feedback.weak;
    } else if (strength <= 4) {
        return feedback.medium;
    } else {
        return feedback.strong;
    }
}

// Update password strength indicator
function updatePasswordStrength(password) {
    const strengthBar = document.getElementById('passwordStrengthBar');
    const strengthText = document.getElementById('passwordStrengthText');
    
    if (!strengthBar || !strengthText) return;
    
    const strength = checkPasswordStrength(password);
    
    // Update strength bar
    strengthBar.className = 'strength-meter-fill ' + strength.class;
    strengthBar.style.width = strength.width;
    
    // Update strength text
    strengthText.textContent = strength.text;
    strengthText.className = 'strength-text ' + strength.class + '-text';
}

// Update password requirements
function updatePasswordRequirements(password) {
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
    };
    
    // Update requirement indicators
    Object.keys(requirements).forEach(req => {
        const element = document.getElementById(`req-${req}`);
        if (element) {
            element.classList.toggle('valid', requirements[req]);
        }
    });
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
    const registerPassword = document.getElementById('registerPassword');
    
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
            } else {
                // Check if email exists
                checkEmailExists(this.value);
            }
        });
    }
    
    // Real-time password strength and requirements
    if (registerPassword) {
        registerPassword.addEventListener('input', function() {
            const password = this.value;
            
            // Update password strength indicator
            updatePasswordStrength(password);
            
            // Update password requirements
            updatePasswordRequirements(password);
            
            // Validate password match in real-time
            const repeatPassword = document.getElementById('repeatPassword');
            if (repeatPassword && repeatPassword.value) {
                if (password !== repeatPassword.value) {
                    repeatPassword.classList.add('is-invalid');
                } else {
                    repeatPassword.classList.remove('is-invalid');
                }
            }
        });
    }
    
    // Real-time password confirmation validation
    const repeatPassword = document.getElementById('repeatPassword');
    if (repeatPassword) {
        repeatPassword.addEventListener('input', function() {
            const password = registerPassword.value;
            if (this.value !== password) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
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
            const passwordVal = registerPassword.value;
            const repeatPasswordVal = repeatPassword.value;
            
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
                registerEmail.nextElementSibling.textContent = '';
                isValid = false;
            }
            
            // Check password strength
            const strength = checkPasswordStrength(passwordVal);
            if (strength.level <= 2 && passwordVal.length > 0) {
                registerPassword.classList.add('is-invalid');
                isValid = false;
            }
            
            // Check password match
            if (passwordVal !== repeatPasswordVal) {
                repeatPassword.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                // Add shake animation to invalid fields
                const invalidFields = registerForm.querySelectorAll('.is-invalid');
                invalidFields.forEach(field => {
                    field.classList.add('shake');
                    setTimeout(() => field.classList.remove('shake'), 500);
                });
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
            formData.append('repeatPassword', repeatPasswordVal);
            
            // Show loading state
            const submitBtn = registerForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Creating Account...';
            submitBtn.disabled = true;
            
            fetch('/exe/student', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessAnimation();
                } else {
                    // Show error message
                    if (data.message) {
                        alert('Registration failed: ' + data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Registration failed. Please try again.');
            })
            .finally(() => {
                // Reset button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
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
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        fetch('/exe/student', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            const emailField = document.getElementById('registerEmail');
            if (data.exists) {
                emailField.classList.add('is-invalid');
                emailField.nextElementSibling.textContent = '';
            } else {
                emailField.classList.remove('is-invalid');
            }
        })
        .catch(error => {
            console.error('Error checking email:', error);
        });
    }, 500); // 500ms debounce delay
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
            if (alertContainer.contains(alertEl)) {
                alertContainer.removeChild(alertEl);
            }
        }, 500);
        
        window.location.href = 'st_dashboard.php';
    }, 1000);
}

// Initialize password strength indicator on page load
function initializePasswordStrengthIndicator() {
    const passwordInput = document.getElementById('registerPassword');
    if (passwordInput) {
        // Initialize with empty state
        updatePasswordStrength('');
        updatePasswordRequirements('');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize password strength indicator
    initializePasswordStrengthIndicator();

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
    
    // Login form handling
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const loginBtn = document.getElementById('loginBtn');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const email = emailInput.value.trim();
            const password = passwordInput.value;

            // Reset previous states
            const invalidFields = loginForm.querySelectorAll('.is-invalid');
            invalidFields.forEach(field => field.classList.remove('is-invalid'));
            
            // Show loading state
            loginBtn.disabled = true;
            const loginSpinner = document.getElementById('loginSpinner');
            if (loginSpinner) loginSpinner.style.display = 'inline-block';
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
                    alert('Your account has been suspended. Please contact support.');
                } else {
                    alert('An unexpected error occurred. Please try again.');
                }
            })
            .catch(error => {
                alert('Network error. Please check your connection and try again.');
                console.error('Login error:', error);
            })
            .finally(() => {
                // Reset button state
                loginBtn.disabled = false;
                const loginSpinner = document.getElementById('loginSpinner');
                if (loginSpinner) loginSpinner.style.display = 'none';
                loginBtn.querySelector('span').textContent = 'Login';
            });
        });
    }
    
    // Modal animation
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            const modalDialog = this.querySelector('.modal-dialog');
            modalDialog.style.transform = 'translateY(-50px)';
            modalDialog.style.opacity = '0';
            
            setTimeout(() => {
                modalDialog.style.transition = 'all 0.3s ease';
                modalDialog.style.transform = 'translateY(0)';
                modalDialog.style.opacity = '1';
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
                if (currentModal) currentModal.hide();
            }
        });
    });
    
    registerModalLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.hasAttribute('data-bs-dismiss')) {
                const currentModal = bootstrap.Modal.getInstance(document.querySelector('.modal.show'));
                if (currentModal) currentModal.hide();
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
        const parallaxElements2 = document.querySelectorAll('.parallax2');
        
        // Second background picture
        parallaxElements2.forEach(element => {
            element.style.backgroundPositionY = scrollPosition * 0.5 + 'px';
        });
    });

    // Show/Hide password functionality for registration form
    const passwordInput_reg = document.getElementById('registerPassword');
    const passwordInput_reg_rep = document.getElementById('repeatPassword');
    const showPasswordCheckbox = document.getElementById('register_show_password');
    
    if (showPasswordCheckbox) {
        showPasswordCheckbox.addEventListener('change', function() {
            if (this.checked) {
                passwordInput_reg.type = 'text';
                passwordInput_reg_rep.type = 'text';
            } else {
                passwordInput_reg.type = 'password';
                passwordInput_reg_rep.type = 'password';
            }
        });
    }

    // Show/Hide password functionality for login form
    const passwordInput_log = document.getElementById('password');
    const showPasswordCheck = document.getElementById('show_login_password');
    
    if (showPasswordCheck) {
        showPasswordCheck.addEventListener('change', function() {
            if (this.checked) {
                passwordInput_log.type = 'text';
            } else {
                passwordInput_log.type = 'password';
            }
        });
    }
});