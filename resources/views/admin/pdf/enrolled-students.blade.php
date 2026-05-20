<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Enrolled Students</title>
    <style>
        @page { 
            margin: 40px 25px; 
            footer: html_footer;
        }
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 11px; 
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #333; 
            padding-bottom: 10px; 
        }
        .header h1 { 
            margin: 0 0 5px 0; 
            color: #2c3e50; 
            font-size: 18px; 
            font-weight: bold;
        }
        .header .subtitle { 
            margin: 3px 0; 
            color: #555; 
            font-size: 12px; 
        }
        .student-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
        }
        .student-table th { 
            background-color: #2c3e50; 
            color: white; 
            text-align: left; 
            padding: 8px 6px; 
            border: 1px solid #ddd;
            font-size: 11px;
            font-weight: bold;
        }
        .student-table td { 
            padding: 8px 6px; 
            border: 1px solid #ddd;
            font-size: 11px;
            vertical-align: middle;
        }
        .student-table tr:nth-child(even) { 
            background-color: #f8f9fa; 
        }
        .student-table tr:hover { 
            background-color: #e9ecef; 
        }
        .group-header { 
            background-color: #2c3e50;
            color: white; 
            padding: 10px 12px; 
            margin: 25px 0 12px 0;
            font-weight: bold; 
            font-size: 13px; 
            border-radius: 4px;
            border-left: 5px solid #3498db;
            page-break-inside: avoid;
        }
        .no-data { 
            text-align: center; 
            padding: 30px; 
            color: #6c757d; 
            font-style: italic; 
            font-size: 12px;
        }
        .total-summary {
            text-align: right;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 2px solid #2c3e50;
            font-weight: bold;
            color: #2c3e50;
            font-size: 13px;
        }
        .page-break { 
            page-break-after: always; 
        }
        
        /* Footer styles */
        .footer {
            text-align: center;
            font-size: 9px;
            color: #6c757d;
            width: 100%;
            position: fixed;
            bottom: -20px;
            left: 0;
        }
        .pagenum:before {
            content: counter(page);
        }
        .pagecount:before {
            content: counter(pages);
        }
        
        /* Numeric columns alignment */
        .numeric-column {
            text-align: center;
            font-weight: bold;
            color: #2c3e50;
        }
        
        /* Student ID styling */
        .student-id {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
        
        /* Ensure no page break inside rows */
        tr {
            page-break-inside: avoid;
        }
        
        /* Name column widths */
        .name-col {
            min-width: 100px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LIST OF OFFICIALLY ENROLLED STUDENTS</h1>
        <div class="subtitle">Academic Year: <strong>{{ $academicYear }}</strong></div>
        <div class="subtitle">Generated on: {{ $generatedDate }}</div>
    </div>

    @php
        $totalStudents = 0;
        $yearLevelCount = 0;
        $yearLevelOrder = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
        
        // Reorder groupedStudents according to the desired order
        $orderedGroupedStudents = [];
        foreach ($yearLevelOrder as $yearLevel) {
            if (isset($groupedStudents[$yearLevel])) {
                $orderedGroupedStudents[$yearLevel] = $groupedStudents[$yearLevel];
            }
        }
        
        // Add any remaining year levels that weren't in the predefined order
        foreach ($groupedStudents as $yearLevel => $students) {
            if (!in_array($yearLevel, $yearLevelOrder)) {
                $orderedGroupedStudents[$yearLevel] = $students;
            }
        }
    @endphp

    @foreach($orderedGroupedStudents as $yearLevel => $students)
        @php
            $yearLevelCount++;
            $totalInYear = count($students);
            $totalStudents += $totalInYear;
        @endphp
        
        @if($yearLevelCount > 1)
            <div style="page-break-before: always;"></div>
        @endif
        
        <div class="group-header">
            {{ strtoupper($yearLevel) }} - {{ $totalInYear }} STUDENT(S)
        </div>
        
        <table class="student-table">
            <thead>
                <tr>
                    <th width="5%">No.</th>
                    <th width="15%">Student ID</th>
                    <th width="20%" class="name-col">Last Name</th>
                    <th width="20%" class="name-col">First Name</th>
                    <th width="15%" class="name-col">Middle Name</th>
                    <th width="12%">Total Subjects</th>
                    <th width="13%">Total Units</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $index => $student)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}.</td>
                        <td class="student-id">{{ $student->id_no ?? 'N/A' }}</td>
                        <td>{{ $student->lastname ?? 'N/A' }}</td>
                        <td>{{ $student->firstname ?? 'N/A' }}</td>
                        <td>{{ $student->middlename ?? 'N/A' }}</td>
                        <td class="numeric-column">{{ $student->total_subjects ?? '0' }}</td>
                        <td class="numeric-column">{{ $student->total_units ?? '0' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="no-data">No students found for this year level</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endforeach

    @if(count($orderedGroupedStudents) == 0)
        <div class="no-data" style="margin-top: 50px;">
            No enrolled students found.
        </div>
    @else
        <div class="total-summary">
            TOTAL ENROLLED STUDENTS: {{ $totalStudents }}
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