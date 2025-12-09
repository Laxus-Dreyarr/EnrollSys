<?php
// Assuming similar user data structure for organization
// $firstname = $user->user_information->firstname ?? 'Organization';
// $lastname = $user->user_information->lastname ?? '';
// $organization_id = $user->user_information->organization->id ?? 'ORG001';
// $profile_picture = $user->profile ?? 'default.png';
$firstname = $user->info->firstname;
$lastname = $user->info->lastname ?? '';
$org_id = $user->info->organization_id ?? 'ORG007';
$profile_picture = $user->profile ?? 'default.png';

// Check if student ID is 'none' (case-insensitive)
$show_student_form = (strtolower($org_id) === 'none');

?>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="application-title" content="EnrollSys">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#101126">
    <meta name="msapplication-navbutton-color" content="#101126">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Organization Dashboard | EnrollSys</title>
    <link rel="website icon" href="{{ asset('img/logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="{{ asset('style/google-fonts.css') }}" rel="stylesheet">
    <link href="{{ asset('style/bootstrap.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/org/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/student/dashboard.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar" id="main-sidebar">
            <div class="sidebar-header">
                <h3>Enroll<span class="logo-accent">Sys</span></h3>
            </div>
            
            <div class="user-profile">
                @if(!empty($profile_picture) && $profile_picture !== 'default.png')
                    <img src="{{ asset('profile/' . $profile_picture) }}" alt="User Avatar" class="user-avatar">
                @else
                    <div class="avatar-container">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(($firstname ?? '') . ' ' . ($lastname ?? '')) }}&background=none&color=fff" alt="User Avatar" class="user-avatar">
                        <div class="status-indicator"></div>
                    </div>
                @endif
                
                <h4 class="user-name">{{ $firstname ?? 'Organization' }}</h4>
                <p class="user-id">{{ $org_id }}</p>
            </div>

            
            <div class="sidebar-menu">
                <a class="menu-item active" data-section="dashboard">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a class="menu-item" data-section="payment-verification">
                    <i class="fas fa-money-check"></i>
                    <span>Payment Verification</span>
                </a>
                <a class="menu-item" data-section="students" onclick="student()"> 
                    <i class="fas fa-users"></i>
                    <span>Students</span>
                </a>
                <a class="menu-item" data-section="profile">
                    <i class="fas fa-user-circle"></i>
                    <span>Profile</span>
                </a>
                <a class="menu-item" data-section="settings" onclick="settings_clk()">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a class="menu-item logout-item" id="logout-btn" role="button" tabindex="0">
                    <div class="menu-icon">
                        <i class="fas fa-arrow-right-from-bracket"></i>
                    </div>
                    <span class="menu-text">Logout</span>
                    <div class="menu-hover-effect"></div>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1 class="page-title">Organization</h1>

                <!-- Search Bar -->
                <div class="search-container">
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Search students, requests...">
                        <button class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <div class="header-actions">
                    <div class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count">5</span>
                    </div>
                    <div class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Section -->
            <div id="dashboard-section" class="content-section active">
                <!-- Stats Overview -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon courses">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h3 class="stat-value">0</h3> <!-- Will be updated by JS -->
                        <p class="stat-label">Pending Enrollment Requests</p>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon grades">
                            <i class="fas fa-money-check"></i>
                        </div>
                        <h3 class="stat-value">0</h3> <!-- Will be updated by JS -->
                        <p class="stat-label">Pending Payment Verifications</p>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon attendance">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="stat-value">0</h3> <!-- Will be updated by JS -->
                        <p class="stat-label">Total Students</p>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon deadlines">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="stat-value">0</h3> <!-- Will be updated by JS -->
                        <p class="stat-label">Approved This Week</p>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <h2 class="section-title">Recent Activity</h2>
                <h2 class="section-title">Pending Payment Verifications</h2>
                <div class="schedule-container">
                    <div class="schedule-day">
                        <h4 class="day-header">Today, {{ \Carbon\Carbon::now()->format('F j, Y') }}</h4>
                        <!-- Payment activities will be dynamically inserted here -->
                    </div>
                </div>

                <!-- Quick Actions -->
                <h2 class="section-title">Quick Actions</h2>
                <div class="courses-grid">
                    
                    <div class="course-card" style="cursor: pointer;" onclick="showSection('payment-verification')">
                        <div class="course-header">
                            <h3 class="course-code"><i class="fas fa-money-check"></i></h3>
                            <p class="course-name">Verify Payments</p>
                        </div>
                        <div class="course-body">
                            <div class="course-info">
                                <span>8 pending verifications</span>
                                <span>Amount: ₱1,200</span>
                            </div>
                            <div class="progress-container">
                                <div class="progress-label">
                                    <span>Verification Rate</span>
                                    <span>85%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress" style="width: 85%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="course-card" style="cursor: pointer;" onclick="showSection('students')">
                        <div class="course-header">
                            <h3 class="course-code"><i class="fas fa-users"></i></h3>
                            <p class="course-name">Manage Students</p>
                        </div>
                        <div class="course-body">
                            <div class="course-info">
                                <span>127 total students</span>
                                <span>42 new this month</span>
                            </div>
                            <div class="progress-container">
                                <div class="progress-label">
                                    <span>Active Students</span>
                                    <span>92%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress" style="width: 92%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
            
            <!-- Payment Verification Section -->
            <div id="payment-verification-section" class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Payment Verification</h2>
                    <div class="section-actions">
                        <button class="btn-primary" id="filter-payments">
                            <i class="fas fa-filter"></i>
                            Filter
                        </button>
                    </div>
                </div>
                
                <div class="schedule-container">
                    <!-- Payment Stats -->
                    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 24px;">
                        <div class="stat-card">
                            <div class="stat-icon pending">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h3 class="stat-value">8</h3>
                            <p class="stat-label">Pending Verification</p>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon verified">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3 class="stat-value">24</h3>
                            <p class="stat-label">Verified Today</p>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon rejected">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <h3 class="stat-value">3</h3>
                            <p class="stat-label">Rejected Today</p>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon total">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <h3 class="stat-value">₱3,600</h3>
                            <p class="stat-label">Total Processed</p>
                        </div>
                    </div>

                    <!-- Payments Table -->
                    <div class="payments-table">
                        <table class="grades-table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Reference No.</th>
                                    <th>Date Paid</th>
                                    <th>Receipt</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>2020-30617</td>
                                    <td>Emily Willis</td>
                                    <td>₱150.00</td>
                                    <td>GCash</td>
                                    <td>GC-789123456</td>
                                    <td>2024-01-15</td>
                                    <td>
                                        <button class="btn-action view-receipt" data-receipt="receipt1.jpg">
                                            <i class="fas fa-receipt"></i>
                                        </button>
                                    </td>
                                    <td><span class="status-badge pending">Pending</span></td>
                                    <td>
                                        <button class="btn-action verify-payment" data-id="1">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn-action reject-payment" data-id="1">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2021-45128</td>
                                    <td>Lexi Lore</td>
                                    <td>₱150.00</td>
                                    <td>GCash</td>
                                    <td>GC-789123457</td>
                                    <td>2024-01-14</td>
                                    <td>
                                        <button class="btn-action view-receipt" data-receipt="receipt2.jpg">
                                            <i class="fas fa-receipt"></i>
                                        </button>
                                    </td>
                                    <td><span class="status-badge verified">Verified</span></td>
                                    <td>
                                        <button class="btn-action view-details" data-id="2">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2019-28745</td>
                                    <td>Abella Danger</td>
                                    <td>₱150.00</td>
                                    <td>GCash</td>
                                    <td>GC-789123458</td>
                                    <td>2024-01-13</td>
                                    <td>
                                        <button class="btn-action view-receipt" data-receipt="receipt3.jpg">
                                            <i class="fas fa-receipt"></i>
                                        </button>
                                    </td>
                                    <td><span class="status-badge rejected">Rejected</span></td>
                                    <td>
                                        <button class="btn-action review-payment" data-id="3">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Students Section -->
            <div id="students-section-0926" class="content-section student-management-section">
                <div class="section-header-0926">
                    <h2 class="section-title-0926">Student Management</h2>
                    <div class="section-actions-0926">
                        <button class="btn-primary-0926" id="add-student-btn-0926">
                            <i class="fas fa-plus"></i>
                            Add Student
                        </button>
                        <button class="btn-secondary-0926" id="export-students-btn-0926">
                            <i class="fas fa-download"></i>
                            Export
                        </button>
                    </div>
                </div>
                
                <div class="student-container-0926">
                    <!-- Enhanced Search and Filters -->
                    <div class="search-filters-0926">
                        <div class="search-wrapper-0926">
                            <div class="search-box-0926">
                                <i class="fas fa-search search-icon-0926"></i>
                                <input type="text" 
                                    placeholder="Search students by name, ID, or program..." 
                                    id="student-search-0926"
                                    class="search-input-0926">
                                <button class="search-clear-0926" id="clear-search-0926">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <button class="filter-toggle-0926" id="filter-toggle-0926">
                                <i class="fas fa-filter"></i>
                                <span class="filter-text-0926">Filters</span>
                                <span class="filter-count-0926" id="active-filters-count-0926">0</span>
                            </button>
                        </div>

                        <!-- Collapsible Filter Options -->
                        <div class="filter-options-0926" id="filter-options-0926">
                            <div class="filter-group-0926">
                                <label class="filter-label-0926">Program</label>
                                <select id="program-filter-0926" class="filter-select-0926">
                                    <option value="">All Programs</option>
                                    <option value="bsit">BS Information Technology</option>
                                    <option value="bscs">BS Computer Science</option>
                                    <option value="bsis">BS Information Systems</option>
                                </select>
                            </div>
                            <div class="filter-group-0926">
                                <label class="filter-label-0926">Year Level</label>
                                <select id="year-level-filter-0926" class="filter-select-0926">
                                    <option value="">All Year Levels</option>
                                    <option value="1">1st Year</option>
                                    <option value="2">2nd Year</option>
                                    <option value="3">3rd Year</option>
                                    <option value="4">4th Year</option>
                                </select>
                            </div>
                            <div class="filter-group-0926">
                                <label class="filter-label-0926">Status</label>
                                <select id="status-filter-0926" class="filter-select-0926">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>
                            <div class="filter-actions-0926">
                                <button class="btn-secondary-0926" id="reset-filters-0926">
                                    Reset
                                </button>
                                <button class="btn-primary-0926" id="apply-filters-0926">
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Results Header -->
                    <div class="results-header-0926">
                        <div class="results-count-0926">
                            Showing <span id="students-count-0926">3</span> students
                        </div>
                        <div class="view-toggle-0926">
                            <button class="view-btn-0926 active" data-view="grid">
                                <i class="fas fa-th"></i>
                            </button>
                            <button class="view-btn-0926" data-view="list">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Students Grid/List -->
                    <div class="students-container-0926" id="students-container-0926">
                        <!-- Grid View -->
                        <div class="students-grid-0926 active" id="grid-view-0926">
                            <div class="student-card-0926">
                                <div class="card-header-0926">
                                    <div class="student-avatar-0926">
                                        <img src="https://ui-avatars.com/api/?name=C+J&background=4361ee&color=fff" 
                                            alt="John Smith"
                                            class="avatar-img-0926">
                                        <div class="status-indicator-0926 active"></div>
                                    </div>
                                    <div class="student-basic-info-0926">
                                        <h4 class="student-name-0926">Carl James Duallo</h4>
                                        <p class="student-id-0926">2020-30617</p>
                                    </div>
                                    <div class="card-actions-0926">
                                        <div class="dropdown-0926">
                                            <button class="dropdown-toggle-0926">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu-0926">
                                                <button class="dropdown-item-0926 view-student-0926" data-id="1">
                                                    <i class="fas fa-eye"></i> View Details
                                                </button>
                                                <button class="dropdown-item-0926 edit-student-0926" data-id="1">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="dropdown-item-0926 deactivate-student-0926" data-id="1">
                                                    <i class="fas fa-user-slash"></i> Deactivate
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body-0926">
                                    <div class="info-row-0926">
                                        <i class="fas fa-graduation-cap"></i>
                                        <span class="student-program-0926">BS Information Technology</span>
                                    </div>
                                    <div class="info-row-0926">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span class="student-year-0926">3rd Year</span>
                                    </div>
                                    <div class="info-row-0926">
                                        <i class="fas fa-clock"></i>
                                        <span class="enrollment-date-0926">Enrolled: Nov 15, 2025</span>
                                    </div>
                                </div>
                                <div class="card-footer-0926">
                                    <span class="status-badge-0926 active">Officially Enrolled</span>
                                    <div class="quick-actions-0926">
                                        <button class="btn-action-0926 view-student-0926" data-id="1" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action-0926 edit-student-0926" data-id="1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="student-card-0926">
                                <div class="card-header-0926">
                                    <div class="student-avatar-0926">
                                        <img src="https://ui-avatars.com/api/?name=R+I&background=4361ee&color=fff" 
                                            alt="Sarah Johnson"
                                            class="avatar-img-0926">
                                        <div class="status-indicator-0926 active"></div>
                                    </div>
                                    <div class="student-basic-info-0926">
                                        <h4 class="student-name-0926">Raziel Insigne</h4>
                                        <p class="student-id-0926">2021-45128</p>
                                    </div>
                                    <div class="card-actions-0926">
                                        <div class="dropdown-0926">
                                            <button class="dropdown-toggle-0926">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu-0926">
                                                <button class="dropdown-item-0926 view-student-0926" data-id="2">
                                                    <i class="fas fa-eye"></i> View Details
                                                </button>
                                                <button class="dropdown-item-0926 edit-student-0926" data-id="2">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="dropdown-item-0926 deactivate-student-0926" data-id="2">
                                                    <i class="fas fa-user-slash"></i> Deactivate
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body-0926">
                                    <div class="info-row-0926">
                                        <i class="fas fa-graduation-cap"></i>
                                        <span class="student-program-0926">BS Information Technology</span>
                                    </div>
                                    <div class="info-row-0926">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span class="student-year-0926">2nd Year</span>
                                    </div>
                                    <div class="info-row-0926">
                                        <i class="fas fa-clock"></i>
                                        <span class="enrollment-date-0926">Enrolled: Nov 20, 2025</span>
                                    </div>
                                </div>
                                <div class="card-footer-0926">
                                    <span class="status-badge-0926 active" style="background: #b88123ff; color: white">Pending</span>
                                    <div class="quick-actions-0926">
                                        <button class="btn-action-0926 view-student-0926" data-id="2" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action-0926 edit-student-0926" data-id="2" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- List View (Hidden by default) -->
                        <div class="students-list-0926" id="list-view-0926">
                            <table class="students-table-0926">
                                <thead>
                                    <tr>
                                        <th class="student-col-0926">Student</th>
                                        <th class="program-col-0926">Program</th>
                                        <th class="year-col-0926">Year Level</th>
                                        <th class="status-col-0926">Status</th>
                                        <th class="actions-col-0926">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="student-cell-0926">
                                                <img src="https://ui-avatars.com/api/?name=John+Smith&background=4361ee&color=fff" 
                                                    alt="John Smith"
                                                    class="avatar-sm-0926">
                                                <div>
                                                    <div class="student-name-0926">John Michael Smith</div>
                                                    <div class="student-id-0926">2020-30617</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>BS Information Technology</td>
                                        <td>3rd Year</td>
                                        <td><span class="status-badge-0926 active">Pending</span></td>
                                        <td>
                                            <div class="table-actions-0926">
                                                <button class="btn-action-0926 view-student-0926" data-id="1" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn-action-0926 edit-student-0926" data-id="1" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Additional rows would go here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-0926">
                        <button class="page-btn-0926 prev-0926" disabled>
                            <i class="fas fa-chevron-left"></i>
                            Previous
                        </button>
                        <div class="page-numbers-0926">
                            <button class="page-number-0926 active">1</button>
                            <button class="page-number-0926">2</button>
                            <button class="page-number-0926">3</button>
                            <span class="page-ellipsis-0926">...</span>
                            <button class="page-number-0926">10</button>
                        </div>
                        <button class="page-btn-0926 next-0926">
                            Next
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Profile Section -->
            <div id="profile-section" class="content-section">
                <div class="profile-header">
                    <h2 class="section-title">Organization</h2>
                    <p class="profile-subtitle">Manage your organization information</p>
                </div>
                
                <div class="profile-container">
                    <!-- Profile Summary Card -->
                    <div class="profile-summary-card">
                        <div class="profile-avatar-section">
                            <div class="avatar-container">
                                <img src="{{ !empty($profile_picture) && $profile_picture !== 'default.png' ? asset('profile/' . $profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode(($firstname ?? '') . ' ' . ($lastname ?? '')) . '&background=4361ee&color=fff&size=150' }}" 
                                    alt="Organization Avatar" 
                                    class="profile-avatar">
                            </div>
                            
                            <button class="btn-secondary btn-avatar">
                                <i class="fas fa-camera"></i>
                                Change Logo
                            </button>
                        </div>
                        
                        <div class="profile-info-summary">
                            <h3 class="profile-name">{{ $firstname ?? 'Organization' }} {{ $lastname ?? '' }}</h3>
                            <p class="profile-id">Organization ID: {{ $org_id }}</p>
                            <div class="profile-badge">
                                <i class="fas fa-building"></i>
                                Active Organization
                            </div>
                            
                            <div class="profile-stats">
                                <div class="profile-stat">
                                    <span class="stat-number">127</span>
                                    <span class="stat-label">Students</span>
                                </div>
                                <div class="profile-stat">
                                    <span class="stat-number">15</span>
                                    <span class="stat-label">Pending</span>
                                </div>
                                <div class="profile-stat">
                                    <span class="stat-number">01/2024</span>
                                    <span class="stat-label">Since</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Organization Details Card -->
                    <div class="profile-details-card">
                        <div class="card-header">
                            <h4>Organization</h4>
                            <button class="btn-edit" id="edit-org-profile-btn">
                                <i class="fas fa-edit"></i>
                                Edit Profile
                            </button>
                        </div>
                        
                        <form class="profile-form" id="org-profile-form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="orgName" class="form-label">
                                        <i class="fas fa-building"></i>
                                        Organization Name
                                    </label>
                                    <input type="text" class="form-control" id="orgName" value="Computer Studies Department" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="orgEmail" class="form-label">
                                        <i class="fas fa-envelope"></i>
                                        Email Address
                                    </label>
                                    <input type="email" class="form-control" id="orgEmail" value="csd@evsu.edu.ph" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="orgPhone" class="form-label">
                                        <i class="fas fa-phone"></i>
                                        Phone Number
                                    </label>
                                    <input type="tel" class="form-control" id="orgPhone" value="+63 53 123 4567" readonly>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label for="orgAddress" class="form-label">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Address
                                    </label>
                                    <input type="text" class="form-control" id="orgAddress" value="EVSU Ormoc City, Leyte" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="orgHead" class="form-label">
                                        <i class="fas fa-user-tie"></i>
                                        Organization Head
                                    </label>
                                    <input type="text" class="form-control" id="orgHead" value="" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="orgType" class="form-label">
                                        <i class="fas fa-university"></i>
                                        Organization Type
                                    </label>
                                    <input type="text" class="form-control" id="orgType" value="Academic Department" readonly>
                                </div>
                            </div>
                            
                            <div class="form-actions" id="org-form-actions" style="display: none;">
                                <button type="button" class="btn-cancel" id="cancel-org-edit">Cancel</button>
                                <button type="submit" class="btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Settings Section -->
            <div id="settings-section" class="content-section">
                <h2 class="section-title">Org Settings</h2>
                
                <div class="settings-card">
                    <h4>Appearance Settings</h4>
                    
                    <div class="settings-option dark-mode-toggle">
                        <div class="option-info">
                            <h5>Dark Mode</h5>
                            <p>Switch between light and dark theme</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="dark-mode-toggle">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="settings-option theme-picker">
                        <div class="option-info">
                            <h5>Theme Color</h5>
                            <p>Choose your preferred accent color for the interface</p>
                            <div class="current-theme-info">
                                <small>Current: <span id="current-theme-name">Blue</span></small>
                            </div>
                        </div>
                        <div class="theme-colors">
                            <div class="color-option active" data-color="#4361ee" data-name="Blue" style="background-color: #4361ee;" title="Blue Theme">
                                <div class="color-tooltip">Blue</div>
                            </div>
                            <div class="color-option" data-color="#2c5530" data-name="Green" style="background-color: #2c5530;" title="Green Theme">
                                <div class="color-tooltip">Green</div>
                            </div>
                            <div class="color-option" data-color="#8b5cf6" data-name="Purple" style="background-color: #8b5cf6;" title="Purple Theme">
                                <div class="color-tooltip">Purple</div>
                            </div>
                            <div class="color-option" data-color="#ef4444" data-name="Red" style="background-color: #ef4444;" title="Red Theme">
                                <div class="color-tooltip">Red</div>
                            </div>
                            <div class="color-option" data-color="#f59e0b" data-name="Orange" style="background-color: #f59e0b;" title="Orange Theme">
                                <div class="color-tooltip">Orange</div>
                            </div>
                            <div class="color-option" data-color="#800000" data-name="Maroon" style="background-color: #800000;" title="Maroon Theme">
                                <div class="color-tooltip">Maroon</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="settings-card">
                    <h4>Org Settings</h4>
                    
                    <div class="settings-option">
                        <div class="option-info">
                            <h5>Enrollment Period</h5>
                            <p>Set the active enrollment period for students</p>
                        </div>
                        <button class="btn-primary">Configure</button>
                    </div>
                    
                    <div class="settings-option">
                        <div class="option-info">
                            <h5>Payment Configuration</h5>
                            <p>Manage payment methods and fees</p>
                        </div>
                        <button class="btn-primary">Configure</button>
                    </div>
                    
                    <div class="settings-option">
                        <div class="option-info">
                            <h5>Notification Templates</h5>
                            <p>Customize email and notification templates</p>
                        </div>
                        <button class="btn-primary">Manage</button>
                    </div>
                </div>
                
                <div class="settings-card">
                    <h4>User Management</h4>
                    
                    <div class="settings-option">
                        <div class="option-info">
                            <h5>Add Organization Member</h5>
                            <p>Invite new members to your organization</p>
                        </div>
                        <button class="btn-primary">Invite</button>
                    </div>
                    
                    <div class="settings-option">
                        <div class="option-info">
                            <h5>Manage Permissions</h5>
                            <p>Set permissions for organization members</p>
                        </div>
                        <button class="btn-primary">Manage</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Logout Form -->
    <form id="logout-form" action="{{ route('org.logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- Logout Confirmation Modal -->
    <div id="logout-modal" class="logout-modal-overlay">
        <div class="logout-modal">
            <div class="logout-modal-header">
                <div class="logout-modal-icon">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <h3 class="logout-modal-title">Confirm Logout</h3>
            </div>
            <p class="logout-modal-message">
                Are you sure you want to logout? You will need to log in again to access your dashboard.
            </p>
            <div class="logout-modal-actions">
                <button class="logout-modal-btn logout-modal-cancel" id="logout-cancel-btn">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
                <button class="logout-modal-btn logout-modal-confirm" id="logout-confirm-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Yes, Logout
                </button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{asset('js/jquery.js')}}"></script>
    <script src="{{asset('js/sweetalert2.js')}}"></script>
    <!-- Student Management -->
    <script src="{{asset('js/function/org/dashboard/dashboard.js')}}"></script>
    
</body>
</html>