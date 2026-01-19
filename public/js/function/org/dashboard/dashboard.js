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
    setupFilterHandlers();
    setupExportHandler();
    initializeGroupedSearch();
    
    loadPaymentData();
    

    // Export all button
    $('#export-all-btn').on('click', function() {
        exportAllPayments();
    });


    // Initialize search for grouped tables
    function initializeGroupedSearch() {
        $('#paymentSearch').off('keyup').on('keyup', function() {
            const searchValue = $(this).val().toLowerCase().trim();
            const searchTerms = searchValue.split(' ').filter(term => term.length > 0);
            
            const $yearSections = $('.year-section');
            
            // Show all if search is empty
            if (!searchValue) {
                $yearSections.show();
                $('.payment-row').show();
                removeHighlights();
                updateGroupedSearchCount($('.payment-row').length, $('.payment-row').length);
                updateYearSectionCounts();
                return;
            }
            
            let totalMatches = 0;
            let totalItems = 0;
            
            // Process each year section
            $yearSections.each(function() {
                const $yearSection = $(this);
                const $rows = $yearSection.find('.payment-row');
                let yearMatches = 0;
                
                totalItems += $rows.length;
                
                $rows.each(function() {
                    const $row = $(this);
                    const searchText = getRowSearchText($row);
                    
                    // Check if all search terms are found
                    const matches = searchTerms.every(term => searchText.indexOf(term) > -1);
                    
                    if (matches) {
                        $row.show();
                        yearMatches++;
                        totalMatches++;
                        
                        // Highlight matching text
                        highlightSearchTermsGrouped($row, searchTerms);
                    } else {
                        $row.hide();
                        removeRowHighlights($row);
                    }
                });
                
                // Update year section visibility
                if (yearMatches > 0) {
                    $yearSection.show();
                    updateYearSectionHeader($yearSection, yearMatches, $rows.length);
                } else {
                    $yearSection.hide();
                }
            });
            
            updateGroupedSearchCount(totalMatches, totalItems);
        });
        
        // Clear search button
        $('#paymentSearch').on('input', function() {
            const $input = $(this);
            if ($input.val()) {
                if (!$input.next('.search-clear').length) {
                    // $input.after(`
                    //     <button class="search-clear btn btn-sm btn-link position-absolute" 
                    //             style="right: 10px; top: 50%; transform: translateY(-50%);">
                    //         <i class="fas fa-times text-muted"></i>
                    //     </button>
                    // `);
                }
            } else {
                $('.search-clear').remove();
            }
        });
        
        // Clear search when clicking X
        $(document).on('click', '.search-clear', function(e) {
            e.preventDefault();
            $('#paymentSearch').val('').trigger('keyup');
            $(this).remove();
        });
    }

    // Helper function to get all searchable text from a row
    function getRowSearchText($row) {
        return [
            $row.data('student-name') || '',
            $row.data('student-id-no') || '',
            $row.data('student-email') || '',
            $row.data('student-contact') || '',
            $row.find('.amount .fw-bold').text().toLowerCase(),
            $row.find('td:nth-child(6)').text().toLowerCase()
        ].join(' ');
    }

    // Highlight search terms in grouped tables
    function highlightSearchTermsGrouped($row, searchTerms) {
        removeRowHighlights($row);
        
        searchTerms.forEach(term => {
            if (term.length < 2) return;
            
            const regex = new RegExp(`(${escapeRegExp(term)})`, 'gi');
            
            // Highlight in each cell except action buttons
            $row.find('td:not(:last-child)').each(function() {
                const $cell = $(this);
                const cellText = $cell.text(); // Get plain text for searching
                
                // Check if this cell contains the search term
                if (regex.test(cellText)) {
                    // For cells with complex HTML structure, we need to highlight specific elements
                    
                    // 1. Student info cell (name and email)
                    if ($cell.find('.student-info').length) {
                        const $name = $cell.find('.student-name');
                        const $email = $cell.find('.student-email');
                        
                        if ($name.length) {
                            const nameText = $name.text();
                            const highlightedName = nameText.replace(regex, '<span class="highlight">$1</span>');
                            if (highlightedName !== nameText) {
                                $name.html(highlightedName);
                            }
                        }
                        
                        if ($email.length) {
                            const emailText = $email.text();
                            const highlightedEmail = emailText.replace(regex, '<span class="highlight">$1</span>');
                            if (highlightedEmail !== emailText) {
                                $email.html(highlightedEmail);
                            }
                        }
                    }
                    // 2. Student ID cell
                    else if ($cell.find('.student-id').length) {
                        const $studentId = $cell.find('.student-id');
                        const idText = $studentId.text();
                        const highlightedId = idText.replace(regex, '<span class="highlight">$1</span>');
                        if (highlightedId !== idText) {
                            $studentId.html(highlightedId);
                        }
                    }
                    // 3. Amount cell
                    else if ($cell.find('.amount').length) {
                        const $amountSpan = $cell.find('.amount .fw-bold');
                        if ($amountSpan.length) {
                            const amountText = $amountSpan.text();
                            const highlightedAmount = amountText.replace(regex, '<span class="highlight">$1</span>');
                            if (highlightedAmount !== amountText) {
                                $amountSpan.html(highlightedAmount);
                            }
                        }
                    }
                    // 4. Regular text cells (contact, date, etc.)
                    else {
                        // For simple text cells, we need to preserve any existing HTML structure
                        const textNodes = [];
                        $cell.contents().each(function() {
                            if (this.nodeType === 3) { // Text node
                                textNodes.push(this);
                            }
                        });
                        
                        textNodes.forEach(textNode => {
                            const text = textNode.nodeValue;
                            const highlightedText = text.replace(regex, '<span class="highlight">$1</span>');
                            if (highlightedText !== text) {
                                $(textNode).replaceWith(highlightedText);
                            }
                        });
                    }
                }
            });
        });
    }

    // Remove highlights from a specific row
    function removeRowHighlights($row) {
        $row.find('.highlight').each(function() {
            $(this).replaceWith($(this).text());
        });
    }

    // Remove all highlights
    function removeHighlights() {
        $('.highlight').each(function() {
            $(this).replaceWith($(this).text());
        });
    }

    // Update year section header during search
    function updateYearSectionHeader($yearSection, matches, total) {
        const $header = $yearSection.find('.year-section-header');
        const yearDisplay = $header.find('h4').contents().first().text().trim();
        const $badge = $header.find('.badge');
        
        const searchValue = $('#paymentSearch').val();
        
        if (searchValue) {
            $badge.text(`${matches} of ${total} matched`);
            $badge.removeClass().addClass('badge bg-info');
        } else {
            const yearKey = $yearSection.data('year-level');
            const yearData = getYearDataFromBadge($badge.text());
            $badge.text(`${total} pending`);
            $badge.removeClass().addClass(`badge year-badge-${getYearClassFromKey(yearKey)}`);
        }
    }

    // Update all year section counts
    function updateYearSectionCounts() {
        $('.year-section').each(function() {
            const $yearSection = $(this);
            const $rows = $yearSection.find('.payment-row');
            const $badge = $yearSection.find('.year-section-header .badge');
            const yearKey = $yearSection.data('year-level');
            
            $badge.text(`${$rows.length} pending`);
            $badge.removeClass().addClass(`badge year-badge-${getYearClassFromKey(yearKey)}`);
        });
    }

    // Helper function to get year class from key
    function getYearClassFromKey(yearKey) {
        const mapping = {
            'first_year': '1st',
            'second_year': '2nd',
            'third_year': '3rd',
            'fourth_year': '4th'
        };
        return mapping[yearKey] || '1st';
    }

    // Helper to extract count from badge text
    function getYearDataFromBadge(badgeText) {
        const match = badgeText.match(/(\d+)/);
        return match ? parseInt(match[1]) : 0;
    }

    // Update search count for grouped view
    function updateGroupedSearchCount(matches, total) {
        const $searchCount = $('#search-count');
        const searchValue = $('#paymentSearch').val();
        
        if (!searchValue) {
            $searchCount.text(`Showing ${total} payments`).removeClass('text-danger');
        } else if (matches === 0) {
            $searchCount.text(`No results found for "${searchValue}"`).addClass('text-danger');
        } else {
            $searchCount.text(`Found ${matches} of ${total} payments for "${searchValue}"`).removeClass('text-danger');
        }
    }

    // Escape regex special characters
    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // Load payment data
    function loadPaymentData() {
        console.log('Loading payment data grouped by year...');
        
        $.ajax({
            url: '/org/payments/refresh',
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Response received:', response);
                
                if (response.success) {
                    displayAllPaymentsGroupedByYear(response.data);
                    updateDashboardStats(response.data);
                    // Reset search after loading new data
                    $('#paymentSearch').val('').trigger('keyup');
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
                
                // Try to use embedded data if available
                useEmbeddedData();
                // Reset search
                $('#paymentSearch').val('').trigger('keyup');
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

    // Display all payments grouped by year level
    function displayAllPaymentsGroupedByYear(data) {
        const $container = $('#year-sections-container');
        const $noPaymentsMsg = $('#no-payments-message');
        
        $container.empty(); // Clear existing content
        
        // Define year levels and their display properties
        const yearLevels = [
            { key: 'first_year', display: '1st Year', badgeClass: 'year-badge-1st', order: 1 },
            { key: 'second_year', display: '2nd Year', badgeClass: 'year-badge-2nd', order: 2 },
            { key: 'third_year', display: '3rd Year', badgeClass: 'year-badge-3rd', order: 3 },
            { key: 'fourth_year', display: '4th Year', badgeClass: 'year-badge-4th', order: 4 }
        ];
        
        let totalPayments = 0;
        let totalAmount = 0;
        let hasAnyPayments = false;
        
        // Sort year levels by order
        yearLevels.sort((a, b) => a.order - b.order);
        
        // Process each year level
        yearLevels.forEach(year => {
            const yearData = data[year.key] || { payments: [], total: 0, pending_count: 0 };
            
            if (yearData.payments && yearData.payments.length > 0) {
                hasAnyPayments = true;
                totalPayments += yearData.payments.length;
                totalAmount += yearData.total || 0;
                
                // Create year section
                const yearSectionId = `year-section-${year.key.replace('_', '-')}`;
                const $yearSection = $(`
                    <div class="year-section" id="${yearSectionId}" data-year-level="${year.key}">
                        <div class="year-section-header d-flex justify-content-between align-items-center">
                            <h4>
                                ${year.display}
                                <span class="badge ${year.badgeClass}">${yearData.payments.length} pending</span>
                            </h4>
                        </div>
                        <div style="white-space: nowrap;" class="table-responsive table-section">
                            <table class="table table-hover mb-0 year-table" data-year="${year.key}">
                                <thead class="table-light">
                                    <tr>
                                        <th width="25%">Student</th>
                                        <th>ID Number</th>
                                        <th>Contact</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date Submitted</th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="${year.key}-payments-body">
                                    <!-- Payments will be inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                `);
                
                $container.append($yearSection);
                
                // Populate table rows for this year level
                const $tbody = $(`#${year.key}-payments-body`);
                
                // Sort payments by date (most recent first)
                yearData.payments.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                
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
                        <tr data-payment-id="${payment.id}" 
                            data-student-id="${payment.student_id}"
                            data-year-level="${year.key}"
                            data-student-name="${fullName.toLowerCase()}"
                            data-student-id-no="${payment.id_no || ''}"
                            data-student-email="${payment.email || ''}"
                            data-student-contact="${contact.toLowerCase()}"
                            class="${rowClass} payment-row">
                            <td>
                                <div class="student-info d-flex align-items-center">
                                    <div class="avatar me-3">
                                        ${isPaymentNotice ? 
                                            '' : 
                                            `<span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                ${avatarInitial}
                                            </span>`
                                        }
                                    </div>
                                    <div>
                                        <div class="student-name fw-bold">${fullName || 'N/A'}</div>
                                        <div class="student-email small text-muted">${payment.email || 'No email'}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="student-id">${payment.id_no || 'N/A'}</div>
                            </td>
                            <td>${contact}</td>
                            <td class="amount">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">${amount}</span>
                                    ${isPaymentNotice ? 
                                        '<small class="text-success"><i class="fas fa-info-circle me-1"></i>Payment notice</small>' : ''}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">Pending</span>
                            </td>
                            <td>${formattedDate}</td>
                            <td>
                                <div class="action-buttons d-flex gap-2">
                                    <button class="btn btn-sm btn-success approve-payment" 
                                            data-payment-id="${payment.id}" 
                                            data-student-id="${payment.student_id}"
                                            title="Approve Payment">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    ${payment.file_path ? 
                                        `<a href="/documents/${payment.file_path.replace('documents/', '')}" 
                                        target="_blank"
                                        class="btn btn-sm btn-outline-primary view-receipt-btn"
                                        title="View Receipt">
                                            <i class="fas fa-eye"></i> View
                                        </a>` : ''
                                    }
                                </div>
                            </td>
                        </tr>
                    `);
                });
            }
        });
        
        // Show/hide no payments message
        if (hasAnyPayments) {
            $noPaymentsMsg.hide();
        } else {
            $noPaymentsMsg.show();
        }
        
        // Update summary
        updatePaymentSummary(totalPayments, totalAmount);
        
        // Initialize search
        initializeGroupedSearch();
        
        // Attach event handlers
        attachEventHandlers();
    }

    function updatePaymentSummary(count, totalAmount) {
        $('#total-pending-amount').text(`₱${totalAmount.toFixed(2)}`);
        $('#payment-count').text(`(${count} payment${count !== 1 ? 's' : ''})`);
    }

    // -------
    $(document).on('click', '.approve-payment', function(e) {
        e.preventDefault();
        
        const paymentId = $(this).data('payment-id');
        const studentId = $(this).data('student-id');
        const button = $(this);
        const row = button.closest('tr');
        const $yearSection = row.closest('.year-section');
        
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
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                
                const csrfToken = $('meta[name="csrf-token"]').attr('content');
                
                $.ajax({
                    url: '/org/approve-payment',
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
                                // Update row
                                row.find('td:nth-child(5)').html('<span class="badge bg-success">Approved</span>');
                                row.find('td:nth-child(7)').html('<span class="text-muted small">Processed</span>');
                                
                                // Remove row after delay
                                setTimeout(() => {
                                    row.fadeOut(300, function() {
                                        $(this).remove();
                                        updatePaymentSummaryAfterApprovalGrouped($yearSection);
                                    });
                                }, 1000);
                                
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
    });

    // Payment summary after approval for grouped view
    function updatePaymentSummaryAfterApprovalGrouped($yearSection) {
        // Update year section count
        const $rows = $yearSection.find('.payment-row');
        const $badge = $yearSection.find('.year-section-header .badge');
        const yearKey = $yearSection.data('year-level');
        
        $badge.text(`${$rows.length} pending`);
        $badge.removeClass().addClass(`badge year-badge-${getYearClassFromKey(yearKey)}`);
        
        // Update overall summary
        let totalPayments = 0;
        let totalAmount = 0;
        
        $('.payment-row').each(function() {
            totalPayments++;
            const amountText = $(this).find('.amount .fw-bold').text();
            const amountMatch = amountText.match(/₱([\d,.]+)/);
            if (amountMatch) {
                const amount = parseFloat(amountMatch[1].replace(/,/g, ''));
                if (!isNaN(amount)) {
                    totalAmount += amount;
                }
            }
        });
        
        updatePaymentSummary(totalPayments, totalAmount);
    }

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
                const status = $(this).find('td:nth-child(5) .badge').text().trim();
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
    
    // Auto-refresh data every 30 seconds
    // setInterval(loadPaymentData, 30000);
});

// Export all payments function
function exportAllPayments() {
    const payments = [];
    
    // Loop through each year section
    $('.year-section').each(function() {
        const $yearSection = $(this);
        const yearLevel = $yearSection.find('.year-section-header h4').text().trim().split(' ')[0] + ' Year';
        
        // Loop through each row in this year section
        $yearSection.find('.payment-row').each(function() {
            const $row = $(this);
            
            // Get data from the correct elements
            const studentName = $row.find('.student-name').text().trim();
            const studentId = $row.find('.student-id').text().trim();
            const email = $row.find('.student-email').text().trim();
            const contact = $row.find('td:nth-child(3)').text().trim();
            
            // Get amount - remove the peso sign
            const amountText = $row.find('.amount .fw-bold').text().trim();
            const amount = amountText.replace('₱', '').replace(/\s/g, '').trim();
            
            // Get status
            const status = $row.find('td:nth-child(5) .badge').text().trim() || 'Pending';
            
            // Get date - from the 6th column
            const date = $row.find('td:nth-child(6)').text().trim();
            
            // Get payment type
            const paymentType = $row.find('.amount .text-success').text().trim() ? 
                'Payment Notice' : 'Receipt';
            
            payments.push({
                name: studentName,
                id: studentId,
                email: email,
                contact: contact,
                yearLevel: yearLevel,
                amount: amount,
                status: status,
                date: date,
                paymentType: paymentType
            });
        });
    });
    
    if (payments.length === 0) {
        Swal.fire('No Data', 'There are no payments to export.', 'info');
        return;
    }
    
    // Create CSV
    let csvContent = "data:text/csv;charset=utf-8,\uFEFF"; // Add BOM for Excel compatibility
    
    // Add headers
    const headers = ["Student Name", "ID Number", "Email", "Contact", "Year Level", "Amount", "Status", "Date Submitted", "Payment Type"];
    csvContent += headers.join(",") + "\r\n";
    
    // Add data rows
    payments.forEach(payment => {
        const row = [
            escapeCSV(payment.name),
            escapeCSV(payment.id),
            escapeCSV(payment.email),
            escapeCSV(payment.contact),
            escapeCSV(payment.yearLevel),
            payment.amount,
            escapeCSV(payment.status),
            escapeCSV(payment.date),
            escapeCSV(payment.paymentType)
        ];
        csvContent += row.join(",") + "\r\n";
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
    
    Swal.fire({
        icon: 'success',
        title: 'Export Started',
        text: 'Your CSV file download should begin shortly.',
        timer: 2000,
        showConfirmButton: false
    });
}

// Helper function to escape CSV special characters
function escapeCSV(text) {
    if (text === null || text === undefined) return '';
    text = String(text);
    
    // If text contains commas, quotes, or newlines, wrap in quotes
    if (text.includes(',') || text.includes('"') || text.includes('\n') || text.includes('\r')) {
        text = text.replace(/"/g, '""'); // Escape double quotes
        text = `"${text}"`;
    }
    
    // Replace any remaining line breaks
    text = text.replace(/(\r\n|\n|\r)/gm, ' ');
    
    return text;
}

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
                    yearLevelSections.style.opacity = '1';
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