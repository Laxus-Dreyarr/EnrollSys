<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Enrolled Students</title>
    <style>
        @page { 
            margin: 50px 25px; 
            footer: html_footer;
        }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 12px; 
            margin: 0;
            padding: 0;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #333; 
            padding-bottom: 10px; 
        }
        .header h1 { 
            margin: 0; 
            color: #2c3e50; 
            font-size: 24px; 
        }
        .header .subtitle { 
            margin: 5px 0; 
            color: #7f8c8d; 
            font-size: 14px; 
        }
        .student-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 30px; 
        }
        .student-table th { 
            background-color: #2c3e50; 
            color: white; 
            text-align: left; 
            padding: 8px; 
            border: 1px solid #ddd;
        }
        .student-table td { 
            padding: 8px; 
            border-bottom: 1px solid #ddd; 
            border-left: 1px solid #ddd;
            border-right: 1px solid #ddd;
        }
        .student-table tr:nth-child(even) { 
            background-color: #f9f9f9; 
        }
        .group-header { 
            background-color: #3498db; 
            color: white; 
            padding: 10px; 
            margin-top: 20px; 
            font-weight: bold; 
            font-size: 14px; 
            page-break-inside: avoid;
        }
        .no-data { 
            text-align: center; 
            padding: 20px; 
            color: #7f8c8d; 
            font-style: italic; 
        }
        .student-count { 
            text-align: right; 
            margin-bottom: 10px; 
            font-weight: bold; 
            color: #2c3e50; 
            page-break-inside: avoid;
        }
        .page-break { 
            page-break-after: always; 
        }
        
        /* Footer styles */
        .footer {
            text-align: center;
            font-size: 10px;
            color: #7f8c8d;
            width: 100%;
            position: fixed;
            bottom: -30px;
            left: 0;
        }
        .pagenum:before {
            content: counter(page);
        }
        .pagecount:before {
            content: counter(pages);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LIST OF OFFICIALLY ENROLLED STUDENTS</h1>
        <div class="subtitle">Academic Year {{ $academicYear }}</div>
        <div class="subtitle">Generated on {{ $generatedDate }}</div>
    </div>

    @php
        $totalStudents = 0;
        $yearLevelCount = 0;
    @endphp

    @foreach($groupedStudents as $yearLevel => $semesters)
        @php
            $yearLevelCount++;
        @endphp
        
        @if($yearLevelCount > 1)
            <div style="page-break-before: always;"></div>
        @endif
        
        <div class="group-header">
            {{ strtoupper($yearLevel) }} STUDENTS
        </div>
        
        @foreach($semesters as $semester => $students)
            @php
                $totalInSemester = count($students);
                $totalStudents += $totalInSemester;
            @endphp
            
            <div class="student-count">Semester: {{ $semester }} ({{ $totalInSemester }} students)</div>
            
            <table class="student-table">
                <thead>
                    <tr>
                        <th width="5%">No.</th>
                        <th width="15%">Student ID</th>
                        <th width="25%">Last Name</th>
                        <th width="25%">First Name</th>
                        <th width="20%">Middle Name</th>
                        <th width="10%">Curriculum</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index => $student)
                        <tr>
                            <td>{{ $index + 1 }}.</td>
                            <td>{{ $student->id_no ?? 'N/A' }}</td>
                            <td>{{ $student->lastname ?? 'N/A' }}</td>
                            <td>{{ $student->firstname ?? 'N/A' }}</td>
                            <td>{{ $student->middlename ?? 'N/A' }}</td>
                            <td>{{ $student->curriculum ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="no-data">No students found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endforeach
    @endforeach

    @if(count($groupedStudents) == 0)
        <div class="no-data" style="margin-top: 50px;">
            No enrolled students found.
        </div>
    @else
        <div style="text-align: right; margin-top: 30px; font-weight: bold;">
            Total Enrolled Students: {{ $totalStudents }}
        </div>
    @endif

    <!-- Footer for PDF -->
    <htmlpagefooter name="footer">
        <div class="footer">
            Page <span class="pagenum"></span> of <span class="pagecount"></span> | Enrollment System | EVSU
        </div>
    </htmlpagefooter>
</body>
</html>