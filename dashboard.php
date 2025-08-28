<?php
session_start();
// include database and admin class
include('Class/Db.php');
include('Class/Admin.php');
// if not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index-admin.php');
    exit();
}

// get admin id from session
$user = $_SESSION['admin_id'];
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
                                                <th>Section</th>
                                                <th>Units</th>
                                                <th>Year/Semester</th>
                                                <th>Schedule</th>
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
                                                <td>
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
                                                <td>
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
                                                <td>
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
                                    <label class="form-check-label" for="editLaboratoryCheck">Laboratory</label>
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
    <script src="javaScript/jquery.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        //Disable right-click context menu
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });
    //Disable F12 and Ctrl+Shift+I
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
            e.preventDefault();
        }
    });
    //Disable Ctrl+U
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'u') {
            e.preventDefault();
        }
    });
    //Disable Ctrl+S
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
        }
    });
    //Disable Ctrl+P
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
        }
    });
    // Disable text selection
    document.addEventListener('selectstart', function(e) {
        e.preventDefault();
    });
    // Disable drag and drop
    document.addEventListener('dragstart', function(e) {
        e.preventDefault();
    });
    // Disable copy
    document.addEventListener('copy', function(e) {
        e.preventDefault();
    });
    // Disable paste
    document.addEventListener('paste', function(e) {
        e.preventDefault();
    });
    // Disable cut
    document.addEventListener('cut', function(e) {
        e.preventDefault();
    });
    // Disable right-click on images
    const images = document.querySelectorAll('img');
    images.forEach(function(image) {
        image.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
    });
    // Disable right-click on links
    const links = document.querySelectorAll('a');
    links.forEach(function(link) {
        link.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
    });
    // Disable right-click on buttons
    const buttons = document.querySelectorAll('button');
    buttons.forEach(function(button) {
        button.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
    });
    // Disable right-click on input fields
    const inputs = document.querySelectorAll('input, textarea');
    inputs.forEach(function(input) {
        input.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
    });
    // Disable right-click on the body
    document.body.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });
    // Disable right-click on the document
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });
    // Disable right-click on the window
    window.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });
    // Disable right-click on the document element
    document.documentElement.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });
        // Theme Toggle
        const themeToggleBtn = document.getElementById('themeToggle');
        const body = document.body;
        
        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme') || 
                          (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        
        // Apply the saved theme
        if (savedTheme === 'dark') {
            body.classList.add('dark-theme');
            themeToggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
        }
        
        // Theme toggle button click event
        themeToggleBtn.addEventListener('click', function() {
            body.classList.toggle('dark-theme');
            
            if (body.classList.contains('dark-theme')) {
                localStorage.setItem('theme', 'dark');
                themeToggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                localStorage.setItem('theme', 'light');
                themeToggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
            }
        });
        
        // Passkey Generator
        const generatePasskeyBtn = document.getElementById('generatePasskeyBtn');
        const sendPasskeyBtn = document.getElementById('sendPasskeyBtn');
        const passkeyDisplay = document.getElementById('passkeyDisplay');
        const emailInput = document.getElementById('emailAddress');
        
        generatePasskeyBtn.addEventListener('click', function() {
            // Generate a random passkey
            const passkey = generatePasskey(15);
            passkeyDisplay.textContent = passkey;
            passkeyDisplay.classList.add('text-primary', 'fw-bold');
            sendPasskeyBtn.disabled = false;
            
            // Add copy functionality
            passkeyDisplay.onclick = function() {
                navigator.clipboard.writeText(passkey);
                const originalText = passkeyDisplay.textContent;
                passkeyDisplay.textContent = 'Copied to clipboard!';
                setTimeout(() => {
                    passkeyDisplay.textContent = originalText;
                }, 2000);
            };
        });
        
        sendPasskeyBtn.addEventListener('click', function() {
            if (!emailInput.value.endsWith('@evsu.edu.ph')) {
                emailInput.classList.add('is-invalid');
                return;
            }
            
            emailInput.classList.remove('is-invalid');
            
            // Simulate sending email
            alert(`Passkey sent to ${emailInput.value}`);
            $('#passkeyModal').modal('hide');
        });
        
        // Email validation
        emailInput.addEventListener('input', function() {
            if (!this.value.endsWith('@evsu.edu.ph')) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
        
        // Schedule adding functionality with type selection
        $('.add-schedule').click(function() {
            const day = $('#scheduleDay').val();
            const start = $('#startTime').val();
            const end = $('#endTime').val();
            const room = $('#room').val() || '';
            const scheduleType = $('#scheduleType').val();
            // try
            const sectionType = $('#sectionType').val();
            
            if (!day || !start || !end || !scheduleType || !sectionType) {
                // alert('Please fill all schedule fields');
                return;
            }
            
            // Check if end time is after start time
            if (start >= end) {
                alert('End time must be after start time');
                return;
            }
            
            const scheduleItem = `
                <div class="schedule-item mb-2 p-2 border rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-bold">${day} (${scheduleType})</span>: ${start} - ${end} ${room ? '(Room ' + room + ')' : ''} Section: ${sectionType}
                        </div>
                        <button type="button" class="btn btn-sm btn-danger remove-schedule">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <input type="hidden" name="schedules[]" value='${JSON.stringify({day, start_time: start, end_time: end, room, type: scheduleType, section: sectionType})}'>
                </div>
            `;
            
            $('#scheduleList').append(scheduleItem);
            
            // Clear inputs
            $('#scheduleDay').val('');
            $('#startTime').val('');
            $('#endTime').val('');
            $('#room').val('');
            $('#scheduleType').val('');
            $('#sectionType').val('');
        });
        
        // Remove schedule item
        $(document).on('click', '.remove-schedule', function() {
            $(this).closest('.schedule-item').remove();
        });
        
        // Prerequisite functionality
        const addPrerequisiteBtn = document.getElementById('addPrerequisiteBtn');
        const prerequisiteDropdown = document.getElementById('prerequisitesDropdown');
        
        addPrerequisiteBtn.addEventListener('click', function() {
            const subjectId = prerequisiteDropdown.value;
            const subjectCode = prerequisiteDropdown.options[prerequisiteDropdown.selectedIndex].text;
            
            if (subjectId) {
                const tag = document.createElement('div');
                tag.className = 'prerequisite-tag badge bg-primary me-1 mb-1';
                tag.innerHTML = `
                    ${subjectCode}
                    <span class="remove-tag ms-1" data-subject="${subjectId}" style="cursor: pointer;">&times;</span>
                    <input type="hidden" name="prerequisites[]" value="${subjectId}">
                `;
                prerequisiteTags.appendChild(tag);
                prerequisiteDropdown.value = '';
                
                // Add event to remove tag
                tag.querySelector('.remove-tag').addEventListener('click', function() {
                    tag.remove();
                });
            }
        });
        
        // Clear all logs button
        document.getElementById('clearAllLogsBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to clear all audit logs? This action cannot be undone.')) {
                alert('All audit logs have been cleared');
            }
        });
        
        // Mobile menu toggle
        const sidebar = document.querySelector('.sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        
        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });
        
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });
        
        // Tab navigation
        const tabLinks = document.querySelectorAll('.sidebar-menu a[data-bs-toggle="tab"]');
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                // Remove active class from all links
                tabLinks.forEach(l => l.classList.remove('active'));
                // Add active class to clicked link
                this.classList.add('active');
                
                // Show the corresponding tab
                const target = this.getAttribute('href');
                const tabPanes = document.querySelectorAll('.tab-pane');
                tabPanes.forEach(pane => pane.classList.remove('show', 'active'));
                document.querySelector(target).classList.add('show', 'active');
                
                // Close sidebar on mobile after clicking a menu item
                if (window.innerWidth <= 768 ) {
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });
        
        // Search functionality
        // Subjects search
        $('#subjectSearch').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('#subjectsTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        
        // Students search
        $('#auditSearch').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('#auditTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        
        // Audit logs search
        $('#auditSearch').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('#auditTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        
        // Files search
        $('#fileSearch').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('.file-item').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
        
        // Load statistics and prerequisites on page load
        loadStatistics();
        loadPrerequisiteOptions();
        
        // Initialize the create subject form submission
        $('#createSubjectBtn').click(function() {
            createSubject();
        });
        
        // Load prerequisites when create modal is shown
        $('#createSubjectModal').on('show.bs.modal', function() {
            loadPrerequisiteOptions();
        });

        // Load prerequisites when edit modal is shown
        $('#editSubjectModal').on('show.bs.modal', function() {
            loadEditPrerequisiteOptions();
        });

        // Function to load edit prerequisites
        function loadEditPrerequisiteOptions() {
            $.post('exe/admin_ajax.php', {action: 'get_prerequisites'}, function(response) {
                if (response.success) {
                    const dropdown = $('#editPrerequisitesDropdown');
                    dropdown.empty().append('<option value="">Select prerequisite subject</option>');
                    
                    response.prerequisites.forEach(function(prereq) {
                        dropdown.append($('<option>', {
                            value: prereq.id,
                            text: prereq.code + ' - ' + prereq.name
                        }));
                    });
                }
            }, 'json').fail(function(xhr, status, error) {
                console.error('Error loading prerequisites:', error);
            });
        }

        // Load subjects when the page is ready
        loadStatistics();
        // loadSubjects2();
        loadSubjects();

        // Load audit logs when the audit tab is shown
        $('a[href="#audit"]').on('shown.bs.tab', function(e) {
            loadAuditLogs();
        });
    });
    
    // Function to generate a random passkey
    function generatePasskey(length) {
        const charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        let passkey = "";
        
        for (let i = 0; i < length; i++) {
            const randomIndex = Math.floor(Math.random() * charset.length);
            passkey += charset[randomIndex];
        }
        
        return passkey;
    }

    function loadStatistics() {
        $.post('exe/admin_ajax.php', {action: 'get_stats'}, function(response) {
            if (response.success) {
                $('#statsCards .col-md-3:eq(0) h2').text(response.stats.students);
                $('#statsCards .col-md-3:eq(1) h2').text(response.stats.instructors);
                $('#statsCards .col-md-3:eq(2) h2').text(response.stats.subjects);
                $('#statsCards .col-md-3:eq(3) h2').text(response.stats.enrollments);
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error loading statistics:', error);
        });
    }

    function loadPrerequisiteOptions() {
        $.post('exe/admin_ajax.php', {action: 'get_prerequisites'}, function(response) {
            if (response.success) {
                const dropdown = $('#prerequisitesDropdown');
                dropdown.empty().append('<option value="">Select prerequisite subject</option>');
                
                response.prerequisites.forEach(function(prereq) {
                    dropdown.append($('<option>', {
                        value: prereq.id,
                        text: prereq.code + ' - ' + prereq.name
                    }));
                });
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error loading prerequisites:', error);
        });
    }

    function createSubject() {
        // Validate subject type selection
        const subjectTypes = $('input[name="subjectType[]"]:checked').map(function() {
            return this.value;
        }).get();
        
        if (subjectTypes.length === 0) {
            alert('Please select at least one subject type (Lecture or Laboratory)');
            return;
        }
        
        // Validate schedules
        const schedules = $('input[name="schedules[]"]').map(function() {
            return JSON.parse(this.value);
        }).get();
        
        if (schedules.length === 0) {
            alert('Please add at least one schedule');
            return;
        }
        
        // Validate that all schedules have a type that matches selected subject types
        const scheduleTypes = [...new Set(schedules.map(s => s.type))];
        const hasMismatch = scheduleTypes.some(type => !subjectTypes.includes(type));
        
        if (hasMismatch) {
            alert('All schedule types must match the selected subject types');
            return;
        }
        
        // Collect form data
        const formData = {
            action: 'create_subject',
            code: $('#subjectCode').val(),
            name: $('#subjectName').val(),
            description: $('#description').val(),
            units: $('#units').val(),
            max_students: $('#maxStudents').val(),
            year_level: $('#yearLevel').val(),
            semester: $('#semester').val(),
            types: subjectTypes,
            prerequisites: $('input[name="prerequisites[]"]').map(function() {
                return this.value;
            }).get(),
            schedules: schedules
        };
        
        // Validate required fields
        if (!formData.code || !formData.name || !formData.units || 
            !formData.max_students || !formData.year_level || !formData.semester) {
            alert('Please fill all required fields');
            return;
        }
        
        console.log('Sending data:', formData);
        
        // Send AJAX request
        $.post('exe/admin_ajax.php', formData, function(response) {
            if (response.success) {
                alert('Subject created successfully!');
                $('#createSubjectModal').modal('hide');
                // Reset form
                $('#createSubjectForm')[0].reset();
                $('#prerequisiteTags').empty();
                $('#scheduleList').empty();
                $('input[name="subjectType[]"]').prop('checked', false);
                
                // Reload the subjects instead of the whole page
                loadStatistics();
                // loadSubjects2();
                loadSubjects();
            } else if (response == 1){
                alert("Section Already exist!")
            } else {
                alert('Error: ' + response.message);
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error creating subject:', error);
            alert('Failed to create subject. Please check console for details.');
        });
    }

    // Handle passkey generation
    $('#generatePasskeyBtn').click(function() {
        const email = $('#emailAddress').val();
        const userType = $('#userTypeSelect').val();
        
        if (!email || !userType) {
            alert('Please fill all fields');
            return;
        }
        
        if (!email.endsWith('@evsu.edu.ph')) {
            $('#emailAddress').addClass('is-invalid');
            return;
        }
        
        $.post('exe/admin_ajax.php', {
            action: 'generate_passkey', 
            email: email, 
            user_type: userType
        }, function(response) {
            if (response.success) {
                alert('Passkey generated and sent to ' + email);
                $('#passkeyModal').modal('hide');
                // Reset form
                $('#emailAddress').val('');
                $('#userTypeSelect').val('');
            } else {
                alert('Error: ' + response.message);
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error generating passkey:', error);
            alert('Failed to generate passkey. Please check console for details.');
        });
    });

    
    
    function loadSubjects() {
        $.post('exe/admin_ajax.php', {action: 'get_subjects'}, function(response) {
            if (response.success) {
                const tbody = $('#subjectsTableBody');
                tbody.empty();
                
                response.subjects.forEach(function(subject) {
                    // Format the schedules
                    let scheduleHtml = '';
                    if (subject.schedules && subject.schedules.length > 0) {
                        subject.schedules.forEach(function(schedule) {
                            const startTime = formatTime(schedule.start_time);
                            const endTime = formatTime(schedule.end_time);
                            scheduleHtml += `${schedule.section_name}: ${schedule.day} ${startTime}-${endTime} ${schedule.room ? '(Room ' + schedule.room + ')' : ''}<br>`;
                        });
                    } else {
                        scheduleHtml = 'No schedule';
                    }
                    
                    // Get unique sections
                    const sections = subject.schedules && subject.schedules.length > 0 
                        ? [...new Set(subject.schedules.map(s => s.section_name))].join(', ') 
                        : 'N/A';
                    
                    const row = `
                        <tr>
                            <td>${subject.code}</td>
                            <td>${subject.name}</td>
                            <td>${sections}</td>
                            <td>${subject.units}</td>
                            <td>${subject.year_level} / ${subject.semester}</td>
                            <td>${scheduleHtml}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="editSubject(${subject.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger ms-1" onclick="deleteSubject(${subject.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error loading subjects:', error);
        });
    }

    // The load subjects for the top dashboard
    function loadSubjects2() {
        $.post('exe/admin_ajax.php', {action: 'get_stats'}, function(response) {
            if (response.success) {
                const tbody2 = $('#subjects_stats');
                 const row2 = `
                        <h5 class="card-title">Subjects</h5>
                        <h2 class="mb-0">${stats.subjects}</h2>
                    `;
                tbody2.append(row2)
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error loading subjects:', error);
        });
    }

    function loadAuditLogs() {
        $.post('exe/admin_ajax.php', {action: 'get_audit_logs'}, function(response) {
            if (response.success) {
                const tbody = $('#auditTableBody');
                tbody.empty();
                
                response.logs.forEach(function(log) {
                    const timestamp = new Date(log.timestamp).toLocaleString();
                    const user = log.firstname && log.lastname ? 
                        `${log.firstname} ${log.lastname}` : 
                        (log.user_id ? `User ID: ${log.user_id}` : 'System');
                    
                    const row = `
                        <tr>
                            <td>${timestamp}</td>
                            <td>${log.action}</td>
                            <td>${user}</td>
                            <td>${log.details || 'N/A'}</td>
                            <td>${log.ip_address || 'N/A'}</td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error loading audit logs:', error);
        });
    }

    function formatTime(timeString) {
        if (!timeString) return '';
        
        const time = new Date(`1970-01-01T${timeString}`);
        return time.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
    }

    function deleteSubject(subjectId) {
        if (confirm('Are you sure you want to delete this subject? This action cannot be undone.')) {
            $.post('exe/admin_ajax.php', {
                action: 'delete_subject',
                subject_id: subjectId
            }, function(response) {
                if (response.success) {
                    alert('Subject deleted successfully');
                    loadStatistics();
                    // loadSubjects2();
                    loadSubjects(); // Reload the subjects
                } else {
                    alert('Error: ' + response.message);
                }
            }, 'json').fail(function(xhr, status, error) {
                console.error('Error deleting subject:', error);
                alert('Failed to delete subject. Please check console for details.');
            });
        }
    }


    function editSubject(subjectId) {
        $.post('exe/admin_ajax.php', {action: 'get_subject', subject_id: subjectId}, function(response) {
            if (response.success) {
                const subject = response.subject;
                
                // Populate the form fields
                $('#editSubjectId').val(subject.id);
                $('#editSubjectCode').val(subject.code);
                $('#editSubjectName').val(subject.name);
                $('#editDescription').val(subject.description || '');
                $('#editUnits').val(subject.units);
                $('#editMaxStudents').val(subject.max_students);
                $('#editYearLevel').val(subject.year_level);
                $('#editSemester').val(subject.semester);
                
                // Set subject types
                $('#editLectureCheck').prop('checked', false);
                $('#editLaboratoryCheck').prop('checked', false);
                
                // Check which types are present in the schedules
                const typesPresent = [...new Set(subject.schedules.map(s => s.Type))];
                if (typesPresent.includes('Lecture')) {
                    $('#editLectureCheck').prop('checked', true);
                }
                if (typesPresent.includes('Laboratory')) {
                    $('#editLaboratoryCheck').prop('checked', true);
                }
                
                // Populate prerequisites
                $('#editPrerequisiteTags').empty();
                if (subject.prerequisites && subject.prerequisites.length > 0) {
                    subject.prerequisites.forEach(prereq => {
                        addPrerequisiteTag(prereq.id, prereq.code, 'edit');
                    });
                }
                
                // Populate schedules
                $('#editScheduleList').empty();
                if (subject.schedules && subject.schedules.length > 0) {
                    subject.schedules.forEach(schedule => {
                        addScheduleItem(
                            schedule.day, 
                            schedule.start_time, 
                            schedule.end_time, 
                            schedule.room, 
                            schedule.Type,
                            schedule.Section,
                            'edit'
                        );
                    });
                }
                
                // Open the modal
                $('#editSubjectModal').modal('show');
            } else {
                alert('Error: ' + response.message);
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error loading subject:', error);
            alert('Failed to load subject data. Please check console for details.');
        });
    }

    function addPrerequisiteTag(subjectId, subjectCode, formType = 'create') {
        const tag = document.createElement('div');
        tag.className = 'prerequisite-tag badge bg-primary me-1 mb-1';
        tag.innerHTML = `
            ${subjectCode}
            <span class="remove-tag ms-1" data-subject="${subjectId}" style="cursor: pointer;">&times;</span>
            <input type="hidden" name="prerequisites[]" value="${subjectId}">
        `;
        
        const container = formType === 'create' ? 
            document.getElementById('prerequisiteTags') : 
            document.getElementById('editPrerequisiteTags');
        
        container.appendChild(tag);
        
        // Add event to remove tag
        tag.querySelector('.remove-tag').addEventListener('click', function() {
            tag.remove();
        });
    }

    function addScheduleItem(day, startTime, endTime, room, type, section, formType = 'create') {
        const scheduleItem = `
            <div class="schedule-item mb-2 p-2 border rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold">${day} (${type})</span>: ${startTime} - ${endTime} ${room ? '(Room ' + room + ')' : ''} Section: ${section}
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-schedule">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <input type="hidden" name="schedules[]" value='${JSON.stringify({day, start_time: startTime, end_time: endTime, room, type, section})}'>
            </div>
        `;
        
        const container = formType === 'create' ? 
            $('#scheduleList') : 
            $('#editScheduleList');
        
        container.append(scheduleItem);
    }

    function updateSubject() {
        // Validate subject type selection
        const subjectTypes = $('#editSubjectForm input[name="subjectType[]"]:checked').map(function() {
            return this.value;
        }).get();
        
        if (subjectTypes.length === 0) {
            alert('Please select at least one subject type (Lecture or Laboratory)');
            return;
        }
        
        // Validate schedules
        const schedules = $('#editSubjectForm input[name="schedules[]"]').map(function() {
            return JSON.parse(this.value);
        }).get();
        
        if (schedules.length === 0) {
            alert('Please add at least one schedule');
            return;
        }
        
        // Validate that all schedules have a type that matches selected subject types
        const scheduleTypes = [...new Set(schedules.map(s => s.type))];
        const hasMismatch = scheduleTypes.some(type => !subjectTypes.includes(type));
        
        if (hasMismatch) {
            alert('All schedule types must match the selected subject types');
            return;
        }
        
        // Collect form data
        const formData = {
            action: 'update_subject',
            subject_id: $('#editSubjectId').val(),
            code: $('#editSubjectCode').val(),
            name: $('#editSubjectName').val(),
            description: $('#editDescription').val(),
            units: $('#editUnits').val(),
            max_students: $('#editMaxStudents').val(),
            year_level: $('#editYearLevel').val(),
            semester: $('#editSemester').val(),
            types: subjectTypes,
            prerequisites: $('#editSubjectForm input[name="prerequisites[]"]').map(function() {
                return this.value;
            }).get(),
            schedules: schedules
        };
        
        // Validate required fields
        if (!formData.code || !formData.name || !formData.units || 
            !formData.max_students || !formData.year_level || !formData.semester) {
            alert('Please fill all required fields');
            return;
        }
        
        // Send AJAX request
        $.post('exe/admin_ajax.php', formData, function(response) {
            if (response.success) {
                alert('Subject updated successfully!');
                $('#editSubjectModal').modal('hide');
                // Reset form
                $('#editSubjectForm')[0].reset();
                $('#editPrerequisiteTags').empty();
                $('#editScheduleList').empty();
                $('#editSubjectForm input[name="subjectType[]"]').prop('checked', false);
                
                // Reload the subjects
                loadStatistics();
                // loadSubjects2();
                loadSubjects();
            } else {
                alert('Error: ' + response.message);
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Error updating subject:', error);
            alert('Failed to update subject. Please check console for details.');
        });
    }

    // Add event listeners for the edit modal
    $(document).ready(function() {
        // Edit prerequisite functionality
        $('#editAddPrerequisiteBtn').click(function() {
            const subjectId = $('#editPrerequisitesDropdown').val();
            const subjectCode = $('#editPrerequisitesDropdown option:selected').text();
            
            if (subjectId) {
                addPrerequisiteTag(subjectId, subjectCode, 'edit');
                $('#editPrerequisitesDropdown').val('');
            }
        });
        
        // Edit schedule functionality
        $('#editAddScheduleBtn').click(function() {
            const day = $('#editScheduleDay').val();
            const start = $('#editStartTime').val();
            const end = $('#editEndTime').val();
            const room = $('#editRoom').val() || '';
            const scheduleType = $('#editScheduleType').val();
            const sectionType = $('#editSectionType').val();
            
            if (!day || !start || !end || !scheduleType || !sectionType) {
                alert('Please fill all schedule fields');
                return;
            }
            
            // Check if end time is after start time
            if (start >= end) {
                alert('End time must be after start time');
                return;
            }
            
            addScheduleItem(day, start, end, room, scheduleType, sectionType, 'edit');
            
            // Clear inputs
            $('#editScheduleDay').val('');
            $('#editStartTime').val('');
            $('#editEndTime').val('');
            $('#editRoom').val('');
            $('#editScheduleType').val('');
            $('#editSectionType').val('');
        });
        
        // Update subject button
        $('#updateSubjectBtn').click(updateSubject);
        
        // Remove schedule item in edit modal
        $(document).on('click', '#editScheduleList .remove-schedule', function() {
            $(this).closest('.schedule-item').remove();
        });
    });

    // Clear form when create modal is hidden
    $('#createSubjectModal').on('hidden.bs.modal', function() {
        $('#createSubjectForm')[0].reset();
        $('#prerequisiteTags').empty();
        $('#scheduleList').empty();
        $('input[name="subjectType[]"]').prop('checked', false);
    });

    // Clear form when edit modal is hidden
    $('#editSubjectModal').on('hidden.bs.modal', function() {
        $('#editSubjectForm')[0].reset();
        $('#editPrerequisiteTags').empty();
        $('#editScheduleList').empty();
        $('#editSubjectForm input[name="subjectType[]"]').prop('checked', false);
    });
</script>
</body>
</html>