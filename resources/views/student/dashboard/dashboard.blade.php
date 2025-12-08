<?php
$firstname = $user->user_information->firstname;
$lastname = $user->user_information->lastname;
$student_id = $user->user_information->student->id_no ?? 'Not Set';
$is_regular = $user->user_information->student->is_regular ?? 'Not Set';
$profile_picture = $user->profile;

// Check if student ID is 'none' (case-insensitive)
$show_student_form = (strtolower($student_id) === 'none');
$show_prereg_form = $isEnrollmentActive;
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
    <title>enrollsys evsu</title>
    <link rel="website icon" href="{{ asset('img/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <i class="fas fa-bell"></i> <!-- Changed from fa-calendar-alt -->
                    <span>Notifications</span> <!-- Changed from Schedule -->
                </a>
                <!-- <a class="menu-item" data-section="grades">
                    <i class="fas fa-chart-bar"></i>
                    <span>Grades</span>
                </a> -->
                <a class="menu-item" data-section="files">
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
                                <i class="fas fa-chart-line"></i>
                            </div>
                        <h3 class="stat-value" id="averageGradeValue" style="font-size: 1rem;"></h3>
                        <p class="stat-label">Average Grade</p>
                        <div class="grade-indicator">
                            <div class="grade-bar">
                                <div class="grade-fill" id="gradeFill"></div>
                            </div>
                            <div class="grade-labels">
                                <span>1.0</span>
                                <span>3.0</span>
                                <span>5.0</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card" id="d-stat-card-enroll">
                        <div class="stat-icon grades" id="enroll_icon">
                            <i class="fa-solid fa-plus"></i>
                        </div>
                        <h3 class="stat-value" id="determined">
                            @if($user->user_information->student->status === 'None' || !$isEnrollmentActive)
                                Enrollment Not Available
                            @else
                                Enroll Now
                            @endif
                        </h3>
                        <p class="stat-label">
                            @if($enrollmentPeriod)
                                Enrollment is until
                                {{ \Carbon\Carbon::parse($enrollmentPeriod->start)->format('F j, Y') }} - {{ \Carbon\Carbon::parse($enrollmentPeriod->end)->format('F j, Y') }}
                            @else
                                No active enrollment period
                            @endif
                        </p>
                        
                        <!-- Debug information (you can remove this after testing) -->
                        <div style="display: none;" class="debug-info">
                            <p>Student Status: {{ $user->user_information->student->status }}</p>
                            <p>Is Enrollment Active: {{ $isEnrollmentActive ? 'Yes' : 'No' }}</p>
                            <p>Enrollment Period: {{ $enrollmentPeriod ? $enrollmentPeriod->semester . ' ' . $enrollmentPeriod->academic_year : 'None' }}</p>
                            <p>Student ID: {{ $user->user_information->student->id }}</p>
                        </div>
                        
                        <input type="text" id="is-regular" value="{{ $user->user_information->student->is_regular }}" hidden>
                        <input type="text" id="student-status" value="{{ $user->user_information->student->status }}" hidden>
                        <input type="text" id="is-enrollment-active" value="{{ $isEnrollmentActive ? '1' : '0' }}" hidden>
                        <input type="text" id="enrollment-period" value="{{ $enrollmentPeriod ? '1' : '0' }}" hidden>
                    </div>

                    <!-- Rest of your stat cards remain the same -->
                    <div class="stat-card">
                        <div class="stat-icon courses">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3 class="stat-value" id="enrolled_subjects"></h3>
                        <p class="stat-label">Enrolled Subjects</p>
                    </div>

                    <!-- <div class="stat-card">
                        <div class="stat-icon courses">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3 class="stat-value" id="enrolled_subjects">1.0</h3>
                        <p class="stat-label">Average Grade</p>
                    </div> -->
                    
                    
                    <div class="stat-card">
                        <div class="stat-icon attendance">
                            <i class="fa-solid fa-folder-open"></i>
                        </div>
                        <h3 class="stat-value" id="count_documents"></h3>
                        <p class="stat-label">Documents</p>
                    </div>
                </div>
                
                <h2 class="section-title">My Subjects</h2>

                <!-- Filter Container -->
                <div class="filter-container">
                    <div class="filter-group">
                        <label for="year-level-filter">Year Level:</label>
                        <select id="year-level-filter" class="filter-select">
                            <option value="all">All Years</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                            <option value="5th Year">5th Year</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="search-subject">Search:</label>
                        <input type="text" id="search-subject" class="search-input" placeholder="Search subject...">
                    </div>
                </div>

                <!-- Subjects Grid -->
                <div class="courses-grid" id="courses-grid">
                    @forelse($enrolledSubjects as $subject)
                        <div class="course-card" 
                            data-year-level="{{ $subject->year_level }}"
                            data-semester="{{ $subject->semester }}"
                            data-subject-code="{{ strtolower($subject->subject_code) }}"
                            data-subject-name="{{ strtolower($subject->subject_name) }}"
                            data-grade="{{ $subject->grade ? 'graded' : 'ungraded' }}"
                            data-grade-value="{{ $subject->grade ?? '' }}">
                            <div class="course-header">
                                <h3 class="course-code">{{ $subject->subject_code }}</h3>
                                <p class="course-name">{{ $subject->subject_name }}</p>
                                <span class="course-badge {{ $subject->year_level == '1st Year' ? 'badge-freshman' : ($subject->year_level == '2nd Year' ? 'badge-sophomore' : ($subject->year_level == '3rd Year' ? 'badge-junior' : 'badge-senior')) }}">
                                    {{ $subject->year_level }}
                                </span>
                            </div>
                            <div class="course-body">
                                <div class="course-info">
                                    <span><strong>Units:</strong> {{ $subject->units }}</span>
                                    <span><strong>Semester:</strong> {{ $subject->semester }}</span>
                                </div>
                                <div class="course-info">
                                    <span><strong>Year Level:</strong> {{ $subject->year_level }}</span>
                                    @if($subject->grade)
                                        <span class="grade-display {{ $subject->grade <= 1.5 ? 'grade-excellent' : ($subject->grade <= 2.5 ? 'grade-good' : 'grade-poor') }}">
                                            <strong>Grade:</strong> {{ $subject->grade }}
                                        </span>
                                    @else
                                        <span class="grade-display grade-ungraded">
                                            <strong>Grade:</strong> Not Yet Graded
                                        </span>
                                    @endif
                                </div>
                                <div class="course-meta">
                                    <small>Enrolled: {{ \Carbon\Carbon::parse($subject->date_enrolled)->format('M d, Y') }}</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="no-subjects">
                            <i class="fas fa-book-open"></i>
                            <h3>No Subjects Enrolled</h3>
                            <p>You haven't enrolled in any subjects yet.</p>
                        </div>
                    @endforelse
                </div>
                
                <!-- Today's Schedule -->
    
                
                
                <!-- <h2 class="section-title">Today's Schedule</h2>
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
                </div> -->
            </div>
            
            <!-- My Courses Section -->
            <div id="courses-section" class="content-section">

                <!-- Filter Container -->
                <div class="filter-container">
                    <div class="filter-group">
                        <label for="year-level-filter">Year Level:</label>
                        <select id="year-level-filter2" class="filter-select">
                            <option value="all">All Years</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                            <option value="5th Year">5th Year</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="search-subject">Search:</label>
                        <input type="text" id="search-subject2" class="search-input" placeholder="Search subject...">
                    </div>
                </div>

                <!-- Subjects Grid -->
                <div class="courses-grid" id="courses-grid2">
                    @forelse($enrolledSubjects as $subject)
                        <div class="course-card" 
                            data-year-level="{{ $subject->year_level }}"
                            data-semester="{{ $subject->semester }}"
                            data-subject-code="{{ strtolower($subject->subject_code) }}"
                            data-subject-name="{{ strtolower($subject->subject_name) }}"
                            data-grade="{{ $subject->grade ? 'graded' : 'ungraded' }}"
                            data-grade-value="{{ $subject->grade ?? '' }}">
                            <div class="course-header">
                                <h3 class="course-code">{{ $subject->subject_code }}</h3>
                                <p class="course-name">{{ $subject->subject_name }}</p>
                                <span class="course-badge {{ $subject->year_level == '1st Year' ? 'badge-freshman' : ($subject->year_level == '2nd Year' ? 'badge-sophomore' : ($subject->year_level == '3rd Year' ? 'badge-junior' : 'badge-senior')) }}">
                                    {{ $subject->year_level }}
                                </span>
                            </div>
                            <div class="course-body">
                                <div class="course-info">
                                    <span><strong>Units:</strong> {{ $subject->units }}</span>
                                    <span><strong>Semester:</strong> {{ $subject->semester }}</span>
                                </div>
                                <div class="course-info">
                                    <span><strong>Year Level:</strong> {{ $subject->year_level }}</span>
                                    @if($subject->grade)
                                        <span class="grade-display {{ $subject->grade <= 1.5 ? 'grade-excellent' : ($subject->grade <= 2.5 ? 'grade-good' : 'grade-poor') }}">
                                            <strong>Grade:</strong> {{ $subject->grade }}
                                        </span>
                                    @else
                                        <span class="grade-display grade-ungraded">
                                            <strong>Grade:</strong> Not Yet Graded
                                        </span>
                                    @endif
                                </div>
                                <div class="course-meta">
                                    <small>Enrolled: {{ \Carbon\Carbon::parse($subject->date_enrolled)->format('M d, Y') }}</small>
                                </div>

                                <button class="btn-primary mt-3 w-100 view-subject-btn" 
                                        data-subject-id="{{ $subject->subject_id }}"
                                        data-subject-code="{{ $subject->subject_code }}"
                                        data-subject-name="{{ $subject->subject_name }}"
                                        data-units="{{ $subject->units }}"
                                        data-year-level="{{ $subject->year_level }}"
                                        data-semester="{{ $subject->semester }}"
                                        data-grade="{{ $subject->grade }}"
                                        data-date-enrolled="{{ $subject->date_enrolled }}">
                                    View Subject
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="no-subjects">
                            <i class="fas fa-book-open"></i>
                            <h3>No Subjects Enrolled</h3>
                            <p>You haven't enrolled in any subjects yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            
            <!-- Schedule Section -->
            <div id="schedule-section" class="content-section">
                <h2 class="section-title">Notifications</h2>
                    
                    <div class="notifications-container">
                        <div class="notifications-header">
                            <div class="notifications-actions">
                                <button class="btn-secondary" id="markAllRead">
                                    <i class="fas fa-check-double"></i> Mark All as Read
                                </button>
                                <button class="btn-secondary" id="clearAllNotifications">
                                    <i class="fas fa-trash"></i> Clear All
                                </button>
                            </div>
                            <div class="notifications-filter">
                                <select class="filter-select" id="notificationFilter">
                                    <option value="all">All Notifications</option>
                                    <option value="unread">Unread Only</option>
                                    <option value="enrollment">Enrollment</option>
                                    <option value="grades">Grades</option>
                                    <option value="documents">Documents</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="notifications-list" id="notificationsList">
                            <!-- Notifications will be loaded dynamically -->
                            <div class="notifications-loading">
                                <div class="loading-spinner"></div>
                                <p>Loading notifications...</p>
                            </div>
                        </div>
                        
                        <div class="notifications-empty-state" id="noNotifications" style="display: none;">
                            <div class="empty-state-icon">
                                <i class="fas fa-bell-slash"></i>
                            </div>
                            <h4>No notifications yet</h4>
                            <p>You're all caught up! New notifications will appear here.</p>
                        </div>
                        
                        <div class="notifications-footer">
                            <div class="notifications-stats">
                                <span id="unreadCount">0 unread</span>
                                <span id="totalCount">• 0 total</span>
                            </div>
                            <button class="btn-secondary" id="loadMoreNotifications">
                                <i class="fas fa-sync-alt"></i> Load More
                            </button>
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
            <!-- <div id="assignments-section" class="content-section">
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
            </div> -->

            
            <!-- Files Storage Section -->
            <div id="files-section" class="content-section">
                <h2 class="section-title">My Files & Documents</h2>
                
                <!-- Year Level Filter -->
                <div class="schedule-container mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Files by Year Level</h4>
                        <div class="year-level-filter">
                            <select id="yearLevelFilter" class="form-select" style="width: auto;">
                                <option value="all">All Years</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                                <option value="5th Year">5th Year</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Year Level Navigation -->
                    <div class="year-level-tabs mb-4">
                        <div class="nav nav-pills" id="yearTabs" role="tablist">
                            <button class="nav-link active" id="all-tab" data-bs-toggle="pill" data-bs-target="#all" type="button">All Files</button>
                            <button class="nav-link" id="year1-tab" data-bs-toggle="pill" data-bs-target="#year1" type="button">1st Year</button>
                            <button class="nav-link" id="year2-tab" data-bs-toggle="pill" data-bs-target="#year2" type="button">2nd Year</button>
                            <button class="nav-link" id="year3-tab" data-bs-toggle="pill" data-bs-target="#year3" type="button">3rd Year</button>
                            <button class="nav-link" id="year4-tab" data-bs-toggle="pill" data-bs-target="#year4" type="button">4th Year</button>
                        </div>
                    </div>
                    
                    <!-- File Statistics -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="stat-card-small">
                                <div class="stat-icon-sm">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="stat-content-sm">
                                    <div class="stat-value-sm" id="totalFiles">0</div>
                                    <div class="stat-label-sm">Total Files</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card-small">
                                <div class="stat-icon-sm" style="background: linear-gradient(135deg, #10b981, #059669);">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stat-content-sm">
                                    <div class="stat-value-sm" id="academicFiles">0</div>
                                    <div class="stat-label-sm">Academic Files</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card-small">
                                <div class="stat-icon-sm" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <div class="stat-content-sm">
                                    <div class="stat-value-sm" id="paymentFiles">0</div>
                                    <div class="stat-label-sm">Payment Records</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card-small">
                                <div class="stat-icon-sm" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                                    <i class="fas fa-archive"></i>
                                </div>
                                <div class="stat-content-sm">
                                    <div class="stat-value-sm" id="totalSize">0 MB</div>
                                    <div class="stat-label-sm">Storage Used</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Files Container -->
                <div class="tab-content" id="yearTabsContent">
                    <!-- All Files Tab -->
                    <div class="tab-pane fade show active" id="all" role="tabpanel">
                        <div class="schedule-container">
                            <h4 class="mb-4">All Academic Files</h4>
                            <div id="all-files-container" class="files-grid">
                                <!-- Files will be loaded here dynamically -->
                                <div class="loading-state">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading your files...</p>
                                </div>
                            </div>
                            
                            <h4 style="display: none;" class="mb-4 mt-5">Payment Records</h4>
                            <div style="display: none;" id="payment-files-container" class="files-grid">
                                <!-- Payment files will be loaded here dynamically -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Year 1 Tab -->
                    <div class="tab-pane fade" id="year1" role="tabpanel">
                        <div class="schedule-container">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="mb-0">1st Year Files</h4>
                                <span class="badge bg-primary">2025-2026</span>
                            </div>
                            <div id="year1-files" class="files-grid">
                                <!-- 1st Year files will be loaded here dynamically -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Year 2 Tab -->
                    <div class="tab-pane fade" id="year2" role="tabpanel">
                        <div class="schedule-container">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="mb-0">2nd Year Files</h4>
                                <span class="badge bg-primary">2026-2027</span>
                            </div>
                            <div id="year2-files" class="files-grid">
                                <!-- 2nd Year files will be loaded here dynamically -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Year 3 Tab -->
                    <div class="tab-pane fade" id="year3" role="tabpanel">
                        <div class="schedule-container">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="mb-0">3rd Year Files</h4>
                                <span class="badge bg-primary">2027-2028</span>
                            </div>
                            <div id="year3-files" class="files-grid">
                                <!-- 3rd Year files will be loaded here dynamically -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Year 4 Tab -->
                    <div class="tab-pane fade" id="year4" role="tabpanel">
                        <div class="schedule-container">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="mb-0">4th Year Files</h4>
                                <span class="badge bg-primary">2028-2029</span>
                            </div>
                            <div id="year4-files" class="files-grid">
                                <!-- 4th Year files will be loaded here dynamically -->
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Empty State Template -->
                <div id="empty-state-template" class="d-none">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h4>No files found</h4>
                        <p>No files available for this year level.</p>
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
                                    <!-- <span class="info-label">Department</span> -->
                                    <span class="info-value">Information Technology</span>
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
                
                <!-- <div class="settings-card">
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
                </div> -->
                
                <!-- <div class="settings-card">
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
                </div> -->
                
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
                    
                    <!-- <div class="settings-option">
                        <div class="option-info">
                            <h5>Font Size</h5>
                            <p>Adjust the font size to your preference</p>
                        </div>
                        <select class="form-control" style="width: auto;">
                            <option>Small</option>
                            <option selected>Medium</option>
                            <option>Large</option>
                        </select>
                    </div> -->

                    <div class="settings-option theme-picker">
                        <div class="option-info">
                            <h5>Theme Color</h5>
                            <p>Choose your preferred accent color for the interface</p>
                            <div class="current-theme-info">
                                <small>Current: <span id="current-theme-name">Blue</span></small>
                            </div>
                        </div>
                        <div class="theme-colors">
                            <div class="color-option" data-color="#4361ee" style="background-color: #4361ee;" title="Blue Theme">
                                <div class="color-tooltip">Blue</div>
                            </div>
                            <div class="color-option" data-color="#2c5530" style="background-color: #2c5530;" title="Green Theme">
                                <div class="color-tooltip">Green</div>
                            </div>
                            <div class="color-option" data-color="#8b5cf6" style="background-color: #8b5cf6;" title="Purple Theme">
                                <div class="color-tooltip">Purple</div>
                            </div>
                            <div class="color-option" data-color="#ef4444" style="background-color: #ef4444;" title="Red Theme">
                                <div class="color-tooltip">Red</div>
                            </div>
                            <div class="color-option" data-color="#f59e0b" style="background-color: #f59e0b;" title="Orange Theme">
                                <div class="color-tooltip">Orange</div>
                            </div>
                            <div class="color-option" data-color="#800000" style="background-color: #800000;" title="Maroon Theme">
                                <div class="color-tooltip">Maroon</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Subject Details Modal -->
            <div id="subjectModal" class="modal fade" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="subjectModalTitle">Subject Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Basic Information</h6>
                                    <p><strong>Subject Code:</strong> <span id="modalSubjectCode"></span></p>
                                    <p><strong>Subject Name:</strong> <span id="modalSubjectName"></span></p>
                                    <p><strong>Units:</strong> <span id="modalSubjectUnits"></span></p>
                                    <p><strong>Year Level:</strong> <span id="modalYearLevel"></span></p>
                                    <p><strong>Semester:</strong> <span id="modalSemester"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Grade Information</h6>
                                    <p><strong>Current Grade:</strong> <span id="modalGrade"></span></p>
                                    <p><strong>Date Enrolled:</strong> <span id="modalDateEnrolled"></span></p>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h6 class="text-muted">Prerequisites</h6>
                                <div id="prerequisitesList">
                                    <!-- Prerequisites will be loaded here -->
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h6 class="text-muted">Subject Description</h6>
                                <p id="modalDescription" class="text-muted">No description available.</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Subject Details Modal -->
        </div>
    </div>

    <!-- Student Information Modal -->
    
    @if($isEnrollmentActive == 1 && $is_regular == 5)
        <div id="studentInfoModal2" class="modal-overlay <?php echo $show_prereg_form ? 'active' : ''; ?>">
            <div class="modal-container">
                <div class="modal-header">
                    <h3 style="color: white;">Enrollment Pre-Registration Form</h3>
                </div>
                
                <form id="studentInfoForm2" class="modal-form">
                    @csrf                   
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
                            <!-- <option value="Transferee">Transferee</option> -->
                        </select>
                        <div class="form-error" id="student_type_error"></div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary2 btn-full">
                            <i class="fas fa-save"></i>
                            Save Information
                        </button>
                    </div>
                </form>
                
                <div class="modal-footer">
                    <p class="form-note">
                        <i class="fas fa-info-circle"></i>
                        This information is required for enrollment.
                    </p>
                </div>
            </div>
        </div>
    @endif()

        <div id="studentInfoModal" class="modal-overlay <?php echo $show_student_form ? 'active' : ''; ?>">
            <div class="modal-container">
                <div class="modal-header">
                    <h3 style="color: white;">Complete Your Student Information</h3>
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
                    
                    <!-- NEW: Curriculum Dropdown -->
                    <div class="form-group">
                        <label for="curriculum" class="form-label">
                            <i class="fas fa-book"></i>
                            Your Curriculum
                        </label>
                        <select id="curriculum" name="curriculum" class="form-control" required>
                            <option value="">Select Curriculum</option>
                            <!-- Options will be populated dynamically -->
                        </select>
                        <div class="form-error" id="curriculum_error"></div>
                        <small class="form-text text-muted" style="display: flex; align-items: center; gap: 6px; margin-top: 6px;">
                            <i class="fas fa-info-circle"></i>
                            Your curriculum determines which subjects will be available for enrollment.
                        </small>
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

    <!-- Enhanced Enrollment Modal -->
    <div id="enhancedEnrollmentModal" class="enhanced-enrollment-modal-overlay">
        <div class="enhanced-enrollment-modal-container">
            <!-- Enhanced Header -->
            <div class="enhanced-enrollment-modal-header">
                <div class="enhanced-enrollment-modal-title-section">
                    <div class="enhanced-enrollment-title-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div>
                        <h2 class="enhanced-enrollment-modal-title">Course Enrollment</h2>
                        <p class="enhanced-enrollment-modal-subtitle">Complete your enrollment in 4 simple steps</p>
                    </div>
                </div>
                <!-- <button type="button" class="enhanced-enrollment-close-modal" id="enhancedCloseEnrollmentModal" aria-label="Close modal">
                    <i class="fas fa-times"></i>
                </button> -->
            </div>
            
            <div class="enhanced-enrollment-modal-content">
                <!-- Progress Indicator -->
                <div class="enhanced-enrollment-progress-section">
                    <div class="enhanced-enrollment-progress-steps">
                        <div class="enhanced-progress-step active" data-step="1">
                            <div class="enhanced-step-number">1</div>
                            <span class="enhanced-step-label">Select Subjects</span>
                        </div>
                        <div class="enhanced-progress-step" data-step="2">
                            <div class="enhanced-step-number">2</div>
                            <span class="enhanced-step-label">Upload FHE</span>
                        </div>
                        <div class="enhanced-progress-step" data-step="3">
                            <div class="enhanced-step-number">3</div>
                            <span class="enhanced-step-label">Payment</span>
                        </div>
                        <div class="enhanced-progress-step" data-step="4">
                            <div class="enhanced-step-number">4</div>
                            <span class="enhanced-step-label">Confirm</span>
                        </div>
                    </div>
                </div>

                <!-- Student Summary Card -->
                <div class="enhanced-enrollment-student-summary">
                    <div class="enhanced-student-avatar">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="enhanced-student-details">
                        <h4>{{ $firstname ?? 'Student' }} {{ $lastname ?? '' }}</h4>
                        <div class="enhanced-student-meta">
                            <span class="enhanced-student-id">{{ $student_id }}</span>
                            <span class="enhanced-student-badge regular" id="enhancedStudentTypeBadge">Regular Student</span>
                        </div>
                    </div>
                    <div class="enhanced-student-stats">
                        <div class="enhanced-stat">
                            <span class="enhanced-stat-value" id="enhancedSelectedCount">0</span>
                            <span class="enhanced-stat-label">Selected</span>
                        </div>
                        <div class="enhanced-stat">
                            <span class="enhanced-stat-value" id="enhancedTotalUnits">0</span>
                            <span class="enhanced-stat-label">Units</span>
                        </div>
                        <div class="enhanced-stat">
                            <span class="enhanced-stat-value" id="enhancedCurrentYearLevel">-</span>
                            <span class="enhanced-stat-label">Year Level</span>
                        </div>
                    </div>
                </div>

                <!-- Enrollment Information -->
                <div class="enhanced-enrollment-info-card">
                    <div class="enhanced-enrollment-info-grid">
                        <div class="enhanced-info-item">
                            <div class="enhanced-info-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="enhanced-info-content">
                                <span class="enhanced-info-label">Year Level</span>
                                <span class="enhanced-info-value" id="enhancedEnrollmentYearLevel">-</span>
                            </div>
                        </div>
                        <div class="enhanced-info-item">
                            <div class="enhanced-info-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="enhanced-info-content">
                                <span class="enhanced-info-label">Semester</span>
                                <span class="enhanced-info-value" id="enhancedEnrollmentSemester">-</span>
                            </div>
                        </div>
                        <div class="enhanced-info-item">
                            <div class="enhanced-info-icon">
                                <i class="fas fa-user-tag"></i>
                            </div>
                            <div class="enhanced-info-content">
                                <span class="enhanced-info-label">Student Type</span>
                                <span class="enhanced-info-value" id="enhancedEnrollmentStudentType">-</span>
                            </div>
                        </div>
                        <div class="enhanced-info-item">
                            <div class="enhanced-info-icon">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <div class="enhanced-info-content">
                                <span class="enhanced-info-label">Max Units</span>
                                <span class="enhanced-info-value" id="enhancedMaxUnits">23</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="enhanced-enrollment-quick-actions">
                    <button class="enhanced-quick-action-btn" id="enhancedQuickSelectAll">
                        <i class="fas fa-check-double"></i>
                        Select All
                    </button>
                    <button class="enhanced-quick-action-btn" id="enhancedQuickDeselectAll">
                        <i class="fas fa-times"></i>
                        Clear All
                    </button>
                    <div class="enhanced-search-toggle" id="enhancedMobileSearchToggle">
                        <i class="fas fa-search"></i>
                    </div>
                </div>

                <!-- Mobile Search Panel -->
                <div class="enhanced-enrollment-mobile-search" id="enhancedMobileSearchPanel">
                    <div class="enhanced-mobile-search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search subjects..." id="enhancedMobileSubjectsSearch">
                        <button class="enhanced-mobile-search-close" id="enhancedMobileSearchClose">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="enhanced-enrollment-main-content">
                    <!-- Desktop Filters -->
                    <div class="enhanced-enrollment-desktop-filters">
                        <div class="enhanced-filter-group">
                            <label>Filter by</label>
                            <select id="enhancedSubjectFilter" class="enhanced-filter-select">
                                <option value="all">All Subjects</option>
                                <option value="available">Available Only</option>
                                <option value="with-prerequisites">With Prerequisites</option>
                            </select>
                        </div>
                        <div class="enhanced-filter-group">
                            <label>Sort by</label>
                            <select id="enhancedSortFilter" class="enhanced-filter-select">
                                <option value="code">Subject Code</option>
                                <option value="name">Subject Name</option>
                                <option value="units">Units</option>
                            </select>
                        </div>
                        <!-- <div class="enhanced-filter-group">
                            <label>Section</label>
                            <select id="enhancedSectionFilter" class="enhanced-filter-select">
                                <option value="all">All Sections</option>
                                Sections will be populated dynamically
                            </select>
                        </div> -->
                        <div class="enhanced-filter-group enhanced-search-group">
                            <label>Search</label>
                            <div class="enhanced-search-input-wrapper">
                                <i class="fas fa-search"></i>
                                <input type="text" id="enhancedSubjectsSearch" placeholder="Search by code or name...">
                                <button class="enhanced-clear-search" id="enhancedClearSearch">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Filter Bar -->
                    <div class="enhanced-enrollment-mobile-filter-bar">
                        <button class="enhanced-mobile-filter-btn" id="enhancedMobileFilterToggle">
                            <i class="fas fa-filter"></i>
                            Filters
                            <span class="enhanced-filter-count">0</span>
                        </button>
                        <div class="enhanced-mobile-selection-info">
                            <span id="enhancedSelectedCount">0 selected</span>
                        </div>
                    </div>

                    <!-- Mobile Filter Panel -->
                    <div class="enhanced-enrollment-mobile-filters" id="enhancedMobileFilterPanel">
                        <div class="enhanced-mobile-filter-header">
                            <h4>Filters</h4>
                            <button class="enhanced-mobile-filter-close" id="enhancedMobileFilterClose">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="enhanced-mobile-filter-content">
                            <div class="enhanced-mobile-filter-group">
                                <label>Filter by</label>
                                <select id="enhancedMobileSubjectFilter" class="enhanced-filter-select">
                                    <option value="all">All Subjects</option>
                                    <option value="available">Available Only</option>
                                    <option value="with-prerequisites">With Prerequisites</option>
                                </select>
                            </div>
                            <div class="enhanced-mobile-filter-group">
                                <label>Sort by</label>
                                <select id="enhancedMobileSortFilter" class="enhanced-filter-select">
                                    <option value="code">Subject Code</option>
                                    <option value="name">Subject Name</option>
                                    <option value="units">Units</option>
                                </select>
                            </div>
                            <div class="enhanced-mobile-filter-group">
                                <label>Section</label>
                                <select id="enhancedMobileSectionFilter" class="enhanced-filter-select">
                                    <option value="all">All Sections</option>
                                    <!-- Sections will be populated dynamically -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Subjects Grid -->
                    <div class="enhanced-enrollment-subjects-grid" id="enhancedSubjectsList">
                        <!-- Subjects will be populated here -->
                        <div class="enhanced-enrollment-loading-state">
                            <div class="enhanced-loading-spinner"></div>
                            <p>Loading available subjects...</p>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div class="enhanced-enrollment-empty-state" style="display: none;">
                        <div class="enhanced-empty-state-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h4>No Subjects Available</h4>
                        <p>All subjects for this semester have been enrolled or no available subjects match your criteria.</p>
                    </div>
                </div>
                <!--  -->
                <!-- FHE Upload Section (Step 2) - NOW OUTSIDE MAIN CONTENT -->
                <div class="enhanced-enrollment-fhe-section" id="enhancedFheSection" style="display: none;">
                    <div class="enhanced-fhe-upload-container">
                        <div class="enhanced-fhe-header">
                            <div class="enhanced-fhe-icon">
                                <i class="fas fa-file-upload"></i>
                            </div>
                            <div class="enhanced-fhe-title">
                                <h3>Upload FHE File</h3>
                                <p>Please upload your FHE (Faculty Head Endorsement) file to proceed with enrollment</p>
                            </div>
                        </div>

                        <div class="enhanced-fhe-upload-area" id="enhancedFheUploadArea">
                            <div class="enhanced-fhe-drop-zone" id="enhancedFheDropZone">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <h4>Drag & Drop your FHE file here</h4>
                                <p>Supported formats: PDF, JPG, PNG, DOC, DOCX</p>
                                <p class="enhanced-fhe-max-size">Max file size: 5MB</p>
                                <button type="button" class="enhanced-btn-primary enhanced-fhe-browse-btn" id="enhancedFheBrowseBtn">
                                    <i class="fas fa-folder-open" style="color:white"></i>
                                    Browse Files
                                </button>
                            </div>
                            <input type="file" id="enhancedFheFileInput" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="display: none;">
                            
                            <div class="enhanced-fhe-preview" id="enhancedFhePreview" style="display: none;">
                                <div class="enhanced-fhe-preview-content">
                                    <div class="enhanced-fhe-file-info">
                                        <i class="fas fa-file-pdf enhanced-fhe-file-icon"></i>
                                        <div class="enhanced-fhe-file-details">
                                            <h5 id="enhancedFheFileName">document.pdf</h5>
                                            <span id="enhancedFheFileSize">2.5 MB</span>
                                        </div>
                                    </div>
                                    <button type="button" class="enhanced-fhe-remove-btn" id="enhancedFheRemoveBtn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="enhanced-fhe-upload-progress" id="enhancedFheUploadProgress" style="display: none;">
                                    <div class="enhanced-fhe-progress-bar">
                                        <div class="enhanced-fhe-progress-fill" id="enhancedFheProgressFill"></div>
                                    </div>
                                    <span class="enhanced-fhe-progress-text" id="enhancedFheProgressText">0%</span>
                                </div>
                            </div>
                        </div>

                        <div class="enhanced-fhe-requirements">
                            <h5>FHE File Requirements:</h5>
                            <ul>
                                <li><i class="fas fa-check"></i> File must be clear and readable</li>
                                <li><i class="fas fa-check"></i> Must contain faculty head signature</li>
                                <li><i class="fas fa-check"></i> Must be for the current semester</li>
                                <li><i class="fas fa-check"></i> File size should not exceed 5MB</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Payment Receipt Upload Section (Step 3) -->
                <div class="enhanced-enrollment-payment-section" id="enhancedPaymentSection" style="display: none;">
                    <div class="enhanced-payment-container">
                        <div class="enhanced-payment-header">
                            <div class="enhanced-payment-icon">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div class="enhanced-payment-title">
                                <h3>Payment Receipt Upload</h3>
                                <p>Upload your GCash payment receipt screenshot to complete enrollment</p>
                            </div>
                        </div>

                        <div class="enhanced-payment-summary">
                            <div class="enhanced-payment-breakdown">
                                <h4>Payment Summary</h4>
                                <div class="enhanced-payment-items">
                                    <div class="enhanced-payment-item">
                                        <span>Organizational Fee</span>
                                        <span>₱150.00</span>
                                    </div>
                                    <div class="enhanced-payment-item total">
                                        <span>Total Amount</span>
                                        <span>₱150.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="enhanced-payment-instructions">
                            <h4>Payment Instructions:</h4>
                            <div class="enhanced-payment-steps">
                                <div class="enhanced-payment-step">
                                    <div class="enhanced-step-number">1</div>
                                    <div class="enhanced-step-content">
                                        <strong>Pay via GCash</strong>
                                        <p>Send ₱150.00 to GCash Number: <strong>0912-345-6789</strong></p>
                                    </div>
                                </div>
                                <div class="enhanced-payment-step">
                                    <div class="enhanced-step-number">2</div>
                                    <div class="enhanced-step-content">
                                        <strong>Take Screenshot</strong>
                                        <p>Take a clear screenshot of your GCash payment receipt</p>
                                    </div>
                                </div>
                                <div class="enhanced-payment-step">
                                    <div class="enhanced-step-number">3</div>
                                    <div class="enhanced-step-content">
                                        <strong>Upload Receipt</strong>
                                        <p>Upload the screenshot using the form below</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Receipt Upload Area -->
                        <div class="enhanced-payment-upload-container">
                            <div class="enhanced-payment-upload-area" id="enhancedPaymentUploadArea">
                                <div class="enhanced-payment-drop-zone" id="enhancedPaymentDropZone">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    <h4>Upload GCash Payment Receipt</h4>
                                    <p>Drag & Drop your payment receipt screenshot here</p>
                                    <p class="enhanced-payment-max-size">Supported formats: JPG, PNG, PDF | Max file size: 5MB</p>
                                    <button type="button" class="enhanced-btn-primary enhanced-payment-browse-btn" id="enhancedPaymentBrowseBtn">
                                        <i class="fa fa-upload" style="font-size:24px;color:white"></i>
                                        Choose File
                                    </button>
                                </div>
                                <input type="file" id="enhancedPaymentFileInput" accept=".jpg,.jpeg,.png,.pdf" style="display: none;">
                                
                                <div class="enhanced-payment-preview" id="enhancedPaymentPreview" style="display: none;">
                                    <div class="enhanced-payment-preview-content">
                                        <div class="enhanced-payment-file-info">
                                            <i class="fas fa-file-image enhanced-payment-file-icon"></i>
                                            <div class="enhanced-payment-file-details">
                                                <h5 id="enhancedPaymentFileName">receipt.jpg</h5>
                                                <span id="enhancedPaymentFileSize">2.5 MB</span>
                                            </div>
                                        </div>
                                        <button type="button" class="enhanced-payment-remove-btn" id="enhancedPaymentRemoveBtn">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="enhanced-payment-upload-progress" id="enhancedPaymentUploadProgress" style="display: none;">
                                        <div class="enhanced-payment-progress-bar">
                                            <div class="enhanced-payment-progress-fill" id="enhancedPaymentProgressFill"></div>
                                        </div>
                                        <span class="enhanced-payment-progress-text" id="enhancedPaymentProgressText">0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="enhanced-payment-requirements">
                            <h5>Receipt Requirements:</h5>
                            <ul>
                                <li><i class="fas fa-check"></i> Must show GCash transaction details clearly</li>
                                <li><i class="fas fa-check"></i> Transaction amount must be ₱150.00</li>
                                <li><i class="fas fa-check"></i> Recipient number must be 0912-345-6789</li>
                                <li><i class="fas fa-check"></i> File must be clear and readable</li>
                                <li><i class="fas fa-check"></i> File size should not exceed 5MB</li>
                            </ul>
                        </div>

                        <!-- Payment Verification Status -->
                        <div class="enhanced-payment-verification" id="enhancedPaymentVerification" style="display: none;">
                            <div class="enhanced-verification-status">
                                <div class="enhanced-verification-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="enhanced-verification-content">
                                    <h4>Payment Receipt Uploaded Successfully!</h4>
                                    <p>Your payment receipt has been submitted for verification.</p>
                                    <div class="enhanced-verification-details">
                                        <div class="enhanced-verification-detail">
                                            <span>File Name:</span>
                                            <span id="enhancedVerificationFileName">receipt.jpg</span>
                                        </div>
                                        <div class="enhanced-verification-detail">
                                            <span>Uploaded:</span>
                                            <span id="enhancedVerificationTime">Just now</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Confirmation Section (Step 4) -->
                <div class="enhanced-enrollment-confirmation-section" id="enhancedConfirmationSection" style="display: none;">
                    <div class="enhanced-confirmation-container">
                        <div class="enhanced-confirmation-header">
                            <div class="enhanced-confirmation-icon">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div class="enhanced-confirmation-title">
                                <h3>Confirm Enrollment</h3>
                                <p>Review your enrollment details before submitting</p>
                            </div>
                        </div>

                        <div class="enhanced-confirmation-summary">
                            <div class="enhanced-confirmation-items">
                                <div class="enhanced-confirmation-item">
                                    <div class="enhanced-confirmation-item-icon">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div class="enhanced-confirmation-item-content">
                                        <h5>Selected Subjects</h5>
                                        <div id="enhancedConfirmationSubjectsList">
                                            <!-- Subjects will be populated here -->
                                        </div>
                                    </div>
                                </div>

                                <div class="enhanced-confirmation-item">
                                    <div class="enhanced-confirmation-item-icon">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div class="enhanced-confirmation-item-content">
                                        <h5>FHE Document</h5>
                                        <span id="enhancedConfirmationFheFile">document.pdf</span>
                                    </div>
                                </div>

                                <div class="enhanced-confirmation-item">
                                    <div class="enhanced-confirmation-item-icon">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <div class="enhanced-confirmation-item-content">
                                        <h5>Payment</h5>
                                        <div class="enhanced-confirmation-payment-status">
                                            <i class="fas fa-check-circle"></i>
                                            <span>₱150.00 paid via GCash</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="enhanced-confirmation-actions">
                            <button class="enhanced-btn-secondary" id="enhancedEditEnrollment">
                                <i class="fas fa-edit"></i>
                                Edit Details
                            </button>
                            <button class="enhanced-btn-primary" id="enhancedFinalSubmitEnrollment">
                                <i class="fas fa-paper-plane"></i>
                                Submit Enrollment
                            </button>
                        </div>
                    </div>
                </div>
                <!--  -->
            </div>
            
            
            <!-- Enhanced Footer -->
            <div class="enhanced-enrollment-modal-footer">
                <div class="enhanced-footer-selection-info">
                    <div class="enhanced-selection-summary">
                        <span class="enhanced-summary-count" id="enhancedSelectionSummary">0 subjects</span>
                        <span class="enhanced-summary-units" id="enhancedUnitsSummary">0 units</span>
                    </div>
                    <div class="enhanced-selection-actions">
                        <button class="enhanced-btn-secondary" id="enhancedCancelEnrollment" onclick="reload()">
                            <i class="fas fa-arrow-left"></i>
                            Cancel
                        </button>
                        <button class="enhanced-btn-secondary" id="enhancedBackStep" style="display: none;">
                            <i class="fas fa-arrow-left"></i>
                            Back
                        </button>
                        <button class="enhanced-btn-primary" id="enhancedNextStep">
                            <i class="fas fa-arrow-right"></i>
                            Next Step
                        </button>
                        <button class="enhanced-btn-primary" id="enhancedSubmitEnrollment" disabled style="display: none;">
                            <i class="fas fa-paper-plane"></i>
                            Submit Enrollment
                            <span class="enhanced-btn-badge" id="enhancedSubmitCount">0</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Irregular Student Past Subjects Modal -->
    <div id="irregularSubjectsModal" class="irregular-modal-overlay">
        <div class="irregular-modal-container">
            <!-- Enhanced Header -->
            <div class="irregular-modal-header">
                <div class="irregular-modal-title-section">
                    <div class="irregular-title-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <div>
                        <h2 class="irregular-modal-title">Academic History</h2>
                        <p class="irregular-modal-subtitle">Select subjects you've successfully completed in previous semesters</p>
                    </div>
                </div>
                <button type="button" class="irregular-close-modal" id="closeIrregularModal" aria-label="Close modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="irregular-modal-content">
                <!-- Progress Indicator -->
                <div class="irregular-progress-section">
                    <div class="irregular-progress-steps">
                        <div class="progress-step active">
                            <div class="step-number">1</div>
                            <span class="step-label">Select Subjects</span>
                        </div>
                        <div class="progress-step">
                            <div class="step-number">2</div>
                            <span class="step-label">Review</span>
                        </div>
                        <div class="progress-step">
                            <div class="step-number">3</div>
                            <span class="step-label">Confirm</span>
                        </div>
                    </div>
                </div>

                <!-- Student Summary Card -->
                <div class="irregular-student-summary">
                    <div class="student-avatar">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="student-details">
                        <h4>{{ $firstname ?? 'Student' }} {{ $lastname ?? '' }}</h4>
                        <div class="student-meta">
                            <span class="student-id">{{ $student_id }}</span>
                            <span class="student-badge irregular">Irregular Student</span>
                        </div>
                    </div>
                    <div class="student-stats">
                        <div class="stat">
                            <span class="stat-value" id="completedCount">0</span>
                            <span class="stat-label">Completed</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value" id="totalUnits">0</span>
                            <span class="stat-label">Units</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value" id="currentYearLevel">-</span>
                            <span class="stat-label">Year Level</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="irregular-quick-actions">
                    <button class="quick-action-btn" id="quickSelectAll">
                        <i class="fas fa-check-double"></i>
                        Select All
                    </button>
                    <button class="quick-action-btn" id="quickDeselectAll">
                        <i class="fas fa-times"></i>
                        Clear All
                    </button>
                    <div class="search-toggle" id="mobileSearchToggle">
                        <i class="fas fa-search"></i>
                    </div>
                </div>

                <!-- Mobile Search Panel -->
                <div class="irregular-mobile-search" id="mobileSearchPanel">
                    <div class="mobile-search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search subjects..." id="mobileSubjectsSearch">
                        <button class="mobile-search-close" id="mobileSearchClose">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="irregular-main-content">
                    <!-- Desktop Filters -->
                    <div class="irregular-desktop-filters">
                        <div class="filter-group">
                            <label>Year Level</label>
                            <select id="yearLevelFilter" class="filter-select">
                                <option value="all">All Years</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Semester</label>
                            <select id="semesterFilter" class="filter-select">
                                <option value="all">All Semesters</option>
                                <option value="1st Sem">1st Semester</option>
                                <option value="2nd Sem">2nd Semester</option>
                            </select>
                        </div>
                        <div class="filter-group search-group">
                            <label>Search</label>
                            <div class="search-input-wrapper">
                                <i class="fas fa-search"></i>
                                <input type="text" id="pastSubjectsSearch" placeholder="Search by code or name...">
                                <button class="clear-search" id="clearSearch">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Filter Bar -->
                    <div class="irregular-mobile-filter-bar">
                        <button class="mobile-filter-btn" id="mobileFilterToggle">
                            <i class="fas fa-filter"></i>
                            Filters
                            <span class="filter-count">0</span>
                        </button>
                        <div class="mobile-selection-info">
                            <span id="selectedCount">0 selected</span>
                        </div>
                    </div>

                    <!-- Mobile Filter Panel -->
                    <div class="irregular-mobile-filters" id="mobileFilterPanel">
                        <div class="mobile-filter-header">
                            <h4>Filters</h4>
                            <button class="mobile-filter-close" id="mobileFilterClose">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="mobile-filter-content">
                            <div class="mobile-filter-group">
                                <label>Year Level</label>
                                <select id="mobileYearLevelFilter" class="filter-select">
                                    <option value="all">All Years</option>
                                    <option value="1st Year">1st Year</option>
                                    <option value="2nd Year">2nd Year</option>
                                    <option value="3rd Year">3rd Year</option>
                                    <option value="4th Year">4th Year</option>
                                </select>
                            </div>
                            <div class="mobile-filter-group">
                                <label>Semester</label>
                                <select id="mobileSemesterFilter" class="filter-select">
                                    <option value="all">All Semesters</option>
                                    <option value="1st Sem">1st Semester</option>
                                    <option value="2nd Sem">2nd Semester</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Subjects Grid -->
                    <div class="irregular-subjects-grid" id="pastSubjectsList">
                        <!-- Subjects will be populated here -->
                        <div class="irregular-loading-state">
                            <div class="loading-spinner"></div>
                            <p>Loading your subjects...</p>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div class="irregular-empty-state" style="display: none;">
                        <div class="empty-state-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h4>No Subjects Found</h4>
                        <p>Try adjusting your search criteria or filters</p>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Footer -->
            <div class="irregular-modal-footer">
                <div class="footer-selection-info">
                    <div class="selection-summary">
                        <span class="summary-count" id="selectionSummary">0 subjects</span>
                        <span class="summary-units" id="unitsSummary">0 units</span>
                    </div>
                    <div class="selection-actions">
                        <button class="btn-secondary" id="cancelIrregular">
                            <i class="fas fa-arrow-left"></i>
                            Back
                        </button>
                        <button class="btn-primary" id="savePastSubjects" disabled>
                            <i class="fas fa-save"></i>
                            Save Selection
                            <span class="btn-badge" id="saveCount">0</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Irregular Student Enrollment Modal -->
    <div id="irregularEnrollmentModal" class="irregular-modal-overlay">
        <div class="irregular-modal-container">
            <!-- Enhanced Header -->
            <div class="irregular-modal-header">
                <div class="irregular-modal-title-section">
                    <div class="irregular-title-icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div>
                        <h2 class="irregular-modal-title">Irregular Student Enrollment</h2>
                        <p class="irregular-modal-subtitle">Select subjects based on your academic standing</p>
                    </div>
                </div>
                <button type="button" class="irregular-close-modal" id="closeIrregularEnrollmentModal" aria-label="Close modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="irregular-modal-content">
                <!-- Progress Indicator -->
                <div class="irregular-progress-section">
                    <div class="irregular-progress-steps">
                        <div class="progress-step active">
                            <div class="step-number">1</div>
                            <span class="step-label">Select Subjects</span>
                        </div>
                        <div class="progress-step">
                            <div class="step-number">2</div>
                            <span class="step-label">Review Schedule</span>
                        </div>
                        <div class="progress-step">
                            <div class="step-number">3</div>
                            <span class="step-label">Confirm Enrollment</span>
                        </div>
                    </div>
                </div>

                <!-- Student Summary Card -->
                <div class="irregular-student-summary">
                    <div class="student-avatar">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="student-details">
                        <h4>{{ $firstname ?? 'Student' }} {{ $lastname ?? '' }}</h4>
                        <div class="student-meta">
                            <span class="student-id">{{ $student_id }}</span>
                            <span class="student-badge irregular">Irregular Student</span>
                        </div>
                    </div>
                    <div class="student-stats">
                        <div class="stat">
                            <span class="stat-value" id="irregularSelectedCount">0</span>
                            <span class="stat-label">Selected</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value" id="irregularTotalUnits">0</span>
                            <span class="stat-label">Units</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value" id="irregularCurrentYearLevel">-</span>
                            <span class="stat-label">Year Level</span>
                        </div>
                    </div>
                </div>

                <!-- Enrollment Information -->
                <div class="enhanced-enrollment-info-card">
                    <div class="enhanced-enrollment-info-grid">
                        <div class="enhanced-info-item">
                            <div class="enhanced-info-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="enhanced-info-content">
                                <span class="enhanced-info-label">Year Level</span>
                                <span class="enhanced-info-value" id="irregularEnrollmentYearLevel">-</span>
                            </div>
                        </div>
                        <div class="enhanced-info-item">
                            <div class="enhanced-info-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="enhanced-info-content">
                                <span class="enhanced-info-label">Semester</span>
                                <span class="enhanced-info-value" id="irregularEnrollmentSemester">-</span>
                            </div>
                        </div>
                        <div class="enhanced-info-item">
                            <div class="enhanced-info-icon">
                                <i class="fas fa-user-tag"></i>
                            </div>
                            <div class="enhanced-info-content">
                                <span class="enhanced-info-label">Student Type</span>
                                <span class="enhanced-info-value" id="irregularEnrollmentStudentType">Irregular</span>
                            </div>
                        </div>
                        <div class="enhanced-info-item">
                            <div class="enhanced-info-icon">
                                <i class="fas fa-calculator"></i>
                            </div>
                            <div class="enhanced-info-content">
                                <span class="enhanced-info-label">Max Units</span>
                                <span class="enhanced-info-value" id="irregularMaxUnits">23</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="irregular-quick-actions">
                    <button class="quick-action-btn" id="irregularQuickSelectAll">
                        <i class="fas fa-check-double"></i>
                        Select All Available
                    </button>
                    <button class="quick-action-btn" id="irregularQuickDeselectAll">
                        <i class="fas fa-times"></i>
                        Clear All
                    </button>
                    <div class="search-toggle" id="irregularMobileSearchToggle">
                        <i class="fas fa-search"></i>
                    </div>
                </div>

                <!-- Mobile Search Panel -->
                <div class="irregular-mobile-search" id="irregularMobileSearchPanel">
                    <div class="mobile-search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search subjects..." id="irregularMobileSubjectsSearch">
                        <button class="mobile-search-close" id="irregularMobileSearchClose">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="irregular-main-content">
                    <!-- Desktop Filters -->
                    <div class="irregular-desktop-filters">
                        <div class="filter-group">
                            <label>Filter by</label>
                            <select id="irregularSubjectFilter" class="filter-select">
                                <option value="all">All Subjects</option>
                                <option value="available">Available Only</option>
                                <option value="with-prerequisites">With Prerequisites</option>
                                <option value="prerequisites-met">Prerequisites Met</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Sort by</label>
                            <select id="irregularSortFilter" class="filter-select">
                                <option value="code">Subject Code</option>
                                <option value="name">Subject Name</option>
                                <option value="units">Units</option>
                                <option value="year_level">Year Level</option>
                            </select>
                        </div>
                        <div class="filter-group search-group">
                            <label>Search</label>
                            <div class="search-input-wrapper">
                                <i class="fas fa-search"></i>
                                <input type="text" id="irregularSubjectsSearch" placeholder="Search by code or name...">
                                <button class="clear-search" id="irregularClearSearch">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Filter Bar -->
                    <div class="irregular-mobile-filter-bar">
                        <button class="mobile-filter-btn" id="irregularMobileFilterToggle">
                            <i class="fas fa-filter"></i>
                            Filters
                            <span class="filter-count">0</span>
                        </button>
                        <div class="mobile-selection-info">
                            <span id="irregularMobileSelectedCount">0 selected</span>
                        </div>
                    </div>

                    <!-- Mobile Filter Panel -->
                    <div class="irregular-mobile-filters" id="irregularMobileFilterPanel">
                        <div class="mobile-filter-header">
                            <h4>Filters</h4>
                            <button class="mobile-filter-close" id="irregularMobileFilterClose">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="mobile-filter-content">
                            <div class="mobile-filter-group">
                                <label>Filter by</label>
                                <select id="irregularMobileSubjectFilter" class="filter-select">
                                    <option value="all">All Subjects</option>
                                    <option value="available">Available Only</option>
                                    <option value="with-prerequisites">With Prerequisites</option>
                                    <option value="prerequisites-met">Prerequisites Met</option>
                                </select>
                            </div>
                            <div class="mobile-filter-group">
                                <label>Sort by</label>
                                <select id="irregularMobileSortFilter" class="filter-select">
                                    <option value="code">Subject Code</option>
                                    <option value="name">Subject Name</option>
                                    <option value="units">Units</option>
                                    <option value="year_level">Year Level</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Subjects Grid -->
                    <div class="irregular-subjects-grid" id="irregularSubjectsList">
                        <!-- Subjects will be populated here -->
                        <div class="irregular-loading-state">
                            <div class="loading-spinner"></div>
                            <p>Loading available subjects for irregular students...</p>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div class="irregular-empty-state" style="display: none;">
                        <div class="empty-state-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h4>No Subjects Available</h4>
                        <p>All available subjects for irregular students have been enrolled or no subjects match your criteria.</p>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Footer -->
            <div class="irregular-modal-footer">
                <div class="footer-selection-info">
                    <div class="selection-summary">
                        <span class="summary-count" id="irregularSelectionSummary">0 subjects</span>
                        <span class="summary-units" id="irregularUnitsSummary">0 units</span>
                    </div>
                    <div class="selection-actions">
                        <button class="btn-secondary" id="irregularCancelEnrollment">
                            <i class="fas fa-arrow-left"></i>
                            Cancel
                        </button>
                        <button class="btn-primary" id="irregularSubmitEnrollment" disabled>
                            <i class="fas fa-paper-plane"></i>
                            Submit Enrollment
                            <span class="btn-badge" id="irregularSubmitCount">0</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Logout Form -->
    <form id="logout-form" action="{{ route('student.logout') }}" method="POST" style="display: none;">
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
    <script src="{{asset('js/function/student/dashboard.js')}}"></script>
</body>
</html>