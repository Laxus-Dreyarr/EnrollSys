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


    let verificationTimer;
    let countdownTime = 600; // 10 minutes in seconds

    
    
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

            const registeremailInputError = document.getElementById('RloginEmailError');

            // Reset email error message
            registeremailInputError.style.display = 'none';
            registeremailInputError.textContent = '';
            
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
                registeremailInputError.textContent = 'Please input a valid evsumail!';
                registeremailInputError.style.display = 'block';
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
                const invalidFields = registerForm.querySelectorAll('.is-invalid');
                invalidFields.forEach(field => {
                    field.classList.add('shake');
                    setTimeout(() => field.classList.remove('shake'), 500);
                });
                return;
            }
            
            // Send verification code instead of registering directly
            sendVerificationCode({
                givenName: givenNameVal,
                lastName: lastNameVal,
                middleName: middleNameVal,
                email: emailVal,
                password: passwordVal,
                repeatPassword: repeatPasswordVal
            });
        });
    }

    function sendVerificationCode(formData) {
        const submitBtn = document.querySelector('#registerForm button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending Code...';
        submitBtn.disabled = true;

        const verificationData = new FormData();
        verificationData.append('action', 'send_verification');
        verificationData.append('givenName', formData.givenName);
        verificationData.append('lastName', formData.lastName);
        verificationData.append('middleName', formData.middleName);
        verificationData.append('email', formData.email);
        verificationData.append('password', formData.password);
        verificationData.append('repeatPassword', formData.repeatPassword);
        verificationData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        fetch('/exe/student', {
            method: 'POST',
            body: verificationData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close register modal and open verification modal
                // const registerModal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
                // registerModal.hide();
                
                // Set email in verification modal and show it
                // document.getElementById('verificationEmail').value = formData.email;
                // document.getElementById('verificationEmailText').textContent = 
                //     `We've sent a 6-digit verification code to ${formData.email}`;
                
                // Reset and show verification modal
                // document.getElementById('verificationCode').value = '';
                // document.getElementById('errorAlert').style.display = 'none';
                // document.getElementById('successAlert').style.display = 'none';

                window.location.href = '/register_student_account';
                
                // const verificationModal = new bootstrap.Modal(document.getElementById('verificationModal'));
                // verificationModal.show();
                
                // // Start countdown timer
                // startVerificationTimer();
                
            } else {
                alert('Failed to send verification code: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to send verification code. Please try again.');
        })
        .finally(() => {
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }

    function startVerificationTimer() {
        countdownTime = 600; // Reset to 10 minutes
        clearInterval(verificationTimer);
        
        const resendBtn = document.getElementById('resendCodeBtn');
        resendBtn.disabled = true;
        
        verificationTimer = setInterval(() => {
            countdownTime--;
            
            const minutes = Math.floor(countdownTime / 60);
            const seconds = countdownTime % 60;
            document.getElementById('countdown').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (countdownTime <= 0) {
                clearInterval(verificationTimer);
                resendBtn.disabled = false;
                document.getElementById('resendText').textContent = 'Resend Code';
                document.getElementById('countdown').textContent = 'Expired';
            }
        }, 1000);
    }


    function resendVerificationCode() {
        const resendBtn = document.getElementById('resendCodeBtn');
        const resendSpinner = document.getElementById('resendSpinner');
        const resendText = document.getElementById('resendText');
        
        resendBtn.disabled = true;
        resendSpinner.style.display = 'inline-block';
        resendText.textContent = 'Resending...';
        
        const email = document.getElementById('verificationEmail').value;
        const formData = new FormData();
        formData.append('action', 'send_verification');
        formData.append('email', email);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        fetch('/exe/student', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Restart timer
                startVerificationTimer();
                document.getElementById('errorAlert').style.display = 'none';
            } else {
                document.getElementById('errorMessage').textContent = data.message;
                document.getElementById('errorAlert').style.display = 'block';
                resendBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('errorMessage').textContent = 'Failed to resend code. Please try again.';
            document.getElementById('errorAlert').style.display = 'block';
            resendBtn.disabled = false;
        })
        .finally(() => {
            resendSpinner.style.display = 'none';
            resendText.textContent = 'Resend Code';
        });
    }



}

let emailTimeout;
// Check if email exists
function checkEmailExists(email) {

    const emailFieldErr = document.getElementById('RloginEmailError')

    // Clear previous timeout
    clearTimeout(emailTimeout);

    // Reset email error message
    emailFieldErr.style.display = 'none';
    emailFieldErr.textContent = '';

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
            const emailField = document.getElementById('registerEmail');RloginEmailError
            if (data.exists) {
                emailField.classList.add('is-invalid');
                emailFieldErr.textContent = ''+data.message;
                emailFieldErr.style.display = 'block';
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

// Show registration success
function showRegistrationSuccess(userData) {
    // You can show a success message or redirect to login
    alert(`Registration successful! Welcome ${userData.name}. Your student ID is: ${userData.student_id}`);
    
    // Reset the registration form
    document.getElementById('registerForm').reset();
    
    // Optionally redirect to login or show login modal
    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
    loginModal.show();
}

document.addEventListener('DOMContentLoaded', function() {

    // Verification form submission
    const verificationForm = document.getElementById('verificationForm');
    if (verificationForm) {
        verificationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            verifyCode();
        });
    }

    // Resend code button
    const resendBtn = document.getElementById('resendCodeBtn');
    if (resendBtn) {
        resendBtn.addEventListener('click', function() {
            if (!this.disabled) {
                resendVerificationCode();
            }
        });
    }

    // Auto-advance verification code input
    const verificationCodeInput = document.getElementById('verificationCode');
    if (verificationCodeInput) {
        verificationCodeInput.addEventListener('input', function() {
            if (this.value.length === 6) {
                // Auto-submit when 6 digits are entered
                verifyCode();
            }
        });
    }

    // Initialize password strength indicator
    initializePasswordStrengthIndicator();

    // Theme Toggle Functionality
    const themeToggleBtn = document.getElementById('themeToggle');
    const body = document.body;

    // Define theme colors
    const lightThemeColor = '#ffffff'; 
    const darkThemeColor = '#101126'; 
    
    // Check for saved theme preference or use preferred color scheme
    const savedTheme = localStorage.getItem('theme') || 
                      (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

    
    // Function to update theme meta tags
    function updateThemeMetaTags(theme) {
        const themeColor = theme === 'dark' ? darkThemeColor : lightThemeColor;
        
        // Update or create theme-color meta tag
        let themeColorMeta = document.querySelector('meta[name="theme-color"]');
        if (!themeColorMeta) {
            themeColorMeta = document.createElement('meta');
            themeColorMeta.name = 'theme-color';
            document.head.appendChild(themeColorMeta);
        }
        themeColorMeta.content = themeColor;
        
        // Update or create msapplication-navbutton-color meta tag
        let msNavButtonMeta = document.querySelector('meta[name="msapplication-navbutton-color"]');
        if (!msNavButtonMeta) {
            msNavButtonMeta = document.createElement('meta');
            msNavButtonMeta.name = 'msapplication-navbutton-color';
            document.head.appendChild(msNavButtonMeta);
        }
        msNavButtonMeta.content = themeColor;
        
        // For Safari iOS - you might want to keep this static or update it too
        let appleMeta = document.querySelector('meta[name="apple-mobile-web-app-status-bar-style"]');
        if (!appleMeta) {
            appleMeta = document.createElement('meta');
            appleMeta.name = 'apple-mobile-web-app-status-bar-style';
            document.head.appendChild(appleMeta);
        }
        appleMeta.content = theme === 'dark' ? 'black-translucent' : 'default';
    }
    
    // Apply the saved theme
    function applyTheme(theme) {
        if (theme === 'dark') {
            body.classList.add('dark-theme');
            themeToggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
        } else {
            body.classList.remove('dark-theme');
            themeToggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
        }
        
        // Update meta tags
        updateThemeMetaTags(theme);
        
        // Save to localStorage
        localStorage.setItem('theme', theme);
    }

   // Initialize theme on page load
    applyTheme(savedTheme);
    
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Theme toggle button click event
    themeToggleBtn.addEventListener('click', function() {
        const isDark = body.classList.contains('dark-theme');
        const newTheme = isDark ? 'light' : 'dark';
        applyTheme(newTheme);
    });


    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        // Only apply system theme if no user preference is saved
        if (!localStorage.getItem('theme')) {
            const newTheme = e.matches ? 'dark' : 'light';
            applyTheme(newTheme);
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

    const emailInputError = document.getElementById('loginEmailError');
    const passwordInputError = document.getElementById('loginPasswordError');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const email = emailInput.value.trim();
            const password = passwordInput.value;

            // Reset previous states
            const invalidFields = loginForm.querySelectorAll('.is-invalid');
            invalidFields.forEach(field => field.classList.remove('is-invalid'));

            // Reset email error message
            emailInputError.style.display = 'none';
            emailInputError.textContent = '';

            // Reset password error message
            passwordInputError.style.display = 'none';
            passwordInputError.textContent = '';
            
            // Show loading state
            loginBtn.disabled = true;
            const loginSpinner = document.getElementById('loginSpinner');
            if (loginSpinner) loginSpinner.style.display = 'inline-block';
            loginBtn.querySelector('span').textContent = 'Logging in...';
            
            // Create FormData
            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('email', email);
            formData.append('password', password);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            // Send login request
            fetch('/exe/student', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data === '3') {
                   showSuccessAlert();
                } else if (data === '2') {
                    passwordInputError.textContent = 'Incorrect password. Please try again.';
                    passwordInputError.style.display = 'block';
                    passwordInput.classList.add('is-invalid', 'shake');
                    setTimeout(() => passwordInput.classList.remove('shake'), 500);
                } else if (data === '1') {
                    emailInputError.textContent = 'Email not found. Please check your EVSUmail address.';
                    emailInputError.style.display = 'block';
                    emailInput.classList.add('is-invalid', 'shake');
                    setTimeout(() => emailInput.classList.remove('shake'), 500);
                } else if (data === '0') {
                    alert('Your account has been suspended. Please contact support.');
                } else if(data == 9) {
                    alert('An unexpected error occurred. Please try again.');
                } else if(data == 10) {
                    const successMessage = document.getElementById('successMessage');
                    if (successMessage) {
                        successMessage.textContent = 'Login Successfully!';
                        successMessage.style.display = 'block';
                    }
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

                // Reset email error message
                // emailInputError.style.display = 'none';
                // emailInputError.textContent = '';
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


    // Verify code and complete registration
    function verifyCode() {
        const code = document.getElementById('verificationCode').value.trim();
        const email = document.getElementById('verificationEmail').value;
        
        if (code.length !== 6) {
            document.getElementById('errorMessage').textContent = 'Please enter a 6-digit verification code.';
            document.getElementById('errorAlert').style.display = 'block';
            document.getElementById('successAlert').style.display = 'none';
            return;
        }
        
        const verifyBtn = document.getElementById('verifyBtn');
        const verifySpinner = document.getElementById('verifySpinner');
        const verifyBtnText = document.getElementById('verifyBtnText');
        
        verifyBtn.disabled = true;
        verifySpinner.style.display = 'inline-block';
        verifyBtnText.textContent = 'Verifying...';
        document.getElementById('errorAlert').style.display = 'none';
        document.getElementById('successAlert').style.display = 'none';
        
        const formData = new FormData();
        formData.append('action', 'verify_code');
        formData.append('email', email);
        formData.append('code', code);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        fetch('/exe/student', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // First check if response is ok
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            // Try to parse as text first since your backend returns plain text
            return response.text();
        })
        .then(responseText => {
            console.log('Raw response:', responseText); // For debugging
            
            // Check if response is exactly '5'
            if (responseText.trim() === '5') {
                // Success case
                document.getElementById('successAlert').style.display = 'block';
                document.getElementById('errorAlert').style.display = 'none';
                
                // Clear timer
                clearInterval(verificationTimer);
                
                // Show success and redirect
                setTimeout(() => {
                    const verificationModal = bootstrap.Modal.getInstance(document.getElementById('verificationModal'));
                    if (verificationModal) {
                        verificationModal.hide();
                    }
                    
                    // Show success message and redirect to login
                    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                    loginModal.show();
                    
                    // Optional: Show success message in login modal
                    const successMessage = document.getElementById('successMessage');
                    if (successMessage) {
                        successMessage.textContent = 'Registration successful! Please login with your credentials.';
                        successMessage.style.display = 'block';
                    }
                    
                }, 2000);
                
            } else {
                // Error case - try to parse as JSON for error message
                try {
                    const errorData = JSON.parse(responseText);
                    document.getElementById('errorMessage').textContent = errorData.message || 'Verification failed.';
                } catch {
                    // If not JSON, use the raw text as error message
                    document.getElementById('errorMessage').textContent = 'Verification failed. Please try again.';
                }
                document.getElementById('errorAlert').style.display = 'block';
                document.getElementById('successAlert').style.display = 'none';
                verifyBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('errorMessage').textContent = 'Network error. Please check your connection and try again.';
            document.getElementById('errorAlert').style.display = 'block';
            document.getElementById('successAlert').style.display = 'none';
            verifyBtn.disabled = false;
        })
        .finally(() => {
            verifySpinner.style.display = 'none';
            verifyBtnText.textContent = 'Verify & Register';
        });
    }

    function showSuccessAndRefresh() {
    const modalBody = document.querySelector('#verificationModal .modal-body');
    
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="success-animation mb-4">
                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
            
            <h4 class="text-success mb-3">
                <i class="fas fa-check-circle me-2"></i>
                Registration Successful!
            </h4>
            
            <p class="text-muted mb-4">
                Your account has been created successfully. 
                You will be redirected to the home page shortly.
            </p>
            
            <div class="countdown-container">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <small class="text-muted">Refreshing page in <span id="countdownTimer">3</span> seconds...</small>
            </div>
        </div>
    `;

    // Countdown and refresh
    let countdown = 3;
    const countdownElement = document.getElementById('countdownTimer');
    
    const countdownInterval = setInterval(() => {
        countdown--;
        if (countdownElement) {
            countdownElement.textContent = countdown;
        }
        
        if (countdown <= 0) {
            clearInterval(countdownInterval);
            location.reload(); // Refresh the page
        }
    }, 1000);
}



});