// Instructor Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // Navigation functionality
    const menuItems = document.querySelectorAll('.menu-item');
    const contentSections = document.querySelectorAll('.content-section');
    const pageTitle = document.querySelector('.page-title');
    
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            if (this.id === 'logout-btn') {
                if (confirm('Are you sure you want to logout?')) {
                    // Logout logic here - redirect to logout URL
                    window.location.href = '/logout';
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
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.classList.add('active');
                
                // Update page title
                const sectionName = this.querySelector('span').textContent;
                if (pageTitle) {
                    pageTitle.textContent = sectionName;
                }
            }
        });
    });
    
    // Quick Actions
    const quickActionCards = document.querySelectorAll('.quick-action-card');
    quickActionCards.forEach(card => {
        card.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            handleQuickAction(action);
        });
    });
    
    function handleQuickAction(action) {
        switch(action) {
            case 'create-assignment':
                // Navigate to assignments section and trigger creation
                document.querySelector('[data-section="assignments"]').click();
                setTimeout(() => {
                    const createBtn = document.getElementById('createAssignmentBtn');
                    if (createBtn) createBtn.click();
                }, 300);
                break;
            case 'manage-grades':
                document.querySelector('[data-section="grades"]').click();
                break;
            case 'view-students':
                document.querySelector('[data-section="students"]').click();
                break;
            case 'upload-materials':
                showNotification('Upload materials feature coming soon!', 'info');
                break;
        }
    }
    
    // Create Assignment Button
    const createAssignmentBtn = document.getElementById('createAssignmentBtn');
    if (createAssignmentBtn) {
        createAssignmentBtn.addEventListener('click', function() {
            showAssignmentCreationModal();
        });
    }
    
    function showAssignmentCreationModal() {
        // In a real implementation, this would show a modal
        // For now, we'll show a notification and simulate the process
        showNotification('Opening assignment creation form...', 'info');
        
        // Simulate modal opening
        setTimeout(() => {
            const modalHTML = `
                <div class="modal-overlay active" id="assignmentModal">
                    <div class="modal-container" style="max-width: 600px;">
                        <div class="modal-header">
                            <h3>Create New Assignment</h3>
                            <button class="close-modal" onclick="closeAssignmentModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form class="modal-form">
                            <div class="form-group">
                                <label for="assignmentTitle" class="form-label">Assignment Title</label>
                                <input type="text" id="assignmentTitle" class="form-control" placeholder="Enter assignment title" required>
                            </div>
                            <div class="form-group">
                                <label for="assignmentCourse" class="form-label">Course</label>
                                <select id="assignmentCourse" class="form-control" required>
                                    <option value="">Select Course</option>
                                    <option value="IT 373">IT 373 - Software Engineering</option>
                                    <option value="CS 301">CS 301 - Data Structures</option>
                                    <option value="IT 401">IT 401 - Web Development</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="assignmentDueDate" class="form-label">Due Date</label>
                                <input type="datetime-local" id="assignmentDueDate" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="assignmentDescription" class="form-label">Description</label>
                                <textarea id="assignmentDescription" class="form-control" rows="4" placeholder="Enter assignment description"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="assignmentPoints" class="form-label">Total Points</label>
                                <input type="number" id="assignmentPoints" class="form-control" placeholder="100" min="1" required>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-cancel" onclick="closeAssignmentModal()">Cancel</button>
                                <button type="submit" class="btn-primary">Create Assignment</button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            
            // Add modal to page
            const modalContainer = document.createElement('div');
            modalContainer.innerHTML = modalHTML;
            document.body.appendChild(modalContainer);
            
            // Handle form submission
            const form = modalContainer.querySelector('form');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                createNewAssignment(this);
            });
        }, 500);
    }
    
    // Close assignment modal function (needs to be global)
    window.closeAssignmentModal = function() {
        const modal = document.getElementById('assignmentModal');
        if (modal) {
            modal.remove();
        }
    };
    
    function createNewAssignment(form) {
        const formData = new FormData(form);
        const assignmentData = {
            title: form.querySelector('#assignmentTitle').value,
            course: form.querySelector('#assignmentCourse').value,
            dueDate: form.querySelector('#assignmentDueDate').value,
            description: form.querySelector('#assignmentDescription').value,
            points: form.querySelector('#assignmentPoints').value
        };
        
        // Show loading state
        const submitBtn = form.querySelector('.btn-primary');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
        submitBtn.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            showNotification('Assignment created successfully!', 'success');
            closeAssignmentModal();
            
            // In a real app, you would refresh the assignments list
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 1500);
    }
    
    // Profile Edit Functionality
    const editProfileBtn = document.getElementById('edit-profile-btn');
    const cancelEditBtn = document.getElementById('cancel-edit');
    const profileForm = document.getElementById('profile-form');
    const formActions = document.getElementById('form-actions');
    
    if (editProfileBtn && profileForm) {
        let originalValues = {};
        
        // Store original values
        const editableFields = profileForm.querySelectorAll('input, textarea');
        editableFields.forEach(field => {
            originalValues[field.id] = field.value;
        });
        
        editProfileBtn.addEventListener('click', function() {
            enableProfileEditing();
        });
        
        if (cancelEditBtn) {
            cancelEditBtn.addEventListener('click', function() {
                disableProfileEditing(originalValues);
            });
        }
        
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveProfileChanges();
        });
    }
    
    function enableProfileEditing() {
        const editableFields = document.querySelectorAll('#profile-form input, #profile-form textarea');
        editableFields.forEach(field => {
            field.readOnly = false;
            field.style.background = 'white';
            field.style.color = '#374151';
            
            if (document.body.classList.contains('dark-mode')) {
                field.style.background = '#1e293b';
                field.style.color = '#e2e8f0';
            }
        });
        
        if (formActions) {
            formActions.style.display = 'flex';
        }
        if (editProfileBtn) {
            editProfileBtn.style.display = 'none';
        }
    }
    
    function disableProfileEditing(originalValues) {
        const editableFields = document.querySelectorAll('#profile-form input, #profile-form textarea');
        editableFields.forEach(field => {
            if (originalValues && originalValues[field.id]) {
                field.value = originalValues[field.id];
            }
            field.readOnly = true;
            field.style.background = '#f8fafc';
            field.style.color = '#64748b';
            
            if (document.body.classList.contains('dark-mode')) {
                field.style.background = 'rgba(255, 255, 255, 0.05)';
                field.style.color = '#94a3b8';
            }
        });
        
        if (formActions) {
            formActions.style.display = 'none';
        }
        if (editProfileBtn) {
            editProfileBtn.style.display = 'flex';
        }
    }
    
    function saveProfileChanges() {
        // Show loading state
        const submitBtn = document.querySelector('#profile-form .btn-primary');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.disabled = true;
        
        // Simulate API call to save profile
        setTimeout(() => {
            showNotification('Profile updated successfully!', 'success');
            disableProfileEditing();
            
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 1500);
    }
    
    // Grade Management
    const gradeInputs = document.querySelectorAll('.grade-input');
    gradeInputs.forEach(input => {
        input.addEventListener('change', function() {
            const row = this.closest('tr');
            const saveBtn = row.querySelector('.btn-primary');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save*';
            }
        });
    });
    
    // Save grade buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-primary') && e.target.closest('tr')) {
            const row = e.target.closest('tr');
            const inputs = row.querySelectorAll('.grade-input');
            const grades = {
                assignments: parseFloat(inputs[0].value) || 0,
                midterm: parseFloat(inputs[1].value) || 0,
                final: parseFloat(inputs[2].value) || 0
            };
            
            // Validate grades
            if (Object.values(grades).some(grade => grade < 0 || grade > 100)) {
                showNotification('Please enter valid grades between 0 and 100', 'error');
                return;
            }
            
            e.target.disabled = true;
            e.target.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving';
            
            // Simulate saving to server
            setTimeout(() => {
                e.target.textContent = 'Saved';
                showNotification('Grade updated successfully!', 'success');
                
                // Recalculate overall grade
                calculateOverallGrade(row, grades);
            }, 1000);
        }
    });
    
    function calculateOverallGrade(row, grades) {
        const overall = (grades.assignments * 0.3) + (grades.midterm * 0.3) + (grades.final * 0.4);
        const overallCell = row.querySelector('td:nth-child(6)');
        if (overallCell) {
            overallCell.textContent = Math.round(overall) + '%';
            
            // Add color coding based on grade
            if (overall >= 90) {
                overallCell.style.color = '#10b981';
                overallCell.style.fontWeight = '600';
            } else if (overall >= 80) {
                overallCell.style.color = '#3b82f6';
                overallCell.style.fontWeight = '600';
            } else if (overall >= 70) {
                overallCell.style.color = '#f59e0b';
                overallCell.style.fontWeight = '600';
            } else {
                overallCell.style.color = '#ef4444';
                overallCell.style.fontWeight = '600';
            }
        }
    }
    
    // Attendance buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-sm') && e.target.textContent.includes('Attendance')) {
            const scheduleItem = e.target.closest('.schedule-item');
            const course = scheduleItem.querySelector('.schedule-course').textContent;
            showAttendanceModal(course);
        }
    });
    
    function showAttendanceModal(course) {
        showNotification(`Taking attendance for ${course}`, 'info');
        
        // Simulate attendance modal
        setTimeout(() => {
            const modalHTML = `
                <div class="modal-overlay active" id="attendanceModal">
                    <div class="modal-container" style="max-width: 800px;">
                        <div class="modal-header">
                            <h3>Take Attendance - ${course}</h3>
                            <button class="close-modal" onclick="closeAttendanceModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-content">
                            <div class="attendance-list">
                                <div class="attendance-item">
                                    <span class="student-name">John Smith (2023001)</span>
                                    <label class="attendance-toggle">
                                        <input type="checkbox" checked>
                                        <span class="slider"></span>
                                        <span class="toggle-label">Present</span>
                                    </label>
                                </div>
                                <div class="attendance-item">
                                    <span class="student-name">Sarah Johnson (2023002)</span>
                                    <label class="attendance-toggle">
                                        <input type="checkbox" checked>
                                        <span class="slider"></span>
                                        <span class="toggle-label">Present</span>
                                    </label>
                                </div>
                                <div class="attendance-item">
                                    <span class="student-name">Michael Brown (2023003)</span>
                                    <label class="attendance-toggle">
                                        <input type="checkbox">
                                        <span class="slider"></span>
                                        <span class="toggle-label">Absent</span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-cancel" onclick="closeAttendanceModal()">Cancel</button>
                                <button type="button" class="btn-primary" onclick="submitAttendance()">Submit Attendance</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            const modalContainer = document.createElement('div');
            modalContainer.innerHTML = modalHTML;
            document.body.appendChild(modalContainer);
        }, 500);
    }
    
    // Close attendance modal function (needs to be global)
    window.closeAttendanceModal = function() {
        const modal = document.getElementById('attendanceModal');
        if (modal) {
            modal.remove();
        }
    };
    
    // Submit attendance function (needs to be global)
    window.submitAttendance = function() {
        const submitBtn = document.querySelector('#attendanceModal .btn-primary');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
        
        setTimeout(() => {
            showNotification('Attendance submitted successfully!', 'success');
            closeAttendanceModal();
        }, 1500);
    };
    
    // Search functionality
    const searchInput = document.querySelector('.search-input');
    const searchBtn = document.querySelector('.search-btn');
    
    function performSearch() {
        const searchTerm = searchInput.value.trim();
        if (searchTerm) {
            // Show loading state
            if (searchBtn) {
                searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            }
            
            // Simulate search
            setTimeout(() => {
                showNotification(`Searching for: ${searchTerm}`, 'info');
                if (searchBtn) {
                    searchBtn.innerHTML = '<i class="fas fa-search"></i>';
                }
            }, 1000);
        }
    }
    
    if (searchBtn) {
        searchBtn.addEventListener('click', performSearch);
    }
    
    if (searchInput) {
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
    }
    
    // Dark mode toggle
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    if (darkModeToggle) {
        const savedTheme = localStorage.getItem('theme') || 
                          (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            darkModeToggle.querySelector('input').checked = true;
        }
        
        darkModeToggle.addEventListener('change', function() {
            document.body.classList.add('theme-transition');
            
            if (this.querySelector('input').checked) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
            }
            
            setTimeout(() => {
                document.body.classList.remove('theme-transition');
            }, 300);
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth < 992 && sidebar) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickInsideToggle = sidebarToggle && sidebarToggle.contains(event.target);
            
            if (!isClickInsideSidebar && !isClickInsideToggle && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        }
    });
    
    // Responsive behavior
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992 && sidebar) {
            sidebar.classList.remove('active');
        }
    });
    
    // Notification function
    function showNotification(message, type = 'info') {
        // Use SweetAlert2 if available, otherwise use browser alert
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
            
            Toast.fire({
                icon: type,
                title: message
            });
        } else {
            // Fallback to custom notification
            const notification = document.createElement('div');
            notification.className = `custom-notification ${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas fa-${getNotificationIcon(type)}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            // Add styles if not already added
            if (!document.querySelector('#notification-styles')) {
                const styles = document.createElement('style');
                styles.id = 'notification-styles';
                styles.textContent = `
                    .custom-notification {
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: white;
                        padding: 16px 20px;
                        border-radius: 12px;
                        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
                        z-index: 10000;
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        max-width: 300px;
                        animation: slideInRight 0.3s ease;
                    }
                    .custom-notification.success {
                        background: #10b981;
                        color: white;
                    }
                    .custom-notification.error {
                        background: #ef4444;
                        color: white;
                    }
                    .custom-notification.info {
                        background: #3b82f6;
                        color: white;
                    }
                    .custom-notification.warning {
                        background: #f59e0b;
                        color: white;
                    }
                    @keyframes slideInRight {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                `;
                document.head.appendChild(styles);
            }
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
    }
    
    function getNotificationIcon(type) {
        switch(type) {
            case 'success': return 'check-circle';
            case 'error': return 'exclamation-circle';
            case 'warning': return 'exclamation-triangle';
            default: return 'info-circle';
        }
    }
    
    // Initialize any additional components
    initializeCourseManagement();
    initializeStudentFilters();
});

// Additional initialization functions
function initializeCourseManagement() {
    // Add any course management specific initialization here
    console.log('Course management initialized');
}

function initializeStudentFilters() {
    const studentSearch = document.querySelector('.student-filters input');
    const studentSelect = document.querySelector('.student-filters select');
    
    if (studentSearch) {
        studentSearch.addEventListener('input', function() {
            filterStudents();
        });
    }
    
    if (studentSelect) {
        studentSelect.addEventListener('change', function() {
            filterStudents();
        });
    }
    
    function filterStudents() {
        // In a real implementation, this would filter the student table
        console.log('Filtering students...');
    }
}

// Make functions available globally if needed
window.instructorDashboard = {
    showNotification: showNotification,
    // Add other functions as needed
};