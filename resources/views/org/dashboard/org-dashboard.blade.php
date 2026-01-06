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
                <!-- First Year -->
                <div class="year-level-card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">First Year Students</h3>
                        <span class="card-badge">6 pending</span>
                    </div>
                    
                    <div class="student-table-container">
                        <table class="student-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Program</th>
                                    <th>Contact</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="first-year-students">
                                <!-- First year students will be populated here -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="card-footer">
                        <div class="total-amount">
                            Total: <span id="first-year-total">₱4,200</span>
                        </div>
                        <button class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                            <i class="fas fa-download me-2"></i> Export
                        </button>
                    </div>
                </div>
                
                <!-- Second Year -->
                <div class="year-level-card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">Second Year Students</h3>
                        <span class="card-badge">6 pending</span>
                    </div>
                    
                    <div class="student-table-container">
                        <table class="student-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Program</th>
                                    <th>Contact</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="second-year-students">
                                <!-- Second year students will be populated here -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="card-footer">
                        <div class="total-amount">
                            Total: <span id="second-year-total">₱5,600</span>
                        </div>
                        <button class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                            <i class="fas fa-download me-2"></i> Export
                        </button>
                    </div>
                </div>
                
                <!-- Third Year -->
                <div class="year-level-card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">Third Year Students</h3>
                        <span class="card-badge">5 pending</span>
                    </div>
                    
                    <div class="student-table-container">
                        <table class="student-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Program</th>
                                    <th>Contact</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="third-year-students">
                                <!-- Third year students will be populated here -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="card-footer">
                        <div class="total-amount">
                            Total: <span id="third-year-total">₱3,800</span>
                        </div>
                        <button class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                            <i class="fas fa-download me-2"></i> Export
                        </button>
                    </div>
                </div>
                
                <!-- Fourth Year -->
                <div class="year-level-card fade-in">
                    <div class="card-header">
                        <h3 class="card-title">Fourth Year Students</h3>
                        <span class="card-badge">3 pending</span>
                    </div>
                    
                    <div class="student-table-container">
                        <table class="student-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Program</th>
                                    <th>Contact</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="fourth-year-students">
                                <!-- Fourth year students will be populated here -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="card-footer">
                        <div class="total-amount">
                            Total: <span id="fourth-year-total">₱1,800</span>
                        </div>
                        <button class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                            <i class="fas fa-download me-2"></i> Export
                        </button>
                    </div>
                </div>
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
    
    <script>
        // Sample data for students with pending organization fees
        const studentsData = [
            // First Year Students
            { id: 1, name: "Maria Santos", studentId: "2023-001", yearLevel: "First Year", program: "BS Computer Science", contact: "0917-123-4567", amount: 500, status: "pending", avatarInitials: "MS" },
            { id: 2, name: "Juan Dela Cruz", studentId: "2023-002", yearLevel: "First Year", program: "BS Information Technology", contact: "0922-987-6543", amount: 500, status: "pending", avatarInitials: "JC" },
            { id: 3, name: "Ana Reyes", studentId: "2023-003", yearLevel: "First Year", program: "BS Business Administration", contact: "0933-456-7890", amount: 500, status: "pending", avatarInitials: "AR" },
            { id: 4, name: "Carlos Lopez", studentId: "2023-004", yearLevel: "First Year", program: "BS Psychology", contact: "0918-777-8888", amount: 500, status: "pending", avatarInitials: "CL" },
            { id: 5, name: "Sofia Garcia", studentId: "2023-005", yearLevel: "First Year", program: "BS Nursing", contact: "0927-111-2222", amount: 500, status: "pending", avatarInitials: "SG" },
            { id: 6, name: "Miguel Torres", studentId: "2023-006", yearLevel: "First Year", program: "BS Education", contact: "0938-333-4444", amount: 700, status: "pending", avatarInitials: "MT" },
            
            // Second Year Students
            { id: 7, name: "Luis Mendoza", studentId: "2022-011", yearLevel: "Second Year", program: "BS Computer Science", contact: "0917-555-6666", amount: 700, status: "pending", avatarInitials: "LM" },
            { id: 8, name: "Elena Castro", studentId: "2022-012", yearLevel: "Second Year", program: "BS Information Technology", contact: "0922-999-0000", amount: 700, status: "pending", avatarInitials: "EC" },
            { id: 9, name: "Roberto Santos", studentId: "2022-013", yearLevel: "Second Year", program: "BS Business Administration", contact: "0933-121-2121", amount: 700, status: "pending", avatarInitials: "RS" },
            { id: 10, name: "Carmen Reyes", studentId: "2022-014", yearLevel: "Second Year", program: "BS Psychology", contact: "0918-343-4343", amount: 700, status: "pending", avatarInitials: "CR" },
            { id: 11, name: "Fernando Cruz", studentId: "2022-015", yearLevel: "Second Year", program: "BS Nursing", contact: "0927-565-6565", amount: 700, status: "pending", avatarInitials: "FC" },
            { id: 12, name: "Isabel Morales", studentId: "2022-016", yearLevel: "Second Year", program: "BS Education", contact: "0938-787-8787", amount: 700, status: "pending", avatarInitials: "IM" },
            
            // Third Year Students
            { id: 13, name: "Antonio Navarro", studentId: "2021-021", yearLevel: "Third Year", program: "BS Computer Science", contact: "0917-909-0909", amount: 600, status: "pending", avatarInitials: "AN" },
            { id: 14, name: "Patricia Lim", studentId: "2021-022", yearLevel: "Third Year", program: "BS Information Technology", contact: "0922-123-1234", amount: 600, status: "pending", avatarInitials: "PL" },
            { id: 15, name: "Ricardo Tan", studentId: "2021-023", yearLevel: "Third Year", program: "BS Business Administration", contact: "0933-234-2345", amount: 600, status: "pending", avatarInitials: "RT" },
            { id: 16, name: "Victoria Chua", studentId: "2021-024", yearLevel: "Third Year", program: "BS Psychology", contact: "0918-345-3456", amount: 600, status: "pending", avatarInitials: "VC" },
            { id: 17, name: "Jose Fernandez", studentId: "2021-025", yearLevel: "Third Year", program: "BS Nursing", contact: "0927-456-4567", amount: 400, status: "pending", avatarInitials: "JF" },
            
            // Fourth Year Students
            { id: 18, name: "Gloria Ramos", studentId: "2020-031", yearLevel: "Fourth Year", program: "BS Computer Science", contact: "0917-567-5678", amount: 600, status: "pending", avatarInitials: "GR" },
            { id: 19, name: "Manuel Sy", studentId: "2020-032", yearLevel: "Fourth Year", program: "BS Information Technology", contact: "0922-678-6789", amount: 600, status: "pending", avatarInitials: "MS" },
            { id: 20, name: "Teresa Ong", studentId: "2020-033", yearLevel: "Fourth Year", program: "BS Business Administration", contact: "0933-789-7890", amount: 600, status: "pending", avatarInitials: "TO" }
        ];

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
                    <td>${payment.method}</td>
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
                    
                    setTimeout(() => {
                        yearLevelSections.style.opacity = '1';
                    }, 300);
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
    </script>
</body>
</html>