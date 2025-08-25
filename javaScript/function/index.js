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
                                 window.location.href = 'dashboard.html';
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
});


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