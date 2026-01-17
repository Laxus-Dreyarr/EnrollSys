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
                    // initializeEnrollmentRequests();
                    count_pending();
                    pending_request();
                    count_students();
                } else if (payload.new.operation === 'UPDATE') {
                    count_pending();
                } else if (payload.new.operation == 'NOTIF') {
                    updateNotificationCount();
                } else if (payload.new.operation === 'UPDATE2') {
                    loadEnrollmentRequests();
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
        operation: 'DELETE'
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


async function insertsupabase2(){
    const data = {
        table_name: 'status',  // make sure these variables are defined
        operation: 'NOTIF'
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

const baseUrl = window.location.origin;

// Helper function for status classes (keep existing)
function getStatusClass(status) {
    switch(status?.toLowerCase()) {
        case 'approved': return 'status-approved';
        case 'rejected': return 'status-rejected';
        case 'pending': return 'status-pending';
        default: return 'status-unknown';
    }
}

// function getStatusClass(status) {
//     if (!status) return 'badge-secondary';
    
//     switch(status.toLowerCase()) {
//         case 'approved':
//             return 'badge-success';
//         case 'rejected':
//             return 'badge-danger';
//         case 'pending':
//             return 'badge-warning';
//         default:
//             return 'badge-secondary';
//     }
// }


// Enrollment Request
// Enrollment Requests functionality
function initializeEnrollmentRequests() {
    console.log('Initializing Enrollment Requests section...');
    loadEnrollmentRequests();
    
    // Set up event listeners for accept/reject buttons
    setupEnrollmentRequestListeners();
}

function loadEnrollmentRequests() {
    const requestsContainer = document.getElementById('enrollment-requests-container');
    const loadingContainer = document.getElementById('enrollment-requests-loading');
    const noRequestsContainer = document.getElementById('no-enrollment-requests');
    
    if (!requestsContainer) return;
    
    showEnrollmentRequestsLoading(true);
    
    fetch('/instructor/enrollment-requests', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Enrollment requests data received:', data);
        displayEnrollmentRequests(data.requests || []);
        showEnrollmentRequestsLoading(false);
    })
    .catch(error => {
        console.error('Error loading enrollment requests:', error);
        showEnrollmentRequestsLoading(false);
        showNotification('Error loading enrollment requests: ' + error.message, 'error');
        displayEnrollmentRequests([]);
    });
}

function displayEnrollmentRequests(requests) {
    const requestsContainer = document.getElementById('enrollment-requests-container');
    const noRequestsContainer = document.getElementById('no-enrollment-requests');
    const requestsList = document.getElementById('enrollment-requests-list');
    const requestDetails = document.getElementById('enrollment-request-details');
    
    if (!requestsContainer || !requestsList) return;
    
    requestsList.innerHTML = '';
    requestDetails.innerHTML = '';
    
    if (!requests || requests.length === 0) {
        noRequestsContainer.style.display = 'block';
        requestsContainer.style.display = 'none';
        return;
    }
    
    noRequestsContainer.style.display = 'none';
    requestsContainer.style.display = 'grid';
    
    requests.forEach(request => {
        const requestItem = createRequestItem(request);
        requestsList.appendChild(requestItem);
    });
}

// Search student enrollment request!
$('#studentSearch').on('keyup', function() {
    const searchValue = $(this).val().toLowerCase().trim();
    const searchTerms = searchValue.split(' ').filter(term => term.length > 0);
    
    // Show all if search is empty
    if (!searchValue) {
        $('.enrollment-request-item').show();
        updateSearchCount($('.enrollment-request-item').length, $('.enrollment-request-item').length);
        return;
    }
    
    let matchCount = 0;
    const totalItems = $('.enrollment-request-item').length;
    
    $('.enrollment-request-item').each(function() {
        const $item = $(this);
        const itemText = $item.text().toLowerCase();
        
        // Check if all search terms are found in the item
        const matches = searchTerms.every(term => itemText.indexOf(term) > -1);
        
        if (matches) {
            $item.show();
            matchCount++;
            
            // Optional: Highlight matching text
            highlightSearchTerms($item, searchTerms);
        } else {
            $item.hide();
            // Remove any existing highlights
            $item.find('.highlight').each(function() {
                $(this).replaceWith($(this).text());
            });
        }
    });
    
    updateSearchCount(matchCount, totalItems);
});

// Optional: Function to highlight search terms
function highlightSearchTerms($item, searchTerms) {
    searchTerms.forEach(term => {
        $item.find('.student-name, .student-id').each(function() {
            const $element = $(this);
            const originalText = $element.text();
            const highlightedText = originalText.replace(
                new RegExp(`(${term})`, 'gi'),
                '<span class="highlight bg-warning">$1</span>'
            );
            $element.html(highlightedText);
        });
    });
}

function createRequestItem(request) {
    const item = document.createElement('div');
    item.className = 'enrollment-request-item';
    item.dataset.requestId = request.request_id;
    item.dataset.studentId = request.student_id;
    
    
    const studentType = request.is_regular === '1' ? 'Regular' : 'Irregular';
    const subjectsCount = request.enrolled_subjects_count || 0;


    // Helper function to determine avatar HTML
    function getAvatarHtml(request) {
        if (request.profile && request.profile !== 'default.png') {
            return `<img src="/profile2-image/${request.id}" alt="Student Avatar" class="request-avatar" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">`;
        } else {
            const firstName = request.firstname || '';
            const lastName = request.lastname || '';
            const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(firstName + ' ' + lastName)}&background=none&color=fff`;
            
            return `
                
                    <img src="${avatarUrl}" alt="User Avatar" class="user-avatar">
                    <div class="status-indicator"></div>
                
            `;
        }
    }

    let regularityText;
    if (request.is_regular == '1') {
        regularityText = 'Regular';
    } else {
        regularityText = 'Irregular';
    }

    item.innerHTML = `
        <div class="request-item-header">
            ${getAvatarHtml(request)}
            <div class="request-student-info">
                <h4 class="student-name">${request.firstname} ${request.middlename || ''} ${request.lastname}</h4>
                <p class="student-id">ID: ${request.id_no}</p>
            </div>
            <div class="request-status-badge pending">Pending</div>
        </div>
        <div class="request-item-details">
            <div class="request-meta">
                <span class="meta-item">
                    <i class="fas fa-calendar"></i>
                    ${request.request_year_level}
                </span>
                <span class="meta-item">
                    <i class="fas fa-book"></i>
                    ${regularityText}
                </span>
                <span class="meta-item">
                    <i class="fas fa-graduation-cap"></i>
                    ${subjectsCount} Subjects
                </span>
            </div>
            <div class="request-date">
                Requested: ${new Date(request.request_date).toLocaleDateString()}
            </div>
        </div>
    `;
    
    item.addEventListener('click', function() {
        // Remove active class from all items
        document.querySelectorAll('.enrollment-request-item').forEach(i => {
            i.classList.remove('active');
        });
        
        // Add active class to clicked item
        this.classList.add('active');
        
        // Load request details
        loadRequestDetails(request);
    });
    
    return item;
}

// Realtime 
function updatePaymentReceiptStatus(studentId, newStatus) {
    // Check if the currently displayed request details are for this student
    const requestDetails = document.getElementById('enrollment-request-details');
    if (!requestDetails) return;
    
    // Get the student ID from the currently displayed request
    const currentStudentId = requestDetails.querySelector('.btn-view-history')?.getAttribute('data-student-id');
    
    // If the currently displayed request matches the updated student
    if (currentStudentId && currentStudentId == studentId) {
        // Find the payment receipt status element
        const paymentReceiptElement = requestDetails.querySelector('.document-item:nth-child(3) .status-badge');
        
        if (paymentReceiptElement) {
            // Update the status text
            paymentReceiptElement.textContent = newStatus;
            
            // Update the CSS class based on status
            const statusClass = getStatusClass(newStatus);
            paymentReceiptElement.className = `status-badge ${statusClass}`;
            
            console.log(`Updated payment receipt status for student ${studentId} to: ${newStatus}`);
        }
    }
    
    // Also update the search results if this student is in the list
    updateSearchResultPaymentStatus(studentId, newStatus);
}

// Helper function to update status in search results
function updateSearchResultPaymentStatus(studentId, newStatus) {
    const requestItems = document.querySelectorAll('.enrollment-request-item');
    
    requestItems.forEach(item => {
        if (item.getAttribute('data-student-id') == studentId) {
            // If you're displaying payment status in the list item, update it here
            // For example, if you add a payment status indicator in the list
            const paymentStatusElement = item.querySelector('.payment-status');
            if (paymentStatusElement) {
                paymentStatusElement.textContent = newStatus;
                paymentStatusElement.className = `payment-status ${getStatusClass(newStatus)}`;
            }
        }
    });
}

// Your existing helper function (make sure it's accessible)
function getStatusClass(status) {
    if (!status) return 'missing';
    
    switch(status.toLowerCase()) {
        case 'approved': return 'approved';
        case 'rejected': return 'rejected';
        case 'pending': return 'pending';
        default: return 'missing';
    }
}


function loadRequestDetails(request) {
    const requestDetails = document.getElementById('enrollment-request-details');
    if (!requestDetails) return;
    
    const avatarUrl = request.profile;
    const studentType = request.is_regular === '1' ? 'Regular' : 'Irrigular';
    
    let subjectsHTML = '';
    if (request.subjects && request.subjects.length > 0) {
        request.subjects.forEach(subject => {
            // Determine CSS class based on prerequisites status
            let subjectClass = 'subject-enrollment-item';
            let prerequisiteWarning = '';
            
            if (subject.has_prerequisites) {
                if (subject.all_prerequisites_passed) {
                    subjectClass += ' prerequisites-passed';
                } else {
                    subjectClass += ' prerequisites-failed';
                    prerequisiteWarning = '<div class="prerequisite-warning">⚠️ Missing/Unpassed Prerequisites</div>';
                }
            }
            
            // Build prerequisites HTML
            let prerequisitesHTML = '';
            if (subject.prerequisites && subject.prerequisites.length > 0) {
                prerequisitesHTML = `
                    
                `;
            } else {
                prerequisitesHTML = `
                    

                `;
            }
            
            subjectsHTML += `
                <div class="${subjectClass}">
                    <div class="subject-header">
                        <div class="subject-code" style="background: green; color: white; padding: 5px; border-radius: 10px;">${subject.subject_code}</div>
                        <div class="subject-meta">
        
                            ${subject.units} units • ${subject.semester}
                        </div>
                    </div>
                    <div class="subject-name">${subject.subject_name}</div>
                    <div class="subject-year">${subject.year_level}</div>
                    ${prerequisiteWarning}
                    ${prerequisitesHTML}
                </div>
            `;
        });
    } else {
        subjectsHTML = '<div class="no-subjects">No subjects found for enrollment</div>';
    }
    
    let documentsHTML = '';
    
    // let documentsHTML = generateDocumentsHTML(request);
    
    // FHE Document Section - UPDATED
    if (request.fhe_document && request.fhe_document.web_path) {
        const fhe = request.fhe_document;
        const fheStatusClass = getStatusClass(fhe.status);
        const fheStatusText = fhe.status || 'Pending';
        
        documentsHTML += `
            <div class="document-item">
                <div class="document-header">
                    <div class="document-title">
                        <i class="fas fa-file-pdf"></i>
                        <span>FHE Document</span>
                    </div>
                </div>
                <a class="btn-view-document" href="${fhe.web_path}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye"></i> View FHE
                </a>
            </div>
        `;
    } else {
        documentsHTML += `
            <div class="document-item">
                <div class="document-header">
                    <div class="document-title">
                        <i class="fas fa-file-pdf"></i>
                        <span>FHE Document</span>
                    </div>
                    <div class="document-status">
                        <span class="status-badge missing">Not Submitted</span>
                    </div>
                </div>
            </div>
        `;
    }

    // PROSPECTUS Document Section - UPDATED
    if (request.prospectus && request.prospectus.web_path) {
        const prospectus = request.prospectus;
        const prospectusClass = getStatusClass(prospectus.status);
        const prospectusText = prospectus.status || 'Pending';
        
        documentsHTML += `
            <div class="document-item">
                <div class="document-header">
                    <div class="document-title">
                        <i class="fas fa-file-pdf"></i>
                        <span>Prospectus Document</span>
                    </div>
                </div>
                <a class="btn-view-document" href="${prospectus.web_path}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye"></i> View Prospectus
                </a>
            </div>
        `;
    } else {
        documentsHTML += `
            <div class="document-item">
                <div class="document-header">
                    <div class="document-title">
                        <i class="fas fa-file-pdf"></i>
                        <span>prospectus Document</span>
                    </div>
                    <div class="document-status">
                        <span class="status-badge missing">Not Submitted</span>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Payment Receipt Section - UPDATED
    if (request.payment_receipt && request.payment_receipt.web_path) {
        const receipt = request.payment_receipt;
        const receiptStatusClass = getStatusClass(receipt.status);
        const receiptStatusText = receipt.status || 'Pending';
        
        documentsHTML += `
            <div class="document-item">
                <div class="document-header">
                    <div class="document-title">
                        <i class="fas fa-receipt"></i>
                        <span>Payment Receipt</span>
                    </div>
                    <div class="document-status">
                        <span class="status-badge ${receiptStatusClass}">${receiptStatusText}</span>
                    </div>
                </div>
                <a class="btn-view-document" href="${receipt.web_path}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye"></i> View Receipt
                </a>
            </div>
        `;
    } else {
        documentsHTML += `
            <div class="document-item">
                <div class="document-header">
                    <div class="document-title">
                        <i class="fas fa-receipt"></i>
                        <span>Payment Receipt</span>
                    </div>
                    <div class="document-status">
                        <span class="status-badge missing">Not Submitted</span>
                    </div>
                </div>
            </div>
        `;
    }
    
    // If you want to show "No documents submitted" only when both are missing:
    if (!request.fhe_document && !request.payment_receipt) {
        documentsHTML = '<div class="no-documents">No documents submitted</div>';
    }

    // Helper function to determine avatar HTML
    function getAvatarHtml2(request) {
        if (request.profile && request.profile !== 'default.png') {
            return `<img src="/profile2-image/${request.id}" alt="Student Avatar" class="request-avatar" style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover;">`;
        } else {
            const firstName = request.firstname || '';
            const lastName = request.lastname || '';
            const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(firstName + ' ' + lastName)}&background=none&color=fff`;
            
            return `
                    <img src="${avatarUrl}" alt="User Avatar" class="user-avatar" style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover;">
            `;
        }
    }
    
    requestDetails.innerHTML = `
        <div class="request-details-header">
            ${getAvatarHtml2(request)}
            <div class="details-student-info">
                <h3>${request.firstname} ${request.middlename || ''} ${request.lastname}</h3>
                <p class="student-id">ID: ${request.id_no}</p>
                <div class="student-meta">
                    <span class="meta-badge year-level">${request.year_level}</span>
                    <span class="meta-badge student-type ${studentType.toLowerCase()}">${studentType}</span>
                    <span class="meta-badge curriculum">${request.curriculum} Curriculum</span>
                </div>
                <div class="header-actions2">
                    <button class="btn-view-history" data-student-id="${request.student_id}">
                        <i class="fas fa-history"></i> View Subjects History
                    </button>
                </div>
            </div>
        </div>
        
        <div class="request-details-content">
            <div class="details-section">
                <h4>
                    <i class="fas fa-book-open"></i>
                    Subjects for Enrollment
                    <span class="badge">${request.subjects ? request.subjects.length : 0}</span>
                </h4>
                <div class="subjects-enrollment-list">
                    ${subjectsHTML}
                </div>
            </div>
            
            <div class="details-section">
                <h4>
                    <i class="fas fa-file"></i>
                    Submitted Documents
                </h4>
                <div class="documents-list">
                    ${documentsHTML}
                </div>
            </div>
            
            <div class="request-actions">
                <button class="btn-accept" data-request-id="${request.request_id}" data-student-id="${request.student_id}">
                    <i class="fas fa-check"></i> Accept Enrollment
                </button>
                <button class="btn-reject" data-request-id="${request.request_id}" data-student-id="${request.student_id}">
                    <i class="fas fa-times"></i> Reject Enrollment
                </button>
            </div>
        </div>
    `;
    
    // Add event listeners to action buttons
    setupRequestActionButtons();
    

    // Add event listener for View Subjects History button
    document.querySelector('.btn-view-history').addEventListener('click', function() {
        const studentId = this.getAttribute('data-student-id');
        showSubjectsHistoryModal(studentId, `${request.firstname} ${request.lastname}`, request.id_no);
    });
    
}


// Separate function to generate documents HTML
// function generateDocumentsHTML(request) {
//     let documentsHTML = '';
    
//     // FHE Document Section
//     if (request.fhe_document && request.fhe_document.web_path) {
//         const fhe = request.fhe_document;
//         const fheStatusClass = getStatusClass(fhe.status);
//         const fheStatusText = fhe.status || 'Pending';
        
//         documentsHTML += `
            
//                     <div class="document-status">
//                         <span class="status-badge ${fheStatusClass}">${fheStatusText}</span>
//                     </div>
               
//         `;
//     } else {
//         documentsHTML += `
//             <div class="document-item">
//                 <div class="document-header">
//                     <div class="document-title">
//                         <i class="fas fa-file-pdf"></i>
//                         <span>FHE Document</span>
//                     </div>
//                     <div class="document-status">
//                         <span class="status-badge missing">Not Submitted</span>
//                     </div>
//                 </div>
//             </div>
//         `;
//     }
    
//     // Payment Receipt Section
//     if (request.payment_receipt && request.payment_receipt.web_path) {
//         const receipt = request.payment_receipt;
//         const receiptStatusClass = getStatusClass(receipt.status);
//         const receiptStatusText = receipt.status || 'Pending';
        
//         documentsHTML += `
//                     <div class="document-status">
//                         <span class="status-badge ${receiptStatusClass}">${receiptStatusText}</span>
//                     </div>
//         `;
//     } else {
//         documentsHTML += `
//             <div class="document-item">
//                 <div class="document-header">
//                     <div class="document-title">
//                         <i class="fas fa-receipt"></i>
//                         <span>Payment Receipt</span>
//                     </div>
//                     <div class="document-status">
//                         <span class="status-badge missing">Not Submitted</span>
//                     </div>
//                 </div>
//             </div>
//         `;
//     }
    
//     // If you want to show "No documents submitted" only when both are missing:
//     if (!request.fhe_document && !request.payment_receipt) {
//         documentsHTML = '<div class="no-documents">No documents submitted</div>';
//     }
    
//     return documentsHTML;
// }


// function refreshDocumentsSection(request) {
//     const documentsContainer = document.querySelector('.documents-list');
//     if (documentsContainer) {
//         documentsContainer.innerHTML = generateDocumentsHTML(request);
//     }
// }


function setupRequestActionButtons() {
    const acceptBtn = document.querySelector('.btn-accept');
    const rejectBtn = document.querySelector('.btn-reject');
    
    if (acceptBtn) {
        acceptBtn.addEventListener('click', handleAcceptEnrollment);
    }
    
    if (rejectBtn) {
        rejectBtn.addEventListener('click', handleRejectEnrollment);
    }
}

function handleAcceptEnrollment(e) {
    const requestId = e.target.dataset.requestId;
    const studentId = e.target.dataset.studentId;
    
    if (!requestId || !studentId) {
        showNotification('Invalid request data', 'error');
        return;
    }
    
    if (!confirm('Are you sure you want to approve this enrollment request?')) {
        return;
    }
    
    const btn = e.target;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Approving...';
    btn.disabled = true;
    
    fetch(window.laravelRoutes.approveEnrollment, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            request_id: requestId,
            student_id: studentId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            insertsupabase();
            showNotification('Enrollment approved successfully! Student is now officially enrolled.', 'success');
            // Reload the requests list
            loadEnrollmentRequests();
        } else {
            showNotification(data.message || 'Error approving enrollment', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error approving enrollment:', error);
        showNotification('Error approving enrollment: ' + error.message, 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function handleRejectEnrollment(e) {
    const requestId = e.target.dataset.requestId;
    const studentId = e.target.dataset.studentId;
    
    if (!requestId || !studentId) {
        showNotification('Invalid request data', 'error');
        return;
    }
    
    const rejectionReason = prompt('Please enter the reason for rejection:');
    
    if (rejectionReason === null) {
        return; // User cancelled
    }
    
    if (!rejectionReason.trim()) {
        showNotification('Rejection reason is required', 'error');
        return;
    }
    
    const btn = e.target;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Rejecting...';
    btn.disabled = true;
    
    fetch(window.laravelRoutes.rejectEnrollment, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            request_id: requestId,
            student_id: studentId,
            rejection_reason: rejectionReason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            insertsupabase();
            showNotification('Enrollment rejected successfully!', 'success');
            // Reload the requests list
            loadEnrollmentRequests();
        } else {
            showNotification(data.message || 'Error rejecting enrollment', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error rejecting enrollment:', error);
        showNotification('Error rejecting enrollment: ' + error.message, 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function showEnrollmentRequestsLoading(show) {
    const loadingContainer = document.getElementById('enrollment-requests-loading');
    const requestsContainer = document.getElementById('enrollment-requests-container');
    const noRequestsContainer = document.getElementById('no-enrollment-requests');
    
    if (show) {
        loadingContainer.style.display = 'block';
        if (requestsContainer) requestsContainer.style.display = 'none';
        noRequestsContainer.style.display = 'none';
    } else {
        loadingContainer.style.display = 'none';
    }
}

function setupEnrollmentRequestListeners() {
    // This function can be used to set up any additional event listeners
    console.log('Enrollment request listeners setup');
}
// End of Enrollment Request

// Enhanced Dark Mode and Theme Management with Smooth Transitions
function initializeDarkMode() {
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    let themeColorOptions = document.querySelectorAll('.color-option');
    
    // Load saved theme preferences
    const savedTheme = localStorage.getItem('theme') || 
                      (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    const savedColor = localStorage.getItem('theme-color') || '#4361ee';
    
    console.log('Initializing theme with:', { savedTheme, savedColor });
    
    // Apply saved theme with smooth transition
    setTimeout(() => {
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            if (darkModeToggle) darkModeToggle.checked = true;
        }
        
        // Apply saved color
        applyThemeColor(savedColor);
        setActiveColorOption(savedColor);
    }, 100);
    
    // Dark mode toggle event
    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', function() {
            if (this.checked) {
                enableDarkModeSmoothly();
            } else {
                disableDarkModeSmoothly();
            }
        });
    }
    
    // Theme color selection events - with better error handling
    function setupThemeColorListeners() {
        themeColorOptions = document.querySelectorAll('.color-option');
        console.log('Setting up listeners for', themeColorOptions.length, 'color options');
        
        themeColorOptions.forEach(option => {
            // Remove existing listeners to prevent duplicates
            option.replaceWith(option.cloneNode(true));
        });
        
        // Re-query after clone
        themeColorOptions = document.querySelectorAll('.color-option');
        
        themeColorOptions.forEach(option => {
            option.addEventListener('click', function() {
                const color = this.getAttribute('data-color');
                console.log('Color option clicked:', color);
                applyThemeColorSmoothly(color);
                setActiveColorOption(color);
                localStorage.setItem('theme-color', color);
            });
        });
    }
    
    // Initial setup
    setupThemeColorListeners();
    
    // Re-setup listeners when settings section is shown (in case of dynamic loading)
    document.addEventListener('click', function(e) {
        if (e.target.closest('[data-section="settings"]')) {
            setTimeout(setupThemeColorListeners, 100);
        }
    });
}

// Make sure the theme color is applied when switching between light/dark mode
function enableDarkModeSmoothly() {
    document.body.classList.add('theme-transition');
    document.body.classList.add('dark-mode');
    localStorage.setItem('theme', 'dark');
    
    // Re-apply theme color after mode change
    const currentColor = localStorage.getItem('theme-color') || '#4361ee';
    setTimeout(() => {
        applyThemeColor(currentColor);
    }, 300);
    
    setTimeout(() => {
        document.body.classList.remove('theme-transition');
    }, 600);
}

function disableDarkModeSmoothly() {
    document.body.classList.add('theme-transition');
    document.body.classList.remove('dark-mode');
    localStorage.setItem('theme', 'light');
    
    // Re-apply theme color after mode change
    const currentColor = localStorage.getItem('theme-color') || '#4361ee';
    setTimeout(() => {
        applyThemeColor(currentColor);
    }, 300);
    
    setTimeout(() => {
        document.body.classList.remove('theme-transition');
    }, 600);
}

function applyThemeColorSmoothly(color) {
    const root = document.documentElement;
    
    // Add transition for color changes
    root.style.setProperty('--transition', 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)');
    
    // Update CSS variables based on the selected color
    switch(color) {
        case '#2c5530': // Instructor Green
            root.style.setProperty('--theme-primary', '#2c5530');
            root.style.setProperty('--theme-primary-dark', '#244328');
            root.style.setProperty('--theme-secondary', '#4a7c59');
            root.style.setProperty('--theme-accent', '#6b8e4e');
            root.style.setProperty('--primary-color', '#2c5530');
            root.style.setProperty('--primary-dark', '#244328');
            root.style.setProperty('--secondary-color', '#4a7c59');
            root.style.setProperty('--accent-color', '#6b8e4e');
            root.style.setProperty('--instructor-primary', '#2c5530');
            root.style.setProperty('--instructor-secondary', '#4a7c59');
            root.style.setProperty('--instructor-accent', '#6b8e4e');
            break;
        case '#8b5cf6': // Purple
            root.style.setProperty('--theme-primary', '#8b5cf6');
            root.style.setProperty('--theme-primary-dark', '#7c3aed');
            root.style.setProperty('--theme-secondary', '#a78bfa');
            root.style.setProperty('--theme-accent', '#c4b5fd');
            root.style.setProperty('--primary-color', '#8b5cf6');
            root.style.setProperty('--primary-dark', '#7c3aed');
            root.style.setProperty('--secondary-color', '#a78bfa');
            root.style.setProperty('--accent-color', '#c4b5fd');
            root.style.setProperty('--instructor-primary', '#8b5cf6');
            root.style.setProperty('--instructor-secondary', '#a78bfa');
            root.style.setProperty('--instructor-accent', '#c4b5fd');
            break;
        case '#ef4444': // Red
            root.style.setProperty('--theme-primary', '#ef4444');
            root.style.setProperty('--theme-primary-dark', '#dc2626');
            root.style.setProperty('--theme-secondary', '#f87171');
            root.style.setProperty('--theme-accent', '#fca5a5');
            root.style.setProperty('--primary-color', '#ef4444');
            root.style.setProperty('--primary-dark', '#dc2626');
            root.style.setProperty('--secondary-color', '#f87171');
            root.style.setProperty('--accent-color', '#fca5a5');
            root.style.setProperty('--instructor-primary', '#ef4444');
            root.style.setProperty('--instructor-secondary', '#f87171');
            root.style.setProperty('--instructor-accent', '#fca5a5');
            break;
        case '#f59e0b': // Amber
            root.style.setProperty('--theme-primary', '#f59e0b');
            root.style.setProperty('--theme-primary-dark', '#d97706');
            root.style.setProperty('--theme-secondary', '#fbbf24');
            root.style.setProperty('--theme-accent', '#fcd34d');
            root.style.setProperty('--primary-color', '#f59e0b');
            root.style.setProperty('--primary-dark', '#d97706');
            root.style.setProperty('--secondary-color', '#fbbf24');
            root.style.setProperty('--accent-color', '#fcd34d');
            root.style.setProperty('--instructor-primary', '#f59e0b');
            root.style.setProperty('--instructor-secondary', '#fbbf24');
            root.style.setProperty('--instructor-accent', '#fcd34d');
            break;
        case '#800000': // Maroon
            root.style.setProperty('--theme-primary', '#800000');
            root.style.setProperty('--theme-primary-dark', '#660000');
            root.style.setProperty('--theme-secondary', '#a52a2a');
            root.style.setProperty('--theme-accent', '#cd5c5c');
            root.style.setProperty('--primary-color', '#800000');
            root.style.setProperty('--primary-dark', '#660000');
            root.style.setProperty('--secondary-color', '#a52a2a');
            root.style.setProperty('--accent-color', '#cd5c5c');
            root.style.setProperty('--instructor-primary', '#800000');
            root.style.setProperty('--instructor-secondary', '#a52a2a');
            root.style.setProperty('--instructor-accent', '#cd5c5c');
            break;
        default: // Default Blue
            root.style.setProperty('--theme-primary', '#4361ee');
            root.style.setProperty('--theme-primary-dark', '#3a56d4');
            root.style.setProperty('--theme-secondary', '#3f37c9');
            root.style.setProperty('--theme-accent', '#4895ef');
            root.style.setProperty('--primary-color', '#4361ee');
            root.style.setProperty('--primary-dark', '#3a56d4');
            root.style.setProperty('--secondary-color', '#3f37c9');
            root.style.setProperty('--accent-color', '#4895ef');
            root.style.setProperty('--instructor-primary', '#4361ee');
            root.style.setProperty('--instructor-secondary', '#3f37c9');
            root.style.setProperty('--instructor-accent', '#4895ef');
    }
    
    // Show notification
    showNotification('Theme color updated to ' + getColorName(color), 'info');
    
    // Reset transition after color change
    setTimeout(() => {
        root.style.setProperty('--transition', 'all 0.3s ease');
    }, 400);
}

function applyThemeColor(color) {
    const root = document.documentElement;
    
    // Update CSS variables based on the selected color
    switch(color) {
        case '#2c5530': // Instructor Green
            root.style.setProperty('--theme-primary', '#2c5530');
            root.style.setProperty('--theme-primary-dark', '#244328');
            root.style.setProperty('--theme-secondary', '#4a7c59');
            root.style.setProperty('--theme-accent', '#6b8e4e');
            root.style.setProperty('--primary-color', '#2c5530');
            root.style.setProperty('--primary-dark', '#244328');
            root.style.setProperty('--secondary-color', '#4a7c59');
            root.style.setProperty('--accent-color', '#6b8e4e');
            root.style.setProperty('--instructor-primary', '#2c5530');
            root.style.setProperty('--instructor-secondary', '#4a7c59');
            root.style.setProperty('--instructor-accent', '#6b8e4e');
            break;
        case '#8b5cf6': // Purple
            root.style.setProperty('--theme-primary', '#8b5cf6');
            root.style.setProperty('--theme-primary-dark', '#7c3aed');
            root.style.setProperty('--theme-secondary', '#a78bfa');
            root.style.setProperty('--theme-accent', '#c4b5fd');
            root.style.setProperty('--primary-color', '#8b5cf6');
            root.style.setProperty('--primary-dark', '#7c3aed');
            root.style.setProperty('--secondary-color', '#a78bfa');
            root.style.setProperty('--accent-color', '#c4b5fd');
            root.style.setProperty('--instructor-primary', '#8b5cf6');
            root.style.setProperty('--instructor-secondary', '#a78bfa');
            root.style.setProperty('--instructor-accent', '#c4b5fd');
            break;
        case '#ef4444': // Red
            root.style.setProperty('--theme-primary', '#ef4444');
            root.style.setProperty('--theme-primary-dark', '#dc2626');
            root.style.setProperty('--theme-secondary', '#f87171');
            root.style.setProperty('--theme-accent', '#fca5a5');
            root.style.setProperty('--primary-color', '#ef4444');
            root.style.setProperty('--primary-dark', '#dc2626');
            root.style.setProperty('--secondary-color', '#f87171');
            root.style.setProperty('--accent-color', '#fca5a5');
            root.style.setProperty('--instructor-primary', '#ef4444');
            root.style.setProperty('--instructor-secondary', '#f87171');
            root.style.setProperty('--instructor-accent', '#fca5a5');
            break;
        case '#f59e0b': // Amber
            root.style.setProperty('--theme-primary', '#f59e0b');
            root.style.setProperty('--theme-primary-dark', '#d97706');
            root.style.setProperty('--theme-secondary', '#fbbf24');
            root.style.setProperty('--theme-accent', '#fcd34d');
            root.style.setProperty('--primary-color', '#f59e0b');
            root.style.setProperty('--primary-dark', '#d97706');
            root.style.setProperty('--secondary-color', '#fbbf24');
            root.style.setProperty('--accent-color', '#fcd34d');
            root.style.setProperty('--instructor-primary', '#f59e0b');
            root.style.setProperty('--instructor-secondary', '#fbbf24');
            root.style.setProperty('--instructor-accent', '#fcd34d');
            break;
        case '#800000': // Maroon
            root.style.setProperty('--theme-primary', '#800000');
            root.style.setProperty('--theme-primary-dark', '#660000');
            root.style.setProperty('--theme-secondary', '#a52a2a');
            root.style.setProperty('--theme-accent', '#cd5c5c');
            root.style.setProperty('--primary-color', '#800000');
            root.style.setProperty('--primary-dark', '#660000');
            root.style.setProperty('--secondary-color', '#a52a2a');
            root.style.setProperty('--accent-color', '#cd5c5c');
            root.style.setProperty('--instructor-primary', '#800000');
            root.style.setProperty('--instructor-secondary', '#a52a2a');
            root.style.setProperty('--instructor-accent', '#cd5c5c');
            break;
        default: // Default Blue
            root.style.setProperty('--theme-primary', '#4361ee');
            root.style.setProperty('--theme-primary-dark', '#3a56d4');
            root.style.setProperty('--theme-secondary', '#3f37c9');
            root.style.setProperty('--theme-accent', '#4895ef');
            root.style.setProperty('--primary-color', '#4361ee');
            root.style.setProperty('--primary-dark', '#3a56d4');
            root.style.setProperty('--secondary-color', '#3f37c9');
            root.style.setProperty('--accent-color', '#4895ef');
            root.style.setProperty('--instructor-primary', '#4361ee');
            root.style.setProperty('--instructor-secondary', '#3f37c9');
            root.style.setProperty('--instructor-accent', '#4895ef');
    }
}


// Helper function to get color name
function getColorName(color) {
    const colorMap = {
        '#4361ee': 'Blue',
        '#2c5530': 'Green', 
        '#8b5cf6': 'Purple',
        '#ef4444': 'Red',
        '#f59e0b': 'Amber',
        '#800000': 'Maroon'
    };
    return colorMap[color] || 'Custom';
}

function setActiveColorOption(color) {
    const colorOptions = document.querySelectorAll('.color-option');
    colorOptions.forEach(option => {
        if (option.getAttribute('data-color') === color) {
            option.classList.add('active');
        } else {
            option.classList.remove('active');
        }
    });
}

// System preference listener with smooth transitions
function watchSystemTheme() {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    
    mediaQuery.addEventListener('change', (e) => {
        // Only auto-switch if user hasn't manually set a preference
        if (!localStorage.getItem('theme')) {
            const darkModeToggle = document.getElementById('dark-mode-toggle');
            if (e.matches) {
                enableDarkModeSmoothly();
                if (darkModeToggle) darkModeToggle.checked = true;
            } else {
                disableDarkModeSmoothly();
                if (darkModeToggle) darkModeToggle.checked = false;
            }
        }
    });
}

// Add keyboard shortcut for dark mode (Ctrl/Cmd + D)
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
        e.preventDefault();
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        if (darkModeToggle) {
            darkModeToggle.checked = !darkModeToggle.checked;
            if (darkModeToggle.checked) {
                enableDarkModeSmoothly();
            } else {
                disableDarkModeSmoothly();
            }
        }
    }
});

// Global notification function
function showNotification(message, type = 'info') {
    // Use SweetAlert2 if available
    if (typeof Swal !== 'undefined') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        
        Toast.fire({
            icon: type,
            title: message
        });
    } else {
        // Fallback to browser alert
        alert(`[${type.toUpperCase()}] ${message}`);
    }
}

// Enhanced mobile subject interactions
function setupMobileSubjectInteractions() {
    const subjectOptions = document.querySelectorAll('.subject-option');
    
    subjectOptions.forEach(option => {
        // Enhanced touch handling
        option.addEventListener('touchstart', function(e) {
            e.preventDefault();
            this.style.transition = 'all 0.1s ease';
        }, { passive: false });
        
        option.addEventListener('touchend', function(e) {
            e.preventDefault();
            this.style.transition = '';
            
            // Trigger click event
            const clickEvent = new MouseEvent('click', {
                view: window,
                bubbles: true,
                cancelable: true
            });
            this.dispatchEvent(clickEvent);
        }, { passive: false });
    });
}

// Input Grades functionality - UPDATED VERSION WITH PROPER MODAL PERSISTENCE
// Input Grades functionality - FIXED VERSION
function initializeInputGrades() {
    console.log('Initializing Input Grades section...');

    const filterForm = document.getElementById('grade-filter-form');
    const resetBtn = document.getElementById('reset-filters');
    const studentsGrid = document.getElementById('students-grid');
    const loadingContainer = document.getElementById('loading-container');
    const noStudentsContainer = document.getElementById('no-students-container');
    
    // Grade modal elements
    const gradeModal = document.getElementById('grade-modal');
    const closeGradeModal = document.getElementById('close-grade-modal');
    const cancelGrade = document.getElementById('cancel-grade');
    const gradeForm = document.getElementById('grade-form');

    // Track if already initialized to prevent duplicate event listeners
    let isInitialized = false;
    
    // Check if required elements exist
    if (!studentsGrid) {
        console.error('Students grid element not found');
        return;
    }
    
    // Load students on initialization
    loadUngradedStudents();
    
    // Filter form submission - only attach once
    if (filterForm && !filterForm.hasAttribute('data-listener-attached')) {
        filterForm.setAttribute('data-listener-attached', 'true');
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            loadUngradedStudents();
        });
    }
    
    // Reset filters - only attach once
    if (resetBtn && !resetBtn.hasAttribute('data-listener-attached')) {
        resetBtn.setAttribute('data-listener-attached', 'true');
        resetBtn.addEventListener('click', function() {
            if (filterForm) {
                filterForm.reset();
                loadUngradedStudents();
            }
        });
    }
    
    // Close modal events - only attach once
    if (closeGradeModal && !closeGradeModal.hasAttribute('data-listener-attached')) {
        closeGradeModal.setAttribute('data-listener-attached', 'true');
        closeGradeModal.addEventListener('click', closeModal);
    }
    
    if (cancelGrade && !cancelGrade.hasAttribute('data-listener-attached')) {
        cancelGrade.setAttribute('data-listener-attached', 'true');
        cancelGrade.addEventListener('click', closeModal);
    }
    
    // Grade form submission - only attach once
    if (gradeForm && !gradeForm.hasAttribute('data-listener-attached')) {
        gradeForm.setAttribute('data-listener-attached', 'true');
        gradeForm.addEventListener('submit', handleGradeSubmission);
    }
    
    // Close modal when clicking outside - only attach once
    if (gradeModal && !gradeModal.hasAttribute('data-listener-attached')) {
        gradeModal.setAttribute('data-listener-attached', 'true');
        gradeModal.addEventListener('click', function(e) {
            if (e.target === gradeModal) {
                closeModal();
            }
        });
    }

    // Separate function for grade submission to prevent multiple bindings
    function handleGradeSubmission(e) {
        e.preventDefault();
        
        const submitBtn = gradeForm.querySelector('.btn-primary');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.disabled = true;
        
        if (window.innerWidth <= 768) {
            submitBtn.style.transform = 'scale(0.98)';
        }
        
        const formData = new FormData(gradeForm);
        const gradeData = {
            student_id: parseInt(formData.get('student_id')),
            subject_id: parseInt(formData.get('subject_id')),
            grade: formData.get('grade')
        };
        
        console.log('Saving grade data:', gradeData);
        
        fetch(window.laravelRoutes.saveGrade, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(gradeData)
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.message || 'Network response was not ok');
                }).catch(() => {
                    throw new Error('Network response was not ok');
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Save grade response:', data);
            if (data.success) {
                handleSuccessfulGradeSubmission(gradeData);
            } else {
                showNotification(data.message || 'Error saving grade', 'error');
            }
        })
        .catch(error => {
            console.error('Error saving grade:', error);
            showNotification('Error saving grade: ' + error.message, 'error');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            if (window.innerWidth <= 768) {
                submitBtn.style.transform = 'scale(1)';
            }
        });
    }

    function handleSuccessfulGradeSubmission(gradeData) {
        // Remove the graded subject from the modal
        const gradedSubjectId = document.getElementById('grade_subject_id').value;
        const gradedSubjectOption = document.querySelector(`.subject-option[data-subject-id="${gradedSubjectId}"]`);
        
        // Update the student card in the main grid - ONLY ONCE
        updateStudentCardAfterGrading(gradeData.student_id, gradeData.subject_id);
        
        if (gradedSubjectOption) {
            gradedSubjectOption.remove();
            
            // Check if there are more subjects for this student in the modal
            const remainingSubjects = document.querySelectorAll('.subject-option');
            
            if (remainingSubjects.length > 0) {
                // Auto-select the next subject and reset the grade input
                const firstSubject = remainingSubjects[0];
                firstSubject.click();
                document.getElementById('grade').value = '';
                
                // Show notification only once
                showNotification('Grade saved! Please select grade for next subject.', 'info');
                
                console.log('Remaining subjects:', remainingSubjects.length, '- Keeping modal open');
            } else {
                // No more subjects, close modal and reload the student list
                showNotification('All subjects graded for this student!', 'success');
                setTimeout(() => {
                    closeModal();
                    loadUngradedStudents();
                }, 1500);
            }
        } else {
            // If for some reason we can't find the subject option, close modal and reload
            console.warn('Could not find graded subject option in modal');
            setTimeout(() => {
                closeModal();
                loadUngradedStudents();
            }, 1000);
        }
    }
    
    function loadUngradedStudents() {
        console.log('Loading students...');
        showLoading(true);
        
        const yearLevel = document.getElementById('year_level')?.value;
        const search = document.getElementById('search')?.value;
        const sortBy = document.getElementById('sort_by')?.value;
        const subjectSearch = document.getElementById('subject_search')?.value;
        const gradeStatus = document.getElementById('grade_status')?.value;
        
        const requestData = {
            year_level: yearLevel || '',
            search: search || '',
            sort_by: sortBy || 'lastname',
            subject_search: subjectSearch || '',
            grade_status: gradeStatus || 'ungraded'
        };
        
        console.log('Request data:', requestData);
        
        fetch(window.laravelRoutes.ungradedStudents, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Students data received:', data);
            displayStudents(data.students || []);
            showLoading(false);
        })
        .catch(error => {
            console.error('Error loading students:', error);
            showLoading(false);
            showNotification('Error loading students: ' + error.message, 'error');
            displayStudents([]);
        });
    }
    
    function displayStudents(students) {
        studentsGrid.innerHTML = '';
        
        if (!students || students.length === 0) {
            noStudentsContainer.style.display = 'block';
            studentsGrid.style.display = 'none';
            return;
        }
        
        noStudentsContainer.style.display = 'none';
        studentsGrid.style.display = 'grid';
        
        students.forEach(student => {
            const studentCard = createStudentCard(student);
            studentsGrid.appendChild(studentCard);
        });
    }
    
    function createStudentCard(student) {
        const card = document.createElement('div');
        card.className = 'student-card';
        
        const avatarUrl = student.profile;
        
        let subjectsPreviewHTML = '';
        let allSubjectsHTML = '';
        
        if (student.subjects && student.subjects.length > 0) {
            // Preview subjects (show first 2)
            const previewSubjects = student.subjects.slice(0, 2);
            previewSubjects.forEach(subject => {
                const isGraded = subject.current_grade !== null;
                const gradeBadge = isGraded ? `<span class="grade-badge" style="background: #10b981; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; margin-left: 8px;">${subject.current_grade}</span>` : '';
                
                subjectsPreviewHTML += `
                    <div class="subject-preview-item ${isGraded ? 'graded' : ''}">
                        <span class="subject-code">${subject.subject_code || 'N/A'}</span>
                        <span class="subject-name">${subject.subject_name || 'N/A'}</span>
                        ${gradeBadge}
                    </div>
                `;
            });
            
            // All subjects for expandable section
            student.subjects.forEach(subject => {
                const isGraded = subject.current_grade !== null;
                const gradeBadge = isGraded ? `<span class="grade-badge" style="background: #10b981; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; margin-left: 8px;">${subject.current_grade}</span>` : '';
                const buttonText = isGraded ? 'Edit Grade' : 'Input Grade';
                const buttonIcon = isGraded ? 'fas fa-edit' : 'fas fa-pen';
                
                allSubjectsHTML += `
                    <div class="subject-item ${isGraded ? 'graded' : ''}" data-subject-id="${subject.subject_id}">
                        <div class="subject-header">
                            <div class="subject-code">${subject.subject_code || 'N/A'} ${gradeBadge}</div>
                            <div class="subject-name">${subject.subject_name || 'N/A'}</div>
                        </div>
                        <div class="subject-meta">
                            <span>${subject.units || '0'} units</span>
                            <span>${subject.subject_semester || 'N/A'}</span>
                            <span>${isGraded ? 'Graded' : 'Not Graded'}</span>
                        </div>
                        <button class="btn-primary btn-grade btn-sm" 
                                data-student-id="${student.student_db_id}"
                                data-student-user-id="${student.student_user_id}"
                                data-firstname="${student.firstname}"
                                data-middlename="${student.middlename || ''}"
                                data-lastname="${student.lastname}"
                                data-id-no="${student.id_no || 'N/A'}"
                                data-year-level="${student.year_level}"
                                data-subject-id="${subject.subject_id}"
                                data-subject-code="${subject.subject_code}"
                                data-subject-name="${subject.subject_name}"
                                data-subject-units="${subject.units}"
                                data-subject-year="${subject.subject_year}"
                                data-subject-semester="${subject.subject_semester}"
                                data-current-grade="${subject.current_grade || ''}">
                            <i class="${buttonIcon}"></i>
                            ${buttonText}
                        </button>
                    </div>
                `;
            });
            
            if (student.subjects.length > 2) {
                subjectsPreviewHTML += `
                    <div class="more-subjects-indicator">
                        +${student.subjects.length - 2} more subjects
                    </div>
                `;
            }
        } else {
            subjectsPreviewHTML = '<div class="no-subjects">No subjects found</div>';
            allSubjectsHTML = '<div class="no-subjects">No subjects found</div>';
        }
        
        card.innerHTML = `
            <div class="student-header">
                <img src="${avatarUrl}" alt="Student Avatar" class="student-avatar-small">
                <div class="student-info">
                    <h4 class="student-name">${student.firstname} ${student.middlename || ''} ${student.lastname}</h4>
                    <p class="student-id">ID: ${student.id_no || 'N/A'}</p>
                    <span class="student-year">${student.year_level || 'N/A'}</span>
                </div>
                <div class="student-actions-toggle">
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </div>
            </div>
            
            <div class="subjects-preview">
                ${subjectsPreviewHTML}
            </div>
            
            <div class="student-subjects-expandable">
                <div class="expandable-content">
                    ${allSubjectsHTML}
                </div>
            </div>
        `;
        
        // Enhanced mobile event listeners
        const toggleBtn = card.querySelector('.student-actions-toggle');
        const expandableSection = card.querySelector('.student-subjects-expandable');
        
        if (toggleBtn && expandableSection) {
            const handleToggle = function(e) {
                e.stopPropagation();
                expandableSection.classList.toggle('expanded');
                const icon = this.querySelector('.toggle-icon');
                icon.classList.toggle('fa-chevron-down');
                icon.classList.toggle('fa-chevron-up');
                
                if (window.innerWidth <= 768 && expandableSection.classList.contains('expanded')) {
                    setTimeout(() => {
                        expandableSection.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'nearest'
                        });
                    }, 300);
                }
            };
            
            // Remove any existing listeners and add new ones
            toggleBtn.replaceWith(toggleBtn.cloneNode(true));
            const newToggleBtn = card.querySelector('.student-actions-toggle');
            newToggleBtn.addEventListener('click', handleToggle);
            newToggleBtn.addEventListener('touchend', function(e) {
                e.preventDefault();
                handleToggle.call(this, e);
            });
        }
        
        // Enhanced grade buttons with better mobile support
        const gradeButtons = card.querySelectorAll('.btn-grade');
        gradeButtons.forEach(button => {
            // Clone and replace to remove any existing listeners
            button.replaceWith(button.cloneNode(true));
            const newButton = card.querySelector(`.btn-grade[data-subject-id="${button.dataset.subjectId}"]`);
            
            const handleGradeClick = function(e) {
                e.stopPropagation();
                openGradeModal(this.dataset, student.profile, student.subjects);
            };
            
            newButton.addEventListener('click', handleGradeClick);
            newButton.addEventListener('touchend', function(e) {
                e.preventDefault();
                handleGradeClick.call(this, e);
            });
        });
        
        return card;
    }

    // FIXED: Update student card after grading - removes only the graded subject
    function updateStudentCardAfterGrading(studentId, subjectId) {
        console.log('Updating student card after grading:', { studentId, subjectId });
        
        // Find the specific student card
        const studentCards = document.querySelectorAll('.student-card');
        let removedCount = 0;
        
        studentCards.forEach(card => {
            // Find the grade button for this specific student and subject
            const gradeButton = card.querySelector(`.btn-grade[data-student-id="${studentId}"][data-subject-id="${subjectId}"]`);
            
            if (gradeButton) {
                // Remove only the specific subject item
                const subjectItem = gradeButton.closest('.subject-item');
                if (subjectItem) {
                    subjectItem.remove();
                    removedCount++;
                    console.log('Removed subject from student card:', subjectId);
                    
                    // Update the subjects preview for this card
                    updateSubjectsPreview(card);
                    
                    // Check if this student has any more subjects
                    const remainingSubjects = card.querySelectorAll('.subject-item');
                    if (remainingSubjects.length === 0) {
                        // Remove the entire student card if no more subjects
                        card.remove();
                        console.log('Removed student card - no more subjects');
                        
                        // Check if no students left
                        const remainingStudents = document.querySelectorAll('.student-card');
                        if (remainingStudents.length === 0) {
                            document.getElementById('no-students-container').style.display = 'block';
                            document.getElementById('students-grid').style.display = 'none';
                        }
                    }
                }
            }
        });
        
        console.log(`Removed ${removedCount} subject(s) for student ${studentId}`);
    }
    
    // FIXED: Update subjects preview in student card
    function updateSubjectsPreview(card) {
        const subjectsPreview = card.querySelector('.subjects-preview');
        const expandableContent = card.querySelector('.expandable-content');
        
        if (!subjectsPreview || !expandableContent) return;
        
        // Get all remaining subject items
        const remainingSubjects = expandableContent.querySelectorAll('.subject-item');
        
        let previewHTML = '';
        const previewSubjects = Array.from(remainingSubjects).slice(0, 2);
        
        previewSubjects.forEach(subjectItem => {
            const subjectCode = subjectItem.querySelector('.subject-code')?.textContent || 'N/A';
            const subjectName = subjectItem.querySelector('.subject-name')?.textContent || 'N/A';
            
            previewHTML += `
                <div class="subject-preview-item">
                    <span class="subject-code">${subjectCode}</span>
                    <span class="subject-name">${subjectName}</span>
                </div>
            `;
        });
        
        // Show more indicator if there are more than 2 subjects
        if (remainingSubjects.length > 2) {
            previewHTML += `
                <div class="more-subjects-indicator">
                    +${remainingSubjects.length - 2} more subjects
                </div>
            `;
        }
        
        // If no subjects left, show no subjects message
        if (remainingSubjects.length === 0) {
            previewHTML = '<div class="no-subjects">All subjects graded</div>';
        }
        
        subjectsPreview.innerHTML = previewHTML;
    }
        
    // Enhanced mobile modal handling
    function openGradeModal(data, sp, allSubjects = []) {
        console.log('Opening grade modal with data:', data);
        
        const avatarUrl = sp;
        
        // Update student info
        document.getElementById('student_avatar').src = avatarUrl;
        document.getElementById('student_full_name').textContent = `${data.firstname} ${data.lastname}`.trim();
        document.getElementById('student_id_display').textContent = `ID: ${data.idNo}`;
        document.getElementById('student_course').textContent = `Year: ${data.yearLevel}`;
        
        // Populate subjects list
        const subjectsList = document.getElementById('subjects_list');
        subjectsList.innerHTML = '';
        
        if (allSubjects && allSubjects.length > 0) {
            allSubjects.forEach(subject => {
                const subjectOption = document.createElement('div');
                subjectOption.className = 'subject-option';
                if (parseInt(subject.subject_id) === parseInt(data.subjectId)) {
                    subjectOption.classList.add('active');
                }
                
                // Add graded indicator
                if (subject.current_grade) {
                    subjectOption.classList.add('graded');
                }
                
                subjectOption.dataset.subjectId = subject.subject_id;
                subjectOption.dataset.subjectCode = subject.subject_code;
                subjectOption.dataset.subjectName = subject.subject_name;
                subjectOption.dataset.units = subject.units;
                subjectOption.dataset.subjectYear = subject.subject_year;
                subjectOption.dataset.subjectSemester = subject.subject_semester;
                subjectOption.dataset.currentGrade = subject.current_grade || '';
                
                const gradeBadge = subject.current_grade ? `<span class="subject-grade-badge">${subject.current_grade}</span>` : '';
                
                subjectOption.innerHTML = `
                    <div class="subject-option-header">
                        <div class="subject-code">${subject.subject_code || 'N/A'} ${gradeBadge}</div>
                        <div class="subject-meta">${subject.units || '0'} units</div>
                    </div>
                    <div class="subject-name">${subject.subject_name || 'N/A'}</div>
                `;
                
                subjectOption.addEventListener('click', function() {
                    document.querySelectorAll('.subject-option').forEach(opt => {
                        opt.classList.remove('active');
                    });
                    
                    this.classList.add('active');
                    
                    updateSubjectDetails({
                        subject_code: this.dataset.subjectCode,
                        subject_name: this.dataset.subjectName,
                        units: this.dataset.units,
                        subject_year: this.dataset.subjectYear,
                        subject_semester: this.dataset.subjectSemester
                    });
                    
                    document.getElementById('grade_subject_id').value = this.dataset.subjectId;
                    
                    // Pre-fill the grade if it exists
                    if (this.dataset.currentGrade) {
                        document.getElementById('grade').value = this.dataset.currentGrade;
                    } else {
                        document.getElementById('grade').value = '';
                    }
                });
                
                subjectsList.appendChild(subjectOption);
            });

            setTimeout(() => {
                const activeSubject = subjectsList.querySelector('.subject-option.active');
                if (!activeSubject && subjectsList.firstChild) {
                    subjectsList.firstChild.click();
                }
            }, 100);
        } else {
            subjectsList.innerHTML = '<div class="no-subjects">No subjects available</div>';
        }
        
        updateSubjectDetails({
            subject_code: data.subjectCode,
            subject_name: data.subjectName,
            units: data.subjectUnits,
            subject_year: data.subjectYear,
            subject_semester: data.subjectSemester
        });
        
        document.getElementById('grade_student_id').value = data.studentId || data.student_db_id;
        document.getElementById('grade_subject_id').value = data.subjectId;
        
        // Pre-fill the grade if it exists in the button data
        document.getElementById('grade').value = data.currentGrade || '';
        
        const gradeModal = document.getElementById('grade-modal');
        gradeModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        if (window.innerWidth <= 768) {
            gradeModal.addEventListener('touchend', handleMobileBackdropTap);
        }
    }

    function handleMobileBackdropTap(e) {
        if (e.target === document.getElementById('grade-modal')) {
            closeModal();
        }
    }
    
    function updateSubjectDetails(subject) {
        document.getElementById('subject_code').textContent = subject.subject_code || 'N/A';
        document.getElementById('subject_name').textContent = subject.subject_name || 'N/A';
        document.getElementById('subject_units').textContent = subject.units || '0';
        document.getElementById('subject_year').textContent = subject.subject_year || 'N/A';
        document.getElementById('subject_semester').textContent = subject.subject_semester || 'N/A';
    }
    
    function closeModal() {
        const gradeModal = document.getElementById('grade-modal');
        gradeModal.style.display = 'none';
        document.body.style.overflow = 'auto';
        
        gradeModal.removeEventListener('touchend', handleMobileBackdropTap);
    }
    
    function showLoading(show) {
        if (show) {
            loadingContainer.style.display = 'block';
            studentsGrid.style.display = 'none';
            noStudentsContainer.style.display = 'none';
        } else {
            loadingContainer.style.display = 'none';
            studentsGrid.style.display = 'grid';
        }
    }

    
}

// View Subject History in Enrollment Request
let allSubjectsData = [];
let currentStudentId = null;

// Function to show subjects history modal
function showSubjectsHistoryModal(studentId, studentName, studentIdNo) {
    currentStudentId = studentId;
    
    // Update modal header
    document.getElementById('modalStudentName').textContent = studentName;
    document.getElementById('modalStudentId').textContent = ` ID: ${studentIdNo}`;
    
    // Show modal
    const modal = document.getElementById('subjectsHistoryModal');
    modal.style.display = 'block';
    
    // Fetch subjects history
    fetchSubjectsHistory(studentId);
}

// Fetch subjects history from server
function fetchSubjectsHistory(studentId) {
    // Show loading indicator
    document.getElementById('loadingIndicator').style.display = 'flex';
    document.getElementById('subjectsHistoryBody').innerHTML = '';
    document.getElementById('noResults').style.display = 'none';
    
    // Make API call to fetch subjects history
    fetch(`/instructor/student-subjects-history/${studentId}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            allSubjectsData = data.subjects || [];
            
            // Populate year filter
            populateYearFilter(allSubjectsData);
            
            // Render table
            renderSubjectsTable(allSubjectsData);
            
            // Update statistics
            updateStatistics(allSubjectsData);
            
            // Hide loading indicator
            document.getElementById('loadingIndicator').style.display = 'none';
        })
        .catch(error => {
            console.error('Error fetching subjects history:', error);
            document.getElementById('loadingIndicator').style.display = 'none';
            document.getElementById('subjectsHistoryBody').innerHTML = `
                <tr>
                    <td colspan="8" class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        Error loading subjects history. Please try again.
                    </td>
                </tr>
            `;
        });
}

// Populate year filter with unique years
function populateYearFilter(subjects) {
    const yearFilter = document.getElementById('yearFilter');
    
    // Extract unique year levels from subjects
    const yearLevels = new Set();
    subjects.forEach(subject => {
        if (subject.year_level) {
            // Convert to string and clean up any whitespace
            const yearLevel = subject.year_level.toString().trim();
            if (yearLevel) {
                yearLevels.add(yearLevel);
            }
        }
    });
    
    // Clear existing options (keep "All Years")
    yearFilter.innerHTML = '<option value="all">All Years</option>';
    
    // Sort year levels in a logical order (1st Year, 2nd Year, etc.)
    const sortedYearLevels = Array.from(yearLevels).sort((a, b) => {
        // Extract numeric part for sorting
        const aNum = extractNumericYear(a);
        const bNum = extractNumericYear(b);
        return aNum - bNum;
    });
    
    // Add year level options
    sortedYearLevels.forEach(yearLevel => {
        const option = document.createElement('option');
        option.value = yearLevel;
        option.textContent = yearLevel;
        yearFilter.appendChild(option);
    });
}

// Helper function to extract numeric year from year level string
function extractNumericYear(yearLevel) {
    if (!yearLevel) return 0;
    
    // Try to extract the first number from the string
    // Handles formats like: "1st Year", "Year 1", "1", etc.
    const match = yearLevel.match(/\d+/);
    return match ? parseInt(match[0]) : 0;
}

// Render subjects table
function renderSubjectsTable(subjects) {
    const tbody = document.getElementById('subjectsHistoryBody');
    
    if (subjects.length === 0) {
        document.getElementById('noResults').style.display = 'block';
        tbody.innerHTML = '';
        return;
    }
    
    document.getElementById('noResults').style.display = 'none';
    
    tbody.innerHTML = subjects.map(subject => {
        const grade = subject.grade || 'Not Graded';
        const status = getGradeStatus(grade);
        const statusClass = getStatusClass(grade);
        
        // Format date
        const dateEnrolled = subject.date_enrolled ? 
            new Date(subject.date_enrolled).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }) : 'N/A';
        
        return `
            <tr id="d-student_subject_history2_1">
                <td class="subject-code-cell">
                    <span class="subject-code">${subject.subject_code}</span>
                </td>
                <td class="subject-name-cell">
                    <span class="subject-name">${subject.subject_name}</span>
                </td>
                <td class="units-cell">${subject.units}</td>
                <td class="year-cell">${subject.year_level}</td>
                <td class="semester-cell">${subject.semester}</td>
                <td class="grade-cell ${statusClass}">
                    ${grade}
                </td>
                <td class="status-cell">
                    <span class="status-badge ${statusClass}">
                        ${status}
                    </span>
                </td>

            </tr>
        `;
    }).join('');
}

// Get grade status
function getGradeStatus(grade) {
    if (grade === 'Not Graded') return 'In Progress';
    
    const gradeStr = grade.toString().toUpperCase();
    
    if (['INC', 'INCOMPLETE'].includes(gradeStr)) return 'Incomplete';
    if (['DRP', 'DROP', 'DROPPED'].includes(gradeStr)) return 'Dropped';
    if (gradeStr === 'ND') return 'No Data';
    
    const gradeNum = parseFloat(grade);
    if (isNaN(gradeNum)) return 'Unknown';
    
    if (gradeNum >= 1.0 && gradeNum <= 3.0) return 'Passed';
    if (gradeNum === 5.0) return 'Failed (5.0)';
    if (gradeNum > 3.0) return 'Failed';
    
    return 'Unknown';
}

// Get CSS class for status
function getStatusClass(grade) {
    if (grade === 'Not Graded') return 'in-progress';
    
    const gradeStr = grade.toString().toUpperCase();
    
    if (['INC', 'INCOMPLETE'].includes(gradeStr)) return 'incomplete';
    if (['DRP', 'DROP', 'DROPPED'].includes(gradeStr)) return 'dropped';
    if (gradeStr === 'ND') return 'no-data';
    
    const gradeNum = parseFloat(grade);
    if (isNaN(gradeNum)) return 'unknown';
    
    if (gradeNum >= 1.0 && gradeNum <= 3.0) return 'passed';
    if (gradeNum === 5.0 || gradeNum > 3.0) return 'failed';
    
    return 'unknown';
}

// Update statistics
function updateStatistics(subjects) {
    let total = subjects.length;
    let passed = 0;
    let failed = 0;
    let inProgress = 0;
    
    subjects.forEach(subject => {
        const grade = subject.grade;
        
        if (!grade || grade === 'Not Graded') {
            inProgress++;
        } else if (['INC', 'INCOMPLETE', 'DRP', 'DROP', 'DROPPED', 'ND'].includes(grade.toString().toUpperCase())) {
            failed++; // Count special cases as failed
        } else {
            const gradeNum = parseFloat(grade);
            if (!isNaN(gradeNum)) {
                if (gradeNum >= 1.0 && gradeNum <= 3.0) {
                    passed++;
                } else {
                    failed++;
                }
            }
        }
    });
    
    document.getElementById('totalSubjects').textContent = total;
    document.getElementById('passedSubjects').textContent = passed;
    document.getElementById('failedSubjects').textContent = failed;
    document.getElementById('inProgressSubjects').textContent = inProgress;
}

// Filter and search functionality
function applyFilters() {
    const searchTerm = document.getElementById('searchSubjects').value.toLowerCase();
    const gradeFilter = document.getElementById('gradeFilter').value;
    const yearFilter = document.getElementById('yearFilter').value;
    const semesterFilter = document.getElementById('semesterFilter').value;
    
    let filteredData = allSubjectsData;
    
    // Apply search filter
    if (searchTerm) {
        filteredData = filteredData.filter(subject => 
            subject.subject_code.toLowerCase().includes(searchTerm) ||
            subject.subject_name.toLowerCase().includes(searchTerm)
        );
    }
    
    // Apply grade filter
    if (gradeFilter !== 'all') {
        filteredData = filteredData.filter(subject => {
            const grade = subject.grade || 'Not Graded';
            const gradeStr = grade.toString().toUpperCase();
            
            switch (gradeFilter) {
                case 'graded':
                    return grade !== 'Not Graded' && !['', null, undefined].includes(grade);
                case 'ungraded':
                    return grade === 'Not Graded' || ['', null, undefined].includes(grade);
                case 'passed':
                    if (!grade || grade === 'Not Graded') return false;
                    const gradeNum = parseFloat(grade);
                    return !isNaN(gradeNum) && gradeNum >= 1.0 && gradeNum <= 3.0;
                case 'failed':
                    if (!grade || grade === 'Not Graded') return false;
                    const gradeNum2 = parseFloat(grade);
                    return !isNaN(gradeNum2) && (gradeNum2 > 3.0 || gradeNum2 === 5.0);
                case 'inc':
                    return ['INC', 'INCOMPLETE'].includes(gradeStr);
                case 'drp':
                    return ['DRP', 'DROP', 'DROPPED'].includes(gradeStr);
                default:
                    return true;
            }
        });
    }
    
    // Apply year filter
    if (yearFilter !== 'all') {
        filteredData = filteredData.filter(subject => 
            subject.year_level && subject.year_level.toString().toLowerCase() === yearFilter.toLowerCase()
        );
    }
    
    // Apply semester filter
    if (semesterFilter !== 'all') {
        filteredData = filteredData.filter(subject => 
            subject.semester === semesterFilter
        );
    }
    
    // Render filtered data
    renderSubjectsTable(filteredData);
    updateStatistics(filteredData);
}

// Setup modal event listeners
function setupModalEventListeners() {
    const modal = document.getElementById('subjectsHistoryModal');
    const closeBtn = modal.querySelector('.close-modal');
    
    // Close modal when clicking X
    closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Setup filter event listeners
    document.getElementById('searchSubjects').addEventListener('input', applyFilters);
    document.getElementById('gradeFilter').addEventListener('change', applyFilters);
    document.getElementById('yearFilter').addEventListener('change', applyFilters);
    document.getElementById('semesterFilter').addEventListener('change', applyFilters);
}

// Add sorting functionality
function setupTableSorting() {
    let sortColumn = null;
    let sortDirection = 'asc';
    
    document.querySelectorAll('#subjectsHistoryTable th').forEach((th, index) => {
        th.addEventListener('click', () => {
            if (sortColumn === index) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn = index;
                sortDirection = 'asc';
            }
            
            sortTable(index);
        });
    });
}

function sortTable(columnIndex) {
    const rows = Array.from(document.querySelectorAll('#subjectsHistoryBody tr'));
    
    rows.sort((a, b) => {
        const aText = a.children[columnIndex].textContent.trim();
        const bText = b.children[columnIndex].textContent.trim();
        
        // Special handling for grade column
        if (columnIndex === 5) {
            const aGrade = parseFloat(aText) || 999; // Put non-numeric grades at bottom
            const bGrade = parseFloat(bText) || 999;
            return sortDirection === 'asc' ? aGrade - bGrade : bGrade - aGrade;
        }
        
        // Default string comparison
        return sortDirection === 'asc' 
            ? aText.localeCompare(bText)
            : bText.localeCompare(aText);
    });
    
    const tbody = document.getElementById('subjectsHistoryBody');
    tbody.innerHTML = '';
    rows.forEach(row => tbody.appendChild(row));
    
    // Update sort indicators
    document.querySelectorAll('#subjectsHistoryTable th').forEach((th, index) => {
        th.classList.remove('sort-asc', 'sort-desc');
        if (index === columnIndex) {
            th.classList.add(sortDirection === 'asc' ? 'sort-asc' : 'sort-desc');
        }
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
            const response = await fetch('/instructor/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });


            // Redirect after a short delay
            setTimeout(() => {
                // Show success message
                showNotification('Logged out successfully! Redirecting...', 'success');
                window.location.href = '/instructor';
            }, 1500);

    });

    // Alternative: Use form submission (simpler but no loading state)
    // logoutConfirmBtn.addEventListener('click', function() {
    //     logoutForm.submit();
    // });
}


function count_pending() {
    const requestsContainer = document.getElementById('requests-count');
    
    fetch('/instructor/enrollment-requests2', {
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

function reload_request() {
    initializeEnrollmentRequests();
}

function count_students() {
     const requestsContainer = document.getElementById('total-students');
    
    fetch('/instructor/enrollment-requests3', {
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

function pending_request() {
    const requestsContainer = document.getElementById('pending_request');
    
    fetch('/instructor/pending_request', {
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

function deleteAllNotifications() {
        if (!confirm('Are you sure you want to delete all notifications? This action cannot be undone.')) {
            return;
        }
        
        const deleteAllBtn = document.getElementById('delete-all-notifications');
        const originalContent = deleteAllBtn.innerHTML;
        
        // Show loading state
        deleteAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
        deleteAllBtn.disabled = true;
        
        console.log('Sending delete all notifications request...');
        
        fetch('/instructor/delete-all-notifications', {
            method: 'POST',
                headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({}) 
            })
        .then(response => {
            console.log('Response status:', response.status, response.statusText);
            
            if (response.status === 404) {
                throw new Error('Route not found. Please check if the route is defined.');
            }
            if (response.status === 401) {
                throw new Error('You are not authenticated. Please log in again.');
            }
            if (response.status === 500) {
                throw new Error('Server error. Please try again later.');
            }
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            
            if (data.success) {
                // Remove all notification elements
                const container = document.querySelector('.schedule-container');
                
                // Remove all children of the container
                while (container.firstChild) {
                    container.removeChild(container.firstChild);
                }


                // reload the page after deleting all notifications
                window.location.reload();
                
                // Show the empty state
                container.innerHTML = `
                    <div class="schedule-day">
                        <h4 class="day-header">No Notifications</h4>
                        <div class="schedule-item">
                            <div class="schedule-details">
                                <div class="schedule-course">No notifications at this time</div>
                                <div class="schedule-location">You'll see important updates here</div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Update the notification count in the header
                updateNotificationCount();
                
                // Hide the delete all button
                toggleDeleteAllButton();
                
                showNotification(data.message, 'success');
            } else {
                showNotification(data.message || 'Failed to delete notifications', 'error');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showNotification('Error deleting notifications: ' + error.message, 'error');
        })
        .finally(() => {
            // Reset button state
            deleteAllBtn.innerHTML = originalContent;
            deleteAllBtn.disabled = false;
        });
    }


function refresh() {
    window.location.reload();
}

// Instructor Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {

    setupModalEventListeners();
    setupTableSorting();
    setupRealtimeSubscription();
    count_pending();
    count_students();
    pending_request();
    updateNotificationCount();
    toggleDeleteAllButton();
    initializeProfilePictureUpload();
    handleImageErrors();

    // Initialize sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');

    console.log('DOM loaded - checking input grades section...');

    // Initialize Dark Mode and Theme System
    initializeDarkMode();
    watchSystemTheme();

    // Logout
    initializeLogout();


    // Mark notification as read functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.mark-as-read-btn')) {
            const button = e.target.closest('.mark-as-read-btn');
            const notificationId = button.dataset.id;
            deleteNotification(notificationId, button);
        }
    });

    // Function to delete single notification
    function deleteNotification(notificationId, button) {
        if (!confirm('Are you sure you want to delete this notification?')) {
            return;
        }
        
        fetch('/instructor/delete-notification', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ notification_id: notificationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove notification with fade out animation
                const notificationItem = button.closest('.schedule-day');
                if (notificationItem) {
                    notificationItem.style.transition = 'opacity 0.3s';
                    notificationItem.style.opacity = '0';
                    
                    setTimeout(() => {
                        notificationItem.remove();
                        
                        // Check if we need to show empty state
                        const container = document.querySelector('.schedule-container');
                        const remainingNotifications = container.querySelectorAll('.schedule-day');
                        
                        if (remainingNotifications.length === 0) {
                            container.innerHTML = `
                                <div class="schedule-day">
                                    <h4 class="day-header">No Notifications</h4>
                                    <div class="schedule-item">
                                        <div class="schedule-details">
                                            <div class="schedule-course">No notifications at this time</div>
                                            <div class="schedule-location">You'll see important updates here</div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                        
                        // Update notification count
                        updateNotificationCount();
                        
                        // Update Delete All button visibility
                        toggleDeleteAllButton();
                        
                    }, 300);
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Helper function to check if container has real notifications (not empty state)
    function hasRealNotifications() {
        const container = document.querySelector('.schedule-container');
        if (!container) return false;
        
        // Get all notification day elements
        const notificationDays = container.querySelectorAll('.schedule-day');
        
        // If no elements, no notifications
        if (notificationDays.length === 0) return false;
        
        // Check if the only element is the "No Notifications" empty state
        if (notificationDays.length === 1) {
            const header = notificationDays[0].querySelector('.day-header');
            if (header && (header.textContent.includes('No Notifications') || 
                        header.textContent.trim() === 'No Notifications')) {
                return false; // This is the empty state
            }
        }
        
        // If we have elements that aren't the empty state, we have real notifications
        return true;
    }

    // Function to toggle Delete All button visibility
    function toggleDeleteAllButton() {
        const deleteAllBtn = document.getElementById('delete-all-notifications');
        if (!deleteAllBtn) return;
        
        if (hasRealNotifications()) {
            deleteAllBtn.style.display = 'block';
        } else {
            deleteAllBtn.style.display = 'none';
        }
    }

    // Function to delete all notifications
    // Function to delete all notifications
    
    
    
    // Function to update notification count in header
    function updateNotificationCount() {
        const unreadNotifications = document.querySelectorAll('.badge-danger').length;
        const notificationCount = document.querySelector('.notification-count');
        
        if (notificationCount) {
            if (unreadNotifications > 0) {
                notificationCount.textContent = unreadNotifications;
                notificationCount.style.display = 'flex';
            } else {
                notificationCount.style.display = 'none';
            }
        }
    }


    // STUDENT MANAGEMENT FUNCTIONALITY
    
    // Get all search inputs
    const yearLevelFilter = document.getElementById('year-level-filter');
    const curriculumFilter = document.getElementById('curriculum-filter');
    const studentSearchInput = document.getElementById('search-students');
    const headerSearchInput = document.getElementById('header-search-input');
    const studentsTableBody = document.getElementById('students-table-body');

    // Function to filter students - DECLARE THIS FIRST
    function filterStudents() {
        const yearLevel = yearLevelFilter ? yearLevelFilter.value : '';
        const curriculum = curriculumFilter ? curriculumFilter.value : '';
        
        // Get search term from either input (prioritize student section search if both have value)
        let searchTerm = '';
        if (studentSearchInput && studentSearchInput.value.trim() !== '') {
            searchTerm = studentSearchInput.value.trim().toLowerCase();
        } else if (headerSearchInput && headerSearchInput.value.trim() !== '') {
            searchTerm = headerSearchInput.value.trim().toLowerCase();
        }

        // Get current rows in case table was updated
        const studentRows = studentsTableBody ? studentsTableBody.querySelectorAll('tr') : [];

        studentRows.forEach(row => {
            const rowYearLevel = row.getAttribute('data-year-level');
            const rowCurriculum = row.getAttribute('data-curriculum');
            const studentName = row.getAttribute('data-student-name');
            const studentId = row.getAttribute('data-student-id');
            
            const yearLevelMatch = !yearLevel || rowYearLevel === yearLevel;
            const curriculumMatch = !curriculum || rowCurriculum === curriculum;
            const searchMatch = !searchTerm || 
                studentName.includes(searchTerm) || 
                studentId.includes(searchTerm);
            
            if (yearLevelMatch && curriculumMatch && searchMatch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Now add event listeners (AFTER the function is defined)
    if (yearLevelFilter) yearLevelFilter.addEventListener('change', filterStudents);
    if (curriculumFilter) curriculumFilter.addEventListener('change', filterStudents);
    if (studentSearchInput) studentSearchInput.addEventListener('input', filterStudents);

    // Add event listener for header search
    if (headerSearchInput) {
        headerSearchInput.addEventListener('input', function() {
            // If header search is being used, clear the student section search
            if (studentSearchInput && headerSearchInput.value.trim() !== '') {
                studentSearchInput.value = '';
            }
            filterStudents();
        });
    }

    // Clear header search when student section search is used
    if (studentSearchInput) {
        studentSearchInput.addEventListener('input', function() {
            if (headerSearchInput && studentSearchInput.value.trim() !== '') {
                headerSearchInput.value = '';
            }
        });
    }

    // Add search button functionality
    const searchBtn5 = document.querySelector('#search-btn');
    if (searchBtn5) {
        searchBtn5.addEventListener('click', function() {
            // Trigger the search
            filterStudents();
            document.querySelector('[data-section="students"]').click();
        });
    }

    // Allow pressing Enter in search inputs
    function handleEnterKey(event) {
        if (event.key === 'Enter') {
            filterStudents();
            document.querySelector('[data-section="students"]').click();
        }
    }

    if (studentSearchInput) studentSearchInput.addEventListener('keypress', handleEnterKey);
    if (headerSearchInput) headerSearchInput.addEventListener('keypress', handleEnterKey);

    // The rest of your code remains the same...
    // View Profile button functionality
    document.querySelectorAll('.view-profile').forEach(button => {
        button.addEventListener('click', function() {
            const studentId = this.getAttribute('data-student-id');
            const row = this.closest('tr');
            const studentData = JSON.parse(row.getAttribute('data-student-data'));
            loadStudentProfile(studentId, studentData);
        });
    });

    // Send Message button functionality
    document.querySelectorAll('.send-message').forEach(button => {
        button.addEventListener('click', function() {
            const email = this.getAttribute('data-email');
            if (email && email !== '') {
                // Redirect to Gmail compose window
                window.open(`https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(email)}`, '_blank');
            } else {
                alert('No email address found for this student.');
            }
        });
    });

    // Modal functionality
    const profileModal = document.getElementById('studentProfileModal');
    const closeModal = document.querySelector('.close-modal2');

    if (closeModal) {
        closeModal.addEventListener('click', function() {
            profileModal.style.display = 'none';
        });
    }

    if (profileModal) {
        window.addEventListener('click', function(event) {
            if (event.target === profileModal) {
                profileModal.style.display = 'none';
            }
        });
    }

    // Function to load student profile
    // Function to load student profile
    async function loadStudentProfile(studentId, studentData) {
        // Show the modal
        const profileModal = document.getElementById('studentProfileModal');
        profileModal.style.display = 'block';
        
        // Display basic student information first
        const basicInfoHTML = `
            <div class="student-basic-info">
                <div class="info-section">
                    <h4>Personal Informations</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Student ID:</strong>
                            <span>${studentData.id_no}</span>
                        </div>
                        <div class="info-item">
                            <strong>Name:</strong>
                            <span>${studentData.full_name}</span>
                        </div>
                        <div class="info-item">
                            <strong>Year Level:</strong>
                            <span>${studentData.year_level}</span>
                        </div>
                        <div class="info-item">
                            <strong>Student Type:</strong>
                            <span>${studentData.student_type}</span>
                        </div>
                        <div class="info-item">
                            <strong>Curriculum:</strong>
                            <span>${studentData.curriculum}</span>
                        </div>
                        <div class="info-item">
                            <strong>Enrollment Status:</strong>
                            <span class="status-badge ${studentData.status === 'Active' ? 'active' : 'warning'}">
                                ${studentData.enrollment_status}
                            </span>
                        </div>
                        <div class="info-item">
                            <strong>Email:</strong>
                            <span>${studentData.email || 'N/A'}</span>
                        </div>
                        <div class="info-item">
                            <strong>Overall Average:</strong>
                            <span>${studentData.average_grade}</span>
                        </div>
                        <div class="info-item">
                            <strong>Address:</strong>
                            <span>${studentData.address}</span>
                        </div>
                    </div>
                </div>
                <div class="academic-history-section" id="academicHistorySection">
                    <h4>Academic History</h4>
                    <div id="academicHistoryContent">
                        <div class="loading">Loading academic history...</div>
                    </div>
                </div>
                <div class="student-files-section" id="studentFilesSection">
                    <h4>Student Files</h4>
                    <div id="studentFilesContent">
                        <div class="loading-files">Loading student files...</div>
                    </div>
                </div>
            </div>
        `;
        
        document.getElementById('studentProfileContent').innerHTML = basicInfoHTML;
        
        // Load academic history and files in parallel
        try {
            const [academicHistoryData, filesData] = await Promise.all([
                fetchAcademicHistory(studentId),
                fetchStudentFiles(studentId)
            ]);
            
            // Display academic history
            displayAcademicHistory(academicHistoryData);
            
            // Display student files
            displayStudentFiles(filesData);
            
        } catch (error) {
            console.error('Error loading student data:', error);
            document.getElementById('academicHistoryContent').innerHTML = 
                '<p class="error">Error loading academic history.</p>';
            document.getElementById('studentFilesContent').innerHTML = 
                '<p class="error">Error loading student files.</p>';
        }
    }

    // Helper function to fetch academic history
    async function fetchAcademicHistory(studentId) {
        const response = await fetch(`/instructor/student-subjects-history/${studentId}`);
        return await response.json();
    }

    // Helper function to fetch student files
    async function fetchStudentFiles(studentId) {
        const response = await fetch(`/instructor/student-files/${studentId}`);
        return await response.json();
    }

    // Function to display academic history
    function displayAcademicHistory(data) {
        const contentDiv = document.getElementById('academicHistoryContent');
        
        if (data.success && data.subjects && data.subjects.length > 0) {
            const subjects = data.subjects;
            let academicHistoryHTML = '';
            
            // Group subjects by year level and semester
            const groupedSubjects = {};
            subjects.forEach(subject => {
                const key = `${subject.year_level} - ${subject.semester}`;
                if (!groupedSubjects[key]) {
                    groupedSubjects[key] = [];
                }
                groupedSubjects[key].push(subject);
            });
            
            // Create HTML for each semester
            for (const [semester, semesterSubjects] of Object.entries(groupedSubjects)) {
                // 
                academicHistoryHTML += `
                    <div class="semester-section">
                        <h5 id="students_info2">${semester}</h5>
                        <div class="table-container">
                            <table class="subjects-table">
                                <thead>
                                    <tr>
                                        <th id="students_info3">Subject Code</th>
                                        <th id="students_info3">Subject Name</th>
                                        <th id="students_info3">Units</th>
                                        <th id="students_info3">Pre-requisite</th>
                                        <th id="students_info3">Grade</th>
                                    </tr>
                                </thead>
                                <tbody id="students_info">
                                    ${semesterSubjects.map(subject => `
                                        <tr>
                                            <td>${subject.subject_code}</td>
                                            <td>${subject.subject_name}</td>
                                            <td>${subject.units}</td>
                                            <td style="font-size: 12px; color: #555;">
                                                ${subject.prerequisites || '—'}
                                            </td>
                                            <td>
                                                <span id="grade-badge ${
                                                    subject.grade === 'INC' || subject.grade === 'DRP' || subject.grade === '' || subject.grade == '4.0' || subject.grade == '5.0' ? 
                                                    'grade-incomplete' : 
                                                    'grade-complete'
                                                }" style="color: ${
                                                    // Red for INC and DRP
                                                    subject.grade === 'INC' || subject.grade === 'DRP' ? 'rgba(214, 9, 9, 1)' : 
                                                    // Gray for empty/NULL
                                                    subject.grade === '' ? 'rgba(128, 128, 128, 1)' : 
                                                    // Orange for 4.0 and 5.0
                                                    subject.grade == '4.0' || subject.grade == '5.0' ? 'rgba(255, 165, 0, 1)' : 
                                                    // Green for everything else
                                                    'rgba(0, 204, 0, 1)'
                                                }">
                                                    ${subject.grade || 'N/A'}
                                                </span>
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
                // 
            }
            contentDiv.innerHTML = academicHistoryHTML;
        } else {
            contentDiv.innerHTML = '<p class="no-files">No academic history available.</p>';
        }
    }

    // Function to display student files
    function displayStudentFiles(data) {
        const contentDiv = document.getElementById('studentFilesContent');
        
        if (data.success) {
            let filesHTML = '';
            
            // 1. Student Files (FHE, Prospectus)
            if (data.studentFiles && data.studentFiles.length > 0) {
                filesHTML += `
                    <div class="files-category">
                        <h5><i class="fas fa-file-alt"></i> Academic Documents</h5>
                        <div class="files-list">
                            ${data.studentFiles.map(file => `
                                <div class="file-item">
                                    <div class="file-info">
                                        <span class="file-name">${file.type}</span>
                                        <div class="file-meta">
                                            <span class="file-type">${file.year_level}</span>
                                            <span class="file-date">${formatDate(file.upload_date)}</span>
                                        </div>
                                    </div>
                                    <div class="file-actions">
                                        <a href="/${file.file_path}" target="_blank" class="btn-view-file">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="/${file.file_path}" download class="btn-download-file">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
            
            // 2. Payment Files
            if (data.paymentFiles && data.paymentFiles.length > 0) {
                filesHTML += `
                    <div class="files-category">
                        <h5><i class="fas fa-receipt"></i> Payment Receipts</h5>
                        <div class="files-list">
                            ${data.paymentFiles.map(file => `
                                <div class="file-item">
                                    <div class="file-info">
                                        <span class="file-name">Organization Fee Receipt</span>
                                        <div class="file-meta">
                                            <span class="file-type">${file.year_level || 'N/A'}</span>
                                            <span class="file-date">${formatDate(file.uploaded_date)}</span>
                                        </div>
                                    </div>
                                    <div class="file-actions">
                                        <a href="/${file.receipt_url}" target="_blank" class="btn-view-file">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="/${file.receipt_url}" download class="btn-download-file">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
            
            // 3. Important Documents
            if (data.importantDocuments && data.importantDocuments.length > 0) {
                filesHTML += `
                    <div class="files-category">
                        <h5><i class="fas fa-file-certificate"></i> Requirements & Credentials</h5>
                        <div class="files-list">
                            ${data.importantDocuments.map(file => `
                                <div class="file-item">
                                    <div class="file-info">
                                        <span class="file-name">${getDocumentName(file.type)}</span>
                                        <div class="file-meta">
                                            <span class="file-type">${file.year_level}</span>
                                            <span class="file-date">${formatDate(file.upload_date)}</span>
                                        </div>
                                    </div>
                                    <div class="file-actions">
                                        <a href="/${file.file_path}" target="_blank" class="btn-view-file">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="/${file.file_path}" download class="btn-download-file">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
            
            // If no files found
            if (!filesHTML) {
                filesHTML = '<div class="no-files">No files available for this student.</div>';
            }
            
            contentDiv.innerHTML = filesHTML;
        } else {
            contentDiv.innerHTML = '<p class="error">Failed to load student files.</p>';
        }
    }

    // Helper function to format date
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    // Helper function to get document display name
    function getDocumentName(type) {
        const documentNames = {
            'FORM138A': 'Form 138-A (Report Card)',
            'GOOD_MORAL': 'Good Moral Certificate',
            'PSA_NSO': 'PSA/NSO Birth Certificate',
            'ID_PICTURE': 'ID Picture'
        };
        return documentNames[type] || type;
    }

    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    // Navigation functionality
    const menuItems = document.querySelectorAll('.menu-item');
    const contentSections = document.querySelectorAll('.content-section');
    const pageTitle = document.querySelector('.page-title');

    // Check if enrollment requests section is active on page load
    const enrollmentRequestsSection = document.getElementById('enrollment-requests-section');
    console.log('Enrollment requests section found:', !!enrollmentRequestsSection);
    if (enrollmentRequestsSection && enrollmentRequestsSection.classList.contains('active')) {
        console.log('Enrollment requests section is active, initializing...');
        setTimeout(initializeEnrollmentRequests, 100);
    }

    // Also listen for section changes
    const observer2 = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const target = mutation.target;
                if (target.id === 'enrollment-requests-section' && target.classList.contains('active')) {
                    console.log('Enrollment requests section became active, initializing...');
                    setTimeout(initializeEnrollmentRequests, 100);
                }
            }
        });
    });

    if (enrollmentRequestsSection) {
        observer2.observe(enrollmentRequestsSection, {
            attributes: true,
            attributeFilter: ['class']
        });
    }

    // Check if input grades section is active on page load
    const inputGradesSection = document.getElementById('input-grades-section');
    console.log('Input grades section found:', !!inputGradesSection);
    if (inputGradesSection && inputGradesSection.classList.contains('active')) {
        console.log('Input grades section is active, initializing...');
        setTimeout(initializeInputGrades, 100);
    }

    // Also listen for section changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const target = mutation.target;
                if (target.id === 'input-grades-section' && target.classList.contains('active')) {
                    console.log('Input grades section became active, initializing...');
                    setTimeout(initializeInputGrades, 100);
                }
            }
        });
    });

    if (inputGradesSection) {
        observer.observe(inputGradesSection, {
            attributes: true,
            attributeFilter: ['class']
        });
    }
    
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            // if (this.id === 'logout-btn') {
            //     if (confirm('Are you sure you want to logout?')) {
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
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.classList.add('active');
                
                // Update page title
                const sectionName = this.querySelector('.menu-text').textContent;
                if (pageTitle) {
                    pageTitle.textContent = sectionName + ' | Instructor';
                }
                
                // Initialize specific section functionality
                initializeSection(this.getAttribute('data-section'));
            }
        });
    });

    
    // Quick Actions
    const quickActionCards = document.querySelectorAll('.quick-action-card');
    quickActionCards.forEach(card => {
        card.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            handleQuickAction(action);
        });
    });
    
    function handleQuickAction(action) {
        switch(action) {
            case 'create-assignment':
                document.querySelector('[data-section="enrollment-requests"]').click();
                break;
            case 'input-grades':
                // Find and click the input-grades menu item
                const inputGradesMenuItem = document.querySelector('[data-section="input-grades"]');
                if (inputGradesMenuItem) {
                    inputGradesMenuItem.click();
                }
                break;
            case 'view-students':
                document.querySelector('[data-section="students"]').click();
                break;
            case 'manage-grades':
                document.querySelector('[data-section="grades"]').click();
                break;
            case 'upload-materials':
                showNotification('Upload materials feature coming soon!', 'info');
                break;
            case 'upload-materials':
                showNotification('Upload materials feature coming soon!', 'info');
                break;
        }
    }

    // Initialize section-specific functionality
    function initializeSection(sectionName) {
        switch(sectionName) {
            case 'input-grades':
                console.log('Initializing input grades section...');
                setTimeout(initializeInputGrades, 100);
                break;
            case 'enrollment-requests':
                console.log('Initializing enrollment requests section...');
                setTimeout(initializeEnrollmentRequests, 100);
                break;
            case 'dashboard':
                // Initialize dashboard if needed
                break;
            case 'courses':
                // Initialize courses if needed
                break;
            // Add other sections as needed
        }
    }
    
    // Create Assignment Button
    const createAssignmentBtn = document.getElementById('createAssignmentBtn');
    if (createAssignmentBtn) {
        createAssignmentBtn.addEventListener('click', function() {
            showAssignmentCreationModal();
        });
    }
    
    function showAssignmentCreationModal() {
        // In a real implementation, this would show a modal
        // For now, we'll show a notification and simulate the process
        showNotification('Opening assignment creation form...', 'info');
        
        // Simulate modal opening
        setTimeout(() => {
            const modalHTML = `
                <div class="modal-overlay active" id="assignmentModal">
                    <div class="modal-container" style="max-width: 600px;">
                        <div class="modal-header">
                            <h3>Create New Assignment</h3>
                            <button class="close-modal" onclick="closeAssignmentModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form class="modal-form">
                            <div class="form-group">
                                <label for="assignmentTitle" class="form-label">Assignment Title</label>
                                <input type="text" id="assignmentTitle" class="form-control" placeholder="Enter assignment title" required>
                            </div>
                            <div class="form-group">
                                <label for="assignmentCourse" class="form-label">Course</label>
                                <select id="assignmentCourse" class="form-control" required>
                                    <option value="">Select Course</option>
                                    <option value="IT 373">IT 373 - Software Engineering</option>
                                    <option value="CS 301">CS 301 - Data Structures</option>
                                    <option value="IT 401">IT 401 - Web Development</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="assignmentDueDate" class="form-label">Due Date</label>
                                <input type="datetime-local" id="assignmentDueDate" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="assignmentDescription" class="form-label">Description</label>
                                <textarea id="assignmentDescription" class="form-control" rows="4" placeholder="Enter assignment description"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="assignmentPoints" class="form-label">Total Points</label>
                                <input type="number" id="assignmentPoints" class="form-control" placeholder="100" min="1" required>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-cancel" onclick="closeAssignmentModal()">Cancel</button>
                                <button type="submit" class="btn-primary">Create Assignment</button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            
            // Add modal to page
            const modalContainer = document.createElement('div');
            modalContainer.innerHTML = modalHTML;
            document.body.appendChild(modalContainer);
            
            // Handle form submission
            const form = modalContainer.querySelector('form');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                createNewAssignment(this);
            });
        }, 500);
    }
    
    // Close assignment modal function (needs to be global)
    window.closeAssignmentModal = function() {
        const modal = document.getElementById('assignmentModal');
        if (modal) {
            modal.remove();
        }
    };
    
    function createNewAssignment(form) {
        const formData = new FormData(form);
        const assignmentData = {
            title: form.querySelector('#assignmentTitle').value,
            course: form.querySelector('#assignmentCourse').value,
            dueDate: form.querySelector('#assignmentDueDate').value,
            description: form.querySelector('#assignmentDescription').value,
            points: form.querySelector('#assignmentPoints').value
        };
        
        // Show loading state
        const submitBtn = form.querySelector('.btn-primary');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
        submitBtn.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            showNotification('Assignment created successfully!', 'success');
            closeAssignmentModal();
            
            // In a real app, you would refresh the assignments list
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 1500);
    }
    
    // Profile Edit Functionality
    const editProfileBtn = document.getElementById('edit-profile-btn');
    const cancelEditBtn = document.getElementById('cancel-edit');
    const profileForm = document.getElementById('profile-form');
    const formActions = document.getElementById('form-actions');
    
    if (editProfileBtn && profileForm) {
        let originalValues = {};
        
        // Store original values
        const editableFields = profileForm.querySelectorAll('input, textarea');
        editableFields.forEach(field => {
            originalValues[field.id] = field.value;
        });
        
        editProfileBtn.addEventListener('click', function() {
            enableProfileEditing();
        });
        
        if (cancelEditBtn) {
            cancelEditBtn.addEventListener('click', function() {
                disableProfileEditing(originalValues);
            });
        }
        
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveProfileChanges();
        });
    }
    
    function enableProfileEditing() {
        const editableFields = document.querySelectorAll('#profile-form input, #profile-form textarea');
        editableFields.forEach(field => {
            field.readOnly = false;
            field.style.background = 'white';
            field.style.color = '#374151';
            
            if (document.body.classList.contains('dark-mode')) {
                field.style.background = '#1e293b';
                field.style.color = '#e2e8f0';
            }
        });
        
        if (formActions) {
            formActions.style.display = 'flex';
        }
        if (editProfileBtn) {
            editProfileBtn.style.display = 'none';
        }
    }
    
    function disableProfileEditing(originalValues) {
        const editableFields = document.querySelectorAll('#profile-form input, #profile-form textarea');
        editableFields.forEach(field => {
            if (originalValues && originalValues[field.id]) {
                field.value = originalValues[field.id];
            }
            field.readOnly = true;
            field.style.background = '#f8fafc';
            field.style.color = '#64748b';
            
            if (document.body.classList.contains('dark-mode')) {
                field.style.background = 'rgba(255, 255, 255, 0.05)';
                field.style.color = '#94a3b8';
            }
        });
        
        if (formActions) {
            formActions.style.display = 'none';
        }
        if (editProfileBtn) {
            editProfileBtn.style.display = 'flex';
        }
    }

    async function saveProfileChanges() {
        // Show loading state
        const submitBtn = document.querySelector('#profile-form .btn-primary');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        submitBtn.disabled = true;
        
        // Simulate API call to save profile
        setTimeout(() => {
            showNotification('Profile updated successfully!', 'success');
            disableProfileEditing();
            
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 1500);

        // Collect form data
        const formData = {
            firstName: document.getElementById('firstName').value,
            lastName: document.getElementById('lastName').value,
            middleName: document.getElementById('middleName').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            department: document.getElementById('department').value,
            office: document.getElementById('office').value,
            bio: document.getElementById('bio').value,
        };

        
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Send the request to the server
            const response = await fetch('/instructor/update-profile', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            let A = ''+data.data.firstname +' '+data.data.lastname;
            let B = ''+data.data.email;
            document.getElementById('profile-form2').textContent = A;
            document.getElementById('profile-form3').textContent = B;

            // Disable editing mode
                disableProfileEditing(originalValues);
                
                // Update the page data without reloading (optional)
                // updateProfileDisplay(data.data);
                
            
        
    }
    
    // function saveProfileChanges() {
    //     // Show loading state
    //     const submitBtn = document.querySelector('#profile-form .btn-primary');
    //     const originalText = submitBtn.innerHTML;
    //     submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    //     submitBtn.disabled = true;
        
    //     // Simulate API call to save profile
    //     setTimeout(() => {
    //         showNotification('Profile updated successfully!', 'success');
    //         disableProfileEditing();
            
    //         submitBtn.innerHTML = originalText;
    //         submitBtn.disabled = false;
    //     }, 1500);
    // }
    
    // Grade Management
    const gradeInputs = document.querySelectorAll('.grade-input');
    gradeInputs.forEach(input => {
        input.addEventListener('change', function() {
            const row = this.closest('tr');
            const saveBtn = row.querySelector('.btn-primary');
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save*';
            }
        });
    });
    
    // Save grade buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-primary') && e.target.closest('tr')) {
            const row = e.target.closest('tr');
            const inputs = row.querySelectorAll('.grade-input');
            const grades = {
                assignments: parseFloat(inputs[0].value) || 0,
                midterm: parseFloat(inputs[1].value) || 0,
                final: parseFloat(inputs[2].value) || 0
            };
            
            // Validate grades
            if (Object.values(grades).some(grade => grade < 0 || grade > 100)) {
                showNotification('Please enter valid grades between 0 and 100', 'error');
                return;
            }
            
            e.target.disabled = true;
            e.target.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving';
            
            // Simulate saving to server
            setTimeout(() => {
                e.target.textContent = 'Saved';
                showNotification('Grade updated successfully!', 'success');
                
                // Recalculate overall grade
                calculateOverallGrade(row, grades);
            }, 1000);
        }
    });
    
    function calculateOverallGrade(row, grades) {
        const overall = (grades.assignments * 0.3) + (grades.midterm * 0.3) + (grades.final * 0.4);
        const overallCell = row.querySelector('td:nth-child(6)');
        if (overallCell) {
            overallCell.textContent = Math.round(overall) + '%';
            
            // Add color coding based on grade
            if (overall >= 90) {
                overallCell.style.color = '#10b981';
                overallCell.style.fontWeight = '600';
            } else if (overall >= 80) {
                overallCell.style.color = '#3b82f6';
                overallCell.style.fontWeight = '600';
            } else if (overall >= 70) {
                overallCell.style.color = '#f59e0b';
                overallCell.style.fontWeight = '600';
            } else {
                overallCell.style.color = '#ef4444';
                overallCell.style.fontWeight = '600';
            }
        }
    }
    
    // Attendance buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-sm') && e.target.textContent.includes('Attendance')) {
            const scheduleItem = e.target.closest('.schedule-item');
            const course = scheduleItem.querySelector('.schedule-course').textContent;
            showAttendanceModal(course);
        }
    });
    
    function showAttendanceModal(course) {
        showNotification(`Taking attendance for ${course}`, 'info');
        
        // Simulate attendance modal
        setTimeout(() => {
            const modalHTML = `
                <div class="modal-overlay active" id="attendanceModal">
                    <div class="modal-container" style="max-width: 800px;">
                        <div class="modal-header">
                            <h3>Take Attendance - ${course}</h3>
                            <button class="close-modal" onclick="closeAttendanceModal()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="modal-content">
                            <div class="attendance-list">
                                <div class="attendance-item">
                                    <span class="student-name">John Smith (2023001)</span>
                                    <label class="attendance-toggle">
                                        <input type="checkbox" checked>
                                        <span class="slider"></span>
                                        <span class="toggle-label">Present</span>
                                    </label>
                                </div>
                                <div class="attendance-item">
                                    <span class="student-name">Sarah Johnson (2023002)</span>
                                    <label class="attendance-toggle">
                                        <input type="checkbox" checked>
                                        <span class="slider"></span>
                                        <span class="toggle-label">Present</span>
                                    </label>
                                </div>
                                <div class="attendance-item">
                                    <span class="student-name">Michael Brown (2023003)</span>
                                    <label class="attendance-toggle">
                                        <input type="checkbox">
                                        <span class="slider"></span>
                                        <span class="toggle-label">Absent</span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-cancel" onclick="closeAttendanceModal()">Cancel</button>
                                <button type="button" class="btn-primary" onclick="submitAttendance()">Submit Attendance</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            const modalContainer = document.createElement('div');
            modalContainer.innerHTML = modalHTML;
            document.body.appendChild(modalContainer);
        }, 500);
    }
    
    // Close attendance modal function (needs to be global)
    window.closeAttendanceModal = function() {
        const modal = document.getElementById('attendanceModal');
        if (modal) {
            modal.remove();
        }
    };
    
    // Submit attendance function (needs to be global)
    window.submitAttendance = function() {
        const submitBtn = document.querySelector('#attendanceModal .btn-primary');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
        
        setTimeout(() => {
            showNotification('Attendance submitted successfully!', 'success');
            closeAttendanceModal();
        }, 1500);
    };
    
    // Search functionality
    const searchInput = document.querySelector('.search-input');
    const searchBtn = document.querySelector('.search-btn');
    
    function performSearch() {
        const searchTerm = searchInput.value.trim();
        if (searchTerm) {
            // Show loading state
            if (searchBtn) {
                searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            }
            
            // Simulate search
            setTimeout(() => {
                showNotification(`Searching for: ${searchTerm}`, 'info');
                if (searchBtn) {
                    searchBtn.innerHTML = '<i class="fas fa-search"></i>';
                }
            }, 1000);
        }
    }
    
    if (searchBtn) {
        searchBtn.addEventListener('click', performSearch);
    }
    
    if (searchInput) {
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
    }

    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth < 992 && sidebar) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickInsideToggle = sidebarToggle && sidebarToggle.contains(event.target);
            
            if (!isClickInsideSidebar && !isClickInsideToggle && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        }
    });
    
    // Responsive behavior
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992 && sidebar) {
            sidebar.classList.remove('active');
        }
    });
    
    // Notification function
    function showNotification(message, type = 'info') {
        // Use SweetAlert2 if available, otherwise use browser alert
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
            
            Toast.fire({
                icon: type,
                title: message
            });
        } else {
            // Fallback to custom notification
            const notification = document.createElement('div');
            notification.className = `custom-notification ${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas fa-${getNotificationIcon(type)}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            // Add styles if not already added
            if (!document.querySelector('#notification-styles')) {
                const styles = document.createElement('style');
                styles.id = 'notification-styles';
                styles.textContent = `
                    .custom-notification {
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: white;
                        padding: 16px 20px;
                        border-radius: 12px;
                        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
                        z-index: 10000;
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        max-width: 300px;
                        animation: slideInRight 0.3s ease;
                    }
                    .custom-notification.success {
                        background: #10b981;
                        color: white;
                    }
                    .custom-notification.error {
                        background: #ef4444;
                        color: white;
                    }
                    .custom-notification.info {
                        background: #3b82f6;
                        color: white;
                    }
                    .custom-notification.warning {
                        background: #f59e0b;
                        color: white;
                    }
                    @keyframes slideInRight {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                `;
                document.head.appendChild(styles);
            }
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
    }
    
    function getNotificationIcon(type) {
        switch(type) {
            case 'success': return 'check-circle';
            case 'error': return 'exclamation-circle';
            case 'warning': return 'exclamation-triangle';
            default: return 'info-circle';
        }
    }
    
    // Initialize any additional components
    initializeCourseManagement();
    initializeStudentFilters();
});

function initializeProfilePictureUpload() {
    const avatarContainer = document.getElementById('avatar-container');
    const changePhotoBtn = document.getElementById('change-photo-btn');
    const removePhotoBtn = document.getElementById('remove-photo-btn');
    const profilePictureInput = document.getElementById('profile-picture-input');
    const profileAvatar = document.getElementById('profile-avatar');
    const avatarLoadingOverlay = document.getElementById('avatar-loading-overlay');
    
    if (!avatarContainer || !profilePictureInput) return;
    
    // Open file dialog when clicking avatar or button
    [avatarContainer, changePhotoBtn].forEach(element => {
        if (element) {
            element.addEventListener('click', function(e) {
                e.preventDefault();
                profilePictureInput.click();
            });
        }
    });
    
    // Handle file selection
    profilePictureInput.addEventListener('change', handleProfilePictureSelect);
    
    // Handle remove photo button
    if (removePhotoBtn) {
        removePhotoBtn.addEventListener('click', handleRemoveProfilePicture);
    }
}

function handleProfilePictureSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    // Validate file type and size
    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    const maxSize = 2 * 1024 * 1024; // 2MB
    
    if (!validTypes.includes(file.type)) {
        showNotification('Please select a valid image file (JPEG, PNG, JPG, GIF).', 'error');
        return;
    }
    
    if (file.size > maxSize) {
        showNotification('Image size must be less than 2MB.', 'error');
        return;
    }
    
    // Show preview and confirmation
    showProfilePicturePreview(file);
}

function showProfilePicturePreview(file) {
    // Create preview modal
    const previewContainer = document.createElement('div');
    previewContainer.className = 'avatar-preview-container';
    previewContainer.innerHTML = `
        <div class="avatar-preview-content">
            <h3>Preview Profile Picture</h3>
            <p>Is this how you want your profile picture to look?</p>
            <img src="" alt="Preview" class="avatar-preview-image" id="avatar-preview-image">
            <div class="avatar-preview-actions">
                <button class="avatar-preview-btn avatar-preview-cancel" id="cancel-upload">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
                <button class="avatar-preview-btn avatar-preview-confirm" id="confirm-upload">
                    <i class="fas fa-check"></i>
                    Upload
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(previewContainer);
    
    // Show the preview image
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('avatar-preview-image').src = e.target.result;
    };
    reader.readAsDataURL(file);
    
    // Show the modal
    setTimeout(() => {
        previewContainer.classList.add('active');
    }, 10);
    
    // Handle cancel button
    document.getElementById('cancel-upload').addEventListener('click', function() {
        previewContainer.classList.remove('active');
        setTimeout(() => {
            previewContainer.remove();
            // Clear the file input
            document.getElementById('profile-picture-input').value = '';
        }, 300);
    });
    
    // Handle confirm button
    document.getElementById('confirm-upload').addEventListener('click', function() {
        uploadProfilePicture(file);
        previewContainer.classList.remove('active');
        setTimeout(() => {
            previewContainer.remove();
        }, 300);
    });
    
    // Close on background click
    previewContainer.addEventListener('click', function(e) {
        if (e.target === previewContainer) {
            document.getElementById('cancel-upload').click();
        }
    });
}

async function uploadProfilePicture(file) {
    const avatarLoadingOverlay = document.getElementById('avatar-loading-overlay');
    const profileAvatar = document.getElementById('profile-avatar');
    const removePhotoBtn = document.getElementById('remove-photo-btn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    try {
        // Show loading overlay
        avatarLoadingOverlay.classList.add('active');
        
        // Create FormData
        const formData = new FormData();
        formData.append('profile_picture', file);
        formData.append('_token', csrfToken);
        
        // Send upload request
        const response = await fetch('/instructor/upload-profile-picture', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update the profile image
            profileAvatar.src = data.profile_url;
            profileAvatar.classList.add('updated');
            
            // Show success message
            showNotification(data.message, 'success');
            
            // Show remove button if it doesn't exist
            if (!removePhotoBtn && data.profile_picture !== 'default.png') {
                addRemovePhotoButton();
            }
            
            // Also update sidebar avatar if it exists
            updateSidebarAvatar(data.profile_url);
            
            // Clear file input
            document.getElementById('profile-picture-input').value = '';
            
            // Remove animation class after animation completes
            setTimeout(() => {
                profileAvatar.classList.remove('updated');
            }, 500);
            
        } else {
            // Show error message
            let errorMessage = data.message;
            if (data.errors && data.errors.profile_picture) {
                errorMessage = data.errors.profile_picture[0];
            }
            showNotification(errorMessage, 'error');
        }
        
    } catch (error) {
        console.error('Upload error:', error);
        showNotification('Failed to upload profile picture. Please try again.', 'error');
    } finally {
        // Hide loading overlay
        avatarLoadingOverlay.classList.remove('active');
    }
}

async function handleRemoveProfilePicture() {
    if (!confirm('Are you sure you want to remove your profile picture?')) {
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const profileAvatar = document.getElementById('profile-avatar');
    const removePhotoBtn = document.getElementById('remove-photo-btn');
    const avatarLoadingOverlay = document.getElementById('avatar-loading-overlay');
    
    try {
        // Show loading overlay
        if (avatarLoadingOverlay) {
            avatarLoadingOverlay.classList.add('active');
        }
        
        // Send remove request
        const response = await fetch('/instructor/remove-profile-picture', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ _token: csrfToken })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update the profile image
            profileAvatar.src = data.profile_url;
            profileAvatar.classList.add('updated');
            
            // Show success message
            showNotification(data.message, 'success');
            
            // Remove the remove button
            if (removePhotoBtn) {
                removePhotoBtn.remove();
            }
            
            // Also update sidebar avatar
            updateSidebarAvatar(data.profile_url);
            
            // Remove animation class after animation completes
            setTimeout(() => {
                profileAvatar.classList.remove('updated');
            }, 500);
            
        } else {
            showNotification(data.message || 'Failed to remove profile picture.', 'error');
        }
        
    } catch (error) {
        console.error('Remove error:', error);
        showNotification('Failed to remove profile picture. Please try again.', 'error');
    } finally {
        // Hide loading overlay
        if (avatarLoadingOverlay) {
            avatarLoadingOverlay.classList.remove('active');
        }
    }
}

function addRemovePhotoButton() {
    const avatarSection = document.querySelector('.profile-avatar-section');
    if (!avatarSection) return;
    
    const removeBtn = document.createElement('button');
    removeBtn.className = 'btn-secondary btn-remove-avatar';
    removeBtn.id = 'remove-photo-btn';
    removeBtn.innerHTML = '<i class="fas fa-trash"></i> Remove Photo';
    removeBtn.style.marginTop = '8px';
    
    removeBtn.addEventListener('click', handleRemoveProfilePicture);
    
    // Insert after the Change Photo button
    const changePhotoBtn = document.getElementById('change-photo-btn');
    if (changePhotoBtn) {
        changePhotoBtn.parentNode.insertBefore(removeBtn, changePhotoBtn.nextSibling);
    }
}

function updateSidebarAvatar(imageUrl) {
    // Update sidebar avatar if it exists
    const sidebarAvatar = document.querySelector('.sidebar .user-avatar');
    if (sidebarAvatar) {
        sidebarAvatar.src = imageUrl;
    }
    
    // Update any other avatar on the page
    const allAvatars = document.querySelectorAll('.user-avatar, .avatar-initials');
    allAvatars.forEach(avatar => {
        if (avatar.classList.contains('user-avatar')) {
            avatar.src = imageUrl;
        }
    });
}

// Handle broken images
function handleImageErrors() {
    document.addEventListener('error', function(e) {
        if (e.target.tagName === 'IMG' && e.target.classList.contains('profile-avatar')) {
            const firstName = document.getElementById('firstName')?.value || 'Instructor';
            const lastName = document.getElementById('lastName')?.value || '';
            const name = encodeURIComponent(firstName + '+' + lastName);
            e.target.src = `https://ui-avatars.com/api/?name=${name}&background=4361ee&color=fff&size=150`;
            e.target.onerror = null; // Prevent infinite loop
        }
    }, true);
}

// Additional initialization functions
function initializeCourseManagement() {
    // Add any course management specific initialization here
    console.log('Course management initialized');
}

function initializeStudentFilters() {
    const studentSearch = document.querySelector('.student-filters input');
    const studentSelect = document.querySelector('.student-filters select');
    
    if (studentSearch) {
        studentSearch.addEventListener('input', function() {
            filterStudents();
        });
    }
    
    if (studentSelect) {
        studentSelect.addEventListener('change', function() {
            filterStudents();
        });
    }
    
    function filterStudents() {
        // In a real implementation, this would filter the student table
        console.log('Filtering students...');
    }
}

// Make functions available globally if needed
window.instructorDashboard = {
    showNotification: showNotification,
    // Add other functions as needed
};