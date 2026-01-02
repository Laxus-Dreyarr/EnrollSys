<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Prospectus - {{ $student['full_name'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }
        
        .header h2 {
            color: #34495e;
            margin: 5px 0;
            font-size: 18px;
        }
        
        .header h3 {
            color: #7f8c8d;
            margin: 5px 0;
            font-size: 14px;
        }
        
        .student-info {
            margin-bottom: 30px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        
        .student-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .student-info td {
            padding: 5px;
            vertical-align: top;
        }
        
        .info-label {
            font-weight: bold;
            width: 120px;
        }
        
        .academic-info {
            background-color: #e9f7fe;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #b8daff;
        }
        
        .academic-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .academic-info td {
            padding: 5px;
        }
        
        .subject-group {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .group-header {
            background-color: #3498db;
            color: white;
            padding: 8px 15px;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .group-title {
            font-size: 14px;
        }
        
        .group-units {
            font-size: 12px;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 3px 10px;
            border-radius: 20px;
        }
        
        .subject-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }
        
        .subject-table th {
            background-color: #ecf0f1;
            color: #2c3e50;
            padding: 8px;
            text-align: left;
            border: 1px solid #bdc3c7;
            font-weight: bold;
        }
        
        .subject-table td {
            padding: 8px;
            border: 1px solid #bdc3c7;
            vertical-align: top;
        }
        
        .grade-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            min-width: 50px;
        }
        
        .grade-badge.graded {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .grade-badge.failed {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .grade-badge.ungraded {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .grade-badge.passed {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .total-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
        }
        
        .total-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .total-card {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
            text-align: center;
        }
        
        .total-card .number {
            font-size: 24px;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 5px;
        }
        
        .total-card .label {
            font-size: 12px;
            color: #7f8c8d;
        }
        
        .overall-total {
            background-color: #3498db;
            color: white;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            margin-top: 20px;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #7f8c8d;
            text-align: center;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .logo-img {
            max-width: 100px;
            height: auto;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(0, 0, 0, 0.1);
            z-index: -1;
            font-weight: bold;
            pointer-events: none;
        }
        
        .prerequisites-cell {
            max-width: 150px;
            word-wrap: break-word;
        }
        
        @page {
            margin: 20px;
        }
        
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    <div class="watermark">EVSU</div>
    
    <!-- Logo Section -->
    <div class="logo">
        <!-- You can add your university logo here -->
        <div style="font-size: 16px; font-weight: bold; color: #2c3e50;">
            EASTERN VISAYAS STATE UNIVERSITY
        </div>
        <div style="font-size: 12px; color: #7f8c8d;">
            EnrollSys - Official Prospectus
        </div>
    </div>
    
    <div class="header">
        <h1>ACADEMIC PROSPECTUS</h1>
        <h2>Student Academic Record</h2>
        <h3>Academic Year: {{ $enrollmentAcademicYear }}</h3>
    </div>
    
    <!-- Student Information -->
    <div class="student-info">
        <table>
            <tr>
                <td class="info-label">Student Name:</td>
                <td style="font-weight: bold;">{{ $student['full_name'] }}</td>
                <td class="info-label">Student ID:</td>
                <td>{{ $student['student_id'] }}</td>
            </tr>
            <tr>
                <td class="info-label">Address:</td>
                <td colspan="3">{{ $student['address'] }}</td>
            </tr>
            <tr>
                <td class="info-label">Email:</td>
                <td>{{ $student['email'] }}</td>
                <td class="info-label">Phone:</td>
                <td>{{ $student['phone'] }}</td>
            </tr>
            <tr>
                <td class="info-label">Year Level:</td>
                <td>{{ $student['year_level'] }}</td>
                <td class="info-label">Curriculum:</td>
                <td>{{ $student['curriculum'] }}</td>
            </tr>
            <tr>
                <td class="info-label">Enrollment Status:</td>
                <td>{{ $student['status'] }}</td>
                <td class="info-label">Generated Date:</td>
                <td>{{ $generated_date }}</td>
            </tr>
        </table>
    </div>
    
    <!-- Academic Information -->
    <div class="academic-info">
        <table>
            <tr>
                <td style="font-weight: bold; width: 150px;">Curriculum Year:</td>
                <td>{{ $curriculumYear }}</td>
                <td style="font-weight: bold; width: 150px;">Academic Year:</td>
                <td>{{ $enrollmentAcademicYear }}</td>
            </tr>
            @if($enrollmentPeriod && isset($enrollmentPeriod['semester']))
            <tr>
                <td style="font-weight: bold;">Semester:</td>
                <td colspan="3">{{ $enrollmentPeriod['semester'] }}</td>
            </tr>
            @endif
        </table>
    </div>
    
    <!-- Subjects by Year Level and Semester -->
    @foreach($yearLevelOrder as $year)
        @if(isset($organizedSubjects[$year]))
            @foreach($semesterOrder as $semester)
                @if(isset($organizedSubjects[$year][$semester]))
                    @php
                        $groupData = $organizedSubjects[$year][$semester];
                        $groupLabel = $year . ' ' . $semester;
                        if ($year === '3rd Year' && $semester === 'Summer') {
                            $groupLabel = 'Summer or Third Term';
                        }
                    @endphp
                    
                    <div class="subject-group">
                        <div class="group-header">
                            <div class="group-title">{{ $groupLabel }}</div>
                            <div class="group-units">Total Units: {{ $groupData['totalUnits'] }}</div>
                        </div>
                        
                        <table class="subject-table">
                            <thead>
                                <tr>
                                    <th>Subject Code</th>
                                    <th>Subject Name</th>
                                    <th>Units</th>
                                    <th>Prerequisites</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupData['subjects'] as $subject)
                                    @php
                                        $failedGrades = ['4.0', '5.0', 'INC', 'DRP'];
                                        $gradeStr = $subject['grade'] ? (string)$subject['grade'] : '';
                                        $hasGrade = !empty($subject['grade']);
                                        
                                        if ($hasGrade) {
                                            if (in_array($gradeStr, $failedGrades)) {
                                                $gradeClass = 'failed';
                                                $status = 'Failed';
                                            } else {
                                                $gradeClass = 'passed';
                                                $status = 'Passed';
                                            }
                                        } else {
                                            $gradeClass = 'ungraded';
                                            $status = 'Pending';
                                        }
                                        
                                        $gradeText = $subject['grade'] ?? 'N/A';
                                    @endphp
                                    <tr>
                                        <td>{{ $subject['subject_code'] }}</td>
                                        <td>{{ $subject['subject_name'] }}</td>
                                        <td style="text-align: center;">{{ $subject['units'] }}</td>
                                        <td class="prerequisites-cell">{{ $subject['prerequisites'] }}</td>
                                        <td style="text-align: center;">
                                            <span class="grade-badge {{ $gradeClass }}">{{ $gradeText }}</span>
                                        </td>
                                        <td style="text-align: center;">{{ $status }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endforeach
        @endif
    @endforeach
    
    <!-- Summary Section -->
    <div class="total-section">
        <div class="total-grid">
            <div class="total-card">
                <div class="number">{{ $totalSubjects }}</div>
                <div class="label">Total Subjects</div>
            </div>
            <div class="total-card">
                <div class="number">{{ $totalUnits }}</div>
                <div class="label">Total Units</div>
            </div>
            <div class="total-card">
                <div class="number">{{ $subjectsWithGrades }}</div>
                <div class="label">Graded Subjects</div>
            </div>
            <div class="total-card">
                <div class="number">{{ $totalSubjects - $subjectsWithGrades }}</div>
                <div class="label">Pending Grades</div>
            </div>
        </div>
        
        <div class="overall-total">
            Cumulative Total Units: {{ $totalUnits }}
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p>EASTERN VISAYAS STATE UNIVERSITY - Enrollment System</p>
        <p>This is an official system-generated document. No signature required.</p>
        <p>Generated on: {{ $dateGenerated }}</p>
        <p style="font-size: 9px; margin-top: 10px;">
            For any discrepancies, please contact the Administrator within 5 working days.
        </p>
    </div>
</body>
</html>