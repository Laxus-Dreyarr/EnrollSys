// Enhanced Irregular Student Modal Functionality
function initializeIrregularModal() {
    const modal = document.getElementById('irregularSubjectsModal');
    const subjectsGrid = document.getElementById('pastSubjectsList');
    const searchInput = document.getElementById('pastSubjectsSearch');
    const mobileSearchInput = document.getElementById('mobileSubjectsSearch');
    const yearLevelFilter = document.getElementById('yearLevelFilter');
    const semesterFilter = document.getElementById('semesterFilter');
    const mobileYearLevelFilter = document.getElementById('mobileYearLevelFilter');
    const mobileSemesterFilter = document.getElementById('mobileSemesterFilter');
    const clearSearchBtn = document.getElementById('clearSearch');
    const quickSelectAllBtn = document.getElementById('quickSelectAll');
    const quickDeselectAllBtn = document.getElementById('quickDeselectAll');
    const saveBtn = document.getElementById('savePastSubjects');
    const selectedCount = document.getElementById('selectedCount');
    const completedCount = document.getElementById('completedCount');
    const totalUnits = document.getElementById('totalUnits');
    const currentYearLevel = document.getElementById('currentYearLevel');
    const selectionSummary = document.getElementById('selectionSummary');
    const unitsSummary = document.getElementById('unitsSummary');
    const saveCount = document.getElementById('saveCount');
    const mobileSearchToggle = document.getElementById('mobileSearchToggle');
    const mobileSearchPanel = document.getElementById('mobileSearchPanel');
    const mobileSearchClose = document.getElementById('mobileSearchClose');
    const mobileFilterToggle = document.getElementById('mobileFilterToggle');
    const mobileFilterPanel = document.getElementById('mobileFilterPanel');
    const mobileFilterClose = document.getElementById('mobileFilterClose');
    const filterCount = document.querySelector('.filter-count');

    let irregularAllSubjects = [];
    let selectedPastSubjects = new Set();
    let currentFilters = {
        yearLevel: 'all',
        semester: 'all',
        search: ''
    };

    // Initialize modal
    function init() {
        loadAllSubjects();
        attachEventListeners();
        updateFilterCount();
    }

    function attachEventListeners() {
        // Search and filter events
        searchInput.addEventListener('input', handleSearch);
        mobileSearchInput.addEventListener('input', handleSearch);
        yearLevelFilter.addEventListener('change', handleFilterChange);
        semesterFilter.addEventListener('change', handleFilterChange);
        mobileYearLevelFilter.addEventListener('change', handleMobileFilterChange);
        mobileSemesterFilter.addEventListener('change', handleMobileFilterChange);
        clearSearchBtn.addEventListener('click', clearSearch);

        // Selection actions
        quickSelectAllBtn.addEventListener('click', selectAllVisible);
        quickDeselectAllBtn.addEventListener('click', deselectAllVisible);
        saveBtn.addEventListener('click', saveSubjects);

        // Mobile interactions
        mobileSearchToggle.addEventListener('click', toggleMobileSearch);
        mobileSearchClose.addEventListener('click', closeMobileSearch);
        mobileFilterToggle.addEventListener('click', toggleMobileFilters);
        mobileFilterClose.addEventListener('click', closeMobileFilters);

        // Close modal
        document.getElementById('closeIrregularModal').addEventListener('click', closeModal);
        document.getElementById('cancelIrregular').addEventListener('click', closeModal);
        
        // Close mobile panels when clicking outside
        document.addEventListener('click', (e) => {
            if (mobileFilterPanel && !mobileFilterPanel.contains(e.target) && !mobileFilterToggle.contains(e.target)) {
                closeMobileFilters();
            }
            if (mobileSearchPanel && !mobileSearchPanel.contains(e.target) && !mobileSearchToggle.contains(e.target)) {
                closeMobileSearch();
            }
        });
    }

    function handleSearch(e) {
        currentFilters.search = e.target.value.trim().toLowerCase();
        if (e.target === mobileSearchInput) {
            searchInput.value = e.target.value;
        } else {
            mobileSearchInput.value = e.target.value;
        }
        filterAndDisplaySubjects();
    }

    function handleFilterChange() {
        currentFilters.yearLevel = yearLevelFilter.value;
        currentFilters.semester = semesterFilter.value;
        syncMobileFilters();
        filterAndDisplaySubjects();
    }

    function handleMobileFilterChange() {
        currentFilters.yearLevel = mobileYearLevelFilter.value;
        currentFilters.semester = mobileSemesterFilter.value;
        syncDesktopFilters();
        filterAndDisplaySubjects();
        closeMobileFilters();
    }

    function syncMobileFilters() {
        mobileYearLevelFilter.value = currentFilters.yearLevel;
        mobileSemesterFilter.value = currentFilters.semester;
    }

    function syncDesktopFilters() {
        yearLevelFilter.value = currentFilters.yearLevel;
        semesterFilter.value = currentFilters.semester;
    }

    function clearSearch() {
        searchInput.value = '';
        mobileSearchInput.value = '';
        currentFilters.search = '';
        filterAndDisplaySubjects();
    }

    function toggleMobileSearch() {
        mobileSearchPanel.classList.toggle('active');
        if (mobileSearchPanel.classList.contains('active')) {
            mobileSearchInput.focus();
        }
    }

    function closeMobileSearch() {
        mobileSearchPanel.classList.remove('active');
    }

    function toggleMobileFilters() {
        mobileFilterPanel.classList.toggle('active');
    }

    function closeMobileFilters() {
        mobileFilterPanel.classList.remove('active');
    }

    function updateFilterCount() {
        let count = 0;
        if (currentFilters.yearLevel !== 'all') count++;
        if (currentFilters.semester !== 'all') count++;
        if (currentFilters.search !== '') count++;
        filterCount.textContent = count;
    }

    // In the loadAllSubjects function in initializeIrregularModal
    function loadAllSubjects() {
        showLoadingState();
        
        fetch('/student/enrollment/all-subjects', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                irregularAllSubjects = data.subjects;
                
                // Log for debugging
                console.log('Loaded subjects for irregular student:', irregularAllSubjects.length);
                console.log('Subjects:', irregularAllSubjects);
                
                displaySubjects(irregularAllSubjects);
                updateSelectionInfo();
            } else {
                showErrorState('Failed to load subjects');
            }
        })
        .catch(error => {
            console.error('Error loading subjects:', error);
            showErrorState('Network error. Please try again.');
        });
    }

    // Display subjects in grid layout
    function displaySubjects(subjects) {
        console.log('Displaying subjects count:', subjects.length);
        
        if (subjects.length === 0) {
            showEmptyState();
            return;
        }

        hideEmptyState();
        
        const subjectsHTML = subjects.map(subject => {
            const isSelected = selectedPastSubjects.has(subject.id.toString());
            
            return `
                <div class="subject-card ${isSelected ? 'selected' : ''}" data-id="${subject.id}">
                    <div class="subject-header">
                        <div class="subject-code">${subject.code}</div>
                        <div class="subject-meta">
                            <span class="subject-units">${subject.units} units</span>
                            <span class="subject-level">${subject.year_level} - ${subject.semester}</span>
                        </div>
                    </div>
                    <div class="subject-name">${subject.name}</div>
                    <div class="subject-description">${subject.description || 'No description available'}</div>
                    ${subject.prerequisites && subject.prerequisites.length > 0 ? `
                        <div class="prerequisite-info">
                            <i class="fas fa-exclamation-triangle"></i>
                            Requires: ${subject.prerequisites.map(p => p.code).join(', ')}
                        </div>
                    ` : ''}
                    <div class="subject-footer">
                        <div class="subject-checkbox">
                            <input type="checkbox" id="subject_${subject.id}" 
                                ${isSelected ? 'checked' : ''}
                                onchange="togglePastSubjectSelection(${subject.id}, '${subject.code.replace(/'/g, "\\'")}', '${subject.name.replace(/'/g, "\\'")}', ${subject.units}, this.checked)">
                            <label for="subject_${subject.id}" class="checkbox-label">Completed</label>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        subjectsGrid.innerHTML = subjectsHTML;
        
        // Debug log
        console.log('Subjects rendered in grid');
    }

    // Filter and display subjects based on current filters
    function filterAndDisplaySubjects() {
        updateFilterCount();
        
        let filteredSubjects = irregularAllSubjects;
        
        // Apply year level filter
        if (currentFilters.yearLevel !== 'all') {
            filteredSubjects = filteredSubjects.filter(subject => 
                subject.year_level === currentFilters.yearLevel
            );
        }
        
        // Apply semester filter
        if (currentFilters.semester !== 'all') {
            filteredSubjects = filteredSubjects.filter(subject => 
                subject.semester === currentFilters.semester
            );
        }
        
        // Apply search filter
        if (currentFilters.search) {
            const searchTerm = currentFilters.search.toLowerCase();
            filteredSubjects = filteredSubjects.filter(subject => 
                subject.code.toLowerCase().includes(searchTerm) ||
                subject.name.toLowerCase().includes(searchTerm) ||
                (subject.description && subject.description.toLowerCase().includes(searchTerm))
            );
        }
        
        displaySubjects(filteredSubjects);
        updateSelectionInfo();
    }

    // Update selection information
    function updateSelectionInfo() {
        const count = selectedPastSubjects.size;
        const units = Array.from(selectedPastSubjects).reduce((total, subjectId) => {
            const subject = irregularAllSubjects.find(s => s.id == subjectId);
            return total + (subject ? parseInt(subject.units) : 0);
        }, 0);
        
        selectedCount.textContent = `${count} selected`;
        completedCount.textContent = count;
        totalUnits.textContent = units;
        selectionSummary.textContent = `${count} subjects`;
        unitsSummary.textContent = `${units} units`;
        saveCount.textContent = count;
        
        // Update save button state
        saveBtn.disabled = count === 0;
    }

    // Toggle subject selection
    window.togglePastSubjectSelection = function(subjectId, subjectCode, subjectName, units, isChecked) {
        if (isChecked) {
            selectedPastSubjects.add(subjectId.toString());
        } else {
            selectedPastSubjects.delete(subjectId.toString());
        }
        
        // Update UI
        const subjectElement = document.querySelector(`.subject-card[data-id="${subjectId}"]`);
        if (subjectElement) {
            subjectElement.classList.toggle('selected', isChecked);
        }
        
        updateSelectionInfo();
    };

    // Select all visible subjects
    function selectAllVisible() {
        const visibleSubjectElements = subjectsGrid.querySelectorAll('.subject-card');
        visibleSubjectElements.forEach(card => {
            const subjectId = card.getAttribute('data-id');
            const checkbox = card.querySelector('input[type="checkbox"]');
            const subject = irregularAllSubjects.find(s => s.id == subjectId);
            
            if (subject && !checkbox.checked) {
                checkbox.checked = true;
                togglePastSubjectSelection(subject.id, subject.code, subject.name, subject.units, true);
            }
        });
    }

    // Deselect all visible subjects
    function deselectAllVisible() {
        const visibleSubjectElements = subjectsGrid.querySelectorAll('.subject-card');
        visibleSubjectElements.forEach(card => {
            const subjectId = card.getAttribute('data-id');
            const checkbox = card.querySelector('input[type="checkbox"]');
            const subject = irregularAllSubjects.find(s => s.id == subjectId);
            
            if (subject && checkbox.checked) {
                checkbox.checked = false;
                togglePastSubjectSelection(subject.id, subject.code, subject.name, subject.units, false);
            }
        });
    }

    // Save functionality
    function saveSubjects() {
        if (selectedPastSubjects.size === 0) {
            showNotification('Please select at least one subject', 'error');
            return;
        }
        
        const submitBtn = saveBtn;
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.disabled = true;
        
        const pastSubjectsArray = Array.from(selectedPastSubjects).map(subjectId => {
            const subject = irregularAllSubjects.find(s => s.id == subjectId);
            return subject ? {
                id: subject.id,
                code: subject.code,
                name: subject.name,
                units: subject.units
            } : null;
        }).filter(item => item !== null);
        
        fetch('/student/enrollment/save-past-subjects', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                past_subjects: pastSubjectsArray
            })
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification('Completed subjects saved successfully!', 'success');
                closeModal();
                
                // Refresh the page
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification(data.message || 'Failed to save subjects', 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error saving past subjects:', error);
            showNotification('Failed to save subjects. Please try again.', 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }

    function closeModal() {
        modal.classList.remove('active');
    }

    // Utility functions
    function showLoadingState() {
        subjectsGrid.innerHTML = `
            <div class="irregular-loading-state">
                <div class="loading-spinner"></div>
                <p>Loading your subjects...</p>
            </div>
        `;
    }

    function showEmptyState() {
        document.querySelector('.irregular-empty-state').style.display = 'flex';
        subjectsGrid.style.display = 'none';
    }

    function hideEmptyState() {
        document.querySelector('.irregular-empty-state').style.display = 'none';
        subjectsGrid.style.display = 'grid';
    }

    function showErrorState(message) {
        subjectsGrid.innerHTML = `
            <div class="irregular-empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h4>Error Loading Subjects</h4>
                <p>${message}</p>
                <button class="btn-primary" onclick="loadAllSubjects()">
                    <i class="fas fa-redo"></i>
                    Try Again
                </button>
            </div>
        `;
    }

    // Make the initialization function available globally
    window.loadIrregularSubjects = init;

    return {
        init,
        modal: modal
    };
}

// Updated checkIrregularStudent function to use both modals
function checkIrregularStudent(irregularModal, enhancedEnrollmentModal) {
    const isIrregular = document.getElementById('is-regular')?.value == 2;
    
    if (isIrregular) {
        fetch('/student/enrollment/check-past-subjects', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && !data.has_submitted) {
                if (irregularModal && irregularModal.init) {
                    irregularModal.init();
                    irregularModal.modal.classList.add('active');
                }
            } else if (data.success && data.has_submitted && enhancedEnrollmentModal) {
                // If they have submitted past subjects, they can use the enhanced enrollment modal
                console.log('Irregular student ready for enrollment');
            }
        })
        .catch(error => {
            console.error('Error checking past subjects:', error);
        });
    }
}

// Profile picture upload functionality
function initializeProfilePictureUpload() {
    const avatarContainer = document.getElementById('avatar-container');
    const avatarInput = document.getElementById('avatar-input');
    const profileAvatar = document.getElementById('profile-avatar');
    const changePhotoBtn = document.getElementById('change-photo-btn');
    const uploadLoading = document.getElementById('upload-loading');
    const progressFill = document.getElementById('progress-fill');
    const progressText = document.getElementById('progress-text');
    
    if (!avatarContainer) return;
    
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
            simulateServerUpload(file);
            
        }, 1500);
    }
    
    // Simulate server upload (replace with actual AJAX call)
    function simulateServerUpload(file) {
        // Create FormData for actual upload
        const formData = new FormData();
        formData.append('avatar', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
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
}

// Initialize tooltips for better UX
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

// Student info modal functionality
function initializeStudentInfoModal() {
    const studentInfoModal = document.getElementById('studentInfoModal');
    const studentInfoForm = document.getElementById('studentInfoForm');
    
    if (!studentInfoModal) {
        return;
    }

    // Populate curriculum dropdown immediately if modal is active
    if (studentInfoModal.classList.contains('active')) {
        populateCurriculumDropdown();
    }

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

    // Real-time validation for school ID
    const schoolIdInput = document.getElementById('school_id');
    schoolIdInput.addEventListener('input', function() {
        validateSchoolIdFormat(this);
    });

    schoolIdInput.addEventListener('blur', function() {
        validateSchoolIdCurriculum(this);
    });

    // Populate curriculum dropdown function
    function populateCurriculumDropdown() {
        const curriculumSelect = document.getElementById('curriculum');
        
        if (!curriculumSelect) {
            console.error('Curriculum select element not found');
            return;
        }
        
        console.log('Populating curriculum dropdown...');
        
        // Fetch available curricula from the server
        fetch('/student/curricula', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Curriculum data received:', data);
            
            if (data.success && data.curricula) {
                // Clear existing options except the first one
                while (curriculumSelect.options.length > 1) {
                    curriculumSelect.remove(1);
                }
                
                // Add curriculum options
                data.curricula.forEach(curriculum => {
                    const option = document.createElement('option');
                    option.value = curriculum.curriculum_year;
                    option.textContent = `${curriculum.curriculum_year} Curriculum`;
                    curriculumSelect.appendChild(option);
                });
                
                console.log('Curriculum dropdown populated with:', data.curricula.length, 'options');
                
                // If there's only one curriculum, select it by default
                if (data.curricula.length === 1) {
                    curriculumSelect.value = data.curricula[0].curriculum_year;
                    console.log('Auto-selected curriculum:', data.curricula[0].curriculum_year);
                }
            } else {
                console.error('Failed to load curricula:', data.message);
                showCurriculumError('Failed to load curriculum options. Please refresh the page.');
            }
        })
        .catch(error => {
            console.error('Error loading curricula:', error);
            showCurriculumError('Error loading curriculum options. Please check your connection.');
            
            // Fallback: Add a default option
            const option = document.createElement('option');
            option.value = '2019';
            option.textContent = '2019 Curriculum (Default)';
            curriculumSelect.appendChild(option);
        });
    }

    function showCurriculumError(message) {
        const errorElement = document.getElementById('curriculum_error');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.add('active');
        }
    }

    function validateSchoolIdFormat(field) {
        const errorElement = document.getElementById(field.id + '_error');
        const schoolId = field.value.trim();
        
        if (!schoolId) {
            showFieldError(field, errorElement, 'School ID is required');
            return false;
        }
        
        // Check format: YYYY-XXXXX
        const schoolIdRegex = /^\d{4}-\d+$/;
        if (!schoolIdRegex.test(schoolId)) {
            showFieldError(field, errorElement, 'School ID must be in the format: YYYY-XXXXX (e.g., 2020-30617)');
            return false;
        }
        
        clearFieldError(field);
        return true;
    }

    function validateSchoolIdCurriculum(field) {
        const errorElement = document.getElementById(field.id + '_error');
        const schoolId = field.value.trim();
        
        if (!validateSchoolIdFormat(field)) {
            return false;
        }
        
        // Extract curriculum year
        const curriculumYear = parseInt(schoolId.split('-')[0]);
        const currentYear = new Date().getFullYear();
        
        if (curriculumYear < 2013) {
            showFieldError(field, errorElement, 'Curriculum year must be 2013 or later.');
            return false;
        }
        
        if (curriculumYear > currentYear) {
            showFieldError(field, errorElement, 'Curriculum year cannot exceed the current year.');
            return false;
        }
        
        clearFieldError(field);
        return true;
    }

    function validateField(field) {
        const errorElement = document.getElementById(field.id + '_error');
        
        if (!field.value.trim()) {
            showFieldError(field, errorElement, 'This field is required');
            return false;
        }
        
        if (field.id === 'school_id') {
            return validateSchoolIdFormat(field) && validateSchoolIdCurriculum(field);
        }
        
        clearFieldError(field);
        return true;
    }

    function showFieldError(field, errorElement, message) {
        field.style.borderColor = 'var(--danger-color)';
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.add('active');
        }
    }

    function clearFieldError(field) {
        field.style.borderColor = '';
        const errorElement = document.getElementById(field.id + '_error');
        if (errorElement) {
            errorElement.classList.remove('active');
        }
    }

    function validateForm() {
        let isValid = true;
        const fields = ['school_id', 'year_level', 'student_type', 'curriculum'];
        
        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && !validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    }

    function submitForm() {
        const submitBtn = studentInfoForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        
        // Get form data
        const formData = new FormData();
        formData.append('action', 'complete_student_info');
        formData.append('school_id', document.getElementById('school_id').value);
        formData.append('year_level', document.getElementById('year_level').value);
        formData.append('student_type', document.getElementById('student_type').value);
        formData.append('curriculum', document.getElementById('curriculum').value);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        console.log('Submitting student info with curriculum:', document.getElementById('curriculum').value);

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
                } else if (data.message.includes('Curriculum year')) {
                    const schoolIdInput = document.getElementById('school_id');
                    const schoolIdInputError = document.getElementById('school_id_error');
                    showFieldError(schoolIdInput, schoolIdInputError, data.message);
                } else if (data.message.includes('curriculum')) {
                    const curriculumInput = document.getElementById('curriculum');
                    const curriculumInputError = document.getElementById('curriculum_error');
                    showFieldError(curriculumInput, curriculumInputError, data.message);
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
            Swal.fire({
                title: 'Network Error',
                text: 'Please check your connection and try again.',
                icon: 'error',
                confirmButtonText: 'Close',
                confirmButtonColor: '#070808ff',
                background: '#1a1a2e',
                color: '#ffffff',
                backdrop: 'rgba(0,0,0,0.7)'
            });
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
                <div class="loading-bar">
                    <div class="loading-progress"></div>
                </div>
            </div>
        `;
        
        // Animate loading bar
        const loadingProgress = modalContainer.querySelector('.loading-progress');
        let progress = 0;
        const interval = setInterval(() => {
            progress += 1;
            loadingProgress.style.width = progress + '%';
            if (progress >= 100) {
                clearInterval(interval);
            }
        }, 30);
        
        // Refresh page after success
        setTimeout(() => {
            window.location.reload();
        }, 3000);
    }
}

// Enhanced notification function
function showNotification(message, type = 'success') {
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

// Add CSS animations for notifications
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
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
document.head.appendChild(notificationStyles);

// Enhanced Enrollment Modal Functionality
function initializeEnhancedEnrollmentModal() {
    const modal = document.getElementById('enhancedEnrollmentModal');
    const subjectsGrid = document.getElementById('enhancedSubjectsList');
    const searchInput = document.getElementById('enhancedSubjectsSearch');
    const mobileSearchInput = document.getElementById('enhancedMobileSubjectsSearch');
    const subjectFilter = document.getElementById('enhancedSubjectFilter');
    const sortFilter = document.getElementById('enhancedSortFilter');
    const mobileSubjectFilter = document.getElementById('enhancedMobileSubjectFilter');
    const mobileSortFilter = document.getElementById('enhancedMobileSortFilter');
    const clearSearchBtn = document.getElementById('enhancedClearSearch');
    const quickSelectAllBtn = document.getElementById('enhancedQuickSelectAll');
    const quickDeselectAllBtn = document.getElementById('enhancedQuickDeselectAll');
    const selectedCount = document.getElementById('enhancedSelectedCount');
    const totalUnits = document.getElementById('enhancedTotalUnits');
    const currentYearLevel = document.getElementById('enhancedCurrentYearLevel');
    const selectionSummary = document.getElementById('enhancedSelectionSummary');
    const unitsSummary = document.getElementById('enhancedUnitsSummary');
    const submitCount = document.getElementById('enhancedSubmitCount');
    const mobileSearchToggle = document.getElementById('enhancedMobileSearchToggle');
    const mobileSearchPanel = document.getElementById('enhancedMobileSearchPanel');
    const mobileSearchClose = document.getElementById('enhancedMobileSearchClose');
    const mobileFilterToggle = document.getElementById('enhancedMobileFilterToggle');
    const mobileFilterPanel = document.getElementById('enhancedMobileFilterPanel');
    const mobileFilterClose = document.getElementById('enhancedMobileFilterClose');
    const filterCount = document.querySelector('.enhanced-filter-count');
    const studentTypeBadge = document.getElementById('enhancedStudentTypeBadge');
    const enrollmentYearLevel = document.getElementById('enhancedEnrollmentYearLevel');
    const enrollmentSemester = document.getElementById('enhancedEnrollmentSemester');
    const enrollmentStudentType = document.getElementById('enhancedEnrollmentStudentType');
    const maxUnitsElement = document.getElementById('enhancedMaxUnits');

    const sectionFilter = document.getElementById('enhancedSectionFilter');
    const mobileSectionFilter = document.getElementById('enhancedMobileSectionFilter');

    // New elements for step navigation and FHE upload
    const nextStepBtn = document.getElementById('enhancedNextStep');
    const backStepBtn = document.getElementById('enhancedBackStep');
    const submitBtn = document.getElementById('enhancedSubmitEnrollment');
    const cancelBtn = document.getElementById('enhancedCancelEnrollment');
    const fheSection = document.getElementById('enhancedFheSection');
    const progressSteps = document.querySelectorAll('.enhanced-progress-step');
    
    // FHE Upload elements
    const fheDropZone = document.getElementById('enhancedFheDropZone');
    const fheBrowseBtn = document.getElementById('enhancedFheBrowseBtn');
    const fheFileInput = document.getElementById('enhancedFheFileInput');
    const fhePreview = document.getElementById('enhancedFhePreview');
    const fheFileName = document.getElementById('enhancedFheFileName');
    const fheFileSize = document.getElementById('enhancedFheFileSize');
    const fheRemoveBtn = document.getElementById('enhancedFheRemoveBtn');
    const fheUploadProgress = document.getElementById('enhancedFheUploadProgress');
    const fheProgressFill = document.getElementById('enhancedFheProgressFill');
    const fheProgressText = document.getElementById('enhancedFheProgressText');

    // New variables for payment step
    const paymentSection = document.getElementById('enhancedPaymentSection');
    const confirmationSection = document.getElementById('enhancedConfirmationSection');
    const payNowBtn = document.getElementById('enhancedPayNowBtn');
    const paymentProcessing = document.getElementById('enhancedPaymentProcessing');
    const paymentSuccess = document.getElementById('enhancedPaymentSuccess');
    const paymentTransactionId = document.getElementById('enhancedPaymentTransactionId');
    const finalSubmitBtn = document.getElementById('enhancedFinalSubmitEnrollment');
    const editEnrollmentBtn = document.getElementById('enhancedEditEnrollment');
    const confirmationSubjectsList = document.getElementById('enhancedConfirmationSubjectsList');
    const confirmationFheFile = document.getElementById('enhancedConfirmationFheFile');

    let enhancedAllSubjects = [];
    let enhancedSelectedSubjects = new Map();
    let enhancedCurrentFilters = {
        subjectFilter: 'all',
        sortBy: 'code',
        search: '',
        section: 'all'
    };

    let availableSections = new Set();
    let enhancedTotalUnits = 0;
    let enhancedIsRegular = true;
    let enhancedMaxUnits = 0;
    let originalMaxUnits = 0;
    let enhancedCompletedSubjects = [];

    // Paymongo configuration
    const PAYMONGO_PUBLIC_KEY = 'pk_test_irtrpF947Hn95spAszT41BW8';
    const ORGANIZATIONAL_FEE = 150.00;
    
    // New variables for step management, FHE file and payment
    let currentStep = 1;
    let paymentIntentId = null;
    let isPaymentCompleted = false;

    // Initialize modal
    function init() {
        attachEventListeners();
        updateStepNavigation();
        initializePaymongo();
    }

    function attachEventListeners() {
        // Search and filter events
        searchInput.addEventListener('input', handleSearch);
        mobileSearchInput.addEventListener('input', handleSearch);
        subjectFilter.addEventListener('change', handleFilterChange);
        sortFilter.addEventListener('change', handleFilterChange);
        mobileSubjectFilter.addEventListener('change', handleMobileFilterChange);
        mobileSortFilter.addEventListener('change', handleMobileFilterChange);
        clearSearchBtn.addEventListener('click', clearSearch);

        // Section filter events
        if (sectionFilter) {
            sectionFilter.addEventListener('change', handleFilterChange);
        }
        if (mobileSectionFilter) {
            mobileSectionFilter.addEventListener('change', handleMobileFilterChange);
        }

        // Selection actions
        quickSelectAllBtn.addEventListener('click', selectAllVisible);
        quickDeselectAllBtn.addEventListener('click', deselectAllVisible);

        // Mobile interactions
        mobileSearchToggle.addEventListener('click', toggleMobileSearch);
        mobileSearchClose.addEventListener('click', closeMobileSearch);
        mobileFilterToggle.addEventListener('click', toggleMobileFilters);
        mobileFilterClose.addEventListener('click', closeMobileFilters);

        // Step navigation
        nextStepBtn.addEventListener('click', goToNextStep);
        backStepBtn.addEventListener('click', goToPreviousStep);
        submitBtn.addEventListener('click', submitEnrollment);
        cancelBtn.addEventListener('click', closeModal);

        // FHE Upload events
        fheBrowseBtn.addEventListener('click', () => fheFileInput.click());
        fheFileInput.addEventListener('change', handleFheFileSelect);
        fheRemoveBtn.addEventListener('click', removeFheFile);
        
        // Drag and drop for FHE
        fheDropZone.addEventListener('dragover', handleFheDragOver);
        fheDropZone.addEventListener('dragleave', handleFheDragLeave);
        fheDropZone.addEventListener('drop', handleFheDrop);

        // Payment events
        payNowBtn.addEventListener('click', processPayment);
        finalSubmitBtn.addEventListener('click', submitFinalEnrollment);
        editEnrollmentBtn.addEventListener('click', goToStepOne);

        // Step navigation
        nextStepBtn.addEventListener('click', goToNextStep);
        backStepBtn.addEventListener('click', goToPreviousStep);
        submitBtn.addEventListener('click', goToNextStep); // Now goes to payment step
        cancelBtn.addEventListener('click', closeModal);
        
        // Close mobile panels when clicking outside
        document.addEventListener('click', (e) => {
            if (mobileFilterPanel && !mobileFilterPanel.contains(e.target) && !mobileFilterToggle.contains(e.target)) {
                closeMobileFilters();
            }
            if (mobileSearchPanel && !mobileSearchPanel.contains(e.target) && !mobileSearchToggle.contains(e.target)) {
                closeMobileSearch();
            }
        });
    }

    // Step Navigation Functions
    function updateStepNavigation() {
        // Update progress steps
        progressSteps.forEach(step => {
            const stepNumber = parseInt(step.getAttribute('data-step'));
            if (stepNumber === currentStep) {
                step.classList.add('active');
            } else {
                step.classList.remove('active');
            }
        });

        // Show/hide sections based on current step
        const mainContent = document.querySelector('.enhanced-enrollment-main-content');
        if (currentStep === 1) {
            mainContent.style.display = 'block';
            fheSection.style.display = 'none';
            paymentSection.style.display = 'none';
            confirmationSection.style.display = 'none';
            backStepBtn.style.display = 'none';
            nextStepBtn.style.display = 'flex';
            submitBtn.style.display = 'none';
            nextStepBtn.disabled = enhancedSelectedSubjects.size === 0;
        } else if (currentStep === 2) {
            mainContent.style.display = 'none';
            fheSection.style.display = 'block';
            paymentSection.style.display = 'none';
            confirmationSection.style.display = 'none';
            backStepBtn.style.display = 'flex';
            nextStepBtn.style.display = 'flex'; // Change this to show next button
            submitBtn.style.display = 'none';   // Hide submit button
            nextStepBtn.disabled = !isFheUploaded; // Enable only if FHE uploaded
        } else if (currentStep === 3) {
            mainContent.style.display = 'none';
            fheSection.style.display = 'none';
            paymentSection.style.display = 'block';
            confirmationSection.style.display = 'none';
            backStepBtn.style.display = 'flex';
            nextStepBtn.style.display = 'none';
            submitBtn.style.display = 'none';
            updatePaymentUI();
        } else if (currentStep === 4) {
            mainContent.style.display = 'none';
            fheSection.style.display = 'none';
            paymentSection.style.display = 'none';
            confirmationSection.style.display = 'block';
            backStepBtn.style.display = 'flex';
            nextStepBtn.style.display = 'none';
            submitBtn.style.display = 'none';
            updateConfirmationUI();
        }
    }

    function goToNextStep() {
        if (currentStep === 1 && enhancedSelectedSubjects.size > 0) {
            currentStep = 2;
            updateStepNavigation();
        } else if (currentStep === 2 && isFheUploaded) {
            currentStep = 3; // Go to payment step, not submit
            updateStepNavigation();
        } else if (currentStep === 3 && isPaymentCompleted) {
            currentStep = 4; // Go to confirmation after payment
            updateStepNavigation();
        }
    }

    function goToPreviousStep() {
        if (currentStep > 1) {
            currentStep--;
            updateStepNavigation();
        }
    }

    function goToStepOne() {
        currentStep = 1;
        updateStepNavigation();
    }

    // FHE Upload Functions
    function handleFheFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            processFheFile(file);
        }
    }

    function handleFheDragOver(event) {
        event.preventDefault();
        fheDropZone.classList.add('dragover');
    }

    function handleFheDragLeave(event) {
        event.preventDefault();
        fheDropZone.classList.remove('dragover');
    }

    function handleFheDrop(event) {
        event.preventDefault();
        fheDropZone.classList.remove('dragover');
        
        const files = event.dataTransfer.files;
        if (files.length > 0) {
            processFheFile(files[0]);
        }
    }

    function processFheFile(file) {
        // Validate file
        if (!validateFheFile(file)) {
            return;
        }

        fheFile = file;
        
        // Show preview
        showFhePreview(file);
        
        // Simulate upload process
        simulateFheUpload();
    }

    function validateFheFile(file) {
        const validTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!validTypes.includes(file.type)) {
            showNotification('Please select a valid file type (PDF, JPG, PNG, DOC, DOCX)', 'error');
            return false;
        }

        if (file.size > maxSize) {
            showNotification('File size must be less than 5MB', 'error');
            return false;
        }

        return true;
    }

    // Payment Functions
    function initializePaymongo() {
        // Load Paymongo.js if not already loaded
        if (typeof window.Paymongo === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://js.paymongo.com/v1/paymongo.js';
            script.onload = () => {
                window.Paymongo.init(PAYMONGO_PUBLIC_KEY);
            };
            document.head.appendChild(script);
        } else {
            window.Paymongo.init(PAYMONGO_PUBLIC_KEY);
        }
    }

    async function processPayment() {
        try {
            // Show processing state
            payNowBtn.disabled = true;
            payNowBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            paymentProcessing.style.display = 'block';

            // Create payment intent on server
            const response = await fetch('/student/enrollment/create-payment-intent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    amount: ORGANIZATIONAL_FEE * 100, // Convert to centavos
                    currency: 'PHP',
                    description: 'Organizational Fee Payment'
                })
            });

            const data = await response.json();

            if (data.success && data.clientSecret) {
                // Initialize Paymongo
                const paymongo = window.Paymongo(data.clientSecret);
                
                // Create payment method (GCash)
                const paymentMethod = await paymongo.createPaymentMethod({
                    type: 'gcash'
                });

                if (paymentMethod.error) {
                    throw new Error(paymentMethod.error.message);
                }

                // Confirm payment
                const paymentResult = await paymongo.confirmPayment({
                    paymentMethodId: paymentMethod.id
                });

                if (paymentResult.error) {
                    throw new Error(paymentResult.error.message);
                }

                // Payment successful
                handlePaymentSuccess(paymentResult, data.paymentIntentId);
                
            } else {
                throw new Error(data.message || 'Failed to create payment intent');
            }

        } catch (error) {
            console.error('Payment processing error:', error);
            showNotification('Payment failed: ' + error.message, 'error');
            resetPaymentUI();
        }
    }

    function handlePaymentSuccess(paymentData, paymentIntentId) {
        // Update payment record on server
        fetch('/student/enrollment/confirm-payment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                paymentIntentId: paymentIntentId,
                paymentData: paymentData
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                isPaymentCompleted = true;
                paymentTransactionId.textContent = data.transactionId;
                paymentProcessing.style.display = 'none';
                paymentSuccess.style.display = 'block';
                paymentIntentId = data.paymentIntentId;
                
                showNotification('Payment completed successfully!', 'success');
                
                // Enable next step after a short delay
                setTimeout(() => {
                    goToNextStep(); // This will go to confirmation step
                }, 2000);
            } else {
                throw new Error(data.message || 'Payment confirmation failed');
            }
        })
        .catch(error => {
            console.error('Payment confirmation error:', error);
            showNotification('Payment confirmation failed: ' + error.message, 'error');
            resetPaymentUI();
        });
    }

    function handlePaymentFailure(errorData) {
        console.error('Payment failed:', errorData);
        showNotification('Payment was cancelled or failed. Please try again.', 'error');
        resetPaymentUI();
    }

    function resetPaymentUI() {
        payNowBtn.disabled = false;
        payNowBtn.innerHTML = '<i class="fas fa-lock"></i> Pay ₱150 via GCash';
        paymentProcessing.style.display = 'none';
        paymentSuccess.style.display = 'none';
    }

    function updatePaymentUI() {
        // Reset payment state when entering payment step
        if (!isPaymentCompleted) {
            resetPaymentUI();
        }
    }

    function updateConfirmationUI() {
        // Populate confirmation subjects list
        let subjectsHTML = '';
        enhancedSelectedSubjects.forEach((data, subjectId) => {
            const subject = enhancedAllSubjects.find(s => s.id == subjectId);
            if (subject) {
                subjectsHTML += `
                    <div class="enhanced-confirmation-subject">
                        <span class="enhanced-confirmation-subject-code">${subject.code}</span>
                        <span class="enhanced-confirmation-subject-name">${subject.name}</span>
                        <span class="enhanced-confirmation-subject-section">Section ${data.section}</span>
                    </div>
                `;
            }
        });
        confirmationSubjectsList.innerHTML = subjectsHTML;

        // Update FHE file name
        if (fheFile) {
            confirmationFheFile.textContent = fheFile.name;
        }
    }

    // Final enrollment submission
    function submitFinalEnrollment() {
        console.log('Final enrollment submission with payment verification');
        
        if (!isPaymentCompleted) {
            showNotification('Please complete the payment before submitting enrollment.', 'error');
            return;
        }

        const submitBtn = finalSubmitBtn;
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
        
        // Create FormData with all enrollment data including payment
        const formData = new FormData();
        
        // Convert Map to Array for submission
        const subjectsArray = Array.from(enhancedSelectedSubjects.values()).map(item => ({
            subjectId: item.subjectId,
            section: item.section
        }));
        
        // Append all data
        formData.append('subjects', JSON.stringify(subjectsArray));
        formData.append('fhe_file', fheFile);
        formData.append('total_units', enhancedTotalUnits.toString());
        formData.append('payment_intent_id', paymentIntentId);
        formData.append('is_payment_completed', isPaymentCompleted.toString());
        
        console.log('Final enrollment submission with payment:', paymentIntentId);
        
        fetch('/student/enrollment/final-enroll', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Final enrollment response:', data);
            if (data.success) {
                showNotification(data.message || 'Enrollment submitted successfully!', 'success');
                closeModal();
                
                // Refresh the page
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showNotification(data.message || 'Enrollment failed. Please try again.', 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Final enrollment error:', error);
            showNotification('Enrollment failed. Please try again. Error: ' + error.message, 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }

    function showFhePreview(file) {
        const fileSize = (file.size / (1024 * 1024)).toFixed(2);
        
        // Determine file type icon
        let fileIcon = 'fas fa-file';
        if (file.type.includes('pdf')) {
            fileIcon = 'fas fa-file-pdf';
        } else if (file.type.includes('image')) {
            fileIcon = 'fas fa-file-image';
        } else if (file.type.includes('word')) {
            fileIcon = 'fas fa-file-word';
        }

        fheFileName.textContent = file.name;
        fheFileSize.textContent = `${fileSize} MB`;
        fhePreview.querySelector('.enhanced-fhe-file-icon').className = fileIcon;
        
        fhePreview.style.display = 'block';
        fheDropZone.style.display = 'none';
    }

    function simulateFheUpload() {
        fheUploadProgress.style.display = 'block';
        let progress = 0;
        
        const interval = setInterval(() => {
            progress += Math.random() * 10;
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
                
                // Upload complete - enable next step but don't submit
                setTimeout(() => {
                    isFheUploaded = true;
                    nextStepBtn.disabled = false; // Enable next button
                    showNotification('FHE file uploaded successfully!', 'success');
                }, 500);
            }
            
            fheProgressFill.style.width = `${progress}%`;
            fheProgressText.textContent = `${Math.round(progress)}%`;
        }, 100);
    }

    function removeFheFile() {
        fheFile = null;
        isFheUploaded = false;
        fhePreview.style.display = 'none';
        fheUploadProgress.style.display = 'none';
        fheDropZone.style.display = 'block';
        fheFileInput.value = '';
        submitBtn.disabled = true;
    }

    function handleSearch(e) {
        enhancedCurrentFilters.search = e.target.value.trim().toLowerCase();
        if (e.target === mobileSearchInput) {
            searchInput.value = e.target.value;
        } else {
            mobileSearchInput.value = e.target.value;
        }
        filterAndDisplaySubjects();
    }

    function handleFilterChange() {
        enhancedCurrentFilters.subjectFilter = subjectFilter.value;
        enhancedCurrentFilters.sortBy = sortFilter.value;
        enhancedCurrentFilters.section = sectionFilter ? sectionFilter.value : 'all'; // FIX: Check if sectionFilter exists
        syncMobileFilters();
        filterAndDisplaySubjects();
    }

    function handleMobileFilterChange() {
        enhancedCurrentFilters.subjectFilter = mobileSubjectFilter.value;
        enhancedCurrentFilters.sortBy = mobileSortFilter.value;
        enhancedCurrentFilters.section = mobileSectionFilter ? mobileSectionFilter.value : 'all'; // FIX: Check if mobileSectionFilter exists
        syncDesktopFilters();
        filterAndDisplaySubjects();
        closeMobileFilters();
    }

    function syncMobileFilters() {
        if (mobileSubjectFilter) mobileSubjectFilter.value = enhancedCurrentFilters.subjectFilter;
        if (mobileSortFilter) mobileSortFilter.value = enhancedCurrentFilters.sortBy;
        if (mobileSectionFilter) mobileSectionFilter.value = enhancedCurrentFilters.section;
    }

    function syncDesktopFilters() {
        if (subjectFilter) subjectFilter.value = enhancedCurrentFilters.subjectFilter;
        if (sortFilter) sortFilter.value = enhancedCurrentFilters.sortBy;
        if (sectionFilter) sectionFilter.value = enhancedCurrentFilters.section;
    }

    function clearSearch() {
        searchInput.value = '';
        mobileSearchInput.value = '';
        enhancedCurrentFilters.search = '';
        filterAndDisplaySubjects();
    }

    function toggleMobileSearch() {
        mobileSearchPanel.classList.toggle('active');
        if (mobileSearchPanel.classList.contains('active')) {
            mobileSearchInput.focus();
        }
    }

    function closeMobileSearch() {
        mobileSearchPanel.classList.remove('active');
    }

    function toggleMobileFilters() {
        mobileFilterPanel.classList.toggle('active');
    }

    function closeMobileFilters() {
        mobileFilterPanel.classList.remove('active');
    }

    function updateFilterCount() {
        if (!filterCount) return;
        let count = 0;
        if (enhancedCurrentFilters.subjectFilter !== 'all') count++;
        if (enhancedCurrentFilters.sortBy !== 'code') count++;
        if (enhancedCurrentFilters.search !== '') count++;
        if (enhancedCurrentFilters.section !== 'all') count++; // ADD: Count section filter
        filterCount.textContent = count;
    }

    // Load subjects for enhanced modal
    function loadEnhancedEnrollmentSubjects() {
        showLoadingState();
        
        fetch('/student/enrollment/subjects', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Enhanced enrollment data received:', data);
            
            if (data.success) {
                // Convert subjects object to array if needed
                let subjectsArray = data.subjects;
                if (subjectsArray && typeof subjectsArray === 'object' && !Array.isArray(subjectsArray)) {
                    console.log('Converting subjects object to array');
                    subjectsArray = Object.values(subjectsArray);
                }
                
                // Create a new data object with the array
                const processedData = {
                    ...data,
                    subjects: subjectsArray || []
                };
                
                enhancedAllSubjects = processedData.subjects || [];
                enhancedCompletedSubjects = data.completed_subjects || [];
                enhancedMaxUnits = data.total_units || 0;
                originalMaxUnits = data.total_units || 0;
                
                console.log('Max units set to:', enhancedMaxUnits);
                console.log('Completed subjects stored:', enhancedCompletedSubjects.length);
                
                displayEnhancedSubjects(processedData);
            } else {
                showEnhancedError('Failed to load subjects: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error loading subjects in enhanced modal:', error);
            showEnhancedError('Network error. Please check your connection and try again. Error: ' + error.message);
        });
    }

    // displayEnhancedSubjects function to include section selection
    function displayEnhancedSubjects(data) {
        console.log('Displaying enhanced subjects with data:', data);
        
        if (!data || typeof data !== 'object') {
            console.error('Invalid data received:', data);
            showEnhancedError('Invalid response from server');
            return;
        }

        // Clear available sections
        availableSections.clear();

        // Ensure subjects is an array
        let subjects = data.subjects || [];
        if (subjects && typeof subjects === 'object' && !Array.isArray(subjects)) {
            console.log('Converting subjects object to array in displayEnhancedSubjects');
            subjects = Object.values(subjects);
        }

        // Update header information
        if (enrollmentYearLevel) enrollmentYearLevel.textContent = data.year_level || '-';
        if (enrollmentSemester) enrollmentSemester.textContent = data.semester || '-';
        if (enrollmentStudentType) enrollmentStudentType.textContent = data.is_regular ? 'Regular' : 'Irregular';
        if (currentYearLevel) currentYearLevel.textContent = data.year_level || '-';
        
        // Update student type badge
        if (studentTypeBadge) {
            studentTypeBadge.textContent = data.is_regular ? 'Regular Student' : 'Irregular Student';
            studentTypeBadge.className = 'enhanced-student-badge ' + (data.is_regular ? 'regular' : 'irregular');
        }

        enhancedIsRegular = data.is_regular;
        
        // UPDATE MAX UNITS DISPLAY
        if (maxUnitsElement) {
            maxUnitsElement.textContent = enhancedMaxUnits;
        }
        
        // USE PASSED SUBJECTS FROM DATA
        const passedSubjects = data.passed_subjects || [];
        const failedSubjects = data.failed_subjects || [];
        
        if (subjects.length === 0) {
            showEnhancedEmptyState();
            return;
        }

        hideEnhancedEmptyState();

        let subjectsHTML = '';
        
        subjects.forEach(subject => {
            console.log('Processing enhanced subject:', subject);
            
            if (!subject || !subject.id || !subject.code) {
                console.warn('Invalid subject skipped:', subject);
                return;
            }

            // Collect all available sections for this subject
            if (subject.schedules && subject.schedules.length > 0) {
                subject.schedules.forEach(schedule => {
                    if (schedule.section) {
                        availableSections.add(schedule.section);
                    }
                });
            }

            // Use the prerequisite information from server
            const hasPrerequisites = subject.has_prerequisites;
            const prerequisitesMet = subject.prerequisites_met;
            const isFailedSubject = subject.is_failed_subject;
            const previousGrade = subject.previous_grade;
            
            // For failed subjects, they are always selectable (need to be retaken)
            // For other subjects, check prerequisites
            const isSelectable = isFailedSubject ? true : (enhancedIsRegular || (!hasPrerequisites || prerequisitesMet));
            
            // Check if this subject is already selected - FIX: Use Map correctly
            const selectedData = enhancedSelectedSubjects.get(subject.id.toString());
            const isSelected = !!selectedData;
            
            // Get available sections for this subject
            const subjectSections = subject.schedules ? 
                [...new Set(subject.schedules.map(s => s.section).filter(Boolean))] : 
                ['A']; // Default section if none specified
            
            // Default to first available section
            const defaultSection = subjectSections[0] || 'A';
            const currentSection = selectedData ? selectedData.section : defaultSection;

            subjectsHTML += `
                <div class="enhanced-subject-card ${isSelected ? 'selected' : ''} ${!isSelectable ? 'disabled' : ''} ${isFailedSubject ? 'failed-subject' : ''}" data-id="${subject.id}">
                    <div class="enhanced-subject-header">
                        <div class="enhanced-subject-code">${subject.code}</div>
                        <div class="enhanced-subject-meta">
                            <span class="enhanced-subject-units">${subject.units || 0} units</span>
                            <span class="enhanced-subject-level">${subject.year_level || ''} - ${subject.semester || ''}</span>
                        </div>
                    </div>
                    <div class="enhanced-subject-name">${subject.name || 'No name'}</div>
                    <div class="enhanced-subject-description">${subject.description || 'No description available'}</div>
                    
                    <!-- Section Selection -->
                    <div class="enhanced-subject-section-selection">
                        <label for="enhanced_subject_section_${subject.id}">Select Section:</label>
                        <select id="enhanced_subject_section_${subject.id}" class="enhanced-section-select" 
                                ${!isSelectable ? 'disabled' : ''} 
                                onchange="enhancedChangeSection(${subject.id}, this.value)">
                            ${subjectSections.map(section => `
                                <option value="${section}" ${section === currentSection ? 'selected' : ''}>
                                    Section ${section}
                                </option>
                            `).join('')}
                        </select>
                    </div>
                    
                    ${subject.schedules && subject.schedules.length > 0 ? `
                        <div class="enhanced-subject-schedule">
                            <div class="enhanced-schedule-header">
                                <i class="fas fa-calendar-alt"></i>
                                <strong>Available Schedules:</strong>
                            </div>
                            ${subject.schedules.map(schedule => `
                                <div class="enhanced-schedule-item ${schedule.section === currentSection ? 'active-section' : ''}">
                                    <span class="enhanced-schedule-badge">
                                        <i class="fas fa-users"></i>
                                        Section ${schedule.section || 'A'} - 
                                        ${schedule.day} ${schedule.start_time} - ${schedule.end_time} (${schedule.room})
                                    </span>
                                </div>
                            `).join('')}
                        </div>
                    ` : ''}
                    
                    ${isFailedSubject ? `
                        <div class="enhanced-failed-subject-info">
                            <i class="fas fa-redo-alt"></i>
                            <strong>Needs Retaking:</strong> 
                            Previous grade: ${previousGrade}
                            <br><small>This subject must be retaken to meet program requirements</small>
                        </div>
                    ` : ''}
                    
                    ${!isFailedSubject && hasPrerequisites && !prerequisitesMet ? `
                        <div class="enhanced-prerequisite-info failed">
                            <i class="fas fa-exclamation-circle"></i>
                            <strong>Cannot enroll:</strong> 
                            Prerequisites not met: ${subject.prerequisites.map(p => p.code).join(', ')}
                            <br><small>You must pass these prerequisites with a grade of 3.0 or better</small>
                        </div>
                    ` : ''}
                    
                    ${!isFailedSubject && !enhancedIsRegular && hasPrerequisites && prerequisitesMet ? `
                        <div class="enhanced-prerequisite-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Ready to enroll!</strong> 
                            Prerequisites completed: ${subject.prerequisites.map(p => p.code).join(', ')}
                        </div>
                    ` : ''}
                    
                    ${!isFailedSubject && !hasPrerequisites ? `
                        <div class="enhanced-prerequisite-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Ready to enroll!</strong> No prerequisites required
                        </div>
                    ` : ''}
                    
                    <div class="enhanced-subject-footer">
                        <div class="enhanced-subject-checkbox">
                            <input type="checkbox" 
                                id="enhanced_subject_${subject.id}" 
                                ${isSelected ? 'checked' : ''}
                                ${!isSelectable ? 'disabled' : ''}
                                onchange="enhancedToggleSubject(${subject.id}, ${subject.units || 0}, ${!isSelectable}, '${currentSection}')">
                            <label for="enhanced_subject_${subject.id}" class="enhanced-checkbox-label">
                                ${isSelectable ? (isFailedSubject ? 'Retake Subject' : 'Select for Enrollment') : 'Prerequisites Not Met'}
                            </label>
                        </div>
                    </div>
                </div>
            `;
        });
        
        subjectsGrid.innerHTML = subjectsHTML;
        
        // Populate section filter dropdown
        updateSectionFilter();
        
        console.log('Subjects rendered. Current selection count:', enhancedSelectedSubjects.size);
        console.log('Current total units:', enhancedTotalUnits);
    }

    // Function to update section filter dropdown
    function updateSectionFilter() {
        if (!sectionFilter) return;
        
        // Clear existing options except "All Sections"
        while (sectionFilter.options.length > 1) {
            sectionFilter.remove(1);
        }
        
        // Add available sections
        const sortedSections = Array.from(availableSections).sort();
        sortedSections.forEach(section => {
            const option = document.createElement('option');
            option.value = section;
            option.textContent = `Section ${section}`;
            sectionFilter.appendChild(option);
        });
        
        // Also update mobile section filter if it exists
        if (mobileSectionFilter) {
            while (mobileSectionFilter.options.length > 1) {
                mobileSectionFilter.remove(1);
            }
            sortedSections.forEach(section => {
                const option = document.createElement('option');
                option.value = section;
                option.textContent = `Section ${section}`;
                mobileSectionFilter.appendChild(option);
            });
        }
    }

    // Function to change section for a subject
    window.enhancedChangeSection = function(subjectId, section) {
        const selectedData = enhancedSelectedSubjects.get(subjectId.toString());
        if (selectedData) {
            // Update the section in the selected subjects map
            enhancedSelectedSubjects.set(subjectId.toString(), {
                subjectId: subjectId,
                section: section,
                units: selectedData.units
            });
            
            // Update the visual indication of active section
            const subjectElement = document.querySelector(`.enhanced-subject-card[data-id="${subjectId}"]`);
            if (subjectElement) {
                const scheduleItems = subjectElement.querySelectorAll('.enhanced-schedule-item');
                scheduleItems.forEach(item => {
                    const badge = item.querySelector('.enhanced-schedule-badge');
                    if (badge && badge.textContent.includes(`Section ${section}`)) {
                        item.classList.add('active-section');
                    } else {
                        item.classList.remove('active-section');
                    }
                });
            }
            
            console.log(`Changed section for subject ${subjectId} to ${section}`);
        }
    };

    // FIXED: Enhanced toggle subject function with proper section handling
    window.enhancedToggleSubject = function(subjectId, units, isDisabled, section = 'A') {
        console.log(`Enhanced toggle subject called: ${subjectId}, units: ${units}, isDisabled: ${isDisabled}, section: ${section}`);
        
        if (isDisabled) {
            const checkbox = document.getElementById(`enhanced_subject_${subjectId}`);
            if (checkbox) {
                checkbox.checked = false;
            }
            return;
        }
        
        const checkbox = document.getElementById(`enhanced_subject_${subjectId}`);
        if (!checkbox) {
            console.error('Enhanced checkbox not found for subject:', subjectId);
            return;
        }
        
        const subjectItem = checkbox.closest('.enhanced-subject-card');
        const isNowChecked = checkbox.checked;
        
        // Get the selected section from the dropdown
        const sectionSelect = document.getElementById(`enhanced_subject_section_${subjectId}`);
        const selectedSection = sectionSelect ? sectionSelect.value : section;
        
        console.log(`Enhanced subject ${subjectId} is now ${isNowChecked ? 'checked' : 'unchecked'}, section: ${selectedSection}, current totalUnits: ${enhancedTotalUnits}`);
        
        if (isNowChecked) {
            // Checkbox was just CHECKED - ADD subject
            const newTotal = enhancedTotalUnits + parseInt(units);
            console.log(`Would be ${newTotal} units, max is ${enhancedMaxUnits}`);
            
            if (newTotal > enhancedMaxUnits) {
                showNotification(`Cannot exceed maximum of ${enhancedMaxUnits} units for this semester. Current: ${enhancedTotalUnits} units`, 'error');
                checkbox.checked = false;
                return;
            }
            
            // FIX: Store subject with section data in Map
            enhancedSelectedSubjects.set(subjectId.toString(), {
                subjectId: subjectId,
                section: selectedSection,
                units: parseInt(units)
            });
            enhancedTotalUnits = newTotal;
            if (subjectItem) subjectItem.classList.add('selected');
            console.log(`ENHANCED SELECTED subject ${subjectId}, section ${selectedSection}, added ${units} units. Total: ${enhancedTotalUnits}`);
        } else {
            // Checkbox was just UNCHECKED - REMOVE subject
            if (enhancedSelectedSubjects.has(subjectId.toString())) {
                enhancedSelectedSubjects.delete(subjectId.toString());
                enhancedTotalUnits -= parseInt(units);
                console.log(`ENHANCED DESELECTED subject ${subjectId}, removed ${units} units. Total: ${enhancedTotalUnits}`);
            }
            if (subjectItem) subjectItem.classList.remove('selected');
        }
        
        // Update the UI
        updateEnhancedSelectionInfo();
        
        console.log('Enhanced currently selected subjects:', Array.from(enhancedSelectedSubjects.entries()));
        console.log('Enhanced total units:', enhancedTotalUnits);
    };

    // FIXED: filter and display function to handle section filtering properly
    function filterAndDisplaySubjects() {
        updateFilterCount();
        
        let filteredSubjects = enhancedAllSubjects;
        
        // FIX: Apply section filter first and properly
        if (enhancedCurrentFilters.section !== 'all') {
            filteredSubjects = filteredSubjects.filter(subject => {
                if (!subject.schedules || subject.schedules.length === 0) return false;
                return subject.schedules.some(schedule => schedule.section === enhancedCurrentFilters.section);
            });
        }
        
        // Apply subject type filter
        if (enhancedCurrentFilters.subjectFilter !== 'all') {
            filteredSubjects = filteredSubjects.filter(subject => {
                if (enhancedCurrentFilters.subjectFilter === 'available') {
                    const hasPrerequisites = subject.prerequisites && subject.prerequisites.length > 0;
                    const prerequisitesMet = hasPrerequisites ? 
                        subject.prerequisites.every(prereq => enhancedCompletedSubjects.includes(prereq.id)) : 
                        true;
                    return enhancedIsRegular || (!hasPrerequisites || prerequisitesMet);
                } else if (enhancedCurrentFilters.subjectFilter === 'with-prerequisites') {
                    return subject.prerequisites && subject.prerequisites.length > 0;
                }
                return true;
            });
        }
        
        // Apply search filter
        if (enhancedCurrentFilters.search) {
            const searchTerm = enhancedCurrentFilters.search.toLowerCase();
            filteredSubjects = filteredSubjects.filter(subject => 
                subject.code.toLowerCase().includes(searchTerm) ||
                subject.name.toLowerCase().includes(searchTerm) ||
                (subject.description && subject.description.toLowerCase().includes(searchTerm))
            );
        }
        
        // Apply sorting
        filteredSubjects.sort((a, b) => {
            switch (enhancedCurrentFilters.sortBy) {
                case 'name':
                    return (a.name || '').localeCompare(b.name || '');
                case 'units':
                    return (b.units || 0) - (a.units || 0);
                case 'code':
                default:
                    return (a.code || '').localeCompare(b.code || '');
            }
        });
        
        // Create a mock data object for display
        const displayData = {
            subjects: filteredSubjects,
            is_regular: enhancedIsRegular,
            year_level: enrollmentYearLevel ? enrollmentYearLevel.textContent : '-',
            semester: enrollmentSemester ? enrollmentSemester.textContent : '-',
            total_units: enhancedMaxUnits,
            completed_subjects: enhancedCompletedSubjects,
            passed_subjects: [],
            failed_subjects: []
        };
        
        displayEnhancedSubjects(displayData);
    }

    // Update selection information
    function updateEnhancedSelectionInfo() {
        const count = enhancedSelectedSubjects.size;
        const units = enhancedTotalUnits;
        
        if (selectedCount) selectedCount.textContent = `${count} selected`;
        if (totalUnits) totalUnits.textContent = units;
        if (selectionSummary) selectionSummary.textContent = `${count} subjects`;
        if (unitsSummary) unitsSummary.textContent = `${units} units`;
        if (submitCount) submitCount.textContent = count;
        
        // Update next step button state
        if (nextStepBtn) {
            nextStepBtn.disabled = count === 0 || units > enhancedMaxUnits;
        }
        
        // Update save button state
        if (submitBtn) {
            submitBtn.disabled = count === 0 || units > enhancedMaxUnits || !isFheUploaded;
            
            // Add warning if over unit limit
            if (units > enhancedMaxUnits) {
                submitBtn.title = `Maximum ${enhancedMaxUnits} units allowed. Current: ${units} units`;
            } else {
                submitBtn.title = '';
            }
        }
    }

    // FIXED: Select all visible subjects with proper section handling
    function selectAllVisible() {
        const visibleSubjectElements = subjectsGrid.querySelectorAll('.enhanced-subject-card:not(.disabled)');
        let selectedCount = 0;
        
        visibleSubjectElements.forEach(card => {
            const subjectId = card.getAttribute('data-id');
            const checkbox = card.querySelector('input[type="checkbox"]');
            const sectionSelect = card.querySelector('.enhanced-section-select');
            const subject = enhancedAllSubjects.find(s => s.id == subjectId);
            
            if (subject && checkbox && !checkbox.checked) {
                // Get the current section from dropdown
                const currentSection = sectionSelect ? sectionSelect.value : 'A';
                
                // Calculate new total units
                const newTotal = enhancedTotalUnits + parseInt(subject.units || 0);
                
                // Check if we can add this subject without exceeding max units
                if (newTotal <= enhancedMaxUnits) {
                    checkbox.checked = true;
                    
                    // Store in Map with section data
                    enhancedSelectedSubjects.set(subjectId.toString(), {
                        subjectId: parseInt(subjectId),
                        section: currentSection,
                        units: parseInt(subject.units || 0)
                    });
                    
                    enhancedTotalUnits = newTotal;
                    card.classList.add('selected');
                    selectedCount++;
                    
                    console.log(`Select All: Added subject ${subjectId}, section ${currentSection}, units ${subject.units}`);
                }
            }
        });
        
        if (selectedCount > 0) {
            updateEnhancedSelectionInfo();
            console.log(`Select All completed: ${selectedCount} subjects selected`);
        } else {
            showNotification('No available subjects can be selected without exceeding unit limit', 'warning');
        }
    }

    // FIXED: Deselect all visible subjects
    function deselectAllVisible() {
        const visibleSubjectElements = subjectsGrid.querySelectorAll('.enhanced-subject-card');
        
        visibleSubjectElements.forEach(card => {
            const subjectId = card.getAttribute('data-id');
            const checkbox = card.querySelector('input[type="checkbox"]');
            const subject = enhancedAllSubjects.find(s => s.id == subjectId);
            
            if (subject && checkbox && checkbox.checked) {
                checkbox.checked = false;
                
                // Remove from Map if exists
                if (enhancedSelectedSubjects.has(subjectId.toString())) {
                    const selectedData = enhancedSelectedSubjects.get(subjectId.toString());
                    enhancedSelectedSubjects.delete(subjectId.toString());
                    enhancedTotalUnits -= parseInt(selectedData.units);
                }
                
                card.classList.remove('selected');
            }
        });
        
        updateEnhancedSelectionInfo();
        console.log('Deselect All completed');
    }

    // FIXED: Submit enrollment with proper section data
    function submitEnrollment() {
        console.log('Enhanced submit enrollment clicked');
        console.log('Enhanced selected subjects count:', enhancedSelectedSubjects.size);
        console.log('Enhanced FHE file:', fheFile);
        
        if (enhancedSelectedSubjects.size === 0) {
            showNotification('Please select at least one subject', 'error');
            return;
        }
        
        if (!isFheUploaded || !fheFile) {
            showNotification('Please upload your FHE file before submitting', 'error');
            return;
        }
        
        if (enhancedTotalUnits > enhancedMaxUnits) {
            showNotification(`Cannot exceed maximum of ${enhancedMaxUnits} units. Current: ${enhancedTotalUnits} units`, 'error');
            return;
        }
        
        const submitBtn = document.getElementById('enhancedSubmitEnrollment');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
        
        // Create FormData to handle file upload
        const formData = new FormData();
        
        // Convert Map to Array for submission - now including section data
        const subjectsArray = Array.from(enhancedSelectedSubjects.values()).map(item => ({
            subjectId: item.subjectId,
            section: item.section
        }));
        
        // Append data to FormData
        formData.append('subjects', JSON.stringify(subjectsArray));
        formData.append('fhe_file', fheFile);
        formData.append('total_units', enhancedTotalUnits.toString());
        
        console.log('Enhanced submitting enrollment with subjects:', subjectsArray);
        console.log('Enhanced total units to submit:', enhancedTotalUnits);
        console.log('Enhanced FHE file:', fheFile.name);
        
        fetch('/student/enrollment/enroll', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => {
            console.log('Enhanced response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Enhanced enrollment response:', data);
            if (data.success) {
                showNotification(data.message || 'Enrollment submitted successfully!', 'success');
                closeModal();
                
                // Refresh the page or update UI as needed
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showNotification(data.message || 'Enrollment failed. Please try again.', 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Enhanced enrollment error:', error);
            showNotification('Enrollment failed. Please try again. Error: ' + error.message, 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }

    function openModal() {
        if (modal) {
            modal.classList.add('active');
            loadEnhancedEnrollmentSubjects();
            // Reset step to 1 when opening modal
            currentStep = 1;
            updateStepNavigation();
        }
    }

    function closeModal() {
        if (modal) {
            modal.classList.remove('active');
            enhancedSelectedSubjects.clear();
            enhancedTotalUnits = 0;
            fheFile = null;
            isFheUploaded = false;
            removeFheFile(); // Reset FHE file state
            updateEnhancedSelectionInfo();
            // Reset to step 1
            currentStep = 1;
            updateStepNavigation();
        }
    }

    // Utility functions for enhanced modal
    function showLoadingState() {
        if (subjectsGrid) {
            subjectsGrid.innerHTML = `
                <div class="enhanced-enrollment-loading-state">
                    <div class="enhanced-loading-spinner"></div>
                    <p>Loading available subjects...</p>
                </div>
            `;
        }
    }

    function showEnhancedEmptyState() {
        const emptyState = document.querySelector('.enhanced-enrollment-empty-state');
        if (emptyState) {
            emptyState.style.display = 'flex';
        }
        if (subjectsGrid) {
            subjectsGrid.style.display = 'none';
        }
    }

    function hideEnhancedEmptyState() {
        const emptyState = document.querySelector('.enhanced-enrollment-empty-state');
        if (emptyState) {
            emptyState.style.display = 'none';
        }
        if (subjectsGrid) {
            subjectsGrid.style.display = 'grid';
        }
    }

    function showEnhancedError(message) {
        if (subjectsGrid) {
            subjectsGrid.innerHTML = `
                <div class="enhanced-enrollment-empty-state">
                    <div class="enhanced-empty-state-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h4>Error Loading Subjects</h4>
                    <p>${message}</p>
                    <button class="enhanced-btn-primary" onclick="loadEnhancedEnrollmentSubjects()">
                        <i class="fas fa-redo"></i>
                        Try Again
                    </button>
                </div>
            `;
        }
    }

    // Make functions available globally
    window.loadEnhancedEnrollmentSubjects = loadEnhancedEnrollmentSubjects;
    window.openEnhancedEnrollmentModal = openModal;
    window.closeEnhancedEnrollmentModal = closeModal;

    return {
        init,
        openModal,
        closeModal,
        modal: modal
    };
}

document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar on mobile
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const searchInput = document.querySelector('.search-input');
    const searchBtn = document.querySelector('.search-btn');
    const searchBar = document.querySelector('.search-bar');

    // Initialize irregular modal
    const irregularModal = initializeIrregularModal();

    // Initialize enhanced enrollment modal
    const enhancedEnrollmentModal = initializeEnhancedEnrollmentModal();
    enhancedEnrollmentModal.init();

    // Make sure the modal is initialized when opened via button
    const openModalBtn = document.querySelector('[data-target="irregularSubjectsModal"]');
    if (openModalBtn) {
        openModalBtn.addEventListener('click', function() {
            irregularModal.loadAllSubjects();
        });
    }

    // Check irregular student status on page load
    checkIrregularStudent(irregularModal);

    // Initialize other components
    initializeProfilePictureUpload();
    initializeTooltips();
    initializeStudentInfoModal();

    const studentInfoModal = document.getElementById('studentInfoModal');
    
    if (studentInfoModal && studentInfoModal.classList.contains('active')) {
        populateCurriculumDropdown();
    }

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

    // Enrollment functionality
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

    // Updated enroll now button to use enhanced modal
    const enrollNowBtn = document.getElementById('d-stat-card-enroll');
    if (enrollNowBtn) {
        const studentStatus = document.getElementById('student-status')?.value;
        const isEnrollmentActive = document.getElementById('is-enrollment-active')?.value === '1';
        const hasEnrollmentPeriod = document.getElementById('enrollment-period')?.value === '1';
        
        console.log('Enrollment button conditions:', {
            studentStatus,
            isEnrollmentActive,
            hasEnrollmentPeriod
        });
        
        // Check if enrollment should be disabled
        if (studentStatus === 'None' || !isEnrollmentActive || !hasEnrollmentPeriod) {
            enrollNowBtn.style.cursor = 'not-allowed';
            enrollNowBtn.style.opacity = '0.6';
            
            enrollNowBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (studentStatus === 'None') {
                    showNotification('Your student status is not eligible for enrollment.', 'error');
                } else if (!hasEnrollmentPeriod) {
                    showNotification('There is no active enrollment period at the moment.', 'error');
                } else if (!isEnrollmentActive) {
                    showNotification('Enrollment is currently not active. Please check the enrollment dates.', 'error');
                }
            });
        } else {
            // Enable enrollment functionality
            enrollNowBtn.style.cursor = 'pointer';
            enrollNowBtn.style.opacity = '1';
            
            enrollNowBtn.addEventListener('click', function() {
                const isIrregular = document.getElementById('is-regular').value == 2;
                console.log('Enroll Now clicked - Is irregular:', isIrregular);
                
                if (isIrregular) {
                    // For irregular students, check if they have submitted past subjects
                    console.log('Checking past subjects for irregular student...');
                    
                    fetch('/student/enrollment/check-past-subjects', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Past subjects check response:', data);
                        if (data.success) {
                            if (data.has_submitted) {
                                // Student has submitted past subjects, proceed with enhanced enrollment
                                console.log('Irregular student has submitted past subjects, opening enhanced enrollment modal');
                                enhancedEnrollmentModal.openModal();
                            } else {
                                // Student hasn't submitted past subjects, show the irregular modal
                                console.log('Irregular student needs to submit past subjects first');
                                if (window.irregularModal) {
                                    window.irregularModal.loadAllSubjects();
                                    window.irregularModal.modal.classList.add('active');
                                }
                            }
                        } else {
                            console.error('Error checking past subjects:', data.message);
                            showNotification('Error checking enrollment status: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error checking past subjects:', error);
                        showNotification('Network error checking enrollment status. Please try again.', 'error');
                    });
                } else {
                    // Regular student - proceed with enhanced modal
                    console.log('Regular student, opening enhanced enrollment modal');
                    enhancedEnrollmentModal.openModal();
                }
            });
        }
    }

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

    // Call this after the enrollment modal opens
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
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Enrollment data received:', data);
            
            if (data.success) {
                // Convert subjects object to array if needed
                let subjectsArray = data.subjects;
                if (subjectsArray && typeof subjectsArray === 'object' && !Array.isArray(subjectsArray)) {
                    console.log('Converting subjects object to array');
                    subjectsArray = Object.values(subjectsArray);
                }
                
                // Create a new data object with the array
                const processedData = {
                    ...data,
                    subjects: subjectsArray || []
                };
                
                displaySubjects(processedData);
            } else {
                showEnrollmentError('Failed to load subjects: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error loading subjects:', error);
            showEnrollmentError('Network error. Please check your connection and try again. Error: ' + error.message);
        });
    }

    // Display subjects in the modal - ENHANCED VERSION
    function displaySubjects(data) {
        console.log('Displaying subjects with data:', data);
        
        // Check if data has the expected structure
        if (!data || typeof data !== 'object') {
            console.error('Invalid data received:', data);
            showEnrollmentError('Invalid response from server');
            return;
        }

        // Ensure subjects is an array
        let subjects = data.subjects;
        if (subjects && typeof subjects === 'object' && !Array.isArray(subjects)) {
            console.log('Converting subjects object to array in displaySubjects');
            subjects = Object.values(subjects);
        } else if (!subjects || !Array.isArray(subjects)) {
            subjects = [];
        }

        enrollmentYearLevel.textContent = data.year_level || '-';
        enrollmentSemester.textContent = data.semester || '-';
        enrollmentStudentType.textContent = data.is_regular ? 'Regular' : 'Irregular';
        isRegular = data.is_regular;
        
        const completedSubjects = data.completed_subjects || [];
        
        if (subjects.length === 0) {
            subjectsList.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-book-open"></i>
                    <span>No subjects available for enrollment.</span>
                    <small>This could be because you have already enrolled in all available subjects or don't meet prerequisites.</small>
                </div>
            `;
            return;
        }
        
        let subjectsHTML = '';
        let totalAvailableUnits = 0;
        
        subjects.forEach(subject => {
            console.log('Processing subject:', subject);
            
            // Make sure subject has required properties
            if (!subject || !subject.id || !subject.code) {
                console.warn('Invalid subject skipped:', subject);
                return; // Skip invalid subjects
            }

            const hasPrerequisites = subject.prerequisites && subject.prerequisites.length > 0;
            const prerequisitesMet = hasPrerequisites ? 
                subject.prerequisites.every(prereq => completedSubjects.includes(prereq.id)) : 
                true;
            
            const isSelectable = isRegular || (!hasPrerequisites || prerequisitesMet);
            const isAutoSelected = isRegular;
            
            // Calculate total available units for display
            totalAvailableUnits += parseInt(subject.units || 0);
            
            if (isAutoSelected) {
                selectedSubjects.add(subject.id);
                totalUnits += parseInt(subject.units || 0);
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
                            onchange="toggleSubject(${subject.id}, ${subject.units || 0}, ${!isSelectable})"
                        >
                    </div>
                    <div class="subject-info">
                        <div class="subject-header">
                            <span class="subject-code">${subject.code}</span>
                            <span class="subject-units">${subject.units || 0} units</span>
                            <span class="subject-level">${subject.year_level || ''} - ${subject.semester || ''}</span>
                        </div>
                        <div class="subject-name">${subject.name || 'No name'}</div>
                        <div class="subject-description">${subject.description || 'No description available'}</div>
                        
                        ${subject.schedules && subject.schedules.length > 0 ? `
                            <div class="subject-schedule">
                                <strong>Schedule:</strong>
                                ${subject.schedules.map(schedule => `
                                    <span class="schedule-badge">
                                        ${schedule.day} ${schedule.start_time} - ${schedule.end_time} (${schedule.room})
                                    </span>
                                `).join('')}
                            </div>
                        ` : ''}
                        
                        ${hasPrerequisites && !prerequisitesMet ? `
                            <div class="enhanced-prerequisite-info failed">
                            <i class="fas fa-exclamation-circle"></i>
                            <strong>Cannot enroll:</strong> 
                            Prerequisites not met or failed: ${subject.prerequisites.map(p => p.code).join(', ')}
                            <br><small>You must pass these prerequisites with a grade of 3.0 or better</small>
                        </div>
                    ` : ''}
                        
                        ${!isRegular && hasPrerequisites && prerequisitesMet ? `
                            <div class="prerequisite-success">
                                <i class="fas fa-check-circle"></i>
                                Prerequisites met: ${subject.prerequisites.map(p => p.code).join(', ')}
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });
        
        subjectsList.innerHTML = subjectsHTML;
        updateUnitsCounter();
        submitEnrollment.disabled = selectedSubjects.size === 0;

        if (isRegular) {
            selectedSubjects.forEach(subjectId => {
                const checkbox = document.getElementById(`subject_${subjectId}`);
                if (checkbox) {
                    const subjectItem = checkbox.closest('.subject-item');
                    subjectItem.classList.add('selected');
                }
            });
        }

        if (!isRegular) {
            // For irregular students, clear any previous selection and reset units
            selectedSubjects.clear();
            totalUnits = 0;
            
            // Also uncheck all checkboxes visually
            const checkboxes = subjectsList.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
                const subjectItem = checkbox.closest('.subject-item');
                if (subjectItem) {
                    subjectItem.classList.remove('selected');
                }
            });
            
            updateUnitsCounter();
            submitEnrollment.disabled = true;
            console.log('Reset selection state for irregular student - totalUnits:', totalUnits);
        }
        
        // Add info about total available units
        if (subjects.length > 0) {
            const infoElement = document.createElement('div');
            infoElement.className = 'available-units-info';
            infoElement.innerHTML = `<small>Total available units for selection: ${totalAvailableUnits}</small>`;
            subjectsList.appendChild(infoElement);
        }
    }

    // Toggle subject selection
    window.toggleSubject = function(subjectId, units, isDisabled) {
        const maxUnits = data.total_units || 23;
        console.log(`Toggle subject called: ${subjectId}, units: ${units}, isDisabled: ${isDisabled}`);
        
        if (isDisabled) {
            const checkbox = document.getElementById(`subject_${subjectId}`);
            if (checkbox) {
                checkbox.checked = false;
            }
            return;
        }
        
        const checkbox = document.getElementById(`subject_${subjectId}`);
        if (!checkbox) {
            console.error('Checkbox not found for subject:', subjectId);
            return;
        }
        
        const subjectItem = checkbox.closest('.subject-item');
        const isNowChecked = checkbox.checked; // This is the NEW state after the click
        
        console.log(`Subject ${subjectId} is now ${isNowChecked ? 'checked' : 'unchecked'}, current totalUnits: ${totalUnits}`);
        
        if (isNowChecked) {
            // Checkbox was just CHECKED - ADD subject
            const newTotal = totalUnits + parseInt(units);
            if (newTotal > maxUnits) {
                showNotification(`Cannot exceed maximum of ${maxUnits} units for this semester. Current: ${totalUnits} units`, 'error');
                checkbox.checked = false; // Uncheck it since we can't add
                return;
            }
            
            selectedSubjects.add(subjectId);
            totalUnits = newTotal;
            subjectItem.classList.add('selected');
            console.log(`SELECTED subject ${subjectId}, added ${units} units. Total: ${totalUnits}`);
        } else {
            // Checkbox was just UNCHECKED - REMOVE subject
            if (selectedSubjects.has(subjectId)) {
                selectedSubjects.delete(subjectId);
                totalUnits -= parseInt(units);
                console.log(`DESELECTED subject ${subjectId}, removed ${units} units. Total: ${totalUnits}`);
            }
            subjectItem.classList.remove('selected');
        }
        
        // Update the UI
        updateUnitsCounter();
        submitEnrollment.disabled = selectedSubjects.size === 0;
        
        console.log('Currently selected subjects:', Array.from(selectedSubjects));
        console.log('Total units:', totalUnits);
    };

    // Update units counter
    function updateUnitsCounter() {
        console.log('Updating units counter, current totalUnits:', totalUnits);
        
        // Ensure totalUnits is never negative and is a valid number
        if (isNaN(totalUnits) || totalUnits < 0) {
            console.warn('Invalid totalUnits value, resetting to 0. Previous value:', totalUnits);
            totalUnits = 0;
        }
        
        const unitCounterElement = document.getElementById('totalUnitsCounter');
        if (unitCounterElement) {
            unitCounterElement.textContent = totalUnits;
            console.log('Updated unit counter to:', totalUnits);
            
            // Also update any other elements that might display total units
            const allUnitElements = document.querySelectorAll('[data-unit-counter]');
            allUnitElements.forEach(element => {
                element.textContent = totalUnits;
            });
        } else {
            console.error('Unit counter element not found!');
        }
    }

    // Show enrollment error
    function showEnrollmentError(message) {
        subjectsList.innerHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-circle"></i>
                <span>${message}</span>
                <small>Please check your connection and try again. If the problem persists, contact support.</small>
                <button class="btn-primary mt-3" onclick="loadEnrollmentSubjects()">
                    <i class="fas fa-redo"></i>
                    Try Again
                </button>
            </div>
        `;
    }

    // Submit enrollment
    submitEnrollment.addEventListener('click', function() {
        console.log('Submit enrollment clicked');
        console.log('Selected subjects count:', selectedSubjects.size);
        console.log('Selected subjects:', Array.from(selectedSubjects));
        console.log('Total units:', totalUnits);
        
        if (selectedSubjects.size === 0) {
            showNotification('Please select at least one subject', 'error');
            return;
        }
        
        const submitBtn = submitEnrollment;
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
        
        // Convert Set to Array for submission
        const subjectsArray = Array.from(selectedSubjects);
        
        console.log('Submitting enrollment with subjects:', subjectsArray);
        console.log('Total units to submit:', totalUnits);
        
        fetch('/student/enrollment/enroll', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                subjects: subjectsArray
            })
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Enrollment response:', data);
            if (data.success) {
                showNotification(data.message || 'Enrollment submitted successfully!', 'success');
                closeEnrollment();
                
                // Refresh the page or update UI as needed
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                showNotification(data.message || 'Enrollment failed. Please try again.', 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Enrollment error:', error);
            console.error('Error details:', error.message);
            showNotification('Enrollment failed. Please try again. Error: ' + error.message, 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Enhanced responsive behavior
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            sidebar.classList.remove('active');
        }
    });
});//End of DOMContentLoaded