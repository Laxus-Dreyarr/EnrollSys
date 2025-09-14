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
            
            if (isValid) {
                // Simulate form submission
                const modal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
                modal.hide();
                
                // Show success message
                alert('Registration successful! Please check your EVSUmail for verification.');
                this.reset();
            }
        });
    }

            // Login Form Submission
            $('#loginForm').on('submit', function(e) {
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
                    url: 'exe/login.php',
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
                            // showSuccess('Access granted! Redirecting to dashboard...');
                            // submitBtn.classList.add('pulse');
                            // $('#successMessage').text('Login successful! Redirecting...');
                             //Redirect or close modal after success
                             //Redirect after delay
                             $('#successMessage').text('Access granted! Redirecting to dashboard...');
                             submitBtn.classList.add('pulse');
                            // Redirect or close modal after success
                            setTimeout(() => {
                                 window.location.href = 'dashboard.php';
                            }, 2000);
                            
                                    
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
    
    // // Form validation for login
    // const loginForm = document.getElementById('loginForm');
    
    // if (loginForm) {
    //     loginForm.addEventListener('submit', function(e) {
    //         e.preventDefault();
            
    //         let isValid = true;
    //         let email = $("#loginEmail").val()
    //         let password = $("#loginPassword").val()

    //         $.ajax({
    //             url: 'exe/login.php',
    //             method: 'POST',
    //             data: {email: email, password: password},
    //             success: function(x){
    //                 if(x == 1){
    //                     email.classList.add('is-invalid');
    //                     email.classList.add('shake');
    //                     setTimeout(() => email.classList.remove('shake'), 500);
    //                     submitBtn.disabled = false;
    //                     isValid = false;
    //                 }else if(x == 2){
    //                     password.classList.add('is-invalid');
    //                     password.classList.add('shake');
    //                     setTimeout(() => password.classList.remove('shake'), 500);
    //                     submitBtn.disabled = false;
    //                     isValid = false;
                        
    //                 }else if(x == 0){
    //                     alert('Your account has been suspended. Please contact the administrator for assistance.');
    //                     isValid = false;

    //                 }else if(x == 3){
    //                     alert('Login successful!');
    //                 }

    //                 // if (isValid) {
    //                 // const submitBtn = document.getElementById('submitBtn');

    //                 // showSuccess('Access granted! Redirecting to dashboard...');
    //                 // submitBtn.classList.add('pulse');
                                
    //                 // // Redirect after delay
    //                 // setTimeout(() => {
    //                 //     window.location.href = 'dashboard.html';
    //                 // }, 2000);
                    
    //                 // }
    //             }
    //         })
            
            
    //     });
    // }

    // function showSuccess(message) {
    //         successMessage.textContent = message;
    // }


    function showSuccess(message) {
        successMessage.textContent = message;
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
    
});// End of DOMContentLoaded event listener


    // Show/Hide password functionality for login form
    const passwordInput = document.getElementById('loginPassword');
    const showPasswordCheckbox = document.getElementById('showPassword');
    // Toggle password visibility when checkbox is clicked
    showPasswordCheckbox.addEventListener('change', function() {
        if (this.checked) {
            passwordInput.type = 'text';
        } else {
            passwordInput.type = 'password';
        }
    });

    // Show/Hide password functionality for forgot password form
    const passwordInput2 = document.getElementById('newPassword');
    const passwordInput3 = document.getElementById('confirmPassword');
    const showPasswordCheckbox2 = document.getElementById('showPassword2');
    // Toggle password visibility when checkbox is clicked
    showPasswordCheckbox2.addEventListener('change', function() {
        if (this.checked) {
            passwordInput2.type = 'text';
            passwordInput3.type = 'text';
        } else {
            passwordInput2.type = 'password';
            passwordInput3.type = 'password';
        }
    });

        // Forgot Password Functionality
    const forgotPasswordLink = document.querySelector('a[data-bs-target="#forgotPasswordModal"]');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const verificationForm = document.getElementById('verificationForm');
    const resendCodeLink = document.getElementById('resendCode');
    const countdownElement = document.getElementById('countdown');

        if (forgotPasswordForm) {
        // Email validation
        const resetEmail = document.getElementById('resetEmail');
        resetEmail.addEventListener('input', function() {
            if (!this.value.endsWith('@evsu.edu.ph')) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
        
        // Password validation
        const newPassword = document.getElementById('newPassword');
        newPassword.addEventListener('input', function() {
            const password = this.value;
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };
            
            // Update requirement indicators
            document.getElementById('reset-req-length').classList.toggle('valid', requirements.length);
            document.getElementById('reset-req-uppercase').classList.toggle('valid', requirements.uppercase);
            document.getElementById('reset-req-lowercase').classList.toggle('valid', requirements.lowercase);
            document.getElementById('reset-req-number').classList.toggle('valid', requirements.number);
            document.getElementById('reset-req-special').classList.toggle('valid', requirements.special);
        });
        
        // Confirm password validation
        const confirmPassword = document.getElementById('confirmPassword');
        confirmPassword.addEventListener('input', function() {
            if (this.value !== newPassword.value) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
        
        // Form submission
        forgotPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate all fields
            let isValid = true;
            
            // Check EVSUmail
            if (!resetEmail.value.endsWith('@evsu.edu.ph')) {
                resetEmail.classList.add('is-invalid');
                isValid = false;
            }
            
            // Check password match
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.classList.add('is-invalid');
                isValid = false;
            }
            
            // Check if password meets requirements
            const password = newPassword.value;
            const requirementsMet = (
                password.length >= 8 &&
                /[A-Z]/.test(password) &&
                /[a-z]/.test(password) &&
                /[0-9]/.test(password) &&
                /[!@#$%^&*(),.?":{}|<>]/.test(password)
            );
            
            if (!requirementsMet) {
                newPassword.classList.add('is-invalid');
                isValid = false;
            }
            
            if (isValid) {
                const sendCodeBtn = document.getElementById('sendCodeBtn');
                sendCodeBtn.disabled = true;
                sendCodeBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';
                
                // Simulate sending verification code
                setTimeout(() => {
                    // Update email in verification modal
                    document.getElementById('emailSentTo').textContent = resetEmail.value;
                    
                    // Switch to verification modal
                    const forgotModal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
                    forgotModal.hide();
                    
                    const verificationModal = new bootstrap.Modal(document.getElementById('verificationModal'));
                    verificationModal.show();
                    
                    // Start countdown for resend
                    startCountdown();
                    
                    // Reset button
                    sendCodeBtn.disabled = false;
                    sendCodeBtn.innerHTML = 'Send Verification Code';
                }, 1500);
            }
        });
    }


        // Verification code input handling
    const verificationInputs = document.querySelectorAll('.verification-code');
    if (verificationInputs.length) {
        verificationInputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                if (this.value.length === 1) {
                    if (index < verificationInputs.length - 1) {
                        verificationInputs[index + 1].focus();
                    }
                }
            });
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '') {
                    if (index > 0) {
                        verificationInputs[index - 1].focus();
                    }
                }
            });
        });
    }

        // Verification form submission
    if (verificationForm) {
        verificationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const verifyCodeBtn = document.getElementById('verifyCodeBtn');
            verifyCodeBtn.disabled = true;
            verifyCodeBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...';
            
            // Get the full code
            let code = '';
            verificationInputs.forEach(input => {
                code += input.value;
            });
            
            // Simulate verification
            setTimeout(() => {
                // In a real application, you would verify the code with your backend
                // For demo purposes, we'll assume any 6-digit code is valid
                if (code.length === 6) {
                    // Show success state
                    verificationForm.innerHTML = `
                        <div class="success-animation">
                            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                            </svg>
                            <div class="success-message">Password reset successfully!</div>
                        </div>
                    `;
                    
                    // Redirect to login after delay
                    setTimeout(() => {
                        const verificationModal = bootstrap.Modal.getInstance(document.getElementById('verificationModal'));
                        verificationModal.hide();
                        
                        const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                        loginModal.show();
                    }, 2000);
                } else {
                    verificationForm.classList.add('shake');
                    setTimeout(() => {
                        verificationForm.classList.remove('shake');
                    }, 500);
                    
                    verifyCodeBtn.disabled = false;
                    verifyCodeBtn.innerHTML = 'Verify Code';
                }
            }, 1500);
        });
    }


        // Resend code functionality
    if (resendCodeLink) {
        resendCodeLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Only allow resend if countdown is complete
            if (!resendCodeLink.classList.contains('disabled')) {
                startCountdown();
                
                // Simulate resending code
                const sendCodeBtn = document.getElementById('sendCodeBtn');
                sendCodeBtn.disabled = true;
                sendCodeBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Resending...';
                
                setTimeout(() => {
                    sendCodeBtn.disabled = false;
                    sendCodeBtn.innerHTML = 'Send Verification Code';
                    
                    // Show brief feedback
                    const originalHtml = resendCodeLink.innerHTML;
                    resendCodeLink.innerHTML = '<i class="fas fa-check"></i> Code sent!';
                    
                    setTimeout(() => {
                        resendCodeLink.innerHTML = originalHtml;
                    }, 2000);
                }, 1000);
            }
        });
    }


        // Countdown timer function
    function startCountdown() {
        let timeLeft = 60;
        resendCodeLink.classList.add('disabled');
        countdownElement.style.display = 'inline';
        
        const countdownInterval = setInterval(() => {
            countdownElement.textContent = `(${timeLeft}s)`;
            
            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                resendCodeLink.classList.remove('disabled');
                countdownElement.style.display = 'none';
            }
            
            timeLeft--;
        }, 1000);
    }

        // Clear form when modal is closed
    $('#forgotPasswordModal').on('hidden.bs.modal', function () {
        forgotPasswordForm.reset();
        $('.is-invalid').removeClass('is-invalid');
    });
    
    $('#verificationModal').on('hidden.bs.modal', function () {
        verificationInputs.forEach(input => {
            input.value = '';
        });
        verificationForm.reset();
    });