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
    <link rel="stylesheet" href="{{ asset('css/student/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/org/dashboard.css') }}">
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
                <a class="menu-item" data-section="enrollment-requests">
                    <i class="fas fa-user-graduate"></i>
                    <span>Enrollment Requests</span>
                </a>
                <a class="menu-item" data-section="payment-verification">
                    <i class="fas fa-money-check"></i>
                    <span>Payment Verification</span>
                </a>
                <a class="menu-item" data-section="students">
                    <i class="fas fa-users"></i>
                    <span>Students</span>
                </a>
                <a class="menu-item" data-section="profile">
                    <i class="fas fa-user-circle"></i>
                    <span>Profile</span>
                </a>
                <a class="menu-item" data-section="settings">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a class="menu-item" id="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
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
                        <h3 class="stat-value">15</h3>
                        <p class="stat-label">Pending Enrollment Requests</p>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon grades">
                            <i class="fas fa-money-check"></i>
                        </div>
                        <h3 class="stat-value">8</h3>
                        <p class="stat-label">Pending Payment Verifications</p>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon attendance">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="stat-value">127</h3>
                        <p class="stat-label">Total Students</p>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon deadlines">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="stat-value">42</h3>
                        <p class="stat-label">Approved This Week</p>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <h2 class="section-title">Recent Activity</h2>
                <div class="schedule-container">
                    <div class="schedule-day">
                        <h4 class="day-header">Today, {{ \Carbon\Carbon::now()->format('F j, Y') }}</h4>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">10:30 AM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">New enrollment request from John Smith</div>
                                <div class="schedule-location">BS in Information Technology - 3rd Year</div>
                            </div>
                        </div>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">09:15 AM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Payment verified for Sarah Johnson</div>
                                <div class="schedule-location">GCash Receipt #GC-789123</div>
                            </div>
                        </div>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">08:45 AM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Enrollment approved for Michael Brown</div>
                                <div class="schedule-location">5 subjects enrolled</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <h2 class="section-title">Quick Actions</h2>
                <div class="courses-grid">
                    <div class="course-card" style="cursor: pointer;" onclick="showSection('enrollment-requests')">
                        <div class="course-header">
                            <h3 class="course-code"><i class="fas fa-user-graduate"></i></h3>
                            <p class="course-name">Review Enrollment Requests</p>
                        </div>
                        <div class="course-body">
                            <div class="course-info">
                                <span>15 pending requests</span>
                                <span>Priority: High</span>
                            </div>
                            <div class="progress-container">
                                <div class="progress-label">
                                    <span>Processing Rate</span>
                                    <span>78%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress" style="width: 78%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
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
            
            <!-- Enrollment Requests Section -->
            <div id="enrollment-requests-section" class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Enrollment Requests</h2>
                    <div class="section-actions">
                        <button class="btn-primary" id="filter-requests">
                            <i class="fas fa-filter"></i>
                            Filter
                        </button>
                        <button class="btn-secondary" id="export-requests">
                            <i class="fas fa-download"></i>
                            Export
                        </button>
                    </div>
                </div>
                
                <div class="schedule-container">
                    <!-- Filter Options -->
                    <div class="filter-options">
                        <div class="filter-group">
                            <label>Status:</label>
                            <select id="status-filter" class="filter-select">
                                <option value="all">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Program:</label>
                            <select id="program-filter" class="filter-select">
                                <option value="all">All Programs</option>
                                <option value="bsit">BS Information Technology</option>
                                <option value="bscs">BS Computer Science</option>
                                <option value="bsis">BS Information Systems</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Date Range:</label>
                            <input type="date" id="date-from" class="filter-date">
                            <span>to</span>
                            <input type="date" id="date-to" class="filter-date">
                        </div>
                    </div>

                    <!-- Requests Table -->
                    <div class="requests-table">
                        <table class="grades-table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Program</th>
                                    <th>Year Level</th>
                                    <th>Requested Subjects</th>
                                    <th>Date Submitted</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>2020-30617</td>
                                    <td>John Michael Smith</td>
                                    <td>BS Information Technology</td>
                                    <td>3rd Year</td>
                                    <td>5 subjects</td>
                                    <td>2024-01-15</td>
                                    <td><span class="status-badge pending">Pending</span></td>
                                    <td>
                                        <button class="btn-action view-request" data-id="1">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action approve-request" data-id="1">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn-action reject-request" data-id="1">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2021-45128</td>
                                    <td>Sarah Marie Johnson</td>
                                    <td>BS Computer Science</td>
                                    <td>2nd Year</td>
                                    <td>6 subjects</td>
                                    <td>2024-01-14</td>
                                    <td><span class="status-badge approved">Approved</span></td>
                                    <td>
                                        <button class="btn-action view-request" data-id="2">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action revoke-request" data-id="2">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2019-28745</td>
                                    <td>Michael Anthony Brown</td>
                                    <td>BS Information Systems</td>
                                    <td>4th Year</td>
                                    <td>4 subjects</td>
                                    <td>2024-01-13</td>
                                    <td><span class="status-badge rejected">Rejected</span></td>
                                    <td>
                                        <button class="btn-action view-request" data-id="3">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action reconsider-request" data-id="3">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination">
                        <button class="page-btn" disabled>Previous</button>
                        <span class="page-info">Page 1 of 5</span>
                        <button class="page-btn">Next</button>
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
                                    <td>John Michael Smith</td>
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
                                    <td>Sarah Marie Johnson</td>
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
                                    <td>Michael Anthony Brown</td>
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
            <div id="students-section" class="content-section">
                <h2 class="section-title">Student Management</h2>
                
                <div class="schedule-container">
                    <!-- Student Search and Filters -->
                    <div class="search-filters">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" placeholder="Search students by name or ID..." id="student-search">
                        </div>
                        <div class="filter-options">
                            <select id="program-filter">
                                <option value="">All Programs</option>
                                <option value="bsit">BS Information Technology</option>
                                <option value="bscs">BS Computer Science</option>
                                <option value="bsis">BS Information Systems</option>
                            </select>
                            <select id="year-level-filter">
                                <option value="">All Year Levels</option>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                            </select>
                            <select id="status-filter">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>

                    <!-- Students Grid -->
                    <div class="students-grid">
                        <div class="student-card">
                            <div class="student-avatar">
                                <img src="https://ui-avatars.com/api/?name=John+Smith&background=4361ee&color=fff" alt="John Smith">
                            </div>
                            <div class="student-info">
                                <h4>John Michael Smith</h4>
                                <p class="student-id">2020-30617</p>
                                <p class="student-program">BS Information Technology</p>
                                <p class="student-year">3rd Year</p>
                            </div>
                            <div class="student-status">
                                <span class="status-badge active">Active</span>
                                <div class="student-actions">
                                    <button class="btn-action view-student" data-id="1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-action edit-student" data-id="1">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="student-card">
                            <div class="student-avatar">
                                <img src="https://ui-avatars.com/api/?name=Sarah+Johnson&background=4361ee&color=fff" alt="Sarah Johnson">
                            </div>
                            <div class="student-info">
                                <h4>Sarah Marie Johnson</h4>
                                <p class="student-id">2021-45128</p>
                                <p class="student-program">BS Computer Science</p>
                                <p class="student-year">2nd Year</p>
                            </div>
                            <div class="student-status">
                                <span class="status-badge active">Active</span>
                                <div class="student-actions">
                                    <button class="btn-action view-student" data-id="2">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-action edit-student" data-id="2">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="student-card">
                            <div class="student-avatar">
                                <img src="https://ui-avatars.com/api/?name=Michael+Brown&background=4361ee&color=fff" alt="Michael Brown">
                            </div>
                            <div class="student-info">
                                <h4>Michael Anthony Brown</h4>
                                <p class="student-id">2019-28745</p>
                                <p class="student-program">BS Information Systems</p>
                                <p class="student-year">4th Year</p>
                            </div>
                            <div class="student-status">
                                <span class="status-badge pending">Pending</span>
                                <div class="student-actions">
                                    <button class="btn-action view-student" data-id="3">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-action approve-student" data-id="3">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
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
                                    <input type="text" class="form-control" id="orgAddress" value="EVSU Main Campus, Ormoc City, Leyte" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="orgHead" class="form-label">
                                        <i class="fas fa-user-tie"></i>
                                        Organization Head
                                    </label>
                                    <input type="text" class="form-control" id="orgHead" value="Dr. Maria Santos" readonly>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{asset('js/jquery.js')}}"></script>
    <script src="{{asset('js/sweetalert2.js')}}"></script>
    <script src="{{asset('js/function/org/dashboard/dashboard.js')}}"></script>
</body>
</html>