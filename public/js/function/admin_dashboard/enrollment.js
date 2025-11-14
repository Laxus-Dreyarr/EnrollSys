// enrollment.js - Enrollment Period Management

document.addEventListener('DOMContentLoaded', function() {
    // Load enrollment data when the enrollment tab is shown
    $('a[href="#enrollment"]').on('shown.bs.tab', function(e) {
        loadEnrollmentData();
    });

    // Save enrollment period
    $('#saveEnrollmentBtn').click(function() {
        saveEnrollmentPeriod();
    });

    // Clear form when modal is hidden
    $('#enrollmentModal').on('hidden.bs.modal', function() {
        $('#enrollmentForm')[0].reset();
        $('#enrollmentForm input[name="enrollment_id"]').remove();
    });
});

function loadEnrollmentData() {
    $.post('/admin/ajax/get-stats', {
        action: 'get_enrollment_periods',
        _token: $('meta[name="csrf-token"]').attr('content')
    }, function(response) {
        if (response.success) {
            updateEnrollmentCards(response.enrollment_periods);
            updateEnrollmentTable(response.enrollment_periods);
        } else {
            console.error('Error loading enrollment data:', response.message);
            showError('Failed to load enrollment data');
        }
    }, 'json').fail(function(xhr, status, error) {
        console.error('Error loading enrollment data:', error);
        showError('Failed to load enrollment data');
    });
}

function updateEnrollmentCards(periods) {
    const cardsContainer = $('#enrollmentCards');
    cardsContainer.empty();

    const activePeriods = periods.filter(p => p.is_active && isDateInRange(p.start_date, p.end_date));
    const upcomingPeriods = periods.filter(p => p.is_active && new Date(p.start_date) > new Date());
    const expiredPeriods = periods.filter(p => !p.is_active || new Date(p.end_date) < new Date());

    const cards = [
        {
            title: 'Active Enrollment',
            count: activePeriods.length,
            color: 'success',
            icon: 'fa-calendar-check',
            description: 'Currently open for enrollment'
        },
        {
            title: 'Upcoming Enrollment',
            count: upcomingPeriods.length,
            color: 'info',
            icon: 'fa-calendar-plus',
            description: 'Scheduled to open soon'
        },
        {
            title: 'Completed Enrollment',
            count: expiredPeriods.length,
            color: 'secondary',
            icon: 'fa-calendar-times',
            description: 'Past enrollment periods'
        }
    ];

    cards.forEach(card => {
        const cardHtml = `
            <div class="col-md-4 col-6">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">${card.title}</h5>
                                <h2 class="mb-0">${card.count}</h2>
                            </div>
                            <div class="bg-${card.color} p-3 rounded">
                                <i class="fas ${card.icon} fa-2x text-white"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="text-muted">${card.description}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        cardsContainer.append(cardHtml);
    });
}

function updateEnrollmentTable(periods) {
    const tbody = $('#enrollmentTableBody');
    tbody.empty();

    if (periods.length === 0) {
        tbody.append('<tr><td colspan="5" class="text-center">No enrollment periods found</td></tr>');
        return;
    }

    periods.forEach(period => {
        const status = getEnrollmentStatus(period);
        const statusBadge = getStatusBadge(status);
        
        const row = `
            <tr>
                <td>
                    <strong>${period.semester}</strong>
                    ${period.academic_year ? `<br><small class="text-muted">${period.academic_year}</small>` : ''}
                </td>
                <td>${formatDateTime(period.start_date)}</td>
                <td>${formatDateTime(period.end_date)}</td>
                <td>${statusBadge}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="editEnrollmentPeriod(${period.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger ms-1" onclick="deleteEnrollmentPeriod(${period.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function getEnrollmentStatus(period) {
    const now = new Date();
    const start = new Date(period.start_date);
    const end = new Date(period.end_date);

    if (!period.is_active) {
        return 'inactive';
    } else if (now < start) {
        return 'upcoming';
    } else if (now >= start && now <= end) {
        return 'active';
    } else {
        return 'expired';
    }
}

function getStatusBadge(status) {
    const badges = {
        'active': '<span class="badge badge-success">Active</span>',
        'upcoming': '<span class="badge badge-info">Upcoming</span>',
        'expired': '<span class="badge badge-warning">Expired</span>',
        'inactive': '<span class="badge badge-secondary">Inactive</span>'
    };
    return badges[status] || '<span class="badge badge-secondary">Unknown</span>';
}

function isDateInRange(startDate, endDate) {
    const now = new Date();
    const start = new Date(startDate);
    const end = new Date(endDate);
    return now >= start && now <= end;
}

function formatDateTime(dateTimeString) {
    if (!dateTimeString) return 'N/A';
    
    const date = new Date(dateTimeString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

function saveEnrollmentPeriod() {
    const formData = {
        action: 'save_enrollment_period',
        semester: $('#enrollmentSemester').val(), // Changed from document.getElementById('semester')
        academic_year: $('#academicYear').val(),
        start_date: $('#startDate').val(),
        end_date: $('#endDate').val(),
        is_active: $('#isActive').is(':checked') ? 1 : 0,
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    // Validate required fields
    if (!formData.semester || !formData.academic_year || !formData.start_date || !formData.end_date) {
        showError('Please fill all required fields');
        return;
    }

    // Validate dates
    const startDate = new Date(formData.start_date);
    const endDate = new Date(formData.end_date);
    
    if (endDate <= startDate) {
        showError('End date must be after start date');
        return;
    }

    // Show loading state
    const saveBtn = $('#saveEnrollmentBtn');
    saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');

    $.post('/admin/ajax/get-stats', formData, function(response) {
        saveBtn.prop('disabled', false).html('Save Enrollment Period');
        
        if (response.success) {
            $('#enrollmentModal').modal('hide');
            showSuccess('Enrollment period saved successfully!');
            loadEnrollmentData();
        } else {
            showError('Error: ' + response.message);
        }
    }, 'json').fail(function(xhr, status, error) {
        saveBtn.prop('disabled', false).html('Save Enrollment Period');
        console.error('Error saving enrollment period:', error);
        showError('Failed to save enrollment period');
    });
}

function editEnrollmentPeriod(enrollmentId) {
    $.post('/admin/ajax/get-stats', {
        action: 'get_enrollment_period',
        enrollment_id: enrollmentId,
        _token: $('meta[name="csrf-token"]').attr('content')
    }, function(response) {
        if (response.success) {
            const period = response.enrollment_period;
            
            // Populate form
            $('#enrollmentSemester').val(period.semester); // Updated ID
            $('#academicYear').val(period.academic_year);
            $('#startDate').val(formatDateForInput(period.start_date));
            $('#endDate').val(formatDateForInput(period.end_date));
            $('#isActive').prop('checked', period.is_active);
            
            // Add hidden field for edit
            if (!$('#enrollmentForm input[name="enrollment_id"]').length) {
                $('#enrollmentForm').append('<input type="hidden" name="enrollment_id" value="' + period.id + '">');
            } else {
                $('#enrollmentForm input[name="enrollment_id"]').val(period.id);
            }
            
            // Update modal title and button
            $('#enrollmentModal .modal-title').text('Edit Enrollment Period');
            $('#saveEnrollmentBtn').text('Update Enrollment Period');
            
            $('#enrollmentModal').modal('show');
        } else {
            showError('Error: ' + response.message);
        }
    }, 'json').fail(function(xhr, status, error) {
        console.error('Error loading enrollment period:', error);
        showError('Failed to load enrollment period');
    });
}

function deleteEnrollmentPeriod(enrollmentId) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will delete the enrollment period. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        background: '#1a1a2e',
        color: '#ffffff'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('/admin/ajax/get-stats', {
                action: 'delete_enrollment_period',
                enrollment_id: enrollmentId,
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(response) {
                if (response.success) {
                    showSuccess('Enrollment period deleted successfully!');
                    loadEnrollmentData();
                } else {
                    showError('Error: ' + response.message);
                }
            }, 'json').fail(function(xhr, status, error) {
                console.error('Error deleting enrollment period:', error);
                showError('Failed to delete enrollment period');
            });
        }
    });
}

function formatDateForInput(dateTimeString) {
    if (!dateTimeString) return '';
    
    const date = new Date(dateTimeString);
    return date.toISOString().slice(0, 16);
}

function showSuccess(message) {
    Swal.fire({
        title: 'Success!',
        text: message,
        icon: 'success',
        confirmButtonText: 'OK',
        confirmButtonColor: '#0d6efd',
        background: '#1a1a2e',
        color: '#ffffff'
    });
}

function showError(message) {
    Swal.fire({
        title: 'Error!',
        text: message,
        icon: 'error',
        confirmButtonText: 'OK',
        confirmButtonColor: '#0d6efd',
        background: '#1a1a2e',
        color: '#ffffff'
    });
}