<?php
$firstname = 'Dofalmingo';
$lastname = 'Donquixote';
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
    <title>Instructor Dashboard | EnrollSys</title>
    <link rel="website icon" href="{{ asset('img/logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="{{ asset('style/google-fonts.css') }}" rel="stylesheet">
    <link href="{{ asset('style/bootstrap.css') }}" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'AbrilFatface';
            src: url("{{ asset('font/BBHSansHegarty-Regular.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/instructor/dashboard.css') }}">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <i class="fas fa-graduation-cap logo-icon"></i>
                    <h3>Enroll<span>Sys</span></h3>
                </div>
            </div>
            
            <div class="user-profile">
                @if(!empty($profile_picture) && $profile_picture !== 'default.png')
                    <div class="avatar-container">
                        <img src="{{ asset('profile/' . $profile_picture) }}" alt="User Avatar" class="user-avatar">
                        <div class="status-indicator"></div>
                    </div>
                @else
                    <div class="avatar-container">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(($firstname ?? '') . ' ' . ($lastname ?? '')) }}&background=4361ee&color=fff" alt="User Avatar" class="user-avatar">
                        <div class="status-indicator"></div>
                    </div>
                @endif
                
                <div class="user-info">
                    <h4 class="user-name">Zeref Dragneel</h4>
                    <p class="user-id">ID: 007</p>
                    <span class="user-role">Instructor</span>
                </div>
            </div>

            <div class="sidebar-menu">
                <a class="menu-item active" data-section="dashboard">
                    <div class="menu-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <span class="menu-text">Dashboard</span>
                    <div class="menu-hover-effect"></div>
                </a>
                <a class="menu-item" data-section="courses">
                    <div class="menu-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <span class="menu-text">My Courses</span>
                    <div class="menu-hover-effect"></div>
                </a>
                <a class="menu-item" data-section="schedule">
                    <div class="menu-icon">
                        <i class="fas fa-calendar-days"></i>
                    </div>
                    <span class="menu-text">Teaching Schedule</span>
                    <div class="menu-hover-effect"></div>
                </a>
                <a class="menu-item" data-section="students">
                    <div class="menu-icon">
                        <i class="fas fa-user-group"></i>
                    </div>
                    <span class="menu-text">Students</span>
                    <div class="menu-hover-effect"></div>
                </a>
                <a class="menu-item" data-section="assignments">
                    <div class="menu-icon">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <span class="menu-text">Assignments</span>
                    <div class="menu-hover-effect"></div>
                </a>
                <a class="menu-item" data-section="grades">
                    <div class="menu-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <span class="menu-text">Grade Management</span>
                    <div class="menu-hover-effect"></div>
                </a>
                <a class="menu-item" data-section="profile">
                    <div class="menu-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <span class="menu-text">Profile</span>
                    <div class="menu-hover-effect"></div>
                </a>
                <a class="menu-item" data-section="settings">
                    <div class="menu-icon">
                        <i class="fas fa-sliders"></i>
                    </div>
                    <span class="menu-text">Settings</span>
                    <div class="menu-hover-effect"></div>
                </a>
                <a class="menu-item logout-item" id="logout-btn">
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
                <h1 class="page-title">Instructor Dashboard</h1>

                <!-- Search Bar -->
                <div class="search-container">
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Search students, courses, assignments...">
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
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">4</h3>
                            <p class="stat-label">Courses Teaching</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon students">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">127</h3>
                            <p class="stat-label">Total Students</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon assignments">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h3 class="stat-value">8</h3>
                        <p class="stat-label">Pending Grading</p>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon deadlines">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <h3 class="stat-value">3</h3>
                        <p class="stat-label">Upcoming Classes</p>
                    </div>
                    
                </div>
                
                <!-- Enhanced Quick Actions -->
                <h2 class="section-title">Quick Actions</h2>
                <div class="quick-actions-grid">
                    <div class="quick-action-card" data-action="create-assignment">
                        <div class="action-icon">
                            <i class="fas fa-file-circle-plus"></i>
                        </div>
                        <h4>Create Assignment</h4>
                        <p>Create new assignment for your courses</p>
                        <div class="action-hover-effect"></div>
                    </div>
                    
                    <div class="quick-action-card" data-action="manage-grades">
                        <div class="action-icon">
                            <i class="fas fa-pen-to-square"></i>
                        </div>
                        <h4>Input Grades</h4>
                        <p>Update student grades and performance</p>
                        <div class="action-hover-effect"></div>
                    </div>
                    
                    <div class="quick-action-card" data-action="view-students">
                        <div class="action-icon">
                            <i class="fas fa-users-viewfinder"></i>
                        </div>
                        <h4>Student Roster</h4>
                        <p>View and manage student lists</p>
                        <div class="action-hover-effect"></div>
                    </div>
                    
                    <div class="quick-action-card" data-action="upload-materials">
                        <div class="action-icon">
                            <i class="fas fa-cloud-arrow-up"></i>
                        </div>
                        <h4>Upload Materials</h4>
                        <p>Share course materials with students</p>
                        <div class="action-hover-effect"></div>
                    </div>
                </div>
                
                <!-- Teaching Schedule -->
                <h2 class="section-title">Today's Classes</h2>
                <div class="schedule-container">
                    <div class="schedule-day">
                        <h4 class="day-header">Monday, August 30</h4>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">10:00 AM - 11:30 AM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Software Engineering (IT 373)</div>
                                <div class="schedule-location">CS-302 | 35 Students</div>
                            </div>
                            <div class="schedule-action">
                                <button class="btn-primary btn-sm">Take Attendance</button>
                            </div>
                        </div>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">2:00 PM - 3:30 PM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Data Structures (CS 301)</div>
                                <div class="schedule-location">CS-105 | 42 Students</div>
                            </div>
                            <div class="schedule-action">
                                <button class="btn-primary btn-sm">Take Attendance</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <h2 class="section-title">Recent Activity</h2>
                <div class="activity-container">
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-file-upload"></i>
                        </div>
                        <div class="activity-content">
                            <h4>New Assignment Created</h4>
                            <p>Software Engineering Project - Due Sep 15</p>
                            <span class="activity-time">2 hours ago</span>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="activity-content">
                            <h4>Attendance Submitted</h4>
                            <p>Data Structures - 38/42 students present</p>
                            <span class="activity-time">Yesterday</span>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="activity-content">
                            <h4>Grades Updated</h4>
                            <p>Midterm exams graded for IT 373</p>
                            <span class="activity-time">2 days ago</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- My Courses Section -->
            <div id="courses-section" class="content-section">
                <div class="section-header">
                    <h2 class="section-title">My Courses</h2>
                    <button class="btn-primary">
                        <i class="fas fa-plus"></i>
                        Add Course
                    </button>
                </div>
                
                <div class="courses-grid">
                    <div class="course-card">
                        <div class="course-header">
                            <h3 class="course-code">IT 373</h3>
                            <p class="course-name">Software Engineering</p>
                        </div>
                        <div class="course-body">
                            <div class="course-info">
                                <span><i class="fas fa-users"></i> 35 Students</span>
                                <span><i class="fas fa-clock"></i> Mon/Wed 10:00 AM</span>
                            </div>
                            <div class="course-info">
                                <span><i class="fas fa-map-marker-alt"></i> CS-302</span>
                                <span><i class="fas fa-book"></i> 3 Units</span>
                            </div>
                            <div class="course-stats">
                                <div class="stat">
                                    <span class="stat-value">92%</span>
                                    <span class="stat-label">Attendance</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-value">85%</span>
                                    <span class="stat-label">Avg Grade</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-value">2</span>
                                    <span class="stat-label">Pending</span>
                                </div>
                            </div>
                            <div class="course-actions">
                                <button class="btn-secondary">View Students</button>
                                <button class="btn-primary">Manage</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="course-card">
                        <div class="course-header">
                            <h3 class="course-code">CS 301</h3>
                            <p class="course-name">Data Structures</p>
                        </div>
                        <div class="course-body">
                            <div class="course-info">
                                <span><i class="fas fa-users"></i> 42 Students</span>
                                <span><i class="fas fa-clock"></i> Tue/Thu 2:00 PM</span>
                            </div>
                            <div class="course-info">
                                <span><i class="fas fa-map-marker-alt"></i> CS-105</span>
                                <span><i class="fas fa-book"></i> 4 Units</span>
                            </div>
                            <div class="course-stats">
                                <div class="stat">
                                    <span class="stat-value">88%</span>
                                    <span class="stat-label">Attendance</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-value">78%</span>
                                    <span class="stat-label">Avg Grade</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-value">5</span>
                                    <span class="stat-label">Pending</span>
                                </div>
                            </div>
                            <div class="course-actions">
                                <button class="btn-secondary">View Students</button>
                                <button class="btn-primary">Manage</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="course-card">
                        <div class="course-header">
                            <h3 class="course-code">IT 401</h3>
                            <p class="course-name">Web Development</p>
                        </div>
                        <div class="course-body">
                            <div class="course-info">
                                <span><i class="fas fa-users"></i> 28 Students</span>
                                <span><i class="fas fa-clock"></i> Mon/Wed/Fri 1:00 PM</span>
                            </div>
                            <div class="course-info">
                                <span><i class="fas fa-map-marker-alt"></i> CS-Lab A</span>
                                <span><i class="fas fa-book"></i> 3 Units</span>
                            </div>
                            <div class="course-stats">
                                <div class="stat">
                                    <span class="stat-value">95%</span>
                                    <span class="stat-label">Attendance</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-value">91%</span>
                                    <span class="stat-label">Avg Grade</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-value">1</span>
                                    <span class="stat-label">Pending</span>
                                </div>
                            </div>
                            <div class="course-actions">
                                <button class="btn-secondary">View Students</button>
                                <button class="btn-primary">Manage</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Schedule Section -->
            <div id="schedule-section" class="content-section">
                <h2 class="section-title">Teaching Schedule</h2>
                
                <div class="schedule-container">
                    <div class="schedule-day">
                        <h4 class="day-header">Monday</h4>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">10:00 AM - 11:30 AM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Software Engineering (IT 373)</div>
                                <div class="schedule-location">CS-302 | 35 Students</div>
                            </div>
                            <div class="schedule-action">
                                <button class="btn-primary btn-sm">Take Attendance</button>
                            </div>
                        </div>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">1:00 PM - 2:30 PM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Web Development (IT 401)</div>
                                <div class="schedule-location">CS-Lab A | 28 Students</div>
                            </div>
                            <div class="schedule-action">
                                <button class="btn-primary btn-sm">Take Attendance</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="schedule-day">
                        <h4 class="day-header">Tuesday</h4>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">2:00 PM - 3:30 PM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Data Structures (CS 301)</div>
                                <div class="schedule-location">CS-105 | 42 Students</div>
                            </div>
                            <div class="schedule-action">
                                <button class="btn-primary btn-sm">Take Attendance</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="schedule-day">
                        <h4 class="day-header">Wednesday</h4>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">10:00 AM - 11:30 AM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Software Engineering (IT 373)</div>
                                <div class="schedule-location">CS-302 | 35 Students</div>
                            </div>
                            <div class="schedule-action">
                                <button class="btn-primary btn-sm">Take Attendance</button>
                            </div>
                        </div>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">1:00 PM - 2:30 PM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Web Development (IT 401)</div>
                                <div class="schedule-location">CS-Lab A | 28 Students</div>
                            </div>
                            <div class="schedule-action">
                                <button class="btn-primary btn-sm">Take Attendance</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="schedule-day">
                        <h4 class="day-header">Thursday</h4>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">2:00 PM - 3:30 PM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Data Structures (CS 301)</div>
                                <div class="schedule-location">CS-105 | 42 Students</div>
                            </div>
                            <div class="schedule-action">
                                <button class="btn-primary btn-sm">Take Attendance</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Students Section -->
            <div id="students-section" class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Student Management</h2>
                    <div class="student-filters">
                        <select class="form-control">
                            <option>All Courses</option>
                            <option>IT 373 - Software Engineering</option>
                            <option>CS 301 - Data Structures</option>
                            <option>IT 401 - Web Development</option>
                        </select>
                        <input type="text" class="form-control" placeholder="Search students...">
                    </div>
                </div>
                
                <div class="students-container">
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Attendance</th>
                                <th>Current Grade</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2023001</td>
                                <td>John Smith</td>
                                <td>IT 373</td>
                                <td>95%</td>
                                <td>88%</td>
                                <td><span class="status-badge active">Active</span></td>
                                <td>
                                    <button class="btn-icon" title="View Profile"><i class="fas fa-eye"></i></button>
                                    <button class="btn-icon" title="Send Message"><i class="fas fa-envelope"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>2023002</td>
                                <td>Sarah Johnson</td>
                                <td>CS 301</td>
                                <td>92%</td>
                                <td>91%</td>
                                <td><span class="status-badge active">Active</span></td>
                                <td>
                                    <button class="btn-icon" title="View Profile"><i class="fas fa-eye"></i></button>
                                    <button class="btn-icon" title="Send Message"><i class="fas fa-envelope"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>2023003</td>
                                <td>Michael Brown</td>
                                <td>IT 401</td>
                                <td>85%</td>
                                <td>76%</td>
                                <td><span class="status-badge warning">At Risk</span></td>
                                <td>
                                    <button class="btn-icon" title="View Profile"><i class="fas fa-eye"></i></button>
                                    <button class="btn-icon" title="Send Message"><i class="fas fa-envelope"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Assignments Section -->
            <div id="assignments-section" class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Assignment Management</h2>
                    <button class="btn-primary" id="createAssignmentBtn">
                        <i class="fas fa-plus"></i>
                        Create Assignment
                    </button>
                </div>
                
                <div class="assignments-container">
                    <div class="assignment-card">
                        <div class="assignment-header">
                            <h4>Software Engineering Project Proposal</h4>
                            <span class="assignment-course">IT 373</span>
                        </div>
                        <div class="assignment-body">
                            <div class="assignment-info">
                                <span><i class="fas fa-calendar"></i> Due: Sep 15, 2023</span>
                                <span><i class="fas fa-users"></i> 35 Students</span>
                                <span><i class="fas fa-check-circle"></i> 28 Submitted</span>
                            </div>
                            <div class="assignment-progress">
                                <div class="progress-label">
                                    <span>Submission Progress</span>
                                    <span>80%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress" style="width: 80%;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="assignment-actions">
                            <button class="btn-secondary">View Submissions</button>
                            <button class="btn-primary">Grade</button>
                        </div>
                    </div>
                    
                    <div class="assignment-card">
                        <div class="assignment-header">
                            <h4>Data Structures Algorithm Analysis</h4>
                            <span class="assignment-course">CS 301</span>
                        </div>
                        <div class="assignment-body">
                            <div class="assignment-info">
                                <span><i class="fas fa-calendar"></i> Due: Sep 18, 2023</span>
                                <span><i class="fas fa-users"></i> 42 Students</span>
                                <span><i class="fas fa-check-circle"></i> 35 Submitted</span>
                            </div>
                            <div class="assignment-progress">
                                <div class="progress-label">
                                    <span>Submission Progress</span>
                                    <span>83%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress" style="width: 83%;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="assignment-actions">
                            <button class="btn-secondary">View Submissions</button>
                            <button class="btn-primary">Grade</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Grades Section -->
            <div id="grades-section" class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Grade Management</h2>
                    <div class="grade-actions">
                        <select class="form-control">
                            <option>IT 373 - Software Engineering</option>
                            <option>CS 301 - Data Structures</option>
                            <option>IT 401 - Web Development</option>
                        </select>
                        <button class="btn-primary">
                            <i class="fas fa-download"></i>
                            Export Grades
                        </button>
                    </div>
                </div>
                
                <div class="grades-container">
                    <table class="grades-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Assignments (30%)</th>
                                <th>Midterm (30%)</th>
                                <th>Final (40%)</th>
                                <th>Overall</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2023001</td>
                                <td>John Smith</td>
                                <td><input type="text" class="grade-input" value="85"></td>
                                <td><input type="text" class="grade-input" value="88"></td>
                                <td><input type="text" class="grade-input" value="90"></td>
                                <td>88%</td>
                                <td>
                                    <button class="btn-primary btn-sm">Save</button>
                                </td>
                            </tr>
                            <tr>
                                <td>2023002</td>
                                <td>Sarah Johnson</td>
                                <td><input type="text" class="grade-input" value="92"></td>
                                <td><input type="text" class="grade-input" value="95"></td>
                                <td><input type="text" class="grade-input" value="89"></td>
                                <td>91%</td>
                                <td>
                                    <button class="btn-primary btn-sm">Save</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Profile Section -->
            <div id="profile-section" class="content-section">
                <div class="profile-header">
                    <h2 class="section-title">My Profile</h2>
                    <p class="profile-subtitle">Manage your professional information</p>
                </div>
                
                <div class="profile-container">
                    <!-- Profile Summary Card -->
                    <div class="profile-summary-card">
                        <div class="profile-avatar-section">
                            <div class="avatar-container">
                                <img src="{{ !empty($profile_picture) && $profile_picture !== 'default.png' ? asset('profile/' . $profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode(($firstname ?? '') . ' ' . ($lastname ?? '')) . '&background=4361ee&color=fff&size=150' }}" 
                                    alt="Instructor Avatar" 
                                    class="profile-avatar">
                                <div class="avatar-overlay">
                                    <i class="fas fa-camera"></i>
                                </div>
                            </div>
                            
                            <button class="btn-secondary btn-avatar">
                                <i class="fas fa-camera"></i>
                                Change Photo
                            </button>
                        </div>
                        
                        <div class="profile-info-summary">
                            <h3 class="profile-name">Donquixote Doflamingo</h3>
                            <p class="profile-id">007</p>
                            <div class="profile-badge">
                                <i class="fas fa-chalkboard-teacher"></i>
                                Faculty Member
                            </div>
                            
                            <div class="profile-stats">
                                <div class="profile-stat">
                                    <span class="stat-number">4</span>
                                    <span class="stat-label">Courses</span>
                                </div>
                                <div class="profile-stat">
                                    <span class="stat-number">127</span>
                                    <span class="stat-label">Students</span>
                                </div>
                                <div class="profile-stat">
                                    <span class="stat-number">5</span>
                                    <span class="stat-label">Years</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Details Card -->
                    <div class="profile-details-card">
                        <div class="card-header">
                            <h4>Professional Information</h4>
                            <button class="btn-edit" id="edit-profile-btn">
                                <i class="fas fa-edit"></i>
                                Edit Profile
                            </button>
                        </div>
                        
                        <form class="profile-form" id="profile-form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="firstName" class="form-label">
                                        <i class="fas fa-user"></i>
                                        First Name
                                    </label>
                                    <input type="text" class="form-control" id="firstName" value="Doflamingo" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="lastName" class="form-label">
                                        <i class="fas fa-user"></i>
                                        Last Name
                                    </label>
                                    <input type="text" class="form-control" id="lastName" value="{{ $lastname }}" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope"></i>
                                        Email Address
                                    </label>
                                    <input type="email" class="form-control" id="email" value="instructor@evsu.edu.ph" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone"></i>
                                        Phone Number
                                    </label>
                                    <input type="tel" class="form-control" id="phone" value="+63 123 456 7890" readonly>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label for="department" class="form-label">
                                        <i class="fas fa-university"></i>
                                        Department
                                    </label>
                                    <input type="text" class="form-control" id="department" value="Computer Studies Department" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="position" class="form-label">
                                        <i class="fas fa-briefcase"></i>
                                        Position
                                    </label>
                                    <input type="text" class="form-control" id="position" value="Assistant Professor" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="office" class="form-label">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Office
                                    </label>
                                    <input type="text" class="form-control" id="office" value="CS Building Room 201" readonly>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label for="bio" class="form-label">
                                        <i class="fas fa-file-alt"></i>
                                        Bio
                                    </label>
                                    <textarea class="form-control" id="bio" rows="3" readonly>Specialized in Software Engineering and Web Development with 5 years of teaching experience.</textarea>
                                </div>
                            </div>
                            
                            <div class="form-actions" id="form-actions" style="display: none;">
                                <button type="button" class="btn-cancel" id="cancel-edit">Cancel</button>
                                <button type="submit" class="btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Settings Section -->
            <div id="settings-section" class="content-section">
                <h2 class="section-title">Settings</h2>
                
                <div class="settings-card">
                    <h4>Appearance</h4>
                    
                    <div class="settings-option">
                        <div class="option-info">
                            <h5>Dark Mode</h5>
                            <p>Switch between light and dark themes</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="dark-mode-toggle">
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="settings-option">
                        <div class="option-info">
                            <h5>Theme Color</h5>
                            <p>Choose your preferred accent color</p>
                        </div>
                        <div class="theme-colors">
                            <div class="color-option" data-color="#4361ee" style="background-color: #4361ee;"></div>
                            <div class="color-option" data-color="#2c5530" style="background-color: #2c5530;"></div>
                            <div class="color-option" data-color="#8b5cf6" style="background-color: #8b5cf6;"></div>
                            <div class="color-option" data-color="#ef4444" style="background-color: #ef4444;"></div>
                            <div class="color-option" data-color="#f59e0b" style="background-color: #f59e0b;"></div>
                            <div class="color-option" data-color="#800000" style="background-color: #800000;"></div>
                        </div>
                    </div>
                </div>
                
                <div class="settings-card">
                    <h4>Account Settings</h4>
                    
                    <div class="settings-option">
                        <div class="option-info">
                            <h5>Change Password</h5>
                            <p>Update your password regularly to keep your account secure</p>
                        </div>
                        <button class="btn-primary">Change</button>
                    </div>
                    
                    <div class="settings-option">
                        <div class="option-info">
                            <h5>Login Activity</h5>
                            <p>View your recent login history and devices</p>
                        </div>
                        <button class="btn-primary">View</button>
                    </div>
                </div>
                
                <div class="settings-card">
                    <h4>Notification Preferences</h4>
                    
                    <div class="settings-option">
                        <div class="option-info">
                            <h5>Assignment Submissions</h5>
                            <p>Get notified when students submit assignments</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="settings-option">
                        <div class="option-info">
                            <h5>Student Questions</h5>
                            <p>Receive notifications for student inquiries</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="settings-option">
                        <div class="option-info">
                            <h5>Grade Alerts</h5>
                            <p>Get alerts for pending grade submissions</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
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
    <script src="{{asset('js/function/instructor/dashboard.js')}}"></script>
</body>
</html>