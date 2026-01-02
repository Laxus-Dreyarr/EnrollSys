<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Prospectus</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
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
        
        .student-info {
            margin-bottom: 30px;
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
        
        .subject-group {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .group-header {
            background-color: #3498db;
            color: white;
            padding: 8px;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 4px;
        }
        
        .subject-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        .subject-table th {
            background-color: #ecf0f1;
            color: #2c3e50;
            padding: 8px;
            text-align: left;
            border: 1px solid #bdc3c7;
            font-size: 11px;
        }
        
        .subject-table td {
            padding: 8px;
            border: 1px solid #bdc3c7;
            font-size: 11px;
        }
        
        .total-units {
            font-weight: bold;
            text-align: right;
            padding: 8px;
            background-color: #f8f9fa;
        }
        
        .grade-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .grade-badge.graded {
            background-color: #d4edda;
            color: #155724;
        }
        
        .grade-badge.failed {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .grade-badge.ungraded {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #7f8c8d;
            text-align: center;
        }
        
        .overall-total {
            text-align: right;
            font-weight: bold;
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>EVSU - Student Prospectus</h1>
        <h2>Academic Records</h2>
    </div>
    
    <div class="student-info">
        <table>
            <tr>
                <td class="info-label">Student Name:</td>
                <td>{{ $student['full_name'] }}</td>
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
    
    @foreach($groupedSubjects as $groupLabel => $group)
        <div class="subject-group">
            <div class="group-header">{{ $groupLabel }}</div>
            
            <table class="subject-table">
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Units</th>
                        <th>Prerequisite</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group['subjects'] as $subject)
                        @php
                            $failedGrades = ['4.0', '5.0', 'INC', 'DRP'];
                            $gradeStr = $subject->grade ? (string)$subject->grade : '';
                            $gradeClass = $subject->grade 
                                ? (in_array($gradeStr, $failedGrades) ? 'failed' : 'graded')
                                : 'ungraded';
                            $gradeText = $subject->grade ? $subject->grade : 'Not Graded';
                        @endphp
                        <tr>
                            <td>{{ $subject->subject_code }}</td>
                            <td>{{ $subject->subject_name }}</td>
                            <td>{{ $subject->units }}</td>
                            <td>{{ $subject->prerequisites }}</td>
                            <td>
                                <span class="grade-badge {{ $gradeClass }}">{{ $gradeText }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="total-units">Total Units for {{ $groupLabel }}:</td>
                        <td>{{ $group['totalUnits'] }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endforeach
    
    <div class="overall-total">
        Overall Total Units: {{ $total_units }}
    </div>
    
    <div class="footer">
        <p>EVSU Enrollment System | Generated Electronically</p>
        <p>This document is system-generated and does not require a signature.</p>
    </div>
</body>
</html>