<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Student;
use App\Models\Subject;

class GradeApiController extends Controller
{
    /**
     * Health check endpoint
     */
    public function healthCheck()
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now(),
            'service' => 'EVSU Enrollment System API',
            'version' => '1.0.0'
        ]);
    }

    /**
     * Get failed grades (4.0, 5.0, INC, DRP)
     */
    // public function getFailedGrades(Request $request)
    // {
    //     // Validate query parameters
    //     $validator = Validator::make($request->all(), [
    //         'year_level' => 'nullable|in:1st Year,2nd Year,3rd Year,4th Year,5th Year',
    //         'semester' => 'nullable|in:1st Sem,2nd Sem,Summer',
    //         'date_enrolled' => 'nullable|date',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'errors' => $validator->errors()
    //         ], 400);
    //     }

    //     // Create cache key based on request parameters
    //     $cacheKey = 'failed_grades_' . md5(serialize($request->all()));
        
    //     // Cache for 1 hour (optional)
    //     $failedGrades = Cache::remember($cacheKey, 3600, function () use ($request) {
    //         return DB::table('enrolled_sub as es')
    //             ->join('students as s', 'es.student_id', '=', 's.id')
    //             ->join('user_info as ui', 's.student_id', '=', 'ui.id')
    //             ->join('users as u', 'ui.user_id', '=', 'u.id')
    //             ->join('subjects as sub', 'es.subject_id', '=', 'sub.id')
    //             ->select([
    //                 'es.student_id',
    //                 's.id_no as student_number',
    //                 DB::raw("CONCAT(ui.lastname, ', ', ui.firstname) as student_name"),
    //                 'u.email2 as student_email',
    //                 'sub.code as subject_code',
    //                 'sub.name as subject_name',
    //                 'es.grade',
    //                 'es.year_level',
    //                 'es.semester',
    //                 'es.units',
    //                 DB::raw("DATE_FORMAT(es.date_enrolled, '%Y-%m-%d') as enrollment_date"),
    //                 's.status as student_status',
    //                 's.curriculum',
    //                 's.is_regular'
    //             ])
    //             ->whereIn('es.grade', ['4.0', '5.0', 'INC', 'DRP'])
    //             ->whereNotNull('es.grade')
    //             ->when($request->year_level, function ($query, $yearLevel) {
    //                 return $query->where('es.year_level', $yearLevel);
    //             })
    //             ->when($request->semester, function ($query, $semester) {
    //                 return $query->where('es.semester', $semester);
    //             })
    //             ->when($request->curriculum, function ($query, $curriculum) {
    //                 return $query->where('s.curriculum', $curriculum);
    //             })
    //             ->when($request->start_date, function ($query, $startDate) {
    //                 return $query->whereDate('es.date_enrolled', '>=', $startDate);
    //             })
    //             ->when($request->end_date, function ($query, $endDate) {
    //                 return $query->whereDate('es.date_enrolled', '<=', $endDate);
    //             })
    //             // ->where('s.status', 'Officially Enrolled') // Only currently enrolled students
    //             ->orderBy('es.year_level')
    //             ->orderBy('es.semester')
    //             ->orderBy('ui.lastname')
    //             ->get();
    //     });

    //     // Calculate statistics
    //     $stats = [
    //         'total_failed_students' => $failedGrades->unique('student_id')->count(),
    //         'total_failed_subjects' => $failedGrades->count(),
    //         'by_year_level' => $failedGrades->groupBy('year_level')->map->count(),
    //         'by_grade' => $failedGrades->groupBy('grade')->map->count(),
    //         'by_semester' => $failedGrades->groupBy('semester')->map->count(),
    //     ];

    //     return response()->json([
    //         'success' => true,
    //         'metadata' => [
    //             'count' => $failedGrades->count(),
    //             'generated_at' => now()->toDateTimeString(),
    //             'parameters' => $request->all(),
    //         ],
    //         'statistics' => $stats,
    //         'data' => $failedGrades,
    //     ]);
    // }


    public function getFailedGrades(Request $request)
    {
        // Validate query parameters
        $validator = Validator::make($request->all(), [
            'year_level' => 'nullable|in:1st Year,2nd Year,3rd Year,4th Year,5th Year',
            'semester' => 'nullable|in:1st Sem,2nd Sem,Summer',
            'date_enrolled' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        // Execute query directly without caching
        $failedGrades = DB::table('enrolled_sub as es')
            ->join('students as s', 'es.student_id', '=', 's.id')
            ->join('user_info as ui', 's.student_id', '=', 'ui.id')
            ->join('users as u', 'ui.user_id', '=', 'u.id')
            ->join('subjects as sub', 'es.subject_id', '=', 'sub.id')
            ->select([
                'es.student_id',
                's.id_no as student_number',
                DB::raw("CONCAT(ui.lastname, ', ', ui.firstname) as student_name"),
                'u.email2 as student_email',
                'sub.code as subject_code',
                'sub.name as subject_name',
                'es.grade',
                'es.year_level',
                'es.semester',
                'es.units',
                DB::raw("DATE_FORMAT(es.date_enrolled, '%Y-%m-%d') as enrollment_date"),
                's.status as student_status',
                's.curriculum',
                's.is_regular'
            ])
            ->whereIn('es.grade', ['4.0', '5.0', 'INC', 'DRP'])
            ->whereNotNull('es.grade')
            ->when($request->year_level, function ($query, $yearLevel) {
                return $query->where('es.year_level', $yearLevel);
            })
            ->when($request->semester, function ($query, $semester) {
                return $query->where('es.semester', $semester);
            })
            ->when($request->curriculum, function ($query, $curriculum) {
                return $query->where('s.curriculum', $curriculum);
            })
            ->when($request->start_date, function ($query, $startDate) {
                return $query->whereDate('es.date_enrolled', '>=', $startDate);
            })
            ->when($request->end_date, function ($query, $endDate) {
                return $query->whereDate('es.date_enrolled', '<=', $endDate);
            })
            ->orderBy('es.year_level')
            ->orderBy('es.semester')
            ->orderBy('ui.lastname')
            ->get();

        // Calculate statistics
        $stats = [
            'total_failed_students' => $failedGrades->unique('student_id')->count(),
            'total_failed_subjects' => $failedGrades->count(),
            'by_year_level' => $failedGrades->groupBy('year_level')->map->count(),
            'by_grade' => $failedGrades->groupBy('grade')->map->count(),
            'by_semester' => $failedGrades->groupBy('semester')->map->count(),
        ];

        return response()->json([
            'success' => true,
            'metadata' => [
                'count' => $failedGrades->count(),
                'generated_at' => now()->toDateTimeString(),
                'parameters' => $request->all(),
            ],
            'statistics' => $stats,
            'data' => $failedGrades,
        ]);
    }

    /**
     * Get retention alerts with more detailed information
     */
    public function getRetentionAlerts(Request $request)
    {
        $failedGrades = DB::table('enrolled_sub as es')
            ->join('students as s', 'es.student_id', '=', 's.id')
            ->join('user_info as ui', 's.student_id', '=', 'ui.id')
            ->join('users as u', 'ui.user_id', '=', 'u.id')
            ->join('subjects as sub', 'es.subject_id', '=', 'sub.id')
            ->select([
                'es.student_id',
                's.id_no as student_number',
                'ui.firstname',
                'ui.lastname',
                'u.email2 as student_email',
                'sub.code as subject_code',
                'sub.name as subject_name',
                'es.grade',
                'es.year_level',
                'es.semester',
                'es.units',
                's.curriculum',
                DB::raw("DATE_FORMAT(es.date_enrolled, '%Y-%m-%d') as enrollment_date"),
                // Calculate academic standing
                DB::raw("CASE 
                    WHEN es.grade = 'INC' THEN 'Incomplete'
                    WHEN es.grade = '4.0' THEN 'Failed'         
                    WHEN es.grade = '5.0' THEN 'Failed'
                    WHEN es.grade = 'DRP' THEN 'Dropped'
                    ELSE 'Unknown'
                END as status"),
                // Risk level assessment
                DB::raw("CASE 
                    WHEN es.grade = '4.0' THEN 'High Risk'    
                    WHEN es.grade = '5.0' THEN 'High Risk'
                    WHEN es.grade = 'INC' AND es.year_level = '4th Year' THEN 'High Risk'
                    WHEN es.grade = 'INC' THEN 'Medium Risk'
                    ELSE 'Low Risk'
                END as risk_level")
            ])
            ->whereIn('es.grade', ['4.0', '5.0', 'INC', 'DRP'])
            ->whereNotNull('es.grade')
            // ->where('s.status', 'Officially Enrolled')
            ->orderBy('risk_level', 'asc')
            ->orderBy('es.year_level')
            ->orderBy('ui.lastname')
            ->get();

        // Group by student for better analysis
        $students = $failedGrades->groupBy('student_id')->map(function ($grades, $studentId) {
            $firstGrade = $grades->first();
            return [
                'student_id' => $studentId,
                'student_number' => $firstGrade->student_number,
                'student_name' => $firstGrade->firstname . ' ' . $firstGrade->lastname,
                'student_email' => $firstGrade->student_email,
                'year_level' => $firstGrade->year_level,
                'curriculum' => $firstGrade->curriculum,
                'total_failed_subjects' => $grades->count(),
                'failed_subjects' => $grades->map(function ($grade) {
                    return [
                        'subject_code' => $grade->subject_code,
                        'subject_name' => $grade->subject_name,
                        'grade' => $grade->grade,
                        'status' => $grade->status,
                        'risk_level' => $grade->risk_level,
                        'semester' => $grade->semester,
                        'enrollment_date' => $grade->enrollment_date,
                    ];
                })->values(),
                'highest_risk_level' => $grades->max('risk_level'),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'alerts' => $students,
            'summary' => [
                'total_students_at_risk' => $students->count(),
                'high_risk_students' => $students->where('highest_risk_level', 'High Risk')->count(),
                'medium_risk_students' => $students->where('highest_risk_level', 'Medium Risk')->count(),
                'generated_at' => now()->toDateTimeString(),
            ]
        ]);
    }

    /**
     * Get grades for specific student
     */
    public function getStudentGrades($id)
    {
        $grades = DB::table('enrolled_sub as es')
            ->join('subjects as sub', 'es.subject_id', '=', 'sub.id')
            ->select([
                'sub.code',
                'sub.name',
                'es.grade',
                'es.year_level',
                'es.semester',
                'es.units',
                DB::raw("DATE_FORMAT(es.date_enrolled, '%Y-%m-%d') as date_enrolled")
            ])
            ->where('es.student_id', $id)
            ->orderBy('es.year_level')
            ->orderBy('es.semester')
            ->orderBy('sub.code')
            ->get();

        // Calculate GPA
        $totalGradePoints = 0;
        $totalUnits = 0;
        
        foreach ($grades as $grade) {
            if (is_numeric($grade->grade)) {
                $totalGradePoints += ((float)$grade->grade * $grade->units);
                $totalUnits += $grade->units;
            }
        }
        
        $gpa = $totalUnits > 0 ? $totalGradePoints / $totalUnits : 0;

        return response()->json([
            'success' => true,
            'student_id' => $id,
            'gpa' => round($gpa, 2),
            'total_units' => $totalUnits,
            'grades' => $grades,
        ]);
    }

    /**
     * Generate API key for your classmate
     * Note: This should be protected by admin auth in real implementation
     */
    public function generateApiKey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_name' => 'required|string|max:255',
            'email' => 'required|email',
            'expires_in_days' => 'nullable|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Generate API key
        $apiKey = 'evsu_' . Str::random(40);
        $expiresAt = now()->addDays($request->expires_in_days ?? 30);

        // Store in database (create this table first)
        DB::table('api_keys')->insert([
            'key' => hash('sha256', $apiKey), // Store hash, not plain text
            'client_name' => $request->client_name,
            'email' => $request->email,
            'created_by' => auth()->id() ?? 0,
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'API key generated successfully',
            'api_key' => $apiKey, // Return plain text only once
            'expires_at' => $expiresAt->toDateTimeString(),
            'usage_instructions' => [
                'header' => 'X-API-Key: ' . $apiKey,
                'endpoints' => [
                    'GET /api/failed-grades' => 'Get all failed grades',
                    'GET /api/retention-alerts' => 'Get retention risk analysis',
                    'GET /api/student/{id}/grades' => 'Get specific student grades',
                ]
            ]
        ]);
    }

    /**
     * Get only INC grades
     */
    public function getIncGrades(Request $request)
    {
        $incGrades = DB::table('enrolled_sub as es')
            ->join('students as s', 'es.student_id', '=', 's.id')
            ->join('user_info as ui', 's.student_id', '=', 'ui.id')
            ->join('users as u', 'ui.user_id', '=', 'u.id')
            ->join('subjects as sub', 'es.subject_id', '=', 'sub.id')
            ->select([
                'es.student_id',
                's.id_no as student_number',
                DB::raw("CONCAT(ui.lastname, ', ', ui.firstname) as student_name"),
                'u.email2 as student_email',
                'sub.code as subject_code',
                'sub.name as subject_name',
                'es.year_level',
                'es.semester',
                DB::raw("DATE_FORMAT(es.date_enrolled, '%Y-%m-%d') as enrollment_date"),
                DB::raw("DATEDIFF(NOW(), es.date_enrolled) as days_since_enrollment")
            ])
            ->where('es.grade', 'INC')
            // ->where('s.status', 'Officially Enrolled')
            ->orderBy('es.year_level')
            ->orderBy('days_since_enrollment', 'desc') // Oldest INC first
            ->get();

        return response()->json([
            'success' => true,
            'count' => $incGrades->count(),
            'data' => $incGrades,
            'generated_at' => now()->toDateTimeString(),
        ]);
    }


    public function getAllGrades(Request $request)
    {
        // Validate query parameters
        $validator = Validator::make($request->all(), [
            'student_id' => 'nullable|integer',
            'year_level' => 'nullable|in:1st Year,2nd Year,3rd Year,4th Year,5th Year',
            'semester' => 'nullable|in:1st Sem,2nd Sem,Summer',
            'curriculum' => 'nullable|string',
            'subject_code' => 'nullable|string',
            'status' => 'nullable|string|in:passed,failed,pending,all',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        $grades = DB::table('enrolled_sub as es')
            ->join('students as s', 'es.student_id', '=', 's.id')
            ->join('user_info as ui', 's.student_id', '=', 'ui.id')
            ->join('users as u', 'ui.user_id', '=', 'u.id')
            ->join('subjects as sub', 'es.subject_id', '=', 'sub.id')
            ->select([
                'es.student_id',
                's.id_no as student_number',
                DB::raw("CONCAT(ui.lastname, ', ', ui.firstname) as student_name"),
                'u.email2 as student_email',
                'sub.code as subject_code',
                'sub.name as subject_name',
                'es.grade',
                'es.year_level',
                'es.semester',
                'es.units',
                DB::raw("DATE_FORMAT(es.date_enrolled, '%Y-%m-%d') as enrollment_date"),
                's.status as student_status',
                's.curriculum',
                's.is_regular',
                // FIXED: MariaDB compatible grade classification
                DB::raw("CASE 
                    WHEN es.grade IS NULL OR es.grade = '' THEN 'No Grade'
                    WHEN es.grade IN ('4.0', '5.0', 'INC', 'DRP') THEN 'Failed'
                    WHEN es.grade REGEXP '^[0-9]+(\\.[0-9]+)?$' AND CAST(es.grade AS DECIMAL(4,2)) <= 3.0 THEN 'Passed'
                    ELSE 'Other'
                END as grade_status")
            ])
            ->when($request->student_id, function ($query, $studentId) {
                return $query->where('es.student_id', $studentId);
            })
            ->when($request->year_level, function ($query, $yearLevel) {
                return $query->where('es.year_level', $yearLevel);
            })
            ->when($request->semester, function ($query, $semester) {
                return $query->where('es.semester', $semester);
            })
            ->when($request->curriculum, function ($query, $curriculum) {
                return $query->where('s.curriculum', $curriculum);
            })
            ->when($request->subject_code, function ($query, $subjectCode) {
                return $query->where('sub.code', 'LIKE', "%{$subjectCode}%");
            })
            ->when($request->status && $request->status !== 'all', function ($query) use ($request) {
                if ($request->status === 'passed') {
                    return $query->whereNotNull('es.grade')
                        ->where('es.grade', 'NOT IN', ['4.0', '5.0', 'INC', 'DRP'])
                        ->whereRaw("es.grade REGEXP '^[0-9]+(\\.[0-9]+)?$'")
                        ->whereRaw('CAST(es.grade AS DECIMAL(4,2)) <= 3.0');
                } elseif ($request->status === 'failed') {
                    return $query->whereIn('es.grade', ['4.0', '5.0', 'INC', 'DRP']);
                } elseif ($request->status === 'pending') {
                    return $query->where(function($q) {
                        $q->whereNull('es.grade')
                        ->orWhere('es.grade', '');
                    });
                }
                return $query;
            })
            // ->where('s.status', 'Officially Enrolled')
            ->orderBy('es.student_id')
            ->orderBy('es.year_level')
            ->orderBy('es.semester')
            ->orderBy('sub.code')
            ->get();

        // Calculate statistics
        $stats = [
            'total_records' => $grades->count(),
            'passed_count' => $grades->where('grade_status', 'Passed')->count(),
            'failed_count' => $grades->where('grade_status', 'Failed')->count(),
            'no_grade_count' => $grades->where('grade_status', 'No Grade')->count(),
            'other_count' => $grades->where('grade_status', 'Other')->count(),
            'unique_students' => $grades->unique('student_id')->count(),
            'unique_subjects' => $grades->unique('subject_code')->count(),
        ];

        return response()->json([
            'success' => true,
            'metadata' => [
                'count' => $grades->count(),
                'generated_at' => now()->toDateTimeString(),
                'parameters' => $request->all(),
            ],
            'statistics' => $stats,
            'data' => $grades,
        ]);
    }


    public function getStudentCompleteGrades($id)
    {
        $student = DB::table('students as s')
            ->join('user_info as ui', 's.student_id', '=', 'ui.id')
            ->join('users as u', 'ui.user_id', '=', 'u.id')
            ->select([
                's.id as student_id',
                's.id_no as student_number',
                DB::raw("CONCAT(ui.firstname, ' ', ui.lastname) as full_name"),
                'u.email2 as student_email',
                's.year_level',
                's.curriculum',
                's.status',
                's.is_regular',
            ])
            ->where('s.id', $id)
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }

        $grades = DB::table('enrolled_sub as es')
            ->join('subjects as sub', 'es.subject_id', '=', 'sub.id')
            ->select([
                'sub.code as subject_code',
                'sub.name as subject_name',
                'es.grade',
                'es.year_level',
                'es.semester',
                'es.units',
                DB::raw("DATE_FORMAT(es.date_enrolled, '%Y-%m-%d') as date_enrolled"),
                // Classification - MARIA DB FIXED VERSION
                DB::raw("CASE 
                    WHEN es.grade IS NULL OR es.grade = '' THEN 'No Grade'
                    WHEN es.grade IN ('4.0', '5.0', 'INC', 'DRP') THEN 'Failed'
                    WHEN es.grade REGEXP '^[0-9]+(\\.[0-9]+)?$' AND CAST(es.grade AS DECIMAL(4,2)) <= 3.0 THEN 'Passed'
                    ELSE 'Other'
                END as status"),
                // Numeric grade for calculation - MARIA DB FIXED VERSION
                DB::raw("CASE 
                    WHEN es.grade IS NULL OR es.grade = '' THEN NULL
                    WHEN es.grade IN ('INC', 'DRP') THEN NULL
                    WHEN es.grade REGEXP '^[0-9]+(\\.[0-9]+)?$' THEN CAST(es.grade AS DECIMAL(4,2))
                    ELSE NULL
                END as numeric_grade")
            ])
            ->where('es.student_id', $id)
            ->orderBy('es.year_level')
            ->orderBy('es.semester')
            ->orderBy('sub.code')
            ->get();

        // Calculate GPA and statistics
        $totalGradePoints = 0;
        $totalUnits = 0;
        $passedUnits = 0;
        $failedUnits = 0;
        
        foreach ($grades as $grade) {
            if ($grade->numeric_grade !== null && $grade->numeric_grade <= 3.0) {
                $totalGradePoints += ($grade->numeric_grade * $grade->units);
                $totalUnits += $grade->units;
                $passedUnits += $grade->units;
            } elseif ($grade->status === 'Failed') {
                $failedUnits += $grade->units;
            }
        }
        
        $gpa = $totalUnits > 0 ? $totalGradePoints / $totalUnits : 0;

        // Group by semester/year
        $semesterSummary = $grades->groupBy(function ($grade) {
            return $grade->year_level . ' - ' . $grade->semester;
        })->map(function ($semesterGrades, $semesterKey) {
            return [
                'total_subjects' => $semesterGrades->count(),
                'passed_subjects' => $semesterGrades->where('status', 'Passed')->count(),
                'failed_subjects' => $semesterGrades->where('status', 'Failed')->count(),
                'no_grade_subjects' => $semesterGrades->where('status', 'No Grade')->count(),
                'total_units' => $semesterGrades->sum('units'),
                'subjects' => $semesterGrades->map(function ($grade) {
                    return [
                        'code' => $grade->subject_code,
                        'name' => $grade->subject_name,
                        'grade' => $grade->grade,
                        'units' => $grade->units,
                        'status' => $grade->status
                    ];
                })->values()
            ];
        });

        return response()->json([
            'success' => true,
            'student_info' => $student,
            'academic_summary' => [
                'gpa' => round($gpa, 2),
                'total_units_enrolled' => $grades->sum('units'),
                'total_subjects_enrolled' => $grades->count(),
                'passed_units' => $passedUnits,
                'failed_units' => $failedUnits,
                'passed_subjects' => $grades->where('status', 'Passed')->count(),
                'failed_subjects' => $grades->where('status', 'Failed')->count(),
                'no_grade_subjects' => $grades->where('status', 'No Grade')->count(),
                'completion_rate' => $grades->sum('units') > 0 ? round(($passedUnits / $grades->sum('units')) * 100, 2) : 0,
            ],
            'semester_breakdown' => $semesterSummary,
            'all_grades' => $grades,
        ]);
    }


    /**
     * Get student grades by email (for external system integration)
     */
    public function getGradesByEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Find student by email
            $student = DB::table('users as u')
                ->join('user_info as ui', 'u.id', '=', 'ui.user_id')
                ->join('students as s', 'ui.id', '=', 's.student_id')
                ->where('u.email2', $request->email)
                ->select('s.id as student_id', 's.id_no as student_number')
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found with this email'
                ], 404);
            }

            // Get grades for this student
            return $this->getStudentGrades($student->student_id);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch grades',
                'error' => $e->getMessage()
            ], 500);
        }
    }




}