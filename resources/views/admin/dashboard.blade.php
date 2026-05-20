<?php
$firstname = $user->info->firstname;
$profile_picture = $user->profile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="application-title" content="EnrollSys">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="#101126">
    <meta name="theme-color" content="#101126">
    <title>Admin Dashboard - EnrollSys</title>
    <link rel="website icon" href="{{ asset('img/logo.png') }}">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('style/bootstrap5.min.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="stylesheet" href="{{ asset('style/google-fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sweetalert2.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js"></script>
    <style>
        /* Light Theme (Default) styles for Year Level Filters */
        .year-level-filter-container {
            background-color: rgba(0, 0, 0, 0.02);
            padding: 12px 18px;
            border-radius: 12px;
            border: 1px solid rgba(0, 0, 0, 0.06);
            margin-bottom: 20px;
        }
        .filter-title {
            color: #4a5568 !important; /* Deep slate gray for light mode */
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        .btn-pill {
            border-radius: 50px;
            padding: 6px 16px;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }
        .btn-year-filter {
            background-color: rgba(0, 0, 0, 0.04);
            color: #4a5568;
            border: 1px solid rgba(0, 0, 0, 0.08);
        }
        .btn-year-filter:hover {
            background-color: rgba(0, 0, 0, 0.08);
            color: #1a202c;
            border-color: rgba(0, 0, 0, 0.15);
        }
        .btn-year-filter.active {
            background-color: #0d6efd;
            color: #fff !important;
            border-color: #0d6efd;
            box-shadow: 0 2px 6px rgba(13, 110, 253, 0.3);
        }

        /* Dark Theme overrides for Year Level Filters */
        body.dark-theme .year-level-filter-container {
            background-color: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        body.dark-theme .filter-title {
            color: #0dcaf0 !important; /* Vibrant cyan for dark mode */
        }
        body.dark-theme .btn-year-filter {
            background-color: rgba(255, 255, 255, 0.05);
            color: #a0aec0;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        body.dark-theme .btn-year-filter:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff !important;
            border-color: rgba(255, 255, 255, 0.2);
        }
        body.dark-theme .btn-year-filter.active {
            background-color: #0d6efd;
            color: #fff !important;
            border-color: #0d6efd;
            box-shadow: 0 0 10px rgba(13, 110, 253, 0.4);
        }

        /* LEGIBILITY FIX: Muted 'Not Enrolled' status styling in Light/Dark themes */
        .badge-secondary {
            background-color: rgba(108, 117, 125, 0.15) !important;
            color: #495057 !important; /* Legible dark slate gray in light mode */
        }
        body.dark-theme .badge-secondary {
            background-color: rgba(255, 255, 255, 0.08) !important;
            color: #ced4da !important; /* Clear light gray in dark mode */
        }

        /* Audit Table Custom wrapping and responsiveness */
        #auditTable {
            table-layout: fixed;
            width: 100%;
        }
        #auditTable th:nth-child(1), #auditTable td:nth-child(1) { width: 15%; } /* Timestamp */
        #auditTable th:nth-child(2), #auditTable td:nth-child(2) { width: 22%; } /* Action */
        #auditTable th:nth-child(3), #auditTable td:nth-child(3) { width: 10%; } /* User */
        #auditTable th:nth-child(4), #auditTable td:nth-child(4) { width: 33%; } /* Details */
        #auditTable th:nth-child(5), #auditTable td:nth-child(5) { width: 12%; } /* IP Address */
        #auditTable th:nth-child(6), #auditTable td:nth-child(6) { width: 8%; text-align: center; } /* Actions */
        
        #auditTable td {
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal !important;
            vertical-align: middle;
        }

        /* Screen responsiveness for mobile layouts */
        @media (max-width: 992px) {
            #auditTable {
                table-layout: auto;
            }
            #auditTable th:nth-child(1), #auditTable td:nth-child(1),
            #auditTable th:nth-child(2), #auditTable td:nth-child(2),
            #auditTable th:nth-child(3), #auditTable td:nth-child(3),
            #auditTable th:nth-child(4), #auditTable td:nth-child(4),
            #auditTable th:nth-child(5), #auditTable td:nth-child(5),
            #auditTable th:nth-child(6), #auditTable td:nth-child(6) {
                width: auto;
            }
        }
    </style>
</head>
<body class="light-theme">
    <div class="dashboard-container">
        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="d-flex align-items-center">
                    <!-- <i id="graduateLogo" class="fas fa-graduation-cap fs-4 me-2 text-primary"></i> -->
                     <i class="logo">
                        <img src="{{ asset('img/evsu-logo.png') }}" alt="">
                    </i>
                    <span class="brand-text fs-5 fw-bold">EnrollSys</span>
                </div>
            </div>
            <div class="sidebar-menu">
                <ul>
                    <li><a href="#dashboard" class="active" data-bs-toggle="tab"><i class="fas fa-home"></i> <span class="menu-text">Dashboard</span></a></li>
                    <li><a href="#enrollment" data-bs-toggle="tab"><i class="fas fa-calendar-alt"></i> <span class="menu-text">Enrollment</span></a></li>
                    <li><a href="#subjects" data-bs-toggle="tab"><i class="fas fa-book"></i> <span class="menu-text">Subjects</span></a></li>
                    <li><a href="#students" data-bs-toggle="tab"><i class="fas fa-users"></i> <span class="menu-text">Students</span></a></li>
                    <li><a href="#instructors" data-bs-toggle="tab"><i class="fas fa-chalkboard-teacher"></i> <span class="menu-text">Instructors</span></a></li>
                    <li><a href="#organizations" data-bs-toggle="tab"><i class="fas fa-sitemap"></i> <span class="menu-text">Organizations</span></a></li>
                    <li><a href="#audit" data-bs-toggle="tab"><i class="fas fa-history"></i> <span class="menu-text">Audit Logs</span></a></li>
                    <li><a href="#files" data-bs-toggle="tab"><i class="fas fa-file-alt"></i> <span class="menu-text">Files</span></a></li>
                    <!-- <li><a href="#"><i class="fas fa-cog"></i> <span class="menu-text">Settings</span></a></li> -->
                    <li>
                        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt"></i> <span class="menu-text">Logout</span>
                        </a>
                    </li>
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
                    <!-- Generate Passkey - always visible -->
                    <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#passkeyModal">
                        <i class="fas fa-key me-1"></i> Generate Passkey
                    </button>
                    
                    <!-- User Profile - centered -->
                    <div class="user-profile me-2">
                        <div class="user-avatar" style="background-color: white;">
                            @php
                                // Get the admin user from Auth
                                $admin = Auth::guard('admin')->user();
                                $profileImageUrl = $admin && $admin->profile 
                                    ? route('admin.image', ['adminId' => $admin->admin_id])
                                    : asset('storage/profile/admin/default.png');
                            @endphp
                            
                            <img style="object-fit: cover;" src="{{ $profileImageUrl }}" alt="user_avatar" class="rounded-circle">
                        </div>
                        <div class="user-info d-none d-md-block">
                            <div class="user-name">
                                {{ $admin->info->firstname ?? $admin->email }}
                            </div>
                            <div class="user-role">Administrator</div>
                        </div>
                    </div>
                    
                    <!-- Theme Toggle - last -->
                    <button id="themeToggle" class="theme-toggle-btn">
                        <i class="fas fa-moon"></i>
                    </button>
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
                                        <h2 class="mb-0">{{ $stats['students'] }}</h2>
                                    </div>
                                    <div class="bg-primary p-3 rounded">
                                        <i class="fas fa-users fa-2x text-white"></i>
                                    </div>
                                </div>
                                <!-- <div class="mt-3">
                                    <span class="text-success"><i class="fas fa-arrow-up"></i> 5.2%</span>
                                    <span class="text-muted ms-2">Since last month</span>
                                </div> -->
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Instructors</h5>
                                        <h2 class="mb-0">{{ $stats['instructors'] }}</h2>
                                    </div>
                                    <div class="bg-success p-3 rounded">
                                        <i class="fas fa-chalkboard-teacher fa-2x text-white"></i>
                                    </div>
                                </div>
                                <!-- <div class="mt-3">
                                    <span class="text-success"><i class="fas fa-arrow-up"></i> 2.1%</span>
                                    <span class="text-muted ms-2">Since last month</span>
                                </div> -->
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
                                <!-- <div class="mt-3">
                                    <span class="text-success"><i class="fas fa-arrow-up"></i> 3.7%</span>
                                    <span class="text-muted ms-2">Since last month</span>
                                </div> -->
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
                                        <h2 class="mb-0">{{ $stats['enrollments'] }}</h2>
                                    </div>
                                    <div class="bg-info p-3 rounded">
                                        <i class="fas fa-file-alt fa-2x text-white"></i>
                                    </div>
                                </div>
                                <!-- <div class="mt-3">
                                    <span class="text-success"><i class="fas fa-arrow-up"></i> 8.3%</span>
                                    <span class="text-muted ms-2">Since last month</span>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="tab-content mt-4">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">Upload CSV File</h5>
                                    <p class="text-muted mb-0 small">Import student data in bulk</p>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#csvTemplateModal">
                                    <i class="fas fa-file-download me-1"></i> Template
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- Upload Area -->
                                <div class="csv-upload-area mb-4">
                                    <div class="upload-container text-center p-5 border-2 border-dashed rounded-4" 
                                        id="dropZone">
                                        <div class="upload-icon mb-3">
                                            <i class="fas fa-file-csv text-primary" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="mb-2">Drag & Drop CSV File</h5>
                                        <p class="text-muted mb-4">or click to browse files</p>
                                        <input type="file" id="csvFileInput" accept=".csv" class="d-none">
                                        <label for="csvFileInput" class="btn btn-primary px-4">
                                            <i class="fas fa-upload me-2"></i> Select File
                                        </label>
                                        <p class="small text-muted mt-3 mb-0">Maximum file size: 5MB. File must be in CSV format.</p>
                                    </div>
                                    
                                    <!-- File Info (Shown when file is selected) -->
                                    <div class="selected-file mt-4 d-none" id="fileInfo">
                                        <div class="alert alert-info d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-csv text-primary fs-4 me-3"></i>
                                                <div>
                                                    <h6 class="mb-0" id="fileName"></h6>
                                                    <small class="text-muted" id="fileSize"></small>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" id="removeFile">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>


                                <!-- Upload Button -->
                                <div class="upload-actions">
                                    <button type="button" class="btn btn-primary btn-lg w-100" id="uploadCsvBtn" disabled>
                                        <span class="upload-text">
                                            <i class="fas fa-cloud-upload-alt me-2"></i> Upload CSV
                                        </span>
                                        <span class="spinner-border spinner-border-sm d-none" id="uploadSpinner"></span>
                                    </button>
                                </div>

                                <!-- Upload Progress -->
                                <div class="upload-progress mt-4 d-none" id="uploadProgress">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Uploading...</span>
                                        <span id="progressPercent">0%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                            role="progressbar" 
                                            style="width: 0%" 
                                            id="progressBar"></div>
                                    </div>
                                </div>

                                <!-- Result Message -->
                                <div class="alert d-none mt-3" id="resultMessage"></div>
                            </div>
                        </div>

                        <!-- Recently Uploaded Files -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="mb-0">Recent Uploads</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">CSV Data from Database</h6>
                                            <button id="downloadCsvBtn" class="btn btn-success btn-sm">
                                                <i class="fas fa-download me-1"></i> Download CSV Data
                                            </button>
                                        </div>
                                        <thead>
                                            <tr>
                                                <th>Application Number</th>
                                                <th>Preferred Program</th>
                                                <th>Last Name</th>
                                                <th>First Name</th>
                                                <th>Middle Name</th>
                                                <th>Email</th>
                                                <th>Contact Number</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="recentUploads" style="white-space: nowrap;">
                                            <!-- Will be populated by AJAX -->
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    <span class="ms-2">Loading recent uploads...</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Move this outside the table, preferably at the end of the tab-content -->
                    <div class="modal fade" id="editCSVModal" tabindex="-1" aria-labelledby="editCSVModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editCSVModalLabel">Edit CSV Record</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="editCSVForm">
                                        <input type="hidden" id="editId">
                                                        
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="editApplicationNumber" class="form-label">Application Number</label>
                                                    <input type="text" class="form-control" id="editApplicationNumber">
                                                </div>
                                                            
                                                <div class="col-md-6 mb-3">
                                                    <label for="editPreferredProgram" class="form-label">Preferred Program</label>
                                                    <input type="text" class="form-control" id="editPreferredProgram">
                                                </div>
                                            </div>
                                                        
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label for="editLastName" class="form-label">Last Name</label>
                                                    <input type="text" class="form-control" id="editLastName" required>
                                                </div>
                                                            
                                                <div class="col-md-4 mb-3">
                                                    <label for="editFirstName" class="form-label">First Name</label>
                                                    <input type="text" class="form-control" id="editFirstName" required>
                                                </div>
                                                            
                                                <div class="col-md-4 mb-3">
                                                    <label for="editMiddleName" class="form-label">Middle Name</label>
                                                    <input type="text" class="form-control" id="editMiddleName">
                                                </div>
                                            </div>
                                                        
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="editEmail" class="form-label">Email Address</label>
                                                    <input type="email" class="form-control" id="editEmail" required>
                                                <div class="form-text">Enter a valid email address</div>
                                            </div>
                                                            
                                            <div class="col-md-6 mb-3">
                                                <label for="editContactNumber" class="form-label">Contact Number</label>
                                                <input type="text" class="form-control" id="editContactNumber">
                                                <div class="form-text">Format: 09171234567</div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary" id="saveCSVBtn" onclick="saveCSVEdit()">
                                        Save Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CSV Template Modal -->
                    <div class="modal fade" id="csvTemplateModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">CSV Template</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Download the template file to ensure proper formatting:</p>
                                    <div class="alert alert-info">
                                        <h6>Required Columns:</h6>
                                        <ul class="mb-0">
                                            <li><code>student_number</code> (e.g., 2020-30617)</li>
                                            <li><code>application_number</code> (e.g., APP-2024-001)</li>
                                            <li><code>preffered_program</code> (e.g., BSIT)</li>
                                            <li><code>lastname</code> (e.g., Donquixote)</li>
                                            <li><code>firstname</code> (e.g., Doflamingo)</li>
                                            <li><code>middlename</code> (e.g., Doffy)</li>
                                            <li><code>email</code> (e.g., student@example.com)</li>
                                            <li><code>contact_number</code> (e.g., 09464930679)</li>
                                        </ul>
                                    </div>
                                    <a href="/admin/csv-template" class="btn btn-primary w-100 mt-3">
                                        <i class="fas fa-download me-2"></i> Download Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subjects Tab -->
                    <div class="tab-pane fade" id="subjects">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <div class="d-flex align-items-center">
                                    <h5 class="mb-2 mb-md-0 me-3">Manage Subjects</h5>
                                    <!-- Curriculum Toggle Button -->
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="curriculumDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            Curriculum: <span id="selectedCurriculum">Select Curriculum</span>
                                        </button>
                                        <ul class="dropdown-menu" id="curriculumList">
                                            <!-- Curriculum options will be loaded here -->
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div class="search-container me-2">
                                        <i class="fas fa-search search-icon"></i>
                                        <input type="text" class="form-control search-input" placeholder="Search subjects..." id="subjectSearch">
                                    </div>
                                    <button style="background-color: maroon;" id="plus_sign" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSubjectModal">
                                        <i class="fas fa-plus me-1"></i> <span class="d-none d-md-inline">Create Subject</span>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="subjectsContainer">
                                    <!-- Subject groups will be loaded here via JavaScript -->
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
                                <!-- Year Level Filter Above Table -->
                                <div class="year-level-filter-container">
                                    <div class="small fw-bold mb-2 uppercase-tracking filter-title"><i class="fas fa-filter me-1"></i> FILTER BY YEAR LEVEL</div>
                                    <div class="d-flex flex-wrap gap-2" id="yearLevelFilters">
                                        <button class="btn btn-sm btn-pill btn-year-filter active" data-year="All">All Years</button>
                                        <button class="btn btn-sm btn-pill btn-year-filter" data-year="1st Year">1st Year</button>
                                        <button class="btn btn-sm btn-pill btn-year-filter" data-year="2nd Year">2nd Year</button>
                                        <button class="btn btn-sm btn-pill btn-year-filter" data-year="3rd Year">3rd Year</button>
                                        <button class="btn btn-sm btn-pill btn-year-filter" data-year="4th Year">4th Year</button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover" id="studentsTable">
                                        <thead>
                                            <tr>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>Program</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                             <tr>
                                                 <td>2023-001</td>
                                                 <td>Eva Elfie</td>
                                                 <td>BS Information Technology</td>
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
                                <div class="table-responsive">
                                    <table class="table table-hover" id="instructorsTable">
                                        <thead>
                                            <tr>
                                                <th>Instructor ID</th>
                                                <th>Name</th>
                                                <th>Department</th>
                                                <th>Office</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Loaded dynamically via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
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
                                <div class="table-responsive">
                                    <table class="table table-hover" id="organizationsTable">
                                        <thead>
                                            <tr>
                                                <th>Organization ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Address</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Loaded dynamically via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Enrollment Tab -->
                    <div class="tab-pane fade" id="enrollment">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h5 class="mb-2 mb-md-0">Enrollment Period Management</h5>
                                <button style="background-color: maroon" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#enrollmentModal">
                                    <i class="fas fa-plus me-1"></i> <span class="d-none d-md-inline">Set Enrollment Period</span>
                                </button>
                            </div>
                            <div class="card-body">
                                <!-- <div class="row" id="enrollmentCards">
                                    Enrollment periods will be loaded here
                                </div> -->
                                
                                <div class="table-responsive mt-4">
                                    <table class="table table-hover" id="enrollmentTable">
                                        <thead>
                                            <tr>
                                                <th>Semester</th>
                                                <th>Academic Year</th>
                                            </tr>
                                        </thead>
                                        <tbody id="enrollmentTableBody">
                                            <!-- Enrollment data will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
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
                                                <th>Actions</th>
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
                                    <!-- Dynamic file items will be loaded here -->
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
                        <!-- Add this hidden input for curriculum -->
                         <input type="hidden" id="curr" name="curr" value="">
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
                            <div style="display: none;" class="col-md-3 mb-3">
                                <label for="maxStudents" class="form-label">Max Students <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="maxStudents" name="maxStudents" min="1" value="50" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="yearLevel" class="form-label">Year Level <span class="text-danger">*</span></label>
                                <select class="form-select" id="yearLevel" name="yearLevel">
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
                                    <input class="form-check-input" type="checkbox" id="lectureCheck" name="subjectType[]" value="Lecture" autoselect>
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

    <!-- Create Curriculum Modal -->
    <div class="modal fade" id="createCurriculumModal" tabindex="-1" aria-labelledby="createCurriculumModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCurriculumModalLabel">Create New Curriculum</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createCurriculumForm">
                        <div class="mb-3">
                            <label for="curriculumYear" class="form-label">Curriculum Year</label>
                            <input type="text" class="form-control" id="curriculumYear" placeholder="e.g., 2024" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="createCurriculumBtn">Create Curriculum</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Curriculum Modal -->
    <div class="modal fade" id="createCurriculumModal" tabindex="-1" aria-labelledby="createCurriculumModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCurriculumModalLabel">Create New Curriculum</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createCurriculumForm">
                        <div class="mb-3">
                            <label for="curriculumYear" class="form-label">Curriculum Year</label>
                            <input type="text" class="form-control" id="curriculumYear" placeholder="e.g., 2024" required>
                            <div class="form-text">Enter the curriculum year (e.g., 2024, 2025)</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveCurriculumBtn">Save Curriculum</button>
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

    <!-- Enrollment Modal -->
    <div class="modal fade" id="enrollmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Set Enrollment Period</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Enrollment Type Toggle -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enrollmentTypeSwitch">
                            <label class="form-check-label" for="enrollmentTypeSwitch">
                                <span id="switchLabel">Switch</span> 
                                <span class="text-muted ms-2" id="switchDescription">(Switch to Summer)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Enrollment Status Display -->
                    <div id="enrollmentStatusDisplay" class="mb-4 p-3 border rounded">
                        <div id="regularStatusInfo" class="enrollment-info">
                            <h6 class="mb-2">Regular Semester Enrollment</h6>
                            <div class="row small text-muted">
                                <div class="col-md-4">
                                    <strong>Year Level:</strong>
                                    <div class="mt-1">All Year Levels</div>
                                </div>
                                <div class="col-md-4">
                                    <strong>Semester:</strong>
                                    <div class="mt-1" id="semesterDisplay">Will be determined based on current month</div>
                                </div>
                                <div class="col-md-4">
                                    <strong>Academic Year:</strong>
                                    <div class="mt-1" id="academicYearDisplay">Will be determined based on current year</div>
                                </div>
                            </div>
                            <div class="mt-3 small">
                                <i class="fas fa-info-circle me-1"></i>
                                <span>Click "On" to start enrollment. The semester and academic year will be automatically set based on today's date.</span>
                            </div>
                        </div>
                        
                        <div id="summerStatusInfo" class="enrollment-info" style="display: none;">
                            <h6 class="mb-2">Summer Semester Enrollment</h6>
                            <div class="row small text-muted">
                                <div class="col-md-4">
                                    <strong>Year Level:</strong>
                                    <div class="mt-1">NULL</div>
                                </div>
                                <div class="col-md-4">
                                    <strong>Semester:</strong>
                                    <div class="mt-1">Summer</div>
                                </div>
                                <div class="col-md-4">
                                    <strong>Academic Year:</strong>
                                    <div class="mt-1" id="summerAcademicYearDisplay">Will be set to current year</div>
                                </div>
                            </div>
                            <div class="mt-3 small">
                                <i class="fas fa-sun me-1"></i>
                                <span>Click "On" to start summer enrollment. The academic year will be set to the current year only.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden forms for data submission (no visible fields) -->
                    <form id="regularForm" class="enrollment-form" style="display: none;">
                        <input type="hidden" id="regularSemester" name="semester">
                        <input type="hidden" id="regularAcademicYear" name="academic_year">
                        <input type="hidden" id="regularIsActive" name="is_active" value="1">
                    </form>

                    <form id="summerForm" class="enrollment-form" style="display: none;">
                        <input type="hidden" id="summerAcademicYear" name="academic_year">
                        <input type="hidden" id="summerSemester" name="semester" value="Summer">
                        <input type="hidden" id="summerIsActive" name="is_active" value="1">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" id="offBtn" style="background-color: maroon;" class="btn btn-secondary" data-bs-dismiss="modal">Off</button>
                    <button type="button" style="background-color: maroon;" class="btn btn-primary" id="onBtn">On</button>
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
                        <input type="email" class="form-control" id="emailAddress" placeholder="evsumail" required>
                        <div class="invalid-feedback">Please enter a valid EVSUmail address</div>
                    </div>
                    <div class="mb-3">
                        <label for="userTypeSelect" class="form-label">User Type</label>
                        <select class="form-select" id="userTypeSelect" required>
                            <option value="">Select user type</option>
                            <option value="instructor">Instructor</option>
                            <option value="organization">Organization</option>
                        </select>
                        <div class="invalid-feedback">Please select a user type</div>
                    </div>
                    <div class="passkey-display" id="passkeyDisplay">
                        Click Generate to create a passkey
                    </div>
                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-primary" id="generatePasskeyBtn">
                            <i class="fas fa-key me-1"></i> Generate Passkey
                        </button>
                        <button class="btn btn-success" id="sendPasskeyBtn" disabled>
                            <i class="fas fa-paper-plane me-1"></i>
                            <span id="loginBtnText">Send Passkey</span> 
                            <span class="spinner-border spinner-border-sm" style="display: none;" id="sendPasskeySpinner"></span>
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
                    <button type="submit" form="uploadFileForm" class="btn btn-primary" id="btnSubmitUpload">Upload File</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Student Modal -->
    <div class="modal fade" id="viewStudentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-primary"><i class="fas fa-user-graduate me-2"></i>Student Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <!-- Profile Card -->
                        <div class="col-md-4 text-center border-md-end border-secondary">
                            <div class="p-3">
                                <div class="student-avatar-container mb-3 d-flex justify-content-center">
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: bold; color: white; box-shadow: 0 0 15px rgba(13, 110, 253, 0.4);" id="viewStudentInitials">
                                        JD
                                    </div>
                                </div>
                                <h4 class="mb-1" id="viewStudentName">John Doe</h4>
                                <p class="text-muted mb-2" id="viewStudentIdNo">2020-00000</p>
                                <span class="badge" id="viewStudentStatusBadge" style="font-size: 0.9rem; padding: 8px 12px;">Active</span>
                            </div>
                        </div>
                        <!-- Details Grid -->
                        <div class="col-md-8">
                            <h5 class="text-info border-bottom border-secondary pb-2 mb-3"><i class="fas fa-info-circle me-2"></i>Personal Information</h5>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Sex:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewStudentSex">N/A</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Age:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewStudentAge">N/A</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Birthdate:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewStudentBirthdate">N/A</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Phone Number:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewStudentPhone">N/A</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Email Address:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewStudentEmail">N/A</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Home Address:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewStudentAddress">N/A</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Civil Status:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewStudentCivilStatus">N/A</div>
                            </div>

                            <h5 class="text-info border-bottom border-secondary pb-2 mb-3 mt-4"><i class="fas fa-university me-2"></i>Academic Profile</h5>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Program / Degree:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewStudentProgram">BS Information Technology</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Year Level:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewStudentYearLevel">N/A</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Curriculum Year:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewStudentCurriculum">N/A</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Student Type:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewStudentType">N/A</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">School Year (SY):</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewStudentSy">N/A</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="viewStudentEditBtn"><i class="fas fa-edit me-1"></i>Edit Student</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Instructor Details Modal -->
    <div class="modal fade" id="viewInstructorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-primary"><i class="fas fa-chalkboard-teacher me-2"></i>Instructor Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <!-- Profile Card -->
                        <div class="col-md-4 text-center border-md-end border-secondary">
                            <div class="p-3">
                                <div class="student-avatar-container mb-3 d-flex justify-content-center">
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: bold; color: white; box-shadow: 0 0 15px rgba(13, 110, 253, 0.4);" id="viewInstructorInitials">
                                        TR
                                    </div>
                                </div>
                                <h4 class="mb-1" id="viewInstructorName">Trainer Name</h4>
                                <p class="text-muted mb-2" id="viewInstructorId">ID-00000</p>
                                <span class="badge" id="viewInstructorStatusBadge" style="font-size: 0.9rem; padding: 8px 12px;">Active</span>
                            </div>
                        </div>
                        <!-- Details Grid -->
                        <div class="col-md-8">
                            <h5 class="text-info border-bottom border-secondary pb-2 mb-3"><i class="fas fa-info-circle me-2"></i>Professional Info</h5>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Department:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewInstructorDepartment">Computer Studies Department</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Office Location:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewInstructorOffice">N/A</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Bio/Introduction:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewInstructorBio">N/A</div>
                            </div>

                            <h5 class="text-info border-bottom border-secondary pb-2 mb-3 mt-4"><i class="fas fa-address-book me-2"></i>Personal & Contact Details</h5>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Email Address:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewInstructorEmail">N/A</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Phone Number:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewInstructorPhone">N/A</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Home Address:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewInstructorAddress">N/A</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Birthdate:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewInstructorBirthdate">N/A</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Age:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewInstructorAge">N/A</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Organization Details Modal -->
    <div class="modal fade" id="viewOrganizationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-primary"><i class="fas fa-sitemap me-2"></i>Organization Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <!-- Profile Card -->
                        <div class="col-md-4 text-center border-md-end border-secondary">
                            <div class="p-3">
                                <div class="student-avatar-container mb-3 d-flex justify-content-center">
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: bold; color: white; box-shadow: 0 0 15px rgba(13, 110, 253, 0.4);" id="viewOrganizationInitials">
                                        OR
                                    </div>
                                </div>
                                <h4 class="mb-1" id="viewOrganizationName">Organization Name</h4>
                                <p class="text-muted mb-2" id="viewOrganizationId">ID-00000</p>
                                <span class="badge" id="viewOrganizationStatusBadge" style="font-size: 0.9rem; padding: 8px 12px;">Active</span>
                            </div>
                        </div>
                        <!-- Details Grid -->
                        <div class="col-md-8">
                            <h5 class="text-info border-bottom border-secondary pb-2 mb-3"><i class="fas fa-info-circle me-2"></i>Organization Information</h5>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Organization Email:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewOrganizationEmail">N/A</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Office/Location Address:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewOrganizationAddress">N/A</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted">Date Created:</div>
                                <div class="col-sm-8 text-white font-weight-bold" id="viewOrganizationDateCreated">N/A</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-white border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-warning"><i class="fas fa-user-edit me-2"></i>Edit Student Information</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editStudentForm">
                        <input type="hidden" id="editStudentId" name="student_id">
                        
                        <div class="row g-3">
                            <h6 class="text-info mb-1"><i class="fas fa-id-card me-2"></i>Academic Details</h6>
                            
                            <div class="col-md-4">
                                <label for="editStudentIdNo" class="form-label text-muted">Student ID / ID No.</label>
                                <input type="text" class="form-control bg-secondary text-white border-secondary" id="editStudentIdNo" name="id_no" required>
                            </div>
                            <div class="col-md-4">
                                <label for="editStudentYearLevel" class="form-label text-muted">Year Level</label>
                                <select class="form-select bg-secondary text-white border-secondary" id="editStudentYearLevel" name="year_level" required>
                                    <option value="1st Year">1st Year</option>
                                    <option value="2nd Year">2nd Year</option>
                                    <option value="3rd Year">3rd Year</option>
                                    <option value="4th Year">4th Year</option>
                                    <option value="5th Year">5th Year</option>
                                    <option value="NONE">NONE</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="editStudentCurriculum" class="form-label text-muted">Curriculum</label>
                                <input type="text" class="form-control bg-secondary text-white border-secondary" id="editStudentCurriculum" name="curriculum" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="editStudentStatus" class="form-label text-muted">Enrollment Status</label>
                                <select class="form-select bg-secondary text-white border-secondary" id="editStudentStatus" name="status" required>
                                    <option value="Not Enrolled">Not Enrolled</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Officially Enrolled">Officially Enrolled</option>
                                    <option value="Rejected">Rejected</option>
                                    <option value="None">None</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="editStudentIsRegular" class="form-label text-muted">Student Type</label>
                                <select class="form-select bg-secondary text-white border-secondary" id="editStudentIsRegular" name="is_regular" required>
                                    <option value="1">Regular</option>
                                    <option value="2">Irregular</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="editStudentSy" class="form-label text-muted">School Year (SY)</label>
                                <input type="text" class="form-control bg-secondary text-white border-secondary" id="editStudentSy" name="sy" required>
                            </div>

                            <hr class="my-4 border-secondary">
                            <h6 class="text-info mb-1"><i class="fas fa-user me-2"></i>Personal Details</h6>

                            <div class="col-md-4">
                                <label for="editStudentFirstname" class="form-label text-muted">First Name</label>
                                <input type="text" class="form-control bg-secondary text-white border-secondary" id="editStudentFirstname" name="firstname" required>
                            </div>
                            <div class="col-md-4">
                                <label for="editStudentLastname" class="form-label text-muted">Last Name</label>
                                <input type="text" class="form-control bg-secondary text-white border-secondary" id="editStudentLastname" name="lastname" required>
                            </div>
                            <div class="col-md-4">
                                <label for="editStudentMiddlename" class="form-label text-muted">Middle Name</label>
                                <input type="text" class="form-control bg-secondary text-white border-secondary" id="editStudentMiddlename" name="middlename">
                            </div>

                            <div class="col-md-6">
                                <label for="editStudentEmail" class="form-label text-muted">Email Address</label>
                                <input type="email" class="form-control bg-secondary text-white border-secondary" id="editStudentEmail" name="email2" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editStudentPhone" class="form-label text-muted">Phone Number</label>
                                <input type="text" class="form-control bg-secondary text-white border-secondary" id="editStudentPhone" name="phone_number">
                            </div>

                            <div class="col-md-4">
                                <label for="editStudentSex" class="form-label text-muted">Sex</label>
                                <select class="form-select bg-secondary text-white border-secondary" id="editStudentSex" name="sex">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="editStudentAge" class="form-label text-muted">Age</label>
                                <input type="number" class="form-control bg-secondary text-white border-secondary" id="editStudentAge" name="age">
                            </div>
                            <div class="col-md-4">
                                <label for="editStudentBirthdate" class="form-label text-muted">Birthdate</label>
                                <input type="date" class="form-control bg-secondary text-white border-secondary" id="editStudentBirthdate" name="birthdate">
                            </div>

                            <div class="col-md-8">
                                <label for="editStudentAddress" class="form-label text-muted">Address</label>
                                <input type="text" class="form-control bg-secondary text-white border-secondary" id="editStudentAddress" name="address">
                            </div>
                            <div class="col-md-4">
                                <label for="editStudentRelationshipStatus" class="form-label text-muted">Civil Status</label>
                                <select class="form-select bg-secondary text-white border-secondary" id="editStudentRelationshipStatus" name="relationship_status">
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Divorced">Divorced</option>
                                    <option value="Widowed">Widowed</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning text-dark font-weight-bold" id="saveStudentBtn"><i class="fas fa-save me-1"></i>Save Changes</button>
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
    <script src="{{asset('js/function/admin_dashboard/dashboard.js')}}"></script>
    <script src="{{asset('js/function/admin_dashboard/enrollment.js')}}"></script>
    <script src="{{asset('js/sweetalert3.js')}}"></script>
</body>
</html>