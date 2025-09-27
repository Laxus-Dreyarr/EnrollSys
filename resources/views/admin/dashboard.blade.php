<?php

// get admin id from session
$user = $_SESSION['admin_id'];
$user_type = $_SESSION['user_type'];
// create admin object
$a =  new Admin($user, 0, 0, 0, 0, 0, 0, 0, 0, 0);
// get admin data
$admin_data = $a->fetch_admin_data();

$firstname = $admin_data[0]['firstname'];
$profile_picture = $admin_data[0]['profile'];


// commit 1
$b = new Admin($user, 0, 0, 0, 0, 0, 0, 0, 0, 0);
$stats = $b->get_statistics();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="application-title" content="EnrollSys">
    <meta name="color-scheme" content="#101126">
    <meta name="theme-color" content="#101126">
    <title>Admin Dashboard - EnrollSys</title>
    <link rel="website icon" href="logo.png">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="style/bootstrap5.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="stylesheet" href="style/google-fonts.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js"></script>
</head>
<body class="light-theme">
    <div class="dashboard-container">
        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="d-flex align-items-center">
                    <i id="graduateLogo" class="fas fa-graduation-cap fs-4 me-2 text-primary"></i>
                    <span class="brand-text fs-5 fw-bold">EnrollSys</span>
                </div>
            </div>
            <div class="sidebar-menu">
                <ul>
                    <li><a href="#dashboard" class="active" data-bs-toggle="tab"><i class="fas fa-home"></i> <span class="menu-text">Dashboard</span></a></li>
                    <li><a href="#subjects" data-bs-toggle="tab"><i class="fas fa-book"></i> <span class="menu-text">Subjects</span></a></li>
                    <li><a href="#students" data-bs-toggle="tab"><i class="fas fa-users"></i> <span class="menu-text">Students</span></a></li>
                    <li><a href="#instructors" data-bs-toggle="tab"><i class="fas fa-chalkboard-teacher"></i> <span class="menu-text">Instructors</span></a></li>
                    <li><a href="#organizations" data-bs-toggle="tab"><i class="fas fa-sitemap"></i> <span class="menu-text">Organizations</span></a></li>
                    <li><a href="#audit" data-bs-toggle="tab"><i class="fas fa-history"></i> <span class="menu-text">Audit Logs</span></a></li>
                    <li><a href="#files" data-bs-toggle="tab"><i class="fas fa-file-alt"></i> <span class="menu-text">Files</span></a></li>
                    <li><a href="#"><i class="fas fa-cog"></i> <span class="menu-text">Settings</span></a></li>
                    <li><a href="#"><i class="fas fa-sign-out-alt"></i> <span class="menu-text">Logout</span></a></li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <div class="topbar">
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-primary me-2 mobile-menu-btn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h5 class="mb-0 d-none d-md-block">Admin Dashboard</h5>
                </div>
                <div class="topbar-right">
                    <button id="themeToggle" class="theme-toggle-btn">
                        <i class="fas fa-moon"></i>
                    </button>
                    <button class="btn btn-outline-primary me-2 d-none d-md-inline-block" data-bs-toggle="modal" data-bs-target="#passkeyModal">
                        <i class="fas fa-key me-1"></i> Generate Passkey
                    </button>
                    <div class="user-profile">
                        <div class="user-avatar">
                            <img src="profile/<?=$profile_picture?>" alt="user_avatar" class="rounded-circle">
                        </div>
                        <div class="user-info d-none d-md-block">
                            <div class="user-name"><?=$firstname?></div>
                            <div class="user-role">Administrator</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content">
                <div class="page-header">
                    <h2 class="page-title">Dashboard Overview</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </nav>
                </div>

                <!-- Stats Cards -->
                <div class="row" id="statsCards">
                    <div class="col-md-3 col-6">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Students</h5>
                                        <h2 class="mb-0"><?php echo $stats['students']; ?></h2>
                                    </div>
                                    <div class="bg-primary p-3 rounded">
                                        <i class="fas fa-users fa-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="text-success"><i class="fas fa-arrow-up"></i> 5.2%</span>
                                    <span class="text-muted ms-2">Since last month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Instructors</h5>
                                        <h2 class="mb-0"><?php echo $stats['instructors']; ?></h2>
                                    </div>
                                    <div class="bg-success p-3 rounded">
                                        <i class="fas fa-chalkboard-teacher fa-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="text-success"><i class="fas fa-arrow-up"></i> 2.1%</span>
                                    <span class="text-muted ms-2">Since last month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div id="subjects_stats">
                                        <h5 class="card-title">Subjects</h5>
                                        <h2 class="mb-0"></h2>
                                    </div>
                                    <div class="bg-warning p-3 rounded">
                                        <i class="fas fa-book fa-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="text-success"><i class="fas fa-arrow-up"></i> 3.7%</span>
                                    <span class="text-muted ms-2">Since last month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                   <!-- <div class="col-md-3 col-6">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Notification</h5>
                                        <h2 class="mb-0" id="notificationCount">0</h2>
                                    </div>
                                    <div class="bg-info p-3 rounded">
                                        <i class="fas fa-bell fa-2x text-white" id="notificationCount"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="text-success"><i class="fas fa-arrow-up"></i> 8.3%</span>
                                    <span class="text-muted ms-2">Since last month</span>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <div class="col-md-3 col-6">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Enrollments</h5>
                                        <h2 class="mb-0"><?php echo $stats['enrollments']; ?></h2>
                                    </div>
                                    <div class="bg-info p-3 rounded">
                                        <i class="fas fa-file-alt fa-2x text-white"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="text-success"><i class="fas fa-arrow-up"></i> 8.3%</span>
                                    <span class="text-muted ms-2">Since last month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="tab-content mt-4">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Recent Activity</h5>
                            </div>
                            <div class="card-body">
                                <p>Welcome to the EnrollSys Admin Dashboard. Here you can manage students, subjects, instructors, and more.</p>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i> 5 pending enrollment requests to review.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subjects Tab -->
                    <div class="tab-pane fade" id="subjects">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h5 class="mb-2 mb-md-0">Manage Subjects</h5>
                                <div class="d-flex">
                                    <div class="search-container me-2">
                                        <i class="fas fa-search search-icon"></i>
                                        <input type="text" class="form-control search-input" placeholder="Search subjects..." id="subjectSearch">
                                    </div>
                                    <button id="plus_sign" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSubjectModal">
                                        <i class="fas fa-plus me-1"></i> <span class="d-none d-md-inline">Create Subject</span>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="subjectsTable">
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Subject Name</th>
                                                <th>Units</th>
                                                <th>Year/Semester</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="subjectsTableBody">
                                            <!-- Subjects will be loaded here via JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="Space">
                            <!-- Space -->
                        </div>
                    </div>


                    <!-- Students Tab -->
                    <div class="tab-pane fade" id="students">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h5 class="mb-2 mb-md-0">Manage Students</h5>
                                <div class="search-container">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" class="form-control search-input" placeholder="Search students..." id="studentSearch">
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="studentsTable">
                                        <thead>
                                            <tr>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>Program</th>
                                                <th>Year Level</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>2023-001</td>
                                                <td>Eva Elfie</td>
                                                <td>BS Information Technology</td>
                                                <td>3rd Year</td>
                                                <td><span class="badge badge-success">Active</span></td>
                                                <td id="_student_btn">
                                                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                                                    <button class="btn btn-sm btn-outline-warning ms-1"><i class="fas fa-edit"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger ms-1"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>2023-002</td>
                                                <td>Lexi Lore</td>
                                                <td>BS Information Technology</td>
                                                <td>2nd Year</td>
                                                <td><span class="badge badge-success">Active</span></td>
                                                <td id="_student_btn">
                                                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                                                    <button class="btn btn-sm btn-outline-warning ms-1"><i class="fas fa-edit"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger ms-1"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>2023-003</td>
                                                <td>Abella Danger</td>
                                                <td>BS Information Technology</td>
                                                <td>4th Year</td>
                                                <td><span class="badge badge-warning">Probation</span></td>
                                                <td id="_student_btn">
                                                    <button class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></button>
                                                    <button class="btn btn-sm btn-outline-warning ms-1"><i class="fas fa-edit"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger ms-1"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="Space">
                            <!-- Space -->
                        </div>
                    </div>

                    <!-- Instructors Tab -->
                    <div class="tab-pane fade" id="instructors">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h5 class="mb-2 mb-md-0">Manage Instructors</h5>
                                <div class="search-container">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" class="form-control search-input" placeholder="Search instructors..." id="instructorSearch">
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-center">Instructors management content will be displayed here.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Organizations Tab -->
                    <div class="tab-pane fade" id="organizations">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h5 class="mb-2 mb-md-0">Manage Organizations</h5>
                                <div class="search-container">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" class="form-control search-input" placeholder="Search organizations..." id="organizationSearch">
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-center">Organizations management content will be displayed here.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Audit Logs Tab -->
                    <div class="tab-pane fade" id="audit">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h5 class="mb-2 mb-md-0">Audit Logs</h5>
                                <div class="d-flex">
                                    <div class="search-container me-2">
                                        <i class="fas fa-search search-icon"></i>
                                        <input type="text" class="form-control search-input" placeholder="Search logs..." id="auditSearch">
                                    </div>
                                    <button class="btn btn-danger" id="clearAllLogsBtn">
                                        <i class="fas fa-trash me-1"></i> <span class="d-none d-md-inline">Clear All Logs</span>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="auditTable">
                                        <thead>
                                            <tr>
                                                <th>Timestamp</th>
                                                <th>Action</th>
                                                <th>User</th>
                                                <th>Details</th>
                                                <th>IP Address</th>
                                            </tr>
                                        </thead>
                                        <tbody id="auditTableBody">
                                            <!-- Audit logs will be loaded here via JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Files Tab -->
                    <div class="tab-pane fade" id="files">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h5 class="mb-2 mb-md-0">File Manager</h5>
                                <div class="d-flex">
                                    <div class="search-container me-2">
                                        <i class="fas fa-search search-icon"></i>
                                        <input type="text" class="form-control search-input" placeholder="Search files..." id="fileSearch">
                                    </div>
                                    <button id="uploadFile" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                                        <i class="fas fa-upload me-1"></i> <span class="d-none d-md-inline">Upload File</span>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="file-list" id="fileList">
                                    <div class="file-item">
                                        <div class="file-icon">
                                            <i class="fas fa-file-pdf"></i>
                                        </div>
                                        <div class="file-info">
                                            <div class="file-name">Academic Calendar 2023-2024.pdf</div>
                                            <div class="file-meta">2.4 MB • PDF • Uploaded: 2023-11-10</div>
                                        </div>
                                        <div class="file-actions">
                                            <button class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i></button>
                                            <button class="btn btn-sm btn-outline-danger ms-1"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                    <div class="file-item">
                                        <div class="file-icon">
                                            <i class="fas fa-file-excel"></i>
                                        </div>
                                        <div class="file-info">
                                            <div class="file-name">Student List.xlsx</div>
                                            <div class="file-meta">1.8 MB • Excel • Uploaded: 2023-11-08</div>
                                        </div>
                                        <div class="file-actions">
                                            <button class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i></button>
                                            <button class="btn btn-sm btn-outline-danger ms-1"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                    <div class="file-item">
                                        <div class="file-icon">
                                            <i class="fas fa-file-word"></i>
                                        </div>
                                        <div class="file-info">
                                            <div class="file-name">Enrollment Guidelines.docx</div>
                                            <div class="file-meta">850 KB • Word • Uploaded: 2023-11-05</div>
                                        </div>
                                        <div class="file-actions">
                                            <button class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i></button>
                                            <button class="btn btn-sm btn-outline-danger ms-1"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Subject Modal -->
    <div class="modal fade" id="createSubjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createSubjectForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="subjectCode" class="form-label">Subject Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="subjectCode" name="subjectCode" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="subjectName" class="form-label">Subject Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="subjectName" name="subjectName" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="units" class="form-label">Units <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="units" name="units" min="1" max="5" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="maxStudents" class="form-label">Max Students <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="maxStudents" name="maxStudents" min="1" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="yearLevel" class="form-label">Year Level <span class="text-danger">*</span></label>
                                <select class="form-select" id="yearLevel" name="yearLevel" required>
                                    <option value="">Select Year Level</option>
                                    <option value="1st Year">1st Year</option>
                                    <option value="2nd Year">2nd Year</option>
                                    <option value="3rd Year">3rd Year</option>
                                    <option value="4th Year">4th Year</option>
                                    <option value="5th Year">5th Year</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="semester" class="form-label">Semester <span class="text-danger">*</span></label>
                                <select class="form-select" id="semester" name="semester" required>
                                    <option value="">Select Semester</option>
                                    <option value="1st Sem">1st Semester</option>
                                    <option value="2nd Sem">2nd Semester</option>
                                    <option value="Summer">Summer</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Subject Type <span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="lectureCheck" name="subjectType[]" value="Lecture">
                                    <label class="form-check-label" for="lectureCheck">Lecture</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="laboratoryCheck" name="subjectType[]" value="Laboratory">
                                    <label class="form-check-label" for="laboratoryCheck">Laboratory</label>
                                </div>
                            </div>
                        </div>
                        <!-- Prerequisites Section -->
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="prerequisites" class="form-label">Prerequisites</label>
                                <div class="input-group">
                                    <select class="form-select" id="prerequisitesDropdown">
                                        <option value="">Select prerequisite subject</option>
                                        <!-- Will be populated via AJAX -->
                                    </select>
                                    <button class="btn btn-outline-primary" type="button" id="addPrerequisiteBtn">Add</button>
                                </div>
                                <div class="prerequisite-tags mt-2" id="prerequisiteTags">
                                    <!-- Prerequisite tags will be added here -->
                                </div>
                            </div>
                        </div>
                        <!-- Schedule Section -->
                        <div class="mb-3">
                            <label class="form-label">Schedule <span class="text-danger">*</span></label>
                            <div class="schedule-container">
                                <div class="row mb-2">
                                    <div class="col-md-3">
                                        <select class="form-select day-select" id="scheduleDay">
                                            <option value="">Select Day</option>
                                            <option value="Monday">Monday</option>
                                            <option value="Tuesday">Tuesday</option>
                                            <option value="Wednesday">Wednesday</option>
                                            <option value="Thursday">Thursday</option>
                                            <option value="Friday">Friday</option>
                                            <option value="Saturday">Saturday</option>
                                            <option value="Sunday">Sunday</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="time" class="form-control start-time" id="startTime">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="time" class="form-control end-time" id="endTime">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control room-input" id="room" placeholder="Room">
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select schedule-type-select" id="scheduleType">
                                            <option value="">Type</option>
                                            <option value="Lecture">Lecture</option>
                                            <option value="Laboratory">Laboratory</option>
                                        </select>
                                    </div>
                                    <!-- try -->
                                     <div class="col-md-2">
                                        <select class="form-select schedule-type-select" id="sectionType">
                                            <option value="">Section</option>
                                            <option value="A">A</option>
                                            <option value="b">b</option>
                                            <option value="C">C</option>
                                            <option value="D">D</option>
                                        </select>
                                    </div>
                                     <!-- e try -->
                                    <div class="col-md-1" id="col-md-1">
                                        <button type="button" class="btn btn-sm btn-success add-schedule"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                                <div class="schedule-list" id="scheduleList">
                                    <!-- Schedule items will be added here dynamically -->
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="createSubjectBtn">Create Subject</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Subject Modal -->
    <div class="modal fade" id="editSubjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editSubjectForm">
                        <input type="hidden" id="editSubjectId" name="subjectId">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editSubjectCode" class="form-label">Subject Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editSubjectCode" name="subjectCode" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editSubjectName" class="form-label">Subject Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editSubjectName" name="subjectName" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="editDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="editDescription" name="description" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="editUnits" class="form-label">Units <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="editUnits" name="units" min="1" max="5" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="editMaxStudents" class="form-label">Max Students <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="editMaxStudents" name="maxStudents" min="1" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="editYearLevel" class="form-label">Year Level <span class="text-danger">*</span></label>
                                <select class="form-select" id="editYearLevel" name="yearLevel" required>
                                    <option value="">Select Year Level</option>
                                    <option value="1st Year">1st Year</option>
                                    <option value="2nd Year">2nd Year</option>
                                    <option value="3rd Year">3rd Year</option>
                                    <option value="4th Year">4th Year</option>
                                    <option value="5th Year">5th Year</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="editSemester" class="form-label">Semester <span class="text-danger">*</span></label>
                                <select class="form-select" id="editSemester" name="semester" required>
                                    <option value="">Select Semester</option>
                                    <option value="1st Sem">1st Semester</option>
                                    <option value="2nd Sem">2nd Semester</option>
                                    <option value="Summer">Summer</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Subject Type <span class="text-danger">*</span></label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="editLectureCheck" name="subjectType[]" value="Lecture">
                                    <label class="form-check-label" for="editLectureCheck">Lecture</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="editLaboratoryCheck" name="subjectType[]" value="Laboratory">
                                    <label class="form-check-label" for editLaboratoryCheck">Laboratory</label>
                                </div>
                            </div>
                        </div>
                        <!-- Prerequisites Section -->
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="editPrerequisites" class="form-label">Prerequisites</label>
                                <div class="input-group">
                                    <select class="form-select" id="editPrerequisitesDropdown">
                                        <option value="">Select prerequisite subject</option>
                                        <!-- Will be populated via AJAX -->
                                    </select>
                                    <button class="btn btn-outline-primary" type="button" id="editAddPrerequisiteBtn">Add</button>
                                </div>
                                <div class="prerequisite-tags mt-2" id="editPrerequisiteTags">
                                    <!-- Prerequisite tags will be added here -->
                                </div>
                            </div>
                        </div>
                        <!-- Schedule Section -->
                        <div class="mb-3">
                            <label class="form-label">Schedule <span class="text-danger">*</span></label>
                            <div class="schedule-container">
                                <div class="row mb-2">
                                    <div class="col-md-3">
                                        <select class="form-select day-select" id="editScheduleDay">
                                            <option value="">Select Day</option>
                                            <option value="Monday">Monday</option>
                                            <option value="Tuesday">Tuesday</option>
                                            <option value="Wednesday">Wednesday</option>
                                            <option value="Thursday">Thursday</option>
                                            <option value="Friday">Friday</option>
                                            <option value="Saturday">Saturday</option>
                                            <option value="Sunday">Sunday</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="time" class="form-control start-time" id="editStartTime">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="time" class="form-control end-time" id="editEndTime">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" class="form-control room-input" id="editRoom" placeholder="Room">
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select schedule-type-select" id="editScheduleType">
                                            <option value="">Type</option>
                                            <option value="Lecture">Lecture</option>
                                            <option value="Laboratory">Laboratory</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-select schedule-type-select" id="editSectionType">
                                            <option value="">Section</option>
                                            <option value="A">A</option>
                                            <option value="B">B</option>
                                            <option value="C">C</option>
                                            <option value="D">D</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-sm btn-success add-schedule" id="editAddScheduleBtn">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="schedule-list" id="editScheduleList">
                                    <!-- Schedule items will be added here dynamically -->
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updateSubjectBtn">Update Subject</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Subject Modal -->
    <div class="modal fade" id="viewSubjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Subject Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="subject-detail-row">
                        <div class="row">
                            <div class="col-md-6">
                                <span class="subject-detail-label">Subject Code:</span>
                                <span id="viewSubjectCode"></span>
                            </div>
                            <div class="col-md-6">
                                <span class="subject-detail-label">Subject Name:</span>
                                <span id="viewSubjectName"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="subject-detail-row">
                        <div class="row">
                            <div class="col-md-6">
                                <span class="subject-detail-label">Units:</span>
                                <span id="viewSubjectUnits"></span>
                            </div>
                            <div class="col-md-6">
                                <span class="subject-detail-label">Year Level:</span>
                                <span id="viewSubjectYearLevel"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="subject-detail-row">
                        <div class="row">
                            <div class="col-md-6">
                                <span class="subject-detail-label">Semester:</span>
                                <span id="viewSubjectSemester"></span>
                            </div>
                            <div class="col-md-6">
                                <span class="subject-detail-label">Max Students:</span>
                                <span id="viewSubjectMaxStudents"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="subject-detail-row">
                        <div class="row">
                            <div class="col-12">
                                <span class="subject-detail-label">Description:</span>
                                <p id="viewSubjectDescription" class="mt-2"></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="subject-detail-row">
                        <div class="row">
                            <div class="col-12">
                                <span class="subject-detail-label">Prerequisites:</span>
                                <div id="viewSubjectPrerequisites" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="subject-detail-row">
                        <div class="row">
                            <div class="col-12">
                                <span class="subject-detail-label">Schedule:</span>
                                <div class="table-responsive mt-2">
                                    <table class="schedule-table">
                                        <thead>
                                            <tr>
                                                <th>Section</th>
                                                <th>Type</th>
                                                <th>Day</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <th>Room</th>
                                            </tr>
                                        </thead>
                                        <tbody id="viewSubjectSchedules">
                                            <!-- Schedules will be populated here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Passkey Generator Modal -->
    <div class="modal fade" id="passkeyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Passkey</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="emailAddress" class="form-label">Recipient Email</label>
                        <input type="email" class="form-control" id="emailAddress" placeholder="name@evsu.edu.ph" required>
                        <div class="invalid-feedback">Please enter a valid EVSUmail address</div>
                    </div>
                    <div class="mb-3">
                        <label for="userTypeSelect" class="form-label">User Type</label>
                        <select class="form-select" id="userTypeSelect" required>
                            <option value="">Select user type</option>
                            <option value="instructor">Instructor</option>
                            <option value="organization">Organization</option>
                        </select>
                    </div>
                    <div class="passkey-display" id="passkeyDisplay">
                        Click Generate to create a passkey
                    </div>
                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-primary" id="generatePasskeyBtn">
                            <i class="fas fa-key me-1"></i> Generate Passkey
                        </button>
                        <button class="btn btn-success" id="sendPasskeyBtn" disabled>
                            <i class="fas fa-paper-plane me-1"></i> Send Passkey
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload File Modal -->
    <div class="modal fade" id="uploadFileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadFileForm">
                        <div class="mb-3">
                            <label for="fileUpload" class="form-label">Select File</label>
                            <input class="form-control" type="file" id="fileUpload" required>
                        </div>
                        <div class="mb-3">
                            <label for="fileName" class="form-label">File Name</label>
                            <input type="text" class="form-control" id="fileName" required>
                        </div>
                        <div class="mb-3">
                            <label for="fileDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="fileDescription" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Upload File</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{asset('js/jquery.js')}}"></script>
    <script src="{{asset('js/function/admin_dashboard/dashboard.js')}}"></script>
</body>
</html>