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
        setInterval(() => this.fetchDashboardData(), 30000);
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

        // Add new activities with payment-specific styling
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
                        <button class="btn-view-receipt" data-file-path="${activity.file_path}" data-student-id="${activity.student_id}">
                            <i class="fas fa-receipt"></i> View Receipt
                        </button>
                        <button class="btn-verify-payment" data-student-id="${activity.student_id}">
                            <i class="fas fa-check"></i> Verify
                        </button>
                    </div>
                </div>
            `;
            
            scheduleContainer.appendChild(activityElement);
        });

        // Add event listeners to the new buttons
        this.attachPaymentEventListeners();
    }

    attachPaymentEventListeners() {
        // View receipt buttons
        document.querySelectorAll('.btn-view-receipt').forEach(button => {
            button.addEventListener('click', (e) => {
                const filePath = e.target.closest('.btn-view-receipt').getAttribute('data-file-path');
                const studentId = e.target.closest('.btn-view-receipt').getAttribute('data-student-id');
                this.viewReceipt(filePath, studentId);
            });
        });

        // Verify payment buttons
        document.querySelectorAll('.btn-verify-payment').forEach(button => {
            button.addEventListener('click', (e) => {
                const studentId = e.target.closest('.btn-verify-payment').getAttribute('data-student-id');
                this.verifyPayment(studentId);
            });
        });
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