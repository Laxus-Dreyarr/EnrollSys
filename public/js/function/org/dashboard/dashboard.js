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
                if (payload.new.operation === 'INSERT') {
                    fetchDashboardData2();
                }
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
        operation: 'UPDATE'
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


// View Students File during Payment Request
// Student Management
function loadStudentsData() {
    fetch('/org-dashboard/students')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error loading students:', data.error);
                return;
            }
            
            displayStudents(data.students);
            updateStudentStats(data.students);
        })
        .catch(error => {
            console.error('Error fetching students data:', error);
        });
}

function displayStudents(students) {
    const studentsGrid = document.querySelector('.students-grid-0926');
    
    if (!studentsGrid) {
        console.error('Students grid element not found!');
        return;
    }
    
    if (students.length === 0) {
        studentsGrid.innerHTML = `
            <div class="no-students-message">
                <i class="fas fa-users fa-3x"></i>
                <h3>No Students Found</h3>
                <p>There are no students in the system yet.</p>
            </div>
        `;
        return;
    }
    
    studentsGrid.innerHTML = students.map(student => `
        <div class="student-card-0926">
            <div class="card-header-0926">
                <div class="student-avatar-0926">
                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(student.name || student.student_id)}&background=4361ee&color=fff" 
                        alt="${student.name || student.student_id}"
                        class="avatar-img-0926">
                    <div class="status-indicator-0926 ${getStatusClass(student.status)}"></div>
                </div>
                <div class="student-basic-info-0926">
                    <h4 class="student-name-0926">${student.name || 'Unknown Student'}</h4>
                    <p class="student-id-0926">${student.student_id}</p>
                </div>
                <div class="card-actions-0926">
                    <div class="dropdown-0926">
                        <button class="dropdown-toggle-0926">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu-0926">
                            <button class="dropdown-item-0926 view-student-0926" data-id="${student.id}" data-student-id="${student.student_id}">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <button class="dropdown-item-0926 edit-student-0926" data-id="${student.id}" data-student-id="${student.student_id}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body-0926">
                <div class="info-row-0926">
                    <i class="fas fa-graduation-cap"></i>
                    <span class="student-program-0926">${student.program || 'N/A'}</span>
                </div>
                <div class="info-row-0926">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="student-year-0926">${student.year_level || 'N/A'}</span>
                </div>
                <div class="info-row-0926">
                    <i class="fas fa-clock"></i>
                    <span class="enrollment-date-0926">Enrolled: ${student.enrollment_date || 'N/A'}</span>
                </div>
            </div>
            <div class="card-footer-0926">
                <span class="status-badge-0926 ${getStatusClass(student.status)}">${student.status}</span>
                <div class="quick-actions-0926">
                    <button class="btn-action-0926 view-student-0926" data-id="${student.id}" data-student-id="${student.student_id}" title="View">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-action-0926 edit-student-0926" data-id="${student.id}" data-student-id="${student.student_id}" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
    
    // Add event listeners to the action buttons
    attachStudentActionListeners();
}

function getStatusClass(status) {
    const statusMap = {
        'Officially Enrolled': 'active',
        'Active': 'active',
        'Pending': 'pending',
        'Not Enrolled': 'inactive',
        'Inactive': 'inactive',
        'Rejected': 'rejected'
    };
    return statusMap[status] || 'inactive';
}

function updateStudentStats(students) {
    const totalStudents = students.length;
    const activeStudents = students.filter(s => s.status === 'Officially Enrolled' || s.status === 'Active').length;
    const pendingStudents = students.filter(s => s.status === 'Pending').length;
    
    // Update the stats in the dashboard if they exist
    const totalStudentsElement = document.querySelector('.stat-card:nth-child(3) .stat-value');
    if (totalStudentsElement) {
        totalStudentsElement.textContent = totalStudents;
    }
    
    // Update the students count in the section
    const studentsCountElement = document.getElementById('students-count-0926');
    if (studentsCountElement) {
        studentsCountElement.textContent = students.length;
    }
}

function attachStudentActionListeners() {
    // View student details
    document.querySelectorAll('.view-student-0926').forEach(button => {
        button.addEventListener('click', function() {
            const studentId = this.getAttribute('data-student-id');
            viewStudentDetails(studentId);
        });
    });
    
    // Edit student
    document.querySelectorAll('.edit-student-0926').forEach(button => {
        button.addEventListener('click', function() {
            const studentId = this.getAttribute('data-student-id');
            editStudent(studentId);
        });
    });
}

function viewStudentDetails(studentId) {
    Swal.fire({
        title: 'Student Details',
        html: `Loading details for student: <strong>${studentId}</strong>`,
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

function editStudent(studentId) {
    Swal.fire({
        title: 'Edit Student',
        html: `Edit student: <strong>${studentId}</strong>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Edit',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('Editing student:', studentId);
        }
    });
}

// Search and filter functionality
function initializeStudentFilters() {
    const searchInput = document.getElementById('student-search-0926');
    const programFilter = document.getElementById('program-filter-0926');
    const yearLevelFilter = document.getElementById('year-level-filter-0926');
    const statusFilter = document.getElementById('status-filter-0926');
    const filterToggle = document.getElementById('filter-toggle-0926');
    const filterOptions = document.getElementById('filter-options-0926');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterStudents);
    }
    if (programFilter) {
        programFilter.addEventListener('change', filterStudents);
    }
    if (yearLevelFilter) {
        yearLevelFilter.addEventListener('change', filterStudents);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', filterStudents);
    }
    
    // Toggle filter options
    if (filterToggle && filterOptions) {
        filterToggle.addEventListener('click', function() {
            filterOptions.classList.toggle('active');
        });
    }
}

function filterStudents() {
    // This would filter the already loaded students
    loadStudentsData();
}

// View toggle functionality
function initializeViewToggle() {
    const viewButtons = document.querySelectorAll('.view-btn-0926');
    const gridView = document.getElementById('grid-view-0926');
    const listView = document.getElementById('list-view-0926');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const view = this.getAttribute('data-view');
            
            // Update active button
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Show/hide views
            if (view === 'grid') {
                gridView.classList.add('active');
                listView.classList.remove('active');
            } else {
                gridView.classList.remove('active');
                listView.classList.add('active');
            }
        });
    });
}

function student() {
    const studentsSection = document.getElementById('students-section-0926');
    if (studentsSection) {
        studentsSection.style.display = 'block';
        loadStudentsData();
    }
}

function settings_clk() {
    const studentsSection = document.getElementById('students-section-0926');
    if (studentsSection) {
        studentsSection.style.display = 'none';
        loadStudentsData();
    }
}

// End View Students File during Payment Request


// Dashboard state
let dashboardState = {
    stats: {
        pendingEnrollmentRequests: 0,
        pendingPaymentVerifications: 0,
        totalStudents: 0,
        approvedThisWeek: 0
    },
    recentActivities: [],
    quickActions: {},
    timeoutId: null,
    subscription: null
};


function fetchDashboardData () {
    fetch('/org-dashboard/data', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json()) 
    .then(data => {
        updateStats(data.stats);
        // updateRecentActivities(data.recentActivities);
        updateQuickActions(data.quickActions);
    })
    .catch(error => {
        alert('Network error. Please check your connection and try again.');
        console.error('Login error:', error);
    });
}

function fetchDashboardData2 () {
    fetch('/org-dashboard/data', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json()) 
    .then(data => {
        updateRecentActivities(data.recentActivities);
    })
    .catch(error => {
        alert('Network error. Please check your connection and try again.');
        console.error('Login error:', error);
    });
}

function updateStats(stats) {
    dashboardState.stats = stats;
    
    // Update stats cards
    updateStatCard('pending-enrollment', stats.pendingEnrollmentRequests);
    updateStatCard('pending-payment', stats.pendingPaymentVerifications);
    updateStatCard('total-students', stats.totalStudents);
    updateStatCard('approved-week', stats.approvedThisWeek);
}

function updateStatCard(statType, value) {
    const cards = {
        'pending-enrollment': '.stat-card:nth-child(1) .stat-value',
        'pending-payment': '.stat-card:nth-child(2) .stat-value',
        'total-students': '.stat-card:nth-child(3) .stat-value',
        'approved-week': '.stat-card:nth-child(4) .stat-value'
    };

    const selector = cards[statType];
    if (selector) {
        const element = document.querySelector(selector);
        if (element) {
            // Animate number change
            animateValue(element, parseInt(element.textContent) || 0, value, 1000);
        }
    }
}

function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        element.textContent = value.toLocaleString();
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

function updateRecentActivities(activities) {
    dashboardState.recentActivities = activities;
    
    const scheduleContainer = document.querySelector('.schedule-day');
    if (!scheduleContainer) return;

    // Clear existing activities (except the header)
    const existingActivities = scheduleContainer.querySelectorAll('.schedule-item');
    existingActivities.forEach(activity => activity.remove());

    if (activities.length === 0 || (activities.length === 1 && !activities[0].student_id)) {
        const noActivities = document.createElement('div');
        noActivities.className = 'schedule-item';
        noActivities.innerHTML = `
            <div class="schedule-time">--:--</div>
            <div class="schedule-details">
                <div class="schedule-course">No pending payment verifications</div>
                <div class="schedule-location">All payments have been processed</div>
            </div>
        `;
        scheduleContainer.appendChild(noActivities);
        return;
    }

    // Add new activities
    activities.forEach(activity => {
        const activityElement = document.createElement('div');
        activityElement.className = 'schedule-item payment-activity';
        
        const time = formatTime(activity.time);
        
        activityElement.innerHTML = `
            <div class="schedule-time">${time}</div>
            <div class="schedule-details">
                <div class="schedule-course">
                    <i class="fas fa-money-bill-wave payment-icon"></i>
                    ${activity.action}
                </div>
                <div class="schedule-location">
                    <strong>${activity.student_id}</strong> - ${activity.year_level}
                </div>
                <div class="payment-actions">
                    <button class="btn-view-details" data-student-id="${activity.student_id}">
                        <i class="fas fa-receipt"></i> View Details
                    </button>
                    <button class="btn-decline-payment" data-student-id="${activity.student_id}">
                        <i class="fa-solid fa-circle-xmark"></i> Decline
                    </button>
                    <button class="btn-approve-payment" data-student-id="${activity.student_id}">
                        <i class="fas fa-check"></i> Approve
                    </button>
                </div>
            </div>
        `;
        
        scheduleContainer.appendChild(activityElement);
    });

    // Re-attach event listeners after a small delay to ensure DOM is updated
    setTimeout(() => {
        attachPaymentEventListeners();
    }, 100);
}

function attachPaymentEventListeners() {
    console.log('Attaching event listeners...');
    
    // Use event delegation for all payment buttons
    const scheduleContainer = document.querySelector('.schedule-day');
    if (!scheduleContainer) return;
    
    scheduleContainer.addEventListener('click', (e) => {
        if (e.target.closest('.btn-view-details')) {
            const button = e.target.closest('.btn-view-details');
            const studentId = button.getAttribute('data-student-id');
            viewStudentDetails2(studentId);
        }
        
        if (e.target.closest('.btn-approve-payment')) {
            const button = e.target.closest('.btn-approve-payment');
            const studentId = button.getAttribute('data-student-id');
            approvePayment(studentId);
        }
        
        if (e.target.closest('.btn-decline-payment')) {
            const button = e.target.closest('.btn-decline-payment');
            const studentId = button.getAttribute('data-student-id');
            declinePayment(studentId);
        }
    });
}

async function viewStudentDetails2(studentId) {
    try {
        console.log('Viewing details for student:', studentId);
        
        const response = await fetch(`/org/student-details/${studentId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (!response.ok) {
            throw new Error('Failed to fetch student details');
        }

        const data = await response.json();
        
        if (data.error) {
            showError(data.error);
            return;
        }

        showStudentDetailsModal(data);

    } catch (error) {
        console.error('Error fetching student details:', error);
        showError('Failed to load student details');
    }
}

// Global modal instance variable
let studentDetailsModalInstance = null;

function showStudentDetailsModal(data) {
    let subjectsHtml = '';
    if (data.subjects && data.subjects.length > 0) {
        subjectsHtml = data.subjects.map(subject => `
            <div class="subject-item">
                <div class="subject-header">
                    <strong>${subject.subject_code}</strong> - ${subject.subject_name}
                    <span class="units">(${subject.units} units)</span>
                </div>
                <div class="subject-details">
                    <small class="text-muted">
                        Section: ${subject.section_name} 
                        ${subject.instructor_name ? `| Instructor: ${subject.instructor_name}` : ''}
                    </small>
                </div>
            </div>
        `).join('');
    } else {
        subjectsHtml = '<div class="text-muted">No enrolled subjects found</div>';
    }

    let fheHtml = data.fheDocument ? 
        `<a href="${data.fheDocument.web_path}" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-file-pdf"></i> View FHE
        </a>` : 
        '<span class="text-danger">No FHE document uploaded</span>';

    let receiptHtml = data.paymentReceipt ? 
        `<a href="${data.paymentReceipt.web_path}" target="_blank" class="btn btn-sm btn-outline-success">
            <i class="fas fa-receipt"></i> View Receipt
        </a>` : 
        '<span class="text-danger">No payment receipt uploaded</span>';

    let enrollmentStatus = data.enrollmentRequest ? 
        `<span class="badge bg-warning">Pending</span>` : 
        '<span class="badge bg-secondary">No Enrollment Request</span>';

    const modalHtml = `
        <div class="modal fade" id="studentDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Student Enrollment Details - ${data.student.id_no}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>Student Information</h6>
                                <p><strong>ID:</strong> ${data.student.id_no}</p>
                                <p><strong>Year Level:</strong> ${data.student.year_level}</p>
                                <p><strong>Status:</strong> ${data.student.status}</p>
                                <p><strong>Enrollment Request:</strong> ${enrollmentStatus}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Documents</h6>
                                <p><strong>FHE:</strong> ${fheHtml}</p>
                                <p><strong>Payment Receipt:</strong> ${receiptHtml}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <h6>Enrolled Subjects (${data.subjects ? data.subjects.length : 0})</h6>
                                <div class="subjects-list">
                                    ${subjectsHtml}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Check if we have an existing modal instance
    if (studentDetailsModalInstance) {
        studentDetailsModalInstance.hide();
        studentDetailsModalInstance.dispose();
    }

    // Remove existing modal
    const existingModal = document.getElementById('studentDetailsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Clean up backdrops
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    

    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Create new modal instance
    const modalElement = document.getElementById('studentDetailsModal');
    studentDetailsModalInstance = new bootstrap.Modal(modalElement);

    // Clean up on hide
    modalElement.addEventListener('hidden.bs.modal', function() {
        studentDetailsModalInstance.dispose();
        studentDetailsModalInstance = null;
        modalElement.remove();
        
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
    });

    studentDetailsModalInstance.show();
    
    // Show modal
    // const modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
    // modal.show();
}

async function approvePayment(studentId) {
    try {
        const confirmed = await Swal.fire({
            title: 'Approve Payment?',
            text: `Are you sure you want to approve payment for student ${studentId}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#4361ee'
        });

        if (!confirmed.isConfirmed) return;

        const response = await fetch('/org/approve-payment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                student_id: studentId
            })
        });

        const result = await response.json();
        insertsupabase();

        if (result.success) {
            Swal.fire({
                title: 'Payment Approved!',
                text: `Payment for student ${studentId} has been approved successfully.`,
                icon: 'success',
                confirmButtonColor: '#4361ee'
            });

            // Refresh dashboard data
            if (dashboardState.timeoutId) {
                clearTimeout(dashboardState.timeoutId);
            }
            dashboardState.timeoutId = setTimeout(() => {
                fetchDashboardData2();
            }, 500);
        } else {
            throw new Error(result.message || 'Failed to approve payment');
        }

    } catch (error) {
        console.error('Error approving payment:', error);
        showError('Failed to approve payment: Check your internet connection');
        // showError('Failed to approve payment: ' + error.message);
    }
}

async function declinePayment(studentId) {
    try {
        const confirmed = await Swal.fire({
            title: 'Decline Payment?',
            text: `Are you sure you want to decline payment for student ${studentId}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Decline',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#e74c3c'
        });

        if (!confirmed.isConfirmed) return;

        const response = await fetch('/org/decline-payment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                student_id: studentId
            })
        });

        const result = await response.json();

        if (result.success) {
            insertsupabase();
            Swal.fire({
                title: 'Payment Declined!',
                text: `Payment for student ${studentId} has been declined.`,
                icon: 'success',
                confirmButtonColor: '#e74c3c'
            });

            // Refresh dashboard data
            if (dashboardState.timeoutId) {
                clearTimeout(dashboardState.timeoutId);
            }
            dashboardState.timeoutId = setTimeout(() => {
                fetchDashboardData2();
            }, 500);
        } else {
            throw new Error(result.message || 'Failed to decline payment');
        }

    } catch (error) {
        console.error('Error declining payment:', error);
        showError('Failed to decline payment: Check your internet connection');
        // showError('Failed to decline payment: ' + error.message);
    }
}

function formatTime(timeString) {
    if (!timeString) return '--:--';
    return timeString;
}

function updateQuickActions(quickActions) {
    dashboardState.quickActions = quickActions;
    
    // Update quick action cards
    updateQuickActionCard(0, quickActions.processingRate, quickActions.pendingRequests);
    updateQuickActionCard(1, quickActions.verificationRate, quickActions.pendingPayments);
    updateQuickActionCard(2, quickActions.activeStudentsRate, dashboardState.stats.totalStudents);
}

function updateQuickActionCard(cardIndex, rate, count) {
    const cards = document.querySelectorAll('.course-card');
    if (cards[cardIndex]) {
        const progressBar = cards[cardIndex].querySelector('.progress');
        const rateSpan = cards[cardIndex].querySelector('.progress-label span:last-child');
        const countSpan = cards[cardIndex].querySelector('.course-info span:first-child');
        
        if (progressBar) {
            progressBar.style.width = rate + '%';
        }
        if (rateSpan) {
            rateSpan.textContent = rate + '%';
        }
        if (countSpan) {
            // Update the count text based on card type
            let countText = '';
            switch(cardIndex) {
                case 0:
                    countText = count + ' pending requests';
                    break;
                case 1:
                    countText = count + ' pending verifications';
                    break;
                case 2:
                    countText = count + ' total students';
                    break;
            }
            countSpan.textContent = countText;
        }
    }
}

function showError(message) {
    console.error('Dashboard Error:', message);
    
    // Optionally show a toast notification
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            timer: 3000
        });
    }
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
            const response = await fetch('/org/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            window.location.href = '/org';

    });

    // Alternative: Use form submission (simpler but no loading state)
    // logoutConfirmBtn.addEventListener('click', function() {
    //     logoutForm.submit();
    // });
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

     initializeLogout(); 
        // 
        const section = document.getElementById('students-section-0926');
        if (section) {
            console.log('Student section found:', section);
            console.log('Display style:', section.style.display);
            console.log('Computed display:', window.getComputedStyle(section).display);
        } else {
            console.error('Student section not found!');
        }

        // Check if students section is visible and load data
        const studentsSection = document.getElementById('students-section-0926');
        if (studentsSection && studentsSection.style.display !== 'none') {
            console.log('Students section is visible, loading data...');
            loadStudentsData();
        }

        // Also set up intersection observer or mutation observer to detect when section becomes visible
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    const studentsSection = document.getElementById('students-section-0926');
                    if (studentsSection && studentsSection.style.display !== 'none') {
                        console.log('Students section became visible, loading data...');
                        loadStudentsData();
                    }
                }
            });
        });

        if (studentsSection) {
            observer.observe(studentsSection, { attributes: true });
        }
    
        // Also load when switching to students section
        const menuItems2 = document.querySelectorAll('.menu-item');
        menuItems2.forEach(item => {
            item.addEventListener('click', function() {
                if (this.getAttribute('data-section') === 'students') {
                    setTimeout(loadStudentsData, 100);
                }
            });
        });
    
        initializeStudentFilters();

        //

            // Initial data fetch
        if (dashboardState.timeoutId) {
            clearTimeout(dashboardState.timeoutId);
        }
        dashboardState.timeoutId = setTimeout(() => {
            fetchDashboardData2();
        }, 0);
        
        // Setup real-time subscription
        setupRealtimeSubscription();
        


            initializeThemeColorPicker();
            // Navigation functionality
            const menuItems = document.querySelectorAll('.menu-item');
            const contentSections = document.querySelectorAll('.content-section');
            const pageTitle = document.querySelector('.page-title');
            
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    // if (this.id === 'logout-btn') {
                    //     if (confirm('Are you sure you want to logout?')) {
                    //         alert('Logging out...');
                    //         // Redirect to logout URL
                    //         window.location.href = '/logout';
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
                        insertsupabase();
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
                        insertsupabase();
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