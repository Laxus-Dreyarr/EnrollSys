// Initialize Supabase at the very top of your script
        const supabaseUrl = "https://dfvapjrkotprotpbpeju.supabase.co";
        const supabaseAnonKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImRmdmFwanJrb3Rwcm90cGJwZWp1Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTcxNDg1OTMsImV4cCI6MjA3MjcyNDU5M30.Hou-GtB-P8qJ4fxXbC-VtyaCkDpf5Kr01DD9aSckhiU";

        // Create Supabase client
        const { createClient } = supabase;
        const supabaseClient = createClient(supabaseUrl, supabaseAnonKey);


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
                        console.log('Change received!', payload);
                        // Refresh the notification count when changes occur
                        // fetchNotificationCount();
                        loadSubjects();
                        loadStatistics();
                                        
                        // Show notification to user
                        showNotification('New subject has been added!');
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


        // fetchNotificationCount();
        setupRealtimeSubscription();

        loadSubjects();
        loadStatistics();
                                
        // Show notification to user
        showNotification('New subject has been added!');
        

        // Passkey Generator
        const generatePasskeyBtn = document.getElementById('generatePasskeyBtn');
        const sendPasskeyBtn = document.getElementById('sendPasskeyBtn');
        const passkeyDisplay = document.getElementById('passkeyDisplay');
        const emailInput = document.getElementById('emailAddress');
        
        generatePasskeyBtn.addEventListener('click', function() {
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
        
        sendPasskeyBtn.addEventListener('click', function() {
            if (!emailInput.value.endsWith('@evsu.edu.ph')) {
                emailInput.classList.add('is-invalid');
                return;
            }
            
            emailInput.classList.remove('is-invalid');
            
            // Simulate sending email
            alert(`Passkey sent to ${emailInput.value}`);
            $('#passkeyModal').modal('hide');
        });
        
        // Email validation
        emailInput.addEventListener('input', function() {
            if (!this.value.endsWith('@evsu.edu.ph')) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
        
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
            $.post('/admin/ajax/get-stats', {action: 'get_prerequisites'}, function(response) {
                if (response.success) {
                    const dropdown = $('#editPrerequisitesDropdown');
                    dropdown.empty().append('<option value="">Select prerequisite subject</option>');
                    
                    response.prerequisites.forEach(function(prereq) {
                        dropdown.append($('<option>', {
                            value: prereq.id,
                            text: prereq.code + ' - ' + prereq.name
                        }));
                    });
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
        $.post('/admin/ajax/get-stats', {action: 'get_stats'}, function(response) {
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
        $.post('/admin/ajax/get-stats', {action: 'get_prerequisites'}, function(response) {
            if (response.success) {
                const dropdown = $('#prerequisitesDropdown');
                dropdown.empty().append('<option value="">Select prerequisite subject</option>');
                
                response.prerequisites.forEach(function(prereq) {
                    dropdown.append($('<option>', {
                        value: prereq.id,
                        text: prereq.code + ' - ' + prereq.name
                    }));
                });
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
        
        // Collect form data
        const formData = {
            action: 'create_subject',
            code: $('#subjectCode').val(),
            name: $('#subjectName').val(),
            description: $('#description').val(),
            units: $('#units').val(),
            max_students: $('#maxStudents').val(),
            year_level: $('#yearLevel').val(),
            semester: $('#semester').val(),
            types: subjectTypes,
            prerequisites: $('input[name="prerequisites[]"]').map(function() {
                return this.value;
            }).get(),
            schedules: schedules
        };
        
        // Validate required fields
        if (!formData.code || !formData.name || !formData.units || 
            !formData.max_students || !formData.year_level || !formData.semester) {
            alert('Please fill all required fields');
            return;
        }
        
        console.log('Sending data:', formData);
        
        // Send AJAX request
        $.post('/admin/ajax/get-stats', formData, function(response) {
            if (response.success) {
                alert('Subject created successfully!');
                $('#createSubjectModal').modal('hide');
                // Reset form
                $('#createSubjectForm')[0].reset();
                $('#prerequisiteTags').empty();
                $('#scheduleList').empty();
                $('input[name="subjectType[]"]').prop('checked', false);
                
                // Reload the subjects instead of the whole page
                loadStatistics();
                // loadSubjects2();
                loadSubjects();
                // supabase insert notifications
                insertsupabase();
                // END
            } else if (response == 1){
                alert("Section Already exist!")
            } else {
                alert('Error: ' + response.message);
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error creating subject:', error);
            alert('Failed to create subject. Please check console for details.');
        });
    }

    // Handle passkey generation
    $('#generatePasskeyBtn').click(function() {
        const email = $('#emailAddress').val();
        const userType = $('#userTypeSelect').val();
        
        if (!email || !userType) {
            alert('Please fill all fields');
            return;
        }
        
        if (!email.endsWith('@evsu.edu.ph')) {
            $('#emailAddress').addClass('is-invalid');
            return;
        }
        
        $.post('/admin/ajax/get-stats', {
            action: 'generate_passkey', 
            email: email, 
            user_type: userType
        }, function(response) {
            if (response.success) {
                alert('Passkey generated and sent to ' + email);
                $('#passkeyModal').modal('hide');
                // Reset form
                $('#emailAddress').val('');
                $('#userTypeSelect').val('');
            } else {
                alert('Error: ' + response.message);
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error generating passkey:', error);
            alert('Failed to generate passkey. Please check console for details.');
        });
    });

    function loadSubjects() {
        $.post('/admin/ajax/get-stats', {action: 'get_subjects'}, function(response) {
            if (response.success) {
                const tbody = $('#subjectsTableBody');
                tbody.empty();
                
                response.subjects.forEach(function(subject) {
                    const row = `
                        <tr>
                            <td>${subject.code}</td>
                            <td>${subject.name}</td>
                            <td>${subject.units}</td>
                            <td>${subject.year_level} / ${subject.semester}</td>
                            <td id="_student_btn">
                                <button id="_view" class="btn btn-sm btn-outline-info" onclick="viewSubject(${subject.id})">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn btn-sm btn-outline-primary ms-1" onclick="editSubject(${subject.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger ms-1" onclick="deleteSubject(${subject.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                    console.log('Loading subjects...');
                });
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error loading subjects:', error);
        });
    }

    // The load subjects for the top dashboard
    function loadSubjects2() {
        $.post('/admin/ajax/get-stats', {action: 'get_stats'}, function(response) {
            if (response.success) {
                const tbody2 = $('#subjects_stats');
                 const row2 = `
                        <h5 class="card-title">Subjects</h5>
                        <h2 class="mb-0">${stats.subjects}</h2>
                    `;
                tbody2.append(row2)
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error loading subjects:', error);
        });
    }

    function loadAuditLogs() {
        $.post('/admin/ajax/get-stats', {action: 'get_audit_logs'}, function(response) {
            if (response.success) {
                const tbody = $('#auditTableBody');
                tbody.empty();
                
                response.logs.forEach(function(log) {
                    const timestamp = new Date(log.timestamp).toLocaleString();
                    const user = log.firstname && log.lastname ? 
                        `${log.firstname} ${log.lastname}` : 
                        (log.user_id ? `User ID: ${log.user_id}` : 'System');
                    
                    const row = `
                        <tr>
                            <td>${timestamp}</td>
                            <td>${log.action}</td>
                            <td>${user}</td>
                            <td>${log.details || 'N/A'}</td>
                            <td>${log.ip_address || 'N/A'}</td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error loading audit logs:', error);
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
                subject_id: subjectId
            }, function(response) {
                if (response.success) {
                    alert('Subject deleted successfully');
                    loadStatistics();
                    // loadSubjects2();
                    loadSubjects(); // Reload the subjects
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
        $.post('/admin/ajax/get-stats', {action: 'get_subject', subject_id: subjectId}, function(response) {
            if (response.success) {
                const subject = response.subject;
                
                // Populate the view modal
                $('#viewSubjectCode').text(subject.code);
                $('#viewSubjectName').text(subject.name);
                $('#viewSubjectUnits').text(subject.units);
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
        $.post('/admin/ajax/get-stats', {action: 'get_subject', subject_id: subjectId}, function(response) {
            if (response.success) {
                const subject = response.subject;
                
                // Populate the form fields
                $('#editSubjectId').val(subject.id);
                $('#editSubjectCode').val(subject.code);
                $('#editSubjectName').val(subject.name);
                $('#editDescription').val(subject.description || '');
                $('#editUnits').val(subject.units);
                $('#editMaxStudents').val(subject.max_students);
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
            year_level: $('#editYearLevel').val(),
            semester: $('#editSemester').val(),
            types: subjectTypes,
            prerequisites: $('#editSubjectForm input[name="prerequisites[]"]').map(function() {
                return this.value;
            }).get(),
            schedules: schedules
        };
        
        // Validate required fields
        if (!formData.code || !formData.name || !formData.units || 
            !formData.max_students || !formData.year_level || !formData.semester) {
            alert('Please fill all required fields');
            return;
        }
        
        // Send AJAX request
        $.post('/admin/ajax/get-stats', formData, function(response) {
            if (response.success) {
                alert('Subject updated successfully!');
                $('#editSubjectModal').modal('hide');
                // Reset form
                $('#editSubjectForm')[0].reset();
                $('#editPrerequisiteTags').empty();
                $('#editScheduleList').empty();
                $('#editSubjectForm input[name="subjectType[]"]').prop('checked', false);
                
                // Reload the subjects
                loadStatistics();
                // loadSubjects2();
                loadSubjects();
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
                alert('Please fill all schedule fields');
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