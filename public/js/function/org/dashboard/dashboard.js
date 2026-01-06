
        
$(document).ready(function() {
    // Load organization fee data
    function loadFeeData() {
        $.ajax({
            url: '{{ route("org.fees.data") }}',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    displayFeeData(response.data);
                } else {
                    console.error('Error loading fee data:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    }

    function displayFeeData(data) {
        // Map display names to database year levels
        const yearMapping = {
            'First Year': '1st Year',
            'Second Year': '2nd Year', 
            'Third Year': '3rd Year',
            'Fourth Year': '4th Year'
        };

        Object.keys(yearMapping).forEach(displayYear => {
            const dbYear = yearMapping[displayYear];
            const yearData = data[dbYear] || { students: [], total_count: 0, total_amount: 0 };
            
            // Update pending count
            $(`#${displayYear.toLowerCase().replace(' ', '-')}-pending`).text(`${yearData.total_count} pending`);
            
            // Update total amount
            $(`#${displayYear.toLowerCase().replace(' ', '-')}-total`).text(`₱${yearData.total_amount.toFixed(2)}`);
            
            // Update table rows
            const tbodyId = `${displayYear.toLowerCase().replace(' ', '-')}-students`;
            const $tbody = $(`#${tbodyId}`);
            $tbody.empty();
            
            if (yearData.students.length > 0) {
                yearData.students.forEach(student => {
                    const fullName = `${student.firstname} ${student.lastname}`;
                    const contact = student.phone_number || 'N/A';
                    const amount = student.amount ? `₱${parseFloat(student.amount).toFixed(2)}` : '₱0.00';
                    const statusBadge = student.status === 'Approved' 
                        ? '<span class="badge bg-success">Approved</span>'
                        : '<span class="badge bg-warning">Pending</span>';
                    
                    const actionBtn = student.status === 'Pending'
                        ? `<button class="btn btn-sm btn-success accept-fee" 
                                  data-id="${student.fee_id}" 
                                  data-table="${student.source_table}">
                              <i class="fas fa-check"></i> Accept
                           </button>`
                        : '<span class="text-muted">Processed</span>';
                    
                    $tbody.append(`
                        <tr>
                            <td>${fullName}</td>
                            <td>${student.student_id}</td>
                            <td>${contact}</td>
                            <td>${amount}</td>
                            <td>${statusBadge}</td>
                            <td>${actionBtn}</td>
                        </tr>
                    `);
                });
            } else {
                $tbody.append(`
                    <tr>
                        <td colspan="6" class="text-center">No pending fees for ${displayYear}</td>
                    </tr>
                `);
            }
        });
    }

    // Handle accept fee button click
    $(document).on('click', '.accept-fee', function() {
        const feeId = $(this).data('id');
        const table = $(this).data('table');
        const button = $(this);
        
        if (confirm('Are you sure you want to approve this fee?')) {
            $.ajax({
                url: '{{ route("org.fees.accept") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    fee_id: feeId,
                    table: table
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        button.closest('tr').find('td:nth-child(5)').html('<span class="badge bg-success">Approved</span>');
                        button.replaceWith('<span class="text-muted">Processed</span>');
                        
                        // Reload the data to update counts
                        setTimeout(loadFeeData, 500);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error approving fee. Please try again.');
                }
            });
        }
    });

    // Handle export button click
    $(document).on('click', '.export-btn', function() {
        const year = $(this).data('year');
        alert(`Export feature for ${year} would be implemented here.`);
        // You can implement CSV/Excel export functionality
    });

    // Load data on page load
    loadFeeData();
    
    // Optional: Refresh data every 30 seconds
    setInterval(loadFeeData, 30000);
});


        // Sample data for payments
        const paymentsData = [
            { id: 1, date: "2023-10-15", student: "Maria Santos", paymentId: "PAY-001", amount: 500, method: "Cash", status: "Paid" },
            { id: 2, date: "2023-10-14", student: "Juan Dela Cruz", paymentId: "PAY-002", amount: 500, method: "GCash", status: "Paid" },
            { id: 3, date: "2023-10-13", student: "Ana Reyes", paymentId: "PAY-003", amount: 500, method: "Bank Transfer", status: "Paid" },
            { id: 4, date: "2023-10-12", student: "Carlos Lopez", paymentId: "PAY-004", amount: 500, method: "Cash", status: "Pending" },
            { id: 5, date: "2023-10-11", student: "Sofia Garcia", paymentId: "PAY-005", amount: 500, method: "GCash", status: "Paid" },
            { id: 6, date: "2023-10-10", student: "Miguel Torres", paymentId: "PAY-006", amount: 700, method: "Bank Transfer", status: "Overdue" }
        ];
        
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            renderDashboard();
            setupEventListeners();
            setupNavigation();
            setupMobileSidebar();
            
            // Load initial data for other sections
            renderAllStudents();
            renderPayments();
            
            // Setup touch interactions
            setupTouchInteractions();
            
            // Setup settings tabs
            setupSettingsTabs();
        });
        
        function setupMobileSidebar() {
            const menuToggleButtons = document.querySelectorAll('.menu-toggle:not(.sidebar-close)');
            const sidebar = document.querySelector('.sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const sidebarCloseBtn = document.querySelector('.sidebar-close');
            
            // Function to open sidebar
            function openSidebar() {
                sidebar.classList.add('active');
                sidebarOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
            
            // Function to close sidebar
            function closeSidebar() {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
            
            // Add event listeners to all menu toggle buttons
            menuToggleButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    openSidebar();
                });
            });
            
            // Close sidebar when clicking the close button inside sidebar
            if (sidebarCloseBtn) {
                sidebarCloseBtn.addEventListener('click', closeSidebar);
            }
            
            // Close sidebar when clicking on overlay
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', closeSidebar);
            }
            
            // Close sidebar when clicking on a nav link (on mobile)
            const navLinks = document.querySelectorAll('.nav-links a');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 1024) {
                        closeSidebar();
                    }
                });
            });
            
            // Close sidebar with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                    closeSidebar();
                }
            });
            
            // Show/hide sidebar close button based on screen size
            function updateSidebarCloseButton() {
                if (window.innerWidth <= 1024) {
                    sidebarCloseBtn.style.display = 'flex';
                } else {
                    sidebarCloseBtn.style.display = 'none';
                }
            }
            
            // Initial check
            updateSidebarCloseButton();
            
            // Update on resize
            window.addEventListener('resize', updateSidebarCloseButton);
            
            // Handle swipe to close sidebar on mobile
            let touchStartX = 0;
            let touchEndX = 0;
            
            sidebar.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });
            
            sidebar.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            }, { passive: true });
            
            function handleSwipe() {
                const swipeThreshold = 50;
                const swipeDistance = touchEndX - touchStartX;
                
                // If swiping left (closing sidebar)
                if (swipeDistance < -swipeThreshold && sidebar.classList.contains('active')) {
                    closeSidebar();
                }
            }
        }
        
        function setupSettingsTabs() {
            const settingsTabs = document.querySelectorAll('[data-settings-tab]');
            
            settingsTabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all tabs
                    settingsTabs.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Hide all settings content
                    const settingsContent = document.querySelectorAll('.settings-tab');
                    settingsContent.forEach(content => {
                        content.classList.remove('active');
                    });
                    
                    // Show selected tab content
                    const tabId = this.getAttribute('data-settings-tab');
                    const targetTab = document.getElementById(`${tabId}-tab`);
                    if (targetTab) {
                        targetTab.classList.add('active');
                    }
                });
            });
        }
        
        function setupTouchInteractions() {
            // Add touch feedback to interactive elements
            const interactiveElements = document.querySelectorAll('button, .btn, .nav-links a, .stat-card, .year-level-card');
            
            interactiveElements.forEach(element => {
                element.addEventListener('touchstart', function() {
                    this.classList.add('active');
                }, { passive: true });
                
                element.addEventListener('touchend', function() {
                    this.classList.remove('active');
                }, { passive: true });
                
                element.addEventListener('touchcancel', function() {
                    this.classList.remove('active');
                }, { passive: true });
            });
            
            // Prevent context menu on long press for buttons
            document.addEventListener('contextmenu', function(e) {
                if (e.target.closest('button') || e.target.closest('.btn')) {
                    e.preventDefault();
                }
            });
            
            // Setup swipe to refresh (simulated)
            let refreshStartY = 0;
            const refreshIndicator = document.getElementById('refreshIndicator');
            
            document.addEventListener('touchstart', function(e) {
                // Only trigger at the top of the page
                if (window.scrollY === 0) {
                    refreshStartY = e.touches[0].clientY;
                }
            }, { passive: true });
            
            document.addEventListener('touchmove', function(e) {
                if (window.scrollY === 0 && refreshStartY > 0) {
                    const touchY = e.touches[0].clientY;
                    const pullDistance = touchY - refreshStartY;
                    
                    if (pullDistance > 0) {
                        const progress = Math.min(pullDistance / 100, 1);
                        refreshIndicator.style.transform = `scaleX(${progress})`;
                        
                        if (pullDistance > 100) {
                            // Trigger refresh
                            refreshIndicator.style.transform = 'scaleX(1)';
                        }
                    }
                }
            }, { passive: true });
            
            document.addEventListener('touchend', function() {
                if (refreshIndicator.style.transform === 'scaleX(1)') {
                    // Simulate refresh
                    setTimeout(() => {
                        refreshIndicator.style.transform = 'scaleX(0)';
                        showToast('Data refreshed successfully');
                    }, 500);
                } else {
                    refreshIndicator.style.transform = 'scaleX(0)';
                }
                refreshStartY = 0;
            }, { passive: true });
        }
        
        function showToast(message) {
            // Create toast element
            const toast = document.createElement('div');
            toast.className = 'position-fixed bottom-0 start-50 translate-middle-x mb-3 p-3 bg-success text-white rounded-pill shadow-lg';
            toast.style.zIndex = '1100';
            toast.style.minWidth = '200px';
            toast.style.textAlign = 'center';
            toast.style.fontSize = '0.875rem';
            toast.style.fontWeight = '500';
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            // Remove toast after 3 seconds
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s';
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 300);
            }, 3000);
        }
        
        function setupNavigation() {
            // Get all sidebar links
            const navLinks = document.querySelectorAll('.nav-links a');
            
            // Add click event to each link
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Get the section to show
                    const sectionId = this.getAttribute('data-section');
                    
                    // Remove active class from all links
                    navLinks.forEach(l => l.classList.remove('active'));
                    
                    // Add active class to clicked link
                    this.classList.add('active');
                    
                    // Hide all content sections
                    const contentSections = document.querySelectorAll('.content-section');
                    contentSections.forEach(section => {
                        section.classList.remove('active');
                    });
                    
                    // Show the selected section
                    const targetSection = document.getElementById(`${sectionId}-section`);
                    if (targetSection) {
                        targetSection.classList.add('active');
                        
                        // Scroll to top when switching sections
                        window.scrollTo(0, 0);
                        
                        // Update page title based on section
                        updatePageTitle(sectionId);
                    }
                });
            });
        }
        
        function updatePageTitle(section) {
            const titles = {
                'dashboard': { main: 'Fee Management', sub: 'Monitor and manage student fee payments' },
                'students': { main: 'Students', sub: 'Manage all students in the organization' },
                'payments': { main: 'Payments', sub: 'Track payment transactions' },
                'analytics': { main: 'Analytics', sub: 'Insights and trends in fee collection' },
                'settings': { main: 'Settings', sub: 'Configure organization and system preferences' },
                'help': { main: 'Help & Support', sub: 'Get assistance and learn how to use the system' }
            };
            
            const title = titles[section];
            if (title) {
                // Update the active section's title
                const activeSection = document.querySelector('.content-section.active');
                const titleElement = activeSection.querySelector('.page-title h1');
                const subtitleElement = activeSection.querySelector('.page-title p');
                
                if (titleElement) titleElement.textContent = title.main;
                if (subtitleElement) subtitleElement.textContent = title.sub;
            }
        }
        
        function renderDashboard() {
            // Clear existing student tables
            document.getElementById('first-year-students').innerHTML = '';
            document.getElementById('second-year-students').innerHTML = '';
            document.getElementById('third-year-students').innerHTML = '';
            document.getElementById('fourth-year-students').innerHTML = '';
            
            // Group students by year level
            const firstYearStudents = studentsData.filter(student => student.yearLevel === "First Year" && student.status === "pending");
            const secondYearStudents = studentsData.filter(student => student.yearLevel === "Second Year" && student.status === "pending");
            const thirdYearStudents = studentsData.filter(student => student.yearLevel === "Third Year" && student.status === "pending");
            const fourthYearStudents = studentsData.filter(student => student.yearLevel === "Fourth Year" && student.status === "pending");
            
            // Render each year level table
            renderStudentTable('first-year-students', firstYearStudents);
            renderStudentTable('second-year-students', secondYearStudents);
            renderStudentTable('third-year-students', thirdYearStudents);
            renderStudentTable('fourth-year-students', fourthYearStudents);
            
            // Update card badges
            updateCardBadges(firstYearStudents, secondYearStudents, thirdYearStudents, fourthYearStudents);
            
            // Calculate and update totals
            updateTotals(firstYearStudents, secondYearStudents, thirdYearStudents, fourthYearStudents);
        }
        
        function renderAllStudents() {
            const tableBody = document.getElementById('all-students-list');
            tableBody.innerHTML = '';
            
            studentsData.forEach(student => {
                const row = document.createElement('tr');
                let statusBadge = '';
                
                if (student.status === 'pending') {
                    statusBadge = '<span class="status-badge status-pending">Pending</span>';
                } else if (student.status === 'accepted') {
                    statusBadge = '<span class="status-badge status-paid">Paid</span>';
                } else {
                    statusBadge = '<span class="status-badge" style="background: #fee2e2; color: #991b1b;">Overdue</span>';
                }
                
                row.innerHTML = `
                    <td>
                        <div class="student-info">
                            <div class="student-avatar">${student.avatarInitials}</div>
                            <div class="student-details">
                                <h4>${student.name}</h4>
                                <p>${student.studentId}</p>
                            </div>
                        </div>
                    </td>
                    <td>${student.studentId}</td>
                    <td>${student.yearLevel}</td>
                    <td>${student.program}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1 mb-1">View</button>
                        <button class="btn btn-sm btn-outline-secondary mb-1">Edit</button>
                    </td>
                `;
                
                tableBody.appendChild(row);
            });
        }
        
        function renderPayments() {
            const tableBody = document.getElementById('payments-list');
            tableBody.innerHTML = '';
            
            paymentsData.forEach(payment => {
                const row = document.createElement('tr');
                let statusBadge = '';
                
                if (payment.status === 'Paid') {
                    statusBadge = '<span class="status-badge status-paid">Paid</span>';
                } else if (payment.status === 'Pending') {
                    statusBadge = '<span class="status-badge status-pending">Pending</span>';
                } else {
                    statusBadge = '<span class="status-badge status-overdue">Overdue</span>';
                }
                
                row.innerHTML = `
                    <td>${payment.date}</td>
                    <td>
                        <div class="student-info">
                            <div class="student-avatar">${payment.student.split(' ').map(n => n[0]).join('')}</div>
                            <div class="student-details">
                                <h4>${payment.student}</h4>
                            </div>
                        </div>
                    </td>
                    <td>${payment.paymentId}</td>
                    <td><strong>₱${payment.amount.toLocaleString()}</strong></td>
                    <td>${payment.method}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-receipt"></i>
                        </button>
                    </td>
                `;
                
                tableBody.appendChild(row);
            });
        }
        
        function renderStudentTable(tableId, students) {
            const tableBody = document.getElementById(tableId);
            
            if (students.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <p class="mt-3">No pending payments</p>
                        </td>
                    </tr>
                `;
                return;
            }
            
            students.forEach(student => {
                const row = document.createElement('tr');
                
                row.innerHTML = `
                    <td>
                        <div class="student-info">
                            <div class="student-avatar">${student.avatarInitials}</div>
                            <div class="student-details">
                                <h4>${student.name}</h4>
                                <p>${student.studentId}</p>
                            </div>
                        </div>
                    </td>
                    <td>${student.program}</td>
                    <td>${student.contact}</td>
                    <td><strong>₱${student.amount.toLocaleString()}</strong></td>
                    <td><span class="status-badge status-pending">Pending</span></td>
                    <td>
                        <button class="btn-accept" onclick="acceptPayment(${student.id})">
                            <i class="fas fa-check"></i> Accept
                        </button>
                    </td>
                `;
                
                tableBody.appendChild(row);
            });
        }
        
        function updateCardBadges(firstYear, secondYear, thirdYear, fourthYear) {
            const badges = document.querySelectorAll('.card-badge');
            if (badges[0]) badges[0].textContent = `${firstYear.length} pending`;
            if (badges[1]) badges[1].textContent = `${secondYear.length} pending`;
            if (badges[2]) badges[2].textContent = `${thirdYear.length} pending`;
            if (badges[3]) badges[3].textContent = `${fourthYear.length} pending`;
        }
        
        function updateTotals(firstYear, secondYear, thirdYear, fourthYear) {
            // Calculate totals for each year level
            const firstYearTotal = firstYear.reduce((sum, student) => sum + student.amount, 0);
            const secondYearTotal = secondYear.reduce((sum, student) => sum + student.amount, 0);
            const thirdYearTotal = thirdYear.reduce((sum, student) => sum + student.amount, 0);
            const fourthYearTotal = fourthYear.reduce((sum, student) => sum + student.amount, 0);
            
            // Update year level totals
            document.getElementById('first-year-total').textContent = `₱${firstYearTotal.toLocaleString()}`;
            document.getElementById('second-year-total').textContent = `₱${secondYearTotal.toLocaleString()}`;
            document.getElementById('third-year-total').textContent = `₱${thirdYearTotal.toLocaleString()}`;
            document.getElementById('fourth-year-total').textContent = `₱${fourthYearTotal.toLocaleString()}`;
            
            // Calculate overall totals
            const totalPending = firstYear.length + secondYear.length + thirdYear.length + fourthYear.length;
            const totalAmount = firstYearTotal + secondYearTotal + thirdYearTotal + fourthYearTotal;
            
            // Update dashboard stats
            document.getElementById('total-pending').textContent = totalPending;
            document.getElementById('total-amount').textContent = `₱${totalAmount.toLocaleString()}`;
            
            // Total students (all year levels)
            const totalStudents = studentsData.length;
            document.getElementById('total-students').textContent = totalStudents;
        }
        
        function acceptPayment(studentId) {
            // Find the student
            const studentIndex = studentsData.findIndex(student => student.id === studentId);
            
            if (studentIndex !== -1) {
                const student = studentsData[studentIndex];
                
                // Update student status
                studentsData[studentIndex].status = "accepted";
                
                // Show modal confirmation
                document.getElementById('accepted-student-name').textContent = student.name;
                document.getElementById('accepted-amount').textContent = `₱${student.amount.toLocaleString()}`;
                
                // Update timestamp
                const now = new Date();
                const timeString = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                document.getElementById('accepted-timestamp').textContent = `Today, ${timeString}`;
                
                const modal = new bootstrap.Modal(document.getElementById('paymentAcceptedModal'));
                modal.show();
                
                // Re-render dashboard with animation
                setTimeout(() => {
                    renderDashboard();
                    // Add animation class to new elements
                    document.querySelectorAll('.fade-in').forEach(el => {
                        el.classList.remove('fade-in');
                        void el.offsetWidth; // Trigger reflow
                        el.classList.add('fade-in');
                    });
                    
                    // Show success toast
                    showToast('Payment accepted successfully');
                }, 500);
            }
        }
        
        function setupEventListeners() {
            // School year filter change
            const yearDropdown = document.getElementById('yearDropdown');
            if (yearDropdown) {
                yearDropdown.addEventListener('click', function(e) {
                    // In a real application, this would filter data from the backend
                    // For this demo, we'll just simulate data loading
                    const yearLevelSections = document.getElementById('year-level-sections');
                    yearLevelSections.style.opacity = '0.7';
                    
                    setTimeout(() => {
                        yearLevelSections.style.opacity = '1';
                    }, 300);
                });
            }
            
            // Analytics period dropdown
            const analyticsPeriod = document.getElementById('analyticsPeriod');
            if (analyticsPeriod) {
                analyticsPeriod.addEventListener('click', function(e) {
                    showToast('Analytics period changed');
                });
            }
            
            // Add hover effects to table rows (for desktop)
            if (window.innerWidth > 768) {
                document.addEventListener('mouseover', function(e) {
                    if (e.target.closest('.student-table tbody tr')) {
                        e.target.closest('.student-table tbody tr').style.transition = 'all 0.2s ease';
                    }
                });
            }
            
            // Handle orientation change
            window.addEventListener('orientationchange', function() {
                // Close sidebar on orientation change for better UX
                if (window.innerWidth <= 1024) {
                    const sidebar = document.querySelector('.sidebar');
                    const sidebarOverlay = document.getElementById('sidebarOverlay');
                    if (sidebar.classList.contains('active')) {
                        sidebar.classList.remove('active');
                        sidebarOverlay.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                }
                
                // Recalculate layout after orientation change
                setTimeout(() => {
                    // Trigger resize event to recalc responsive layouts
                    window.dispatchEvent(new Event('resize'));
                }, 300);
            });
        }