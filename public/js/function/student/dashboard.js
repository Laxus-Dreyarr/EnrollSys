document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar on mobile
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebar = document.querySelector('.sidebar');
        const searchInput = document.querySelector('.search-input');
        const searchBtn = document.querySelector('.search-btn');
        const searchBar = document.querySelector('.search-bar');

        // Search functionality
        function performSearch() {
            const searchTerm = searchInput.value.trim();
            
            if (searchTerm) {
                // Add loading state
                searchBar.classList.add('loading');
                
                // Simulate search (replace with actual search logic)
                setTimeout(() => {
                    searchBar.classList.remove('loading');
                    alert(`Searching for: ${searchTerm}`);
                    // Here you would typically filter content or make an API call
                }, 1000);
            }
        }

        // Search on button click
        searchBtn.addEventListener('click', performSearch);

        // Search on Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });

        // Clear search on escape
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                searchInput.value = '';
                searchInput.blur();
            }
        });
        
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth < 992) {
                const isClickInsideSidebar = sidebar.contains(event.target);
                const isClickInsideToggle = sidebarToggle.contains(event.target);
                
                if (!isClickInsideSidebar && !isClickInsideToggle && sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                }
            }
        });
        
        // Navigation functionality
        const menuItems = document.querySelectorAll('.menu-item');
        const contentSections = document.querySelectorAll('.content-section');
        const pageTitle = document.querySelector('.page-title');
        
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                if (this.id === 'logout-btn') {
                    // Logout functionality
                    if (confirm('Are you sure you want to logout?')) {
                        alert('Logging out...');
                        // In a real app, this would redirect to logout URL
                    }
                    return;
                }
                
                // Remove active class from all menu items
                menuItems.forEach(i => i.classList.remove('active'));
                
                // Add active class to clicked menu item
                this.classList.add('active');
                
                // Hide all content sections
                contentSections.forEach(section => section.classList.remove('active'));
                
                // Show the selected content section
                const sectionId = this.getAttribute('data-section') + '-section';
                document.getElementById(sectionId).classList.add('active');
                
                // Update page title
                const sectionName = this.querySelector('span').textContent;
                pageTitle.textContent = sectionName;
            });
        });
        
        // Enhanced Dark Mode Toggle with Smooth Transitions
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        
        // Check for saved theme preference or use preferred color scheme
        const savedTheme = localStorage.getItem('theme') || 
                        (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        
        // Apply the saved theme
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            darkModeToggle.querySelector('input').checked = true;
        }
        
        darkModeToggle.addEventListener('change', function() {
            // Add smooth transition class
            document.body.classList.add('theme-transition');
            
            if (this.querySelector('input').checked) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
            }
            
            // Remove transition class after animation
            setTimeout(() => {
                document.body.classList.remove('theme-transition');
            }, 300);
        });
        
        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (!localStorage.getItem('theme')) {
                if (e.matches) {
                    document.body.classList.add('dark-mode');
                    darkModeToggle.querySelector('input').checked = true;
                } else {
                    document.body.classList.remove('dark-mode');
                    darkModeToggle.querySelector('input').checked = false;
                }
            }
        });
        
        // Animate cards on scroll
        const animateOnScroll = function() {
            const cards = document.querySelectorAll('.stat-card, .course-card');
            
            cards.forEach(card => {
                const cardPosition = card.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.2;
                
                if (cardPosition < screenPosition) {
                    card.classList.add('animate-card');
                }
            });
        };
        
        window.addEventListener('scroll', animateOnScroll);
        animateOnScroll(); // Run once on page load

        // Profile edit functionality
        const editProfileBtn = document.getElementById('edit-profile-btn');
        const cancelEditBtn = document.getElementById('cancel-edit');
        const profileForm = document.getElementById('profile-form');
        const formActions = document.getElementById('form-actions');
        const editableFields = ['firstName', 'lastName', 'email', 'phone', 'address'];
        
        let originalValues = {};
        
        // Store original values
        editableFields.forEach(field => {
            originalValues[field] = document.getElementById(field).value;
        });
        
        // Edit profile button click
        editProfileBtn.addEventListener('click', function() {
            // Enable editing for all fields
            editableFields.forEach(field => {
                const input = document.getElementById(field);
                input.readOnly = false;
                input.style.background = 'white';
                input.style.color = '#374151';
                
                // Update dark mode styles if active
                if (document.body.classList.contains('dark-mode')) {
                    input.style.background = '#1e293b';
                    input.style.color = '#e2e8f0';
                }
            });
            
            // Show form actions
            formActions.style.display = 'flex';
            
            // Change edit button to editing state
            editProfileBtn.innerHTML = '<i class="fas fa-pencil-alt"></i> Editing...';
            editProfileBtn.style.background = '#fbbf24';
            editProfileBtn.style.borderColor = '#fbbf24';
            editProfileBtn.style.color = '#78350f';
        });
        
        // Cancel edit button click
        cancelEditBtn.addEventListener('click', function() {
            // Restore original values
            editableFields.forEach(field => {
                const input = document.getElementById(field);
                input.value = originalValues[field];
                input.readOnly = true;
                input.style.background = '#f8fafc';
                input.style.color = '#64748b';
                
                // Update dark mode styles if active
                if (document.body.classList.contains('dark-mode')) {
                    input.style.background = 'rgba(255, 255, 255, 0.05)';
                    input.style.color = '#94a3b8';
                }
            });
            
            // Hide form actions
            formActions.style.display = 'none';
            
            // Reset edit button
            editProfileBtn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
            editProfileBtn.style.background = 'transparent';
            editProfileBtn.style.borderColor = 'var(--primary-color)';
            editProfileBtn.style.color = 'var(--primary-color)';
        });
        
        // Form submission
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Here you would typically send the data to your server
            // For now, we'll just show a success message
            
            // Make fields read-only again
            editableFields.forEach(field => {
                const input = document.getElementById(field);
                input.readOnly = true;
                input.style.background = '#f8fafc';
                input.style.color = '#64748b';
                
                // Update dark mode styles if active
                if (document.body.classList.contains('dark-mode')) {
                    input.style.background = 'rgba(255, 255, 255, 0.05)';
                    input.style.color = '#94a3b8';
                }
                
                // Update original values
                originalValues[field] = input.value;
            });
            
            // Hide form actions
            formActions.style.display = 'none';
            
            // Reset edit button
            editProfileBtn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
            editProfileBtn.style.background = 'transparent';
            editProfileBtn.style.borderColor = 'var(--primary-color)';
            editProfileBtn.style.color = 'var(--primary-color)';
            
            // Show success message
            showNotification('Profile updated successfully!', 'success');
        });

        // Profile picture upload functionality
        const avatarContainer = document.getElementById('avatar-container');
        const avatarInput = document.getElementById('avatar-input');
        const profileAvatar = document.getElementById('profile-avatar');
        const avatarOverlay = document.getElementById('avatar-overlay');
        const changePhotoBtn = document.getElementById('change-photo-btn');
        const uploadLoading = document.getElementById('upload-loading');
        const progressFill = document.getElementById('progress-fill');
        const progressText = document.getElementById('progress-text');
        
        // Add success checkmark element
        const successCheckmark = document.createElement('div');
        successCheckmark.className = 'upload-success';
        successCheckmark.innerHTML = '<i class="fas fa-check"></i>';
        avatarContainer.appendChild(successCheckmark);
        
        // Add error message element
        const errorMessage = document.createElement('div');
        errorMessage.className = 'avatar-error';
        avatarContainer.appendChild(errorMessage);
        
        // Click events for avatar container and button
        avatarContainer.addEventListener('click', function() {
            avatarInput.click();
        });
        
        changePhotoBtn.addEventListener('click', function() {
            avatarInput.click();
        });
        
        // File input change event
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // Validate file
                if (!validateImageFile(file)) {
                    return;
                }
                
                // Show preview
                showImagePreview(file);
                
                // Start upload process
                uploadImageToServer(file);
            }
        });
        
        // File validation
        function validateImageFile(file) {
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            // Check file type
            if (!validTypes.includes(file.type)) {
                showError('Please select a valid image (JPG, PNG, WEBP)');
                return false;
            }
            
            // Check file size
            if (file.size > maxSize) {
                showError('Image must be less than 5MB');
                return false;
            }
            
            return true;
        }
        
        // Show image preview
        function showImagePreview(file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                profileAvatar.src = e.target.result;
                profileAvatar.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    profileAvatar.style.transform = 'scale(1)';
                }, 200);
            };
            
            reader.readAsDataURL(file);
        }
        
        // Upload image to server
        function uploadImageToServer(file) {
            // Disable button and show loading state
            changePhotoBtn.disabled = true;
            changePhotoBtn.classList.add('loading');
            avatarContainer.classList.add('uploading');
            
            // Show loading animation
            uploadLoading.classList.add('active');
            
            // Simulate upload progress
            simulateUploadProgress(file);
        }
        
        // Simulate upload progress (replace with actual AJAX upload)
        function simulateUploadProgress(file) {
            let progress = 0;
            const totalSteps = 100;
            const stepTime = 30; // ms per step
            
            const progressInterval = setInterval(() => {
                progress += 1;
                
                // Update progress bar
                progressFill.style.width = progress + '%';
                progressText.textContent = progress + '%';
                
                // Simulate different speeds for different parts of upload
                if (progress === 20) {
                    progressText.textContent = 'Processing image...';
                } else if (progress === 60) {
                    progressText.textContent = 'Uploading to server...';
                } else if (progress === 85) {
                    progressText.textContent = 'Saving to database...';
                }
                
                // Complete upload
                if (progress >= totalSteps) {
                    clearInterval(progressInterval);
                    completeUpload(file);
                }
            }, stepTime);
        }
        
        // Complete upload process
        function completeUpload(file) {
            // Show success animation
            successCheckmark.classList.add('active');
            
            // Update progress text
            progressText.textContent = 'Upload Complete!';
            
            // Hide loading after delay
            setTimeout(() => {
                uploadLoading.classList.remove('active');
                successCheckmark.classList.remove('active');
                avatarContainer.classList.remove('uploading');
                
                // Re-enable button
                changePhotoBtn.disabled = false;
                changePhotoBtn.classList.remove('loading');
                
                // In a real application, you would submit the form or send AJAX here
                // For now, we'll simulate a successful upload
                simulateServerUpload(file);
                
            }, 1500);
        }
        
        // Simulate server upload (replace with actual AJAX call)
        function simulateServerUpload(file) {
            // Create FormData for actual upload
            const formData = new FormData();
            formData.append('avatar', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            // This is where you would make the actual AJAX call
            /*
            fetch('/api/upload-profile-picture', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update avatar with new image URL
                    profileAvatar.src = data.imageUrl + '?t=' + new Date().getTime();
                    showNotification('Profile picture updated successfully!', 'success');
                } else {
                    showError('Upload failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                showError('Upload failed. Please try again.');
            });
            */
            
            // For demo purposes, we'll just show a success message
            setTimeout(() => {
                showNotification('Profile picture updated successfully!', 'success');
            }, 500);
        }
        
        // Show error message
        function showError(message) {
            errorMessage.textContent = message;
            errorMessage.classList.add('active');
            
            setTimeout(() => {
                errorMessage.classList.remove('active');
            }, 3000);
        }
        
        // Show notification
        function showNotification(message, type = 'success') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `upload-notification ${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            
            // Add styles for notification
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? 'var(--success-color)' : 'var(--danger-color)'};
                color: white;
                padding: 16px 20px;
                border-radius: 12px;
                box-shadow: var(--shadow-hover);
                z-index: 10000;
                display: flex;
                align-items: center;
                gap: 10px;
                font-weight: 500;
                animation: slideInRight 0.3s ease;
                max-width: 300px;
            `;
            
            document.body.appendChild(notification);
            
            // Remove notification after delay
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
        
        // Add CSS animations for notifications
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(100%);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            @keyframes slideOutRight {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }
                to {
                    opacity: 0;
                    transform: translateX(100%);
                }
            }
            
            .upload-notification {
                backdrop-filter: blur(10px);
            }
        `;
        document.head.appendChild(style);
        
        // Drag and drop functionality
        avatarContainer.addEventListener('dragover', function(e) {
            e.preventDefault();
            avatarContainer.style.borderColor = 'var(--primary-color)';
            avatarContainer.style.background = 'rgba(67, 97, 238, 0.1)';
        });
        
        avatarContainer.addEventListener('dragleave', function(e) {
            e.preventDefault();
            avatarContainer.style.borderColor = '';
            avatarContainer.style.background = '';
        });
        
        avatarContainer.addEventListener('drop', function(e) {
            e.preventDefault();
            avatarContainer.style.borderColor = '';
            avatarContainer.style.background = '';
            
            const file = e.dataTransfer.files[0];
            if (file) {
                avatarInput.files = e.dataTransfer.files;
                const event = new Event('change', { bubbles: true });
                avatarInput.dispatchEvent(event);
            }
        });

        // Initialize tooltips for better UX
        initializeTooltips();
        
        function initializeTooltips() {
            const elementsWithTooltip = document.querySelectorAll('[data-tooltip]');
            
            elementsWithTooltip.forEach(element => {
                element.addEventListener('mouseenter', showTooltip);
                element.addEventListener('mouseleave', hideTooltip);
            });
            
            function showTooltip(e) {
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = this.getAttribute('data-tooltip');
                tooltip.style.cssText = `
                    position: absolute;
                    background: rgba(0, 0, 0, 0.8);
                    color: white;
                    padding: 8px 12px;
                    border-radius: 6px;
                    font-size: 0.8rem;
                    z-index: 10000;
                    white-space: nowrap;
                    pointer-events: none;
                `;
                
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
                
                this.tooltip = tooltip;
            }
            
            function hideTooltip() {
                if (this.tooltip) {
                    this.tooltip.remove();
                    this.tooltip = null;
                }
            }
        }
        
        // Enhanced responsive behavior
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                sidebar.classList.remove('active');
            }
        });

        const studentInfoModal = document.getElementById('studentInfoModal');
        const studentInfoForm = document.getElementById('studentInfoForm');
        
        // Only initialize if modal exists and is active
        if (studentInfoModal && studentInfoModal.classList.contains('active')) {
            // Prevent closing modal by clicking outside
            studentInfoModal.addEventListener('click', function(e) {
                if (e.target === studentInfoModal) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });

            // Form validation
            studentInfoForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (validateForm()) {
                    submitForm();
                }
            });

            // Real-time validation
            const inputs = studentInfoForm.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                
                input.addEventListener('input', function() {
                    clearFieldError(this);
                });
            });
        }

        function validateField(field) {
            const errorElement = document.getElementById(field.id + '_error');
            
            if (!field.value.trim()) {
                showFieldError(field, errorElement, 'This field is required');
                return false;
            }
            
            if (field.id === 'school_id' && field.value.trim().length < 3) {
                showFieldError(field, errorElement, 'School ID must be at least 3 characters');
                return false;
            }
            
            clearFieldError(field);
            return true;
        }

        function showFieldError(field, errorElement, message) {
            field.style.borderColor = 'var(--danger-color)';
            errorElement.textContent = message;
            errorElement.classList.add('active');
        }

        function clearFieldError(field) {
            field.style.borderColor = '';
            const errorElement = document.getElementById(field.id + '_error');
            errorElement.classList.remove('active');
        }

        function validateForm() {
            let isValid = true;
            const fields = ['school_id', 'year_level', 'student_type'];
            
            fields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (!validateField(field)) {
                    isValid = false;
                }
            });
            
            return isValid;
        }

        function submitForm() {
            const submitBtn = studentInfoForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner"></i> Saving...';
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            
            // Get form data
            const formData = new FormData();
            formData.append('action', 'complete_student_info');
            formData.append('school_id', document.getElementById('school_id').value);
            formData.append('year_level', document.getElementById('year_level').value);
            formData.append('student_type', document.getElementById('student_type').value);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            // Send request
            fetch('/exe/student', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showSuccessMessage();
                } else {
                    // Handle specific field errors
                    if (data.message.includes('School ID')) {
                        const schoolIdInput = document.getElementById('school_id');
                        const schoolIdInputError = document.getElementById('school_id_error');
                        showFieldError(schoolIdInput, schoolIdInputError, data.message);
                    } else if (data.message.includes('year level')) {
                        const yearLevelInput = document.getElementById('year_level');
                        const yearLevelInputError = document.getElementById('year_level_error');
                        showFieldError(yearLevelInput, yearLevelInputError, data.message);
                    } else if (data.message.includes('student type')) {
                        const studentTypeInput = document.getElementById('student_type');
                        const studentTypeInputError = document.getElementById('student_type_error');
                        showFieldError(studentTypeInput, studentTypeInputError, data.message);
                    } else {
                        // Show generic error
                        Swal.fire({
                            title: 'Failed',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: 'Close',
                            confirmButtonColor: '#070808ff',
                            background: '#1a1a2e',
                            color: '#ffffff',
                            backdrop: 'rgba(0,0,0,0.7)',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showClass: {
                                popup: 'animate__animated animate__fadeInDown'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOutUp'
                            }
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error. Please check your connection and try again.');
            })
            .finally(() => {
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            });
        }

        function showSuccessMessage() {
            const modalContainer = document.querySelector('.modal-container');
            const originalContent = modalContainer.innerHTML;
            
            modalContainer.innerHTML = `
                <div class="success-animation">
                    <svg viewBox="0 0 52 52" fill="none">
                        <circle cx="26" cy="26" r="25" fill="#10b981" stroke="#10b981" stroke-width="2"/>
                        <path d="M14 27l7 7 17-17" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <h4>Information Saved Successfully!</h4>
                    <p>Your student information has been updated. The page will refresh shortly.</p>
                </div>
            `;
            
            // Refresh page after success
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        }



    // Enrollment functionality
    const enrollNowBtn = document.getElementById('d-stat-card-enroll');
    const enrollmentModal = document.getElementById('enrollmentModal');
    const closeEnrollmentModal = document.getElementById('closeEnrollmentModal');
    const cancelEnrollment = document.getElementById('cancelEnrollment');
    const subjectsList = document.getElementById('subjectsList');
    const submitEnrollment = document.getElementById('submitEnrollment');
    const totalUnitsCounter = document.getElementById('totalUnitsCounter');
    const enrollmentYearLevel = document.getElementById('enrollmentYearLevel');
    const enrollmentSemester = document.getElementById('enrollmentSemester');
    const enrollmentStudentType = document.getElementById('enrollmentStudentType');

    let selectedSubjects = new Set();
    let totalUnits = 0;
    let isRegular = true;

    // Open enrollment modal
    enrollNowBtn.addEventListener('click', function() {
        enrollmentModal.classList.add('active');
        loadEnrollmentSubjects();
    });

    // Close enrollment modal
    closeEnrollmentModal.addEventListener('click', closeEnrollment);
    cancelEnrollment.addEventListener('click', closeEnrollment);

    function closeEnrollment() {
        enrollmentModal.classList.remove('active');
        selectedSubjects.clear();
        totalUnits = 0;
        updateUnitsCounter();
        submitEnrollment.disabled = true;
    }

    // Load enrollment subjects
    function loadEnrollmentSubjects() {
        subjectsList.innerHTML = `
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <span>Loading subjects...</span>
            </div>
        `;

        fetch('/student/enrollment/subjects', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySubjects(data);
            } else {
                showEnrollmentError('Failed to load subjects');
            }
        })
        .catch(error => {
            console.error('Error loading subjects:', error);
            showEnrollmentError('Network error. Please try again.');
        });
    }

    // Display subjects in the modal
    function displaySubjects(data) {
        enrollmentYearLevel.textContent = data.year_level;
        enrollmentSemester.textContent = data.semester;
        enrollmentStudentType.textContent = data.is_regular ? 'Regular' : 'Irregular';
        isRegular = data.is_regular;
        
        if (data.subjects.length === 0) {
            subjectsList.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-book-open"></i>
                    <span>No subjects available for your year level and semester.</span>
                </div>
            `;
            return;
        }
        
        let subjectsHTML = '';
        
        data.subjects.forEach(subject => {
            const hasPrerequisites = subject.prerequisites && subject.prerequisites.length > 0;
            const prerequisitesMet = hasPrerequisites ? 
                subject.prerequisites.every(prereq => data.completed_subjects.includes(prereq.id)) : 
                true;
            
            const isSelectable = isRegular || (!hasPrerequisites || prerequisitesMet);
            const isAutoSelected = isRegular;
            
            if (isAutoSelected) {
                selectedSubjects.add(subject.id);
                totalUnits += parseInt(subject.units);
            }
            
            subjectsHTML += `
                <div class="subject-item ${!isSelectable ? 'disabled' : ''}">
                    <div class="subject-checkbox">
                        <input 
                            type="checkbox" 
                            id="subject_${subject.id}" 
                            value="${subject.id}" 
                            ${isAutoSelected ? 'checked' : ''}
                            ${!isSelectable ? 'disabled' : ''}
                            onchange="toggleSubject(${subject.id}, ${subject.units}, ${!isSelectable})"
                        >
                    </div>
                    <div class="subject-info">
                        <div class="subject-header">
                            <span class="subject-code">${subject.code}</span>
                            <span class="subject-units">${subject.units} units</span>
                        </div>
                        <div class="subject-name">${subject.name}</div>
                        <div class="subject-description">${subject.description || 'No description available'}</div>
                        
                        ${subject.schedules && subject.schedules.length > 0 ? `
                            <div class="subject-schedule">
                                ${subject.schedules.map(schedule => `
                                    <span class="schedule-badge">
                                        ${schedule.day} ${schedule.start_time} - ${schedule.end_time}
                                    </span>
                                `).join('')}
                            </div>
                        ` : ''}
                        
                        ${hasPrerequisites && !prerequisitesMet ? `
                            <div class="prerequisite-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Requires prerequisites: ${subject.prerequisites.map(p => p.code).join(', ')}
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });
        
        subjectsList.innerHTML = subjectsHTML;
        updateUnitsCounter();
        submitEnrollment.disabled = selectedSubjects.size === 0;
    }

    // Toggle subject selection
    function toggleSubject(subjectId, units, isDisabled) {
        if (isDisabled) return;
        
        const checkbox = document.getElementById(`subject_${subjectId}`);
        
        if (checkbox.checked) {
            selectedSubjects.add(subjectId);
            totalUnits += parseInt(units);
        } else {
            selectedSubjects.delete(subjectId);
            totalUnits -= parseInt(units);
        }
        
        updateUnitsCounter();
        submitEnrollment.disabled = selectedSubjects.size === 0;
    }

    // Update units counter
    function updateUnitsCounter() {
        totalUnitsCounter.textContent = totalUnits;
    }

    // Show enrollment error
    function showEnrollmentError(message) {
        subjectsList.innerHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-circle"></i>
                <span>${message}</span>
            </div>
        `;
    }

    // Submit enrollment
    submitEnrollment.addEventListener('click', function() {
        if (selectedSubjects.size === 0) return;
        
        const submitBtn = submitEnrollment;
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
        
        fetch('/student/enrollment/enroll', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                subjects: Array.from(selectedSubjects)
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                closeEnrollment();
                
                // Refresh the page or update UI as needed
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showNotification(data.message, 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Enrollment error:', error);
            showNotification('Enrollment failed. Please try again.', 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Enhanced notification function (update your existing one)
    function showNotification(message, type = 'success') {
        // Your existing notification code, but ensure it handles different types
        const notification = document.createElement('div');
        notification.className = `upload-notification ${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? 'var(--success-color)' : 'var(--danger-color)'};
            color: white;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: var(--shadow-hover);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            animation: slideInRight 0.3s ease;
            max-width: 300px;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    });//End of DOMContentLoaded