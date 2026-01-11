<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\UserInfo;
use App\Models\Subject;

class EnrollmentApiController extends Controller
{
    /**
     * Get enrolled students with their subjects
     */
    public function getEnrolledStudents(Request $request)
    {
        // Validate request parameters
        $request->validate([
            'academic_year' => 'nullable|string',
            'semester' => 'nullable|string',
            'status' => 'nullable|string|in:Enrolled,Pending,Dropped',
            'year_level' => 'nullable|string',
            'curriculum_id' => 'nullable|integer',
        ]);

        // Start building the query
        $query = DB::table('enrollments as e')
            ->join('students as s', 'e.student_id', '=', 's.id')
            ->join('user_info as ui', 's.student_id', '=', 'ui.id')
            ->join('subjects as subj', 'e.subject_id', '=', 'subj.id')
            ->join('sections as sec', 'e.section_id', '=', 'sec.id')
            ->leftJoin('instructor_info as ii', 'sec.instructor_id', '=', 'ii.instructor_id')
            ->select(
                'e.id as enrollment_id',
                'e.student_id',
                's.id_no',
                DB::raw("CONCAT(ui.firstname, ' ', ui.lastname) as student_name"),
                'ui.firstname',
                'ui.lastname',
                's.year_level',
                's.status as student_status',
                's.curriculum',
                'e.subject_id',
                'subj.code as subject_code',
                'subj.name as subject_name',
                'subj.units',
                'subj.year_level as subject_year_level',
                'subj.semester',
                'e.section_id',
                'sec.section_name',
                DB::raw("CONCAT(ii.firstname, ' ', ii.lastname) as instructor_name"),
                'e.grade',
                'e.status as enrollment_status',
                'e.enrollment_date'
            );

        // Apply filters if provided
        if ($request->has('academic_year')) {
            // You might need to join with another table for academic year
            // For now, filter by enrollment date
            $academicYear = $request->academic_year;
            if (strpos($academicYear, '-') !== false) {
                list($startYear, $endYear) = explode('-', $academicYear);
                $query->whereYear('e.enrollment_date', '>=', $startYear)
                      ->whereYear('e.enrollment_date', '<=', $endYear);
            }
        }

        if ($request->has('semester')) {
            $query->where('subj.semester', $request->semester);
        }

        if ($request->has('status')) {
            $query->where('e.status', $request->status);
        } else {
            // Default to enrolled students
            $query->where('e.status', 'Enrolled');
        }

        if ($request->has('year_level')) {
            $query->where('s.year_level', $request->year_level);
        }

        if ($request->has('curriculum_id')) {
            $query->where('subj.curriculum_id', $request->curriculum_id);
        }

        // Get results with pagination
        $perPage = $request->get('per_page', 50);
        $enrollments = $query->orderBy('e.enrollment_date', 'desc')
                            ->paginate($perPage);

        // Format the response
        $formattedData = [];
        foreach ($enrollments as $enrollment) {
            $formattedData[] = [
                'enrollment_id' => $enrollment->enrollment_id,
                'student' => [
                    'student_id' => $enrollment->student_id,
                    'id_no' => $enrollment->id_no,
                    'name' => $enrollment->student_name,
                    'firstname' => $enrollment->firstname,
                    'lastname' => $enrollment->lastname,
                    'year_level' => $enrollment->year_level,
                    'status' => $enrollment->student_status,
                    'curriculum' => $enrollment->curriculum,
                ],
                'subject' => [
                    'subject_id' => $enrollment->subject_id,
                    'code' => $enrollment->subject_code,
                    'name' => $enrollment->subject_name,
                    'units' => $enrollment->units,
                    'year_level' => $enrollment->subject_year_level,
                    'semester' => $enrollment->semester,
                ],
                'section' => [
                    'section_id' => $enrollment->section_id,
                    'section_name' => $enrollment->section_name,
                    'instructor' => $enrollment->instructor_name,
                ],
                'enrollment' => [
                    'grade' => $enrollment->grade,
                    'status' => $enrollment->enrollment_status,
                    'date' => $enrollment->enrollment_date,
                ]
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $formattedData,
            'pagination' => [
                'current_page' => $enrollments->currentPage(),
                'last_page' => $enrollments->lastPage(),
                'per_page' => $enrollments->perPage(),
                'total' => $enrollments->total(),
            ]
        ]);
    }

    /**
     * Get enrolled students grouped by student
     * Returns each student with all their enrolled subjects
     */
    public function getStudentsWithSubjects(Request $request)
    {
        $request->validate([
            'academic_year' => 'nullable|string',
            'semester' => 'nullable|string',
            'year_level' => 'nullable|string',
            'student_id' => 'nullable|integer',
        ]);

        // First, get all enrolled students
        $studentsQuery = DB::table('students as s')
            ->join('user_info as ui', 's.student_id', '=', 'ui.id')
            ->join('enrollments as e', 's.id', '=', 'e.student_id')
            ->where('e.status', 'Enrolled')
            ->select(
                's.id as student_id',
                's.id_no',
                DB::raw("CONCAT(ui.firstname, ' ', ui.lastname) as student_name"),
                'ui.firstname',
                'ui.lastname',
                's.year_level',
                's.status',
                's.curriculum'
            )
            ->distinct();

        // Apply filters
        if ($request->has('academic_year')) {
            // Filter by academic year if needed
        }

        if ($request->has('year_level')) {
            $studentsQuery->where('s.year_level', $request->year_level);
        }

        if ($request->has('student_id')) {
            $studentsQuery->where('s.id', $request->student_id);
        }

        $students = $studentsQuery->get();

        $result = [];
        
        foreach ($students as $student) {
            // Get all subjects for this student
            $subjects = DB::table('enrollments as e')
                ->join('subjects as subj', 'e.subject_id', '=', 'subj.id')
                ->join('sections as sec', 'e.section_id', '=', 'sec.id')
                ->leftJoin('instructor_info as ii', 'sec.instructor_id', '=', 'ii.instructor_id')
                ->where('e.student_id', $student->student_id)
                ->where('e.status', 'Enrolled')
                ->select(
                    'e.id as enrollment_id',
                    'subj.id as subject_id',
                    'subj.code as subject_code',
                    'subj.name as subject_name',
                    'subj.units',
                    'subj.semester',
                    'sec.section_name',
                    DB::raw("CONCAT(ii.firstname, ' ', ii.lastname) as instructor_name"),
                    'e.grade',
                    'e.enrollment_date'
                );

            if ($request->has('semester')) {
                $subjects->where('subj.semester', $request->semester);
            }

            $enrolledSubjects = $subjects->get();

            // Calculate total units
            $totalUnits = $enrolledSubjects->sum('units');

            $result[] = [
                'student_id' => $student->student_id,
                'id_no' => $student->id_no,
                'name' => $student->student_name,
                'firstname' => $student->firstname,
                'lastname' => $student->lastname,
                'year_level' => $student->year_level,
                'status' => $student->status,
                'curriculum' => $student->curriculum,
                'total_units' => $totalUnits,
                'subjects' => $enrolledSubjects,
                'subject_count' => $enrolledSubjects->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $result,
            'count' => count($result)
        ]);
    }

    /**
     * Get enrollment statistics
     */
    public function getEnrollmentStats(Request $request)
    {
        $stats = DB::table('enrollments as e')
            ->join('students as s', 'e.student_id', '=', 's.id')
            ->join('subjects as subj', 'e.subject_id', '=', 'subj.id')
            ->select(
                's.year_level',
                'subj.semester',
                DB::raw('COUNT(DISTINCT e.student_id) as student_count'),
                DB::raw('COUNT(e.id) as enrollment_count'),
                DB::raw('SUM(subj.units) as total_units')
            )
            ->where('e.status', 'Enrolled')
            ->groupBy('s.year_level', 'subj.semester')
            ->get();

        $totalStats = DB::table('enrollments as e')
            ->join('subjects as subj', 'e.subject_id', '=', 'subj.id')
            ->select(
                DB::raw('COUNT(DISTINCT e.student_id) as total_students'),
                DB::raw('COUNT(e.id) as total_enrollments'),
                DB::raw('SUM(subj.units) as total_units_all')
            )
            ->where('e.status', 'Enrolled')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'by_year_level' => $stats,
                'totals' => $totalStats
            ]
        ]);
    }

    public function getStudentEnrollments(Request $request, $student_id)
    {
        // Validate student_id
        if (!is_numeric($student_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid student ID'
            ], 400);
        }

        try {
            // Get student information
            $student = DB::table('students as s')
                ->join('user_info as ui', 's.student_id', '=', 'ui.id')
                ->where('s.id', $student_id)
                ->select(
                    's.id as student_id',
                    's.id_no',
                    DB::raw("CONCAT(ui.firstname, ' ', ui.lastname) as student_name"),
                    'ui.firstname',
                    'ui.lastname',
                    's.year_level',
                    's.status',
                    's.curriculum',
                    's.sy'
                )
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ], 404);
            }

            // Get enrolled subjects for this student
            $subjects = DB::table('enrollments as e')
                ->join('subjects as subj', 'e.subject_id', '=', 'subj.id')
                ->join('sections as sec', 'e.section_id', '=', 'sec.id')
                ->leftJoin('instructor_info as ii', 'sec.instructor_id', '=', 'ii.instructor_id')
                ->where('e.student_id', $student_id)
                ->where('e.status', 'Enrolled')
                ->select(
                    'e.id as enrollment_id',
                    'subj.id as subject_id',
                    'subj.code as subject_code',
                    'subj.name as subject_name',
                    'subj.units',
                    'subj.year_level as subject_year_level',
                    'subj.semester',
                    'sec.section_name',
                    DB::raw("CONCAT(ii.firstname, ' ', ii.lastname) as instructor_name"),
                    'e.grade',
                    'e.enrollment_date'
                );

            // Apply filters if provided
            if ($request->has('semester')) {
                $subjects->where('subj.semester', $request->semester);
            }

            if ($request->has('year_level')) {
                $subjects->where('subj.year_level', $request->year_level);
            }

            $enrolledSubjects = $subjects->get();

            // Calculate total units and GPA
            $totalUnits = $enrolledSubjects->sum('units');
            $totalGradePoints = 0;
            $gradedUnits = 0;
            $passedCount = 0;
            $failedCount = 0;
            $pendingCount = 0;

            foreach ($enrolledSubjects as $subject) {
                if ($subject->grade && is_numeric($subject->grade)) {
                    $grade = floatval($subject->grade);
                    if ($grade <= 3.0) {
                        $totalGradePoints += ($grade * $subject->units);
                        $gradedUnits += $subject->units;
                        $passedCount++;
                    } else {
                        $failedCount++;
                    }
                } else if ($subject->grade && in_array($subject->grade, ['INC', 'DRP', '4.0', '5.0'])) {
                    $failedCount++;
                } else {
                    $pendingCount++;
                }
            }

            $gpa = $gradedUnits > 0 ? $totalGradePoints / $gradedUnits : 0;

            // Get academic year from enrollment_date
            $academicYear = null;
            if ($student->sy) {
                $academicYear = $student->sy;
            } else if ($enrolledSubjects->count() > 0) {
                $firstEnrollment = $enrolledSubjects->first();
                if ($firstEnrollment->enrollment_date) {
                    $date = new \DateTime($firstEnrollment->enrollment_date);
                    $year = $date->format('Y');
                    $academicYear = ($year - 1) . '-' . $year;
                }
            }

            $response = [
                'student_id' => $student->student_id,
                'id_no' => $student->id_no,
                'name' => $student->student_name,
                'firstname' => $student->firstname,
                'lastname' => $student->lastname,
                'year_level' => $student->year_level,
                'status' => $student->status,
                'curriculum' => $student->curriculum,
                'academic_year' => $academicYear,
                'total_units' => $totalUnits,
                'gpa' => round($gpa, 2),
                'subjects' => $enrolledSubjects,
                'subject_count' => $enrolledSubjects->count(),
                'academic_summary' => [
                    'passed_subjects' => $passedCount,
                    'failed_subjects' => $failedCount,
                    'pending_subjects' => $pendingCount,
                    'completion_rate' => $enrolledSubjects->count() > 0 ? 
                        round(($passedCount / $enrolledSubjects->count()) * 100, 1) : 0
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching student enrollment data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}