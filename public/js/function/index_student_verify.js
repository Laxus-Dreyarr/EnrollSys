const supabaseUrl = "https://dfvapjrkotprotpbpeju.supabase.co";
const supabaseAnonKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImRmdmFwanJrb3Rwcm90cGJwZWp1Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTcxNDg1OTMsImV4cCI6MjA3MjcyNDU5M30.Hou-GtB-P8qJ4fxXbC-VtyaCkDpf5Kr01DD9aSckhiU";

// Create Supabase client
const { createClient } = supabase;
const supabaseClient = createClient(supabaseUrl, supabaseAnonKey);

// Realtime update for submit enrollment
async function insertsupabase(){
    const data = {
        table_name: 'status',  // make sure these variables are defined
        operation: 'INSERT'
    };
    // Create AbortController for timeout (similar to PHP's 10s timeout)
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 10000);
            try {
            const response = await fetch(`${supabaseUrl}/rest/v1/enrollment_status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'apikey': supabaseAnonKey,
                    'Authorization': `Bearer ${supabaseAnonKey}`,
                    'Prefer': 'return=minimal'
                },
                    body: JSON.stringify(data),
                    signal: controller.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseData = await response.json();
            console.log(responseData);
            } catch (error) {
                if (error.name === 'AbortError') {
                    console.error('Request timed out');
                } else {
                    console.error('Error:', error);
                }
            }
}

document.addEventListener('DOMContentLoaded', function() {

    if (!sessionStorage.getItem('hasReloaded')) {
        sessionStorage.setItem('hasReloaded', 'true');
        setTimeout(() => {
            window.location.reload();
        }, 0);
    }

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

// Initialize and show the modal on page load
    const verificationModal = new bootstrap.Modal(document.getElementById('verificationModal'), {
        backdrop: 'static', 
        keyboard: false
    });
    verificationModal.show();
    
    // Prevent closing by clicking outside
    document.getElementById('verificationModal').addEventListener('click', function(event) {
        if (event.target === this) {
            event.stopPropagation();
        }
    });

        // Timer functionality with localStorage persistence
    const timerDuration = 1 * 120; // 5 minutes in seconds
    const countdownElement = document.getElementById('countdown');
    const timerProgress = document.getElementById('timerProgress');
    const redirectButton = document.getElementById('redirectButton');
    
    // Get stored timer end time or set a new one
    let timerEnd = localStorage.getItem('verificationTimerEnd');
    
    if (!timerEnd) {
        // Set timer end time if not exists
        timerEnd = Date.now() + (timerDuration * 1000);
        localStorage.setItem('verificationTimerEnd', timerEnd);
    } else {
        timerEnd = parseInt(timerEnd);
    }
    
    // Function to update timer display
    function updateTimer() {
        const now = Date.now();
        const timeLeft = Math.max(0, timerEnd - now);
        const secondsLeft = Math.floor(timeLeft / 1000);
        
        // Calculate minutes and seconds
        const minutes = Math.floor(secondsLeft / 60);
        const seconds = secondsLeft % 60;
        
        // Update display
        countdownElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        // Update progress bar
        const progressPercent = (secondsLeft / timerDuration) * 100;
        timerProgress.style.width = `${progressPercent}%`;
        
        // Change color based on time left
        if (secondsLeft < 60) {
            timerProgress.classList.remove('bg-warning');
            timerProgress.classList.add('bg-danger');
        } else if (secondsLeft < 120) {
            timerProgress.classList.remove('bg-success');
            timerProgress.classList.add('bg-warning');
        }
        
        // Redirect when timer completes
        if (secondsLeft === 0) {
            localStorage.removeItem('verificationTimerEnd');
            clearInterval(timerInterval);
            window.location.href = '/clear';
        }
    }


        // Initial timer update
    updateTimer();
    
    // Update timer every second
    const timerInterval = setInterval(updateTimer, 1000);
    
    // Redirect button functionality
    redirectButton.addEventListener('click', function() {
        localStorage.removeItem('verificationTimerEnd');
        clearInterval(timerInterval);
        window.location.href = '/clear';
    });
    
    
    // Single OTP input field functionality
    const verificationCodeInput = document.getElementById('verificationCodeInput');
    const verifyButton = document.getElementById('verifyButton');
    const statusMessage = document.getElementById('statusMessage');
    let wrongAttempts = 0; // Track wrong attempts globally

    // Auto-focus on the input field when modal opens
    verificationModal.show();
    setTimeout(() => {
        verificationCodeInput.focus();
    }, 500);



    // Function to validate and auto-verify OTP
    function validateAndAutoVerify() {
        const enteredCode = verificationCodeInput.value.trim();
        const actualCode = document.getElementById('code').value;
        
        // Only proceed if we have exactly 6 digits
        if (enteredCode.length !== 6 || !/^\d+$/.test(enteredCode)) {
            return false;
        }
        
        // If code matches, auto-verify
        if (enteredCode === actualCode) {
            performVerification();
            return true;
        }
        
        return false;
    }

    // Add input event listener for auto-verification
    verificationCodeInput.addEventListener('input', function(e) {
        // Remove any non-digit characters
        this.value = this.value.replace(/\D/g, '');
        
        // Auto-verify when 6 digits are entered
        if (this.value.length === 6) {
            validateAndAutoVerify();
        }
        
        // Clear any previous error messages when typing
        if (this.value.length > 0) {
            statusMessage.style.display = 'none';
        }
    });

    // Also check on paste event
    verificationCodeInput.addEventListener('paste', function(e) {
        // Get pasted text
        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
        
        // Only keep digits and limit to 6
        const digitsOnly = pastedText.replace(/\D/g, '').substring(0, 6);
        
        // Update input value
        this.value = digitsOnly;
        
        // Auto-verify if we have 6 digits
        if (digitsOnly.length === 6) {
            setTimeout(() => {
                validateAndAutoVerify();
            }, 100);
        }
        
        e.preventDefault();
    });


    // Manual verification button click
    verifyButton.addEventListener('click', function(e) {
        e.preventDefault();
        
        const enteredCode = verificationCodeInput.value.trim();
        
        // Validate form
        if (enteredCode.length !== 6) {
            showStatus('Please enter the complete 6-digit verification code', 'error');
            verificationCodeInput.focus();
            return;
        }
        
        if (!/^\d+$/.test(enteredCode)) {
            showStatus('Please enter numbers only', 'error');
            verificationCodeInput.focus();
            verificationCodeInput.select();
            return;
        }
        
        const actualCode = document.getElementById('code').value;
        
        // Verify the code
        if (enteredCode !== actualCode) {
            wrongAttempts++; // Increment wrong attempts

            if (wrongAttempts >= 3) {
                showStatus('Too many failed attempts. Refreshing page...', 'error');
                setTimeout(() => {
                    window.location.href = '/clear';
                }, 2000); // Optional: 2 second delay to show message
                return;
            }

            const remainingAttempts = 3 - wrongAttempts;
            showStatus(`Invalid verification code. ${remainingAttempts} attempt(s) remaining.`, 'error');
            verificationCodeInput.focus();
            verificationCodeInput.select();
            return;
        }
        
        // If code is correct, proceed with verification
        performVerification();
    });

    // Function to perform the actual verification
    function performVerification() {
        const nemail = document.getElementById('resetEmail').value;
        
        // Show loading state on verify button
        verifyButton.disabled = true;
        verifyButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...';
        
        // Also disable the input field
        verificationCodeInput.disabled = true;
        
        // Clear any error messages
        statusMessage.style.display = 'none';
        
        // Show success message
        showStatus('Verification successful! Saving account...', 'success');
        
        // Save account (your existing AJAX call)
        saveAccount(nemail);
    }

    // Function to show status messages
    function showStatus(message, type) {
        statusMessage.textContent = message;
        statusMessage.className = 'status-message ' + type;
        statusMessage.style.display = 'block';
        
        // Auto hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                statusMessage.style.display = 'none';
            }, 5000);
        }
    }

    // Function to update password via AJAX
    function saveAccount(nemail) {
        $.ajax({
            url: '/exe/student',
            method: 'POST',
            data: {
                email: nemail,
                action: 'confirm_account',
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response == '0'){
                    showStatus('Something went wrong!', 'error');
                    verifyButton.disabled = false;
                    verificationCodeInput.disabled = false;
                    verifyButton.innerHTML = '<i class="fas fa-check me-2"></i>Verify';
                }else if (response == '7') {
                    showStatus('Failed to save user record!', 'error');
                    verifyButton.disabled = false;
                    verificationCodeInput.disabled = false;
                    verifyButton.innerHTML = '<i class="fas fa-check me-2"></i>Verify';
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 2000);
                }else if (response == '9') {
                    insertsupabase();
                    showStatus('Registration Complete! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 3000);
                }
            },
            error: function() {
                showStatus('An error occurred. Please try again.', 'error');
                verifyButton.disabled = false;
                verificationCodeInput.disabled = false;
                verifyButton.innerHTML = '<i class="fas fa-check me-2"></i>Verify';
            }
        });
    }
    
    
});// End of DOMContentLoaded event listener