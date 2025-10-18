<?php
$firstname = $user->user_information->firstname;
$lastname = $user->user_information->lastname;
$student_id = $user->user_information->student->id_no;
$profile_picture = $user->profile;
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
    <title>Student Dashboard | EnrollSys</title>
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
    <link rel="stylesheet" href="{{ asset('css/student/dashboard.css') }}">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>Enroll<span>Sys</span></h3>
            </div>
            
            <div class="user-profile">
                @if(!empty($profile_picture) && $profile_picture !== 'default.png')
                    <img src="{{ asset('profile/' . $profile_picture) }}" alt="User Avatar" class="user-avatar">
                @else
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(($firstname ?? '') . ' ' . ($lastname ?? '')) }}&background=4361ee&color=fff" alt="User Avatar" class="user-avatar">
                @endif
                
                <h4 class="user-name">{{ $firstname ?? 'User' }}</h4>
                <p class="user-id">{{ $student_id }}</p>
            </div>
            
            <div class="sidebar-menu">
                <a class="menu-item active" data-section="dashboard">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a class="menu-item" data-section="courses">
                    <i class="fas fa-book"></i>
                    <span>My Courses</span>
                </a>
                <a class="menu-item" data-section="schedule">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Schedule</span>
                </a>
                <a class="menu-item" data-section="grades">
                    <i class="fas fa-chart-bar"></i>
                    <span>Grades</span>
                </a>
                <a class="menu-item" data-section="assignments">
                    <i class="fas fa-tasks"></i>
                    <span>Assignments</span>
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
                <h1 class="page-title">Student Dashboard</h1>

                <!-- Search Bar -->
                <div class="search-container">
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Search courses, assignments, grades...">
                        <button class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <div class="header-actions">
                    <div class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count">3</span>
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
                        <h3 class="stat-value">5</h3>
                        <p class="stat-label">Enrolled Courses</p>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon grades">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="stat-value">89.5%</h3>
                        <p class="stat-label">Average Grade</p>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon deadlines">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <h3 class="stat-value">3</h3>
                        <p class="stat-label">Upcoming Enrollment</p>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon attendance">
                            <i class="fa-solid fa-folder-open"></i>
                        </div>
                        <h3 class="stat-value">96.2%</h3>
                        <p class="stat-label">Documents</p>
                    </div>
                </div>
                
                <!-- My Courses -->
                <h2 class="section-title">My Courses</h2>
                <div class="courses-grid">
                    <div class="course-card">
                        <div class="course-header">
                            <h3 class="course-code">IT 373</h3>
                            <p class="course-name">Software Engineering</p>
                        </div>
                        <div class="course-body">
                            <div class="course-info">
                                <span>Instructor: Dr. Smith</span>
                                <span>Units: 3</span>
                            </div>
                            <div class="course-info">
                                <span>Schedule: Mon/Wed 10:00 AM</span>
                                <span>Room: CS-302</span>
                            </div>
                            <div class="progress-container">
                                <div class="progress-label">
                                    <span>Course Progress</span>
                                    <span>65%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress" style="width: 65%;"></div>
                                </div>
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
                                <span>Instructor: Prof. Johnson</span>
                                <span>Units: 4</span>
                            </div>
                            <div class="course-info">
                                <span>Schedule: Tue/Thu 2:00 PM</span>
                                <span>Room: CS-105</span>
                            </div>
                            <div class="progress-container">
                                <div class="progress-label">
                                    <span>Course Progress</span>
                                    <span>78%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress" style="width: 78%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="course-card">
                        <div class="course-header">
                            <h3 class="course-code">MATH 202</h3>
                            <p class="course-name">Calculus II</p>
                        </div>
                        <div class="course-body">
                            <div class="course-info">
                                <span>Instructor: Dr. Lee</span>
                                <span>Units: 3</span>
                            </div>
                            <div class="course-info">
                                <span>Schedule: Mon/Wed/Fri 1:00 PM</span>
                                <span>Room: MATH-204</span>
                            </div>
                            <div class="progress-container">
                                <div class="progress-label">
                                    <span>Course Progress</span>
                                    <span>42%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress" style="width: 42%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Today's Schedule -->
                <h2 class="section-title">Today's Schedule</h2>
                <div class="schedule-container">
                    <div class="schedule-day">
                        <h4 class="day-header">Monday, August 30</h4>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">10:00 AM - 11:30 AM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Software Engineering (IT 373)</div>
                                <div class="schedule-location">CS-302 | Dr. Smith</div>
                            </div>
                        </div>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">1:00 PM - 2:30 PM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Calculus II (MATH 202)</div>
                                <div class="schedule-location">MATH-204 | Dr. Lee</div>
                            </div>
                        </div>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">3:00 PM - 4:30 PM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Programming Lab</div>
                                <div class="schedule-location">CS-Lab A | TA Rodriguez</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- My Courses Section -->
            <div id="courses-section" class="content-section">
                <h2 class="section-title">My Courses</h2>
                
                <div class="courses-grid">
                    <div class="course-card">
                        <div class="course-header">
                            <h3 class="course-code">IT 373</h3>
                            <p class="course-name">Software Engineering</p>
                        </div>
                        <div class="course-body">
                            <div class="course-info">
                                <span>Instructor: Dr. Smith</span>
                                <span>Units: 3</span>
                            </div>
                            <div class="course-info">
                                <span>Schedule: Mon/Wed 10:00 AM</span>
                                <span>Room: CS-302</span>
                            </div>
                            <div class="progress-container">
                                <div class="progress-label">
                                    <span>Course Progress</span>
                                    <span>65%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress" style="width: 65%;"></div>
                                </div>
                            </div>
                            <button class="btn-primary mt-3 w-100">View Course</button>
                        </div>
                    </div>
                    
                    <div class="course-card">
                        <div class="course-header">
                            <h3 class="course-code">CS 301</h3>
                            <p class="course-name">Data Structures</p>
                        </div>
                        <div class="course-body">
                            <div class="course-info">
                                <span>Instructor: Prof. Johnson</span>
                                <span>Units: 4</span>
                            </div>
                            <div class="course-info">
                                <span>Schedule: Tue/Thu 2:00 PM</span>
                                <span>Room: CS-105</span>
                            </div>
                            <div class="progress-container">
                                <div class="progress-label">
                                    <span>Course Progress</span>
                                    <span>78%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress" style="width: 78%;"></div>
                                </div>
                            </div>
                            <button class="btn-primary mt-3 w-100">View Course</button>
                        </div>
                    </div>
                    
                    <div class="course-card">
                        <div class="course-header">
                            <h3 class="course-code">MATH 202</h3>
                            <p class="course-name">Calculus II</p>
                        </div>
                        <div class="course-body">
                            <div class="course-info">
                                <span>Instructor: Dr. Lee</span>
                                <span>Units: 3</span>
                            </div>
                            <div class="course-info">
                                <span>Schedule: Mon/Wed/Fri 1:00 PM</span>
                                <span>Room: MATH-204</span>
                            </div>
                            <div class="progress-container">
                                <div class="progress-label">
                                    <span>Course Progress</span>
                                    <span>42%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress" style="width: 42%;"></div>
                                </div>
                            </div>
                            <button class="btn-primary mt-3 w-100">View Course</button>
                        </div>
                    </div>
                    
                    <div class="course-card">
                        <div class="course-header">
                            <h3 class="course-code">ENG 101</h3>
                            <p class="course-name">Composition I</p>
                        </div>
                        <div class="course-body">
                            <div class="course-info">
                                <span>Instructor: Prof. Davis</span>
                                <span>Units: 3</span>
                            </div>
                            <div class="course-info">
                                <span>Schedule: Tue/Thu 9:00 AM</span>
                                <span>Room: LIB-205</span>
                            </div>
                            <div class="progress-container">
                                <div class="progress-label">
                                    <span>Course Progress</span>
                                    <span>85%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress" style="width: 85%;"></div>
                                </div>
                            </div>
                            <button class="btn-primary mt-3 w-100">View Course</button>
                        </div>
                    </div>
                    
                    <div class="course-card">
                        <div class="course-header">
                            <h3 class="course-code">HIST 110</h3>
                            <p class="course-name">World History</p>
                        </div>
                        <div class="course-body">
                            <div class="course-info">
                                <span>Instructor: Dr. Garcia</span>
                                <span>Units: 3</span>
                            </div>
                            <div class="course-info">
                                <span>Schedule: Mon/Wed 3:00 PM</span>
                                <span>Room: HSS-102</span>
                            </div>
                            <div class="progress-container">
                                <div class="progress-label">
                                    <span>Course Progress</span>
                                    <span>55%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress" style="width: 55%;"></div>
                                </div>
                            </div>
                            <button class="btn-primary mt-3 w-100">View Course</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Schedule Section -->
            <div id="schedule-section" class="content-section">
                <h2 class="section-title">Weekly Schedule</h2>
                
                <div class="schedule-container">
                    <div class="schedule-day">
                        <h4 class="day-header">Monday</h4>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">10:00 AM - 11:30 AM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Software Engineering (IT 373)</div>
                                <div class="schedule-location">CS-302 | Dr. Smith</div>
                            </div>
                        </div>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">1:00 PM - 2:30 PM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Calculus II (MATH 202)</div>
                                <div class="schedule-location">MATH-204 | Dr. Lee</div>
                            </div>
                        </div>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">3:00 PM - 4:30 PM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Programming Lab</div>
                                <div class="schedule-location">CS-Lab A | TA Rodriguez</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="schedule-day">
                        <h4 class="day-header">Tuesday</h4>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">9:00 AM - 10:30 AM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Composition I (ENG 101)</div>
                                <div class="schedule-location">LIB-205 | Prof. Davis</div>
                            </div>
                        </div>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">2:00 PM - 3:30 PM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Data Structures (CS 301)</div>
                                <div class="schedule-location">CS-105 | Prof. Johnson</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="schedule-day">
                        <h4 class="day-header">Wednesday</h4>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">10:00 AM - 11:30 AM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Software Engineering (IT 373)</div>
                                <div class="schedule-location">CS-302 | Dr. Smith</div>
                            </div>
                        </div>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">1:00 PM - 2:30 PM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Calculus II (MATH 202)</div>
                                <div class="schedule-location">MATH-204 | Dr. Lee</div>
                            </div>
                        </div>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">3:00 PM - 4:30 PM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">World History (HIST 110)</div>
                                <div class="schedule-location">HSS-102 | Dr. Garcia</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="schedule-day">
                        <h4 class="day-header">Thursday</h4>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">9:00 AM - 10:30 AM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Composition I (ENG 101)</div>
                                <div class="schedule-location">LIB-205 | Prof. Davis</div>
                            </div>
                        </div>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">2:00 PM - 3:30 PM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Data Structures (CS 301)</div>
                                <div class="schedule-location">CS-105 | Prof. Johnson</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="schedule-day">
                        <h4 class="day-header">Friday</h4>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">1:00 PM - 2:30 PM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Calculus II (MATH 202)</div>
                                <div class="schedule-location">MATH-204 | Dr. Lee</div>
                            </div>
                        </div>
                        
                        <div class="schedule-item">
                            <div class="schedule-time">3:00 PM - 4:30 PM</div>
                            <div class="schedule-details">
                                <div class="schedule-course">Study Group</div>
                                <div class="schedule-location">Library Study Room 3</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Grades Section -->
            <div id="grades-section" class="content-section">
                <h2 class="section-title">My Grades</h2>
                
                <div class="schedule-container">
                    <h4 class="mb-4">Current Semester: Fall 2023</h4>
                    
                    <table class="grades-table">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Instructor</th>
                                <th>Assignments</th>
                                <th>Midterm</th>
                                <th>Final</th>
                                <th>Overall</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>IT 373 - Software Engineering</td>
                                <td>Dr. Smith</td>
                                <td>88%</td>
                                <td>92%</td>
                                <td>-</td>
                                <td>90%</td>
                            </tr>
                            <tr>
                                <td>CS 301 - Data Structures</td>
                                <td>Prof. Johnson</td>
                                <td>95%</td>
                                <td>87%</td>
                                <td>-</td>
                                <td>91%</td>
                            </tr>
                            <tr>
                                <td>MATH 202 - Calculus II</td>
                                <td>Dr. Lee</td>
                                <td>78%</td>
                                <td>85%</td>
                                <td>-</td>
                                <td>82%</td>
                            </tr>
                            <tr>
                                <td>ENG 101 - Composition I</td>
                                <td>Prof. Davis</td>
                                <td>92%</td>
                                <td>88%</td>
                                <td>-</td>
                                <td>90%</td>
                            </tr>
                            <tr>
                                <td>HIST 110 - World History</td>
                                <td>Dr. Garcia</td>
                                <td>85%</td>
                                <td>90%</td>
                                <td>-</td>
                                <td>88%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="stats-grid mt-4">
                    <div class="stat-card">
                        <div class="stat-icon courses">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h3 class="stat-value">3.75</h3>
                        <p class="stat-label">Current GPA</p>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon grades">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="stat-value">89.5%</h3>
                        <p class="stat-label">Average Grade</p>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon attendance">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h3 class="stat-value">2</h3>
                        <p class="stat-label">Academic Honors</p>
                    </div>
                </div>
            </div>
            
            <!-- Assignments Section -->
            <div id="assignments-section" class="content-section">
                <h2 class="section-title">My Assignments</h2>
                
                <div class="schedule-container">
                    <h4 class="mb-4">Upcoming Assignments</h4>
                    
                    <div class="assignment-item">
                        <div class="assignment-info">
                            <h4>Software Engineering Project Proposal</h4>
                            <p>IT 373 - Due: Sep 15, 2023</p>
                        </div>
                        <div class="assignment-status status-pending">Pending</div>
                    </div>
                    
                    <div class="assignment-item">
                        <div class="assignment-info">
                            <h4>Data Structures Algorithm Analysis</h4>
                            <p>CS 301 - Due: Sep 18, 2023</p>
                        </div>
                        <div class="assignment-status status-pending">Pending</div>
                    </div>
                    
                    <div class="assignment-item">
                        <div class="assignment-info">
                            <h4>Calculus II Problem Set #5</h4>
                            <p>MATH 202 - Due: Sep 20, 2023</p>
                        </div>
                        <div class="assignment-status status-pending">Pending</div>
                    </div>
                </div>
                
                <div class="schedule-container mt-4">
                    <h4 class="mb-4">Completed Assignments</h4>
                    
                    <div class="assignment-item">
                        <div class="assignment-info">
                            <h4>Composition I Essay Draft</h4>
                            <p>ENG 101 - Submitted: Sep 5, 2023</p>
                        </div>
                        <div class="assignment-status status-done">Graded: 92%</div>
                    </div>
                    
                    <div class="assignment-item">
                        <div class="assignment-info">
                            <h4>World History Research Outline</h4>
                            <p>HIST 110 - Submitted: Sep 3, 2023</p>
                        </div>
                        <div class="assignment-status status-done">Graded: 88%</div>
                    </div>
                    
                    <div class="assignment-item">
                        <div class="assignment-info">
                            <h4>Data Structures Programming Task</h4>
                            <p>CS 301 - Submitted: Aug 30, 2023</p>
                        </div>
                        <div class="assignment-status status-done">Graded: 95%</div>
                    </div>
                </div>
            </div>
            
            <!-- Profile Section -->
            <div id="profile-section" class="content-section">
                <div class="profile-header">
                    <h2 class="section-title">My Profile</h2>
                    <p class="profile-subtitle">Manage your personal information and account settings</p>
                </div>
                
                <div class="profile-container">
                    <!-- Profile Summary Card -->
                    <div class="profile-summary-card">
                        <div class="profile-avatar-section">
                            <div class="avatar-container">
                                <img src="https://ui-avatars.com/api/?name=Carl+James+Duallo&background=4361ee&color=fff&size=150" alt="User Avatar" class="profile-avatar">
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
                            <h3 class="profile-name">Laxus Dreyar</h3>
                            <p class="profile-id">Student ID: 2023-12345</p>
                            <div class="profile-badge">
                                <i class="fas fa-graduation-cap"></i>
                                Active Student
                            </div>
                            
                            <div class="profile-stats">
                                <div class="profile-stat">
                                    <span class="stat-number">5</span>
                                    <span class="stat-label">Courses</span>
                                </div>
                                <div class="profile-stat">
                                    <span class="stat-number">89.5%</span>
                                    <span class="stat-label">Avg Grade</span>
                                </div>
                                <div class="profile-stat">
                                    <span class="stat-number">96%</span>
                                    <span class="stat-label">Attendance</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Details Card -->
                    <div class="profile-details-card">
                        <div class="card-header">
                            <h4>Personal Information</h4>
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
                                    <input type="text" class="form-control" id="firstName" value="Laxus" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="lastName" class="form-label">
                                        <i class="fas fa-user"></i>
                                        Last Name
                                    </label>
                                    <input type="text" class="form-control" id="lastName" value="Dreyar" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope"></i>
                                        Email Address
                                    </label>
                                    <input type="email" class="form-control" id="email" value="laxus@evsu.edu.ph" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone"></i>
                                        Phone Number
                                    </label>
                                    <input type="tel" class="form-control" id="phone" value="+63 123 456 7890" readonly>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label for="address" class="form-label">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Address
                                    </label>
                                    <input type="text" class="form-control" id="address" value="Ormoc City, Leyte" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="program" class="form-label">
                                        <i class="fas fa-book"></i>
                                        Program
                                    </label>
                                    <input type="text" class="form-control" id="program" value="BS in Information Technology" disabled>
                                </div>
                                
                                <div class="form-group">
                                    <label for="yearLevel" class="form-label">
                                        <i class="fas fa-calendar-alt"></i>
                                        Year Level
                                    </label>
                                    <input type="text" class="form-control" id="yearLevel" value="3rd Year" disabled>
                                </div>
                                
                                <div class="form-group">
                                    <label for="semester" class="form-label">
                                        <i class="fas fa-school"></i>
                                        Semester
                                    </label>
                                    <input type="text" class="form-control" id="semester" value="1st Semester 2023-2024" disabled>
                                </div>
                            </div>
                            
                            <div class="form-actions" id="form-actions" style="display: none;">
                                <button type="button" class="btn-cancel" id="cancel-edit">Cancel</button>
                                <button type="submit" class="btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>

                    <!-- Additional Information Card -->
                    <div class="profile-info-card">
                        <div class="card-header">
                            <h4>Academic Information</h4>
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-university"></i>
                                </div>
                                <div class="info-content">
                                    <span class="info-label">Department</span>
                                    <span class="info-value">College of Information Technology</span>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-id-card"></i>
                                </div>
                                <div class="info-content">
                                    <span class="info-label">Student Type</span>
                                    <span class="info-value">Regular</span>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="info-content">
                                    <span class="info-label">Current GPA</span>
                                    <span class="info-value">3.75</span>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="info-content">
                                    <span class="info-label">Enrollment Date</span>
                                    <span class="info-value">August 15, 2023</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Settings Section -->
            <div id="settings-section" class="content-section">
                <h2 class="section-title">Settings</h2>
                
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
                            <h5>Two-Factor Authentication</h5>
                            <p>Add an extra layer of security to your account</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
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
                            <h5>Email Notifications</h5>
                            <p>Receive important updates via email</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="settings-option">
                        <div class="option-info">
                            <h5>Assignment Reminders</h5>
                            <p>Get notified about upcoming assignments</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="settings-option">
                        <div class="option-info">
                            <h5>Grade Updates</h5>
                            <p>Receive notifications when new grades are posted</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="settings-option">
                        <div class="option-info">
                            <h5>Course Announcements</h5>
                            <p>Get notified about new course announcements</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
                
                <div class="settings-card">
                    <h4>Appearance</h4>
                    
                    <div class="settings-option">
                        <div class="option-info">
                            <h5>Dark Mode</h5>
                            <p>Switch between light and dark themes</p>
                        </div>
                        <label class="toggle-switch" id="dark-mode-toggle">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="settings-option">
                        <div class="option-info">
                            <h5>Font Size</h5>
                            <p>Adjust the font size to your preference</p>
                        </div>
                        <select class="form-control" style="width: auto;">
                            <option>Small</option>
                            <option selected>Medium</option>
                            <option>Large</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar on mobile
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            const sidebar = document.querySelector('.sidebar');
            const searchInput = document.querySelector('.search-input');
            const searchBtn = document.querySelector('.search-btn');
            const searchBar = document.querySelector('.search-bar');

            // Search functionality
            function performSearch() {
                const searchTerm = searchInput.value.trim();
                
                if (searchTerm) {
                    // Add loading state
                    searchBar.classList.add('loading');
                    
                    // Simulate search (replace with actual search logic)
                    setTimeout(() => {
                        searchBar.classList.remove('loading');
                        alert(`Searching for: ${searchTerm}`);
                        // Here you would typically filter content or make an API call
                    }, 1000);
                }
            }

            // Search on button click
            searchBtn.addEventListener('click', performSearch);

            // Search on Enter key
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });

            // Clear search on escape
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    searchInput.value = '';
                    searchInput.blur();
                }
            });
            
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth < 992) {
                    const isClickInsideSidebar = sidebar.contains(event.target);
                    const isClickInsideToggle = sidebarToggle.contains(event.target);
                    
                    if (!isClickInsideSidebar && !isClickInsideToggle && sidebar.classList.contains('active')) {
                        sidebar.classList.remove('active');
                    }
                }
            });
            
            // Navigation functionality
            const menuItems = document.querySelectorAll('.menu-item');
            const contentSections = document.querySelectorAll('.content-section');
            const pageTitle = document.querySelector('.page-title');
            
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    if (this.id === 'logout-btn') {
                        // Logout functionality
                        if (confirm('Are you sure you want to logout?')) {
                            alert('Logging out...');
                            // In a real app, this would redirect to logout URL
                        }
                        return;
                    }
                    
                    // Remove active class from all menu items
                    menuItems.forEach(i => i.classList.remove('active'));
                    
                    // Add active class to clicked menu item
                    this.classList.add('active');
                    
                    // Hide all content sections
                    contentSections.forEach(section => section.classList.remove('active'));
                    
                    // Show the selected content section
                    const sectionId = this.getAttribute('data-section') + '-section';
                    document.getElementById(sectionId).classList.add('active');
                    
                    // Update page title
                    const sectionName = this.querySelector('span').textContent;
                    pageTitle.textContent = sectionName;
                });
            });
            
            // Dark mode toggle
            const darkModeToggle = document.getElementById('dark-mode-toggle');
            
            // Check for saved theme preference or use preferred color scheme
            const savedTheme = localStorage.getItem('theme') || 
                              (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            
            // Apply the saved theme
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-mode');
                darkModeToggle.querySelector('input').checked = true;
            }
            
            darkModeToggle.addEventListener('change', function() {
                if (this.querySelector('input').checked) {
                    document.body.classList.add('dark-mode');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.body.classList.remove('dark-mode');
                    localStorage.setItem('theme', 'light');
                }
            });
            
            // Animate cards on scroll
            const animateOnScroll = function() {
                const cards = document.querySelectorAll('.stat-card, .course-card');
                
                cards.forEach(card => {
                    const cardPosition = card.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.2;
                    
                    if (cardPosition < screenPosition) {
                        card.classList.add('animate-card');
                    }
                });
            };
            
            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll(); // Run once on page load

            // Profile edit functionality
            const editProfileBtn = document.getElementById('edit-profile-btn');
            const cancelEditBtn = document.getElementById('cancel-edit');
            const profileForm = document.getElementById('profile-form');
            const formActions = document.getElementById('form-actions');
            const editableFields = ['firstName', 'lastName', 'email', 'phone', 'address'];
            
            let originalValues = {};
            
            // Store original values
            editableFields.forEach(field => {
                originalValues[field] = document.getElementById(field).value;
            });
            
            // Edit profile button click
            editProfileBtn.addEventListener('click', function() {
                // Enable editing for all fields
                editableFields.forEach(field => {
                    const input = document.getElementById(field);
                    input.readOnly = false;
                    input.style.background = 'white';
                    input.style.color = '#374151';
                });
                
                // Show form actions
                formActions.style.display = 'flex';
                
                // Change edit button to editing state
                editProfileBtn.innerHTML = '<i class="fas fa-pencil-alt"></i> Editing...';
                editProfileBtn.style.background = '#fbbf24';
                editProfileBtn.style.borderColor = '#fbbf24';
                editProfileBtn.style.color = '#78350f';
            });
            
            // Cancel edit button click
            cancelEditBtn.addEventListener('click', function() {
                // Restore original values
                editableFields.forEach(field => {
                    const input = document.getElementById(field);
                    input.value = originalValues[field];
                    input.readOnly = true;
                    input.style.background = '#f8fafc';
                    input.style.color = '#64748b';
                });
                
                // Hide form actions
                formActions.style.display = 'none';
                
                // Reset edit button
                editProfileBtn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
                editProfileBtn.style.background = 'transparent';
                editProfileBtn.style.borderColor = 'var(--primary-color)';
                editProfileBtn.style.color = 'var(--primary-color)';
            });
            
            // Form submission
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Here you would typically send the data to your server
                // For now, we'll just show a success message
                
                // Make fields read-only again
                editableFields.forEach(field => {
                    const input = document.getElementById(field);
                    input.readOnly = true;
                    input.style.background = '#f8fafc';
                    input.style.color = '#64748b';
                    
                    // Update original values
                    originalValues[field] = input.value;
                });
                
                // Hide form actions
                formActions.style.display = 'none';
                
                // Reset edit button
                editProfileBtn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
                editProfileBtn.style.background = 'transparent';
                editProfileBtn.style.borderColor = 'var(--primary-color)';
                editProfileBtn.style.color = 'var(--primary-color)';
                
                // Show success message
                alert('Profile updated successfully!');
            });
            
        });//End of DOMContentLoaded
    </script>
</body>
</html>