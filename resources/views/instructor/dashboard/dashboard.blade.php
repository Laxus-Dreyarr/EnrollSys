<?php
use Illuminate\Support\Facades\DB;

$firstname = $user->info->firstname;
$lastname = 'Donquixote';
$id = $user->info->instructor_id;
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
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <!-- <i class="fas fa-graduation-cap logo-icon"></i> -->
                     <i class="logo">
                        <img src="{{ asset('img/evsu-logo.png') }}" alt="">
                    </i>
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
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(($firstname ?? '') . ' ' . ($lastname ?? '')) }}&background=none&color=fff" alt="User Avatar" class="user-avatar">
                        <div class="status-indicator"></div>
                    </div>
                @endif
                
                <div class="user-info">
                    <h4 class="user-name">{{$firstname}}</h4>
                    <p class="user-id">{{$id}}</p>
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
                <a class="menu-item" data-section="input-grades">
                    <div class="menu-icon">
                        <i class="fas fa-pen-to-square"></i>
                    </div>
                    <span class="menu-text">Input Grades</span>
                    <div class="menu-hover-effect"></div>
                </a>
                <a class="menu-item" data-section="students">
                    <div class="menu-icon">
                        <i class="fas fa-user-group"></i>
                    </div>
                    <span class="menu-text">Students</span>
                    <div class="menu-hover-effect"></div>
                </a>
                <a class="menu-item" data-section="enrollment-requests">
                    <div class="menu-icon">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <span class="menu-text">Enrollment Request</span>
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
                <h1 class="page-title">Instructor Dashboard</h1>

                <div class="search-container">
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="header-search-input" class="search-input" placeholder="Search students, subjects...">
                        <button id="search-btn" class="search-btn">
                            <i style="color: white;" class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <!-- Search Bar -->
                <!-- <div class="search-container">
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="header-search-input" class="search-input" placeholder="Search students, subjects...">
                        <button class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div> -->

                <div class="notification-btn" onclick="showNotifications()">
                    <i class="fas fa-bell"></i>
                    <span id="notification-count2" class="notification-count" style="display: none;">0</span>
                </div>

                <!-- <div class="header-actions">
                    <div class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count">5</span>
                    </div>
                    <div class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </div>
                </div> -->
            </div>
            
            <!-- Dashboard Section -->
            <div id="dashboard-section" class="content-section active">
                <!-- Stats Overview -->
                <div class="stats-grid">
                    <!-- <div class="stat-card">
                        
                        <div class="card-bg-image courses-bg"></div>
                        <div class="stat-icon courses">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value">4</h3>
                            <p class="stat-label">Courses Teaching</p>
                        </div>
                    </div> -->
                    
                    <div class="stat-card" id="stat-card">
                        <!-- Background Image Container -->
                        <div class="card-bg-image students-bg"></div>
                        <div class="stat-icon students">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value" id="total-students"></h3>
                            <p class="stat-label">Total Students</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <!-- Background Image Container -->
                        <div class="card-bg-image assignments-bg"></div>
                        <div class="stat-icon assignments">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value" id="pending_request"></h3>
                            <p class="stat-label">Pending Request</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <!-- Background Image Container -->
                        <div class="card-bg-image deadlines-bg"></div>
                        <div class="stat-icon deadlines">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-content">
                            <h3 class="stat-value" style="font-size: 20px;">Enrollment Date</h3>
                            <p class="stat-label" id="enrollment_period">
                                 @if($enrollmentPeriod)
                                    Enrollment is until
                                    {{ \Carbon\Carbon::parse($enrollmentPeriod->start)->format('F j, Y') }} - {{ \Carbon\Carbon::parse($enrollmentPeriod->end)->format('F j, Y') }}
                                @else
                                    No active enrollment period
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Enhanced Quick Actions -->
                <h2 class="section-title">Quick Actions</h2>
                <div class="quick-actions-grid">
                    <div class="quick-action-card" data-action="create-assignment">
                        <div class="action-icon">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        <h4>Enrollment Request</h4>
                        <p>Student's Enrollment Request</p>
                        <div class="action-hover-effect"></div>
                    </div>
                    
                    <div class="quick-action-card" data-action="input-grades">
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
                </div>
                
                <!-- Notifications -->
                <h2 class="section-title">Notifications</h2>
                <div class="schedule-container">
                    @forelse($notifications as $notification)
                        <div class="schedule-day">
                            <h4 class="day-header">
                                {{ \Carbon\Carbon::parse($notification->created_at)->format('F d, Y') }}
                                @if(!$notification->is_read)
                                    <span class="badge badge-danger" style="background: #ef4444; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; margin-left: 8px;">New</span>
                                @endif
                            </h4>
                            
                            <div class="schedule-item">
                                <div class="schedule-time">
                                    {{ \Carbon\Carbon::parse($notification->created_at)->format('h:i A') }}
                                </div>
                                <div class="schedule-details">
                                    <div class="schedule-course">{{ $notification->title }}</div>
                                    <div class="schedule-location">{{ $notification->message }}</div>
                                </div>
                                <div class="schedule-action">
                                    <button class="btn-primary btn-sm mark-as-read-btn" data-id="{{ $notification->id }}">
                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="schedule-day">
                            <h4 class="day-header">No Notifications</h4>
                            <div class="schedule-item">
                                <div class="schedule-details">
                                    <div class="schedule-course">No notifications at this time</div>
                                    <div class="schedule-location">You'll see important updates here</div>
                                </div>
                            </div>
                        </div>
                    @endforelse
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
            <!-- Input Grades Section -->
            <div id="input-grades-section" class="content-section">
                @include('instructor.dashboard.partials.input-grades')
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
                    <h2 class="section-title">Student Records</h2>
                    <div class="student-filters">
                        <select class="form-control" id="year-level-filter">
                            <option value="">All Year Levels</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                            <option value="5th Year">5th Year</option>
                        </select>

                        <select class="form-control" id="curriculum-filter">
                            <option value="">All Curriculum</option>
                            <?php
                            // Get distinct curriculums from students table
                            $curriculumQuery = DB::table('students')
                                ->whereNotNull('curriculum')
                                ->distinct()
                                ->pluck('curriculum');
                            
                            foreach ($curriculumQuery as $curriculum) {
                                // Format curriculum display
                                $display = $curriculum;
                                if (is_numeric($curriculum) && strlen($curriculum) === 4) {
                                    $year = (int)$curriculum;
                                    $nextYear = $year + 1;
                                    $display = $year . '-' . $nextYear;
                                }
                                echo "<option value=\"$curriculum\">$display</option>";
                            }
                            ?>
                        </select>
                        <label for="#">Search:</label>
                        <input type="text" class="form-control" id="search-students" placeholder="">
                    </div>
                </div>
                
                <div class="students-container">
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Year Level</th>
                                <th>Student Type</th>
                                <th>Average Grade</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="students-table-body">
                            <?php
                            // Fetch all students with their information
                            $students = DB::table('students as s')
                                ->join('user_info as ui', 's.student_id', '=', 'ui.id')
                                ->leftJoin('enrolled_sub as es', function($join) {
                                    $join->on('s.id', '=', 'es.student_id')
                                        ->whereNotNull('es.grade')
                                        ->where('es.grade', '!=', 'INC')
                                        ->where('es.grade', '!=', 'DRP')
                                        ->where('es.grade', '!=', '');
                                })
                                ->leftJoin('student_count_year as scy', 's.id', '=', 'scy.student_id')
                                ->leftJoin('users as u', 'ui.user_id', '=', 'u.id')
                                ->select(
                                    's.id as student_table_id',
                                    's.id_no',
                                    's.year_level',
                                    's.is_regular',
                                    's.curriculum',
                                    's.status as enrollment_status',
                                    'ui.firstname',
                                    'ui.lastname',
                                    'ui.middlename',
                                    'scy.total_year',
                                    'u.email2 as email',
                                    DB::raw('AVG(CASE 
                                        WHEN es.grade REGEXP "^[0-9]+(\\\\.[0-9]+)?$" THEN CAST(es.grade AS DECIMAL(10,2)) 
                                        ELSE NULL 
                                    END) as average_grade')
                                )
                                ->groupBy('s.id', 's.id_no', 's.year_level', 's.is_regular', 's.curriculum', 
                                        's.status', 'ui.firstname', 'ui.lastname', 'ui.middlename', 'scy.total_year', 'u.email2')
                                ->get();
                            
                            foreach ($students as $student):
                                // Format name
                                $fullName = $student->firstname . ' ' . $student->lastname;
                                
                                // Determine student type
                                $studentType = '';
                                if ($student->is_regular == 1) {
                                    $studentType = 'Regular';
                                } elseif ($student->is_regular == 2) {
                                    $studentType = 'Irregular';
                                } elseif ($student->is_regular == 3) {
                                    $studentType = 'Transferee';
                                }
                                
                                // Format average grade
                                $averageGrade = 'N/A';
                                if ($student->average_grade !== null) {
                                    $averageGrade = number_format($student->average_grade, 1);
                                }
                                
                                // Determine status
                                $status = '';
                                $statusClass = '';
                                
                                if (!$student->total_year) {
                                    $status = 'Active';
                                    $statusClass = 'active';
                                } elseif ($student->total_year <= 4) {
                                    $status = 'Active';
                                    $statusClass = 'active';
                                } else {
                                    $status = 'At Risk';
                                    $statusClass = 'warning';
                                }
                                
                                // Format curriculum display
                                $curriculumDisplay = $student->curriculum;
                                if (is_numeric($student->curriculum) && strlen($student->curriculum) === 4) {
                                    $year = (int)$student->curriculum;
                                    $nextYear = $year + 1;
                                    $curriculumDisplay = $year . '-' . $nextYear;
                                }
                            ?>
                            <tr data-year-level="<?php echo $student->year_level; ?>" 
                                data-curriculum="<?php echo $student->curriculum; ?>"
                                data-student-name="<?php echo strtolower($fullName); ?>"
                                data-student-id="<?php echo $student->id_no; ?>"
                                data-student-data='<?php echo json_encode([
                                    'id_no' => $student->id_no,
                                    'full_name' => $fullName,
                                    'firstname' => $student->firstname,
                                    'lastname' => $student->lastname,
                                    'middlename' => $student->middlename,
                                    'year_level' => $student->year_level,
                                    'student_type' => $studentType,
                                    'curriculum' => $curriculumDisplay,
                                    'enrollment_status' => $student->enrollment_status,
                                    'email' => $student->email,
                                    'average_grade' => $averageGrade,
                                    'status' => $status
                                ]); ?>'>
                                <td><?php echo $student->id_no; ?></td>
                                <td><?php echo $fullName; ?></td>
                                <td><?php echo $student->year_level; ?></td>
                                <td><?php echo $studentType; ?></td>
                                <td><?php echo $averageGrade; ?></td>
                                <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                                <td>
                                    <button class="btn-icon view-profile" 
                                            title="View Profile" 
                                            data-student-id="<?php echo $student->student_table_id; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon send-message" 
                                            title="Send Message" 
                                            data-email="<?php echo $student->email; ?>">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Student Profile Modal -->
            <div id="studentProfileModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 style="color: white;">Student Profile</h3>
                        <span class="close-modal2">&times;</span>
                    </div>
                    <div class="modal-body" id="studentProfileContent">
                        <!-- Profile content will be loaded here -->
                    </div>
                </div>
            </div>
            
            <!-- Students Section -->
            <!-- <div id="students-section" class="content-section">
                <div class="section-header">
                    <h2 class="section-title">Student Management</h2>
                    <div class="student-filters">
                        <select class="form-control" id="year-level-filter">
                            <option value="">All Year Levels</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                            <option value="5th Year">5th Year</option>
                        </select>

                        <select class="form-control" id="curriculum-filter">
                            <option value="">All Curriculum</option>
                            <?php
                            // Get distinct curriculums from students table
                            $curriculumQuery = DB::table('students')
                                ->whereNotNull('curriculum')
                                ->distinct()
                                ->pluck('curriculum');
                            
                            foreach ($curriculumQuery as $curriculum) {
                                // Format curriculum display
                                if ($curriculum == '2018') {
                                    $display = '2018-2019';
                                } else {
                                    $display = $curriculum;
                                }
                                echo "<option value=\"$curriculum\">$display</option>";
                            }
                            ?>
                        </select>
                        <input type="text" class="form-control" id="search-students" placeholder="Search students...">
                    </div>
                </div>
                
                <div class="students-container">
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Year Level</th>
                                <th>Student Type</th>
                                <th>Average Grade</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="students-table-body">
                            <?php
                            // Fetch all students with their information
                            $students = DB::table('students as s')
                                ->join('user_info as ui', 's.student_id', '=', 'ui.id')
                                ->leftJoin('enrolled_sub as es', 's.id', '=', 'es.student_id')
                                ->leftJoin('student_count_year as scy', 's.id', '=', 'scy.student_id')
                                ->select(
                                    's.id as student_table_id',
                                    's.id_no',
                                    's.year_level',
                                    's.is_regular',
                                    's.curriculum',
                                    's.status as enrollment_status',
                                    'ui.firstname',
                                    'ui.lastname',
                                    'ui.middlename',
                                    'scy.total_year',
                                    DB::raw('AVG(CASE 
                                        WHEN es.grade REGEXP "^[0-9]+(\\\\.[0-9]+)?$" THEN CAST(es.grade AS DECIMAL(10,2)) 
                                        ELSE NULL 
                                    END) as average_grade')
                                )
                                ->groupBy('s.id', 's.id_no', 's.year_level', 's.is_regular', 's.curriculum', 
                                        's.status', 'ui.firstname', 'ui.lastname', 'ui.middlename', 'scy.total_year')
                                ->get();
                            
                            foreach ($students as $student):
                                // Format name
                                $fullName = $student->firstname . ' ' . $student->lastname;
                                
                                // Determine student type
                                $studentType = '';
                                if ($student->is_regular == 1) {
                                    $studentType = 'Regular';
                                } elseif ($student->is_regular == 2) {
                                    $studentType = 'Irregular';
                                } elseif ($student->is_regular == 3) {
                                    $studentType = 'Transferee';
                                }
                                
                                // Format average grade
                                $averageGrade = $student->average_grade ? number_format($student->average_grade, 5) : 'N/A';
                                
                                // Determine status
                                $status = '';
                                $statusClass = '';
                                
                                if (!$student->total_year) {
                                    $status = 'Active';
                                    $statusClass = 'active';
                                } elseif ($student->total_year <= 4) {
                                    $status = 'Active';
                                    $statusClass = 'active';
                                } else {
                                    $status = 'At Risk';
                                    $statusClass = 'warning';
                                }
                                
                                // Get email from users table for this student
                                $userEmail = DB::table('users')
                                    ->join('user_info', 'users.id', '=', 'user_info.user_id')
                                    ->where('user_info.id', $student->student_table_id)
                                    ->value('users.email2');
                            ?>
                            <tr data-year-level="<?php echo $student->year_level; ?>" 
                                data-curriculum="<?php echo $student->curriculum; ?>"
                                data-student-name="<?php echo strtolower($fullName); ?>"
                                data-student-id="<?php echo $student->id_no; ?>">
                                <td><?php echo $student->id_no; ?></td>
                                <td><?php echo $fullName; ?></td>
                                <td><?php echo $student->year_level; ?></td>
                                <td><?php echo $studentType; ?></td>
                                <td><?php echo $averageGrade; ?></td>
                                <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                                <td>
                                    <button class="btn-icon view-profile" 
                                            title="View Profile" 
                                            data-student-id="<?php echo $student->student_table_id; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-icon send-message" 
                                            title="Send Message" 
                                            data-email="<?php echo $userEmail; ?>">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div> -->

            <!-- Student Profile Modal -->
            <!-- <div id="studentProfileModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Student Profile</h3>
                        <span class="close-modal">&times;</span>
                    </div>
                    <div class="modal-body" id="studentProfileContent">
                        
                    </div>
                </div>
            </div> -->
            
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

            <!-- Enrollment Request Section -->
             <section id="enrollment-requests-section" class="content-section">
                <div class="section-header">
                    <h2>Enrollment Requests</h2>
                    <p>Review and manage student enrollment requests</p>
                </div>

                <div class="section-content">
                    <div id="enrollment-requests-loading" class="loading-container" style="display: none;">
                        <div class="loading-spinner"></div>
                        <p>Loading enrollment requests...</p>
                    </div>

                    <div id="no-enrollment-requests" class="no-data-container" style="display: none;">
                        <i class="fas fa-inbox"></i>
                        <h3>No Pending Requests</h3>
                        <p>There are no pending enrollment requests at this time.</p>
                    </div>

                    <div id="enrollment-requests-container" class="enrollment-requests-container" style="display: none;">
                        <div class="requests-sidebar">
                            <div class="sidebar-header" onclick="reload_request()">
                                <h3>Pending Requests</h3>
                                <span class="requests-count" id="requests-count"></span>
                            </div>
                            <div class="requests-list" id="enrollment-requests-list">
                                <!-- Requests will be loaded here -->
                            </div>
                        </div>
                        
                        <div class="requests-details">
                            <div id="enrollment-request-details" class="request-details-panel">
                                <div class="no-selection-message">
                                    <i class="fas fa-user-graduate"></i>
                                    <h3>Select a student</h3>
                                    <p>Click on a student from the list to view their enrollment details</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
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
                </div> -->
            </div>
        </div>
    </div>

    <!-- Subjects History Modal -->
    <div id="subjectsHistoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>
                    <i class="fas fa-history"></i>
                    <span>Subjects History - </span>
                    <span id="modalStudentName"></span>
                    <span id="modalStudentId" class="student-id"></span>
                </h2>
                <span class="close-modal">&times;</span>
            </div>
            
            <div class="modal-body">
                <!-- Filters -->
                <div class="filters-section">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="gradeFilter">
                                <i class="fas fa-filter"></i> Grade Status:
                            </label>
                            <select id="gradeFilter" class="form-select">
                                <option value="all">All Subjects</option>
                                <option value="graded">Graded Only</option>
                                <option value="ungraded">Ungraded Only</option>
                                <option value="passed">Passed (1.0-3.0)</option>
                                <option value="failed">Failed (4.0-5.0)</option>
                                <option value="inc">Incomplete (INC)</option>
                                <option value="drp">Dropped (DRP)</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="yearFilter">
                                <i class="fas fa-calendar"></i> School Year:
                            </label>
                            <select id="yearFilter" class="form-select">
                                <option value="all">All Years</option>
                                <!-- Will be populated dynamically -->
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="semesterFilter">
                                <i class="fas fa-calendar-alt"></i> Semester:
                            </label>
                            <select id="semesterFilter" class="form-select">
                                <option value="all">All Semesters</option>
                                <option value="1st Sem">1st Semester</option>
                                <option value="2nd Sem">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="search-group">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchSubjects" placeholder="Search by subject code or name...">
                        </div>
                    </div>
                </div>
                
                <!-- Statistics -->
                <div class="statistics-section">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Total Subjects</div>
                            <div class="stat-value" id="totalSubjects">0</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon passed">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Passed</div>
                            <div class="stat-value" id="passedSubjects">0</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon failed">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Failed</div>
                            <div class="stat-value" id="failedSubjects">0</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon in-progress">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">In Progress</div>
                            <div class="stat-value" id="inProgressSubjects">0</div>
                        </div>
                    </div>
                </div>
                
                <!-- Subjects Table -->
                <div class="table-container">
                    <table id="subjectsHistoryTable">
                        <thead>
                            <tr>
                                <th>
                                    <i class="fas fa-sort"></i>
                                    Subject Code
                                </th>
                                <th>
                                    <i class="fas fa-sort"></i>
                                    Subject Name
                                </th>
                                <th>
                                    <i class="fas fa-sort"></i>
                                    Units
                                </th>
                                <th>
                                    <i class="fas fa-sort"></i>
                                    Year Level
                                </th>
                                <th>
                                    <i class="fas fa-sort"></i>
                                    Semester
                                </th>
                                <th>
                                    <i class="fas fa-sort"></i>
                                    Grade
                                </th>
                                <th>
                                    <i class="fas fa-sort"></i>
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody id="subjectsHistoryBody">
                            <!-- Will be populated dynamically -->
                        </tbody>
                    </table>
                </div>
                
                <!-- No results message -->
                <div id="noResults" class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>No subjects found</h3>
                    <p>Try adjusting your filters or search term</p>
                </div>
                
                <!-- Loading indicator -->
                <div id="loadingIndicator" class="loading-indicator">
                    <div class="spinner"></div>
                    <p>Loading subjects history...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Logout Form -->
    <form id="logout-form" action="{{ route('instructor.logout') }}" method="POST" style="display: none;">
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
    <script src="{{asset('js/function/instructor/dashboard.js')}}"></script>
    <script>
        // Pass Laravel routes to JavaScript
        window.laravelRoutes = {
            ungradedStudents: '{{ route("instructor.ungraded-students") }}',
            saveGrade: '{{ route("instructor.save-grade") }}',

            getEnrollmentRequests: '{{ route("instructor.enrollment-requests") }}',
            approveEnrollment: '{{ route("instructor.approve-enrollment") }}',
            rejectEnrollment: '{{ route("instructor.reject-enrollment") }}'
        };
    </script>
    <script>
        console.log('Laravel Routes:', window.laravelRoutes);
        console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    </script>
</body>
</html>