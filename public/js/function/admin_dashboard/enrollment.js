// enrollment.js - Enrollment Period Management

document.addEventListener('DOMContentLoaded', function() {
    loadEnrollmentData();
    // Load enrollment data when the enrollment tab is shown
    $('a[href="#enrollment"]').on('shown.bs.tab', function(e) {
        loadEnrollmentData();
    });

    // On button click
    $('#onBtn').click(function() {
        startEnrollmentPeriod();
    });

    // Off button click
    $('#offBtn').click(function() {
        stopEnrollmentPeriod();
    });

    // Handle enrollment type switch
    $('#enrollmentTypeSwitch').change(function() {
        toggleEnrollmentForm(this.checked);
    });

    // Update display when modal is shown
    $('#enrollmentModal').on('show.bs.modal', function() {
        updateEnrollmentDisplay();
    });
});

function toggleEnrollmentForm(isSummer) {
    const modalTitle = $('#modalTitle');
    const switchLabel = $('#switchLabel');
    const switchDescription = $('#switchDescription');
    
    if (isSummer) {
        // Switch to Summer form
        $('#regularStatusInfo').hide();
        $('#summerStatusInfo').show();
        modalTitle.text('Set Summer Enrollment Period');
        switchLabel.text('Summer');
        switchDescription.text('(Switch back to Regular)');
        updateSummerDisplay();
    } else {
        // Switch to Regular form
        $('#summerStatusInfo').hide();
        $('#regularStatusInfo').show();
        modalTitle.text('Set Enrollment Period');
        switchLabel.text('Regular');
        switchDescription.text('(Switch to Summer)');
        updateRegularDisplay();
    }
}

function updateEnrollmentDisplay() {
    const isSummer = $('#enrollmentTypeSwitch').is(':checked');
    
    if (isSummer) {
        updateSummerDisplay();
    } else {
        updateRegularDisplay();
    }
}

function updateRegularDisplay() {
    const now = new Date();
    const currentYear = now.getFullYear();
    const currentMonth = now.getMonth() + 1; // JavaScript months are 0-11
    
    let semester;
    let academicYear;
    
    // Determine semester based on month
    if (currentMonth >= 3 && currentMonth <= 5) {
        // March, April, May - 1st Sem for freshmen enrollment (classes start July)
        semester = '1st Sem';
        academicYear = `${currentYear}-${currentYear + 1}`;
    } else if ((currentMonth >= 7 && currentMonth <= 12) || currentMonth === 6) {
        // July to December and June - 1st Sem
        semester = '1st Sem';
        academicYear = `${currentYear}-${currentYear + 1}`;
    } else if (currentMonth >= 1 && currentMonth <= 2) {
        // January and February - 2nd Sem
        semester = '2nd Sem';
        academicYear = `${currentYear - 1}-${currentYear}`;
    }
    
    // Update display
    $('#semesterDisplay').text(semester);
    $('#academicYearDisplay').text(academicYear);
    
    // Set hidden form values
    $('#regularSemester').val(semester);
    $('#regularAcademicYear').val(academicYear);
}

function updateSummerDisplay() {
    const now = new Date();
    const currentYear = now.getFullYear();
    
    // Update display
    $('#summerAcademicYearDisplay').text(currentYear);
    
    // Set hidden form values
    $('#summerAcademicYear').val(currentYear);
}

function getActiveFormData() {
    const isSummer = $('#enrollmentTypeSwitch').is(':checked');
    
    if (isSummer) {
        return {
            semester: 'Summer',
            academic_year: $('#summerAcademicYear').val(),
            is_active: 1
        };
    } else {
        return {
            semester: $('#regularSemester').val(),
            academic_year: $('#regularAcademicYear').val(),
            is_active: 1
        };
    }
}

function startEnrollmentPeriod() {
    const formData = getActiveFormData();
    const isSummer = $('#enrollmentTypeSwitch').is(':checked');
    
    // Validate
    if (!formData.semester || !formData.academic_year) {
        showError('Cannot determine enrollment parameters. Please try again.');
        return;
    }
    
    // Show loading state
    const onBtn = $('#onBtn');
    onBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Starting...');
    
    $.post('/admin/ajax/get-stats', {
        action: 'start_enrollment_period',
        semester: formData.semester,
        academic_year: formData.academic_year,
        _token: $('meta[name="csrf-token"]').attr('content')
    }, function(response) {
        onBtn.prop('disabled', false).html('On');
        
        if (response.success) {
            insertsupabase2();
            $('#enrollmentModal').modal('hide');
            showSuccess('Enrollment period started successfully!');
            loadEnrollmentData();
        } else {
            showError('Error: ' + response.message);
        }
    }, 'json').fail(function(xhr, status, error) {
        onBtn.prop('disabled', false).html('On');
        console.error('Error starting enrollment period:', error);
        showError('Failed to start enrollment period');
    });
}

function stopEnrollmentPeriod() {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will stop the current enrollment period and reset all student enrollment status.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, stop it!',
        cancelButtonText: 'Cancel',
        background: '#1a1a2e',
        color: '#ffffff'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            $('#offBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Stopping...');
            
            $.post('/admin/ajax/get-stats', {
                action: 'stop_enrollment_period',
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(response) {
                $('#offBtn').prop('disabled', false).html('Off');
                
                if (response.success) {
                    insertsupabase2();
                    $('#enrollmentModal').modal('hide');
                    showSuccess('Enrollment period stopped successfully!');
                    loadEnrollmentData();
                } else {
                    showError('Error: ' + response.message);
                }
            }, 'json').fail(function(xhr, status, error) {
                $('#offBtn').prop('disabled', false).html('Off');
                console.error('Error stopping enrollment period:', error);
                showError('Failed to stop enrollment period');
            });
        }
    });
}

function loadEnrollmentData() {
    $.post('/admin/ajax/get-stats', {
        action: 'get_enrollment_periods',
        _token: $('meta[name="csrf-token"]').attr('content')
    }, function(response) {
        console.log('Response:', response); // Debug line
        if (response.success) {
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

function updateEnrollmentTable(periods) {
    const tbody = $('#enrollmentTableBody');
    tbody.empty();

    if (periods.length === 0) {
        tbody.append('<tr><td colspan="2" class="text-center">No enrollment periods found</td></tr>');
        return;
    }

    periods.forEach(period => {
        const row = `
            <tr>
                <td>
                    <strong>${period.semester}</strong>
                </td>
                <td>
                    <strong>${period.academic_year}</strong>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
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