// Initialize Supabase at the very top of your script
        const supabaseUrl = "https://dfvapjrkotprotpbpeju.supabase.co";
        const supabaseAnonKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImRmdmFwanJrb3Rwcm90cGJwZWp1Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTcxNDg1OTMsImV4cCI6MjA3MjcyNDU5M30.Hou-GtB-P8qJ4fxXbC-VtyaCkDpf5Kr01DD9aSckhiU";

        // Create Supabase client
        const { createClient } = supabase;
        const supabaseClient = createClient(supabaseUrl, supabaseAnonKey);

        // This is for the enrollment status
        // Function to set up real-time subscription
function setupRealtimeSubscription2() {
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
async function insertsupabase2(){
    const data = {
        table_name: 'status',  // make sure these variables are defined
        operation: 'RESTART'
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


        // Function to fetch notification count
        // async function fetchNotificationCount() {
        //     try {
        //         const { count, error } = await supabaseClient
        //             .from('notifications')
        //             .select('*', { count: 'exact', head: true });

        //         if (error) {
        //             console.error('Error fetching notification count:', error);
        //             // Try fallback method
        //             fetchNotificationCountFallback();
        //             return;
        //         }

        //         // Update the notification count using the ID
        //         const notificationElement = document.getElementById('notificationCount');
        //         if (notificationElement) {
        //             notificationElement.textContent = count;
        //         }
        //     } catch (err) {
        //         console.error('Error in fetchNotificationCount:', err);
        //         // Try fallback method
        //         fetchNotificationCountFallback();
        //     }
        // }

        // Alternative method using fetch if Supabase JS isn't working
        // async function fetchNotificationCountFallback() {
        //     try {
        //         const response = await fetch(`https://dfvapjrkotprotpbpeju.supabase.co/rest/v1/notifications?select=*`, {
        //             headers: {
        //                 'apikey': 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImRmdmFwanJrb3Rwcm90cGJwZWp1Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTcxNDg1OTMsImV4cCI6MjA3MjcyNDU5M30.Hou-GtB-P8qJ4fxXbC-VtyaCkDpf5Kr01DD9aSckhiU',
        //                 'Authorization': 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImRmdmFwanJrb3Rwcm90cGJwZWp1Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTcxNDg1OTMsImV4cCI6MjA3MjcyNDU5M30.Hou-GtB-P8qJ4fxXbC-VtyaCkDpf5Kr01DD9aSckhiU'
        //             }
        //         });
                
        //         if (!response.ok) {
        //             throw new Error('Network response was not ok');
        //         }
                
        //         const data = await response.json();
        //         const count = data.length;
                
        //         const notificationElement = document.getElementById('notificationCount');
        //         if (notificationElement) {
        //             notificationElement.textContent = count;
        //         }
        //     } catch (err) {
        //         console.error('Error in fetchNotificationCountFallback:', err);
        //         // Set a default value if both methods fail
        //         const notificationElement = document.getElementById('notificationCount');
        //         if (notificationElement) {
        //             notificationElement.textContent = '0';
        //         }
        //     }
        // }

        

        async function insertsupabase(){
            const data = {
                table_name: 'subject',  // make sure these variables are defined
                operation: 'INSERT',
                record_id: 'ID'
            };
            // Create AbortController for timeout (similar to PHP's 10s timeout)
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000);
            try {
            const response = await fetch(`${supabaseUrl}/rest/v1/notifications`, {
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

        async function updateSupabase(recordId) {
            const data = {
                table_name: 'subject',
                operation: 'UPDATE',
                record_id: recordId,
            };

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000);

            try {
                const response = await fetch(`${supabaseUrl}/rest/v1/notifications`, {
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
                console.log('Update notification sent:', responseData);
                return true;
            } catch (error) {
                if (error.name === 'AbortError') {
                    console.error('Update request timed out');
                } else {
                    console.error('Error updating Supabase:', error);
                }
                return false;
            }
        }

        async function deletesupabase(){
            const data = {
                table_name: 'subject',  // make sure these variables are defined
                operation: 'DELETE',
                record_id: 'ID'
            };
            // Create AbortController for timeout (similar to PHP's 10s timeout)
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 10000);
            try {
            const response = await fetch(`${supabaseUrl}/rest/v1/notifications`, {
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

        // Function to set up real-time subscription
        function setupRealtimeSubscription() {
            const subscription = supabaseClient
                .channel('notifications-changes')
                .on('postgres_changes', 
                    { 
                        event: '*',  // Listen for all changes (INSERT, UPDATE, DELETE)
                        schema: 'public', 
                        table: 'notifications' 
                    }, 
                    (payload) => {
                        // Refresh data based on the operation type
                        if (payload.new && payload.new.table_name === 'subject') {
                            if (payload.new.operation === 'INSERT') {
                                // showNotification('New subject has been added!');
                            } else if (payload.new.operation === 'DELETE') {
                                // showNotification('Subject has been deleted!');
                            } else if (payload.new.operation === 'UPDATE') {
                                // showNotification('Subject has been updated!');
                            }
                            // Refresh the notification count when changes occur
                            // fetchNotificationCount();
                            loadSubjects();
                            loadStatistics();
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


        // Function to show notification
        function showNotification(message) {
        // Create notification element if it doesn't exist
            if (!$('#realtime-notification').length) {
                $('body').append(`
                <div id="realtime-notification" class="alert alert-info alert-dismissible fade show" 
                    style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: none;">
                    <span id="notification-message"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                `);
            }
            
            // Show notification
            $('#notification-message').text(message);
            $('#realtime-notification').fadeIn();
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                $('#realtime-notification').fadeOut();
            }, 5000);
        }


    // Simplified CSV Upload JavaScript
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('csvFileInput');
const uploadBtn = document.getElementById('uploadCsvBtn');
const resultMessage = document.getElementById('resultMessage');
const recentUploadsTable = document.getElementById('recentUploads');

// File upload functionality
if (dropZone) {
    dropZone.addEventListener('click', () => fileInput.click());
    
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
    
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('drag-over');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            updateFileInfo(e.dataTransfer.files[0]);
        }
    });
}

// File input change
if (fileInput) {
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length) {
            updateFileInfo(e.target.files[0]);
        }
    });
}

// Update file info display
function updateFileInfo(file) {
    if (file && (file.type === 'text/csv' || file.name.endsWith('.csv'))) {
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = formatSize(file.size);
        document.getElementById('fileInfo').classList.remove('d-none');
        uploadBtn.disabled = false;
    } else {
        alert('Please select a CSV file.');
        resetFile();
    }
}

// Format file size
function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' Bytes';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}

// Reset file selection
function resetFile() {
    fileInput.value = '';
    document.getElementById('fileInfo').classList.add('d-none');
    uploadBtn.disabled = true;
    hideResult();
}

// Upload CSV file
async function uploadCSV() {
    if (!fileInput.files.length) return;
    
    const formData = new FormData();
    formData.append('csvFile', fileInput.files[0]);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    
    // Show loading state
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Uploading...';
    
    try {
        const response = await fetch('/upload-csv', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        // Show result
        showResult(data.message, data.success ? 'success' : 'danger');
        
        if (data.success) {
            resetFile();
            loadRecentUploads(); // Refresh the recent uploads table
        }
        
    } catch (error) {
        showResult('Upload failed. Please try again.', 'danger');
        console.error('Upload error:', error);
    } finally {
        // Reset button
        uploadBtn.disabled = false;
        uploadBtn.textContent = 'Upload CSV';
    }
}

// Show result message
function showResult(message, type) {
    resultMessage.innerHTML = message;
    resultMessage.className = `alert alert-${type}`;
    resultMessage.classList.remove('d-none');
    
    // Auto-hide success messages after 5 seconds
    if (type === 'success') {
        setTimeout(() => resultMessage.classList.add('d-none'), 5000);
    }
}

// Hide result message
function hideResult() {
    resultMessage.classList.add('d-none');
}

// Load recent uploads
async function loadRecentUploads() {
    if (!recentUploadsTable) return;
    
    try {
        const response = await fetch('/admin/recent-csv-uploads');
        const data = await response.json();
        
        if (data.success && data.uploads.length > 0) {
            let html = '';
            data.uploads.forEach(upload => {
                const statusClass = upload.status === 'success' ? 'success' : 
                                 upload.status === 'partial' ? 'warning' : 'danger';
                
                html += `
                        <tr>
                            <td style="white-space: nowrap;">
                                <i class="fas fa-file-csv text-primary me-2"></i>
                                ${upload.file_name}
                            </td>
                            <td style="white-space: nowrap;">${upload.date}</td>
                            <td style="white-space: nowrap;">${upload.records}/${upload.total}</td>
                            <td style="white-space: nowrap;">
                                <span class="badge bg-${statusClass}">${upload.status}</span>
                            </td>
                            <td id="button-container">
                                <a href="/admin/download-csv/${upload.id}" class="btn btn-sm btn-outline-primary" 
                                onclick="event.stopPropagation(); return true;">
                                    <i class="fas fa-download"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-info" onclick="showUploadDetails(${upload.id})">
                                    <i class="fas fa-info-circle"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteUpload(${upload.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        `;
            });
            recentUploadsTable.innerHTML = html;
        } else {
            recentUploadsTable.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        No recent uploads found
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error loading recent uploads:', error);
        recentUploadsTable.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4 text-danger">
                    Failed to load recent uploads
                </td>
            </tr>
        `;
    }
}

// Show upload details
async function showUploadDetails(id) {
    try {
        const response = await fetch(`/admin/upload-details/${id}`);
        const data = await response.json();
        
        if (data.success) {
            // Format the details from data.upload object
            const upload = data.upload;
            let detailsText = `Upload Details:\n\n`;
            detailsText += `File Name: ${upload.file_name}\n`;
            detailsText += `Date: ${upload.date}\n`;
            detailsText += `Total Records: ${upload.total_records}\n`;
            detailsText += `Inserted: ${upload.inserted_records}\n`;
            detailsText += `Updated: ${upload.updated_records}\n`;
            detailsText += `Failed: ${upload.failed_records}\n`;
            detailsText += `Status: ${upload.status}\n`;
            
            // Add errors if any
            if (upload.error_log && upload.error_log.length > 0) {
                detailsText += `\nErrors:\n`;
                upload.error_log.forEach(error => {
                    detailsText += `• ${error}\n`;
                });
            }
            
            alert(detailsText);
        } else {
            alert('Error loading details: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error loading details:', error);
        alert('Error loading details. Please try again.');
    }
}

// Delete upload
async function deleteUpload(id) {
    if (!confirm('Are you sure you want to delete this upload record and file?')) return;
    
    try {
        const response = await fetch(`/admin/delete-upload/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Upload deleted successfully');
            loadRecentUploads(); // Refresh table
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error deleting upload');
    }
}


    document.addEventListener('DOMContentLoaded', function() {        
        
        // Theme Toggle
        const themeToggleBtn = document.getElementById('themeToggle');
        const body = document.body;
        
        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme') || 
                          (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        
        // Apply the saved theme
        if (savedTheme === 'dark') {
            body.classList.add('dark-theme');
            themeToggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
        }
        
        // Theme toggle button click event
        themeToggleBtn.addEventListener('click', function() {
            body.classList.toggle('dark-theme');
            
            if (body.classList.contains('dark-theme')) {
                localStorage.setItem('theme', 'dark');
                themeToggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                localStorage.setItem('theme', 'light');
                themeToggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
            }
        });

    
    // Attach upload function to button
    if (uploadBtn) {
        uploadBtn.addEventListener('click', uploadCSV);
    }
    
    // Attach remove file button
    const removeBtn = document.getElementById('removeFile');
    if (removeBtn) {
        removeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            resetFile();
        });
    }
    
    // Load recent uploads on page load
    loadRecentUploads();


        // fetchNotificationCount();
        setupRealtimeSubscription();
        insertsupabase2();

        loadSubjects();
        loadStatistics();

        loadCurriculums();

        $(document).on('click', '.curriculum-item', function(e) {
            e.preventDefault();
            const curriculumId = $(this).data('id');
            const curriculumYear = $(this).data('year');
            selectCurriculum(curriculumId, curriculumYear);
        });
        
        // Create curriculum button
        $('#createCurriculumBtn').click(function() {
            createCurriculum();
        });
                                
        // Show notification to user
        // showNotification('New subject has been added!');

        

        // Passkey Generator
        const generatePasskeyBtn = document.getElementById('generatePasskeyBtn');
        const sendPasskeyBtn = document.getElementById('sendPasskeyBtn');
        const passkeyDisplay = document.getElementById('passkeyDisplay');
        const emailInput = document.getElementById('emailAddress');
        const userTypeSelect = document.getElementById('userTypeSelect');

        // Function to check if both required fields are valid
        function checkFormValidity() {
            const isEmailValid = emailInput.value.endsWith('@evsu.edu.ph');
            const isUserTypeSelected = userTypeSelect.value !== '';
            
            // Enable generate button only if both fields are valid
            generatePasskeyBtn.disabled = !(isEmailValid && isUserTypeSelected);
        }

        // Email validation and form check
        emailInput.addEventListener('input', function() {
            if (!this.value.endsWith('@evsu.edu.ph')) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
            checkFormValidity();
        });

        // User type validation and form check
        userTypeSelect.addEventListener('change', function() {
            if (this.value === '') {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
            checkFormValidity();
        });

        // Generate passkey button event
        generatePasskeyBtn.addEventListener('click', function() {
            // Double-check validation before generating
            if (!emailInput.value.endsWith('@evsu.edu.ph') || userTypeSelect.value === '') {
                // Show validation errors
                if (!emailInput.value.endsWith('@evsu.edu.ph')) {
                    emailInput.classList.add('is-invalid');
                }
                if (userTypeSelect.value === '') {
                    userTypeSelect.classList.add('is-invalid');
                }
                return;
            }
    
            // Generate a random passkey
            const passkey = generatePasskey(15);
            passkeyDisplay.textContent = passkey;
            passkeyDisplay.classList.add('text-primary', 'fw-bold');
            sendPasskeyBtn.disabled = false;
    
            // Add copy functionality
            passkeyDisplay.onclick = function() {
                navigator.clipboard.writeText(passkey);
                const originalText = passkeyDisplay.textContent;
                passkeyDisplay.textContent = 'Copied to clipboard!';
                setTimeout(() => {
                    passkeyDisplay.textContent = originalText;
                }, 2000);
            };
        });

        // Send passkey button event
        sendPasskeyBtn.addEventListener('click', function() {
            if (!emailInput.value.endsWith('@evsu.edu.ph')) {
                emailInput.classList.add('is-invalid');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'generate_passkey');
            formData.append('email', emailInput.value);
            formData.append('userType', userTypeSelect.value);
            formData.append('passkey', passkeyDisplay.value || passkeyDisplay.textContent);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            const sendPasskeyBtn1 = document.getElementById('sendPasskeyBtn');
            sendPasskeyBtn1.disabled = true;
            if (sendPasskeySpinner) sendPasskeySpinner.style.display = 'inline-block';
            sendPasskeyBtn1.querySelector('span').textContent = 'Sending...';


            fetch('/admin/ajax/get-stats', {
                method: 'POST',
                body: formData,
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data == 7) {
                    Swal.fire({
                        title: 'Send Successfully!',
                        text: 'to: ' + emailInput.value,
                        icon: 'success',
                        confirmButtonText: 'Continue to Dashboard',
                        confirmButtonColor: '#0d6efd',
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
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "/ad-dashboard";
                        }
                    });

                } else if(data == 1) {
                    Swal.fire({
                        title: 'Send Failed',
                        text: 'to: ' + emailInput.value,
                        icon: 'error',
                        confirmButtonText: 'Continue to Dashboard',
                        confirmButtonColor: '#0d6efd',
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
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "/ad-dashboard";
                        }
                    });
                    
                }
            })
            .catch(error => {
                console.error('Error checking email:', error);
            });
            
            emailInput.classList.remove('is-invalid');
            
            // Simulate sending email
            // alert(`Passkey sent to ${emailInput.value}`);
            // $('#passkeyModal').modal('hide');
        });

        // Initialize form state on modal open (optional but good practice)
        document.getElementById('passkeyModal').addEventListener('show.bs.modal', function() {
            // Reset form state when modal opens
            generatePasskeyBtn.disabled = true;
            sendPasskeyBtn.disabled = true;
            passkeyDisplay.textContent = 'Click Generate to create a passkey';
            passkeyDisplay.classList.remove('text-primary', 'fw-bold');
        });

        // You'll also need the generatePasskey function if you don't have it already:
        function generatePasskey(length) {
            const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let passkey = '';
            for (let i = 0; i < length; i++) {
                passkey += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            return passkey;
        }

        // End of Passkey Generator
        
        // Schedule adding functionality with type selection
        $('.add-schedule').click(function() {
            const day = $('#scheduleDay').val();
            const start = $('#startTime').val();
            const end = $('#endTime').val();
            const room = $('#room').val() || '';
            const scheduleType = $('#scheduleType').val();
            // try
            const sectionType = $('#sectionType').val();
            
            if (!day || !start || !end || !scheduleType || !sectionType) {
                // alert('Please fill all schedule fields');
                return;
            }
            
            // Check if end time is after start time
            if (start >= end) {
                alert('End time must be after start time');
                return;
            }
            
            const scheduleItem = `
                <div class="schedule-item mb-2 p-2 border rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold">${day} (${scheduleType})</span>: ${start} - ${end} ${room ? '(Room ' + room + ')' : ''} Section: ${sectionType}
                        </div>
                        <button type="button" class="btn btn-sm btn-danger remove-schedule">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <input type="hidden" name="schedules[]" value='${JSON.stringify({day, start_time: start, end_time: end, room, type: scheduleType, section: sectionType})}'>
                </div>
            `;
            
            $('#scheduleList').append(scheduleItem);
            
            // Clear inputs
            $('#scheduleDay').val('');
            $('#startTime').val('');
            $('#endTime').val('');
            $('#room').val('');
            $('#scheduleType').val('');
            $('#sectionType').val('');
        });
        
        // Remove schedule item
        $(document).on('click', '.remove-schedule', function() {
            $(this).closest('.schedule-item').remove();
        });
        
        // Prerequisite functionality
        const addPrerequisiteBtn = document.getElementById('addPrerequisiteBtn');
        const prerequisiteDropdown = document.getElementById('prerequisitesDropdown');
        
        addPrerequisiteBtn.addEventListener('click', function() {
            const subjectId = prerequisiteDropdown.value;
            const subjectCode = prerequisiteDropdown.options[prerequisiteDropdown.selectedIndex].text;
            
            if (subjectId) {
                const tag = document.createElement('div');
                tag.className = 'prerequisite-tag badge bg-primary me-1 mb-1';
                tag.innerHTML = `
                    ${subjectCode}
                    <span class="remove-tag ms-1" data-subject="${subjectId}" style="cursor: pointer;">&times;</span>
                    <input type="hidden" name="prerequisites[]" value="${subjectId}">
                `;
                prerequisiteTags.appendChild(tag);
                prerequisiteDropdown.value = '';
                
                // Add event to remove tag
                tag.querySelector('.remove-tag').addEventListener('click', function() {
                    tag.remove();
                });
            }
        });
        
        // Clear all logs button
        document.getElementById('clearAllLogsBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to clear all audit logs? This action cannot be undone.')) {
                alert('All audit logs have been cleared');
            }
        });
        
        // Mobile menu toggle
        const sidebar = document.querySelector('.sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        
        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });
        
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });
        
        // Tab navigation
        const tabLinks = document.querySelectorAll('.sidebar-menu a[data-bs-toggle="tab"]');
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                // Remove active class from all links
                tabLinks.forEach(l => l.classList.remove('active'));
                // Add active class to clicked link
                this.classList.add('active');
                
                // Show the corresponding tab
                const target = this.getAttribute('href');
                const tabPanes = document.querySelectorAll('.tab-pane');
                tabPanes.forEach(pane => pane.classList.remove('show', 'active'));
                document.querySelector(target).classList.add('show', 'active');
                
                // Close sidebar on mobile after clicking a menu item
                if (window.innerWidth <= 768 ) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });
        
        // Search functionality
        // Subjects search
        $('#subjectSearch').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('#subjectsTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        
        // Students search
        $('#auditSearch').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('#auditTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        
        // Audit logs search
        $('#auditSearch').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('#auditTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        
        // Files search
        $('#fileSearch').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('.file-item').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        
        // Load statistics and prerequisites on page load
        loadStatistics();
        loadPrerequisiteOptions();
        
        // Initialize the create subject form submission
        $('#createSubjectBtn').click(function() {
            createSubject();
        });
        
        // Load prerequisites when create modal is shown
        $('#createSubjectModal').on('show.bs.modal', function() {
            loadPrerequisiteOptions();
        });

        // Load prerequisites when edit modal is shown
        $('#editSubjectModal').on('show.bs.modal', function() {
            loadEditPrerequisiteOptions();
        });

        // Function to load edit prerequisites
        function loadEditPrerequisiteOptions() {
            // Only load prerequisites if a curriculum is selected
            if (!selectedCurriculum) {
                const dropdown = $('#editPrerequisitesDropdown');
                dropdown.empty().append('<option value="">Please select a curriculum first</option>');
                return;
            }

            $.post('/admin/ajax/get-stats', {
                action: 'get_prerequisites', 
                curriculum_id: selectedCurriculum, // Send the selected curriculum
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(response) {
                if (response.success) {
                    const dropdown = $('#editPrerequisitesDropdown');
                    dropdown.empty().append('<option value="">Select prerequisite subject</option>');
                    
                    if (response.prerequisites.length === 0) {
                        dropdown.append('<option value="">No subjects available in this curriculum</option>');
                    } else {
                        response.prerequisites.forEach(function(prereq) {
                            dropdown.append($('<option>', {
                                value: prereq.id,
                                text: prereq.code + ' - ' + prereq.name
                            }));
                        });
                    }
                }
            }, 'json').fail(function(xhr, status, error) {
                console.error('Error loading prerequisites:', error);
            });
        }

        // Load subjects when the page is ready
        loadStatistics();
        // loadSubjects2();
        loadSubjects();
        

        // Load audit logs when the audit tab is shown
        $('a[href="#audit"]').on('shown.bs.tab', function(e) {
            loadAuditLogs();
        });


        
}); //END OF DocumentcontentLoad
    
    // Function to generate a random passkey
    function generatePasskey(length) {
        const charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        let passkey = "";
        
        for (let i = 0; i < length; i++) {
            const randomIndex = Math.floor(Math.random() * charset.length);
            passkey += charset[randomIndex];
        }
        
        return passkey;
    }

    function loadStatistics() {
        $.post('/admin/ajax/get-stats', {action: 'get_stats', _token: $('meta[name="csrf-token"]').attr('content')}, function(response) {
            if (response.success) {
                $('#statsCards .col-md-3:eq(0) h2').text(response.stats.students);
                $('#statsCards .col-md-3:eq(1) h2').text(response.stats.instructors);
                $('#statsCards .col-md-3:eq(2) h2').text(response.stats.subjects);
                $('#statsCards .col-md-3:eq(3) h2').text(response.stats.enrollments);
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error loading statistics:', error);
        });
    }

    function loadPrerequisiteOptions() {
        // Only load prerequisites if a curriculum is selected
        if (!selectedCurriculum) {
            const dropdown = $('#prerequisitesDropdown');
            dropdown.empty().append('<option value="">Please select a curriculum first</option>');
            return;
        }

        $.post('/admin/ajax/get-stats', {
            action: 'get_prerequisites', 
            curriculum_id: selectedCurriculum, // Send the selected curriculum
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            if (response.success) {
                const dropdown = $('#prerequisitesDropdown');
                dropdown.empty().append('<option value="">Select prerequisite subject</option>');
                
                if (response.prerequisites.length === 0) {
                    dropdown.append('<option value="">No subjects available in this curriculum</option>');
                } else {
                    response.prerequisites.forEach(function(prereq) {
                        dropdown.append($('<option>', {
                            value: prereq.id,
                            text: prereq.code + ' - ' + prereq.name
                        }));
                    });
                }
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error loading prerequisites:', error);
        });
    }

    function createSubject() {
        // Validate subject type selection
        const subjectTypes = $('input[name="subjectType[]"]:checked').map(function() {
            return this.value;
        }).get();
        
        if (subjectTypes.length === 0) {
            alert('Please select at least one subject type (Lecture or Laboratory)');
            return;
        }

        // Validate that a curriculum is selected
        if (!selectedCurriculum) {
            alert('Please select a curriculum first');
            return;
        }
        
        // Validate schedules
        const schedules = $('input[name="schedules[]"]').map(function() {
            return JSON.parse(this.value);
        }).get();
        
        if (schedules.length === 0) {
            alert('Please add at least one schedule');
            return;
        }
        
        // Validate that all schedules have a type that matches selected subject types
        const scheduleTypes = [...new Set(schedules.map(s => s.type))];
        const hasMismatch = scheduleTypes.some(type => !subjectTypes.includes(type));
        
        if (hasMismatch) {
            alert('All schedule types must match the selected subject types');
            return;
        }

        if (!selectedCurriculum) {
            alert('Please select a curriculum first');
            return;
        }
        
        // Collect form data
        const formData = {
            action: 'create_subject',
            code: $('#subjectCode').val(),
            name: $('#subjectName').val(),
            description: $('#description').val(),
            units: $('#units').val(),
            max_students: $('#maxStudents').val(),
            curr: $('#curr').val(),
            year_level: $('#yearLevel').val(),
            semester: $('#semester').val(),
            types: subjectTypes,
            prerequisites: $('input[name="prerequisites[]"]').map(function() {
                return this.value;
            }).get(),
            schedules: schedules,
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        // Validate required fields
        if (!formData.code || !formData.name || !formData.units || 
            !formData.max_students || !formData.semester) {
            alert('Please fill all required fields');
            return;
        }
        
        
        // Send AJAX request
        $.post('/admin/ajax/get-stats', formData, function(response) {
            if (response.success) {
                if (response.has_conflicts) {
                    // Show warning but subject was created successfully
                    let conflictMessage = "<div class='text-start'>";
                    conflictMessage += "<strong>⚠️ Subject Created with Schedule Conflicts</strong><br><br>";
                    conflictMessage += "<div class='alert alert-warning'>This subject was saved successfully, but there are schedule conflicts that may cause issues:</div>";
                    
                    response.conflicts.forEach((conflict, index) => {
                        conflictMessage += `<div class='text-warning mb-2'><i class='fas fa-exclamation-triangle'></i> ${conflict.message}</div>`;
                    });
                    
                    if (response.available_slots && response.available_slots.length > 0) {
                        conflictMessage += "<br><strong>Available time slots for future reference:</strong><br><div class='suggestions-container mt-2'>";
                        response.available_slots.forEach((slot, index) => {
                            conflictMessage += `
                                <div class="suggestion-slot btn btn-outline-success btn-sm me-2 mb-2" 
                                    data-day="${slot.day}" 
                                    data-start="${slot.start_time}" 
                                    data-end="${slot.end_time}" 
                                    data-room="${slot.room}">
                                    <i class="fas fa-clock me-1"></i>${slot.day} ${slot.start_time}-${slot.end_time}
                                    <br><small>Room: ${slot.room}</small>
                                </div>
                            `;
                        });
                        conflictMessage += "</div>";
                    }
                    
                    conflictMessage += "<br><div class='alert alert-info mt-3'><i class='fas fa-info-circle'></i> You can edit this subject later to resolve the conflicts.</div>";
                    conflictMessage += "</div>";

                    Swal.fire({
                        title: 'Subject Created (with Conflicts)',
                        html: conflictMessage,
                        icon: 'warning',
                        confirmButtonText: 'Continue to Dashboard',
                        confirmButtonColor: '#0d6efd',
                        showCancelButton: true,
                        cancelButtonText: 'Edit Subject',
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
                        },
                        didOpen: () => {
                            // Add click handlers to suggestion slots
                            document.querySelectorAll('.suggestion-slot').forEach(slot => {
                                slot.addEventListener('click', function() {
                                    const day = this.getAttribute('data-day');
                                    const start = this.getAttribute('data-start');
                                    const end = this.getAttribute('data-end');
                                    const room = this.getAttribute('data-room');
                                    
                                    // Store these values for potential editing
                                    sessionStorage.setItem('lastConflictDay', day);
                                    sessionStorage.setItem('lastConflictStart', start);
                                    sessionStorage.setItem('lastConflictEnd', end);
                                    sessionStorage.setItem('lastConflictRoom', room);
                                    
                                    Swal.fire({
                                        title: 'Time Slot Copied!',
                                        text: 'This time slot has been saved. You can use it when editing the subject.',
                                        icon: 'info',
                                        timer: 2000,
                                        showConfirmButton: false,
                                        background: '#1a1a2e',
                                        color: '#ffffff'
                                    });
                                });
                            });
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Continue to dashboard
                            $('#createSubjectModal').modal('hide');
                            $('#createSubjectForm')[0].reset();
                            $('#prerequisiteTags').empty();
                            $('#scheduleList').empty();
                            $('input[name="subjectType[]"]').prop('checked', false);
                            
                            loadStatistics();
                            loadSubjects();
                            insertsupabase();
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            // Edit subject - reload the page or specific subject
                            loadSubjects(); // Reload to show the new subject
                            // Optionally auto-open the edit modal for the new subject
                            // You might need to get the new subject ID from response
                        }
                    });
                } else {
                    // No conflicts - normal success flow
                    Swal.fire({
                        title: 'Success!',
                        text: 'Subject created successfully!',
                        icon: 'success',
                        confirmButtonText: 'Continue',
                        confirmButtonColor: '#0d6efd',
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
                    }).then((result) => {
                        $('#createSubjectModal').modal('hide');
                        $('#createSubjectForm')[0].reset();
                        $('#prerequisiteTags').empty();
                        $('#scheduleList').empty();
                        $('input[name="subjectType[]"]').prop('checked', false);
                        
                        loadStatistics();
                        loadSubjects();
                        insertsupabase();
                    });
                }
            } else {
                alert('Error: ' + response.message);
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error creating subject:', error);
            alert('Failed to create subject. Please check console for details.');
        });
    }

    // Global variable to store selected curriculum
    let selectedCurriculum = null;

    // Function to load curriculums
    function loadCurriculums() {
        $.post('/admin/ajax/get-stats', {action: 'get_curriculums', _token: $('meta[name="csrf-token"]').attr('content')}, function(response) {
            if (response.success) {
                const curriculumList = $('#curriculumList');
                curriculumList.empty();
                
                // Add "Create New" option
                curriculumList.append(`
                    <li><a class="dropdown-item text-primary" href="#" data-bs-toggle="modal" data-bs-target="#createCurriculumModal">
                        <i class="fas fa-plus me-2"></i>Create New Curriculum
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                `);
                
                response.curriculums.forEach(function(curriculum) {
                    const isActive = curriculum.is_active ? ' <span class="badge bg-success">Active</span>' : '';
                    curriculumList.append(`
                        <li><a class="dropdown-item curriculum-item" href="#" data-id="${curriculum.id}" data-year="${curriculum.curriculum_year}">
                            ${curriculum.curriculum_year}${isActive}
                        </a></li>
                    `);
                });
                
                // Set default selection to the first active curriculum
                const activeCurriculum = response.curriculums.find(c => c.is_active);
                if (activeCurriculum) {
                    selectCurriculum(activeCurriculum.id, activeCurriculum.curriculum_year);
                } else if (response.curriculums.length > 0) {
                    selectCurriculum(response.curriculums[0].id, response.curriculums[0].curriculum_year);
                }
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error loading curriculums:', error);
        });
    }

    // Function to select a curriculum
    function selectCurriculum(curriculumId, curriculumYear) {
        selectedCurriculum = curriculumId;
        $('#selectedCurriculum').text(curriculumYear);
        
        // Update the create subject form
        $('#curr').val(curriculumId);
        
        // Reload subjects for the selected curriculum
        loadSubjects();

        // Reload prerequisites for the selected curriculum
        loadPrerequisiteOptions();
    }

    // Function to create new curriculum
    function createCurriculum() {
        const curriculumYear = $('#curriculumYear').val();
        
        if (!curriculumYear) {
            alert('Please enter a curriculum year');
            return;
        }
        
        $.post('/admin/ajax/get-stats', {
            action: 'create_curriculum',
            curriculum_year: curriculumYear,
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            if (response.success) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Curriculum created successfully!',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#0d6efd',
                    background: '#1a1a2e',
                    color: '#ffffff'
                }).then((result) => {
                    $('#createCurriculumModal').modal('hide');
                    $('#createCurriculumForm')[0].reset();
                    loadCurriculums();
                });
            } else {
                alert('Error: ' + response.message);
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error creating curriculum:', error);
            alert('Failed to create curriculum');
        });
    }

    function loadSubjects() {
        const data = {
            action: 'get_subjects',
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        // Add curriculum filter if selected
        if (selectedCurriculum) {
            data.curriculum_id = selectedCurriculum;
        }
        
        console.log('Loading subjects with curriculum_id:', selectedCurriculum); // Debug log
        console.log('Sending request with data:', data); // Debug
        
        $.post('/admin/ajax/get-stats', data, function(response) {
            if (response.success) {
                const tbody = $('#subjectsTableBody');
                tbody.empty();
                
                if (response.subjects.length === 0) {
                    tbody.append('<tr><td colspan="6" class="text-center">No subjects found for selected curriculum</td></tr>');
                    return;
                }
                
                response.subjects.forEach(function(subject) {
                    const row = `
                        <tr>
                            <td>${subject.code}</td>
                            <td>${subject.name}</td>
                            <td>${subject.units}</td>
                            <td>${subject.year_level} / ${subject.semester}</td>
                            <td>${subject.curriculum_year || 'N/A'}</td> <!-- Fixed to show curriculum year -->
                            <td id="_student_btn" class="d-inline-flex">
                                <button id="_view" class="btn btn-sm btn-outline-info m-1" onclick="viewSubject(${subject.id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary m-1" onclick="editSubject(${subject.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger m-1" onclick="deleteSubject(${subject.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            } else {
                console.error('Server returned error:', response.message);
                $('#subjectsTableBody').html('<tr><td colspan="6" class="text-center text-danger">Error loading subjects</td></tr>');
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('AJAX Error:', error);
            $('#subjectsTableBody').html('<tr><td colspan="6" class="text-center text-danger">Failed to load subjects</td></tr>');
        });
    }

    // The load subjects for the top dashboard
    function loadSubjects2() {
        $.post('/admin/ajax/get-stats', {action: 'get_stats', _token: $('meta[name="csrf-token"]').attr('content')}, function(response) {
            if (response.success) {
                const tbody2 = $('#subjects_stats');
                 const row2 = `
                        
                        <h2 class="mb-0">${response.stats.subjects}</h2>
                    `;
                tbody2.append(row2)
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error loading subjects:', error);
        });
    }

    
    function loadAuditLogs() {
        console.log('loadAuditLogs function called');
        
        $.post('/admin/ajax/get-audit-logs', { 
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            console.log('Response received:', response);
            
            if (response.success) {
                console.log('Logs count:', response.logs.length);
                console.log('First log:', response.logs[0]);
                
                const tbody = $('#auditTableBody');
                tbody.empty();
                
                if (response.logs.length === 0) {
                    tbody.append('<tr><td colspan="5" class="text-center">No audit logs found</td></tr>');
                    return;
                }
                
                response.logs.forEach(function(log) {
                    // Just use the raw timestamp for now
                    const timestamp = log.timestamp || 'N/A';
                    
                    const row = `
                        <tr>
                            <td>${timestamp}</td>
                            <td>${log.action || 'N/A'}</td>
                            <td>${log.user_id || 'System'}</td>
                            <td>${log.details || 'N/A'}</td>
                            <td>${log.ip_address || 'N/A'}</td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            } else {
                console.error('Response success is false:', response.message);
                $('#auditTableBody').html('<tr><td colspan="5" class="text-center text-danger">Error: ' + (response.message || 'Unknown error') + '</td></tr>');
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('AJAX Error:', error);
            console.log('Status:', status);
            console.log('XHR Response:', xhr.responseText);
            $('#auditTableBody').html('<tr><td colspan="5" class="text-center text-danger">AJAX Error: ' + error + '</td></tr>');
        });
    }


    function formatTime(timeString) {
        if (!timeString) return '';
        
        const time = new Date(`1970-01-01T${timeString}`);
        return time.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
    }

    function deleteSubject(subjectId) {
        if (confirm('Are you sure you want to delete this subject? This action cannot be undone.')) {
            $.post('/admin/ajax/get-stats', {
                action: 'delete_subject',
                subject_id: subjectId,
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(response) {
                if (response.success) {
                    alert('Subject deleted successfully');
                    loadStatistics();
                    // loadSubjects2();
                    loadSubjects(); // Reload the subjects
                    deletesupabase();
                } else {
                    alert('Error: ' + response.message);
                }
            }, 'json').fail(function(xhr, status, error) {
                console.error('Error deleting subject:', error);
                alert('Failed to delete subject. Please check console for details.');
            });
        }
    }

    function viewSubject(subjectId) {
        $.post('/admin/ajax/get-stats', {action: 'get_subject', subject_id: subjectId, _token: $('meta[name="csrf-token"]').attr('content')}, function(response) {
            if (response.success) {
                const subject = response.subject;
                
                // Populate the view modal
                $('#viewSubjectCode').text(subject.code);
                $('#viewSubjectName').text(subject.name);
                $('#viewSubjectUnits').text(subject.units);
                $('#viewSubjectCurriculum').text(subject.curriculum);
                $('#viewSubjectYearLevel').text(subject.year_level);
                $('#viewSubjectSemester').text(subject.semester);
                $('#viewSubjectMaxStudents').text(subject.max_students);
                $('#viewSubjectDescription').text(subject.description || 'No description available');
                
                // Populate prerequisites
                const prerequisitesContainer = $('#viewSubjectPrerequisites');
                prerequisitesContainer.empty();
                
                if (subject.prerequisites && subject.prerequisites.length > 0) {
                    subject.prerequisites.forEach(prereq => {
                        prerequisitesContainer.append(`<span class="badge bg-primary me-1">${prereq.code}</span>`);
                    });
                } else {
                    prerequisitesContainer.text('No prerequisites');
                }
                
                // Populate schedules
                const schedulesContainer = $('#viewSubjectSchedules');
                schedulesContainer.empty();
                
                if (subject.schedules && subject.schedules.length > 0) {
                    subject.schedules.forEach(schedule => {
                        const startTime = formatTime(schedule.start_time);
                        const endTime = formatTime(schedule.end_time);
                        
                        schedulesContainer.append(`
                            <tr>
                                <td>${schedule.Section || 'N/A'}</td>
                                <td>${schedule.Type || 'N/A'}</td>
                                <td>${schedule.day || 'N/A'}</td>
                                <td>${startTime}</td>
                                <td>${endTime}</td>
                                <td>${schedule.room || 'N/A'}</td>
                            </tr>
                        `);
                    });
                } else {
                    schedulesContainer.append('<tr><td colspan="6" class="text-center">No schedules available</td></tr>');
                }
                
                // Open the view modal
                $('#viewSubjectModal').modal('show');
            } else {
                alert('Error: ' + response.message);
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error loading subject:', error);
            alert('Failed to load subject data. Please check console for details.');
        });
    }

    function editSubject(subjectId) {
        $.post('/admin/ajax/get-stats', {action: 'get_subject', subject_id: subjectId, _token: $('meta[name="csrf-token"]').attr('content')}, function(response) {
            if (response.success) {
                const subject = response.subject;
                
                // Populate the form fields
                $('#editSubjectId').val(subject.id);
                $('#editSubjectCode').val(subject.code);
                $('#editSubjectName').val(subject.name);
                $('#editDescription').val(subject.description || '');
                $('#editUnits').val(subject.units);
                $('#editMaxStudents').val(subject.max_students);
                $('#editCurriculum').val(subject.curriculum);
                $('#editYearLevel').val(subject.year_level);
                $('#editSemester').val(subject.semester);
                
                // Set subject types
                $('#editLectureCheck').prop('checked', false);
                $('#editLaboratoryCheck').prop('checked', false);
                
                // Check which types are present in the schedules
                const typesPresent = [...new Set(subject.schedules.map(s => s.Type))];
                if (typesPresent.includes('Lecture')) {
                    $('#editLectureCheck').prop('checked', true);
                }
                if (typesPresent.includes('Laboratory')) {
                    $('#editLaboratoryCheck').prop('checked', true);
                }
                
                // Populate prerequisites
                $('#editPrerequisiteTags').empty();
                if (subject.prerequisites && subject.prerequisites.length > 0) {
                    subject.prerequisites.forEach(prereq => {
                        addPrerequisiteTag(prereq.id, prereq.code, 'edit');
                    });
                }
                
                // Populate schedules
                $('#editScheduleList').empty();
                if (subject.schedules && subject.schedules.length > 0) {
                    subject.schedules.forEach(schedule => {
                        addScheduleItem(
                            schedule.day, 
                            schedule.start_time, 
                            schedule.end_time, 
                            schedule.room, 
                            schedule.Type,
                            schedule.Section,
                            'edit'
                        );
                    });
                }

                updateSupabase("recordId");
                
                // Open the modal
                $('#editSubjectModal').modal('show');
            } else {
                alert('Error: ' + response.message);
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error loading subject:', error);
            alert('Failed to load subject data. Please check console for details.');
        });
    }

    function addPrerequisiteTag(subjectId, subjectCode, formType = 'create') {
        const tag = document.createElement('div');
        tag.className = 'prerequisite-tag badge bg-primary me-1 mb-1';
        tag.innerHTML = `
            ${subjectCode}
            <span class="remove-tag ms-1" data-subject="${subjectId}" style="cursor: pointer;">&times;</span>
            <input type="hidden" name="prerequisites[]" value="${subjectId}">
        `;
        
        const container = formType === 'create' ? 
            document.getElementById('prerequisiteTags') : 
            document.getElementById('editPrerequisiteTags');
        
        container.appendChild(tag);
        
        // Add event to remove tag
        tag.querySelector('.remove-tag').addEventListener('click', function() {
            tag.remove();
        });
    }

    function addScheduleItem(day, startTime, endTime, room, type, section, formType = 'create') {
        const scheduleItem = `
            <div class="schedule-item mb-2 p-2 border rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold">${day} (${type})</span>: ${startTime} - ${endTime} ${room ? '(Room ' + room + ')' : ''} Section: ${section}
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-schedule">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <input type="hidden" name="schedules[]" value='${JSON.stringify({day, start_time: startTime, end_time: endTime, room, type, section})}'>
            </div>
        `;
        
        const container = formType === 'create' ? 
            $('#scheduleList') : 
            $('#editScheduleList');
        
        container.append(scheduleItem);
    }

    function updateSubject() {
        // Validate subject type selection
        const subjectTypes = $('#editSubjectForm input[name="subjectType[]"]:checked').map(function() {
            return this.value;
        }).get();
        
        if (subjectTypes.length === 0) {
            alert('Please select at least one subject type (Lecture or Laboratory)');
            return;
        }
        
        // Validate schedules
        const schedules = $('#editSubjectForm input[name="schedules[]"]').map(function() {
            return JSON.parse(this.value);
        }).get();
        
        if (schedules.length === 0) {
            alert('Please add at least one schedule');
            return;
        }
        
        // Validate that all schedules have a type that matches selected subject types
        const scheduleTypes = [...new Set(schedules.map(s => s.type))];
        const hasMismatch = scheduleTypes.some(type => !subjectTypes.includes(type));
        
        if (hasMismatch) {
            alert('All schedule types must match the selected subject types');
            return;
        }
        
        // Collect form data
        const formData = {
            action: 'update_subject',
            subject_id: $('#editSubjectId').val(),
            code: $('#editSubjectCode').val(),
            name: $('#editSubjectName').val(),
            description: $('#editDescription').val(),
            units: $('#editUnits').val(),
            max_students: $('#editMaxStudents').val(),
            curr: $('#editCurriculum').val(),
            year_level: $('#editYearLevel').val(),
            semester: $('#editSemester').val(),
            types: subjectTypes,
            prerequisites: $('#editSubjectForm input[name="prerequisites[]"]').map(function() {
                return this.value;
            }).get(),
            schedules: schedules,
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        // Validate required fields
        if (!formData.code || !formData.name || !formData.units || 
            !formData.max_students || !formData.semester) {
            alert('Please fill all required fields');
            return;
        }
        
        // Send AJAX request
        $.post('/admin/ajax/get-stats', formData, function(response) {
            if (response.success) {
                if (response.has_conflicts) {
                    // Show warning but subject was updated successfully
                    let conflictMessage = "<div class='text-start'>";
                    conflictMessage += "<strong>⚠️ Subject Updated with Schedule Conflicts</strong><br><br>";
                    conflictMessage += "<div class='alert alert-warning'>This subject was updated successfully, but there are schedule conflicts that may cause issues:</div>";
                    
                    response.conflicts.forEach((conflict, index) => {
                        conflictMessage += `<div class='text-warning mb-2'><i class='fas fa-exclamation-triangle'></i> ${conflict.message}</div>`;
                    });
                    
                    conflictMessage += "<br><div class='alert alert-info mt-3'><i class='fas fa-info-circle'></i> You can edit this subject again to resolve the conflicts if needed.</div>";
                    conflictMessage += "</div>";

                    Swal.fire({
                        title: 'Subject Updated (with Conflicts)',
                        html: conflictMessage,
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0d6efd',
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
                    }).then((result) => {
                        $('#editSubjectModal').modal('hide');
                        $('#editSubjectForm')[0].reset();
                        $('#editPrerequisiteTags').empty();
                        $('#editScheduleList').empty();
                        $('#editSubjectForm input[name="subjectType[]"]').prop('checked', false);
                        
                        loadStatistics();
                        loadSubjects();
                    });
                } else {
                    // No conflicts - normal success flow
                    alert('Subject updated successfully!');
                    $('#editSubjectModal').modal('hide');
                    $('#editSubjectForm')[0].reset();
                    $('#editPrerequisiteTags').empty();
                    $('#editScheduleList').empty();
                    $('#editSubjectForm input[name="subjectType[]"]').prop('checked', false);
                    
                    loadStatistics();
                    loadSubjects();
                }
            } else {
                alert('Error: ' + response.message);
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error updating subject:', error);
            alert('Failed to update subject. Please check console for details.');
        });
    }

    // Add event listeners for the edit modal
    $(document).ready(function() {
        // Edit prerequisite functionality
        $('#editAddPrerequisiteBtn').click(function() {
            const subjectId = $('#editPrerequisitesDropdown').val();
            const subjectCode = $('#editPrerequisitesDropdown option:selected').text();
            
            if (subjectId) {
                addPrerequisiteTag(subjectId, subjectCode, 'edit');
                $('#editPrerequisitesDropdown').val('');
            }
        });
        
        // Edit schedule functionality
        $('#editAddScheduleBtn').click(function() {
            const day = $('#editScheduleDay').val();
            const start = $('#editStartTime').val();
            const end = $('#editEndTime').val();
            const room = $('#editRoom').val() || '';
            const scheduleType = $('#editScheduleType').val();
            const sectionType = $('#editSectionType').val();
            
            if (!day || !start || !end || !scheduleType || !sectionType) {
                // alert('Please fill all schedule fields');
                return;
            }
            
            // Check if end time is after start time
            if (start >= end) {
                alert('End time must be after start time');
                return;
            }
            
            addScheduleItem(day, start, end, room, scheduleType, sectionType, 'edit');
            
            // Clear inputs
            $('#editScheduleDay').val('');
            $('#editStartTime').val('');
            $('#editEndTime').val('');
            $('#editRoom').val('');
            $('#editScheduleType').val('');
            $('#editSectionType').val('');
        });
        
        // Update subject button
        $('#updateSubjectBtn').click(updateSubject);
        
        // Remove schedule item in edit modal
        $(document).on('click', '#editScheduleList .remove-schedule', function() {
            $(this).closest('.schedule-item').remove();
        });
    });

    // Clear form when create modal is hidden
    $('#createSubjectModal').on('hidden.bs.modal', function() {
        $('#createSubjectForm')[0].reset();
        $('#prerequisiteTags').empty();
        $('#scheduleList').empty();
        $('input[name="subjectType[]"]').prop('checked', false);
    });

    // Clear form when edit modal is hidden
    $('#editSubjectModal').on('hidden.bs.modal', function() {
        $('#editSubjectForm')[0].reset();
        $('#editPrerequisiteTags').empty();
        $('#editScheduleList').empty();
        $('#editSubjectForm input[name="subjectType[]"]').prop('checked', false);
    });