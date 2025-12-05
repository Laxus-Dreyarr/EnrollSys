<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Prospectus - {{ $student['id_no'] ?? '' }}</title>
    <style>
        /* Base Styles */
        @page {
            margin: 20mm 15mm;
            size: A4;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            font-size: 10pt;
            line-height: 1.4;
            justify-content: center;
            align-items: center;
            align-content: center;
        }
        
        /* Container */
        .container {
            width: 100%;
            max-width: 100%; /* A4 width */
            margin: 0 auto;
            /* padding: mm; */
            box-sizing: border-box;
            justify-content: center;
            align-items: center;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 8mm;
            border-bottom: 2pt solid #1f2937;
            padding-bottom: 5mm;
        }
        
        .university-name {
            font-size: 14pt;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
            text-transform: uppercase;
        }
        
        .university-address {
            font-size: 9pt;
            color: #666;
            margin: 2pt 0;
        }
        
        .document-title {
            font-size: 16pt;
            font-weight: 700;
            color: #222;
            margin: 5mm 0 3mm 0;
        }
        
        .document-subtitle {
            font-size: 11pt;
            color: #555;
            margin: 2pt 0;
        }
        
        /* Student Information Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 4mm;
            margin-bottom: 8mm;
            padding: 5mm;
            background: #f8f9fa;
            border: 1pt solid #e0e0e0;
            border-radius: 3pt;
        }
        
        .info-item {
            margin-bottom: 2mm;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
            display: inline-block;
            width: 45mm;
        }
        
        .info-value {
            color: #222;
        }
        
        /* Table Styling */
        .semester-section {
            margin-top: 8mm;
            page-break-inside: avoid;
        }
        
        .semester-title {
            font-size: 12pt;
            font-weight: 700;
            background: #581a1aff;
            color: white;
            padding: 3mm 4mm;
            border-radius: 2pt 2pt 0 0;
            margin: 0;
        }
        
        .semester-units {
            float: right;
            font-weight: normal;
            font-size: 10pt;
            color: #d1d5db;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            table-layout: fixed;
        }
        
        th {
            background: #6b0a0aff;
            color: white;
            font-weight: 600;
            text-align: left;
            padding: 2.5mm;
            border: 1pt solid #4b5563;
            font-size: 9pt;
            vertical-align: middle;
        }
        
        td {
            padding: 2mm;
            border: 0.5pt solid #e5e7eb;
            font-size: 9pt;
            vertical-align: top;
            word-wrap: break-word;
            hyphens: auto;
        }
        
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        /* Column Widths */
        .col-code {
            width: 20mm;
        }
        
        .col-title {
            width: auto;
        }
        
        .col-units {
            width: 15mm;
            text-align: center;
        }
        
        .col-grade {
            width: 20mm;
            text-align: center;
        }
        
        .col-prereq {
            width: 30mm;
        }
        
        /* Grade Styling */
        .grade {
            display: inline-block;
            padding: 1mm 2mm;
            border-radius: 2pt;
            font-weight: 600;
            min-width: 8mm;
            text-align: center;
        }
        
        .grade-pending {
            background: #f3f4f6;
            color: #6b7280;
        }
        
        .grade-passed {
            background: #d1fae5;
            color: #065f46;
        }
        
        .grade-failed {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .grade-inc {
            background: #fed7aa;
            color: #9a3412;
        }
        
        /* Summary Section */
        .summary {
            margin-top: 10mm;
            padding: 5mm;
            background: #e3f2fd;
            border: 1pt solid #bbdefb;
            border-radius: 3pt;
            page-break-inside: avoid;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 4mm;
        }
        
        .summary-item {
            text-align: center;
            padding: 2mm;
        }
        
        .summary-value {
            font-size: 16pt;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1mm;
        }
        
        .summary-label {
            font-size: 9pt;
            color: #4b5563;
        }
        
        /* Signature Section */
        .signature-section {
            margin-top: 15mm;
            page-break-inside: avoid;
        }
        
        .signature-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10mm;
            margin-top: 8mm;
        }
        
        .signature-box {
            text-align: center;
        }
        
        .signature-line {
            width: 80mm;
            height: 0;
            border-top: 1pt solid #000;
            margin: 15mm auto 2mm;
        }
        
        .signature-name {
            font-size: 10pt;
            font-weight: 600;
            margin-top: 1mm;
        }
        
        .signature-role {
            font-size: 9pt;
            color: #666;
        }
        
        /* Footer */
        .footer {
            margin-top: 15mm;
            padding-top: 3mm;
            border-top: 0.5pt solid #e0e0e0;
            font-size: 8pt;
            color: #666;
            text-align: center;
        }
        
        .page-number {
            position: running(footer);
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
        
        /* Page Break Control */
        .page-break {
            page-break-before: always;
        }
        
        .keep-together {
            page-break-inside: avoid;
        }
        
        /* No Data */
        .no-data {
            text-align: center;
            padding: 10mm;
            background: #f9fafb;
            border: 1pt dashed #d1d5db;
            border-radius: 3pt;
            margin: 5mm 0;
        }
        
        .no-data h3 {
            color: #6b7280;
            margin-bottom: 2mm;
        }
        
        /* Print Optimizations */
        @media print {
            body {
                font-size: 9.5pt;
            }
            
            .container {
                padding: 0;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            table {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            /* Force full width for print */
            table, th, td {
                border-color: #000 !important;
            }
            
            /* Remove background colors for better printing */
            .grade {
                border: 0.5pt solid #ccc !important;
            }
        }
        
        /* Utility Classes */
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .bold {
            font-weight: 700;
        }
        
        .italic {
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1 class="university-name">EASTERN VISAYAS STATE UNIVERSITY</h1>
            <p class="university-address">Republic of the Philippines</p>
            <p class="university-address">Office of the University Registrar</p>
            
            <h2 class="document-title">ENROLLMENT PROSPECTUS</h2>
            <p class="document-subtitle">Bachelor of Science in Information Technology</p>
            <p class="document-subtitle">Curriculum: {{ $curriculumYear }}</p>
        </div>

        <!-- Student Information -->
        <div class="info-grid keep-together">
            <div class="info-item">
                <span class="info-label">Student Name:</span>
                <span class="info-value">{{ $student['firstname'] ?? '' }} {{ $student['middlename'] ?? '' }} {{ $student['lastname'] ?? '' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Student ID:</span>
                <span class="info-value">{{ $student['id_no'] ?? '' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Year Level:</span>
                <span class="info-value">{{ $student['year_level'] ?? '' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Enrollment Status:</span>
                <span class="info-value">{{ $student['status'] ?? '' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Academic Year:</span>
                <span class="info-value">{{ $enrollmentAcademicYear ?? '2025-2026' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Semester:</span>
                <span class="info-value">{{ $enrollmentPeriod['semester'] ?? '1st Semester' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Date Generated:</span>
                <span class="info-value">{{ $dateGenerated }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Curriculum:</span>
                <span class="info-value">{{ $curriculumYear }}</span>
            </div>
        </div>

        <!-- Academic Record -->
        <h3 class="document-subtitle" style="margin-bottom: 5mm; font-weight: 600;">ACADEMIC RECORD</h3>

        @php
            $pageCount = 0;
            $subjectCount = 0;
            $maxSubjectsPerPage = 20; // Adjust based on your content
        @endphp

        @foreach($yearLevelOrder as $yearLevel)
            @if(isset($organizedSubjects[$yearLevel]))
                @foreach($semesterOrder as $semester)
                    @if(isset($organizedSubjects[$yearLevel][$semester]) && count($organizedSubjects[$yearLevel][$semester]) > 0)
                        @php
                            $semesterSubjects = $organizedSubjects[$yearLevel][$semester];
                            $semesterUnits = array_sum(array_column($semesterSubjects, 'units'));
                            
                            // Check if we need a page break
                            if($subjectCount > $maxSubjectsPerPage) {
                                echo '<div class="page-break"></div>';
                                $subjectCount = 0;
                            }
                            
                            $subjectCount += count($semesterSubjects);
                        @endphp
                        
                        <div class="semester-section">
                            <div class="semester-title">
                                {{ $yearLevel }} – {{ $semester }}
                                <span class="semester-units">{{ $semesterUnits }} units</span>
                            </div>
                            
                            <table>
                                <thead>
                                    <tr>
                                        <th class="col-code">Course Code</th>
                                        <th class="col-title">Descriptive Title</th>
                                        <th class="col-units">Units</th>
                                        <th class="col-grade">Grade</th>
                                        <th class="col-prereq">Prerequisite(s)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($semesterSubjects as $subject)
                                        @php
                                            $gradeDisplay = $subject['grade'] ?? '';
                                            $gradeClass = 'grade-pending';
                                            
                                            if ($subject['grade']) {
                                                $gradeNum = floatval($subject['grade']);
                                                if ($subject['grade'] === 'INC' || $subject['grade'] === '4.00') {
                                                    $gradeClass = 'grade-inc';
                                                } elseif ($subject['grade'] === '5.00' || $gradeNum > 3.00) {
                                                    $gradeClass = 'grade-failed';
                                                } elseif ($gradeNum <= 3.00 && $gradeNum >= 1.00) {
                                                    $gradeClass = 'grade-passed';
                                                }
                                            }
                                        @endphp
                                        
                                        <tr>
                                            <td class="col-code">{{ $subject['subject_code'] ?? '' }}</td>
                                            <td class="col-title">{{ $subject['subject_name'] ?? '' }}</td>
                                            <td class="col-units text-center">{{ $subject['units'] ?? '' }}</td>
                                            <td class="col-grade">
                                                <span class="grade {{ $gradeClass }}">
                                                    {{ $gradeDisplay ?: '-' }}
                                                </span>
                                            </td>
                                            <td class="col-prereq">{{ $subject['prerequisites'] ?? 'None' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if($totalSubjects == 0)
            <div class="no-data">
                <h3>No Enrolled Subjects</h3>
                <p>This student has not enrolled in any subjects for the current academic year.</p>
            </div>
        @endif

        <!-- Summary -->
        <div class="summary keep-together">
            <div style="margin-bottom: 3mm; font-weight: 600; color: #1f2937;">ENROLLMENT SUMMARY</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-value">{{ $totalUnits }}</div>
                    <div class="summary-label">Total Units</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">{{ $totalSubjects }}</div>
                    <div class="summary-label">Total Subjects</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">{{ $subjectsWithGrades }}</div>
                    <div class="summary-label">Grades Posted</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">
                        @php
                            $semesterCount = 0;
                            foreach($organizedSubjects as $yearSubjects) {
                                $semesterCount += count($yearSubjects);
                            }
                            echo $semesterCount;
                        @endphp
                    </div>
                    <div class="summary-label">Active Semesters</div>
                </div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="signature-section keep-together">
            <div class="signature-grid">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-name">UNIVERSITY REGISTRAR</div>
                    <div class="signature-role">Authorized Signature</div>
                    <div style="margin-top: 3mm; font-size: 9pt;">
                        Date: __________________
                    </div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-name">STUDENT</div>
                    <div class="signature-role">Signature Over Printed Name</div>
                    <div style="margin-top: 3mm; font-size: 9pt;">
                        Date: __________________
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 10mm; padding: 3mm; background: #f3f4f6; border-radius: 2pt; font-size: 9pt;">
                <strong>Remarks:</strong> This prospectus is generated by the EVSU Enrollment System and serves as an official record of enrollment. All grades are subject to verification by the Registrar's Office.
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="page-number">Page <span class="pagenum"></span></div>
            <p>Generated by EVSU Enrollment System</p>
            <p>This document is valid only with the official stamp of the Registrar's Office</p>
            <p>Date Generated: {{ $dateGenerated }}</p>
        </div>
    </div>

    <script>
        // Add page numbers for PDF
        document.addEventListener('DOMContentLoaded', function() {
            var pages = document.getElementsByClassName('container');
            for (var i = 0; i < pages.length; i++) {
                var pageNum = i + 1;
                var pagenumElements = pages[i].getElementsByClassName('pagenum');
                for (var j = 0; j < pagenumElements.length; j++) {
                    pagenumElements[j].textContent = pageNum;
                }
            }
        });
    </script>
</body>
</html>