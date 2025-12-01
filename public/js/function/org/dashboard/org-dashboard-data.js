
// Organization Dashboard Data Handler
class OrgDashboardData {
    constructor() {
        this.stats = {
            pendingEnrollmentRequests: 0,
            pendingPaymentVerifications: 0,
            totalStudents: 0,
            approvedThisWeek: 0
        };
        this.recentActivities = [];
        this.quickActions = {};
        
        this.init();
    }

    init() {
        this.fetchDashboardData();
        // Refresh data every 30 seconds
        setInterval(() => this.fetchDashboardData(), 60000);
    }

    async fetchDashboardData() {
        try {
            const response = await fetch('/org-dashboard/data', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            
            if (data.error) {
                console.error('Error fetching dashboard data:', data.error);
                return;
            }

            this.updateStats(data.stats);
            this.updateRecentActivities(data.recentActivities);
            this.updateQuickActions(data.quickActions);

        } catch (error) {
            console.error('Error fetching dashboard data:', error);
            this.showError('Failed to load dashboard data');
        }
    }

    updateStats(stats) {
        this.stats = stats;
        
        // Update stats cards
        this.updateStatCard('pending-enrollment', stats.pendingEnrollmentRequests);
        this.updateStatCard('pending-payment', stats.pendingPaymentVerifications);
        this.updateStatCard('total-students', stats.totalStudents);
        this.updateStatCard('approved-week', stats.approvedThisWeek);
    }

    updateStatCard(statType, value) {
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
                this.animateValue(element, parseInt(element.textContent) || 0, value, 1000);
            }
        }
    }

    animateValue(element, start, end, duration) {
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

    updateRecentActivities(activities) {
        this.recentActivities = activities;
        
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
            
            const time = this.formatTime(activity.time);
            
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
            this.attachPaymentEventListeners();
        }, 100);
    }

    attachPaymentEventListeners() {
        console.log('Attaching event listeners...'); // Debug log
        
        // View details buttons - FIXED: Use event delegation
        document.querySelector('.schedule-day').addEventListener('click', (e) => {
            if (e.target.closest('.btn-view-details')) {
                const button = e.target.closest('.btn-view-details');
                const studentId = button.getAttribute('data-student-id');
                this.viewStudentDetails(studentId);
            }
            
            if (e.target.closest('.btn-approve-payment')) {
                const button = e.target.closest('.btn-approve-payment');
                const studentId = button.getAttribute('data-student-id');
                this.approvePayment(studentId);
            }
            
            if (e.target.closest('.btn-decline-payment')) {
                const button = e.target.closest('.btn-decline-payment');
                const studentId = button.getAttribute('data-student-id');
                this.declinePayment(studentId);
            }
        });
    }

    async viewStudentDetails(studentId) {
        try {
            console.log('Viewing details for student:', studentId); // Debug log
            
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
                this.showError(data.error);
                return;
            }

            this.showStudentDetailsModal(data);

        } catch (error) {
            console.error('Error fetching student details:', error);
            this.showError('Failed to load student details');
        }
    }

    // showStudentDetailsModal(data) {
    //     // Build the modal content with student details
    //     let subjectsHtml = '';
    //     if (data.subjects && data.subjects.length > 0) {
    //         subjectsHtml = data.subjects.map(subject => `
    //             <div class="subject-item">
    //                 <strong>${subject.subject_code}</strong> - ${subject.subject_name}
    //                 <span class="units">(${subject.units} units)</span>
    //             </div>
    //         `).join('');
    //     } else {
    //         subjectsHtml = '<div class="text-muted">No subjects found</div>';
    //     }

    //     let fheHtml = data.fheDocument ? 
    //         `<a href="/${data.fheDocument.file_path}" target="_blank" class="btn btn-sm btn-outline-primary">
    //             <i class="fas fa-file-pdf"></i> View FHE
    //         </a>` : 
    //         '<span class="text-danger">No FHE document uploaded</span>';

    //     let receiptHtml = data.paymentReceipt ? 
    //         `<a href="/${data.paymentReceipt.file_path}" target="_blank" class="btn btn-sm btn-outline-success">
    //             <i class="fas fa-receipt"></i> View Receipt
    //         </a>` : 
    //         '<span class="text-danger">No payment receipt uploaded</span>';

    //     const modalHtml = `
    //         <div class="modal fade" id="studentDetailsModal" tabindex="-1">
    //             <div class="modal-dialog modal-lg">
    //                 <div class="modal-content">
    //                     <div class="modal-header">
    //                         <h5 class="modal-title">Student Enrollment Details - ${data.student.id_no}</h5>
    //                         <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    //                     </div>
    //                     <div class="modal-body">
    //                         <div class="row mb-3">
    //                             <div class="col-md-6">
    //                                 <h6>Student Information</h6>
    //                                 <p><strong>ID:</strong> ${data.student.id_no}</p>
    //                                 <p><strong>Year Level:</strong> ${data.student.year_level}</p>
    //                                 <p><strong>Status:</strong> ${data.student.status}</p>
    //                             </div>
    //                             <div class="col-md-6">
    //                                 <h6>Documents</h6>
    //                                 <p><strong>FHE:</strong> ${fheHtml}</p>
    //                                 <p><strong>Payment Receipt:</strong> ${receiptHtml}</p>
    //                             </div>
    //                         </div>
    //                         <div class="row">
    //                             <div class="col-12">
    //                                 <h6>Requested Subjects</h6>
    //                                 <div class="subjects-list">
    //                                     ${subjectsHtml}
    //                                 </div>
    //                             </div>
    //                         </div>
    //                     </div>
    //                     <div class="modal-footer">
    //                         <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    //                     </div>
    //                 </div>
    //             </div>
    //         </div>
    //     `;

    //     // Remove existing modal if any
    //     const existingModal = document.getElementById('studentDetailsModal');
    //     if (existingModal) {
    //         existingModal.remove();
    //     }

    //     // Add modal to page
    //     document.body.insertAdjacentHTML('beforeend', modalHtml);
        
    //     // Show modal
    //     const modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
    //     modal.show();
    // }

    //
    
    showStudentDetailsModal(data) {
        // Build the modal content with student details
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

        // Remove existing modal if any
        const existingModal = document.getElementById('studentDetailsModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Add modal to page
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
        modal.show();
    }

    async approvePayment(studentId) {
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

            if (result.success) {
                Swal.fire({
                    title: 'Payment Approved!',
                    text: `Payment for student ${studentId} has been approved successfully.`,
                    icon: 'success',
                    confirmButtonColor: '#4361ee'
                });

                // Refresh dashboard data
                this.fetchDashboardData();
                setInterval(() => this.fetchDashboardData(), 60000);
            } else {
                throw new Error(result.message || 'Failed to approve payment');
            }

        } catch (error) {
            console.error('Error approving payment:', error);
            this.showError('Failed to approve payment: ' + error.message);
        }
    }

    async declinePayment(studentId) {
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
                Swal.fire({
                    title: 'Payment Declined!',
                    text: `Payment for student ${studentId} has been declined.`,
                    icon: 'success',
                    confirmButtonColor: '#e74c3c'
                });

                // Refresh dashboard data
                this.fetchDashboardData();
                setInterval(() => this.fetchDashboardData(), 60000);
            } else {
                throw new Error(result.message || 'Failed to decline payment');
            }

        } catch (error) {
            console.error('Error declining payment:', error);
            this.showError('Failed to decline payment: ' + error.message);
        }
    }

    viewReceipt(filePath, studentId) {
        if (!filePath) {
            this.showError('No receipt available for this student');
            return;
        }

        // Create a modal or open in new tab to view the receipt
        const receiptUrl = `/${filePath}`; // Adjust path as needed
        
        // Open in new tab
        window.open(receiptUrl, '_blank');
        
        // Alternatively, you could show a modal with the receipt
        // this.showReceiptModal(receiptUrl, studentId);
    }

    async verifyPayment(studentId) {
        try {
            // Show confirmation dialog
            const confirmed = await Swal.fire({
                title: 'Verify Payment?',
                text: `Are you sure you want to verify payment for student ${studentId}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Verify',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#4361ee'
            });

            if (!confirmed.isConfirmed) return;

            // Send verification request to server
            const response = await fetch('/org/verify-payment', {
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
                Swal.fire({
                    title: 'Payment Verified!',
                    text: `Payment for student ${studentId} has been verified successfully.`,
                    icon: 'success',
                    confirmButtonColor: '#4361ee'
                });

                // Refresh the dashboard data
                this.fetchDashboardData();
            } else {
                throw new Error(result.message || 'Failed to verify payment');
            }

        } catch (error) {
            console.error('Error verifying payment:', error);
            this.showError('Failed to verify payment: ' + error.message);
        }
    }


    formatTime(timeString) {
        if (!timeString) return '--:--';
        return timeString; // Already formatted in PHP
    }

    parseActivityDetails(details) {
        if (!details) return 'No additional details';
        
        // Extract meaningful information from details
        if (details.includes('Windows/desktop') || details.includes('iOS/mobile')) {
            const parts = details.split('/');
            if (parts.length >= 3) {
                return `${parts[0]} • ${parts[2].split(' ')[0]}`;
            }
        }
        
        return details.length > 50 ? details.substring(0, 50) + '...' : details;
    }

    updateQuickActions(quickActions) {
        this.quickActions = quickActions;
        
        // Update quick action cards
        this.updateQuickActionCard(0, quickActions.processingRate, quickActions.pendingRequests);
        this.updateQuickActionCard(1, quickActions.verificationRate, quickActions.pendingPayments);
        this.updateQuickActionCard(2, quickActions.activeStudentsRate, this.stats.totalStudents);
    }

    updateQuickActionCard(cardIndex, rate, count) {
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

    showError(message) {
        // You can implement a toast notification system here
        console.error('Dashboard Error:', message);
    }

    showReceiptModal(receiptUrl, studentId) {
        // You can implement a modal here to show the receipt
        // This is a simplified version - you might want to use a proper modal library
        const modalHtml = `
            <div class="receipt-modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; align-items: center; justify-content: center;">
                <div style="background: white; padding: 20px; border-radius: 8px; max-width: 90%; max-height: 90%; overflow: auto;">
                    <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 15px;">
                        <h3>Receipt for Student ${studentId}</h3>
                        <button class="close-modal" style="background: none; border: none; font-size: 20px; cursor: pointer;">×</button>
                    </div>
                    <iframe src="${receiptUrl}" style="width: 800px; height: 600px; border: none;"></iframe>
                </div>
            </div>
        `;

        const modalElement = document.createElement('div');
        modalElement.innerHTML = modalHtml;
        document.body.appendChild(modalElement);

        // Close modal functionality
        modalElement.querySelector('.close-modal').addEventListener('click', () => {
            document.body.removeChild(modalElement);
        });

        // Close when clicking outside
        modalElement.addEventListener('click', (e) => {
            if (e.target === modalElement) {
                document.body.removeChild(modalElement);
            }
        });
    }
}

// Initialize dashboard data when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.orgDashboardData = new OrgDashboardData();
});