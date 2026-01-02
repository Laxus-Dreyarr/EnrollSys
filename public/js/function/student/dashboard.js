const supabaseUrl = "https://dfvapjrkotprotpbpeju.supabase.co";
const supabaseAnonKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImRmdmFwanJrb3Rwcm90cGJwZWp1Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTcxNDg1OTMsImV4cCI6MjA3MjcyNDU5M30.Hou-GtB-P8qJ4fxXbC-VtyaCkDpf5Kr01DD9aSckhiU";

// Create Supabase client
const { createClient } = supabase;
const supabaseClient = createClient(supabaseUrl, supabaseAnonKey);

// Function to set up real-time subscription
function setupRealtimeSubscription() {
    const subscription = supabaseClient
        .channel('enrollment_status-changes')
        .on('postgres_changes', 
        { 
            event: '*',  // Listen for all changes (INSERT, UPDATE, DELETE)
            schema: 'public', 
            table: 'enrollment_status' 
        }, 
        (payload) => {
            // Refresh data based on the operation type
            if (payload.new && payload.new.table_name === 'status') {
                if (payload.new.operation === 'DELETE') {
                    status_update();
                    count_enrolled_subjects();
                    count_documents();
                } else if (payload.new.operation === 'RESTART') {
                    window.location.reload();
                }
                // Refresh the notification count when changes occur
                // fetchNotificationCount();
            }
                        
        }
        )
        .subscribe((status) => {
            console.log('Subscription status:', status);
            if (status === 'SUBSCRIBED') {
                console.log('Real-time subscription established');
            }
        });
            
    return subscription;
}


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


// Theme Color Picker Functionality
function initializeThemeColorPicker() {
    const colorOptions = document.querySelectorAll('.color-option');
    const savedThemeColor = localStorage.getItem('themeColor') || '#4361ee';
    
    // Apply saved theme color on page load
    applyThemeColor(savedThemeColor);
    setActiveColorOption(savedThemeColor);
    updateCurrentThemeName(savedThemeColor);
    
    // Add click event listeners to color options
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            const selectedColor = this.getAttribute('data-color');
            applyThemeColor(selectedColor);
            setActiveColorOption(selectedColor);
            saveThemeColor(selectedColor);
            updateCurrentThemeName(selectedColor);
            
            // Show enhanced notification
            showThemeNotification(`Theme updated to ${getColorName(selectedColor)}`, 'success');
        });
        
        // Add keyboard accessibility
        option.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                this.click();
            }
        });
        
        // Make color options focusable for accessibility
        option.setAttribute('tabindex', '0');
    });
}

// Update current theme name display
function updateCurrentThemeName(color) {
    const themeNameElement = document.getElementById('current-theme-name');
    if (themeNameElement) {
        themeNameElement.textContent = getColorName(color);
        themeNameElement.style.color = color;
    }
}

// Apply theme color to CSS variables
function applyThemeColor(color) {
    // Add transition class for smooth color change
    document.body.classList.add('theme-color-transition');
    
    // Calculate color variants for sidebar
    const sidebarColor1 = shadeColor(color, -60); // Darkest
    const sidebarColor2 = shadeColor(color, -40); // Medium
    const sidebarColor3 = shadeColor(color, -20); // Lightest
    const sidebarAccent = shadeColor(color, 10);  // Accent color
    const sidebarHover = `rgba(${hexToRgb(color).join(', ')}, 0.2)`;
    const sidebarActive = `rgba(${hexToRgb(color).join(', ')}, 0.3)`;
    const sidebarBorder = `rgba(${hexToRgb(color).join(', ')}, 0.1)`;
    
    // Update CSS variables for sidebar
    const root = document.documentElement;
    root.style.setProperty('--sidebar-gradient-1', sidebarColor1);
    root.style.setProperty('--sidebar-gradient-2', sidebarColor2);
    root.style.setProperty('--sidebar-gradient-3', sidebarColor3);
    root.style.setProperty('--sidebar-accent-color', sidebarAccent);
    root.style.setProperty('--sidebar-border-color', sidebarBorder);
    root.style.setProperty('--sidebar-menu-hover', sidebarHover);
    root.style.setProperty('--sidebar-menu-active', sidebarActive);
    
    // Update main theme colors (existing functionality)
    const darkerColor = shadeColor(color, -20);
    const lighterColor = shadeColor(color, 10);
    
    root.style.setProperty('--primary-color', color);
    root.style.setProperty('--primary-dark', darkerColor);
    root.style.setProperty('--secondary-color', darkerColor);
    root.style.setProperty('--accent-color', lighterColor);
    
    // Update avatar background color if using default avatar
    updateAvatarBackground(color);
    
    // Update meta theme color for mobile browsers
    updateMetaThemeColor(color);
    
    // Remove transition class after animation completes
    setTimeout(() => {
        document.body.classList.remove('theme-color-transition');
    }, 400);
}

// Update avatar background color for default avatars
function updateAvatarBackground(color) {
    const dynamicAvatar = document.getElementById('dynamic-avatar');
    if (dynamicAvatar) {
        // Extract the first name and last name from the current src or use defaults
        const currentSrc = dynamicAvatar.src;
        const firstName = "{{ $firstname ?? 'User' }}";
        const lastName = "{{ $lastname ?? '' }}";
        
        // Create new avatar URL with updated background color
        const newAvatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(firstName + ' ' + lastName)}&background=${color.replace('#', '')}&color=fff&size=150`;
        
        dynamicAvatar.src = newAvatarUrl;
    }
}

// Helper function to convert hex to RGB array
function hexToRgb(hex) {
    // Remove the # if present
    hex = hex.replace(/^#/, '');
    
    // Parse the hex values
    let r, g, b;
    
    if (hex.length === 3) {
        r = parseInt(hex.charAt(0) + hex.charAt(0), 16);
        g = parseInt(hex.charAt(1) + hex.charAt(1), 16);
        b = parseInt(hex.charAt(2) + hex.charAt(2), 16);
    } else {
        r = parseInt(hex.substring(0, 2), 16);
        g = parseInt(hex.substring(2, 4), 16);
        b = parseInt(hex.substring(4, 6), 16);
    }
    
    return [r, g, b];
}



// Update meta theme color
function updateMetaThemeColor(color) {
    let metaThemeColor = document.querySelector('meta[name="theme-color"]');
    if (!metaThemeColor) {
        metaThemeColor = document.createElement('meta');
        metaThemeColor.name = 'theme-color';
        document.head.appendChild(metaThemeColor);
    }
    metaThemeColor.setAttribute('content', color);
}

function updateDynamicElements(color) {
    // Update any elements that might have inline theme colors
    const themeElements = document.querySelectorAll('[data-theme-color]');
    themeElements.forEach(element => {
        element.style.backgroundColor = color;
    });
}

// Set active state on color option
function setActiveColorOption(color) {
    const colorOptions = document.querySelectorAll('.color-option');
    colorOptions.forEach(option => {
        option.classList.remove('active');
        if (option.getAttribute('data-color') === color) {
            option.classList.add('active');
        }
    });
}

// Save theme color to localStorage
function saveThemeColor(color) {
    localStorage.setItem('themeColor', color);
}

// Utility function to shade colors
function shadeColor(color, percent) {
    const num = parseInt(color.replace("#", ""), 16);
    const amt = Math.round(2.55 * percent);
    const R = Math.min(255, Math.max(0, (num >> 16) + amt));
    const G = Math.min(255, Math.max(0, ((num >> 8) & 0x00FF) + amt));
    const B = Math.min(255, Math.max(0, (num & 0x0000FF) + amt));
    
    return "#" + (
        0x1000000 +
        (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
        (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
        (B < 255 ? B < 1 ? 0 : B : 255)
    ).toString(16).slice(1);
}

// Get color name for notification
function getColorName(color) {
    const colorMap = {
        '#4361ee': 'Blue',
        '#2c5530': 'Green', 
        '#8b5cf6': 'Purple',
        '#ef4444': 'Red',
        '#f59e0b': 'Orange',
        '#800000': 'Maroon'
    };
    return colorMap[color] || 'Custom';
}

// Enhanced notification function for theme changes
// Enhanced notification for theme changes including sidebar
function showThemeNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `theme-notification ${type}`;
    notification.innerHTML = `
        <i class="fas fa-palette"></i>
        <span>${message}</span>
        <small>Sidebar and interface colors updated</small>
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${type === 'success' ? 'var(--success-color)' : 'var(--danger-color)'};
        color: white;
        padding: 16px 20px;
        border-radius: 12px;
        box-shadow: var(--shadow-hover);
        z-index: 10000;
        display: flex;
        flex-direction: column;
        gap: 4px;
        font-weight: 500;
        animation: slideInRight 0.3s ease;
        max-width: 300px;
        border-left: 4px solid var(--primary-color);
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


function reload() {
    window.location.reload();
}


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
    const uploadStatusTitle = document.getElementById('upload-status-title');
    
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
        
        const startTime = Date.now();
        const progressInterval = setInterval(() => {
            progress += 1;
            
            // Update progress bar
            progressFill.style.width = progress + '%';
            progressText.textContent = progress + '%';
            
            // Update status messages
            if (uploadStatusTitle) {
                if (progress < 30) {
                    uploadStatusTitle.textContent = 'Preparing...';
                } else if (progress < 60) {
                    uploadStatusTitle.textContent = 'Uploading...';
                } else if (progress < 90) {
                    uploadStatusTitle.textContent = 'Processing...';
                } else {
                    uploadStatusTitle.textContent = 'Finishing...';
                }
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
        // Show success animation for client-side processing
        successCheckmark.classList.add('active');
        progressText.textContent = 'Processing complete!';
        successCheckmark.classList.remove('active');
            // Start actual server upload
            uploadToServer(file);
            

    }
    
    // Actual server upload function
    function uploadToServer(file) {
        const formData = new FormData();
        formData.append('avatar', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        // Get upload URL (you should define this in your blade template)
        const uploadUrl = document.getElementById('avatar-container').getAttribute('data-upload-url') || '/student/upload-avatar';
        
        // Create XMLHttpRequest for better progress tracking
        const xhr = new XMLHttpRequest();
        
        xhr.open('POST', uploadUrl, true);
        
        // Track upload start time for speed calculation
        const uploadStartTime = Date.now();
        let lastLoaded = 0;
        
        // Update progress during upload
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                progressFill.style.width = percentComplete + '%';
                progressText.textContent = Math.round(percentComplete) + '%';
                
                // Update status messages
                if (uploadStatusTitle) {
                    if (percentComplete < 30) {
                        uploadStatusTitle.textContent = 'Preparing...';
                    } else if (percentComplete < 60) {
                        uploadStatusTitle.textContent = 'Uploading...';
                    } else if (percentComplete < 90) {
                        uploadStatusTitle.textContent = 'Processing...';
                    } else {
                        uploadStatusTitle.textContent = 'Finishing...';
                    }
                }
            }
        });
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                
                if (response.success) {
                    // Update profile picture on page
                    profileAvatar.src = response.profile_url;

                    const sidebarAvatar = document.querySelector('.sidebar .user-avatar');
                    if (sidebarAvatar) {
                        sidebarAvatar.src = response.profile_url;
                    }
                    
                    // Show success message
                    showNotification(response.message, 'success');
                    
                    // Update progress to complete
                    progressFill.style.width = '100%';
                    progressText.textContent = '100%';
                    
                    // Update status to success
                    if (uploadStatusTitle) uploadStatusTitle.textContent = 'Complete!';
                    
                    // Show success checkmark
                    successCheckmark.classList.add('active');
                    window.location.reload();
                    
                    // After success, hide loading and reset
                    setTimeout(() => {
                        uploadLoading.classList.remove('active');
                        successCheckmark.classList.remove('active');
                        avatarContainer.classList.remove('uploading');
                        
                        // Reset status messages
                        if (uploadStatusTitle) uploadStatusTitle.textContent = 'Uploading...';
                        
                        // Re-enable button
                        changePhotoBtn.disabled = false;
                        changePhotoBtn.classList.remove('loading');
                    }, 2000);
                } else {
                    showError(response.error || 'Upload failed. Please try again.');
                    resetUploadState();
                }
            } else {
                showError('Server error occurred. Please try again.');
                resetUploadState();
            }
        };
        
        xhr.onerror = function() {
            showError('Network error occurred. Please check your connection.');
            resetUploadState();
        };
        
        // Send the request
        xhr.send(formData);
    }

    function resetUploadState() {
        setTimeout(() => {
            uploadLoading.classList.remove('active');
            avatarContainer.classList.remove('uploading');
            changePhotoBtn.disabled = false;
            changePhotoBtn.classList.remove('loading');
            progressFill.style.width = '0%';
            progressText.textContent = '0%';
        }, 1000);
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
        const curriculumSelect2 = document.getElementById('curriculum2');
        
        if (!curriculumSelect2) {
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
                while (curriculumSelect2.options.length > 1) {
                    curriculumSelect2.remove(1);
                }
                
                // Add curriculum options
                data.curricula.forEach(curriculum => {
                    const option = document.createElement('option');
                    option.value = curriculum.curriculum_year;
                    option.textContent = `${curriculum.curriculum_year} Curriculum`;
                    curriculumSelect2.appendChild(option);
                });
                
                console.log('Curriculum dropdown populated with:', data.curricula.length, 'options');
                
                // If there's only one curriculum, select it by default
                if (data.curricula.length === 1) {
                    curriculumSelect2.value = data.curricula[0].curriculum_year;
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
            curriculumSelect2.appendChild(option);
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
        
        // if (!field.value.trim()) {
        //     showFieldError(field, errorElement, 'This field is required');
        //     return false;
        // }
        
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
        const fields = ['school_id', 'curriculum'];
        
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
        formData.append('curriculum2', document.getElementById('curriculum2').value);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        console.log('Submitting student info with curriculum:', document.getElementById('curriculum2').value);

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

// Student info modal functionality 2
function initializeStudentInfoModal2() {
    const studentInfoModal2 = document.getElementById('studentInfoModal2');
    const studentInfoForm2 = document.getElementById('studentInfoForm2');
    
    if (!studentInfoModal2) {
        return;
    }

    // Prevent closing modal by clicking outside
    studentInfoModal2.addEventListener('click', function(e) {
        if (e.target === studentInfoModal2) {
            e.preventDefault();
            e.stopPropagation();
        }
    });

    // Form validation
    studentInfoForm2.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm2();
        // if (validateForm()) {
        //     submitForm2();
        // }
    });

    // function validateField(field) {
    //     const value = field.value.trim();
    //     const errorElement = document.getElementById(field.id + '_error');
        
    //     // Clear previous error
    //     clearFieldError(field);
        
    //     // Required field validation
    //     if (!value) {
    //         showFieldError(field, errorElement, 'This field is required');
    //         return false;
    //     }
        
    //     return true;
    // }

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

    // function validateForm() {
    //     let isValid = true;
    //     const fields = ['year_level', 'student_type'];
        
    //     fields.forEach(fieldId => {
    //         const field = document.getElementById(fieldId);
    //         if (field && !validateField(field)) {
    //             isValid = false;
    //         }
    //     });
        
    //     return isValid;
    // }

    function submitForm2() {
        const submitBtn = studentInfoForm2.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        
        // Get form data
        const formData = new FormData();
        formData.append('action', 'complete_student_info2');
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
                if (data.message.includes('year level')) {
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

// Student info modal functionality 3
function initializeStudentInfoModal3() {
    const studentInfoModal3 = document.getElementById('studentInfoModal3');
    const studentInfoForm3 = document.getElementById('studentInfoForm3');

    
    if (!studentInfoModal3) {
        return;
    }

    // Prevent closing modal by clicking outside
    studentInfoModal3.addEventListener('click', function(e) {
        if (e.target === studentInfoModal3) {
            e.preventDefault();
            e.stopPropagation();
        }
    });

    // Form validation
    studentInfoForm3.addEventListener('submit', function(e) {
        e.preventDefault();
        if (validateForm()) {
            submitForm3();
        }
    });

    // Real-time validation for school ID
    const schoolIdInput = document.getElementById('school_id3');
    schoolIdInput.addEventListener('input', function() {
        validateSchoolIdFormat(this);
    });

    schoolIdInput.addEventListener('blur', function() {
        validateSchoolIdCurriculum(this);
    });

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
        
        // if (!field.value.trim()) {
        //     showFieldError(field, errorElement, 'This field is required');
        //     return false;
        // }
        
        if (field.id === 'school_id3') {
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
        const fields = ['school_id3', 'current_status'];
        
        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && !validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    }


    function submitForm3() {
        const submitBtn = studentInfoForm3.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        
        // Get form data
        const formData = new FormData();
        formData.append('action', 'complete_student_info_starter');
        formData.append('current_status', document.getElementById('current_status').value);
        formData.append('school_id3', document.getElementById('school_id3').value);
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
                    const schoolIdInput = document.getElementById('school_id3');
                    const schoolIdInputError = document.getElementById('school_id_error');
                    showFieldError(schoolIdInput, schoolIdInputError, data.message);
                } else if (data.message.includes('Curriculum year')) {
                    const schoolIdInput = document.getElementById('school_id3');
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

    // Payment Receipt Upload elements
    const paymentSection = document.getElementById('enhancedPaymentSection');
    const confirmationSection = document.getElementById('enhancedConfirmationSection');
    const paymentUploadArea = document.getElementById('enhancedPaymentUploadArea');
    const paymentDropZone = document.getElementById('enhancedPaymentDropZone');
    const paymentBrowseBtn = document.getElementById('enhancedPaymentBrowseBtn');
    const paymentFileInput = document.getElementById('enhancedPaymentFileInput');
    const paymentPreview = document.getElementById('enhancedPaymentPreview');
    const paymentFileName = document.getElementById('enhancedPaymentFileName');
    const paymentFileSize = document.getElementById('enhancedPaymentFileSize');
    const paymentRemoveBtn = document.getElementById('enhancedPaymentRemoveBtn');
    const paymentUploadProgress = document.getElementById('enhancedPaymentUploadProgress');
    const paymentProgressFill = document.getElementById('enhancedPaymentProgressFill');
    const paymentProgressText = document.getElementById('enhancedPaymentProgressText');
    const paymentVerification = document.getElementById('enhancedPaymentVerification');
    const verificationFileName = document.getElementById('enhancedVerificationFileName');
    const verificationTime = document.getElementById('enhancedVerificationTime');
    
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

    // New variables for step management, FHE file and payment receipt
    let currentStep = 1;
    let fheFile = null;
    let isFheUploaded = false;
    let paymentReceiptFile = null;
    let isPaymentReceiptUploaded = false;

    // Initialize modal
    function init() {
        attachEventListeners();
        updateStepNavigation();
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

        // Payment Receipt Upload events
        paymentBrowseBtn.addEventListener('click', () => paymentFileInput.click());
        paymentFileInput.addEventListener('change', handlePaymentFileSelect);
        paymentRemoveBtn.addEventListener('click', removePaymentFile);
        
        // Drag and drop for payment receipt
        paymentDropZone.addEventListener('dragover', handlePaymentDragOver);
        paymentDropZone.addEventListener('dragleave', handlePaymentDragLeave);
        paymentDropZone.addEventListener('drop', handlePaymentDrop);

        // Final submission
        finalSubmitBtn.addEventListener('click', submitFinalEnrollment);
        editEnrollmentBtn.addEventListener('click', goToStepOne);

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
            nextStepBtn.style.display = 'flex';
            submitBtn.style.display = 'none';
            nextStepBtn.disabled = !isFheUploaded;
        } else if (currentStep === 3) {
            mainContent.style.display = 'none';
            fheSection.style.display = 'none';
            paymentSection.style.display = 'block';
            confirmationSection.style.display = 'none';
            backStepBtn.style.display = 'flex';
            nextStepBtn.style.display = 'flex';
            submitBtn.style.display = 'none';
            nextStepBtn.disabled = false; // Allow proceeding without payment receipt
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
            currentStep = 3; // Go to payment receipt upload step
            updateStepNavigation();
        } else if (currentStep === 3) {
            // Allow proceeding even without payment receipt upload
            currentStep = 4; // Go to confirmation after payment receipt upload
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
                
                // Upload complete - enable next step
                setTimeout(() => {
                    isFheUploaded = true;
                    nextStepBtn.disabled = false;
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
        nextStepBtn.disabled = true;
        window.location.reload();
    }

    // Payment Receipt Upload Functions
    function handlePaymentFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            processPaymentFile(file);
        }
    }

    function handlePaymentDragOver(event) {
        event.preventDefault();
        paymentDropZone.classList.add('dragover');
    }

    function handlePaymentDragLeave(event) {
        event.preventDefault();
        paymentDropZone.classList.remove('dragover');
    }

    function handlePaymentDrop(event) {
        event.preventDefault();
        paymentDropZone.classList.remove('dragover');
        
        const files = event.dataTransfer.files;
        if (files.length > 0) {
            processPaymentFile(files[0]);
        }
    }

    function processPaymentFile(file) {
        // Validate file
        if (!validatePaymentFile(file)) {
            return;
        }

        paymentReceiptFile = file;
        
        // Show preview
        showPaymentPreview(file);
        
        // Simulate upload process
        simulatePaymentUpload();
    }

    function validatePaymentFile(file) {
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!validTypes.includes(file.type)) {
            showNotification('Please select a valid file type (JPG, PNG, PDF)', 'error');
            return false;
        }

        if (file.size > maxSize) {
            showNotification('File size must be less than 5MB', 'error');
            return false;
        }

        return true;
    }

    function showPaymentPreview(file) {
        const fileSize = (file.size / (1024 * 1024)).toFixed(2);
        
        // Determine file type icon
        let fileIcon = 'fas fa-file';
        if (file.type.includes('pdf')) {
            fileIcon = 'fas fa-file-pdf';
        } else if (file.type.includes('image')) {
            fileIcon = 'fas fa-file-image';
        }

        paymentFileName.textContent = file.name;
        paymentFileSize.textContent = `${fileSize} MB`;
        paymentPreview.querySelector('.enhanced-payment-file-icon').className = fileIcon;
        
        paymentPreview.style.display = 'block';
        paymentDropZone.style.display = 'none';
    }

    function simulatePaymentUpload() {
        paymentUploadProgress.style.display = 'block';
        let progress = 0;
        
        const interval = setInterval(() => {
            progress += Math.random() * 10;
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
                
                // Upload complete
                setTimeout(() => {
                    isPaymentReceiptUploaded = true;
                    nextStepBtn.disabled = false;
                    showPaymentVerification();
                    showNotification('Payment receipt uploaded successfully!', 'success');
                }, 500);
            }
            
            paymentProgressFill.style.width = `${progress}%`;
            paymentProgressText.textContent = `${Math.round(progress)}%`;
        }, 100);
    }

    function showPaymentVerification() {
        verificationFileName.textContent = paymentReceiptFile.name;
        verificationTime.textContent = 'Just now';
        paymentVerification.style.display = 'block';
        paymentUploadArea.style.display = 'none';
    }

    function removePaymentFile() {
        paymentReceiptFile = null;
        isPaymentReceiptUploaded = false;
        paymentPreview.style.display = 'none';
        paymentUploadProgress.style.display = 'none';
        paymentDropZone.style.display = 'block';
        paymentFileInput.value = '';
        paymentVerification.style.display = 'none';
        paymentUploadArea.style.display = 'block';
        // Don't disable next button - allow proceeding without payment receipt
        nextStepBtn.disabled = false;
    }

    function updatePaymentUI() {
        // Reset payment state when entering payment step
        if (!isPaymentReceiptUploaded) {
            // Reset UI but keep next button enabled
            paymentReceiptFile = null;
            paymentPreview.style.display = 'none';
            paymentUploadProgress.style.display = 'none';
            paymentDropZone.style.display = 'block';
            paymentFileInput.value = '';
            paymentVerification.style.display = 'none';
            paymentUploadArea.style.display = 'block';
            // Ensure next button is enabled (allow proceeding without payment receipt)
            nextStepBtn.disabled = false;
        } else {
            showPaymentVerification();
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
                    </div>
                `;
            }
        });
        confirmationSubjectsList.innerHTML = subjectsHTML;

        // Update FHE file name
        if (fheFile) {
            confirmationFheFile.textContent = fheFile.name;
        }

        // Update payment confirmation to show receipt or will pay later
        const paymentStatusElement = document.querySelector('.enhanced-confirmation-payment-status');
        if (paymentStatusElement) {
            if (isPaymentReceiptUploaded) {
                paymentStatusElement.innerHTML = `
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Payment receipt uploaded - Awaiting verification</span>
                `;
            } else {
                paymentStatusElement.innerHTML = `
                    <i class="fas fa-clock"></i>
                    <span>Payment will be made later</span>
                `;
            }
        }
    }

    // Final enrollment submission
    function submitFinalEnrollment() {
        console.log('Final enrollment submission with receipt verification');
        
        const submitBtn = finalSubmitBtn;
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
        
        // Create FormData with all enrollment data including payment receipt
        const formData = new FormData();
        
        // Convert Map to Array for submission
        const subjectsArray = Array.from(enhancedSelectedSubjects.values()).map(item => ({
            subjectId: item.subjectId,
            section: item.section
        }));
        
        // Append all data
        formData.append('subjects', JSON.stringify(subjectsArray));
        formData.append('fhe_file', fheFile);
        // Only append payment receipt if it was uploaded
        if (isPaymentReceiptUploaded && paymentReceiptFile) {
            formData.append('payment_receipt', paymentReceiptFile);
        }
        formData.append('total_units', enhancedTotalUnits.toString());
        formData.append('is_payment_uploaded', isPaymentReceiptUploaded.toString());
        
        console.log('Final enrollment submission with payment receipt');
        
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
                insertsupabase();
                setupRealtimeSubscription();
                showNotification(data.message || 'Enrollment submitted successfully!', 'success');

                // If prospectus URL is available, show download option
                if (data.prospectus_url) {
                    setTimeout(() => {
                        let downloadMessage = 'Your enrollment prospectus has been generated. <a href="' + data.prospectus_url + '" target="_blank" style="color: white; text-decoration: underline; font-weight: bold;">Click here to download</a>';
                        
                        // If payment notice URL is available, add it to the message
                        if (data.payment_notice_url) {
                            downloadMessage += '<br><br>Your payment notice has been generated. <a href="' + data.payment_notice_url + '" target="_blank" style="color: white; text-decoration: underline; font-weight: bold;">Click here to download payment notice</a>';
                        }
                        
                        showNotification(
                            downloadMessage,
                            'success',
                            8000 // Show for 8 seconds to allow time to read both links
                        );
                        
                        // Optional: Auto-open in new tab
                        window.open(data.prospectus_url, '_blank');
                        if (data.payment_notice_url) {
                            setTimeout(() => {
                                window.open(data.payment_notice_url, '_blank');
                            }, 500);
                        }
                    }, 1000);
                }

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
        enhancedCurrentFilters.section = sectionFilter ? sectionFilter.value : 'all';
        syncMobileFilters();
        filterAndDisplaySubjects();
    }

    function handleMobileFilterChange() {
        enhancedCurrentFilters.subjectFilter = mobileSubjectFilter.value;
        enhancedCurrentFilters.sortBy = mobileSortFilter.value;
        enhancedCurrentFilters.section = mobileSectionFilter ? mobileSectionFilter.value : 'all';
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
        if (enhancedCurrentFilters.section !== 'all') count++;
        filterCount.textContent = count;
    }

    // In the loadEnhancedEnrollmentSubjects function
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
                // Check if there's an existing enrollment request
                if (data.has_existing_request) {
                    showNotification(`You cannot enroll at this time. You have an existing enrollment request with status: ${data.existing_status}`, 'error');
                    closeModal();
                    return;
                }
                
                // Continue with normal processing...
                let subjectsArray = data.subjects;
                if (subjectsArray && typeof subjectsArray === 'object' && !Array.isArray(subjectsArray)) {
                    console.log('Converting subjects object to array');
                    subjectsArray = Object.values(subjectsArray);
                }
                
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
                // Check if it's an existing request error
                if (data.has_existing_request) {
                    showNotification(data.message, 'error');
                    closeModal();
                } else {
                    showEnhancedError('Failed to load subjects: ' + (data.message || 'Unknown error'));
                }
            }
        })
        .catch(error => {
            console.error('Error loading subjects in enhanced modal:', error);
            showEnhancedError('Network error. Please check your connection and try again. Error: ' + error.message);
        });
    }

    // Revised displayEnhancedSubjects function for table format grouped by year level and semester
    function displayEnhancedSubjects(data) {
        
        console.log('Displaying enhanced subjects in table format with data:', data);
        
        if (!data || typeof data !== 'object') {
            console.error('Invalid data received:', data);
            showEnhancedError('Invalid response from server');
            return;
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
        
        // Ensure subjects is an array
        let subjects = data.subjects || [];
        if (subjects && typeof subjects === 'object' && !Array.isArray(subjects)) {
            console.log('Converting subjects object to array');
            subjects = Object.values(subjects);
        }

        if (subjects.length === 0) {
            showEnhancedEmptyState();
            return;
        }

        hideEnhancedEmptyState();

        // Auto-select all subjects for regular students (only their current year level and semester)
        if (data.is_regular) {
            enhancedSelectedSubjects.clear();
            enhancedTotalUnits = 0;
            
            // Auto-select all subjects that match the student's current year level and semester
            subjects.forEach(subject => {
                // Only process subjects for the current year level and semester
                if (subject.year_level === data.year_level && subject.semester === data.semester) {
                    const hasPrerequisites = subject.has_prerequisites;
                    const prerequisitesMet = subject.prerequisites_met;
                    const isFailedSubject = subject.is_failed_subject;
                    
                    // For regular students, only select subjects without prerequisites or with met prerequisites
                    let isSelectable = false;
                    if (isFailedSubject) {
                        isSelectable = true;
                    } else if (!hasPrerequisites) {
                        isSelectable = true;
                    } else {
                        isSelectable = prerequisitesMet;
                    }
                    
                    if (isSelectable) {
                        const units = parseInt(subject.units || 0);
                        const newTotal = enhancedTotalUnits + units;
                        
                        if (newTotal <= enhancedMaxUnits) {
                            enhancedSelectedSubjects.set(subject.id.toString(), {
                                subjectId: subject.id,
                                section: 'A', // Default section
                                units: units
                            });
                            enhancedTotalUnits = newTotal;
                        }
                    }
                }
            });
            
            console.log('Auto-selected subjects for regular student:', enhancedSelectedSubjects.size);
            console.log('Total auto-selected units:', enhancedTotalUnits);
        }
        
        // Group subjects by year level and semester
        const groupedSubjects = {};
        subjects.forEach(subject => {
            if (!subject || !subject.id || !subject.code) {
                console.warn('Invalid subject skipped:', subject);
                return;
            }
            
            // Filter subjects for regular students - ONLY show current year level and semester
            if (data.is_regular) {
                // Only add subjects that match the student's current year level and semester
                if (subject.year_level === data.year_level && subject.semester === data.semester) {
                    const key = `${subject.year_level || ''} - ${subject.semester || ''}`;
                    if (!groupedSubjects[key]) {
                        groupedSubjects[key] = [];
                    }
                    groupedSubjects[key].push(subject);
                }
            } else {
                // For irregular students, show all subjects as before
                const key = `${subject.year_level || ''} - ${subject.semester || ''}`;
                if (!groupedSubjects[key]) {
                    groupedSubjects[key] = [];
                }
                groupedSubjects[key].push(subject);
            }
        });
        
        // Sort group keys: year level first, then semester
        const yearOrder = { '1st Year': 1, '2nd Year': 2, '3rd Year': 3, '4th Year': 4 };
        const semOrder = { '1st Sem': 1, '2nd Sem': 2, 'Summer': 3 };
        
        const sortedGroupKeys = Object.keys(groupedSubjects).sort((a, b) => {
            const [yearA, semA] = a.split(' - ');
            const [yearB, semB] = b.split(' - ');
            
            const yearDiff = (yearOrder[yearA] || 99) - (yearOrder[yearB] || 99);
            if (yearDiff !== 0) return yearDiff;
            
            return (semOrder[semA] || 99) - (semOrder[semB] || 99);
        });
        
        // Build HTML for all tables
        let containerHTML = '';
        
        sortedGroupKeys.forEach(groupKey => {
            const groupSubjects = groupedSubjects[groupKey];
            
            // Add label above table
            containerHTML += `
                <div class="enhanced-subject-group">
                    <h4 class="enhanced-subject-group-label">${groupKey}</h4>
                    <div class="enhanced-subject-table-wrapper">
                        <table class="enhanced-subjects-table">
                            <thead>
                                <tr>
                                    <th class="enhanced-table-checkbox-header">
                                        <input type="checkbox" 
                                            id="enhancedSelectAll_${groupKey.replace(/\s+/g, '_')}" 
                                            class="enhanced-select-all-checkbox"
                                            onchange="enhancedToggleSelectAllGroup('${groupKey.replace(/\s+/g, '_')}', this.checked)">
                                    </th>
                                    <th>Subject Code</th>
                                    <th>Subject Name</th>
                                    <th>Units</th>
                                    <th>Prerequisite</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            // Add rows for each subject in this group
            groupSubjects.forEach(subject => {
                // Check if this subject is already selected
                const selectedData = enhancedSelectedSubjects.get(subject.id.toString());
                const isSelected = !!selectedData;
                
                // Determine if subject is selectable based on prerequisites
                const hasPrerequisites = subject.has_prerequisites;
                const prerequisitesMet = subject.prerequisites_met;
                const isFailedSubject = subject.is_failed_subject;
                
                // Logic for irregular students
                let isSelectable = false;
                if (enhancedIsRegular) {
                    // Regular student: can select any subject for their level and semester
                    isSelectable = true;
                } else {
                    // Irregular student logic
                    if (isFailedSubject) {
                        // Failed subjects are always selectable
                        isSelectable = true;
                    } else if (!hasPrerequisites) {
                        // Subjects without prerequisites are selectable
                        isSelectable = true;
                    } else {
                        // Subjects with prerequisites: only selectable if prerequisites are met
                        isSelectable = prerequisitesMet;
                    }
                }
                
                // Format prerequisites for display
                let prerequisitesText = 'None';
                if (subject.prerequisites && subject.prerequisites.length > 0) {
                    prerequisitesText = subject.prerequisites.map(p => p.code).join(', ');
                }
                
                containerHTML += `
                    <tr class="enhanced-subject-row ${isSelected ? 'selected' : ''} ${!isSelectable ? 'disabled' : ''} ${isFailedSubject ? 'failed-subject' : ''}" data-id="${subject.id}">
                        <td class="enhanced-subject-checkbox-cell">
                            <input type="checkbox" 
                                id="enhanced_subject_${subject.id}" 
                                class="enhanced-subject-checkbox"
                                ${isSelected ? 'checked' : ''}
                                ${!isSelectable ? 'disabled' : ''}
                                onchange="enhancedToggleSubject(${subject.id}, ${subject.units || 0}, ${!isSelectable})">
                        </td>
                        <td class="enhanced-subject-code-cell">
                            <span class="enhanced-subject-code">${subject.code}</span>
                            ${isFailedSubject ? '<span class="enhanced-failed-badge">Failed</span>' : ''}
                        </td>
                        <td class="enhanced-subject-name-cell">
                            <div class="enhanced-subject-name">${subject.name || 'No name'}</div>
                            ${isFailedSubject ? `<div class="enhanced-failed-grade">Previous Grade: ${subject.previous_grade || 'N/A'}</div>` : ''}
                            ${!isSelectable && hasPrerequisites && !prerequisitesMet ? `
                                <div class="enhanced-prerequisite-warning">
                                    <i class="fas fa-exclamation-circle"></i>
                                    Prerequisites not met: ${prerequisitesText}
                                </div>
                            ` : ''}
                        </td>
                        <td class="enhanced-subject-units-cell">${subject.units || 0}</td>
                        <td class="enhanced-subject-prereq-cell">${prerequisitesText}</td>
                    </tr>
                `;
            });
            
            // Close table
            containerHTML += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        });
        
        // Update container
        const container = document.getElementById('enhancedSubjectsList');
        if (container) {
            container.innerHTML = containerHTML;
        }
        
        console.log('Subjects rendered in tables. Current selection count:', enhancedSelectedSubjects.size);
        console.log('Current total units:', enhancedTotalUnits);
        
        // Update UI
        updateEnhancedSelectionInfo();
    }
    
    // Function to toggle select all for a specific group
    window.enhancedToggleSelectAllGroup = function(groupKey, checked) {
        const groupLabel = groupKey.replace(/_/g, ' ');
        
        // Find the group container by matching the label text
        const allGroups = document.querySelectorAll('.enhanced-subject-group');
        let groupContainer = null;
        
        allGroups.forEach(group => {
            const label = group.querySelector('.enhanced-subject-group-label');
            if (label && label.textContent.trim() === groupLabel) {
                groupContainer = group;
            }
        });
        
        if (!groupContainer) {
            console.warn('Group container not found for:', groupLabel);
            return;
        }
        
        const checkboxes = groupContainer.querySelectorAll('.enhanced-subject-checkbox:not(:disabled)');
        checkboxes.forEach(checkbox => {
            if (checkbox.checked !== checked) {
                const row = checkbox.closest('.enhanced-subject-row');
                const subjectId = row.getAttribute('data-id');
                const subject = enhancedAllSubjects.find(s => s.id == subjectId);
                
                if (subject) {
                    checkbox.checked = checked;
                    const units = parseInt(subject.units || 0);
                    
                    if (checked) {
                        const newTotal = enhancedTotalUnits + units;
                        if (newTotal <= enhancedMaxUnits) {
                            enhancedSelectedSubjects.set(subjectId.toString(), {
                                subjectId: parseInt(subjectId),
                                section: 'A',
                                units: units
                            });
                            enhancedTotalUnits = newTotal;
                            row.classList.add('selected');
                        } else {
                            checkbox.checked = false;
                            showNotification(`Cannot exceed maximum of ${enhancedMaxUnits} units`, 'error');
                        }
                    } else {
                        if (enhancedSelectedSubjects.has(subjectId.toString())) {
                            const selectedData = enhancedSelectedSubjects.get(subjectId.toString());
                            enhancedSelectedSubjects.delete(subjectId.toString());
                            enhancedTotalUnits -= parseInt(selectedData.units);
                        }
                        row.classList.remove('selected');
                    }
                }
            }
        });
        
        updateEnhancedSelectionInfo();
    };

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
    // Revised toggle subject function for table
    window.enhancedToggleSubject = function(subjectId, units, isDisabled) {
        console.log(`Enhanced toggle subject called: ${subjectId}, units: ${units}, isDisabled: ${isDisabled}`);
        
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
        
        const row = checkbox.closest('.enhanced-subject-row');
        const isNowChecked = checkbox.checked;
        
        console.log(`Enhanced subject ${subjectId} is now ${isNowChecked ? 'checked' : 'unchecked'}, current totalUnits: ${enhancedTotalUnits}`);
        
        if (isNowChecked) {
            // Checkbox was just CHECKED - ADD subject
            const newTotal = enhancedTotalUnits + parseInt(units);
            console.log(`Would be ${newTotal} units, max is ${enhancedMaxUnits}`);
            
            if (newTotal > enhancedMaxUnits) {
                showNotification(`Cannot exceed maximum of ${enhancedMaxUnits} units for this semester. Current: ${enhancedTotalUnits} units`, 'error');
                checkbox.checked = false;
                return;
            }
            
            // Store subject in Map
            enhancedSelectedSubjects.set(subjectId.toString(), {
                subjectId: subjectId,
                section: 'A', // Default section for table view
                units: parseInt(units)
            });
            enhancedTotalUnits = newTotal;
            if (row) row.classList.add('selected');
            console.log(`ENHANCED SELECTED subject ${subjectId}, added ${units} units. Total: ${enhancedTotalUnits}`);
        } else {
            // Checkbox was just UNCHECKED - REMOVE subject
            if (enhancedSelectedSubjects.has(subjectId.toString())) {
                enhancedSelectedSubjects.delete(subjectId.toString());
                enhancedTotalUnits -= parseInt(units);
                console.log(`ENHANCED DESELECTED subject ${subjectId}, removed ${units} units. Total: ${enhancedTotalUnits}`);
            }
            if (row) row.classList.remove('selected');
        }
        
        // Update the UI
        updateEnhancedSelectionInfo();
        updateSelectAllCheckbox();
        
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

    // Revised selectAllVisible function for table
    function selectAllVisible() {
        const visibleCheckboxes = document.querySelectorAll('.enhanced-subject-checkbox:not(:disabled)');
        let selectedCount = 0;
        
        visibleCheckboxes.forEach(checkbox => {
            if (!checkbox.checked) {
                const row = checkbox.closest('.enhanced-subject-row');
                const subjectId = row.getAttribute('data-id');
                const subject = enhancedAllSubjects.find(s => s.id == subjectId);
                
                if (subject) {
                    const units = parseInt(subject.units || 0);
                    const newTotal = enhancedTotalUnits + units;
                    
                    // Check if we can add this subject without exceeding max units
                    if (newTotal <= enhancedMaxUnits) {
                        checkbox.checked = true;
                        
                        // Store in Map
                        enhancedSelectedSubjects.set(subjectId.toString(), {
                            subjectId: parseInt(subjectId),
                            section: 'A', // Default section for table view
                            units: units
                        });
                        
                        enhancedTotalUnits = newTotal;
                        row.classList.add('selected');
                        selectedCount++;
                    }
                }
            }
        });
        
        if (selectedCount > 0) {
            updateEnhancedSelectionInfo();
            console.log(`Select All completed: ${selectedCount} subjects selected`);
        } else {
            showNotification('No available subjects can be selected without exceeding unit limit', 'warning');
        }
        
        // Update select all checkbox state
        updateSelectAllCheckbox();
    }

    // Revised deselectAllVisible function for table
    function deselectAllVisible() {
        const visibleCheckboxes = document.querySelectorAll('.enhanced-subject-checkbox:checked');
        
        visibleCheckboxes.forEach(checkbox => {
            const row = checkbox.closest('.enhanced-subject-row');
            const subjectId = row.getAttribute('data-id');
            const subject = enhancedAllSubjects.find(s => s.id == subjectId);
            
            if (subject) {
                checkbox.checked = false;
                
                // Remove from Map if exists
                if (enhancedSelectedSubjects.has(subjectId.toString())) {
                    const selectedData = enhancedSelectedSubjects.get(subjectId.toString());
                    enhancedSelectedSubjects.delete(subjectId.toString());
                    enhancedTotalUnits -= parseInt(selectedData.units);
                }
                
                row.classList.remove('selected');
            }
        });
        
        updateEnhancedSelectionInfo();
        updateSelectAllCheckbox();
        console.log('Deselect All completed');
    }

    // Helper function to update select all checkbox
    function updateSelectAllCheckbox() {
        const selectAllCheckbox = document.getElementById('enhancedSelectAllCheckbox');
        if (selectAllCheckbox) {
            const allSelectable = Array.from(document.querySelectorAll('.enhanced-subject-checkbox:not(:disabled)'));
            const allChecked = allSelectable.length > 0 && allSelectable.every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = !allChecked && allSelectable.some(cb => cb.checked);
        }
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
            paymentReceiptFile = null;
            isPaymentReceiptUploaded = false;
            removeFheFile(); // Reset FHE file state
            removePaymentFile(); // Reset payment file state
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
            subjectsGrid.style.display = 'block';
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

// Subject filtering and sorting functionality
function initializeSubjectFilters() {
    const yearLevelFilter = document.getElementById('year-level-filter');
    const searchInput = document.getElementById('search-subject');
    const coursesGrid = document.getElementById('courses-grid');
    
    // Check if elements exist before proceeding
    if (!yearLevelFilter || !searchInput || !coursesGrid) {
        console.warn('Subject filter elements not found');
        return;
    }
    
    function filterSubjects() {
        const yearLevelValue = yearLevelFilter.value;
        const searchValue = searchInput.value.toLowerCase().trim();
        
        const courseCards = Array.from(coursesGrid.getElementsByClassName('course-card'));
        let hasVisibleCards = false;
        
        // Filter and show/hide courses
        courseCards.forEach(card => {
            const yearLevel = card.dataset.yearLevel;
            const subjectCode = card.dataset.subjectCode || '';
            const subjectName = card.dataset.subjectName || '';
            
            let shouldShow = true;
            
            // Year level filter
            if (yearLevelValue !== 'all' && yearLevel !== yearLevelValue) {
                shouldShow = false;
            }
            
            // Search filter
            if (shouldShow && searchValue) {
                const matchesSearch = subjectCode.includes(searchValue) || 
                                    subjectName.includes(searchValue);
                if (!matchesSearch) {
                    shouldShow = false;
                }
            }
            
            // Show or hide card
            card.style.display = shouldShow ? '' : 'none';
            if (shouldShow) hasVisibleCards = true;
        });
        
        // Handle empty state
        const existingEmptyState = coursesGrid.querySelector('.no-subjects, .no-results');
        if (existingEmptyState) {
            existingEmptyState.remove();
        }
        
        if (!hasVisibleCards) {
            const noResults = document.createElement('div');
            noResults.className = 'no-results';
            noResults.innerHTML = `
                <i class="fas fa-search"></i>
                <h3>No Subjects Found</h3>
                <p>Try adjusting your filters to see more results.</p>
            `;
            coursesGrid.appendChild(noResults);
        }
    }
    
    // Add event listeners with debouncing for search input
    yearLevelFilter.addEventListener('change', filterSubjects);
    
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterSubjects, 300);
    });
    
    // Initial filter to ensure proper state
    filterSubjects();
}

// Subject filtering and sorting functionality in the My Subjects Side Bar
function initializeSubjectFilters2() {
    const yearLevelFilter = document.getElementById('year-level-filter2');
    const searchInput = document.getElementById('search-subject2');
    const coursesGrid = document.getElementById('courses-grid2');
    
    // Check if elements exist before proceeding
    if (!yearLevelFilter || !searchInput || !coursesGrid) {
        console.warn('Subject filter elements not found');
        return;
    }
    
    function filterSubjects() {
        const yearLevelValue = yearLevelFilter.value;
        const searchValue = searchInput.value.toLowerCase().trim();
        
        const courseCards = Array.from(coursesGrid.getElementsByClassName('course-card'));
        let hasVisibleCards = false;
        
        // Filter and show/hide courses
        courseCards.forEach(card => {
            const yearLevel = card.dataset.yearLevel;
            const subjectCode = card.dataset.subjectCode || '';
            const subjectName = card.dataset.subjectName || '';
            
            let shouldShow = true;
            
            // Year level filter
            if (yearLevelValue !== 'all' && yearLevel !== yearLevelValue) {
                shouldShow = false;
            }
            
            // Search filter
            if (shouldShow && searchValue) {
                const matchesSearch = subjectCode.includes(searchValue) || 
                                    subjectName.includes(searchValue);
                if (!matchesSearch) {
                    shouldShow = false;
                }
            }
            
            // Show or hide card
            card.style.display = shouldShow ? '' : 'none';
            if (shouldShow) hasVisibleCards = true;
        });
        
        // Handle empty state
        const existingEmptyState = coursesGrid.querySelector('.no-subjects, .no-results');
        if (existingEmptyState) {
            existingEmptyState.remove();
        }
        
        if (!hasVisibleCards) {
            const noResults = document.createElement('div');
            noResults.className = 'no-results';
            noResults.innerHTML = `
                <i class="fas fa-search"></i>
                <h3>No Subjects Found</h3>
                <p>Try adjusting your filters to see more results.</p>
            `;
            coursesGrid.appendChild(noResults);
        }
    }
    
    // Add event listeners with debouncing for search input
    yearLevelFilter.addEventListener('change', filterSubjects);
    
    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterSubjects, 300);
    });
    
    // Initial filter to ensure proper state
    filterSubjects();
}

// View Subject Informtaion
function initializeSubjectView() {
    // Add click event listeners to all view subject buttons
    document.querySelectorAll('.view-subject-btn').forEach(button => {
        button.addEventListener('click', function() {
            const subjectId = this.getAttribute('data-subject-id');
            const subjectCode = this.getAttribute('data-subject-code');
            const subjectName = this.getAttribute('data-subject-name');
            const units = this.getAttribute('data-units');
            const yearLevel = this.getAttribute('data-year-level');
            const semester = this.getAttribute('data-semester');
            const grade = this.getAttribute('data-grade');
            const dateEnrolled = this.getAttribute('data-date-enrolled');

            // Populate modal with basic information
            document.getElementById('subjectModalTitle').textContent = subjectCode + ' - ' + subjectName;
            document.getElementById('modalSubjectCode').textContent = subjectCode;
            document.getElementById('modalSubjectName').textContent = subjectName;
            document.getElementById('modalSubjectUnits').textContent = units;
            document.getElementById('modalYearLevel').textContent = yearLevel;
            document.getElementById('modalSemester').textContent = semester;
            document.getElementById('modalGrade').textContent = grade || 'Not Yet Graded';
            document.getElementById('modalDateEnrolled').textContent = new Date(dateEnrolled).toLocaleDateString();

            // Show loading for prerequisites
            document.getElementById('prerequisitesList').innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Loading prerequisites...</div>';
            document.getElementById('modalDescription').textContent = 'Loading description...';

            // Fetch subject details and prerequisites via AJAX
            fetchSubjectDetails(subjectId);
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('subjectModal'));
            modal.show();
        });
    });
}

// Function to fetch subject details and prerequisites
function fetchSubjectDetails(subjectId) {
    fetch(`/student/subject/${subjectId}/details`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update description
                if (data.subject.description) {
                    document.getElementById('modalDescription').textContent = data.subject.description;
                }

                // Update prerequisites
                const prerequisitesList = document.getElementById('prerequisitesList');
                if (data.prerequisites && data.prerequisites.length > 0) {
                    let html = '<ul class="list-group">';
                    data.prerequisites.forEach(prereq => {
                        html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${prereq.code}</strong> - ${prereq.name}
                                    <br><small class="text-muted">${prereq.units} units</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">${prereq.year_level}</span>
                            </li>
                        `;
                    });
                    html += '</ul>';
                    prerequisitesList.innerHTML = html;
                } else {
                    prerequisitesList.innerHTML = '<div class="alert alert-info">No prerequisites required for this course.</div>';
                }
            } else {
                document.getElementById('prerequisitesList').innerHTML = '<div class="alert alert-warning">Unable to load prerequisites.</div>';
                document.getElementById('modalDescription').textContent = 'No description available.';
            }
        })
        .catch(error => {
            console.error('Error fetching subject details:', error);
            document.getElementById('prerequisitesList').innerHTML = '<div class="alert alert-danger">Error loading prerequisites.</div>';
            document.getElementById('modalDescription').textContent = 'No description available.';
        });
}

function status_update () {
    // Create FormData
    const formData = new FormData();
    formData.append('action', 'status_now');
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
    // Send login request
    fetch('/exe/student_status', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json()) 
    .then(data => {
        if (data) {
            if (data.students_status == 'Approved') {
                $("#enroll_icon").html('<i class="fa-solid fa-circle-check"></i>');
                $("#determined").text('Officially Enrolled');
            } else {
                $("#determined").text(data.students_status);
            }
            
        } 
    })
    .catch(error => {
        alert('Network error. Please check your connection and try again.');
        console.error('Login error:', error);
    })
    .finally(() => {
        
    });
}

// Logout Functionality
function initializeLogout() {
    const logoutBtn = document.getElementById('logout-btn');
    const logoutModal = document.getElementById('logout-modal');
    const logoutCancelBtn = document.getElementById('logout-cancel-btn');
    const logoutConfirmBtn = document.getElementById('logout-confirm-btn');
    const logoutForm = document.getElementById('logout-form');

    if (!logoutBtn || !logoutModal) return;

    // Show logout confirmation modal
    logoutBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Show modal with animation
        logoutModal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent scrolling
    });

    // Close modal on cancel
    logoutCancelBtn.addEventListener('click', function() {
        logoutModal.classList.remove('active');
        document.body.style.overflow = '';
        window.location.reload();
    });

    // Close modal when clicking outside
    logoutModal.addEventListener('click', function(e) {
        if (e.target === logoutModal) {
            logoutModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && logoutModal.classList.contains('active')) {
            logoutModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // Handle logout confirmation
    logoutConfirmBtn.addEventListener('click', async function() {
        const btn = this;
        const originalText = btn.innerHTML;
        
        // Disable button and show loading
        btn.disabled = true;
        btn.classList.add('loading');
        
            // Get CSRF token from meta tag
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Send logout request
            const response = await fetch('/student/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            window.location.href = '/';
    });

    // Alternative: Use form submission (simpler but no loading state)
    // logoutConfirmBtn.addEventListener('click', function() {
    //     logoutForm.submit();
    // });
}

function count_enrolled_subjects() {
    const requestsContainer = document.getElementById('enrolled_subjects');
    
    fetch('/student/enrolled_sub', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({}) // Empty body for POST
    })
    .then(response => response.json())
    .then(data => {
        requestsContainer.textContent = data.count || 0;
    })
    .catch(error => {
        console.error('Error:', error);
        requestsContainer.textContent = '0';
    });
}

function loadAverageGradeChart() {
    // First, fetch the average grade data
    fetch('/student/average-grade', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        // Update the grade value display
        document.getElementById('averageGradeValue').textContent = data.average_grade;
        
        // Load Google Charts and draw the gauge
        google.charts.load('current', {'packages':['gauge']});
        google.charts.setOnLoadCallback(drawChart);
        
        function drawChart() {
            var chartData = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['Grade', data.gauge_percentage]
            ]);

            var options = {
                width: '100%', 
                height: 150,
                redFrom: 0, 
                redTo: 50,
                yellowFrom:50, 
                yellowTo: 75,
                greenFrom:75, 
                greenTo: 100,
                minorTicks: 5,
                majorTicks: ['0', '25', '50', '75', '100'],
                max: 100,
                min: 0
            };

            var chart = new google.visualization.Gauge(document.getElementById('averageGradeChart'));
            
            // Optional: Add animation
            var animationOptions = {
                duration: 1000,
                easing: 'out'
            };
            
            chart.draw(chartData, options);
        }
    })
    .catch(error => {
        console.error('Error loading average grade:', error);
        document.getElementById('averageGradeValue').textContent = 'Error';
    });
}

function loadSimpleAverageGrade() {
    fetch('/student/average-grade', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        const averageGrade = parseFloat(data.average_grade);
        const gradeElement = document.getElementById('averageGradeValue');
        const gradeFill = document.getElementById('gradeFill');
        
        // Display the grade
        gradeElement.textContent = data.average_grade;
        
        // Color code based on grade (Philippine system)
        if (averageGrade <= 1.5) {
            gradeElement.style.color = '#4CAF50'; // Green for excellent
        } else if (averageGrade <= 2.5) {
            gradeElement.style.color = '#FFC107'; // Yellow for good
        } else if (averageGrade <= 3.0) {
            gradeElement.style.color = '#FF9800'; // Orange for fair
        } else {
            gradeElement.style.color = '#F44336'; // Red for poor
        }
        
        // Set the fill position on the grade bar
        // Convert 1.0-5.0 scale to 0-100%
        let percentage = 100 * (averageGrade - 1.0) / 4.0;
        percentage = Math.min(100, Math.max(0, percentage));
        gradeFill.style.width = percentage + '%';
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('averageGradeValue').textContent = 'Error';
    });
}

function count_documents() {
    const requestsContainer = document.getElementById('count_documents');
    
    fetch('/student/count_documents', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({}) // Empty body for POST
    })
    .then(response => response.json())
    .then(data => {
        requestsContainer.textContent = data.count || 0;
    })
    .catch(error => {
        console.error('Error:', error);
        requestsContainer.textContent = '0';
    });
}

// Notifications functionality
let currentFilter = 'all';
let notificationsOffset = 0;
const notificationsPerPage = 10;

function loadNotifications() {
    const notificationsList = document.getElementById('notificationsList');
    const noNotifications = document.getElementById('noNotifications');
    const loadingElement = notificationsList.querySelector('.notifications-loading');
    
    if (loadingElement) {
        loadingElement.style.display = 'block';
    }
    
    // Clear existing content if loading first page
    if (notificationsOffset === 0) {
        notificationsList.innerHTML = '<div class="notifications-loading"><div class="loading-spinner"></div><p>Loading notifications...</p></div>';
    }
    
    // Fetch notifications from server
    fetch('/student/notifications', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            filter: currentFilter,
            offset: notificationsOffset,
            limit: notificationsPerPage
        })
    })
    .then(response => response.json())
    .then(data => {
        // Remove loading indicator
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
        
        if (notificationsOffset === 0) {
            notificationsList.innerHTML = '';
        }
        
        if (data.notifications && data.notifications.length > 0) {
            noNotifications.style.display = 'none';
            
            data.notifications.forEach(notification => {
                const notificationElement = createNotificationElement(notification);
                notificationsList.appendChild(notificationElement);
            });
            
            // Update statistics
            updateNotificationStats(data.total, data.unread);
            
            // Show/hide load more button
            const loadMoreBtn = document.getElementById('loadMoreNotifications');
            if (notificationsOffset + notificationsPerPage >= data.total) {
                loadMoreBtn.style.display = 'none';
            } else {
                loadMoreBtn.style.display = 'flex';
            }
        } else if (notificationsOffset === 0) {
            // No notifications
            notificationsList.innerHTML = '';
            noNotifications.style.display = 'block';
            updateNotificationStats(0, 0);
        }
    })
    .catch(error => {
        console.error('Error loading notifications:', error);
        notificationsList.innerHTML = '<div class="notification-item"><div class="notification-message" style="color: var(--danger-color);">Failed to load notifications. Please try again.</div></div>';
    });
}

function createNotificationElement(notification) {
    const div = document.createElement('div');
    div.className = `notification-item ${notification.is_read ? 'read' : 'unread'}`;
    div.dataset.id = notification.id;
    
    // Determine notification type based on title or content
    let type = 'enrollment';
    if (notification.title.includes('Grade') || notification.title.includes('grade')) {
        type = 'grades';
    } else if (notification.title.includes('Payment') || notification.title.includes('payment')) {
        type = 'payment';
    } else if (notification.title.includes('Document') || notification.title.includes('document')) {
        type = 'documents';
    }
    
    // Format time
    const time = new Date(notification.created_at);
    const timeString = time.toLocaleDateString() + ' ' + time.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    div.innerHTML = `
        <div class="notification-header">
            <div class="notification-title">
                <i class="fas fa-bell${notification.is_read ? '' : '-slash'}"></i>
                ${notification.title}
            </div>
            <div class="notification-time">${timeString}</div>
        </div>
        <div class="notification-message">${notification.message}</div>
        <div class="notification-type ${type}">
            <i class="fas ${getNotificationIcon(type)}"></i>
            ${type.charAt(0).toUpperCase() + type.slice(1)}
        </div>
        <div class="notification-status ${notification.is_read ? 'read' : 'unread'}"></div>
        <div class="notification-actions">
            <button class="notification-action-btn mark-read" data-id="${notification.id}">
                <i class="fas fa-check"></i> Mark as Read
            </button>
            <button class="notification-action-btn delete-notification" data-id="${notification.id}">
                <i class="fas fa-trash"></i> Delete
            </button>
        </div>
    `;
    
    // Add click handler for entire notification
    div.addEventListener('click', function(e) {
        if (!e.target.closest('.notification-action-btn')) {
            markNotificationAsRead(notification.id, div);
        }
    });
    
    return div;
}

function getNotificationIcon(type) {
    const icons = {
        'enrollment': 'fa-clipboard-check',
        'grades': 'fa-chart-line',
        'documents': 'fa-file-alt',
        'payment': 'fa-credit-card'
    };
    return icons[type] || 'fa-bell';
}

function updateNotificationStats(total, unread) {
    const unreadCount = document.getElementById('unreadCount');
    const totalCount = document.getElementById('totalCount');
    const headerNotificationCount = document.querySelector('.notification-count');
    
    if (unreadCount) {
        unreadCount.textContent = `${unread} unread`;
    }
    
    if (totalCount) {
        totalCount.textContent = `• ${total} total`;
    }
    
    if (headerNotificationCount) {
        headerNotificationCount.textContent = unread;
        if (unread > 0) {
            headerNotificationCount.style.display = 'flex';
        } else {
            headerNotificationCount.style.display = 'none';
        }
    }
}

function markNotificationAsRead(notificationId, element) {
    fetch('/student/notifications/mark-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (element) {
                element.classList.remove('unread');
                element.classList.add('read');
                element.querySelector('.notification-status').classList.remove('unread');
                element.querySelector('.notification-status').classList.add('read');
                element.querySelector('.notification-title i').className = 'fas fa-bell';
            }
            
            // Update statistics
            const currentUnread = parseInt(document.getElementById('unreadCount').textContent);
            updateNotificationStats(data.total, data.unread);
            
            // Update audit log
            logNotificationAction('marked as read', notificationId);
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteNotification(notificationId) {
    if (!confirm('Are you sure you want to delete this notification?')) {
        return;
    }
    
    fetch('/student/notifications/delete', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove notification from DOM
            const notificationElement = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
            if (notificationElement) {
                notificationElement.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => {
                    notificationElement.remove();
                    
                    // Check if no notifications left
                    const notificationsList = document.getElementById('notificationsList');
                    if (notificationsList.children.length === 0) {
                        document.getElementById('noNotifications').style.display = 'block';
                    }
                }, 300);
            }
            
            // Update statistics
            updateNotificationStats(data.total, data.unread);
            
            // Update audit log
            logNotificationAction('deleted', notificationId);
        }
    })
    .catch(error => console.error('Error:', error));
}

function markAllNotificationsAsRead() {
    if (!confirm('Mark all notifications as read?')) {
        return;
    }
    
    fetch('/student/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update all notification elements
            document.querySelectorAll('.notification-item.unread').forEach(element => {
                element.classList.remove('unread');
                element.classList.add('read');
                element.querySelector('.notification-status').classList.remove('unread');
                element.querySelector('.notification-status').classList.add('read');
                element.querySelector('.notification-title i').className = 'fas fa-bell';
            });
            
            // Update statistics
            updateNotificationStats(data.total, 0);
            
            // Update audit log
            logNotificationAction('marked all as read');
        }
    })
    .catch(error => console.error('Error:', error));
}

function clearAllNotifications() {
    if (!confirm('Are you sure you want to clear all notifications? This action cannot be undone.')) {
        return;
    }
    
    fetch('/student/notifications/clear-all', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear notifications list
            const notificationsList = document.getElementById('notificationsList');
            notificationsList.innerHTML = '';
            
            // Show empty state
            document.getElementById('noNotifications').style.display = 'block';
            
            // Update statistics
            updateNotificationStats(0, 0);
            
            // Update audit log
            logNotificationAction('cleared all');
        }
    })
    .catch(error => console.error('Error:', error));
}

function initializeNotifications() {
    // Load initial notifications
    loadNotifications();
    
    // Event listeners
    const notificationFilter = document.getElementById('notificationFilter');
    if (notificationFilter) {
        notificationFilter.addEventListener('change', function() {
            currentFilter = this.value;
            notificationsOffset = 0;
            loadNotifications();
        });
    }
    
    const markAllReadBtn = document.getElementById('markAllRead');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', markAllNotificationsAsRead);
    }
    
    const clearAllBtn = document.getElementById('clearAllNotifications');
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', clearAllNotifications);
    }
    
    const loadMoreBtn = document.getElementById('loadMoreNotifications');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            notificationsOffset += notificationsPerPage;
            loadNotifications();
        });
    }
    
    // Delegate event listeners for dynamic content
    document.addEventListener('click', function(e) {
        // Mark as read button
        if (e.target.closest('.mark-read')) {
            const btn = e.target.closest('.mark-read');
            const notificationId = btn.dataset.id;
            const notificationElement = btn.closest('.notification-item');
            markNotificationAsRead(notificationId, notificationElement);
        }
        
        // Delete notification button
        if (e.target.closest('.delete-notification')) {
            const btn = e.target.closest('.delete-notification');
            const notificationId = btn.dataset.id;
            deleteNotification(notificationId);
        }
    });
    
}

function initializeFilesSystem() {
    // Load files when the section becomes active
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            if (targetId === 'files-section') {
                loadAllFiles();
                updateFileStatistics();
            }
        });
    });
    
    // Year level filter change
    const yearLevelFilter = document.getElementById('yearLevelFilter');
    if (yearLevelFilter) {
        yearLevelFilter.addEventListener('change', function() {
            filterFilesByYear(this.value);
        });
    }
    
    // Tab change listeners
    const yearTabs = document.querySelectorAll('#yearTabs button[data-bs-toggle="pill"]');
    yearTabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            const targetId = event.target.getAttribute('data-bs-target');
            loadFilesForYear(targetId.replace('#', ''));
        });
    });
    
    // Initial load if already on files section
    if (document.getElementById('files-section').classList.contains('active')) {
        loadAllFiles();
        updateFileStatistics();
    }
}

function loadAllFiles() {
    // Load academic files
    loadAcademicFiles();
    
    // Load payment files
    loadPaymentFiles();

    // Load required documents
    loadRequiredDocuments();
}

function loadAcademicFiles(yearLevel = 'all') {
    const container = yearLevel === 'all' ? 
        document.getElementById('all-files-container') : 
        document.getElementById(`${yearLevel}-files`);
    
    if (!container) return;
    
    container.innerHTML = `
        <div class="loading-state">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading files...</p>
        </div>
    `;
    
    // Fetch academic files from server
    fetch('/student/files/academic', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.files && data.files.length > 0) {
            let filteredFiles = data.files;
            
            // Filter by year level if not 'all'
            if (yearLevel !== 'all') {
                // Convert yearLevel from 'year1', 'year2', 'year3', 'year4' to '1st Year', '2nd Year', '3rd Year', '4th Year'
                const yearMap = {
                    'year1': '1st Year',
                    'year2': '2nd Year',
                    'year3': '3rd Year',
                    'year4': '4th Year'
                };
                
                const targetYear = yearMap[yearLevel];
                if (targetYear) {
                    filteredFiles = data.files.filter(file => file.year_level === targetYear);
                }
            }
            
            displayFiles(filteredFiles, container, 'academic');
        } else {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h4>No academic files found</h4>
                    <p>You haven't uploaded any academic files yet.</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading academic files:', error);
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h4>Error loading files</h4>
                <p>Unable to load academic files. Please try again later.</p>
            </div>
        `;
    });
}


function loadPaymentFiles() {
    const container = document.getElementById('payment-files-container');
    if (!container) return;
    
    container.innerHTML = `
        <div class="loading-state">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading payment records...</p>
        </div>
    `;
    
    // Fetch payment files from server
    fetch('/student/files/payments', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.payments && data.payments.length > 0) {
            displayPaymentFiles(data.payments, container);
        } else {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h4>No payment records found</h4>
                    <p>You don't have any payment records yet.</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading payment files:', error);
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h4>Error loading payment records</h4>
                <p>Unable to load payment records. Please try again later.</p>
            </div>
        `;
    });
}

function loadFilesForYear(yearId) {
    if (yearId === 'all') {
        loadAllFiles();
    } else {
        loadAcademicFiles(yearId);
        // Note: Required documents are usually shown for all years
    }
}

function filterFilesByYear(yearLevel) {
    const allContainers = ['all-files-container', 'year1-files', 'year2-files', 'year3-files', 'year4-files'];
    
    allContainers.forEach(containerId => {
        const container = document.getElementById(containerId);
        if (container) {
            container.style.display = 'none';
        }
    });
    
    if (yearLevel === 'all') {
        const container = document.getElementById('all-files-container');
        if (container) {
            container.style.display = 'grid';
            loadAcademicFiles('all');
        }
    } else {
        const yearId = 'year' + yearLevel.split(' ')[0];
        const container = document.getElementById(yearId + '-files');
        if (container) {
            container.style.display = 'grid';
            loadAcademicFiles(yearId);
        }
    }
}

function displayFiles(files, container, type = 'academic') {
    if (!files || files.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h4>No files found</h4>
                <p>No ${type} files available.</p>
            </div>
        `;
        return;
    }
    
    // Create table structure with unique ID based on container
    const tableId = container.id + '-table-body';
    container.innerHTML = `
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;"><i class="fas fa-file"></i></th>
                        <th>File Type</th>
                        <th>Year Level</th>
                        <th>Title</th>
                        <th>Upload Date</th>
                        <th style="width: 150px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="${tableId}">
                </tbody>
            </table>
        </div>
    `;
    
    const tbody = container.querySelector(`#${tableId}`);
    
    files.forEach(file => {
        const row = createFileTableRow(file, type);
        tbody.appendChild(row);
    });
}

function createFileTableRow(file, type = 'academic') {
    const tr = document.createElement('tr');
    
    // Determine file type icon and color
    let fileIcon = 'file';
    let fileIconClass = 'text-secondary';
    
    if (file.file_path) {
        const ext = file.file_path.split('.').pop().toLowerCase();
        if (ext === 'pdf') {
            fileIcon = 'file-pdf';
            fileIconClass = 'text-danger';
        } else if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
            fileIcon = 'file-image';
            fileIconClass = 'text-info';
        } else if (['doc', 'docx'].includes(ext)) {
            fileIcon = 'file-word';
            fileIconClass = 'text-primary';
        }
    }
    
    const uploadDate = new Date(file.upload_date || file.created_at);
    const formattedDate = uploadDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
    
    tr.innerHTML = `
        <td class="text-center">
            <i class="fas fa-${fileIcon} ${fileIconClass}" style="font-size: 1.5rem;"></i>
        </td>
        <td>
            <span class="badge bg-primary">${file.type || 'Academic'}</span>
        </td>
        <td>
            <span class="badge bg-secondary">${file.year_level || 'N/A'}</span>
        </td>
        <td>
            <strong>${file.title || file.type || 'Academic File'}</strong>
            ${file.description ? `<br><small class="text-muted">${file.description}</small>` : ''}
        </td>
        <td>
            <i class="far fa-calendar me-1"></i>${formattedDate}
        </td>
        <td class="text-center">
            <div class="btn-group btn-group-sm" role="group">
                <a href="${file.file_path}" target="_blank" class="btn btn-outline-primary btn-sm" title="View">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="${file.file_path}" download class="btn btn-outline-success btn-sm" title="Download">
                    <i class="fas fa-download"></i>
                </a>
            </div>
        </td>
    `;
    
    return tr;
}


function displayPaymentFiles(payments, container) {
    if (!payments || payments.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <h4>No payment records found</h4>
                <p>You don't have any payment records yet.</p>
            </div>
        `;
        return;
    }
    
    // Create table structure with unique ID based on container
    const tableId = container.id + '-table-body';
    container.innerHTML = `
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;"><i class="fas fa-receipt"></i></th>
                        <th>Payment Type</th>
                        <th>Year Level</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="width: 150px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="${tableId}">
                </tbody>
            </table>
        </div>
    `;
    
    const tbody = container.querySelector(`#${tableId}`);
    
    payments.forEach(payment => {
        const row = createPaymentTableRow(payment);
        tbody.appendChild(row);
    });
}

function createPaymentTableRow(payment) {
    const tr = document.createElement('tr');
    
    // Determine status and icon
    let statusClass = 'warning';
    let statusText = payment.status || 'Pending';
    let statusBadgeClass = 'bg-warning';
    let fileIcon = 'money-bill-wave';
    let iconClass = 'text-success';
    
    // Map status to appropriate classes
    if (payment.status === 'Completed' || payment.status === 'Paid' || payment.status === 'Approved') {
        statusClass = 'success';
        statusBadgeClass = 'bg-success';
        fileIcon = 'check-circle';
        iconClass = 'text-success';
    } else if (payment.status === 'Failed' || payment.status === 'Rejected') {
        statusClass = 'danger';
        statusBadgeClass = 'bg-danger';
        fileIcon = 'times-circle';
        iconClass = 'text-danger';
    } else if (payment.status === 'Pending') {
        statusClass = 'warning';
        statusBadgeClass = 'bg-warning';
        fileIcon = 'clock';
        iconClass = 'text-warning';
    } else if (payment.red_flag) {
        statusClass = 'danger';
        statusBadgeClass = 'bg-danger';
        fileIcon = 'exclamation-triangle';
        iconClass = 'text-danger';
    }
    
    // Format the payment date
    const paymentDate = new Date(payment.payment_date || payment.uploaded_date || new Date());
    const formattedDate = paymentDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
    
    // Format amount
    const formattedAmount = payment.amount ? 
        `₱${parseFloat(payment.amount).toFixed(2)}` : 
        '₱0.00';
    
    // Red flag warning
    const redFlagWarning = payment.red_flag ? 
        `<br><small class="text-danger"><i class="fas fa-exclamation-circle"></i> ${payment.red_flag}</small>` : '';
    
    tr.innerHTML = `
        <td class="text-center">
            <i class="fas fa-${fileIcon} ${iconClass}" style="font-size: 1.5rem;"></i>
        </td>
        <td>
            <strong>${payment.type || 'Payment'}</strong>
            ${payment.id ? `<br><small class="text-muted">ID: #${payment.id}</small>` : ''}
        </td>
        <td>
            <span class="badge bg-secondary">${payment.year_level || 'N/A'}</span>
        </td>
        <td>
            <strong class="text-success">${formattedAmount}</strong>
        </td>
        <td>
            <span class="badge ${statusBadgeClass}">${statusText}</span>
            ${redFlagWarning}
        </td>
        <td>
            <i class="far fa-calendar me-1"></i>${formattedDate}
        </td>
        <td class="text-center">
            <div class="btn-group btn-group-sm" role="group">
                ${payment.receipt_url ? `
                    <a href="${payment.receipt_url}" target="_blank" class="btn btn-outline-primary btn-sm" title="View Receipt">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="${payment.receipt_url}" download class="btn btn-outline-success btn-sm" title="Download">
                        <i class="fas fa-download"></i>
                    </a>
                ` : `
                    <button class="btn btn-outline-secondary btn-sm" disabled title="No receipt available">
                        <i class="fas fa-ban"></i>
                    </button>
                `}
            </div>
        </td>
    `;
    
    return tr;
}


function createOrganizationFeeCard(payment) {
    const div = document.createElement('div');
    div.className = 'file-card';
    
    // Determine status and icon
    let statusClass = 'pending';
    let statusText = payment.status || 'Pending';
    let fileIcon = 'money-bill-wave'; // Default icon for payments
    
    // Map status to appropriate classes
    if (payment.status === 'Completed' || payment.status === 'Paid') {
        statusClass = 'completed';
        fileIcon = 'check-circle';
    } else if (payment.status === 'Failed' || payment.status === 'Rejected') {
        statusClass = 'failed';
        fileIcon = 'times-circle';
    } else if (payment.status === 'Pending') {
        statusClass = 'pending';
        fileIcon = 'clock';
    } else if (payment.red_flag) {
        statusClass = 'failed'; // Red flag gets warning color
        fileIcon = 'exclamation-triangle';
    }
    
    // Format the payment date
    const paymentDate = new Date(payment.payment_date || payment.uploaded_date || new Date());
    const formattedDate = paymentDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    // Format amount
    const formattedAmount = payment.amount ? 
        `₱${parseFloat(payment.amount).toFixed(2)}` : 
        '₱0.00';
    
    // Determine if there's a red flag warning
    const redFlagWarning = payment.red_flag ? 
        `<div class="red-flag-warning">
            <i class="fas fa-exclamation-circle"></i>
            <span>${payment.red_flag}</span>
        </div>` : '';
    
    div.innerHTML = `
        <div class="file-icon payment ${statusClass}">
            <i class="fas fa-${fileIcon}"></i>
        </div>
        <div class="file-header">
            <span class="file-type-badge">${payment.type || 'Organization Fee'}</span>
            <span class="payment-status ${statusClass}">
                <i class="fas fa-circle"></i> ${statusText}
            </span>
        </div>
        <h5 class="file-title">${payment.type || 'Organization Fee'} #${payment.id || 'N/A'}</h5>
        <p class="file-description">${payment.type || 'Organization'} fee payment record</p>
        
        ${redFlagWarning}
        
        <div class="payment-details">
            <div class="detail-item">
                <span class="detail-label">Amount:</span>
                <span class="detail-value amount">${formattedAmount}</span>
            </div>
        </div>
        
        <div class="file-meta">
            <span class="file-date">
                <i class="far fa-calendar"></i> ${formattedDate}
            </span>
            <span class="file-size">Fee</span>
        </div>
        <div class="file-actions">
            ${payment.receipt_url ? `
                <a href="${payment.receipt_url}" target="_blank" class="btn-file-action primary">
                    <i class="fas fa-eye"></i> View Receipt
                </a>
                <a href="${payment.receipt_url}" download class="btn-file-action">
                    <i class="fas fa-download"></i> Download
                </a>
            ` : `
                <button class="btn-file-action" disabled>
                    <i class="fas fa-ban"></i> No Receipt
                </button>
            `}
        </div>
    `;
    
    return div;
}

function createFileCard(file, type) {
    const div = document.createElement('div');
    div.className = 'file-card';
    
    // Determine file type icon and color
    let fileIcon = 'document';
    let fileIconClass = 'document';
    
    if (file.file_path) {
        const ext = file.file_path.split('.').pop().toLowerCase();
        if (ext === 'pdf') {
            fileIcon = 'file-pdf';
            fileIconClass = 'pdf';
        } else if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
            fileIcon = 'file-image';
            fileIconClass = 'image';
        } else if (['doc', 'docx'].includes(ext)) {
            fileIcon = 'file-word';
            fileIconClass = 'document';
        }
    }
    
    const uploadDate = new Date(file.upload_date || file.created_at);
    const formattedDate = uploadDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    div.innerHTML = `
        <div class="file-icon ${fileIconClass}">
            <i class="fas fa-${fileIcon}"></i>
        </div>
        <div class="file-header">
            <span class="file-type-badge">${file.type || 'Academic'}</span>
            <span class="badge bg-secondary">${file.year_level || 'N/A'}</span>
        </div>
        <h5 class="file-title">${file.title || file.type || 'Academic File'}</h5>
        <p class="file-description">${file.description || 'Academic document for ' + (file.year_level || 'current year')}</p>
        <div class="file-meta">
            <span class="file-date">${formattedDate}</span>
            <span class="file-size">${file.file_size || 'N/A'}</span>
        </div>
        <div class="file-actions">
            <a href="${file.file_path}" target="_blank" class="btn-file-action primary">
                <i class="fas fa-eye"></i> View
            </a>
            <a href="${file.file_path}" download class="btn-file-action">
                <i class="fas fa-download"></i> Download
            </a>
        </div>
    `;
    
    return div;
}

function createPaymentCard(payment) {
    const div = document.createElement('div');
    div.className = 'file-card';
    
    const paymentDate = new Date(payment.payment_date || payment.created_at);
    const formattedDate = paymentDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    let statusClass = 'pending';
    let statusText = 'Pending';
    
    if (payment.status === 'Completed') {
        statusClass = 'completed';
        statusText = 'Completed';
    } else if (payment.status === 'Failed') {
        statusClass = 'failed';
        statusText = 'Failed';
    }
    
    div.innerHTML = `
        <div class="file-icon payment">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="file-header">
            <span class="file-type-badge">Payment Receipt</span>
            <span class="payment-status ${statusClass}">
                <i class="fas fa-circle"></i> ${statusText}
            </span>
        </div>
        <h5 class="file-title">Payment Receipt #${payment.id || 'N/A'}</h5>
        <p class="file-description">Payment for enrollment and fees</p>
        <div class="mb-3">
            <strong>Amount:</strong> ₱${parseFloat(payment.amount || 0).toFixed(2)}
        </div>
        <div class="file-meta">
            <span class="file-date">${formattedDate}</span>
            <span class="file-size">Receipt</span>
        </div>
        <div class="file-actions">
            ${payment.receipt_url ? `
                <a href="${payment.receipt_url}" target="_blank" class="btn-file-action primary">
                    <i class="fas fa-eye"></i> View Receipt
                </a>
                <a href="${payment.receipt_url}" download class="btn-file-action">
                    <i class="fas fa-download"></i> Download
                </a>
            ` : `
                <button class="btn-file-action" disabled>
                    <i class="fas fa-exclamation-circle"></i> No Receipt
                </button>
            `}
        </div>
    `;
    
    return div;
}

function updateFileStatistics() {
    // Fetch statistics from server
    fetch('/student/files/statistics', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('totalFiles').textContent = data.totalFiles || 0;
            document.getElementById('academicFiles').textContent = data.academicFiles || 0;
            document.getElementById('paymentFiles').textContent = data.paymentFiles || 0;
            document.getElementById('totalSize').textContent = (data.totalSize || 0) + ' MB';
        }
    })
    .catch(error => {
        console.error('Error loading file statistics:', error);
    });
}


function loadRequiredDocuments() {
    const container = document.getElementById('required-documents-container');
    if (!container) return;
    
    container.innerHTML = `
        <div class="loading-state">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading required documents...</p>
        </div>
    `;
    
    console.log('Fetching required documents...'); // Debug log
    
    // Fetch required documents from server
    fetch('/student/files/required-documents', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Response status:', response.status); // Debug log
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data); // Debug log
        if (data.success && data.documents && data.documents.length > 0) {
            displayRequiredDocuments(data.documents, container);
        } else if (data.success && (!data.documents || data.documents.length === 0)) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h4>No required documents found</h4>
                    <p>You haven't uploaded any required documents yet.</p>
                </div>
            `;
        } else {
            // Handle server-side error message
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h4>Error loading required documents</h4>
                    <p>${data.message || 'Unable to load required documents.'}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading required documents:', error);
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h4>Error loading required documents</h4>
                <p>Unable to load required documents. Please try again later.</p>
                <p class="text-muted small">${error.message}</p>
            </div>
        `;
    });
}

function displayRequiredDocuments(documents, container) {
    if (!documents || documents.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h4>No required documents found</h4>
                <p>You haven't uploaded any required documents yet.</p>
            </div>
        `;
        return;
    }
    
    // Create table structure with unique ID based on container
    const tableId = container.id + '-table-body';
    container.innerHTML = `
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;"><i class="fas fa-file"></i></th>
                        <th>Document Type</th>
                        <th>Year Level</th>
                        <th>Document Name</th>
                        <th>Status</th>
                        <th>Upload Date</th>
                        <th style="width: 150px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="${tableId}">
                </tbody>
            </table>
        </div>
    `;
    
    const tbody = container.querySelector(`#${tableId}`);
    
    documents.forEach(doc => {
        const row = createRequiredDocumentTableRow(doc);
        tbody.appendChild(row);
    });
}

function createRequiredDocumentTableRow(doc) {
    const tr = document.createElement('tr');
    
    // Determine file type icon and color based on document type
    let fileIcon = 'file-alt';
    let iconClass = 'text-secondary';
    let badgeColor = 'bg-secondary';
    
    switch(doc.type) {
        case 'FORM138A':
        case 'FORM138B':
            fileIcon = 'file-contract';
            iconClass = 'text-primary';
            badgeColor = 'bg-primary';
            break;
        case 'GOOD_MORAL':
            fileIcon = 'file-contract';
            iconClass = 'text-success';
            badgeColor = 'bg-success';
            break;
        case 'PSA_NSO':
            fileIcon = 'id-card';
            iconClass = 'text-info';
            badgeColor = 'bg-info';
            break;
        case 'ID_PICTURE':
            fileIcon = 'file-image';
            iconClass = 'text-warning';
            badgeColor = 'bg-warning';
            break;
        case 'BIRTH_CERTIFICATE':
            fileIcon = 'file-medical';
            iconClass = 'text-danger';
            badgeColor = 'bg-danger';
            break;
    }
    
    // Format the upload date
    const uploadDate = new Date(doc.upload_date);
    const formattedDate = uploadDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
    
    // Create readable document type name
    const docTypeName = doc.type_name || doc.type.split('_').join(' ').replace('ID', 'ID');
    
    // Status badge
    let statusBadge = 'bg-secondary';
    let statusText = 'Unknown';
    if (doc.status) {
        if (doc.status === 'Approved') {
            statusBadge = 'bg-success';
            statusText = 'Approved';
        } else if (doc.status === 'Rejected') {
            statusBadge = 'bg-danger';
            statusText = 'Rejected';
        } else {
            statusBadge = 'bg-warning';
            statusText = 'Pending';
        }
    }
    
    tr.innerHTML = `
        <td class="text-center">
            <i class="fas fa-${fileIcon} ${iconClass}" style="font-size: 1.5rem;"></i>
        </td>
        <td>
            <span class="badge ${badgeColor}">${doc.type}</span>
        </td>
        <td>
            <span class="badge bg-secondary">${doc.year_level || 'N/A'}</span>
        </td>
        <td>
            <strong>${docTypeName}</strong>
            ${doc.description ? `<br><small class="text-muted">${doc.description}</small>` : ''}
        </td>
        <td>
            <span class="badge ${statusBadge}">${statusText}</span>
        </td>
        <td>
            <i class="far fa-calendar me-1"></i>${formattedDate}
        </td>
        <td class="text-center">
            <div class="btn-group btn-group-sm" role="group">
                ${doc.file_path ? `
                    <a href="${doc.file_path}" target="_blank" class="btn btn-outline-primary btn-sm" title="View">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="${doc.file_path}" download class="btn btn-outline-success btn-sm" title="Download">
                        <i class="fas fa-download"></i>
                    </a>
                ` : `
                    <button class="btn btn-outline-secondary btn-sm" disabled title="No file available">
                        <i class="fas fa-ban"></i>
                    </button>
                `}
            </div>
        </td>
    `;
    
    return tr;
}

function createRequiredDocumentCard(doc) { 
    const div = document.createElement('div');
    div.className = 'file-card';
    
    // Determine file type icon and color based on document type
    let fileIcon = 'file-alt';
    let fileIconClass = 'document';
    let badgeColor = 'bg-secondary';
    
    switch(doc.type) {  // Changed from document.type to doc.type
        case 'FORM138A':
        case 'FORM138B':
            fileIcon = 'file-contract';
            fileIconClass = 'prospectus';
            badgeColor = 'bg-primary';
            break;
        case 'GOOD_MORAL':
            fileIcon = 'file-contract';
            fileIconClass = 'document';
            badgeColor = 'bg-success';
            break;
        case 'PSA_NSO':
            fileIcon = 'id-card';
            fileIconClass = 'payment';
            badgeColor = 'bg-info';
            break;
        case 'ID_PICTURE':
            fileIcon = 'file-image';
            fileIconClass = 'image';
            badgeColor = 'bg-warning';
            break;
        case 'BIRTH_CERTIFICATE':
            fileIcon = 'file-medical';
            fileIconClass = 'document';
            badgeColor = 'bg-danger';
            break;
    }
    
    // Format the upload date
    const uploadDate = new Date(doc.upload_date);  // Changed from document.upload_date
    const formattedDate = uploadDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    // Use actual file size from the server response
    const fileSize = doc.file_size || "N/A";
    
    // Create readable document type name
    const docTypeName = doc.type.split('_').join(' ').replace('ID', 'ID');  // Changed from document.type
    
    div.innerHTML = `
        <div class="file-icon ${fileIconClass}">
            <i class="fas fa-${fileIcon}"></i>
        </div>
        <div class="file-header">
            <span class="file-type-badge">Required Document</span>
            <span class="badge ${badgeColor}">${doc.year_level || 'N/A'}</span>  <!-- Changed -->
        </div>
        <h5 class="file-title">${doc.type_name || docTypeName}</h5>  <!-- Added type_name from server -->
        <p class="file-description">${getDocumentDescription(doc.type)}</p>  <!-- Changed -->
        
        <div class="document-type-badge mb-3">
            <span class="badge bg-dark">${doc.type}</span>  <!-- Changed -->
        </div>
        
        <div class="file-meta">
            <span class="file-date">
                <i class="far fa-calendar"></i> ${formattedDate}
            </span>
            <span class="file-size">
                <i class="fas fa-hdd"></i> ${fileSize}
            </span>
        </div>
        
        <div class="file-actions">
            ${doc.file_path ? `  <!-- Changed -->
                <a href="${doc.file_path}" target="_blank" class="btn-file-action primary">  <!-- Changed -->
                    <i class="fas fa-eye"></i> View Document
                </a>
                <a href="${doc.file_path}" download class="btn-file-action">  <!-- Changed -->
                    <i class="fas fa-download"></i> Download
                </a>
            ` : `
                <button class="btn-file-action" disabled>
                    <i class="fas fa-ban"></i> No File
                </button>
            `}
        </div>
    `;
    
    return div;
}

function getDocumentDescription(type) {
    const descriptions = {
        'FORM138A': 'High School Report Card (Form 138) - Original Copy',
        'FORM138B': 'High School Report Card (Form 138) - Photocopy',
        'GOOD_MORAL': 'Certificate of Good Moral Character',
        'PSA_NSO': 'PSA/NSO Birth Certificate (Original and Photocopy)',
        'ID_PICTURE': '2x2 ID Picture with White Background',
        'BIRTH_CERTIFICATE': 'Birth Certificate (Original)'
    };
    
    return descriptions[type] || 'Required document for enrollment';
}


function loadSimpleAverageGrade2() {
    fetch('/student/average-grade2', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update average grade
            document.querySelector('.info-item:last-child .info-value').textContent = data.average_grade;
            
            // Calculate and update rating
            const avg = parseFloat(data.average_grade);
            let rating = 1;
            
            if (avg >= 1.0 && avg <= 1.5) rating = 5;
            else if (avg > 1.5 && avg <= 2.0) rating = 4;
            else if (avg > 2.0 && avg <= 2.5) rating = 3;
            else if (avg > 2.5 && avg <= 3.0) rating = 2;
            
            document.querySelector('.info-item:nth-child(3) .info-value').textContent = rating;
        }
    })
    .catch(error => console.error('Error loading average grade:', error));
}

function count_enrolled_subjects2() {
    fetch('/student/enrolled_sub2', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update courses count
            document.querySelector('.profile-stat:first-child .stat-number').textContent = data.courses_count;
            
            // Update total units
            document.querySelector('.profile-stat:nth-child(2) .stat-number').textContent = data.total_units;
        }
    })
    .catch(error => console.error('Error counting enrolled subjects:', error));
}

// Document Upload Modal JavaScript
// Document Upload Modal JavaScript with Step-by-Step Process
function initializeDocumentsUpload() {
    const modal = document.getElementById('studentDocumentsModal');
    const form = document.getElementById('studentDocumentsForm');
    const fileInputs = document.querySelectorAll('.document-input');
    const previewButtons = document.querySelectorAll('.btn-preview');
    const previewModal = document.querySelector('.preview-modal-overlay');
    const closePreview = document.querySelector('.close-preview');
    const submitBtn = form.querySelector('.btn-submit');
    const nextBtn = form.querySelector('.btn-next');
    const prevBtn = form.querySelector('.btn-prev');
    const progressFill = document.getElementById('uploadProgressFill');
    const progressSteps = document.querySelectorAll('.progress-step');
    const documentSteps = document.querySelectorAll('.documents-step');
    const currentStepSpan = document.querySelector('.current-step');
    const totalStepsSpan = document.querySelector('.total-steps');
    const confirmationCheckbox = document.getElementById('confirmationCheckbox');
    
    const uploadedFiles = {};
    let currentStep = 1;
    const totalSteps = parseInt(totalStepsSpan.textContent);
    
    // Initialize progress
    updateProgress();
    
    // Step Navigation
    nextBtn.addEventListener('click', nextStep);
    prevBtn.addEventListener('click', prevStep);
    
    function nextStep() {
        if (currentStep < totalSteps) {
            // Validate current step before proceeding
            if (!validateCurrentStep()) {
                showNotification('Please upload a file or skip this step before proceeding.', 'error');
                return;
            }
            
            currentStep++;
            updateStep();
        }
    }
    
    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            updateStep();
        }
    }
    
    function updateStep() {
        // Update current step display
        currentStepSpan.textContent = currentStep;
        
        // Update active step in progress indicator
        progressSteps.forEach((step, index) => {
            const stepNumber = parseInt(step.dataset.step);
            if (stepNumber === currentStep) {
                step.classList.add('active');
            } else if (stepNumber < currentStep) {
                step.classList.remove('active');
                step.classList.add('completed');
            } else {
                step.classList.remove('active', 'completed');
            }
        });
        
        // Show/hide document steps
        documentSteps.forEach(step => {
            const stepNumber = parseInt(step.dataset.step);
            if (stepNumber === currentStep) {
                step.classList.add('active');
            } else {
                step.classList.remove('active');
            }
        });
        
        // Update progress bar
        updateProgress();
        
        // Update navigation buttons
        prevBtn.disabled = currentStep === 1;
        
        if (currentStep === totalSteps) {
            // Last step - show submit button
            nextBtn.style.display = 'none';
            submitBtn.style.display = 'flex';
            updateReviewStep();
        } else {
            nextBtn.style.display = 'flex';
            submitBtn.style.display = 'none';
        }
        
        // Update next button text if needed
        if (currentStep === totalSteps - 1) {
            nextBtn.innerHTML = 'Review <i class="fas fa-clipboard-check"></i>';
        } else if (currentStep === totalSteps) {
            nextBtn.style.display = 'none';
        } else {
            nextBtn.innerHTML = 'Next <i class="fas fa-arrow-right"></i>';
        }
        
        // Scroll to top of step
        modal.querySelector('.documents-step.active').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
    function updateProgress() {
        const progressPercentage = ((currentStep - 1) / (totalSteps - 1)) * 100;
        progressFill.style.width = `${progressPercentage}%`;
    }
    
    function validateCurrentStep() {
        const currentStepElement = document.querySelector(`.documents-step[data-step="${currentStep}"]`);
        const documentCard = currentStepElement.querySelector('.document-card');
        
        if (!documentCard) return true; // Review step doesn't need validation
        
        const documentType = documentCard.dataset.document;
        const fileInput = document.getElementById(documentType);
        
        // If it's a conditional document and no file uploaded, it's okay
        if (documentCard.classList.contains('conditional') && !uploadedFiles[documentType]) {
            return true;
        }
        
        // For required documents, check if file is uploaded
        if (documentCard.classList.contains('required') && !uploadedFiles[documentType]) {
            return false;
        }
        
        return true;
    }
    
    function updateReviewStep() {
        // Update status for each document in review
        const documents = ['form138a', 'good_moral', 'psa_nso', 'id_picture'];
        if (totalSteps === 6) {
            documents.push('marriage_certificate');
        }
        
        documents.forEach(doc => {
            const fileData = uploadedFiles[doc];
            const statusIndicator = document.getElementById(`${doc}-status-indicator`);
            const statusText = document.getElementById(`${doc}-status-text`);
            const reviewStatus = document.getElementById(`${doc}-review-status`);
            
            if (fileData) {
                statusIndicator.className = 'status-indicator uploaded';
                statusText.className = 'status-text uploaded';
                statusText.textContent = 'Uploaded';
                reviewStatus.textContent = `${fileData.name} (${fileData.size})`;
            } else {
                const documentCard = document.querySelector(`[data-document="${doc}"]`);
                if (documentCard && documentCard.classList.contains('conditional')) {
                    statusIndicator.className = 'status-indicator optional';
                    statusText.className = 'status-text optional';
                    statusText.textContent = 'Optional';
                    reviewStatus.textContent = 'Not required';
                } else {
                    statusIndicator.className = 'status-indicator missing';
                    statusText.className = 'status-text missing';
                    statusText.textContent = 'Missing';
                    reviewStatus.textContent = 'Not uploaded';
                }
            }
        });
    }
    
    // File Upload Handling (keep existing with updates)
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = this.files[0];
            const documentType = this.name;
            const previewDiv = document.getElementById(this.dataset.preview);
            const documentCard = this.closest('.document-card');
            
            if (file) {
                // Validate file
                if (!validateFile(file, documentType)) {
                    this.value = '';
                    return;
                }
                
                // Store file
                uploadedFiles[documentType] = {
                    file: file,
                    name: file.name,
                    size: formatFileSize(file.size),
                    type: file.type,
                    lastModified: new Date(file.lastModified).toLocaleDateString()
                };
                
                // Update preview
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewDiv.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewDiv.innerHTML = `
                        <i class="fas fa-file-pdf" style="color: #e53e3e;"></i>
                        <p>${file.name}</p>
                        <small>${formatFileSize(file.size)}</small>
                    `;
                }
                
                // Update status text
                const statusSpan = documentCard.querySelector('.document-status');
                statusSpan.textContent = 'Uploaded';
                statusSpan.className = 'document-status uploaded';
                
                // Show success animation
                showUploadSuccess(previewDiv);
                
                // Auto-advance to next step if this is the current step
                setTimeout(() => {
                    if (parseInt(documentCard.closest('.documents-step').dataset.step) === currentStep) {
                        nextStep();
                    }
                }, 1000);
            }
        });
    });
    
    // Form Submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Check confirmation
        if (confirmationCheckbox && !confirmationCheckbox.checked) {
            showNotification('Please confirm that all documents are authentic before submitting.', 'error');
            return;
        }
        
        // Check if required files are uploaded
        const requiredDocs = ['form138a', 'good_moral', 'psa_nso', 'id_picture'];
        const missingDocs = requiredDocs.filter(doc => !uploadedFiles[doc]);
        
        if (missingDocs.length > 0) {
            showNotification(`Missing required documents: ${missingDocs.join(', ')}. Please go back and upload them.`, 'error');
            return;
        }
        
        submitForm();
    });
    
    // Helper Functions (keep existing)
    function validateFile(file, documentType) {
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            showNotification(`File size must be less than 5MB. Your file is ${formatFileSize(file.size)}.`, 'error');
            return false;
        }
        
        let allowedTypes = [];
        
        switch(documentType) {
            case 'id_picture':
                allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                break;
            default:
                allowedTypes = [
                    'image/jpeg', 'image/jpg', 'image/png', 
                    'application/pdf', 'application/x-pdf',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/msword', 'application/octet-stream'
                ];
        }
        
        if (!allowedTypes.includes(file.type)) {
            showNotification(`Please upload ${allowedTypes.includes('application/pdf') ? 'PDF or image files' : 'image files only'}.`, 'error');
            return false;
        }
        
        return true;
    }
    
    function showUploadSuccess(element) {
        const successDiv = document.createElement('div');
        successDiv.className = 'upload-success';
        successDiv.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <span>Uploaded</span>
        `;
        
        element.appendChild(successDiv);
        
        setTimeout(() => {
            successDiv.remove();
        }, 2000);
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => notification.classList.add('show'), 10);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Form submission function (keep existing)
    async function submitForm() {
        const submitBtn = document.getElementById('submitBtn_intro');
        const modal = document.getElementById('studentDocumentsModal');
        
        if (!submitBtn) return;
        
        const form = document.getElementById('studentDocumentsForm');
        if (!form) return;
        
        const formData = new FormData(form);
        
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('/student/documents/upload', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('Documents submitted successfully!', 'success');
                
                setTimeout(() => {
                    if (modal) modal.classList.remove('active');
                    location.reload();
                }, 1500);
            } else {
                let errorMessage = result.message || 'Upload failed';
                if (result.errors) {
                    errorMessage += ': ' + Object.values(result.errors).flat().join(', ');
                }
                showNotification(errorMessage, 'error');
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            }
        } catch (error) {
            console.error('Upload error:', error);
            showNotification(error.message || 'An error occurred', 'error');
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
        }
    }
}


// 
// Add this function to your JavaScript file
function initializeDocumentsUpload2() {
    // Handle click on the upload button in the modal
    document.getElementById('uploadDocumentBtn').addEventListener('click', function() {
        const form = document.getElementById('uploadDocumentForm');
        const formData = new FormData(form);
        
        // Show loading state
        const uploadBtn = this;
        uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...';
        uploadBtn.disabled = true;
        
        // Send AJAX request
        fetch('/student/upload-required-document', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                alert('Document uploaded successfully.');
                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('uploadDocumentModal'));
                modal.hide();
                // Reset the form
                form.reset();
                // Reload the required documents section
                loadRequiredDocuments();
            } else {
                alert('Upload failed: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during upload.');
        })
        .finally(() => {
            // Reset the button
            uploadBtn.innerHTML = 'Upload';
            uploadBtn.disabled = false;
        });
    });
}


// Initialize Grade Input System
function initializeGradeInput() {
    // Load initial student data first
    loadInitialStudentData();
    
    // Then load subjects for main view
    loadGradeSubjects();

    // Setup form submit
    $('#grade-filter-form').on('submit', function(e) {
        e.preventDefault();
        loadGradeSubjects();
    });

    // Setup reset filters
    $('#reset-filters').on('click', function() {
        $('#grade-filter-form')[0].reset();
        loadGradeSubjects();
    });

    // Setup grade form submission
    $('#grade-form').on('submit', function(e) {
        e.preventDefault();
        saveGrade();
    });

    // Setup modal close
    $('#close-grade-modal, #cancel-grade').on('click', function() {
        closeGradeModal();
    });

    // Setup subject click in modal list
    $(document).on('click', '.subject-list-item', function() {
        const subjectId = $(this).data('id');
        loadSubjectDetails(subjectId);
    });
    
    // Setup click to open grade input from main view
    $(document).on('click', '.open-grade-modal', function() {
        const subjectId = $(this).data('id');
        openGradeModal();
        loadSubjectDetails(subjectId);
    });
}

// Load initial student data
function loadInitialStudentData() {
    $.ajax({
        url: '/student/get-initial-grade-data',
        type: 'GET',
        success: function(response) {
            // Store student data globally or in data attributes
            window.studentData = response;
            // You can use this data to pre-fill the modal or other areas
        },
        error: function(xhr) {
            console.error('Error loading student data:', xhr);
        }
    });
}


// Load subjects for grade input
// function loadGradeSubjects() {
//     const yearLevel = $('#year_level').val();
//     const subjectSearch = $('#subject_search').val();
//     const gradeStatus = $('#grade_status').val();

//     // Show loading in main container
//     $('#grades-container').html(`
//         <div class="loading-state">
//             <i class="fas fa-spinner fa-spin"></i>
//             <p>Loading subjects...</p>
//         </div>
//     `);

//     $.ajax({
//         url: '/student/get-enrolled-subjects',
//         type: 'GET',
//         data: {
//             year_level: yearLevel,
//             subject_search: subjectSearch,
//             grade_status: gradeStatus
//         },
//         success: function(response) {
//             renderMainGradeSubjects(response);
//             // Also update modal list
//             renderModalSubjectsList(response);
//         },
//         error: function(xhr) {
//             console.error('Error loading subjects:', xhr);
//             $('#grades-container').html(`
//                 <div class="error-state">
//                     <i class="fas fa-exclamation-circle"></i>
//                     <p>Failed to load subjects</p>
//                 </div>
//             `);
//         }
//     });
// }

function loadGradeSubjects() {
    const yearLevel = $('#year_level').val();
    const subjectSearch = $('#subject_search').val();
    const gradeStatus = $('#grade_status').val();

    // Show loading in main container
    $('#grades-container').html(`
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading subjects...</p>
        </div>
    `);

    $.ajax({
        url: '/student/get-enrolled-subjects',
        type: 'GET',
        data: {
            year_level: yearLevel,
            subject_search: subjectSearch,
            grade_status: gradeStatus
        },
        success: function(response) {
            renderMainGradeSubjects(response);
            // Also update modal list
            renderModalSubjectsList(response);
        },
        error: function(xhr) {
            console.error('Error loading subjects:', xhr);
            $('#grades-container').html(`
                <div class="error-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Failed to load subjects</p>
                </div>
            `);
        }
    });
}

// Render subjects in the MAIN VIEW
// function renderMainGradeSubjects(subjects) {
//     const container = $('#grades-container');
//     container.empty();

//     if (subjects.length === 0) {
//         container.html(`
//             <div class="empty-state">
//                 <i class="fas fa-book-open"></i>
//                 <p>No subjects found</p>
//             </div>
//         `);
//         return;
//     }

//     // Group subjects by year level and semester
//     const groupedSubjects = {};
//     subjects.forEach(subject => {
//         const yearLevel = subject.year_level || '';
//         const semester = subject.semester || '';
        
//         // Create a special label for 3rd Year Summer
//         let groupLabel = `${yearLevel} ${semester}`;
//         if (yearLevel === '3rd Year' && semester === 'Summer') {
//             groupLabel = 'Summer or Third Term';
//         }
        
//         if (!groupedSubjects[groupLabel]) {
//             groupedSubjects[groupLabel] = {
//                 yearLevel: yearLevel,
//                 semester: semester,
//                 subjects: []
//             };
//         }
//         groupedSubjects[groupLabel].subjects.push(subject);
//     });

//     let allTablesHTML = '';

//     // Sort groups: 1st Year 1st Sem, 1st Year 2nd Sem, 2nd Year 1st Sem, etc.
//     const groupKeys = Object.keys(groupedSubjects).sort((a, b) => {
//         const yearOrder = { '1st Year': 1, '2nd Year': 2, '3rd Year': 3, '4th Year': 4, '5th Year': 5 };
//         const semOrder = { '1st Sem': 1, '2nd Sem': 2, 'Summer': 3 };
        
//         const getYear = (label) => {
//             if (label === 'Summer or Third Term') return '3rd Year';
//             return Object.keys(yearOrder).find(year => label.includes(year)) || '';
//         };
        
//         const getSemester = (label) => {
//             if (label === 'Summer or Third Term') return 'Summer';
//             return Object.keys(semOrder).find(sem => label.includes(sem)) || '';
//         };
        
//         const yearA = getYear(a);
//         const yearB = getYear(b);
//         const semA = getSemester(a);
//         const semB = getSemester(b);
        
//         if (yearOrder[yearA] !== yearOrder[yearB]) {
//             return yearOrder[yearA] - yearOrder[yearB];
//         }
//         return (semOrder[semA] || 0) - (semOrder[semB] || 0);
//     });

//     allTablesHTML += `
//             <div class="subject-group-header" style="text-align: center;"><br><br>
//                 <h3>Prospectus</h3>
//             </div>
//         `;

//     groupKeys.forEach(groupLabel => {
//         const group = groupedSubjects[groupLabel];
        
//         // Create header for this group
//         allTablesHTML += `
//             <br>
//             <div class="subject-group-header">
//                 <h3 style="margin-left: 10px;">${groupLabel}</h3>
//             </div>
//         `;

//         // Create table for this group
//         let tableHTML = `
//             <div class="table-responsive">
//                 <table class="table table-bordered table-hover">
//                     <thead class="thead-light">
//                         <tr>
//                             <th class="tbl_header" style="color:white;" scope="col">Subject Code</th>
//                             <th class="tbl_header" style="color:white;" scope="col">Subject Name</th>
//                             <th class="tbl_header" style="color:white;" scope="col">Units</th>
//                             <th class="tbl_header" style="color:white;" scope="col">Prerequisite</th>
//                             <th class="tbl_header" style="color:white;" scope="col">Grade</th>
//                             <th class="tbl_header" style="color:white;" scope="col">Action</th>
//                         </tr>
//                     </thead>
//                     <tbody>
//         `;

//         group.subjects.forEach(subject => {
//             const gradeText = subject.grade ? subject.grade : 'Not Graded';
//             const gradeClass = subject.grade ? 'grade-badge graded' : 'grade-badge ungraded';
            
//             // Get prerequisites for this subject (you'll need to fetch this from backend)
//             const prerequisites = subject.prerequisites || subject.prerequisite_codes || 'None';
            
//             tableHTML += `
//                 <tr>
//                     <td class="tbl_data">${subject.subject_code || ''}</td>
//                     <td class="tbl_data">${subject.subject_name || ''}</td>
//                     <td class="tbl_data">${subject.units || '0'}</td>
//                     <td class="tbl_data">${prerequisites}</td>
//                     <td class="tbl_data">
//                         <span class="${gradeClass}">${gradeText}</span>
//                     </td>
//                     <td class="tbl_data">
//                         <button class="btn btn-primary btn-sm open-grade-modal" data-id="${subject.id}">
//                             <i class="fas fa-pen"></i> Input Grade
//                         </button>
//                     </td>
//                 </tr>
//             `;
//         });

//         tableHTML += `
//                     </tbody>
//                 </table>
//             </div>
//             <div class="group-spacer"></div>
//         `;

//         allTablesHTML += tableHTML;
//     });

//     container.html(allTablesHTML);
// }

// Render subjects in the MAIN VIEW
function renderMainGradeSubjects(response) {
    const container = $('#grades-container');
    container.empty();

    const subjects = response.subjects;
    const isEnrollmentPeriodActive = response.is_enrollment_period_active;

    if (subjects.length === 0) {
        container.html(`
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <p>No subjects found</p>
            </div>
        `);
        return;
    }

    // Show enrollment period status
    // if (!isEnrollmentPeriodActive) {
    //     container.html(`
    //         <div class="alert alert-warning" role="alert">
    //             <i class="fas fa-exclamation-triangle"></i>
    //             <strong>Note:</strong> The grade input feature is currently disabled because there is no active enrollment period.
    //             Please contact the administrator if you need to input grades.
    //         </div>
    //     `);
    // }

    // Group subjects by year level and semester
    const groupedSubjects = {};
    subjects.forEach(subject => {
        const yearLevel = subject.year_level || '';
        const semester = subject.semester || '';
        
        // Create a special label for 3rd Year Summer
        let groupLabel = `${yearLevel} ${semester}`;
        if (yearLevel === '3rd Year' && semester === 'Summer') {
            groupLabel = 'Summer or Third Term';
        }
        
        if (!groupedSubjects[groupLabel]) {
            groupedSubjects[groupLabel] = {
                yearLevel: yearLevel,
                semester: semester,
                subjects: []
            };
        }
        groupedSubjects[groupLabel].subjects.push(subject);
    });

    let allTablesHTML = '';

    // Show enrollment period status message at the top
    // if (!isEnrollmentPeriodActive) {
    //     allTablesHTML += `
    //         <div class="alert alert-info" style="margin-bottom: 20px;">
    //             <i class="fas fa-info-circle"></i>
    //             <strong>Enrollment Period Status:</strong> Grade input is currently disabled. Buttons will be enabled when an enrollment period is active.
    //         </div>
    //     `;
    // }

    allTablesHTML += `
            <div class="subject-group-header" style="text-align: center;"><br><br>
                <h3>Prospectus</h3>
            </div>
        `;

    // Sort groups: 1st Year 1st Sem, 1st Year 2nd Sem, 2nd Year 1st Sem, etc.
    const groupKeys = Object.keys(groupedSubjects).sort((a, b) => {
        const yearOrder = { '1st Year': 1, '2nd Year': 2, '3rd Year': 3, '4th Year': 4, '5th Year': 5 };
        const semOrder = { '1st Sem': 1, '2nd Sem': 2, 'Summer': 3 };
        
        const getYear = (label) => {
            if (label === 'Summer or Third Term') return '3rd Year';
            return Object.keys(yearOrder).find(year => label.includes(year)) || '';
        };
        
        const getSemester = (label) => {
            if (label === 'Summer or Third Term') return 'Summer';
            return Object.keys(semOrder).find(sem => label.includes(sem)) || '';
        };
        
        const yearA = getYear(a);
        const yearB = getYear(b);
        const semA = getSemester(a);
        const semB = getSemester(b);
        
        if (yearOrder[yearA] !== yearOrder[yearB]) {
            return yearOrder[yearA] - yearOrder[yearB];
        }
        return (semOrder[semA] || 0) - (semOrder[semB] || 0);
    });

    groupKeys.forEach(groupLabel => {
        const group = groupedSubjects[groupLabel];
        
        // Create header for this group
        allTablesHTML += `
            <br>
            <div class="subject-group-header">
                <h3 style="margin-left: 10px;">${groupLabel}</h3>
            </div>
        `;

        // Create table for this group
        let tableHTML = `
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th class="tbl_header" style="color:white;" scope="col">Subject Code</th>
                            <th class="tbl_header" style="color:white;" scope="col">Subject Name</th>
                            <th class="tbl_header" style="color:white;" scope="col">Units</th>
                            <th class="tbl_header" style="color:white;" scope="col">Prerequisite</th>
                            <th class="tbl_header" style="color:white;" scope="col">Grade</th>
                            <th class="tbl_header" style="color:white;" scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        group.subjects.forEach(subject => {

            const failedGrades = ['4.0', '5.0', 'INC', 'DRP'];
            const gradeStr = subject.grade ? subject.grade.toString() : '';
            
            const gradeText = subject.grade ? subject.grade : 'Not Graded';
            
            // Use nested ternary for grade class
            const gradeClass = subject.grade 
                ? (failedGrades.includes(gradeStr) ? 'grade-badge failed' : 'grade-badge graded')
                : 'grade-badge ungraded';
            
            const prerequisites = subject.prerequisites || subject.prerequisite_codes || 'None';
            
            // Determine button attributes based on enrollment period
            const buttonHTML = isEnrollmentPeriodActive 
                ? `<button class="btn btn-primary btn-sm open-grade-modal" data-id="${subject.id}">
                       <i class="fas fa-pen"></i> Input Grade
                   </button>`
                : `<button class="btn btn-secondary btn-sm" disabled title="Grade input is disabled. No active enrollment period.">
                       <i class="fas fa-ban"></i> Disabled
                   </button>`;
            
            tableHTML += `
                <tr>
                    <td class="tbl_data">${subject.subject_code || ''}</td>
                    <td class="tbl_data">${subject.subject_name || ''}</td>
                    <td class="tbl_data">${subject.units || '0'}</td>
                    <td class="tbl_data">${prerequisites}</td>
                    <td class="tbl_data">
                        <span class="${gradeClass}">${gradeText}</span>
                    </td>
                    <td class="tbl_data">
                        ${buttonHTML}
                    </td>
                </tr>
            `;
        });

        tableHTML += `
                    </tbody>
                </table>
            </div>
            <div class="group-spacer"></div>
        `;

        allTablesHTML += tableHTML;
    });

    container.append(allTablesHTML);
}

// Render subjects in the MODAL LIST (left panel)
function renderModalSubjectsList(subjects) {
    const container = $('#subjects_list');
    container.empty();

    if (subjects.length === 0) {
        container.html(`
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <p>No subjects found</p>
            </div>
        `);
        return;
    }

    // Group by year level and semester for modal
    const groupedSubjects = {};
    subjects.forEach(subject => {
        const key = `${subject.year_level} - ${subject.semester}`;
        if (!groupedSubjects[key]) {
            groupedSubjects[key] = [];
        }
        groupedSubjects[key].push(subject);
    });

    // Render grouped subjects in modal
    Object.keys(groupedSubjects).forEach(groupName => {
        const groupDiv = $(`
            <div class="subject-group">
                <div class="group-header">${groupName}</div>
                <div class="group-subjects"></div>
            </div>
        `);

        const groupSubjectsDiv = groupDiv.find('.group-subjects');
        
        groupedSubjects[groupName].forEach(subject => {
            const gradeText = subject.grade ? `Grade: ${subject.grade}` : 'No grade yet';
            const gradeClass = subject.grade ? 'graded' : 'ungraded';
            
            const subjectItem = $(`
                <div class="subject-list-item ${gradeClass}" data-id="${subject.id}">
                    <div class="subject-list-code">${subject.subject_code}</div>
                    <div class="subject-list-name">${subject.subject_name}</div>
                    <div class="subject-list-grade">${gradeText}</div>
                </div>
            `);
            
            groupSubjectsDiv.append(subjectItem);
        });

        container.append(groupDiv);
    });
}

// Render subjects in the modal list
function renderGradeSubjects(subjects) {
    const container = $('#subjects_list');
    container.empty();

    if (subjects.length === 0) {
        container.html(`
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <p>No subjects found</p>
            </div>
        `);
        return;
    }

    // Group by year level and semester
    const groupedSubjects = {};
    subjects.forEach(subject => {
        const key = `${subject.year_level} - ${subject.semester}`;
        if (!groupedSubjects[key]) {
            groupedSubjects[key] = [];
        }
        groupedSubjects[key].push(subject);
    });

    // Render grouped subjects
    Object.keys(groupedSubjects).forEach(groupName => {
        const groupDiv = $(`
            <div class="subject-group">
                <div class="group-header">${groupName}</div>
                <div class="group-subjects"></div>
            </div>
        `);

        const groupSubjectsDiv = groupDiv.find('.group-subjects');
        
        groupedSubjects[groupName].forEach(subject => {
            const gradeText = subject.grade ? `Grade: ${subject.grade}` : 'No grade yet';
            const gradeClass = subject.grade ? 'graded' : 'ungraded';
            
            const subjectItem = $(`
                <div class="subject-list-item ${gradeClass}" data-id="${subject.id}">
                    <div class="subject-list-code">${subject.subject_code}</div>
                    <div class="subject-list-name">${subject.subject_name}</div>
                    <div class="subject-list-grade">${gradeText}</div>
                </div>
            `);
            
            groupSubjectsDiv.append(subjectItem);
        });

        container.append(groupDiv);
    });
}

// Load subject details for grade input
function loadSubjectDetails(subjectId) {
    // Show loading in modal
    $('.modal-body').append(`
        <div class="loading-overlay">
            <div class="loading-spinner"></div>
            <p>Loading subject details...</p>
        </div>
    `);

    $.ajax({
        url: `/student/get-subject-details/${subjectId}`,
        type: 'GET',
        success: function(response) {
            $('.modal-body .loading-overlay').remove();
            populateGradeModal(response);
        },
        error: function(xhr) {
            console.error('Error loading subject details:', xhr);
            $('.modal-body .loading-overlay').remove();
            showNotification('Failed to load subject details', 'error');
        }
    });
}

// Populate grade modal with data
// Update your populateGradeModal function to use window.studentData
function populateGradeModal(data) {
    const { subject } = data;
    const student = window.studentData?.student || {};
    const userInfo = window.studentData?.userInfo || {};
    
    // Set student info
    $('#student_full_name').text(userInfo.full_name || 'Student Name');
    $('#student_id_display').text(student.id_no || 'N/A');
    $('#student_course').text(`Year: ${student.year_level || 'N/A'}`);
    
    // Set subject info
    $('#subject_code').text(subject.subject_code || '-');
    $('#subject_name').text(subject.subject_name || '-');
    $('#subject_units').text(subject.units || '0');
    $('#subject_year').text(subject.year_level || '-');
    $('#subject_semester').text(subject.semester || '-');
    
    // Set hidden inputs
    $('#grade_subject_id').val(subject.id);
    $('#grade_student_id').val(student.id || '');
    
    // Set current grade
    $('#grade').val(subject.grade || '');
    
    // Highlight selected subject in modal list
    $('.subject-list-item').removeClass('selected');
    $(`.subject-list-item[data-id="${subject.id}"]`).addClass('selected');
}

// Save grade
function saveGrade() {
    const subjectId = $('#grade_subject_id').val();
    const grade = $('#grade').val();

    // if (!grade) {
    //     showNotification('Please select a grade', 'error');
    //     return;
    // }

    // Show loading in form
    $('#grade-form').append(`
        <div class="loading-overlay">
            <div class="loading-spinner"></div>
            <p>Saving grade...</p>
        </div>
    `);

    $.ajax({
        url: '/student/update-grade',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            subject_id: subjectId,
            grade: grade
        },
        success: function(response) {
            showNotification('Grade saved successfully!', 'success');
            closeGradeModal();
            loadGradeSubjects(); // Refresh the main list
        },
        error: function(xhr) {
            console.error('Error saving grade:', xhr);
            const error = xhr.responseJSON?.error || 'Failed to save grade';
            showNotification(error, 'error');
            $('#grade-form .loading-overlay').remove();
        }
    });
}

// Open grade modal
function openGradeModal() {
    $('#grade-modal').css('display', 'flex');
    setTimeout(() => {
        $('#grade-modal').addClass('active');
    }, 10);
}

// Close grade modal
function closeGradeModal() {
    $('#grade-modal').removeClass('active');
    setTimeout(() => {
        $('#grade-modal').css('display', 'none');
        $('#grade-form')[0].reset();
        $('#subjects_list').empty(); // Clear modal list
    }, 300);
}

// Open grade input for a specific subject
function openGradeInputForSubject(subjectId) {
    openGradeModal();
    loadSubjectDetails(subjectId);
}

// Helper functions for loading states
function showLoading(element, message = 'Loading...') {
    const $element = $(element);
    $element.append(`
        <div class="loading-overlay">
            <div class="loading-spinner"></div>
            <p>${message}</p>
        </div>
    `);
}

function hideLoading(element) {
    $(element).find('.loading-overlay').remove();
}

function showError(element, message) {
    const $element = $(element);
    $element.append(`
        <div class="error-state">
            <i class="fas fa-exclamation-circle"></i>
            <p>${message}</p>
        </div>
    `);
}

// function showNotification(message, type = 'info') {
//     // Create notification element
//     const notification = $(`
//         <div class="notification notification-${type}">
//             <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
//             <span>${message}</span>
//         </div>
//     `);
    
//     $('body').append(notification);
    
//     // Show with animation
//     setTimeout(() => notification.addClass('show'), 10);
    
//     // Remove after 3 seconds
//     setTimeout(() => {
//         notification.removeClass('show');
//         setTimeout(() => notification.remove(), 300);
//     }, 3000);
// }

function refresh() {
    window.location.reload();
}

document.addEventListener('DOMContentLoaded', function() {

    initializeThemeColorPicker();
    initializeSubjectFilters();
    initializeSubjectFilters2();
    initializeSubjectView();
    setupRealtimeSubscription();
    status_update();
    loadSimpleAverageGrade();
    count_enrolled_subjects();
    count_documents();
    initializeNotifications();
    loadAllFiles();
    initializeFilesSystem();
    updateFileStatistics();
    // loadAverageGradeChart();
    initializeDocumentsUpload2();

    initializeDocumentsUpload();

    // Add grade input initialization if we're on the grades page
    if (document.getElementById('input-grades-section')) {
        initializeGradeInput();
    }

    initializeLogout();
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

    // Add search button functionality
    const searchBtn5 = document.querySelector('#search-btn');
    if (searchBtn5) {
        searchBtn5.addEventListener('click', function() {
            document.querySelector('[data-section="courses"]').click();
        });
    }

    // Allow pressing Enter in search inputs
    function handleEnterKey(event) {
        if (event.key === 'Enter') {
            document.querySelector('[data-section="courses"]').click();
        }
    }

    const notificationbtn = document.querySelector('#notification-btn');
    if (notificationbtn) {
        notificationbtn.addEventListener('click', function() {
            document.querySelector('[data-section="schedule"]').click();
        });
    }

    const inputyourgrades = document.querySelector('#inputyourgrades');
    if (inputyourgrades) {
        inputyourgrades.addEventListener('click', function() {
            document.querySelector('[data-section="input-grades"]').click();
        });
    }

    // Check irregular student status on page load
    checkIrregularStudent(irregularModal);

    // Initialize other components
    initializeProfilePictureUpload();
    initializeTooltips();
    initializeStudentInfoModal();
    initializeStudentInfoModal2();
    initializeStudentInfoModal3();

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
                // alert(`Searching for: ${searchTerm}`);
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
            // if (this.id === 'logout-btn') {
            //     // Logout functionality
            //     if (confirm('Are you sure you want to logout?')) {
            //         alert('Logging out...');
            //         // In a real app, this would redirect to logout URL
            //     }
            //     return;
            // }
            
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


            if(sectionName === 'Notifications' || sectionName === 'Files' || sectionName === 'Input Grades' || sectionName === 'Profile') {
                $(".header2").css("display", "none");
            } else {
                $(".header2").css("display", "block");
            }
            
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
    const editableFields = ['firstName', 'lastName', 'middlename', 'phone', 'address'];
    
    let originalValues = {};

    loadProfileData();
    initializeProfileEdit();
    
    // Store original values
    // editableFields.forEach(field => {
    //     originalValues[field] = document.getElementById(field).value;
    // });

    // Load profile data
    function loadProfileData() {
        fetch('/student/profile/data')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.profile) {
                    const profile = data.profile;
                    
                    // Update form fields
                    document.getElementById('firstName').value = profile.firstname;
                    document.getElementById('lastName').value = profile.lastname;
                    document.getElementById('middlename').value = profile.middlename;
                    document.getElementById('email').value = profile.email;
                    document.getElementById('phone').value = profile.phone;
                    document.getElementById('address').value = profile.address;
                    document.getElementById('program').value = profile.program;
                    document.getElementById('yearLevel').value = profile.year_level;
                    document.getElementById('curriculum').value = profile.curriculum;
                    
                    // Store original values
                    editableFields.forEach(field => {
                        originalValues[field] = document.getElementById(field).value;
                    });
                }
            })
            .catch(error => console.error('Error loading profile data:', error));
    }


    function initializeProfileEdit() {
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
            
            // Collect form data
            const formData = {
                firstname: document.getElementById('firstName').value,
                lastname: document.getElementById('lastName').value,
                middlename: document.getElementById('middlename').value,
                phone: document.getElementById('phone').value,
                address: document.getElementById('address').value,
            };
            
            // Send update request
            fetch('/student/profile/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
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
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message || 'Failed to update profile', 'error');
                }
            })
            .catch(error => {
                console.error('Error updating profile:', error);
                showNotification('An error occurred while updating profile', 'error');
            });
        });
    }

    
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
        setTimeout(() => {
                    window.location.reload();
                }, 2000);
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
                        
                        // FIRST CHECK FOR EXISTING ENROLLMENT REQUEST
                        checkExistingEnrollmentRequest().then(hasExistingRequest => {
                            if (hasExistingRequest) {
                                // Don't proceed if there's an existing request
                                return;
                            }
                            
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
                    });
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

    // Add this helper function to check for existing enrollment requests
    function checkExistingEnrollmentRequest() {
        return fetch('/student/enrollment/check-existing-request', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.has_existing_request) {
                showNotification(`You already have an existing enrollment request with status: ${data.existing_status}. Please wait for it to be processed.`, 'error');
                return true;
            }
            return false;
        })
        .catch(error => {
            console.error('Error checking existing enrollment request:', error);
            return false;
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