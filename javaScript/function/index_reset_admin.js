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
    const timerDuration = 1 * 60; // 5 minutes in seconds
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
            window.location.href = 'index-admin.php';
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
        window.location.href = 'index-admin.php';
    });
    
    // Verification code input automation
    const digitInputs = document.querySelectorAll('.verification-digit');
    
    digitInputs.forEach((input, index) => {
        // Move to next input on digit entry
        input.addEventListener('input', function() {
            if (this.value.length === 1 && index < digitInputs.length - 1) {
                digitInputs[index + 1].focus();
            }
        });
        
        // Move to previous input on backspace
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && this.value === '' && index > 0) {
                digitInputs[index - 1].focus();
            }
        });
    });
    
    // Auto-focus first digit input
    if (digitInputs.length > 0) {
        digitInputs[0].focus();
    }
    
});// End of DOMContentLoaded event listener