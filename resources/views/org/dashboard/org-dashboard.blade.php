<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="application-title" content="EnrollSys">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#101126">
    <meta name="msapplication-navbutton-color" content="#101126">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Organization Fee Management | Dashboard</title>
    <link rel="website icon" href="{{ asset('img/evsu-logo.png') }}">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/org/dashboard.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
</head>
<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="logo-container">
            <div class="logo-icon">
                <i class="fas fa-university"></i>
            </div>
            <div class="logo-text">
                <h2>EduManage</h2>
                <span>Organization Portal</span>
            </div>
            <button class="menu-toggle sidebar-close" style="position: absolute; right: 1rem; top: 1.5rem; display: none; background: var(--gray-100);">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <nav>
            <ul class="nav-links">
                <li><a href="#" class="active" data-section="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="#" data-section="students"><i class="fas fa-users"></i> Students</a></li>
                <li><a href="#" data-section="payments"><i class="fas fa-file-invoice-dollar"></i> Payments</a></li>
                <li><a href="#" data-section="analytics"><i class="fas fa-chart-bar"></i> Analytics</a></li>
                <li><a href="#" data-section="settings"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="#" data-section="help"><i class="fas fa-question-circle"></i> Help</a></li>
            </ul>
        </nav>
        
        <div class="sidebar-footer" style="position: absolute; bottom: 1.5rem; width: calc(100% - 2rem);">
            <div class="user-profile">
                <div class="user-avatar">AD</div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 600; font-size: 0.875rem; line-height: 1.3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Admin User</div>
                    <div style="font-size: 0.75rem; color: var(--gray-500); line-height: 1.3;">Administrator</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Overlay for mobile sidebar -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Refresh indicator -->
    <div class="refresh-indicator" id="refreshIndicator"></div>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Dashboard Section -->
        <section id="dashboard-section" class="content-section active">
            <!-- Top Bar -->
            <header class="top-bar">
                <button class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="page-title">
                    <h1>Fee Management</h1>
                    <p>Monitor and manage student fee payments</p>
                </div>
                
                <div class="top-bar-actions">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center justify-content-between w-100" type="button" id="yearDropdown" data-bs-toggle="dropdown">
                            <span><i class="fas fa-calendar-alt me-2"></i> 2023-2024</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="yearDropdown">
                            <li><a class="dropdown-item" href="#">2023-2024</a></li>
                            <li><a class="dropdown-item" href="#">2022-2023</a></li>
                            <li><a class="dropdown-item" href="#">2021-2022</a></li>
                        </ul>
                    </div>
                    
                    <button class="btn btn-primary d-flex align-items-center justify-content-center">
                        <i class="fas fa-plus me-2"></i> <span>Add Payment</span>
                    </button>
                </div>
            </header>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card fade-in">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value" id="total-pending">12</div>
                            <div class="stat-label">Pending Payments</div>
                        </div>
                        <div class="stat-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up me-1"></i> 2 from yesterday
                    </div>
                </div>
                
                <div class="stat-card fade-in">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value" id="total-amount">₱15,400</div>
                            <div class="stat-label">Pending Amount</div>
                        </div>
                        <div class="stat-icon amount">
                            <i class="fas fa-peso-sign"></i>
                        </div>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up me-1"></i> ₱1,200
                    </div>
                </div>
                
                <div class="stat-card fade-in">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value" id="accepted-today">3</div>
                            <div class="stat-label">Accepted Today</div>
                        </div>
                        <div class="stat-icon accepted">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up me-1"></i> 1 from yesterday
                    </div>
                </div>
                
                <div class="stat-card fade-in">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value" id="total-students">48</div>
                            <div class="stat-label">Total Students</div>
                        </div>
                        <div class="stat-icon students">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up me-1"></i> 5 this week
                    </div>
                </div>
            </div>

            <!-- Year Level Sections -->
            <div id="year-level-sections">
                @foreach (['First Year', 'Second Year', 'Third Year', 'Fourth Year'] as $year)
                    @php
                        $yearKey = strtolower(str_replace(' ', '_', $year));
                        $yearData = $yearLevelData[$yearKey] ?? [
                            'payments' => collect([]),
                            'total' => 0,
                            'pending_count' => 0
                        ];
                    @endphp
                    
                    <div class="year-level-card fade-in">
                        <div class="card-header">
                            <h3 class="card-title">{{ $year }} Students</h3>
                            <span class="card-badge" id="{{ str_replace(' ', '-', strtolower($year)) }}-pending">
                                {{ $yearData['pending_count'] }} pending
                            </span>
                        </div>
                        
                        <div class="student-table-container">
                            <table class="student-table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>ID Number</th>
                                        <th>Contact</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date Submitted</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="{{ str_replace(' ', '-', strtolower($year)) }}-students">
                                    @if($yearData['payments']->count() > 0)
                                        @foreach($yearData['payments'] as $payment)
                                            <tr>
                                                <td>
                                                    <div class="student-info">
                                                        <div class="avatar">
                                                            @if(!empty($payment->firstname))
                                                                <span>{{ substr($payment->firstname, 0, 1) }}</span>
                                                            @else
                                                                <span>N</span>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <div class="student-name">
                                                                {{ $payment->firstname ?? 'N/A' }} 
                                                                {{ $payment->lastname ?? '' }}
                                                            </div>
                                                            <div class="student-email">
                                                                {{ $payment->email ?? 'No email' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $payment->id_no ?? 'N/A' }}</td>
                                                <td>{{ $payment->phone_number ?? 'No contact' }}</td>
                                                <td class="amount">₱{{ number_format($payment->amount, 2) }}</td>
                                                <td>
                                                    <span class="status-badge status-{{ strtolower($payment->status) }}">
                                                        {{ ucfirst($payment->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($payment->created_at)->format('M d, Y h:i A') }}
                                                </td>
                                                <td>
                                                    <div class="action-buttons d-flex gap-2">
                                                        <button class="btn btn-sm btn-success approve-payment" 
                                                                data-payment-id="{{ $payment->id }}"
                                                                data-student-id="{{ $payment->student_id }}">
                                                            <i class="fas fa-check"></i> Approve
                                                        </button>
                                                        <button class="btn btn-sm btn-danger reject-payment" 
                                                                data-payment-id="{{ $payment->id }}"
                                                                data-student-id="{{ $payment->student_id }}">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                        @if(!empty($payment->file_path))
                                                            <a href="{{ asset($payment->file_path) }}" 
                                                            target="_blank"
                                                            class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye"></i> View Receipt
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                                    <p>No pending payments found</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="card-footer">
                            <div class="total-amount">
                                Total Pending: <span id="{{ str_replace(' ', '-', strtolower($year)) }}-total">
                                    ₱{{ number_format($yearData['total'], 2) }}
                                </span>
                            </div>
                            <button class="btn btn-outline-primary d-flex align-items-center justify-content-center export-btn" 
                                    data-year="{{ $yearKey }}">
                                <i class="fas fa-download me-2"></i> Export
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Footer -->
            <footer class="text-center mt-4 pt-3 border-top" style="color: var(--gray-500); font-size: 0.875rem; padding: 0 0.5rem;">
                <p><i class="fas fa-info-circle me-2"></i> Only pending payments shown. Updated: Today, 2:45 PM</p>
            </footer>
        </section>

        <!-- Students Section -->
        <section id="students-section" class="content-section">
            <!-- Top Bar -->
            <header class="top-bar">
                <button class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="page-title">
                    <h1>Students</h1>
                    <p>Manage all students in the organization</p>
                </div>
                
                <div class="top-bar-actions">
                    <button class="btn btn-primary d-flex align-items-center justify-content-center">
                        <i class="fas fa-plus me-2"></i> <span>Add Student</span>
                    </button>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card fade-in">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">48</div>
                            <div class="stat-label">Total Students</div>
                        </div>
                        <div class="stat-icon students">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up me-1"></i> 5 this week
                    </div>
                </div>
                
                <div class="stat-card fade-in">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">24</div>
                            <div class="stat-label">Paid This Month</div>
                        </div>
                        <div class="stat-icon accepted">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up me-1"></i> 3 from last month
                    </div>
                </div>
                
                <div class="stat-card fade-in">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">12</div>
                            <div class="stat-label">Pending</div>
                        </div>
                        <div class="stat-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="stat-change" style="background: #fee2e2; color: #dc2626;">
                        <i class="fas fa-arrow-down me-1"></i> 2 from yesterday
                    </div>
                </div>
                
                <div class="stat-card fade-in">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">4</div>
                            <div class="stat-label">Overdue</div>
                        </div>
                        <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                    <div class="stat-change" style="background: #fee2e2; color: #dc2626;">
                        <i class="fas fa-arrow-up me-1"></i> 1 from yesterday
                    </div>
                </div>
            </div>

            <div class="year-level-card">
                <div class="card-header">
                    <h3 class="card-title">All Students</h3>
                    <div class="d-flex flex-column flex-sm-row gap-2 w-100">
                        <input type="text" class="form-control" placeholder="Search students...">
                        <button class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                            <i class="fas fa-filter me-2"></i> Filter
                        </button>
                    </div>
                </div>
                
                <div class="student-table-container">
                    <table class="student-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Student ID</th>
                                <th>Year</th>
                                <th>Program</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="all-students-list">
                            <!-- All students will be populated here -->
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer">
                    <div class="total-amount">
                        Showing <span id="students-count">48</span> students
                    </div>
                    <button class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                        <i class="fas fa-download me-2"></i> Export All
                    </button>
                </div>
            </div>
        </section>

        <!-- Payments Section -->
        <section id="payments-section" class="content-section">
            <!-- Top Bar -->
            <header class="top-bar">
                <button class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="page-title">
                    <h1>Payments</h1>
                    <p>Track payment transactions</p>
                </div>
                
                <div class="top-bar-actions">
                    <button class="btn btn-primary d-flex align-items-center justify-content-center">
                        <i class="fas fa-plus me-2"></i> <span>Record Payment</span>
                    </button>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card fade-in">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">₱32,400</div>
                            <div class="stat-label">Collected</div>
                        </div>
                        <div class="stat-icon amount">
                            <i class="fas fa-peso-sign"></i>
                        </div>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up me-1"></i> ₱4,200
                    </div>
                </div>
                
                <div class="stat-card fade-in">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">₱15,400</div>
                            <div class="stat-label">Pending</div>
                        </div>
                        <div class="stat-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="stat-change" style="background: #fee2e2; color: #dc2626;">
                        <i class="fas fa-arrow-down me-1"></i> ₱800
                    </div>
                </div>
                
                <div class="stat-card fade-in">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">27</div>
                            <div class="stat-label">Successful</div>
                        </div>
                        <div class="stat-icon accepted">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up me-1"></i> 3 from yesterday
                    </div>
                </div>
                
                <div class="stat-card fade-in">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">4</div>
                            <div class="stat-label">Overdue</div>
                        </div>
                        <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                    <div class="stat-change" style="background: #fee2e2; color: #dc2626;">
                        <i class="fas fa-arrow-up me-1"></i> 1 from yesterday
                    </div>
                </div>
            </div>

            <div class="year-level-card">
                <div class="card-header">
                    <h3 class="card-title">Recent Payments</h3>
                    <div class="d-flex flex-column flex-sm-row gap-2 w-100">
                        <select class="form-select">
                            <option>All Status</option>
                            <option>Paid</option>
                            <option>Pending</option>
                            <option>Overdue</option>
                        </select>
                        <input type="month" class="form-control">
                    </div>
                </div>
                
                <div class="student-table-container">
                    <table class="student-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Student</th>
                                <th>Payment ID</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Receipt</th>
                            </tr>
                        </thead>
                        <tbody id="payments-list">
                            <!-- Payments will be populated here -->
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer">
                    <div class="total-amount">
                        Total: <span id="payments-total">₱32,400</span>
                    </div>
                    <button class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                        <i class="fas fa-download me-2"></i> Export
                    </button>
                </div>
            </div>
        </section>

        <!-- Analytics Section -->
        <section id="analytics-section" class="content-section">
            <!-- Top Bar -->
            <header class="top-bar">
                <button class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="page-title">
                    <h1>Analytics</h1>
                    <p>Insights and trends in fee collection</p>
                </div>
                
                <div class="top-bar-actions">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center justify-content-between" type="button" id="analyticsPeriod" data-bs-toggle="dropdown">
                            <span><i class="fas fa-calendar-alt me-2"></i> This Month</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="analyticsPeriod">
                            <li><a class="dropdown-item" href="#">This Week</a></li>
                            <li><a class="dropdown-item" href="#">This Month</a></li>
                            <li><a class="dropdown-item" href="#">This Quarter</a></li>
                            <li><a class="dropdown-item" href="#">This Year</a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card fade-in">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">85%</div>
                            <div class="stat-label">Collection Rate</div>
                        </div>
                        <div class="stat-icon analytics">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up me-1"></i> 5% from last month
                    </div>
                </div>
                
                <div class="stat-card fade-in">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">₱47,800</div>
                            <div class="stat-label">Total Revenue</div>
                        </div>
                        <div class="stat-icon amount">
                            <i class="fas fa-peso-sign"></i>
                        </div>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up me-1"></i> 12% from last year
                    </div>
                </div>
                
                <div class="stat-card fade-in">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">3.2 days</div>
                            <div class="stat-label">Avg. Processing Time</div>
                        </div>
                        <div class="stat-icon" style="background: #f0f9ff; color: #0ea5e9;">
                            <i class="fas fa-stopwatch"></i>
                        </div>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-down me-1"></i> 0.5 days from last month
                    </div>
                </div>
                
                <div class="stat-card fade-in">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">94%</div>
                            <div class="stat-label">Student Satisfaction</div>
                        </div>
                        <div class="stat-icon" style="background: #f0fdf4; color: #16a34a;">
                            <i class="fas fa-smile"></i>
                        </div>
                    </div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up me-1"></i> 2% from last quarter
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mb-3">
                    <div class="year-level-card">
                        <div class="card-header">
                            <h3 class="card-title">Collection Trends</h3>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary btn-sm active">Monthly</button>
                                <button class="btn btn-outline-secondary btn-sm">Quarterly</button>
                                <button class="btn btn-outline-secondary btn-sm">Yearly</button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <div style="height: 250px; display: flex; align-items: center; justify-content: center; color: var(--gray-400); flex-direction: column;">
                                <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                <h4>Collection Trends Chart</h4>
                                <p class="mb-0 text-center">Visualization of fee collection over time</p>
                                <div class="mt-3 d-flex gap-3">
                                    <div class="text-center">
                                        <div class="fw-bold text-primary">₱15,400</div>
                                        <small class="text-muted">Current Month</small>
                                    </div>
                                    <div class="text-center">
                                        <div class="fw-bold text-success">₱32,400</div>
                                        <small class="text-muted">Last Month</small>
                                    </div>
                                    <div class="text-center">
                                        <div class="fw-bold text-warning">+12%</div>
                                        <small class="text-muted">Growth</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="year-level-card">
                        <div class="card-header">
                            <h3 class="card-title">Payment Methods</h3>
                        </div>
                        <div class="chart-container">
                            <div style="height: 250px; display: flex; align-items: center; justify-content: center; color: var(--gray-400); flex-direction: column;">
                                <i class="fas fa-chart-pie fa-3x mb-3"></i>
                                <h4>Payment Distribution</h4>
                                <p class="mb-0 text-center">Breakdown of payment methods used</p>
                                <div class="mt-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2" style="width: 12px; height: 12px; background-color: #4361ee; border-radius: 2px;"></div>
                                        <small>GCash (45%)</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2" style="width: 12px; height: 12px; background-color: #10b981; border-radius: 2px;"></div>
                                        <small>Bank Transfer (30%)</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2" style="width: 12px; height: 12px; background-color: #f59e0b; border-radius: 2px;"></div>
                                        <small>Cash (25%)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="year-level-card">
                <div class="card-header">
                    <h3 class="card-title">Year-Level Performance</h3>
                </div>
                <div class="row">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="text-center p-3 border rounded">
                            <div class="fw-bold text-primary">First Year</div>
                            <div class="fs-4 fw-bold">92%</div>
                            <small class="text-muted">Collection Rate</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="text-center p-3 border rounded">
                            <div class="fw-bold text-primary">Second Year</div>
                            <div class="fs-4 fw-bold">88%</div>
                            <small class="text-muted">Collection Rate</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="text-center p-3 border rounded">
                            <div class="fw-bold text-primary">Third Year</div>
                            <div class="fs-4 fw-bold">85%</div>
                            <small class="text-muted">Collection Rate</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="text-center p-3 border rounded">
                            <div class="fw-bold text-primary">Fourth Year</div>
                            <div class="fs-4 fw-bold">90%</div>
                            <small class="text-muted">Collection Rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Settings Section -->
        <section id="settings-section" class="content-section">
            <!-- Top Bar -->
            <header class="top-bar">
                <button class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="page-title">
                    <h1>Settings</h1>
                    <p>Configure organization and system preferences</p>
                </div>
            </header>

            <div class="row">
                <div class="col-lg-3 mb-4">
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action active" data-settings-tab="organization">
                            <i class="fas fa-university me-2"></i> Organization
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" data-settings-tab="fees">
                            <i class="fas fa-money-bill-wave me-2"></i> Fee Settings
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" data-settings-tab="notifications">
                            <i class="fas fa-bell me-2"></i> Notifications
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" data-settings-tab="users">
                            <i class="fas fa-users me-2"></i> User Management
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" data-settings-tab="security">
                            <i class="fas fa-shield-alt me-2"></i> Security
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-9">
                    <!-- Organization Settings Tab -->
                    <div class="settings-tab active" id="organization-tab">
                        <div class="year-level-card">
                            <h3 class="card-title mb-4">Organization Settings</h3>
                            <form>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Organization Name</label>
                                        <input type="text" class="form-control" value="EduManage University">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Academic Year</label>
                                        <input type="text" class="form-control" value="2023-2024">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Organization Address</label>
                                    <textarea class="form-control" rows="3">123 University Ave, Manila, Philippines</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Contact Email</label>
                                        <input type="email" class="form-control" value="info@edumanage.edu">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Contact Phone</label>
                                        <input type="text" class="form-control" value="(02) 8123-4567">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Fee Settings Tab -->
                    <div class="settings-tab" id="fees-tab">
                        <div class="year-level-card">
                            <h3 class="card-title mb-4">Fee Settings</h3>
                            <form>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Base Organization Fee</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" value="500">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Late Payment Fee</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" value="100">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Payment Deadline</label>
                                    <input type="date" class="form-control" value="2023-11-30">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Allowed Payment Methods</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="cashMethod" checked>
                                        <label class="form-check-label" for="cashMethod">Cash</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="gcashMethod" checked>
                                        <label class="form-check-label" for="gcashMethod">GCash</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="bankTransferMethod" checked>
                                        <label class="form-check-label" for="bankTransferMethod">Bank Transfer</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Fee Settings</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Notifications Tab -->
                    <div class="settings-tab" id="notifications-tab">
                        <div class="year-level-card">
                            <h3 class="card-title mb-4">Notification Settings</h3>
                            <form>
                                <div class="mb-3">
                                    <label class="form-label">Email Notifications</label>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="paymentReminders" checked>
                                        <label class="form-check-label" for="paymentReminders">Payment Reminders</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="paymentConfirmations" checked>
                                        <label class="form-check-label" for="paymentConfirmations">Payment Confirmations</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="systemAlerts">
                                        <label class="form-check-label" for="systemAlerts">System Alerts</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SMS Notifications</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="smsReminders">
                                        <label class="form-check-label" for="smsReminders">Enable SMS Reminders</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reminder Frequency</label>
                                    <select class="form-select">
                                        <option>Daily</option>
                                        <option selected>Weekly</option>
                                        <option>Monthly</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Notification Settings</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- User Management Tab -->
                    <div class="settings-tab" id="users-tab">
                        <div class="year-level-card">
                            <h3 class="card-title mb-4">User Management</h3>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Role</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar me-2">AD</div>
                                                    <div>Admin User</div>
                                                </div>
                                            </td>
                                            <td>Administrator</td>
                                            <td>admin@edumanage.edu</td>
                                            <td><span class="badge bg-success">Active</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary me-1">Edit</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar me-2" style="background: linear-gradient(135deg, #10b981, #059669);">TM</div>
                                                    <div>Treasurer Member</div>
                                                </div>
                                            </td>
                                            <td>Treasurer</td>
                                            <td>treasurer@edumanage.edu</td>
                                            <td><span class="badge bg-success">Active</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary me-1">Edit</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <button class="btn btn-primary mt-3">
                                <i class="fas fa-plus me-2"></i> Add New User
                            </button>
                        </div>
                    </div>
                    
                    <!-- Security Tab -->
                    <div class="settings-tab" id="security-tab">
                        <div class="year-level-card">
                            <h3 class="card-title mb-4">Security Settings</h3>
                            <form>
                                <div class="mb-3">
                                    <label class="form-label">Two-Factor Authentication</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enable2FA">
                                        <label class="form-check-label" for="enable2FA">Enable Two-Factor Authentication</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Session Timeout</label>
                                    <select class="form-select">
                                        <option>15 minutes</option>
                                        <option>30 minutes</option>
                                        <option selected>1 hour</option>
                                        <option>4 hours</option>
                                        <option>8 hours</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password Policy</label>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="requireStrongPasswords" checked>
                                        <label class="form-check-label" for="requireStrongPasswords">Require strong passwords</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="passwordExpiration">
                                        <label class="form-check-label" for="passwordExpiration">Enable password expiration (90 days)</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Security Settings</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Help Section -->
        <section id="help-section" class="content-section">
            <!-- Top Bar -->
            <header class="top-bar">
                <button class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <div class="page-title">
                    <h1>Help & Support</h1>
                    <p>Get assistance and learn how to use the system</p>
                </div>
            </header>

            <div class="row">
                <div class="col-lg-4 mb-3">
                    <div class="help-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value"><i class="fas fa-question-circle fa-2x"></i></div>
                                <div class="stat-label">FAQs</div>
                            </div>
                        </div>
                        <p class="mt-3">Browse frequently asked questions about fee management, payments, and system usage.</p>
                        <button class="btn btn-outline-primary w-100 mt-2">View FAQs</button>
                    </div>
                    
                    <div class="help-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value"><i class="fas fa-book fa-2x"></i></div>
                                <div class="stat-label">Documentation</div>
                            </div>
                        </div>
                        <p class="mt-3">Complete user guide and system documentation for administrators and users.</p>
                        <button class="btn btn-outline-primary w-100 mt-2">Read Docs</button>
                    </div>
                    
                    <div class="help-card">
                        <div class="stat-header">
                            <div>
                                <div class="stat-value"><i class="fas fa-video fa-2x"></i></div>
                                <div class="stat-label">Video Tutorials</div>
                            </div>
                        </div>
                        <p class="mt-3">Step-by-step video guides for common tasks and features.</p>
                        <button class="btn btn-outline-primary w-100 mt-2">Watch Tutorials</button>
                    </div>
                </div>
                
                <div class="col-lg-8">
                    <div class="year-level-card">
                        <h3 class="card-title mb-4">Contact Support</h3>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h5><i class="fas fa-envelope me-2"></i> Email Support</h5>
                                    <p class="text-muted">support@edumanage.edu</p>
                                    <p>Response time: Within 24 hours</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h5><i class="fas fa-phone me-2"></i> Phone Support</h5>
                                    <p class="text-muted">(02) 8123-4567</p>
                                    <p>Available: Mon-Fri, 9AM-5PM</p>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="mt-4">Submit a Support Ticket</h5>
                        <form>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Your Name</label>
                                    <input type="text" class="form-control" placeholder="Enter your name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Your Email</label>
                                    <input type="email" class="form-control" placeholder="Enter your email">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subject</label>
                                <input type="text" class="form-control" placeholder="Enter subject">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" rows="4" placeholder="Describe your issue"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Priority</label>
                                <select class="form-select">
                                    <option>Low</option>
                                    <option selected>Medium</option>
                                    <option>High</option>
                                    <option>Urgent</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Ticket</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Payment Accepted Modal (Mobile Optimized) -->
    <div class="modal fade" id="paymentAcceptedModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center w-100">
                        <div class="stat-icon accepted me-3">
                            <i class="fas fa-check"></i>
                        </div>
                        <div style="flex: 1;">
                            <h5 class="modal-title mb-1">Payment Accepted</h5>
                            <p class="text-muted mb-0" style="font-size: 0.875rem;">Payment recorded successfully</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="alert alert-success" role="alert" style="font-size: 0.9375rem;">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong id="accepted-student-name">Student Name</strong>'s payment of 
                        <strong id="accepted-amount">₱0</strong> accepted.
                    </div>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                        <i class="fas fa-clock me-1"></i> <span id="accepted-timestamp">Today, 2:45 PM</span>
                    </p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Continue</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{asset('js/jquery.js')}}"></script>
    <script src="{{asset('js/sweetalert2.js')}}"></script>
    <!-- <script src="{{asset('js/sweetalert3.js')}}"></script> -->
    <script src="{{asset('js/function/org/dashboard/dashboard.js')}}"></script>
    <script>
$(document).ready(function() {
    // Initialize mobile sidebar
    setupMobileSidebar();
    
    // Initialize settings tabs
    setupSettingsTabs();
    
    // Setup navigation
    setupNavigation();
    
    // Setup touch interactions
    setupTouchInteractions();
});

// Simplified setupMobileSidebar function
function setupMobileSidebar() {
    const menuToggleButtons = document.querySelectorAll('.menu-toggle:not(.sidebar-close)');
    const sidebar = document.querySelector('.sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const sidebarCloseBtn = document.querySelector('.sidebar-close');
    
    // Function to open sidebar
    function openSidebar() {
        if (sidebar) {
            sidebar.classList.add('active');
            if (sidebarOverlay) {
                sidebarOverlay.classList.add('active');
            }
            document.body.style.overflow = 'hidden';
        }
    }
    
    // Function to close sidebar
    function closeSidebar() {
        if (sidebar) {
            sidebar.classList.remove('active');
            if (sidebarOverlay) {
                sidebarOverlay.classList.remove('active');
            }
            document.body.style.overflow = '';
        }
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
        if (e.key === 'Escape' && sidebar && sidebar.classList.contains('active')) {
            closeSidebar();
        }
    });
    
    // Handle window resize
    function updateSidebarCloseButton() {
        if (window.innerWidth <= 1024 && sidebarCloseBtn) {
            sidebarCloseBtn.style.display = 'flex';
        } else if (sidebarCloseBtn) {
            sidebarCloseBtn.style.display = 'none';
        }
    }
    
    updateSidebarCloseButton();
    window.addEventListener('resize', updateSidebarCloseButton);
}

// Setup navigation between sections
function setupNavigation() {
    const navLinks = document.querySelectorAll('.nav-links a');
    
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
                window.scrollTo(0, 0);
            }
        });
    });
}

// Setup settings tabs
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

// Setup touch interactions
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
    });
}
</script>
</body>
</html>