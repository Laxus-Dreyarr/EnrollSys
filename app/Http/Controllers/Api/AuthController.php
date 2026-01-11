<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Student Login API
     */
    public function studentLogin(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $email = $request->email;
            $password = $request->password;

            // Find user by email2 (as per your existing logic)
            $user = User::where('email2', $email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 401);
            }

            // Check password
            if (!Hash::check($password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Check if user is active
            if ($user->is_active == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is not active'
                ], 401);
            }

            // Attempt login with student guard (if configured)
            // If not, we'll just verify credentials and return user data
            if (Auth::guard('student')->attempt([
                'email2' => $email,
                'password' => $password
            ])) {
                // Regenerate session if needed
                if ($request->hasSession()) {
                    $request->session()->regenerate();
                }
            }

            // Get student information from related tables
            $studentInfo = DB::table('user_info')
                ->join('students', 'user_info.id', '=', 'students.student_id')
                ->where('user_info.user_id', $user->id)
                ->select(
                    'students.id as student_id',
                    'students.id_no as student_number',
                    'user_info.firstname',
                    'user_info.lastname',
                    'user_info.middlename',
                    'students.year_level',
                    'students.curriculum',
                    'students.status',
                    'students.section',
                    'students.is_regular'
                )
                ->first();

            // Generate a simple API token (or use Laravel Sanctum if installed)
            $token = $this->generateApiToken($user);

            // Prepare response
            $response = [
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email2,
                        'email2' => $user->email2,
                        'is_active' => $user->is_active,
                    ],
                    'student' => $studentInfo,
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => 3600, // 1 hour
                ]
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate simple API token
     */
    private function generateApiToken($user)
    {
        // Create a simple token (you can use Laravel Sanctum for better security)
        $token = hash('sha256', $user->id . $user->email2 . time());
        
        // Store token in database (optional)
        DB::table('api_tokens')->insert([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addHours(1),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return $token;
    }

    /**
     * Get Student Profile (requires API key)
     */
    public function getStudentProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $email = $request->email;
            
            // Get user and student info
            $user = User::where('email2', $email)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ], 404);
            }

            // Get complete student information
            $studentData = DB::table('users as u')
                ->join('user_info as ui', 'u.id', '=', 'ui.user_id')
                ->join('students as s', 'ui.id', '=', 's.student_id')
                ->where('u.email2', $email)
                ->select(
                    'u.id as user_id',
                    'u.email2 as email',
                    'ui.firstname',
                    'ui.lastname',
                    'ui.middlename',
                    'ui.birthdate',
                    'ui.sex',
                    'ui.phone_number',
                    'ui.address',
                    's.id as student_id',
                    's.id_no as student_number',
                    's.year_level',
                    's.curriculum',
                    's.status',
                    's.is_regular'
                )
                ->first();

            if (!$studentData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student information not found'
                ], 404);
            }

            // Get current subjects
            $currentSubjects = DB::table('enrolled_sub as es')
                ->join('subjects as sub', 'es.subject_id', '=', 'sub.id')
                ->where('es.student_id', $studentData->student_id)
                ->where(function($query) {
                    $query->whereNull('es.grade')
                          ->orWhere('es.grade', '')
                          ->orWhere('es.grade', 'INC'); // Include INC subjects as current
                })
                ->select(
                    'sub.code as subject_code',
                    'sub.name as subject_name',
                    'es.units',
                    'es.section',
                    'es.schedule',
                    'es.grade'
                )
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'profile' => $studentData,
                    'current_subjects' => $currentSubjects,
                    'enrollment_status' => [
                        'is_regular' => $studentData->is_regular,
                        'year_level' => $studentData->year_level,
                        'curriculum' => $studentData->curriculum,
                        'status' => $studentData->status
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Student Grades (by email)
     */
    public function getStudentGradesByEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $email = $request->email;
            
            // Find student by email
            $student = DB::table('users as u')
                ->join('user_info as ui', 'u.id', '=', 'ui.user_id')
                ->join('students as s', 'ui.id', '=', 's.student_id')
                ->where('u.email2', $email)
                ->select('s.id as student_id', 's.id_no as student_number')
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ], 404);
            }

            // Get grades for this student
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
                ->where('es.student_id', $student->student_id)
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

            // Count passed/failed
            $passedCount = 0;
            $failedCount = 0;
            foreach ($grades as $grade) {
                if ($grade->grade && $grade->grade <= 3.0 && !in_array($grade->grade, ['4.0', '5.0', 'INC', 'DRP'])) {
                    $passedCount++;
                } elseif (in_array($grade->grade, ['4.0', '5.0', 'INC', 'DRP'])) {
                    $failedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'student' => [
                    'student_id' => $student->student_id,
                    'student_number' => $student->student_number,
                ],
                'academic_summary' => [
                    'gpa' => round($gpa, 2),
                    'total_units' => $totalUnits,
                    'passed_subjects' => $passedCount,
                    'failed_subjects' => $failedCount,
                    'total_subjects' => count($grades),
                ],
                'grades' => $grades
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch grades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout student
     */
    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();
            
            if ($token) {
                // Remove token from database
                DB::table('api_tokens')->where('token', $token)->delete();
            }

            // Logout from guard if authenticated
            if (Auth::guard('student')->check()) {
                Auth::guard('student')->logout();
                
                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change Password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email2', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 401);
            }

            // Update password
            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update password',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}