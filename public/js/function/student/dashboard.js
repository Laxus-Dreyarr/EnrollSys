// Enhanced Irregular Student Modal Functionality
function initializeIrregularModal() {
    const modal = document.getElementById('irregularSubjectsModal');
    const categoriesList = document.getElementById('categoriesList');
    const subjectsList = document.getElementById('pastSubjectsList');
    const searchInput = document.getElementById('pastSubjectsSearch');
    const yearLevelFilter = document.getElementById('yearLevelFilter');
    const semesterFilter = document.getElementById('semesterFilter');
    const clearSearchBtn = document.getElementById('clearSearch');
    const expandAllBtn = document.getElementById('expandAllCategories');
    const selectAllBtn = document.getElementById('selectAllVisible');
    const deselectAllBtn = document.getElementById('deselectAllVisible');
    const saveBtn = document.getElementById('savePastSubjects');
    const selectedCount = document.getElementById('selectedCount');
    const completedCount = document.getElementById('completedCount');
    const totalUnits = document.getElementById('totalUnits');
    const selectionSummary = document.getElementById('selectionSummary');
    const currentCategoryTitle = document.getElementById('currentCategoryTitle');

    let irregularAllSubjects = [];
    let selectedPastSubjects = new Set();
    let currentCategory = 'all';
    let currentFilters = {
        yearLevel: 'all',
        semester: 'all',
        search: ''
    };

    // Load subjects and initialize modal
    function loadAllSubjects() {
        showLoadingState();
        
        console.log('Loading subjects for irregular modal...');
        
        fetch('/student/enrollment/all-subjects', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Subjects loaded for irregular modal:', data);
            if (data.success) {
                irregularAllSubjects = data.subjects;
                console.log('Total subjects loaded:', irregularAllSubjects.length);
                initializeCategories();
                displaySubjects(irregularAllSubjects);
                updateSelectionInfo();
            } else {
                showErrorState('Failed to load subjects: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error loading subjects:', error);
            showErrorState('Network error. Please try again.');
        });
    }

    // Initialize categories sidebar
    function initializeCategories() {
        if (irregularAllSubjects.length === 0) {
            console.warn('No subjects available for categories');
            return;
        }
        const categories = groupSubjectsByCategories(irregularAllSubjects);
        renderCategories(categories);
    }

    // Group subjects by year level and semester
    function groupSubjectsByCategories(subjects) {
        const categories = {};
        
        subjects.forEach(subject => {
            const yearLevel = subject.year_level;
            const semester = subject.semester;
            
            if (!categories[yearLevel]) {
                categories[yearLevel] = {};
            }
            
            if (!categories[yearLevel][semester]) {
                categories[yearLevel][semester] = [];
            }
            
            categories[yearLevel][semester].push(subject);
        });
        
        console.log('Categories grouped:', categories);
        return categories;
    }

    // Render categories in sidebar
    function renderCategories(categories) {
        if (Object.keys(categories).length === 0) {
            categoriesList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <span>No categories available</span>
                </div>
            `;
            return;
        }
        
        let categoriesHTML = '';
        
        // Add "All Subjects" option first
        categoriesHTML += `
            <div class="category-group">
                <div class="category-header active" data-category="all">
                    <div class="category-title">
                        <i class="fas fa-layer-group"></i>
                        All Subjects
                    </div>
                    <div class="category-count">${irregularAllSubjects.length}</div>
                    <i class="fas fa-chevron-down category-arrow"></i>
                </div>
            </div>
        `;
        
        Object.keys(categories).sort().forEach(yearLevel => {
            const yearSubjects = categories[yearLevel];
            let totalYearSubjects = 0;
            
            Object.values(yearSubjects).forEach(semesterSubjects => {
                totalYearSubjects += semesterSubjects.length;
            });
            
            categoriesHTML += `
                <div class="category-group">
                    <div class="category-header" data-year="${yearLevel}">
                        <div class="category-title">
                            <i class="fas fa-graduation-cap"></i>
                            ${yearLevel}
                        </div>
                        <div class="category-count">${totalYearSubjects}</div>
                        <i class="fas fa-chevron-down category-arrow"></i>
                    </div>
                    <div class="subcategories">
            `;
            
            Object.keys(yearSubjects).sort().forEach(semester => {
                const semesterSubjects = yearSubjects[semester];
                
                categoriesHTML += `
                    <div class="subcategory-item" data-year="${yearLevel}" data-semester="${semester}">
                        <div class="subcategory-name">
                            <i class="fas fa-calendar-alt"></i>
                            ${semester}
                        </div>
                        <div class="subcategory-count">${semesterSubjects.length}</div>
                    </div>
                `;
            });
            
            categoriesHTML += `
                    </div>
                </div>
            `;
        });
        
        categoriesList.innerHTML = categoriesHTML;
        attachCategoryEventListeners();
    }

    // Attach event listeners to categories
    function attachCategoryEventListeners() {
        console.log('Attaching category event listeners...');
        
        // Year level category headers - toggle expand/collapse
        document.querySelectorAll('.category-header').forEach(header => {
            header.addEventListener('click', function(e) {
                e.stopPropagation();
                const categoryType = this.getAttribute('data-category');
                const yearLevel = this.getAttribute('data-year');
                
                if (categoryType === 'all') {
                    // Handle "All Subjects" click
                    document.querySelectorAll('.category-header').forEach(h => h.classList.remove('active'));
                    document.querySelectorAll('.subcategory-item').forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                    
                    currentCategory = 'all';
                    currentCategoryTitle.textContent = 'All Subjects';
                    filterAndDisplaySubjects();
                    return;
                }
                
                // Toggle expand/collapse for year categories
                const subcategories = this.nextElementSibling;
                if (subcategories) {
                    this.classList.toggle('active');
                    subcategories.classList.toggle('expanded');
                    
                    const arrow = this.querySelector('.category-arrow');
                    if (arrow) {
                        arrow.style.transform = this.classList.contains('active') ? 'rotate(180deg)' : 'rotate(0deg)';
                    }
                }
            });
        });
        
        // Semester subcategory items - filter subjects
        document.querySelectorAll('.subcategory-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                const yearLevel = this.getAttribute('data-year');
                const semester = this.getAttribute('data-semester');
                
                console.log('Subcategory clicked:', yearLevel, semester);
                
                // Remove active state from all category headers and subcategories
                document.querySelectorAll('.category-header').forEach(h => h.classList.remove('active'));
                document.querySelectorAll('.subcategory-item').forEach(i => i.classList.remove('active'));
                
                // Add active state to clicked subcategory and its parent
                this.classList.add('active');
                const parentHeader = this.closest('.category-group').querySelector('.category-header');
                if (parentHeader) {
                    parentHeader.classList.add('active');
                    // Ensure the subcategories are expanded
                    const subcategories = parentHeader.nextElementSibling;
                    if (subcategories) {
                        subcategories.classList.add('expanded');
                        const arrow = parentHeader.querySelector('.category-arrow');
                        if (arrow) {
                            arrow.style.transform = 'rotate(180deg)';
                        }
                    }
                }
                
                // Update current category and display subjects
                currentCategory = `${yearLevel}|${semester}`;
                currentCategoryTitle.textContent = `${yearLevel} - ${semester}`;
                
                filterAndDisplaySubjects();
            });
        });
        
        console.log('Category event listeners attached');
    }

    // Display subjects based on current filters and category
    function displaySubjects(subjects) {
        console.log('Displaying subjects:', subjects.length);
        
        if (subjects.length === 0) {
            subjectsList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h4>No Subjects Found</h4>
                    <p>Try adjusting your search or filters.</p>
                </div>
            `;
            return;
        }
        
        let subjectsHTML = '';
        
        subjects.forEach(subject => {
            const isSelected = selectedPastSubjects.has(subject.id.toString());
            
            subjectsHTML += `
                <div class="subject-item ${isSelected ? 'selected' : ''}" data-id="${subject.id}">
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
                            <label for="subject_${subject.id}" class="checkbox-label">Mark as completed</label>
                        </div>
                    </div>
                </div>
            `;
        });
        
        subjectsList.innerHTML = subjectsHTML;
    }

    // Filter and display subjects based on current selection
    function filterAndDisplaySubjects() {
        console.log('Filtering subjects with:', {
            currentCategory,
            currentFilters
        });
        
        let filteredSubjects = irregularAllSubjects;
        
        // Apply category filter
        if (currentCategory !== 'all') {
            const [yearLevel, semester] = currentCategory.split('|');
            filteredSubjects = filteredSubjects.filter(subject => 
                subject.year_level === yearLevel && subject.semester === semester
            );
            console.log(`After category filter (${yearLevel}|${semester}):`, filteredSubjects.length);
        }
        
        // Apply year level filter
        if (currentFilters.yearLevel !== 'all') {
            filteredSubjects = filteredSubjects.filter(subject => 
                subject.year_level === currentFilters.yearLevel
            );
            console.log(`After year level filter (${currentFilters.yearLevel}):`, filteredSubjects.length);
        }
        
        // Apply semester filter
        if (currentFilters.semester !== 'all') {
            filteredSubjects = filteredSubjects.filter(subject => 
                subject.semester === currentFilters.semester
            );
            console.log(`After semester filter (${currentFilters.semester}):`, filteredSubjects.length);
        }
        
        // Apply search filter
        if (currentFilters.search) {
            const searchTerm = currentFilters.search.toLowerCase();
            filteredSubjects = filteredSubjects.filter(subject => 
                subject.code.toLowerCase().includes(searchTerm) ||
                subject.name.toLowerCase().includes(searchTerm) ||
                (subject.description && subject.description.toLowerCase().includes(searchTerm))
            );
            console.log(`After search filter (${currentFilters.search}):`, filteredSubjects.length);
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
        
        selectedCount.textContent = `${count} ${count === 1 ? 'subject' : 'subjects'} selected`;
        completedCount.textContent = count;
        totalUnits.textContent = `${units} units`;
        selectionSummary.textContent = `${count} subjects selected • ${units} total units`;
        
        // Update save button state
        saveBtn.disabled = count === 0;
    }

    // Toggle subject selection
    window.togglePastSubjectSelection = function(subjectId, subjectCode, subjectName, units, isChecked) {
        console.log('Toggling subject:', subjectId, isChecked);
        
        if (isChecked) {
            selectedPastSubjects.add(subjectId.toString());
        } else {
            selectedPastSubjects.delete(subjectId.toString());
        }
        
        // Update UI
        const subjectElement = document.querySelector(`.subject-item[data-id="${subjectId}"]`);
        if (subjectElement) {
            subjectElement.classList.toggle('selected', isChecked);
        }
        
        updateSelectionInfo();
    };

    // Event listeners for filters
    yearLevelFilter.addEventListener('change', function() {
        currentFilters.yearLevel = this.value;
        console.log('Year level filter changed to:', this.value);
        filterAndDisplaySubjects();
    });

    semesterFilter.addEventListener('change', function() {
        currentFilters.semester = this.value;
        console.log('Semester filter changed to:', this.value);
        filterAndDisplaySubjects();
    });

    searchInput.addEventListener('input', function() {
        currentFilters.search = this.value.trim().toLowerCase();
        console.log('Search input:', this.value);
        filterAndDisplaySubjects();
    });

    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        currentFilters.search = '';
        filterAndDisplaySubjects();
    });

    expandAllBtn.addEventListener('click', function() {
        document.querySelectorAll('.category-header').forEach(header => {
            header.classList.add('active');
            const subcategories = header.nextElementSibling;
            if (subcategories) {
                subcategories.classList.add('expanded');
            }
            const arrow = header.querySelector('.category-arrow');
            if (arrow) {
                arrow.style.transform = 'rotate(180deg)';
            }
        });
    });

    selectAllBtn.addEventListener('click', function() {
        const visibleSubjectElements = subjectsList.querySelectorAll('.subject-item');
        visibleSubjectElements.forEach(item => {
            const subjectId = item.getAttribute('data-id');
            const checkbox = item.querySelector('input[type="checkbox"]');
            const subject = irregularAllSubjects.find(s => s.id == subjectId);
            
            if (subject && !checkbox.checked) {
                checkbox.checked = true;
                togglePastSubjectSelection(subject.id, subject.code, subject.name, subject.units, true);
            }
        });
    });

    deselectAllBtn.addEventListener('click', function() {
        const visibleSubjectElements = subjectsList.querySelectorAll('.subject-item');
        visibleSubjectElements.forEach(item => {
            const subjectId = item.getAttribute('data-id');
            const checkbox = item.querySelector('input[type="checkbox"]');
            const subject = irregularAllSubjects.find(s => s.id == subjectId);
            
            if (subject && checkbox.checked) {
                checkbox.checked = false;
                togglePastSubjectSelection(subject.id, subject.code, subject.name, subject.units, false);
            }
        });
    });

    // Save functionality
    saveBtn.addEventListener('click', function() {
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
        
        console.log('Saving subjects:', pastSubjectsArray);
        
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
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Save response:', data);
            if (data.success) {
                showNotification('Completed subjects saved successfully!', 'success');
                modal.classList.remove('active');
                
                // Refresh the page or update UI
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
    });

    // Utility functions
    function showLoadingState() {
        subjectsList.innerHTML = `
            <div class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <span>Loading subjects...</span>
            </div>
        `;
    }

    function showErrorState(message) {
        subjectsList.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-exclamation-circle"></i>
                <h4>Error Loading Subjects</h4>
                <p>${message}</p>
                <button class="btn-primary mt-3" onclick="loadAllSubjects()">
                    <i class="fas fa-redo"></i>
                    Try Again
                </button>
            </div>
        `;
    }

    // Close modal functionality
    modal.addEventListener('click', function(e) {
        if (e.target === modal || e.target.closest('.irregular-close-modal') || e.target.closest('.irregular-cancel-btn')) {
            modal.classList.remove('active');
        }
    });

    // Make the initialization function available globally
    window.loadIrregularSubjects = loadAllSubjects;

    return {
        loadAllSubjects,
        updateSelectionInfo,
        modal: modal
    };
}

// Check if student is irregular and show modal if needed
function checkIrregularStudent(irregularModal) {
    const isIrregular = document.getElementById('is-regular')?.value == 2;
    
    if (isIrregular) {
        console.log('Student is irregular, checking past subjects status...');
        
        fetch('/student/enrollment/check-past-subjects', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Past subjects check response:', data);
            if (data.success && !data.has_submitted) {
                // Student hasn't submitted past subjects, show modal
                console.log('Irregular student needs to submit past subjects');
                irregularModal.loadAllSubjects();
                irregularModal.modal.classList.add('active');
            } else if (data.success) {
                console.log('Irregular student has already submitted past subjects');
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
    
    if (!studentInfoModal || !studentInfoModal.classList.contains('active')) {
        return;
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

document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar on mobile
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const searchInput = document.querySelector('.search-input');
    const searchBtn = document.querySelector('.search-btn');
    const searchBar = document.querySelector('.search-bar');

    // Initialize irregular modal
    const irregularModal = initializeIrregularModal();

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
                        // Student has submitted past subjects, proceed with enrollment
                        console.log('Irregular student has submitted past subjects, opening enrollment modal');
                        enrollmentModal.classList.add('active');
                        loadEnrollmentSubjects();
                    } else {
                        // Student hasn't submitted past subjects, show the irregular modal
                        console.log('Irregular student needs to submit past subjects first');
                        irregularModal.loadAllSubjects();
                        irregularModal.modal.classList.add('active');
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
            // Regular student - proceed normally
            console.log('Regular student, opening enrollment modal');
            enrollmentModal.classList.add('active');
            loadEnrollmentSubjects();
        }
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
                            <div class="prerequisite-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Requires prerequisites: ${subject.prerequisites.map(p => p.code).join(', ')}
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
    window.toggleSubject = function(subjectId, units, isDisabled, maxUnits = 23) {
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