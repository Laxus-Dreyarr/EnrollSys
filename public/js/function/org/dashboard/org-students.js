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


// Load students when the students section is shown
document.addEventListener('DOMContentLoaded', function() {

    
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
    const menuItems = document.querySelectorAll('.menu-item');
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            if (this.getAttribute('data-section') === 'students') {
                setTimeout(loadStudentsData, 100);
            }
        });
    });
    
    initializeStudentFilters();
});
