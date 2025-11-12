// Enhanced Dark Mode and Theme Management with Smooth Transitions
function initializeDarkMode() {
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    let themeColorOptions = document.querySelectorAll('.color-option');
    
    // Load saved theme preferences
    const savedTheme = localStorage.getItem('theme') || 
                      (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    const savedColor = localStorage.getItem('theme-color') || '#4361ee';
    
    console.log('Initializing theme with:', { savedTheme, savedColor });
    
    // Apply saved theme with smooth transition
    setTimeout(() => {
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            if (darkModeToggle) darkModeToggle.checked = true;
        }
        
        // Apply saved color
        applyThemeColor(savedColor);
        setActiveColorOption(savedColor);
    }, 100);
    
    // Dark mode toggle event
    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', function() {
            if (this.checked) {
                enableDarkModeSmoothly();
            } else {
                disableDarkModeSmoothly();
            }
        });
    }
    
    // Theme color selection events - with better error handling
    function setupThemeColorListeners() {
        themeColorOptions = document.querySelectorAll('.color-option');
        console.log('Setting up listeners for', themeColorOptions.length, 'color options');
        
        themeColorOptions.forEach(option => {
            // Remove existing listeners to prevent duplicates
            option.replaceWith(option.cloneNode(true));
        });
        
        // Re-query after clone
        themeColorOptions = document.querySelectorAll('.color-option');
        
        themeColorOptions.forEach(option => {
            option.addEventListener('click', function() {
                const color = this.getAttribute('data-color');
                console.log('Color option clicked:', color);
                applyThemeColorSmoothly(color);
                setActiveColorOption(color);
                localStorage.setItem('theme-color', color);
            });
        });
    }
    
    // Initial setup
    setupThemeColorListeners();
    
    // Re-setup listeners when settings section is shown (in case of dynamic loading)
    document.addEventListener('click', function(e) {
        if (e.target.closest('[data-section="settings"]')) {
            setTimeout(setupThemeColorListeners, 100);
        }
    });
}

// Make sure the theme color is applied when switching between light/dark mode
function enableDarkModeSmoothly() {
    document.body.classList.add('theme-transition');
    document.body.classList.add('dark-mode');
    localStorage.setItem('theme', 'dark');
    
    // Re-apply theme color after mode change
    const currentColor = localStorage.getItem('theme-color') || '#4361ee';
    setTimeout(() => {
        applyThemeColor(currentColor);
    }, 300);
    
    setTimeout(() => {
        document.body.classList.remove('theme-transition');
    }, 600);
}

function disableDarkModeSmoothly() {
    document.body.classList.add('theme-transition');
    document.body.classList.remove('dark-mode');
    localStorage.setItem('theme', 'light');
    
    // Re-apply theme color after mode change
    const currentColor = localStorage.getItem('theme-color') || '#4361ee';
    setTimeout(() => {
        applyThemeColor(currentColor);
    }, 300);
    
    setTimeout(() => {
        document.body.classList.remove('theme-transition');
    }, 600);
}

function applyThemeColorSmoothly(color) {
    const root = document.documentElement;
    
    // Add transition for color changes
    root.style.setProperty('--transition', 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)');
    
    // Update CSS variables based on the selected color
    switch(color) {
        case '#2c5530': // Instructor Green
            root.style.setProperty('--theme-primary', '#2c5530');
            root.style.setProperty('--theme-primary-dark', '#244328');
            root.style.setProperty('--theme-secondary', '#4a7c59');
            root.style.setProperty('--theme-accent', '#6b8e4e');
            root.style.setProperty('--primary-color', '#2c5530');
            root.style.setProperty('--primary-dark', '#244328');
            root.style.setProperty('--secondary-color', '#4a7c59');
            root.style.setProperty('--accent-color', '#6b8e4e');
            root.style.setProperty('--instructor-primary', '#2c5530');
            root.style.setProperty('--instructor-secondary', '#4a7c59');
            root.style.setProperty('--instructor-accent', '#6b8e4e');
            break;
        case '#8b5cf6': // Purple
            root.style.setProperty('--theme-primary', '#8b5cf6');
            root.style.setProperty('--theme-primary-dark', '#7c3aed');
            root.style.setProperty('--theme-secondary', '#a78bfa');
            root.style.setProperty('--theme-accent', '#c4b5fd');
            root.style.setProperty('--primary-color', '#8b5cf6');
            root.style.setProperty('--primary-dark', '#7c3aed');
            root.style.setProperty('--secondary-color', '#a78bfa');
            root.style.setProperty('--accent-color', '#c4b5fd');
            root.style.setProperty('--instructor-primary', '#8b5cf6');
            root.style.setProperty('--instructor-secondary', '#a78bfa');
            root.style.setProperty('--instructor-accent', '#c4b5fd');
            break;
        case '#ef4444': // Red
            root.style.setProperty('--theme-primary', '#ef4444');
            root.style.setProperty('--theme-primary-dark', '#dc2626');
            root.style.setProperty('--theme-secondary', '#f87171');
            root.style.setProperty('--theme-accent', '#fca5a5');
            root.style.setProperty('--primary-color', '#ef4444');
            root.style.setProperty('--primary-dark', '#dc2626');
            root.style.setProperty('--secondary-color', '#f87171');
            root.style.setProperty('--accent-color', '#fca5a5');
            root.style.setProperty('--instructor-primary', '#ef4444');
            root.style.setProperty('--instructor-secondary', '#f87171');
            root.style.setProperty('--instructor-accent', '#fca5a5');
            break;
        case '#f59e0b': // Amber
            root.style.setProperty('--theme-primary', '#f59e0b');
            root.style.setProperty('--theme-primary-dark', '#d97706');
            root.style.setProperty('--theme-secondary', '#fbbf24');
            root.style.setProperty('--theme-accent', '#fcd34d');
            root.style.setProperty('--primary-color', '#f59e0b');
            root.style.setProperty('--primary-dark', '#d97706');
            root.style.setProperty('--secondary-color', '#fbbf24');
            root.style.setProperty('--accent-color', '#fcd34d');
            root.style.setProperty('--instructor-primary', '#f59e0b');
            root.style.setProperty('--instructor-secondary', '#fbbf24');
            root.style.setProperty('--instructor-accent', '#fcd34d');
            break;
        case '#800000': // Maroon
            root.style.setProperty('--theme-primary', '#800000');
            root.style.setProperty('--theme-primary-dark', '#660000');
            root.style.setProperty('--theme-secondary', '#a52a2a');
            root.style.setProperty('--theme-accent', '#cd5c5c');
            root.style.setProperty('--primary-color', '#800000');
            root.style.setProperty('--primary-dark', '#660000');
            root.style.setProperty('--secondary-color', '#a52a2a');
            root.style.setProperty('--accent-color', '#cd5c5c');
            root.style.setProperty('--instructor-primary', '#800000');
            root.style.setProperty('--instructor-secondary', '#a52a2a');
            root.style.setProperty('--instructor-accent', '#cd5c5c');
            break;
        default: // Default Blue
            root.style.setProperty('--theme-primary', '#4361ee');
            root.style.setProperty('--theme-primary-dark', '#3a56d4');
            root.style.setProperty('--theme-secondary', '#3f37c9');
            root.style.setProperty('--theme-accent', '#4895ef');
            root.style.setProperty('--primary-color', '#4361ee');
            root.style.setProperty('--primary-dark', '#3a56d4');
            root.style.setProperty('--secondary-color', '#3f37c9');
            root.style.setProperty('--accent-color', '#4895ef');
            root.style.setProperty('--instructor-primary', '#4361ee');
            root.style.setProperty('--instructor-secondary', '#3f37c9');
            root.style.setProperty('--instructor-accent', '#4895ef');
    }
    
    // Show notification
    showNotification('Theme color updated to ' + getColorName(color), 'info');
    
    // Reset transition after color change
    setTimeout(() => {
        root.style.setProperty('--transition', 'all 0.3s ease');
    }, 400);
}

function applyThemeColor(color) {
    const root = document.documentElement;
    
    // Update CSS variables based on the selected color
    switch(color) {
        case '#2c5530': // Instructor Green
            root.style.setProperty('--theme-primary', '#2c5530');
            root.style.setProperty('--theme-primary-dark', '#244328');
            root.style.setProperty('--theme-secondary', '#4a7c59');
            root.style.setProperty('--theme-accent', '#6b8e4e');
            root.style.setProperty('--primary-color', '#2c5530');
            root.style.setProperty('--primary-dark', '#244328');
            root.style.setProperty('--secondary-color', '#4a7c59');
            root.style.setProperty('--accent-color', '#6b8e4e');
            root.style.setProperty('--instructor-primary', '#2c5530');
            root.style.setProperty('--instructor-secondary', '#4a7c59');
            root.style.setProperty('--instructor-accent', '#6b8e4e');
            break;
        case '#8b5cf6': // Purple
            root.style.setProperty('--theme-primary', '#8b5cf6');
            root.style.setProperty('--theme-primary-dark', '#7c3aed');
            root.style.setProperty('--theme-secondary', '#a78bfa');
            root.style.setProperty('--theme-accent', '#c4b5fd');
            root.style.setProperty('--primary-color', '#8b5cf6');
            root.style.setProperty('--primary-dark', '#7c3aed');
            root.style.setProperty('--secondary-color', '#a78bfa');
            root.style.setProperty('--accent-color', '#c4b5fd');
            root.style.setProperty('--instructor-primary', '#8b5cf6');
            root.style.setProperty('--instructor-secondary', '#a78bfa');
            root.style.setProperty('--instructor-accent', '#c4b5fd');
            break;
        case '#ef4444': // Red
            root.style.setProperty('--theme-primary', '#ef4444');
            root.style.setProperty('--theme-primary-dark', '#dc2626');
            root.style.setProperty('--theme-secondary', '#f87171');
            root.style.setProperty('--theme-accent', '#fca5a5');
            root.style.setProperty('--primary-color', '#ef4444');
            root.style.setProperty('--primary-dark', '#dc2626');
            root.style.setProperty('--secondary-color', '#f87171');
            root.style.setProperty('--accent-color', '#fca5a5');
            root.style.setProperty('--instructor-primary', '#ef4444');
            root.style.setProperty('--instructor-secondary', '#f87171');
            root.style.setProperty('--instructor-accent', '#fca5a5');
            break;
        case '#f59e0b': // Amber
            root.style.setProperty('--theme-primary', '#f59e0b');
            root.style.setProperty('--theme-primary-dark', '#d97706');
            root.style.setProperty('--theme-secondary', '#fbbf24');
            root.style.setProperty('--theme-accent', '#fcd34d');
            root.style.setProperty('--primary-color', '#f59e0b');
            root.style.setProperty('--primary-dark', '#d97706');
            root.style.setProperty('--secondary-color', '#fbbf24');
            root.style.setProperty('--accent-color', '#fcd34d');
            root.style.setProperty('--instructor-primary', '#f59e0b');
            root.style.setProperty('--instructor-secondary', '#fbbf24');
            root.style.setProperty('--instructor-accent', '#fcd34d');
            break;
        case '#800000': // Maroon
            root.style.setProperty('--theme-primary', '#800000');
            root.style.setProperty('--theme-primary-dark', '#660000');
            root.style.setProperty('--theme-secondary', '#a52a2a');
            root.style.setProperty('--theme-accent', '#cd5c5c');
            root.style.setProperty('--primary-color', '#800000');
            root.style.setProperty('--primary-dark', '#660000');
            root.style.setProperty('--secondary-color', '#a52a2a');
            root.style.setProperty('--accent-color', '#cd5c5c');
            root.style.setProperty('--instructor-primary', '#800000');
            root.style.setProperty('--instructor-secondary', '#a52a2a');
            root.style.setProperty('--instructor-accent', '#cd5c5c');
            break;
        default: // Default Blue
            root.style.setProperty('--theme-primary', '#4361ee');
            root.style.setProperty('--theme-primary-dark', '#3a56d4');
            root.style.setProperty('--theme-secondary', '#3f37c9');
            root.style.setProperty('--theme-accent', '#4895ef');
            root.style.setProperty('--primary-color', '#4361ee');
            root.style.setProperty('--primary-dark', '#3a56d4');
            root.style.setProperty('--secondary-color', '#3f37c9');
            root.style.setProperty('--accent-color', '#4895ef');
            root.style.setProperty('--instructor-primary', '#4361ee');
            root.style.setProperty('--instructor-secondary', '#3f37c9');
            root.style.setProperty('--instructor-accent', '#4895ef');
    }
}

// Helper function to get color name
function getColorName(color) {
    const colorMap = {
        '#4361ee': 'Blue',
        '#2c5530': 'Green', 
        '#8b5cf6': 'Purple',
        '#ef4444': 'Red',
        '#f59e0b': 'Amber',
        '#800000': 'Maroon'
    };
    return colorMap[color] || 'Custom';
}

function setActiveColorOption(color) {
    const colorOptions = document.querySelectorAll('.color-option');
    colorOptions.forEach(option => {
        if (option.getAttribute('data-color') === color) {
            option.classList.add('active');
        } else {
            option.classList.remove('active');
        }
    });
}

// System preference listener with smooth transitions
function watchSystemTheme() {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    
    mediaQuery.addEventListener('change', (e) => {
        // Only auto-switch if user hasn't manually set a preference
        if (!localStorage.getItem('theme')) {
            const darkModeToggle = document.getElementById('dark-mode-toggle');
            if (e.matches) {
                enableDarkModeSmoothly();
                if (darkModeToggle) darkModeToggle.checked = true;
            } else {
                disableDarkModeSmoothly();
                if (darkModeToggle) darkModeToggle.checked = false;
            }
        }
    });
}

// Add keyboard shortcut for dark mode (Ctrl/Cmd + D)
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
        e.preventDefault();
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        if (darkModeToggle) {
            darkModeToggle.checked = !darkModeToggle.checked;
            if (darkModeToggle.checked) {
                enableDarkModeSmoothly();
            } else {
                disableDarkModeSmoothly();
            }
        }
    }
});

// Global notification function
function showNotification(message, type = 'info') {
    // Use SweetAlert2 if available
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
        // Fallback to browser alert
        alert(`[${type.toUpperCase()}] ${message}`);
    }
}

// Input Grades functionality - UPDATED VERSION WITH HORIZONTAL MODAL
function initializeInputGrades() {
    console.log('Initializing Input Grades section...');

    const filterForm = document.getElementById('grade-filter-form');
    const resetBtn = document.getElementById('reset-filters');
    const studentsGrid = document.getElementById('students-grid');
    const loadingContainer = document.getElementById('loading-container');
    const noStudentsContainer = document.getElementById('no-students-container');
    
    // Grade modal elements
    const gradeModal = document.getElementById('grade-modal');
    const closeGradeModal = document.getElementById('close-grade-modal');
    const cancelGrade = document.getElementById('cancel-grade');
    const gradeForm = document.getElementById('grade-form');

    // Check if required elements exist
    if (!studentsGrid) {
        console.error('Students grid element not found');
        return;
    }
    
    // Load students on initialization
    loadUngradedStudents();
    
    // Filter form submission
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            loadUngradedStudents();
        });
    }
    
    // Reset filters
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (filterForm) {
                filterForm.reset();
                loadUngradedStudents();
            }
        });
    }
    
    // Close modal events
    if (closeGradeModal) {
        closeGradeModal.addEventListener('click', closeModal);
    }
    
    if (cancelGrade) {
        cancelGrade.addEventListener('click', closeModal);
    }
    
    // Enhanced mobile form submission
    if (gradeForm) {
        gradeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Enhanced mobile loading state
            const submitBtn = gradeForm.querySelector('.btn-primary');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;
            
            // Add visual feedback for mobile
            if (window.innerWidth <= 768) {
                submitBtn.style.transform = 'scale(0.98)';
            }
            
            const formData = new FormData(gradeForm);
            const gradeData = {
                student_id: parseInt(formData.get('student_id')),
                subject_id: parseInt(formData.get('subject_id')),
                grade: formData.get('grade')
            };
            
            console.log('Saving grade data:', gradeData);
            
            fetch(window.laravelRoutes.saveGrade, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(gradeData)
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    return response.json().then(errorData => {
                        throw new Error(errorData.message || 'Network response was not ok');
                    }).catch(() => {
                        throw new Error('Network response was not ok');
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Save grade response:', data);
                if (data.success) {
                    showNotification('Grade saved successfully!', 'success');
                    closeModal();
                    loadUngradedStudents();
                } else {
                    showNotification(data.message || 'Error saving grade', 'error');
                }
            })
            .catch(error => {
                console.error('Error saving grade:', error);
                showNotification('Error saving grade: ' + error.message, 'error');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                if (window.innerWidth <= 768) {
                    submitBtn.style.transform = 'scale(1)';
                }
            });
        });
    }
    
    // Close modal when clicking outside
    if (gradeModal) {
        gradeModal.addEventListener('click', function(e) {
            if (e.target === gradeModal) {
                closeModal();
            }
        });
    }
    
    function loadUngradedStudents() {
        console.log('Loading ungraded students...');
        showLoading(true);
        
        const formData = new FormData();
        const yearLevel = document.getElementById('year_level')?.value;
        const search = document.getElementById('search')?.value;
        const sortBy = document.getElementById('sort_by')?.value;
        
        const requestData = {
            year_level: yearLevel || '',
            search: search || '',
            sort_by: sortBy || 'lastname'
        };
        
        console.log('Request data:', requestData);
        
        fetch(window.laravelRoutes.ungradedStudents, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Students data received:', data);
            displayStudents(data.students || []);
            showLoading(false);
        })
        .catch(error => {
            console.error('Error loading students:', error);
            showLoading(false);
            showNotification('Error loading students: ' + error.message, 'error');
            // Display empty state on error
            displayStudents([]);
        });
    }
    
    function displayStudents(students) {
        studentsGrid.innerHTML = '';
        
        if (!students || students.length === 0) {
            noStudentsContainer.style.display = 'block';
            studentsGrid.style.display = 'none';
            return;
        }
        
        noStudentsContainer.style.display = 'none';
        studentsGrid.style.display = 'grid';
        
        students.forEach(student => {
            const studentCard = createStudentCard(student);
            studentsGrid.appendChild(studentCard);
        });
    }
    
    // Enhanced student card creation for mobile
    function createStudentCard(student) {
        const card = document.createElement('div');
        card.className = 'student-card';
        
        const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(student.firstname + ' ' + student.lastname)}&background=4361ee&color=fff&size=60`;
        
        // Create subjects preview (limited to 2 subjects initially)
        let subjectsPreviewHTML = '';
        let allSubjectsHTML = '';
        
        if (student.subjects && student.subjects.length > 0) {
            // Preview subjects (show first 2)
            const previewSubjects = student.subjects.slice(0, 2);
            previewSubjects.forEach(subject => {
                subjectsPreviewHTML += `
                    <div class="subject-preview-item">
                        <span class="subject-code">${subject.subject_code || 'N/A'}</span>
                        <span class="subject-name">${subject.subject_name || 'N/A'}</span>
                    </div>
                `;
            });
            
            // All subjects for expandable section
            student.subjects.forEach(subject => {
                allSubjectsHTML += `
                    <div class="subject-item" data-subject-id="${subject.subject_id}">
                        <div class="subject-header">
                            <div class="subject-code">${subject.subject_code || 'N/A'}</div>
                            <div class="subject-name">${subject.subject_name || 'N/A'}</div>
                        </div>
                        <div class="subject-meta">
                            <span>${subject.units || '0'} units</span>
                            <span>${subject.subject_semester || 'N/A'}</span>
                        </div>
                        <button class="btn-primary btn-grade btn-sm" 
                                data-student-id="${student.student_db_id}"
                                data-student-user-id="${student.student_user_id}"
                                data-firstname="${student.firstname}"
                                data-middlename="${student.middlename || ''}"
                                data-lastname="${student.lastname}"
                                data-id-no="${student.id_no || 'N/A'}"
                                data-year-level="${student.year_level}"
                                data-subject-id="${subject.subject_id}"
                                data-subject-code="${subject.subject_code}"
                                data-subject-name="${subject.subject_name}"
                                data-subject-units="${subject.units}"
                                data-subject-year="${subject.subject_year}"
                                data-subject-semester="${subject.subject_semester}">
                            <i class="fas fa-pen"></i>
                            Input Grade
                        </button>
                    </div>
                `;
            });
            
            // Show more indicator if there are more than 2 subjects
            if (student.subjects.length > 2) {
                subjectsPreviewHTML += `
                    <div class="more-subjects-indicator">
                        +${student.subjects.length - 2} more subjects
                    </div>
                `;
            }
        } else {
            subjectsPreviewHTML = '<div class="no-subjects">No subjects found</div>';
            allSubjectsHTML = '<div class="no-subjects">No subjects found</div>';
        }
        
        card.innerHTML = `
            <div class="student-header">
                <img src="${avatarUrl}" alt="Student Avatar" class="student-avatar-small">
                <div class="student-info">
                    <h4 class="student-name">${student.firstname} ${student.middlename || ''} ${student.lastname}</h4>
                    <p class="student-id">ID: ${student.id_no || 'N/A'}</p>
                    <span class="student-year">${student.year_level || 'N/A'}</span>
                </div>
                <div class="student-actions-toggle">
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </div>
            </div>
            
            <div class="subjects-preview">
                ${subjectsPreviewHTML}
            </div>
            
            <div class="student-subjects-expandable">
                <div class="expandable-content">
                    ${allSubjectsHTML}
                </div>
            </div>
        `;
        
        // Enhanced mobile event listeners
        const toggleBtn = card.querySelector('.student-actions-toggle');
        const expandableSection = card.querySelector('.student-subjects-expandable');
        
        if (toggleBtn && expandableSection) {
            const handleToggle = function(e) {
                e.stopPropagation();
                expandableSection.classList.toggle('expanded');
                const icon = this.querySelector('.toggle-icon');
                icon.classList.toggle('fa-chevron-down');
                icon.classList.toggle('fa-chevron-up');
                
                // Smooth scroll to expanded content on mobile
                if (window.innerWidth <= 768 && expandableSection.classList.contains('expanded')) {
                    setTimeout(() => {
                        expandableSection.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'nearest'
                        });
                    }, 300);
                }
            };
            
            toggleBtn.addEventListener('click', handleToggle);
            // Add touch support for mobile
            toggleBtn.addEventListener('touchend', function(e) {
                e.preventDefault();
                handleToggle.call(this, e);
            });
        }
        
        // Enhanced grade buttons with better mobile support
        const gradeButtons = card.querySelectorAll('.btn-grade');
        gradeButtons.forEach(button => {
            const handleGradeClick = function(e) {
                e.stopPropagation();
                openGradeModal(this.dataset, student.subjects);
            };
            
            button.addEventListener('click', handleGradeClick);
            // Add touch support
            button.addEventListener('touchend', function(e) {
                e.preventDefault();
                handleGradeClick.call(this, e);
            });
        });
        
        return card;
    }
    
    // Enhanced mobile modal handling
    function openGradeModal(data, allSubjects = []) {
        console.log('Opening grade modal with data:', data);
        
        const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(data.firstname + ' ' + data.lastname)}&background=4361ee&color=fff&size=80`;
        
        // Update student info
        document.getElementById('student_avatar').src = avatarUrl;
        document.getElementById('student_full_name').textContent = `${data.firstname} ${data.middlename || ''} ${data.lastname}`.trim();
        document.getElementById('student_id_display').textContent = `ID: ${data.idNo}`;
        document.getElementById('student_course').textContent = `Year: ${data.yearLevel}`;
        
        // Populate subjects list
        const subjectsList = document.getElementById('subjects_list');
        subjectsList.innerHTML = '';
        
        if (allSubjects && allSubjects.length > 0) {
            allSubjects.forEach(subject => {
                const subjectOption = document.createElement('div');
                subjectOption.className = 'subject-option';
                if (subject.subject_id == data.subjectId) {
                    subjectOption.classList.add('active');
                }
                
                subjectOption.innerHTML = `
                    <div class="subject-option-header">
                        <div class="subject-code">${subject.subject_code || 'N/A'}</div>
                        <div class="subject-meta">${subject.units || '0'} units</div>
                    </div>
                    <div class="subject-name">${subject.subject_name || 'N/A'}</div>
                `;
                
                // Enhanced touch event for mobile
                const handleSubjectClick = function() {
                    // Remove active class from all options
                    document.querySelectorAll('.subject-option').forEach(opt => {
                        opt.classList.remove('active');
                    });
                    
                    // Add active class to clicked option
                    this.classList.add('active');
                    
                    // Update subject details
                    updateSubjectDetails(subject);
                    
                    // Update hidden fields
                    document.getElementById('grade_subject_id').value = subject.subject_id;
                    
                    // On mobile, scroll the subject into view
                    if (window.innerWidth <= 768) {
                        this.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'nearest',
                            inline: 'nearest'
                        });
                    }
                };
                
                // Use both click and touch events for better mobile support
                subjectOption.addEventListener('click', handleSubjectClick);
                subjectOption.addEventListener('touchend', function(e) {
                    e.preventDefault();
                    handleSubjectClick.call(this);
                });
                
                subjectsList.appendChild(subjectOption);
            });
        } else {
            subjectsList.innerHTML = '<div class="no-subjects">No subjects available</div>';
        }
        
        // Update with initial subject data
        updateSubjectDetails({
            subject_code: data.subjectCode,
            subject_name: data.subjectName,
            units: data.subjectUnits,
            subject_year: data.subjectYear,
            subject_semester: data.subjectSemester
        });
        
        // Set hidden fields
        document.getElementById('grade_student_id').value = data.studentId || data.student_db_id;
        document.getElementById('grade_subject_id').value = data.subjectId;
        document.getElementById('grade').value = '';
        
        // Show modal with enhanced mobile handling
        const gradeModal = document.getElementById('grade-modal');
        gradeModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Add touch event to close modal when tapping outside on mobile
        if (window.innerWidth <= 768) {
            gradeModal.addEventListener('touchend', handleMobileBackdropTap);
        }
    }

    // Enhanced mobile backdrop tap handler
    function handleMobileBackdropTap(e) {
        if (e.target === document.getElementById('grade-modal')) {
            closeModal();
        }
    }
    
    function updateSubjectDetails(subject) {
        document.getElementById('subject_code').textContent = subject.subject_code || 'N/A';
        document.getElementById('subject_name').textContent = subject.subject_name || 'N/A';
        document.getElementById('subject_units').textContent = subject.units || '0';
        document.getElementById('subject_year').textContent = subject.subject_year || 'N/A';
        document.getElementById('subject_semester').textContent = subject.subject_semester || 'N/A';
    }
    
    // Enhanced close modal function for mobile
    function closeModal() {
        const gradeModal = document.getElementById('grade-modal');
        gradeModal.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        // Remove mobile event listeners
        gradeModal.removeEventListener('touchend', handleMobileBackdropTap);
    }
    
    function saveGrade(e) {
        e.preventDefault();
        
        const formData = new FormData(gradeForm);
        const submitBtn = gradeForm.querySelector('.btn-primary');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.disabled = true;
        
        const gradeData = {
            student_id: parseInt(formData.get('student_id')),
            subject_id: parseInt(formData.get('subject_id')),
            grade: formData.get('grade')
        };
        
        console.log('Saving grade data:', gradeData);
        
        fetch(window.laravelRoutes.saveGrade, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(gradeData)
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                // Try to get error message from response
                return response.json().then(errorData => {
                    throw new Error(errorData.message || 'Network response was not ok');
                }).catch(() => {
                    throw new Error('Network response was not ok');
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Save grade response:', data);
            if (data.success) {
                showNotification('Grade saved successfully!', 'success');
                closeModal();
                loadUngradedStudents(); // Reload the list
            } else {
                showNotification(data.message || 'Error saving grade', 'error');
            }
        })
        .catch(error => {
            console.error('Error saving grade:', error);
            showNotification('Error saving grade: ' + error.message, 'error');
        })
        .finally(() => {
            submitBtn.innerHTML = '<i class="fas fa-save"></i>Save Grade';
            submitBtn.disabled = false;
        });
    }
    
    function showLoading(show) {
        if (show) {
            loadingContainer.style.display = 'block';
            studentsGrid.style.display = 'none';
            noStudentsContainer.style.display = 'none';
        } else {
            loadingContainer.style.display = 'none';
            studentsGrid.style.display = 'grid';
        }
    }
}

// Instructor Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');

    console.log('DOM loaded - checking input grades section...');

    // Initialize Dark Mode and Theme System
    initializeDarkMode();
    watchSystemTheme();
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    // Navigation functionality
    const menuItems = document.querySelectorAll('.menu-item');
    const contentSections = document.querySelectorAll('.content-section');
    const pageTitle = document.querySelector('.page-title');

    // Check if input grades section is active on page load
    const inputGradesSection = document.getElementById('input-grades-section');
    console.log('Input grades section found:', !!inputGradesSection);
    if (inputGradesSection && inputGradesSection.classList.contains('active')) {
        console.log('Input grades section is active, initializing...');
        setTimeout(initializeInputGrades, 100);
    }

    // Also listen for section changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const target = mutation.target;
                if (target.id === 'input-grades-section' && target.classList.contains('active')) {
                    console.log('Input grades section became active, initializing...');
                    setTimeout(initializeInputGrades, 100);
                }
            }
        });
    });

    if (inputGradesSection) {
        observer.observe(inputGradesSection, {
            attributes: true,
            attributeFilter: ['class']
        });
    }
    
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            if (this.id === 'logout-btn') {
                if (confirm('Are you sure you want to logout?')) {
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
                const sectionName = this.querySelector('.menu-text').textContent;
                if (pageTitle) {
                    pageTitle.textContent = sectionName + ' | Instructor Dashboard';
                }
                
                // Initialize specific section functionality
                initializeSection(this.getAttribute('data-section'));
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
                document.querySelector('[data-section="assignments"]').click();
                setTimeout(() => {
                    const createBtn = document.getElementById('createAssignmentBtn');
                    if (createBtn) createBtn.click();
                }, 300);
                break;
            case 'input-grades':
                // Find and click the input-grades menu item
                const inputGradesMenuItem = document.querySelector('[data-section="input-grades"]');
                if (inputGradesMenuItem) {
                    inputGradesMenuItem.click();
                }
                break;
            case 'view-students':
                document.querySelector('[data-section="students"]').click();
                break;
            case 'manage-grades':
                document.querySelector('[data-section="grades"]').click();
                break;
            case 'upload-materials':
                showNotification('Upload materials feature coming soon!', 'info');
                break;
            case 'upload-materials':
                showNotification('Upload materials feature coming soon!', 'info');
                break;
        }
    }

    // Initialize section-specific functionality
    function initializeSection(sectionName) {
        switch(sectionName) {
            case 'input-grades':
                console.log('Initializing input grades section...');
                setTimeout(initializeInputGrades, 100);
                break;
            case 'dashboard':
                // Initialize dashboard if needed
                break;
            case 'courses':
                // Initialize courses if needed
                break;
            // Add other sections as needed
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