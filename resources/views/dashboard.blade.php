<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EnrollSys</title>
    <!-- Your existing CSS and JS links -->
    <link rel="stylesheet" href="{{ asset('style/bootstrap5.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body class="light-theme">
    <div class="dashboard-container">
        <!-- Your existing sidebar and layout -->

        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <div class="topbar">
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-sm btn-light d-md-none" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="d-flex align-items-center">
                        <span class="me-3">Welcome, {{ $user->info->firstname ?? 'Admin' }}</span>
                        <!-- Profile dropdown -->
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <div class="page-content">
                <h1>Welcome, {{ $user->info->firstname }}!</h1>
                
                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-primary">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="card-title">{{ $stats['students'] }}</h5>
                                        <p class="card-text">Total Students</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-success">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="card-title">{{ $stats['instructors'] }}</h5>
                                        <p class="card-text">Total Instructors</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-info">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="card-title">{{ $stats['subjects'] }}</h5>
                                        <p class="card-text">Active Subjects</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-warning">
                                        <i class="fas fa-clipboard-list"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="card-title">{{ $stats['enrollments'] }}</h5>
                                        <p class="card-text">Current Enrollments</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="{{ asset('js/bootstrap5.bundle.min.js') }}"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>