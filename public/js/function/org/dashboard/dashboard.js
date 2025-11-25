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

// Organization Dashboard JavaScript
        document.addEventListener('DOMContentLoaded', function() {

            initializeThemeColorPicker();
            // Navigation functionality
            const menuItems = document.querySelectorAll('.menu-item');
            const contentSections = document.querySelectorAll('.content-section');
            const pageTitle = document.querySelector('.page-title');
            
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (this.id === 'logout-btn') {
                        if (confirm('Are you sure you want to logout?')) {
                            alert('Logging out...');
                            // Redirect to logout URL
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
                    document.getElementById(sectionId).classList.add('active');
                    
                    // Update page title
                    const sectionName = this.querySelector('span').textContent;
                    pageTitle.textContent = 'Organization';
                });
            });

            // Toggle sidebar on mobile
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            const sidebar = document.querySelector('.sidebar');
            
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });

            // Dark Mode Toggle
            const darkModeToggle = document.getElementById('dark-mode-toggle');

            if (darkModeToggle) {
                darkModeToggle.addEventListener('change', function() {
                    // Let the existing dark mode logic run first
                    setTimeout(() => {
                        updateSidebarForCurrentMode();
                    }, 100);
                });
            }
            
            // Check for saved dark mode preference
            const isDarkMode = localStorage.getItem('darkMode') === 'true';
            
            // Set initial state
            if (isDarkMode) {
                document.body.classList.add('dark-mode');
                darkModeToggle.checked = true;
                updateSidebarForCurrentMode(); // Initialize sidebar for dark mode
            }
            
            // Toggle dark mode
            // Toggle dark mode
            darkModeToggle.addEventListener('change', function() {
                if (this.checked) {
                    document.body.classList.add('dark-mode');
                    localStorage.setItem('darkMode', 'true');
                } else {
                    document.body.classList.remove('dark-mode');
                    localStorage.setItem('darkMode', 'false');
                }
                // Update sidebar colors when mode changes
                updateSidebarForCurrentMode();
            });

            // Theme Color Picker
            const colorOptions = document.querySelectorAll('.color-option');
            const currentThemeName = document.getElementById('current-theme-name');
            
            // Check for saved theme preference
            const savedTheme = localStorage.getItem('themeColor') || '#4361ee';
            const savedThemeName = localStorage.getItem('themeName') || 'Blue';
            
            // Set initial theme
            setTheme(savedTheme, savedThemeName);
            
            // Update active color option
            colorOptions.forEach(option => {
                if (option.getAttribute('data-color') === savedTheme) {
                    option.classList.add('active');
                } else {
                    option.classList.remove('active');
                }
            });
            
            // Handle color selection
            colorOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const color = this.getAttribute('data-color');
                    const name = this.getAttribute('data-name');
                    
                    // Update active state
                    colorOptions.forEach(opt => opt.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Set the theme
                    setTheme(color, name);
                    
                    // Save preference
                    localStorage.setItem('themeColor', color);
                    localStorage.setItem('themeName', name);
                });
            });
            
            // Function to set theme
            function setTheme(color, name) {
                document.documentElement.style.setProperty('--primary-color', color);
                
                // Calculate hover color (10% darker)
                const hoverColor = shadeColor(color, -10);
                document.documentElement.style.setProperty('--primary-hover', hoverColor);
                
                // Update current theme name
                currentThemeName.textContent = name;
            }
            
            // Helper function to shade colors
            function shadeColor(color, percent) {
                let R = parseInt(color.substring(1, 3), 16);
                let G = parseInt(color.substring(3, 5), 16);
                let B = parseInt(color.substring(5, 7), 16);

                R = parseInt(R * (100 + percent) / 100);
                G = parseInt(G * (100 + percent) / 100);
                B = parseInt(B * (100 + percent) / 100);

                R = (R < 255) ? R : 255;
                G = (G < 255) ? G : 255;
                B = (B < 255) ? B : 255;

                const RR = ((R.toString(16).length === 1) ? "0" + R.toString(16) : R.toString(16));
                const GG = ((G.toString(16).length === 1) ? "0" + G.toString(16) : G.toString(16));
                const BB = ((B.toString(16).length === 1) ? "0" + B.toString(16) : B.toString(16));

                return "#" + RR + GG + BB;
            }

            // Receipt Modal functionality
            const receiptModal = document.getElementById('receiptModal');
            const closeReceiptModal = document.getElementById('closeReceiptModal');
            const viewReceiptButtons = document.querySelectorAll('.view-receipt');
            const verifyReceiptBtn = document.getElementById('verifyReceipt');
            const rejectReceiptBtn = document.getElementById('rejectReceipt');

            viewReceiptButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const receipt = this.getAttribute('data-receipt');
                    // In a real application, you would fetch the actual receipt image
                    document.getElementById('receiptImage').src = '/receipts/' + receipt;
                    receiptModal.classList.add('active');
                });
            });

            closeReceiptModal.addEventListener('click', function() {
                receiptModal.classList.remove('active');
            });

            verifyReceiptBtn.addEventListener('click', function() {
                // Verify payment logic
                Swal.fire({
                    title: 'Verify Payment?',
                    text: 'Are you sure you want to verify this payment?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Verify',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // API call to verify payment
                        Swal.fire('Verified!', 'Payment has been verified successfully.', 'success');
                        receiptModal.classList.remove('active');
                        // Refresh the payment table or update UI
                    }
                });
            });

            rejectReceiptBtn.addEventListener('click', function() {
                // Reject payment logic
                Swal.fire({
                    title: 'Reject Payment?',
                    text: 'Are you sure you want to reject this payment?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Reject',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#ef4444'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // API call to reject payment
                        Swal.fire('Rejected!', 'Payment has been rejected.', 'success');
                        receiptModal.classList.remove('active');
                        // Refresh the payment table or update UI
                    }
                });
            });

            // Enrollment Details Modal functionality
            const enrollmentDetailsModal = document.getElementById('enrollmentDetailsModal');
            const closeEnrollmentDetails = document.getElementById('closeEnrollmentDetails');
            const viewRequestButtons = document.querySelectorAll('.view-request');
            const approveEnrollmentBtn = document.getElementById('approveEnrollment');
            const rejectEnrollmentBtn = document.getElementById('rejectEnrollment');

            viewRequestButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const requestId = this.getAttribute('data-id');
                    // Fetch enrollment details from API
                    fetchEnrollmentDetails(requestId);
                    enrollmentDetailsModal.classList.add('active');
                });
            });

            closeEnrollmentDetails.addEventListener('click', function() {
                enrollmentDetailsModal.classList.remove('active');
            });

            function fetchEnrollmentDetails(requestId) {
                // Mock data - in real application, fetch from API
                const mockData = {
                    studentName: 'John Michael Smith',
                    studentId: '2020-30617',
                    program: 'BS Information Technology',
                    yearLevel: '3rd Year',
                    subjects: [
                        { code: 'IT 373', name: 'Software Engineering', units: 3, schedule: 'Mon/Wed 10:00 AM', instructor: 'Dr. Smith' },
                        { code: 'CS 301', name: 'Data Structures', units: 4, schedule: 'Tue/Thu 2:00 PM', instructor: 'Prof. Johnson' },
                        { code: 'MATH 202', name: 'Calculus II', units: 3, schedule: 'Mon/Wed/Fri 1:00 PM', instructor: 'Dr. Lee' }
                    ]
                };

                // Populate student info
                document.getElementById('detailStudentName').textContent = mockData.studentName;
                document.getElementById('detailStudentId').textContent = mockData.studentId;
                document.getElementById('detailProgram').textContent = mockData.program;
                document.getElementById('detailYearLevel').textContent = mockData.yearLevel;

                // Populate subjects table
                const subjectsTableBody = document.getElementById('subjectsTableBody');
                subjectsTableBody.innerHTML = mockData.subjects.map(subject => `
                    <tr>
                        <td>${subject.code}</td>
                        <td>${subject.name}</td>
                        <td>${subject.units}</td>
                        <td>${subject.schedule}</td>
                        <td>${subject.instructor}</td>
                    </tr>
                `).join('');
            }

            approveEnrollmentBtn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Approve Enrollment?',
                    text: 'Are you sure you want to approve this enrollment request?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Approve',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // API call to approve enrollment
                        Swal.fire('Approved!', 'Enrollment request has been approved.', 'success');
                        enrollmentDetailsModal.classList.remove('active');
                        // Refresh the enrollment requests table
                    }
                });
            });

            rejectEnrollmentBtn.addEventListener('click', function() {
                Swal.fire({
                    title: 'Reject Enrollment?',
                    text: 'Are you sure you want to reject this enrollment request?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Reject',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#ef4444'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // API call to reject enrollment
                        Swal.fire('Rejected!', 'Enrollment request has been rejected.', 'success');
                        enrollmentDetailsModal.classList.remove('active');
                        // Refresh the enrollment requests table
                    }
                });
            });

            // Quick action buttons in dashboard
            const quickActionButtons = document.querySelectorAll('.course-card[style*="cursor: pointer"]');
            quickActionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetSection = this.getAttribute('onclick').match(/'([^']+)'/)[1];
                    showSection(targetSection);
                });
            });

            // Global function to show sections
            window.showSection = function(sectionName) {
                // Hide all content sections
                contentSections.forEach(section => section.classList.remove('active'));
                
                // Show the target section
                document.getElementById(sectionName + '-section').classList.add('active');
                
                // Update active menu item
                menuItems.forEach(item => {
                    item.classList.remove('active');
                    if (item.getAttribute('data-section') === sectionName) {
                        item.classList.add('active');
                    }
                });
                
                // Update page title
                pageTitle.textContent = document.querySelector(`[data-section="${sectionName}"] span`).textContent + 'Organization';
            };

            // Filter functionality for enrollment requests
            const statusFilter = document.getElementById('status-filter');
            const programFilter = document.getElementById('program-filter');
            
            [statusFilter, programFilter].forEach(filter => {
                filter.addEventListener('change', function() {
                    // In a real application, this would filter the table data
                    console.log('Filtering requests...');
                });
            });

            // Search functionality for students
            const studentSearch = document.getElementById('student-search');
            studentSearch.addEventListener('input', function() {
                // In a real application, this would filter the student list
                console.log('Searching students...');
            });

            // Profile edit functionality
            const editOrgProfileBtn = document.getElementById('edit-org-profile-btn');
            const cancelOrgEditBtn = document.getElementById('cancel-org-edit');
            const orgFormActions = document.getElementById('org-form-actions');
            const orgProfileForm = document.getElementById('org-profile-form');
            
            if (editOrgProfileBtn) {
                editOrgProfileBtn.addEventListener('click', function() {
                    const editableFields = ['orgName', 'orgEmail', 'orgPhone', 'orgAddress', 'orgHead', 'orgType'];
                    
                    // Enable editing for all fields
                    editableFields.forEach(field => {
                        const input = document.getElementById(field);
                        input.readOnly = false;
                        input.style.background = 'white';
                        input.style.color = '#374151';
                    });
                    
                    // Show form actions
                    orgFormActions.style.display = 'flex';
                    
                    // Change edit button to editing state
                    editOrgProfileBtn.innerHTML = '<i class="fas fa-pencil-alt"></i> Editing...';
                    editOrgProfileBtn.style.background = '#fbbf24';
                    editOrgProfileBtn.style.borderColor = '#fbbf24';
                    editOrgProfileBtn.style.color = '#78350f';
                });

                cancelOrgEditBtn.addEventListener('click', function() {
                    // Reload the page to reset changes
                    location.reload();
                });

                orgProfileForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Submit form data to server
                    Swal.fire({
                        title: 'Update Profile?',
                        text: 'Are you sure you want to update the organization profile?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Update',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // API call to update profile
                            Swal.fire('Updated!', 'Organization profile has been updated.', 'success');
                            // Reload the page or update UI
                            location.reload();
                        }
                    });
                });
            }
        });