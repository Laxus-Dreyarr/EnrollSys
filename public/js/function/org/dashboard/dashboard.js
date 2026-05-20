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


// Realtime update for submit
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

async function updatesupabase(studentId){
    const data = {
        table_name: 'status',  // make sure these variables are defined
        operation: 'UPDATE2',
        student_id: studentId,
        status: 'Approved'
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


let allPayments = []; // Store all payments for filtering
let allStudentsList = []; // Store all students for search/filter and export
let currentFilters = {
    status: '',
    month: ''
};

// Load payments data
function loadPayments() {
    showLoadingState();
    
    $.ajax({
        url: '/org/fees/data',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                allPayments = response.payments;
                applyFilters(); // Apply any existing filters
                updateStatsGrid(allPayments); // Update stats grid with full data
                if (response.total_students !== undefined) {
                    $('#total-students').text(response.total_students);
                }
                if (response.collected_today !== undefined) {
                    $('#collected-change').html(`<i class="fas fa-arrow-up me-1"></i> ₱${parseFloat(response.collected_today).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} today`);
                }
                if (response.pending_today !== undefined) {
                    $('#pending-change').html(`<i class="fas fa-arrow-up me-1"></i> ₱${parseFloat(response.pending_today).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} today`);
                }
                if (response.approved_today_count !== undefined) {
                    $('#accepted-change').html(`<i class="fas fa-arrow-up me-1"></i> ${response.approved_today_count} today`);
                }
                if (response.new_students_this_week !== undefined) {
                    $('#students-change').html(`<i class="fas fa-arrow-up me-1"></i> ${response.new_students_this_week} this week`);
                }
            } else {
                showError('Failed to load payments: ' + response.error);
                showEmptyState('Failed to load payments');
            }
        },
        error: function(xhr, status, error) {
            let errorMessage = 'Error loading payments. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMessage = 'Error: ' + xhr.responseJSON.error;
            }
            showError(errorMessage);
            showEmptyState('Failed to load payments');
            console.error('Payment load error:', error);
        }
    });
}

// Load all students data via AJAX
function loadAllStudents() {
    const tableBody = $('#all-students-list');
    tableBody.html(`
        <tr>
            <td colspan="6" class="text-center py-4">
                <div class="d-flex flex-column align-items-center justify-content-center gap-2">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="text-muted mb-0">Loading students data...</p>
                </div>
            </td>
        </tr>
    `);

    $.ajax({
        url: '/org-dashboard/students',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.students) {
                allStudentsList = response.students;
                renderAllStudents(allStudentsList);
            } else {
                showStudentEmptyState('Failed to load students data');
            }
        },
        error: function(xhr, status, error) {
            showStudentEmptyState('Error loading students data. Please try again.');
            console.error('Students load error:', error);
        }
    });
}

// Show empty state for student table
function showStudentEmptyState(message) {
    const tableBody = $('#all-students-list');
    tableBody.html(`
        <tr>
            <td colspan="6" class="text-center py-5">
                <div class="d-flex flex-column align-items-center justify-content-center p-4">
                    <i class="fas fa-users-slash fa-3x text-muted mb-3" style="opacity: 0.5;"></i>
                    <p class="text-muted mb-0" style="font-weight: 500;">${message}</p>
                </div>
            </td>
        </tr>
    `);
}

// Render student table rows with premium UI styling
function renderAllStudents(students) {
    const tableBody = $('#all-students-list');
    tableBody.empty();
    
    // Update students count badge
    $('#students-count').text(students.length);
    
    if (students.length === 0) {
        showStudentEmptyState('No students found matching the criteria.');
        return;
    }
    
    students.forEach(student => {
        // Initials avatar
        const initials = student.name
            .split(' ')
            .map(n => n[0])
            .join('')
            .toUpperCase()
            .substring(0, 2);
            
        // Map status to badge style matching org styling
        let statusBadge = '';
        const statusClean = (student.status || '').toLowerCase().trim();
        
        if (statusClean === 'paid' || statusClean === 'approved' || statusClean === 'enrolled') {
            statusBadge = '<span class="status-badge status-paid">Paid</span>';
        } else if (statusClean === 'pending') {
            statusBadge = '<span class="status-badge status-pending">Pending</span>';
        } else if (statusClean === 'overdue') {
            statusBadge = '<span class="status-badge" style="background: #fee2e2; color: #991b1b; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; display: inline-block;">Overdue</span>';
        } else {
            statusBadge = `<span class="status-badge" style="background: #e2e8f0; color: #475569; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; display: inline-block;">${student.status || 'Pending'}</span>`;
        }
        
        const row = `
            <tr>
                <td>
                    <div class="student-info">
                        <div class="student-avatar" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; margin-right: 12px; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);">
                            ${initials}
                        </div>
                        <div class="student-details">
                            <h4 style="margin: 0; font-size: 14px; font-weight: 600; color: #1e293b;">${student.name}</h4>
                            <p style="margin: 0; font-size: 12px; color: #64748b;">${student.email || 'No email'}</p>
                        </div>
                    </div>
                </td>
                <td>
                    <span style="font-family: monospace; font-size: 13px; color: #475569; font-weight: 500;">${student.student_id}</span>
                </td>
                <td>${student.year_level}</td>
                <td>${student.program || 'BS Information Technology'}</td>
                <td>${statusBadge}</td>
                <td>
                    <button class="view-btn view-student-btn" data-student='${JSON.stringify(student).replace(/'/g, "&apos;")}'>
                        <i class="fas fa-eye"></i> View
                    </button>
                </td>
            </tr>
        `;
        tableBody.append(row);
    });
}

// Apply filters to payments
function applyFilters() {
    let filteredPayments = [...allPayments];
    
    // Apply status filter
    if (currentFilters.status) {
        filteredPayments = filteredPayments.filter(payment => 
            payment.status === currentFilters.status
        );
    }
    
    // Apply month filter
    if (currentFilters.month) {
        filteredPayments = filteredPayments.filter(payment => {
            const paymentDate = new Date(payment.raw_date || payment.date);
            const filterDate = new Date(currentFilters.month + '-01');
            
            return paymentDate.getFullYear() === filterDate.getFullYear() && 
                   paymentDate.getMonth() === filterDate.getMonth();
        });
    }
    
    // Populate table with filtered payments
    populatePaymentsTable(filteredPayments);
    
    // Update total amount and count
    updateTotalAmount(filteredPayments);
    
    // Update UI to show active filters
    updateFilterUI();
}

// Populate payments table
function populatePaymentsTable(payments) {
    const paymentsList = $('#payments-list');
    paymentsList.empty();
    
    if (payments.length === 0) {
        showEmptyState('No payments match your filters');
        return;
    }
    
    payments.forEach(payment => {
        const row = `
            <tr data-payment-id="${payment.payment_id}" data-student-id="${payment.student_id}">
                <td>${payment.date}</td>
                <td>
                    <div class="student-info">
                        <strong>${payment.student_name}</strong>
                        <small>${payment.student_id}</small>
                        <div class="student-year">${payment.year_level}</div>
                    </div>
                </td>
                <td>${payment.payment_id}</td>
                <td class="amount-cell">
                    <span class="amount">₱${payment.amount}</span>
                </td>
                <td>
                    <span class="status-badge ${payment.status_class}">
                        ${payment.status}
                    </span>
                </td>
                <td>
                    ${payment.receipt_url ? `
                    <button class="view-btn view-receipt-btn" 
                            data-payment-id="${payment.payment_id}"
                            data-receipt-url="${payment.receipt_url}"
                            data-folder="${payment.folder}"
                            data-filename="${payment.filename}">
                        <i class="fas fa-eye"></i> View
                    </button>
                    ` : `
                    <button class="view-btn" disabled>
                        <i class="fas fa-eye-slash"></i> No Receipt
                    </button>
                    `}
                    ${payment.status === 'Pending' ? `
                    <div class="action-buttons">
                        <button class="approve-btn" data-payment-id="${payment.payment_id}" placeholder="Approve">
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                    ` : ''}
                </td>
            </tr>
        `;
        paymentsList.append(row);
    });
    
    // Attach event listeners
    attachPaymentEventListeners();
}


// Update total amount and count
function updateTotalAmount(payments) {
    if (payments.length === 0) {
        $('#payments-total').text('₱0.00');
        $('#filtered-count').text('(0 payments)');
        return;
    }
    
    const totalAmount = payments.reduce((sum, payment) => {
        return sum + parseFloat(payment.raw_amount || payment.amount.replace('₱', '').replace(',', ''));
    }, 0);
    
    $('#payments-total').text('₱' + totalAmount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
    $('#filtered-count').text(`(${payments.length} payment${payments.length !== 1 ? 's' : ''})`);
}

// Update stats grid with payment data
function updateStatsGrid(payments) {
    if (payments.length === 0) return;
    
    const approvedPayments = payments.filter(p => p.status === 'Approved');
    const pendingPayments = payments.filter(p => p.status === 'Pending');
    
    // Calculate totals
    const totalCollected = approvedPayments.reduce((sum, p) => 
        sum + parseFloat(p.raw_amount || p.amount.replace('₱', '').replace(',', '')), 0);
    
    const totalPending = pendingPayments.reduce((sum, p) => 
        sum + parseFloat(p.raw_amount || p.amount.replace('₱', '').replace(',', '')), 0);
    
    // Update stats cards
    $('.stat-card:nth-child(1) .stat-value').text('₱' + totalCollected.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
    $('.stat-card:nth-child(2) .stat-value').text('₱' + totalPending.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ","));
    $('.stat-card:nth-child(3) .stat-value').text(approvedPayments.length);
    
    // You might want to calculate changes from previous period
    // For now, we'll just show the counts
}


// Update filter UI to show active filters
function updateFilterUI() {
    const statusFilter = $('#status-filter');
    const monthFilter = $('#month-filter');
    const clearBtn = $('#clear-filters');
    
    // Reset active states
    statusFilter.removeClass('filter-active');
    monthFilter.removeClass('filter-active');
    
    // Add active state to filters with values
    if (currentFilters.status) {
        statusFilter.addClass('filter-active');
    }
    if (currentFilters.month) {
        monthFilter.addClass('filter-active');
    }
    
    // Show/hide clear button
    if (currentFilters.status || currentFilters.month) {
        clearBtn.show();
    } else {
        clearBtn.hide();
    }
}


// Show empty state
function showEmptyState(message = 'No payment records found') {
    $('#payments-list').html(`
        <tr>
            <td colspan="6" class="text-center">
                <div class="empty-state">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <p>${message}</p>
                    ${currentFilters.status || currentFilters.month ? 
                        '<button id="clear-filters-empty" class="btn btn-sm btn-outline-secondary mt-2">Clear Filters</button>' : 
                        ''
                    }
                </div>
            </td>
        </tr>
    `);
    
    // Attach clear filter listener to empty state button
    $('#clear-filters-empty').on('click', clearFilters);
}

// Show loading state
function showLoadingState() {
    $('#payments-list').html(`
        <tr>
            <td colspan="6" class="text-center">
                <div class="loading-state">
                    <i class="fas fa-spinner"></i>
                    <p>Loading payments...</p>
                </div>
            </td>
        </tr>
    `);
}

// Filter event handlers
function setupFilterHandlers() {
    // Status filter change
    $('#status-filter').on('change', function() {
        currentFilters.status = $(this).val();
        applyFilters();
    });
    
    // Month filter change
    $('#month-filter').on('change', function() {
        currentFilters.month = $(this).val();
        applyFilters();
    });
    
    // Clear filters button
    $('#clear-filters').on('click', clearFilters);
}

function clearFilters() {
    $('#status-filter').val('');
    $('#month-filter').val('');
    currentFilters = { status: '', month: '' };
    applyFilters();
}

function setupExportHandler() {
    $('#export-btn').on('click', function() {
        if (allPayments.length === 0) {
            showError('No data to export');
            return;
        }
        
        // Create CSV data
        let csvContent = "data:text/csv;charset=utf-8,";
        
        // Add headers
        const headers = ["Date", "Student Name", "Student ID", "Year Level", "Amount", "Status", "Payment ID"];
        csvContent += headers.join(",") + "\n";
        
        // Add data rows
        allPayments.forEach(payment => {
            const row = [
                `"${payment.date}"`,
                `"${payment.student_name}"`,
                `"${payment.student_id}"`,
                `"${payment.year_level}"`,
                `"₱${payment.amount}"`,
                `"${payment.status}"`,
                `"${payment.payment_id}"`
            ];
            csvContent += row.join(",") + "\n";
        });
        
        // Create download link
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `payments_export_${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        
        // Trigger download
        link.click();
        document.body.removeChild(link);
        
        showSuccess('Export started. Your download should begin shortly.');
    });
}

// Update payment statistics
function updatePaymentStats(payments) {
    const totalPayments = payments.length;
    const pendingPayments = payments.filter(p => p.status === 'Pending').length;
    const approvedPayments = payments.filter(p => p.status === 'Approved').length;
    const totalAmount = payments.reduce((sum, p) => sum + parseFloat(p.raw_amount), 0);
    
    // Update stats cards if they exist
    $('#total-payments-count').text(totalPayments);
    $('#pending-payments-count').text(pendingPayments);
    $('#approved-payments-count').text(approvedPayments);
    $('#total-amount').text('₱' + totalAmount.toFixed(2));
}

// Attach event listeners to payment buttons
function attachPaymentEventListeners() {
    // View receipt button
    $('.view-receipt-btn').on('click', function() {
        const paymentId = $(this).data('payment-id');
        const folder = $(this).data('folder');
        const filename = $(this).data('filename');
        
        if (folder && filename) {
            // Open receipt in new tab
            const receiptUrl = `/documents/${folder}/${filename}`;
            window.open(receiptUrl, '_blank');
        } else {
            showError('Receipt file not found');
        }
    });
    
    // Approve payment button
    $('.approve-btn').on('click', function(e) {
        e.stopPropagation();
        const paymentId = $(this).data('payment-id');
        approvePayment(paymentId);
    });
    
    // Reject payment button
    $('.reject-btn').on('click', function(e) {
        e.stopPropagation();
        const paymentId = $(this).data('payment-id');
        rejectPayment(paymentId);
    });
}

// Approve payment function
function approvePayment(paymentId) {
    if (!confirm('Are you sure you want to approve this payment?')) return;
    
    $.ajax({
        url: '/org/fees/accept',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            payment_id: paymentId
        },
        success: function(response) {
            if (response.success) {
                showSuccess('Payment approved successfully!');
                loadPayments(); // Refresh the list
            } else {
                showError(response.error || 'Failed to approve payment');
            }
        },
        error: function() {
            showError('Error approving payment. Please try again.');
        }
    });
}

// Reject payment function
function rejectPayment(paymentId) {
    const reason = prompt('Please enter reason for rejection:', '');
    
    if (reason === null) return;
    
    if (!reason.trim()) {
        showError('Rejection reason is required');
        return;
    }
    
    $.ajax({
        url: '/org/reject-payment',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            payment_id: paymentId,
            reason: reason
        },
        success: function(response) {
            if (response.success) {
                showSuccess('Payment rejected successfully!');
                loadPayments(); // Refresh the list
            } else {
                showError(response.error || 'Failed to reject payment');
            }
        },
        error: function() {
            showError('Error rejecting payment. Please try again.');
        }
    });
}

// Add these utility functions if not already present
function showLoading() {
    $('#payments-list').html(`
        <tr>
            <td colspan="6" class="text-center">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading payments...</p>
                </div>
            </td>
        </tr>
    `);
}

function hideLoading() {
    // Remove loading indicator
}

function showError(message) {
    // Use your preferred notification method
    alert('Error: ' + message);
}

function showSuccess(message) {
    // Use your preferred notification method
    alert('Success: ' + message);
}
        
$(document).ready(function() {
    loadPayments();
    loadAllStudents();
    setupFilterHandlers();
    setupExportHandler();
    // Load payment data
    function loadPaymentData() {
        console.log('Attempting to load payment data...');
        
        $.ajax({
            url: '/org/payments/refresh',
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Response received:', response);
                
                if (response.success) {
                    displayPaymentData(response.data);
                    updateDashboardStats(response.data);
                } else {
                    console.error('Server returned error:', response.message);
                    showErrorMessage('Failed to load payment data: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error Details:');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Response:', xhr.responseText);
                
                // Use embedded data from the page
                console.log('Using embedded data instead');
                useEmbeddedData();
            }
        });
    }

    // Update dashboard stats from data
    function updateDashboardStats(data) {
        let totalPending = 0;
        let totalAmount = 0;
        let acceptedToday = 0;
        let totalStudents = 0;
        
        // Calculate totals from all year levels
        Object.values(data).forEach(yearData => {
            totalPending += yearData.pending_count || 0;
            totalAmount += yearData.total || 0;
            // For accepted today and total students, we would need additional data
        });
        
        // Update the stats cards
        $('#total-pending').text(totalPending);
        $('#total-amount').text('₱' + totalAmount.toFixed(2));
        // Update other stats as needed
    }

    // Display payment data in tables
    function displayPaymentData(data) {
        // Map database year levels to display IDs
        const yearMapping = {
            'first_year': 'first-year',
            'second_year': 'second-year',
            'third_year': 'third-year',
            'fourth_year': 'fourth-year'
        };

        // Process each year level
        Object.keys(yearMapping).forEach(yearKey => {
            const yearData = data[yearKey] || { payments: [], total: 0, pending_count: 0 };
            const tbodyId = `${yearMapping[yearKey]}-students`;
            
            // Update pending count badge
            const pendingBadge = $(`#${yearMapping[yearKey]}-pending`);
            pendingBadge.text(`${yearData.pending_count} pending`);
            
            // Update total amount
            const totalSpan = $(`#${yearMapping[yearKey]}-total`);
            totalSpan.text(`₱${yearData.total.toFixed(2)}`);
            
            // Update table rows
            const $tbody = $(`#${tbodyId}`);
            $tbody.empty(); // Clear existing rows
            
            const card = $(`#${yearMapping[yearKey]}-card`);
            if (yearData.payments && yearData.payments.length > 0) {
                card.show();
                yearData.payments.forEach(payment => {
                    const fullName = `${payment.firstname || ''} ${payment.lastname || ''}`.trim();
                    const avatarInitial = fullName ? fullName.charAt(0) : 'N';
                    const contact = payment.phone_number || 'No contact';
                    const amount = payment.amount ? `₱${parseFloat(payment.amount).toFixed(2)}` : '₱0.00';
                    
                    // Format date
                    const date = new Date(payment.created_at);
                    const formattedDate = date.toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    const isPaymentNotice = payment.file_path && payment.file_path.includes('payment_notice_');
                    const rowClass = isPaymentNotice ? 'payment-notice-row' : '';
                    
                    $tbody.append(`
                        <tr data-payment-id="${payment.id}" class="${rowClass}">
                            <td>
                                <div class="student-info">
                                    <div class="avatar">
                                        ${isPaymentNotice ? 
                                            '' : 
                                            `<span>${avatarInitial}</span>`
                                        }
                                    </div>
                                    <div>
                                        <div class="student-name">${fullName || 'N/A'}</div>
                                        <div class="student-email">${payment.email || 'No email'}</div>
                                    </div>
                                </div>
                            </td>
                            <td>${payment.id_no || 'N/A'}</td>
                            <td>${contact}</td>
                            <td class="amount">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">${amount}</span>
                                    ${isPaymentNotice ? 
                                        '<small style="color: green;"><i class="fas fa-info-circle"></i> Payment will be made later</small>' : ''}
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-pending">Pending</span>
                            </td>
                            <td>${formattedDate}</td>
                            <td>
                                <div class="action-buttons d-flex gap-2">
                                    <button class="btn btn-sm btn-success approve-payment" 
                                            data-payment-id="${payment.id}" 
                                            data-student-id="${payment.student_id}">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    ${payment.file_path ? 
                                        `<a href="/documents/${payment.file_path.replace('documents/', '')}" 
                                        target="_blank"
                                        class="btn btn-sm btn-outline-primary view-receipt-btn">
                                            <i class="fas fa-eye"></i> View
                                        </a>` : ''
                                    }
                                </div>
                            </td>
                        </tr>
                    `);
                });
            } else {
                card.hide();
            }
        });
        
        attachEventHandlers();
        updateDashboardStats(data);
    }


    // -------
$(document).on('click', '.approve-payment', function(e) {
    e.preventDefault();
    
    const paymentId = $(this).data('payment-id');
    const studentId = $(this).data('student-id');
    const button = $(this);
    const row = button.closest('tr');
    
    console.log('Approve payment clicked:', { paymentId, studentId });
    
    Swal.fire({
        title: 'Approve Payment?',
        text: "Are you sure you want to approve this payment?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, approve it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            
            // Get CSRF token
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            
            console.log('Sending approval request with token:', csrfToken);
            
            $.ajax({
                url: '/org/approve-payment', // This should match your route
                method: 'POST',
                data: {
                    _token: csrfToken,
                    payment_id: paymentId,
                    student_id: studentId
                },
                success: function(response) {
                    console.log('Approval response:', response);
                    
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Update row without reloading
                            row.find('td:nth-child(5)').html('<span class="status-badge status-approved">Approved</span>');
                            row.find('td:nth-child(7)').html('<span class="text-muted">Processed</span>');
                            
                            // Update pending counts
                            updateCountsFromTable();
                            updatesupabase(studentId);
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                        button.prop('disabled', false).html('<i class="fas fa-check"></i> Approve');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    Swal.fire('Error!', 'An error occurred. Please try again.', 'error');
                    button.prop('disabled', false).html('<i class="fas fa-check"></i> Approve');
                }
            });
        }
    });

    // Search input handler for students
    $(document).on('input keyup', '#search-students', function() {
        const query = $(this).val().toLowerCase().trim();
        if (!query) {
            renderAllStudents(allStudentsList);
            return;
        }
        
        const filtered = allStudentsList.filter(student => {
            const name = (student.name || '').toLowerCase();
            const studentId = (student.student_id || '').toLowerCase();
            const email = (student.email || '').toLowerCase();
            const program = (student.program || '').toLowerCase();
            const yearLevel = (student.year_level || '').toLowerCase();
            const status = (student.status || '').toLowerCase();
            
            return name.includes(query) || 
                   studentId.includes(query) || 
                   email.includes(query) || 
                   program.includes(query) || 
                   yearLevel.includes(query) ||
                   status.includes(query);
        });
        
        renderAllStudents(filtered);
    });

    // Export button handler for students (CSV format)
    $(document).on('click', '#export-students-btn', function(e) {
        e.preventDefault();
        
        const query = $('#search-students').val() || '';
        let listToExport = allStudentsList;
        if (query.trim()) {
            const queryLower = query.toLowerCase().trim();
            listToExport = allStudentsList.filter(student => {
                const name = (student.name || '').toLowerCase();
                const studentId = (student.student_id || '').toLowerCase();
                const email = (student.email || '').toLowerCase();
                const program = (student.program || '').toLowerCase();
                const yearLevel = (student.year_level || '').toLowerCase();
                const status = (student.status || '').toLowerCase();
                
                return name.includes(queryLower) || 
                       studentId.includes(queryLower) || 
                       email.includes(queryLower) || 
                       program.includes(queryLower) || 
                       yearLevel.includes(queryLower) ||
                       status.includes(queryLower);
            });
        }
        
        if (listToExport.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'No Data',
                text: 'No student records to export.',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        
        let csvContent = "data:text/csv;charset=utf-8,";
        
        // Headers
        const headers = ["Student Name", "Student Email", "Student ID", "Year Level", "Program", "Status", "Curriculum", "Enrollment Type"];
        csvContent += headers.join(",") + "\n";
        
        // Rows
        listToExport.forEach(student => {
            const name = (student.name || '').replace(/"/g, '""');
            const email = (student.email || '').replace(/"/g, '""');
            const studentId = (student.student_id || '').replace(/"/g, '""');
            const yearLevel = (student.year_level || '').replace(/"/g, '""');
            const program = (student.program || 'BS Information Technology').replace(/"/g, '""');
            const status = (student.status || 'Pending').replace(/"/g, '""');
            const curriculum = (student.curriculum || '').replace(/"/g, '""');
            const isRegular = (student.is_regular || 'Regular').replace(/"/g, '""');
            
            const row = [
                `"${name}"`,
                `"${email}"`,
                `"${studentId}"`,
                `"${yearLevel}"`,
                `"${program}"`,
                `"${status}"`,
                `"${curriculum}"`,
                `"${isRegular}"`
            ];
            csvContent += row.join(",") + "\n";
        });
        
        // Create download link
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `organization_students_${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        
        // Trigger download
        link.click();
        document.body.removeChild(link);
        
        Swal.fire({
            icon: 'success',
            title: 'Exported!',
            text: `Successfully exported ${listToExport.length} student records to CSV.`,
            timer: 2000,
            showConfirmButton: false
        });
    });

    // View details button handler for students
    $(document).on('click', '.view-student-btn', function() {
        const student = $(this).data('student');
        
        let statusBadgeClass = 'bg-secondary text-white';
        const statusClean = (student.status || '').toLowerCase().trim();
        if (statusClean === 'paid' || statusClean === 'approved' || statusClean === 'enrolled') {
            statusBadgeClass = 'bg-success text-white';
        } else if (statusClean === 'pending') {
            statusBadgeClass = 'bg-warning text-dark';
        } else if (statusClean === 'overdue') {
            statusBadgeClass = 'bg-danger text-white';
        }
        
        Swal.fire({
            title: `<span style="font-family: inherit; font-weight: 700; color: #1e293b;">Student Details</span>`,
            html: `
                <div class="text-start p-2" style="font-family: inherit; color: #334155; line-height: 1.6;">
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px; margin-right: 12px; box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2);">
                            ${student.name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)}
                        </div>
                        <div>
                            <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: #1e293b;">${student.name}</h4>
                            <p style="margin: 0; font-size: 12px; color: #64748b;">${student.email || 'No email provided'}</p>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <small class="text-muted d-block mb-1" style="font-size: 10px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Student ID</small>
                            <span style="font-family: monospace; font-size: 13px; font-weight: 600; color: #1e293b;">${student.student_id}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block mb-1" style="font-size: 10px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Status</small>
                            <span class="badge ${statusBadgeClass} px-2 py-1" style="font-size: 11px; font-weight: 600; border-radius: 4px;">${student.status}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block mb-1" style="font-size: 10px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Year Level</small>
                            <span style="font-size: 13px; font-weight: 600; color: #1e293b;">${student.year_level}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block mb-1" style="font-size: 10px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Program</small>
                            <span style="font-size: 13px; font-weight: 600; color: #1e293b;">${student.program}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block mb-1" style="font-size: 10px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Curriculum</small>
                            <span style="font-size: 13px; color: #1e293b;">${student.curriculum || 'N/A'}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block mb-1" style="font-size: 10px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Enrollment Type</small>
                            <span style="font-size: 13px; color: #1e293b;">${student.is_regular}</span>
                        </div>
                    </div>
                </div>
            `,
            confirmButtonText: 'Close',
            confirmButtonColor: '#3b82f6',
            customClass: {
                popup: 'border-0 rounded-3 shadow-lg',
                confirmButton: 'btn btn-primary px-4 py-2'
            }
        });
    });
});

    // Use data that's already embedded in the page (from PHP)
    function useEmbeddedData() {
        console.log('Using embedded payment data from page');
        
        // Data is already displayed by PHP in the Blade template
        // We just need to attach event handlers
        attachEventHandlers();
        
        // Update counts and totals from the current table data
        updateCountsFromTable();
    }

    // Update counts and totals from the current table data
    function updateCountsFromTable() {
        const yearCards = ['first-year', 'second-year', 'third-year', 'fourth-year'];
        let totalPending = 0;
        let totalAmount = 0;
        
        yearCards.forEach(year => {
            const tbodyId = `${year}-students`;
            const $tbody = $(`#${tbodyId}`);
            
            // Count pending payments in this year
            let pendingCount = 0;
            let yearTotal = 0;
            
            $tbody.find('tr[data-payment-id]').each(function() {
                const status = $(this).find('td:nth-child(5) span').text().trim();
                const amountText = $(this).find('td:nth-child(4)').text().trim();
                
                if (status === 'Pending') {
                    pendingCount++;
                    totalPending++;
                }
                
                // Extract amount from text like "₱120.00"
                const amountMatch = amountText.match(/₱([\d,.]+)/);
                if (amountMatch) {
                    const amount = parseFloat(amountMatch[1].replace(/,/g, ''));
                    if (!isNaN(amount)) {
                        yearTotal += amount;
                        totalAmount += amount;
                    }
                }
            });
            
            // Update badge and total
            $(`#${year}-pending`).text(`${pendingCount} pending`);
            $(`#${year}-total`).text(`₱${yearTotal.toFixed(2)}`);
            
            // Show or hide card based on pending payments count
            const card = $(`#${year}-card`);
            if (pendingCount === 0) {
                card.hide();
            } else {
                card.show();
            }
        });
        
        // Update dashboard stats
        $('#total-pending').text(totalPending);
        $('#total-amount').text('₱' + totalAmount.toFixed(2));
    }

    // Show error message
    function showErrorMessage(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    }

    // Initialize
    console.log('Organization dashboard initialized');
    
    // Load initial data
    loadPaymentData();
    
    // Export year level students to CSV
    $(document).on('click', '.export-btn', function(e) {
        e.preventDefault();
        const yearKey = $(this).data('year'); // e.g. 'first_year'
        const yearMapping = {
            'first_year': 'first-year',
            'second_year': 'second-year',
            'third_year': 'third-year',
            'fourth_year': 'fourth-year'
        };
        const displayYear = yearMapping[yearKey] || yearKey;
        const tbodyId = `${displayYear}-students`;
        const $tbody = $(`#${tbodyId}`);
        
        // Find all rows in this year level table
        const rows = $tbody.find('tr[data-payment-id]');
        if (rows.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'No Data',
                text: 'No student records to export for this year level.',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        
        let csvContent = "data:text/csv;charset=utf-8,";
        
        // Headers
        const headers = ["Student Name", "Student Email", "ID Number", "Contact", "Amount", "Status", "Date Submitted"];
        csvContent += headers.join(",") + "\n";
        
        // Rows
        rows.each(function() {
            const studentName = $(this).find('.student-name').text().trim().replace(/"/g, '""');
            const studentEmail = $(this).find('.student-email').text().trim().replace(/"/g, '""');
            const idNumber = $(this).find('td:nth-child(2)').text().trim().replace(/"/g, '""');
            const contact = $(this).find('td:nth-child(3)').text().trim().replace(/"/g, '""');
            const amount = $(this).find('td:nth-child(4) .fw-bold').text().trim().replace(/"/g, '""') || $(this).find('td:nth-child(4)').text().trim().replace(/"/g, '""');
            const status = $(this).find('td:nth-child(5) span').text().trim().replace(/"/g, '""');
            const dateSubmitted = $(this).find('td:nth-child(6)').text().trim().replace(/"/g, '""');
            
            const row = [
                `"${studentName}"`,
                `"${studentEmail}"`,
                `"${idNumber}"`,
                `"${contact}"`,
                `"${amount}"`,
                `"${status}"`,
                `"${dateSubmitted}"`
            ];
            csvContent += row.join(",") + "\n";
        });
        
        // Create download link
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        
        const formattedYear = yearKey.replace('_', ' ');
        const capitalizedYear = formattedYear.charAt(0).toUpperCase() + formattedYear.slice(1);
        link.setAttribute("download", `${yearKey}_students_pending_${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        
        // Trigger download
        link.click();
        document.body.removeChild(link);
        
        Swal.fire({
            icon: 'success',
            title: 'Exported!',
            text: `Successfully exported ${capitalizedYear} pending student records.`,
            timer: 2000,
            showConfirmButton: false
        });
    });
    
    // Auto-refresh data every 30 seconds
    // setInterval(loadPaymentData, 30000);
});
