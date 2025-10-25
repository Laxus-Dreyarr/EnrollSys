<?php
$firstname = $user->user_information->firstname;
$lastname = $user->user_information->lastname;
$student_id = $user->user_information->student->id_no;
$is_regular = $user->user_information->student->is_regular;
$profile_picture = $user->profile;

// Check if student ID is 'none' (case-insensitive)
$show_student_form = (strtolower($student_id) === 'none');
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
                <!-- <a class="menu-item" data-section="grades">
                    <i class="fas fa-chart-bar"></i>
                    <span>Grades</span>
                </a> -->
                <a class="menu-item" data-section="assignments">
                    <i class="fa-solid fa-folder-open"></i>
                    <span>Files</span>
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
                    <div class="stat-card" id="d-stat-card-enroll">
                        <div class="stat-icon grades">
                            <i class="fa-solid fa-plus"></i>
                        </div>
                        <h3 class="stat-value">Enroll Now</h3>
                        <p class="stat-label">October 22, 2025</p>
                        <input type="text" id="is-regular" value="{{ $is_regular }}" hidden>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon courses">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3 class="stat-value">5</h3>
                        <p class="stat-label">Enrolled Courses</p>
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
                        <h3 class="stat-value">10</h3>
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
            <!-- <div id="grades-section" class="content-section">
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
                                <td>Lexi Lore</td>
                                <td>88%</td>
                                <td>92%</td>
                                <td>-</td>
                                <td>90%</td>
                            </tr>
                            <tr>
                                <td>CS 301 - Data Structures</td>
                                <td>Emily Willis</td>
                                <td>95%</td>
                                <td>87%</td>
                                <td>-</td>
                                <td>91%</td>
                            </tr>
                            <tr>
                                <td>MATH 202 - Calculus II</td>
                                <td>Azi Acosta</td>
                                <td>78%</td>
                                <td>85%</td>
                                <td>-</td>
                                <td>82%</td>
                            </tr>
                            <tr>
                                <td>ENG 101 - Composition I</td>
                                <td>Angeli Khang</td>
                                <td>92%</td>
                                <td>88%</td>
                                <td>-</td>
                                <td>90%</td>
                            </tr>
                            <tr>
                                <td>HIST 110 - World History</td>
                                <td>Ayanna Misola</td>
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
            </div> -->
            
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
                    <p class="profile-subtitle">Manage your personal information</p>
                </div>
                
                <div class="profile-container">
                    <!-- Profile Summary Card -->
                    <div class="profile-summary-card">
                        <div class="profile-avatar-section">
                            <div class="avatar-container" id="avatar-container">
                                <img src="{{ !empty($profile_picture) && $profile_picture !== 'default.png' ? asset('profile/' . $profile_picture) : 'https://ui-avatars.com/api/?name=' . urlencode(($firstname ?? '') . ' ' . ($lastname ?? '')) . '&background=4361ee&color=fff&size=150' }}" 
                                    alt="User Avatar" 
                                    class="profile-avatar"
                                    id="profile-avatar">
                                <div class="avatar-overlay" id="avatar-overlay">
                                    <i class="fas fa-camera"></i>
                                </div>
                                
                                <!-- Loading Animation -->
                                <div class="upload-loading" id="upload-loading">
                                    <div class="loading-spinner"></div>
                                    <div class="loading-progress">
                                        <div class="progress-bar">
                                            <div class="progress-fill" id="progress-fill"></div>
                                        </div>
                                        <span class="progress-text" id="progress-text">0%</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden File Input -->
                            <input type="file" 
                                id="avatar-input" 
                                accept="image/*" 
                                style="display: none;">
                            
                            <button class="btn-secondary btn-avatar" id="change-photo-btn">
                                <i class="fas fa-camera"></i>
                                Change Photo
                            </button>
                            
                            <div class="upload-requirements">
                                <small>Supported: JPG, PNG, WEBP • Max: 5MB</small>
                            </div>
                        </div>
                        
                        <div class="profile-info-summary">
                            <h3 class="profile-name">{{ $firstname ?? 'User' }} {{ $lastname ?? '' }}</h3>
                            <p class="profile-id">Student ID: {{ $student_id }}</p>
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
                                    <span class="stat-number">10</span>
                                    <span class="stat-label">Total Units</span>
                                </div>
                                <div class="profile-stat">
                                    <span class="stat-number">10/2025</span>
                                    <span class="stat-label">Enrolled</span>
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
                                    <span class="info-label">Rating</span>
                                    <span class="info-value">5</span>
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
                    
                    <!-- <div class="settings-option">
                        <div class="option-info">
                            <h5>Two-Factor Authentication</h5>
                            <p>Add an extra layer of security to your account</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div> -->
                    
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
                            <h5>Enrollment Reminders</h5>
                            <p>Get notified about upcoming enrollment</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    
                    <div class="settings-option">
                        <div class="option-info">
                            <h5>Enrollment Status Updates</h5>
                            <p>Receive notifications when enrollment is approved</p>
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

    <!-- Student Information Modal -->
    <div id="studentInfoModal" class="modal-overlay <?php echo $show_student_form ? 'active' : ''; ?>">
        <div class="modal-container">
            <div class="modal-header">
                <h3>Complete Your Student Information</h3>
            </div>
            
            <form id="studentInfoForm" class="modal-form">
                @csrf
                <div class="form-group">
                    <label for="school_id" class="form-label">
                        <i class="fas fa-id-card"></i>
                        School ID Number
                    </label>
                    <input 
                        type="text" 
                        id="school_id" 
                        name="school_id" 
                        class="form-control" 
                        placeholder="Enter your school ID number"
                        required
                    >
                    <div class="form-error" id="school_id_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="year_level" class="form-label">
                        <i class="fas fa-graduation-cap"></i>
                        Year Level
                    </label>
                    <select id="year_level" name="year_level" class="form-control" required>
                        <option value="">Select Year Level</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                        <option value="5th Year">5th Year</option>
                    </select>
                    <div class="form-error" id="year_level_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="student_type" class="form-label">
                        <i class="fas fa-user-tag"></i>
                        Student Type
                    </label>
                    <select id="student_type" name="student_type" class="form-control" required>
                        <option value="">Select Student Type</option>
                        <option value="Regular">Regular</option>
                        <option value="Irregular">Irregular</option>
                        <option value="Transferee">Transferee</option>
                    </select>
                    <div class="form-error" id="student_type_error"></div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary btn-full">
                        <i class="fas fa-save"></i>
                        Save Information
                    </button>
                </div>
            </form>
            
            <div class="modal-footer">
                <p class="form-note">
                    <i class="fas fa-info-circle"></i>
                    This information is required to access all dashboard features.
                </p>
            </div>
        </div>
    </div>

    <!-- Enrollment Modal -->
    <div id="enrollmentModal" class="modal-overlay">
        <div class="modal-container" style="max-width: 800px;">
            <div class="modal-header">
                <h3>Course Enrollment</h3>
                <button type="button" class="close-modal" id="closeEnrollmentModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-form">
                <div class="enrollment-info">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Year Level:</span>
                            <span class="info-value" id="enrollmentYearLevel">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Semester:</span>
                            <span class="info-value" id="enrollmentSemester">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Student Type:</span>
                            <span class="info-value" id="enrollmentStudentType">-</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Total Units:</span>
                            <span class="info-value" id="totalUnitsCounter">0</span>
                        </div>
                    </div>
                </div>

                <div class="subjects-container">
                    <h4>Available Subjects</h4>
                    <div class="subjects-list" id="subjectsList">
                        <div class="loading-state">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span>Loading subjects...</span>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="cancelEnrollment">Cancel</button>
                    <button type="button" class="btn-primary" id="submitEnrollment" disabled>
                        <i class="fas fa-paper-plane"></i>
                        Submit Enrollment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Irregular Student Past Subjects Modal -->
    <div id="irregularSubjectsModal" class="irregular-modal-overlay">
        <div class="irregular-modal-container">
            <div class="irregular-modal-header">
                <div class="irregular-modal-title-section">
                    <h3 class="irregular-modal-title">Complete Your Academic History</h3>
                    <p class="irregular-modal-subtitle">Select subjects you've successfully completed in previous semesters</p>
                </div>
                <button type="button" class="irregular-close-modal" id="closeIrregularModal" aria-label="Close modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="irregular-modal-content">
                <!-- Student Information Card -->
                <div class="irregular-student-card">
                    <div class="irregular-student-header">
                        <i class="fas fa-user-graduate"></i>
                        <h4>Student Information</h4>
                    </div>
                    <div class="irregular-student-grid">
                        <div class="irregular-student-item">
                            <span class="irregular-student-label">Student Type:</span>
                            <span class="irregular-student-value irregular-badge">Irregular Student</span>
                        </div>
                        <div class="irregular-student-item">
                            <span class="irregular-student-label">Current Year Level:</span>
                            <span class="irregular-student-value" id="currentYearLevel">-</span>
                        </div>
                        <div class="irregular-student-item">
                            <span class="irregular-student-label">Completed Subjects:</span>
                            <span class="irregular-student-value" id="completedCount">0</span>
                        </div>
                        <div class="irregular-student-item">
                            <span class="irregular-student-label">Total Units:</span>
                            <span class="irregular-student-value" id="totalUnits">0 units</span>
                        </div>
                    </div>
                </div>

                <!-- Instructions Card -->
                <div class="irregular-instruction-card">
                    <div class="irregular-instruction-header">
                        <i class="fas fa-info-circle"></i>
                        <h5>Important Instructions</h5>
                    </div>
                    <div class="irregular-instruction-content">
                        <p>This information helps us determine which subjects you're eligible to enroll in. Please select <strong>ALL subjects</strong> you have successfully completed in previous semesters.</p>
                        <ul class="irregular-instruction-list">
                            <li><i class="fas fa-check-circle"></i> Select subjects you've passed</li>
                            <li><i class="fas fa-clock"></i> Include subjects from all previous years</li>
                            <li><i class="fas fa-exclamation-triangle"></i> Do not select current or future subjects</li>
                        </ul>
                    </div>
                </div>

                <div class="irregular-subjects-section">
                    <!-- Search and Filter Section -->
                    <div class="irregular-filter-section">
                        <div class="irregular-search-container">
                            <div class="irregular-search-bar">
                                <i class="fas fa-search irregular-search-icon"></i>
                                <input type="text" class="irregular-search-input" id="pastSubjectsSearch" placeholder="Search subjects by code or name...">
                                <div class="irregular-search-actions">
                                    <button class="irregular-clear-search" id="clearSearch" title="Clear search" aria-label="Clear search">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="irregular-filter-controls">
                            <div class="irregular-filter-group">
                                <label for="yearLevelFilter" class="irregular-filter-label">
                                    <i class="fas fa-filter"></i>
                                    Filter by Year Level:
                                </label>
                                <select id="yearLevelFilter" class="irregular-filter-select">
                                    <option value="all">All Year Levels</option>
                                    <option value="1st Year">1st Year</option>
                                    <option value="2nd Year">2nd Year</option>
                                    <option value="3rd Year">3rd Year</option>
                                    <option value="4th Year">4th Year</option>
                                </select>
                            </div>
                            
                            <div class="irregular-filter-group">
                                <label for="semesterFilter" class="irregular-filter-label">
                                    <i class="fas fa-calendar-alt"></i>
                                    Filter by Semester:
                                </label>
                                <select id="semesterFilter" class="irregular-filter-select">
                                    <option value="all">All Semesters</option>
                                    <option value="1st Sem">1st Semester</option>
                                    <option value="2nd Sem">2nd Semester</option>
                                </select>
                            </div>

                            <div class="irregular-selection-info">
                                <span id="selectedCount">0 subjects selected</span>
                            </div>
                        </div>
                    </div>

                    <!-- Categories and Subjects Section -->
                    <div class="irregular-content-layout">
                        <!-- Categories Sidebar -->
                        <div class="irregular-categories-sidebar">
                            <div class="irregular-categories-header">
                                <h5><i class="fas fa-folder"></i> Categories</h5>
                                <button class="irregular-expand-all" id="expandAllCategories">
                                    <i class="fas fa-expand"></i>
                                    Expand All
                                </button>
                            </div>
                            <div class="irregular-categories-list" id="categoriesList">
                                <!-- Categories will be populated by JavaScript -->
                                <div class="irregular-loading-state">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    <span>Loading categories...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Subjects Main Content -->
                        <div class="irregular-subjects-main">
                            <div class="irregular-subjects-header">
                                <h4 id="currentCategoryTitle">All Subjects</h4>
                                <div class="irregular-subject-actions">
                                    <button class="irregular-select-all" id="selectAllVisible">
                                        <i class="fas fa-check-square"></i>
                                        Select All Visible
                                    </button>
                                    <button class="irregular-deselect-all" id="deselectAllVisible">
                                        <i class="fas fa-square"></i>
                                        Deselect All
                                    </button>
                                </div>
                            </div>
                            
                            <div class="irregular-subjects-container">
                                <div class="irregular-subjects-list" id="pastSubjectsList">
                                    <div class="irregular-empty-state">
                                        <i class="fas fa-book-open"></i>
                                        <h4>No Subjects Available</h4>
                                        <p>Select a category to view subjects, or use the search above.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="irregular-modal-actions">
                    <button type="button" class="irregular-cancel-btn" id="cancelIrregular">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <div class="irregular-action-info">
                        <span class="irregular-selection-summary" id="selectionSummary">No subjects selected</span>
                    </div>
                    <button type="button" class="irregular-save-btn" id="savePastSubjects" disabled>
                        <i class="fas fa-save"></i>
                        Save Completed Subjects
                    </button>
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
    <script src="{{asset('js/function/student/dashboard.js')}}"></script>
</body>
</html>