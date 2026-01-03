<?php
$total_pending = 5;
$total_amount = 500;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Organization Dashboard - Payment Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --success-color: #4cc9f0;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
            color: white;
            height: 100vh;
            position: fixed;
            width: 250px;
            padding-top: 20px;
            box-shadow: var(--card-shadow);
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                margin-bottom: 20px;
            }
            .main-content {
                margin-left: 0;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 15px 10px;
            }
            .dashboard-card {
                padding: 15px;
                margin-bottom: 15px;
            }
            .table-responsive {
                font-size: 14px;
            }
            .action-buttons .btn {
                padding: 5px 10px;
                font-size: 12px;
            }
        }
        
        .dashboard-header {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            border-left: 5px solid var(--primary-color);
        }
        
        .dashboard-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card {
            border-top: 4px solid var(--primary-color);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .year-level-badge {
            background: linear-gradient(45deg, var(--primary-color), var(--success-color));
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .btn-accept {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-accept:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);
        }
        
        .btn-view {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-view:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .payment-amount {
            font-weight: 700;
            color: #27ae60;
            font-size: 1.1rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 15px;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            margin: 5px 0;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .org-logo {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .org-logo h4 {
            font-weight: 700;
            margin: 0;
        }
        
        .modal-header {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 10px 10px 0 0;
        }
        
        .notes-textarea {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 15px;
            resize: none;
        }
        
        .notes-textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2 col-md-3 sidebar">
                <div class="org-logo">
                    <h4><i class="bi bi-building"></i> Organization</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">
                            <i class="bi bi-credit-card"></i> Payment Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-clock-history"></i> Payment History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-bar-chart"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-10 col-md-9 main-content">
                <!-- Dashboard Header -->
                <div class="dashboard-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="display-6 fw-bold mb-2">Payment Management Dashboard</h1>
                            <p class="text-muted mb-0">Manage and track student organization fee payments</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <div class="d-flex justify-content-md-end align-items-center">
                                <div class="me-3">
                                    <small class="text-muted">Logged in as:</small>
                                    <div class="fw-bold"><?php echo $_SESSION['org_name'] ?? 'Organization'; ?></div>
                                </div>
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="bi bi-building text-white fs-5"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Summary Cards -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="dashboard-card stat-card">
                            <div class="stat-number"><?php echo $total_pending; ?></div>
                            <div class="stat-label">Pending Payments</div>
                            <div class="text-muted mt-2">
                                <i class="bi bi-clock text-warning"></i> Awaiting confirmation
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-card stat-card">
                            <div class="stat-number">₱<?php echo number_format($total_amount, 2); ?></div>
                            <div class="stat-label">Total Amount Due</div>
                            <div class="text-muted mt-2">
                                <i class="bi bi-cash-stack text-success"></i> Across all year levels
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-card stat-card">
                            <div class="stat-number"><?php echo count($yearly_totals); ?></div>
                            <div class="stat-label">Year Levels</div>
                            <div class="text-muted mt-2">
                                <i class="bi bi-layers text-primary"></i> With pending payments
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pending Payments by Year Level -->
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold mb-0">Pending Payments</h3>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-filter"></i> Filter by Year
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="filterYear('all')">All Years</a></li>
                                <?php foreach (array_keys($payments_by_year) as $year): ?>
                                    <li><a class="dropdown-item" href="#" onclick="filterYear('<?php echo $year; ?>')"><?php echo $year; ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    
                    <?php if (empty($pending_payments)): ?>
                        <div class="empty-state">
                            <i class="bi bi-check-circle"></i>
                            <h4>No Pending Payments</h4>
                            <p>All student payments have been processed.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($payments_by_year as $year_level => $payments): ?>
                            <div class="year-section mb-5" data-year="<?php echo $year_level; ?>">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <span class="year-level-badge"><?php echo $year_level; ?></span>
                                        <span class="text-muted ms-3">
                                            <?php echo count($payments); ?> student(s) • Total: ₱<?php echo number_format($yearly_totals[$year_level], 2); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Student Information</th>
                                                <th>ID Number</th>
                                                <th>Amount</th>
                                                <th>Receipt</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($payments as $payment): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($payment['lastname'] . ', ' . $payment['firstname']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($payment['student_email']); ?></small>
                                                    </td>
                                                    <td class="fw-bold"><?php echo htmlspecialchars($payment['id_no']); ?></td>
                                                    <td class="payment-amount">₱<?php echo number_format($payment['amount'], 2); ?></td>
                                                    <td>
                                                        <?php if (!empty($payment['receipt_url'])): ?>
                                                            <a href="<?php echo htmlspecialchars($payment['receipt_url']); ?>" 
                                                               target="_blank" 
                                                               class="btn btn-sm btn-view">
                                                                <i class="bi bi-receipt"></i> View
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-warning">No receipt</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="bi bi-clock"></i> Pending
                                                        </span>
                                                    </td>
                                                    <td class="action-buttons">
                                                        <button class="btn btn-accept btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#acceptModal"
                                                                data-payment-id="<?php echo $payment['id']; ?>"
                                                                data-student-name="<?php echo htmlspecialchars($payment['firstname'] . ' ' . $payment['lastname']); ?>">
                                                            <i class="bi bi-check-circle"></i> Accept
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Accept Payment Modal -->
    <div class="modal fade" id="acceptModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title">Accept Payment</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="payment_id_input" name="payment_id">
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> You are about to accept payment from <strong id="student_name_display"></strong>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control notes-textarea" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="Add any notes about this payment..."></textarea>
                            <div class="form-text">This will be recorded in the payment history.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="accept_payment" class="btn btn-accept">
                            <i class="bi bi-check-circle"></i> Confirm Acceptance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050">
            <div class="toast show" role="alert">
                <div class="toast-header bg-success text-white">
                    <i class="bi bi-check-circle me-2"></i>
                    <strong class="me-auto">Success</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050">
            <div class="toast show" role="alert">
                <div class="toast-header bg-danger text-white">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <strong class="me-auto">Error</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Modal handling
        const acceptModal = document.getElementById('acceptModal');
        if (acceptModal) {
            acceptModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const paymentId = button.getAttribute('data-payment-id');
                const studentName = button.getAttribute('data-student-name');
                
                document.getElementById('payment_id_input').value = paymentId;
                document.getElementById('student_name_display').textContent = studentName;
            });
        }
        
        // Year filtering
        function filterYear(year) {
            const yearSections = document.querySelectorAll('.year-section');
            
            yearSections.forEach(section => {
                if (year === 'all' || section.getAttribute('data-year') === year) {
                    section.style.display = 'block';
                } else {
                    section.style.display = 'none';
                }
            });
            
            // Update dropdown text
            const dropdownButton = document.querySelector('.dropdown-toggle');
            if (year === 'all') {
                dropdownButton.innerHTML = '<i class="bi bi-filter"></i> All Years';
            } else {
                dropdownButton.innerHTML = `<i class="bi bi-filter"></i> ${year}`;
            }
        }
        
        // Auto-hide toast messages
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                setTimeout(() => {
                    const bsToast = new bootstrap.Toast(toast);
                    bsToast.hide();
                }, 5000);
            });
        });
    </script>
</body>
</html>