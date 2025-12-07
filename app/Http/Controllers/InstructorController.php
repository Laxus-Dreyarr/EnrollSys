<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\UserInfo;
use App\Models\Passkey;
use App\Models\AuditLog;
use App\Models\Instructor;
use App\Models\InstructorInfo;
use App\Models\Student;
use App\Mail\RegistrationVerification;
use Illuminate\Support\Facades\Log;
use App\Mail\PasswordResetOtp;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class InstructorController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     $this->middleware('role:instructor');
    // }

    // public function dashboard()
    // {
    //     return view('instructor.dashboard');
    // }

    // public function profile()
    // {
    //     $user = Auth::user();
    //     $userInfo = UserInfo::where('user_id', $user->id)->first();
    //     return view('instructor.profile', compact('user', 'userInfo'));
    // }

    private function getDeviceInfo()
    {
        $agent = new Agent();
        
        return [
            'device' => $agent->device(),
            'platform' => $agent->platform(),
            'browser' => $agent->browser(),
            'is_desktop' => $agent->isDesktop(),
            'is_phone' => $agent->isPhone(),
            'is_tablet' => $agent->isTablet(),
            'is_robot' => $agent->isRobot(),
            'robot_name' => $agent->isRobot() ? $agent->robot() : null,
            'user_agent' => request()->userAgent(),
        ];
    }

    private function getDeviceSummary()
    {
        $agent = new Agent();
        $device = $agent->device();
        
        if ($agent->isDesktop()) {
            return "Desktop" . ($device ? " ($device)" : "");
        } elseif ($agent->isTablet()) {
            return "Tablet" . ($device ? " ($device)" : "");
        } elseif ($agent->isPhone()) {
            return "Mobile" . ($device ? " ($device)" : "");
        } elseif ($agent->isRobot()) {
            return "Robot" . ($agent->robot() ? " ({$agent->robot()})" : "");
        }
        
        return "Unknown Device";
    }


    private function updateUserDeviceInfo($user)
    {
        $deviceInfo = $this->getDeviceInfo();
        
        $user->update([
            'ip_address' => request()->ip(),
            'device_type' => $this->getDeviceSummary(),
            'platform' => $deviceInfo['platform'],
            'browser' => $deviceInfo['browser'],
            'device' => $deviceInfo['device'],
            'is_desktop' => $deviceInfo['is_desktop'],
            'is_mobile' => $deviceInfo['is_phone'],
            'is_tablet' => $deviceInfo['is_tablet'],
            'is_robot' => $deviceInfo['is_robot'],
            'user_agent' => $deviceInfo['user_agent'],
            'last_login_at' => now(),
        ]);
    }

    private function getClientRealIp()
    {
        $ipAddress = '';

        // Check for forwarded IP addresses first (common with proxies, load balancers)
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ipAddress = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }

        // Clean the IP address
        $ipAddress = trim($ipAddress);
        
        // Validate it's a real IP address
        if (filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return $ipAddress;
        }

        return 'Unknown';
    }

    public function getClientDeviceInfo() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        // Get client IP address (handling proxies)
        $ipAddress = $this->getClientRealIp();
        
        // Parse device information from user agent
        $deviceInfo = $this->parseUserAgent($userAgent);
        
        return [
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_info' => $deviceInfo,
            'request_time' => date('Y-m-d H:i:s'),
            'server_vars' => [
                'http_referer' => $_SERVER['HTTP_REFERER'] ?? 'Direct',
                'http_accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'Unknown',
                'server_protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown'
            ]
        ];
    }

    public function getClientIP() {
        // Check for shared internet/ISP IP
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        // Check for IPs passing through proxies
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Check for multiple IPs in X_FORWARDED_FOR
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ipList[0]);
        }
        // Check for remote IP
        elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        
        return 'Unknown';
    }

    public function parseUserAgent($userAgent) {
        $deviceType = 'desktop';
        $browser = 'Unknown';
        $os = 'Unknown';
        
        // Device type detection
        if (preg_match('/(android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini)/i', $userAgent)) {
            $deviceType = 'mobile';
            if (preg_match('/(tablet|ipad)/i', $userAgent)) {
                $deviceType = 'tablet';
            }
        }
        
        // Browser detection
        if (preg_match('/Chrome/i', $userAgent) && !preg_match('/Edg/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari/i', $userAgent) && !preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edg/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/Opera|OPR/i', $userAgent)) {
            $browser = 'Opera';
        }
        
        // OS detection
        if (preg_match('/Android/i', $userAgent)) {
            $os = 'Android';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
            $os = 'iOS';
        } elseif (preg_match('/Windows/i', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/Macintosh|Mac OS X/i', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $os = 'Linux';
        }
        
        // Device brand detection
        $brand = 'Unknown';
        $model = 'Unknown';
        
        if (preg_match('/Samsung|SM-[A-Z0-9]+|GT-[A-Z0-9]+/i', $userAgent)) {
            $brand = 'Samsung';
        } elseif (preg_match('/Realme|RMX[A-Z0-9]+/i', $userAgent)) {
            $brand = 'Realme';
        } elseif (preg_match('/iPhone/i', $userAgent)) {
            $brand = 'Apple';
            $model = 'iPhone';
        } elseif (preg_match('/iPad/i', $userAgent)) {
            $brand = 'Apple';
            $model = 'iPad';
        } elseif (preg_match('/Macintosh/i', $userAgent)) {
            $brand = 'Apple';
            $model = 'Mac';
        } elseif (preg_match('/Redmi|Mi |Xiaomi/i', $userAgent)) {
            $brand = 'Xiaomi';
        } elseif (preg_match('/Huawei/i', $userAgent)) {
            $brand = 'Huawei';
        } elseif (preg_match('/OnePlus/i', $userAgent)) {
            $brand = 'OnePlus';
        } elseif (preg_match('/Pixel/i', $userAgent)) {
            $brand = 'Google';
        }
        
        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'operating_system' => $os,
            'brand' => $brand,
            'model' => $model
        ];
    }

    public function collectClientInformation() {
        $deviceInfo = $this->getClientDeviceInfo();
        
        // Attempt to get MAC address (works only in local network)
        // $macAddress = attemptMacAddressDetection($deviceInfo['ip_address']);
        
        $completeInfo = [
            'ip_address' => $deviceInfo['ip_address'],
            'user_agent' => $deviceInfo['user_agent'],
            'device_type' => $deviceInfo['device_info']['device_type'],
            'browser' => $deviceInfo['device_info']['browser'],
            'operating_system' => $deviceInfo['device_info']['operating_system'],
            'device_brand' => $deviceInfo['device_info']['brand'],
            'device_model' => $deviceInfo['device_info']['model'],
            'timestamp' => $deviceInfo['request_time'],
            'is_local_network' => $this->isLocalIP($deviceInfo['ip_address'])
        ];

        // 'mac_address' => $macAddress,
        
        return $completeInfo;
    }

    public function isLocalIP($ip) {
        // Check if IP is in local range
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    public function register(Request $request){

        $action = $request->input('action');

        switch ($action) {
            case 'check_passkey':
                return $this->checkPasskey($request);
            case 'registering':
                return $this->registerInstructor($request);
            case 'check_email':
                return $this->checkEmailExists($request);
            case 'forgot_verification':
                return $this->sendInstructorOtpForgotPass($request);
            case 'resetPassword_account':
                return $this->InstructorResetPass($request);
            case 'login':
                return $this->InstructorLogin($request);
        }

    }

    private function InstructorLogin(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $email = $request->email;
        $password = $request->password;

        if(!$request) {
            $x = '9';
            return $x;
        }

        $y = Instructor::where('email5', $email)->first();

        if(!$y) {
            $x = '1';
            return $x;
        }


        if (!Hash::check($password, $y->password)) {
            $x = 2;
            return $x;  
        }

        if($y->is_active == 0) {
            $x = 0;
            return $x;  
        }


        if (Auth::guard('instructor')->attempt([
            'email5' => $request->email,
            'password' => $request->password
            ])) {
            $request->session()->regenerate();
            return response()->json(10);
        }
        
    }

    public function dashboard(){

        if (!Auth::guard('instructor')->check()) {
            return redirect('/instructor')->with('error', 'Please login first.');
        }

        // Check if there's an active enrollment period
        $enrollmentPeriod = $this->checkActiveEnrollmentPeriod();

        $user = Auth::guard('instructor')->user();

        // Get notifications for the instructor
        $notifications = DB::table('notifications_instructor')
            ->where('user_id', $user->instructor_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('instructor.dashboard.dashboard', compact('user', 'enrollmentPeriod', 'notifications'));
        
    }

    public function markNotificationAsRead(Request $request)
    {
        $notificationId = $request->input('notification_id');
        
        $updated = DB::table('notifications_instructor')
            ->where('id', $notificationId)
            ->update(['is_read' => 1]);
        
        return response()->json([
            'success' => $updated > 0,
            'message' => $updated > 0 ? 'Notification marked as read' : 'Failed to update notification'
        ]);
    }

    public function countNotificationAsRead(Request $request)
    {
        try {
            $instructor = Auth::guard('instructor')->user();
            
            // Get count of pending enrollment requests
            $count = DB::table('notifications_instructor')
                ->where('is_read', 0)
                ->count();

            return response()->json([
                'count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching enrollment requests: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch enrollment requests'], 500);
        }
    }

    public function logout(Request $request)
    {
        // Check if user is logged in
        if (Auth::guard('instructor')->check()) {
            // Logout the instructor
            Auth::guard('instructor')->logout();
            
            // Invalidate the session
            $request->session()->invalidate();
            
            // Regenerate the CSRF token
            $request->session()->regenerateToken();
            
            // Return JSON response for AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Logged out successfully',
                    'redirect' => '/instructor'
                ]);
            }
            
            // Redirect to login page
            return redirect('/instructor')->with('success', 'Logged out successfully');
        }
        
        return response()->json([
            'success' => false,
            'message' => 'No user logged in'
        ], 401);
    }

    public function getUngradedStudents(Request $request)
    {
        try {
            $yearLevel = $request->input('year_level');
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'lastname');
            $subjectSearch = $request->input('subject_search');
            $gradeStatus = $request->input('grade_status', 'ungraded');

            // Base query for enrolled subjects
            $query = DB::table('enrolled_sub as es')
                ->join('students as s', 'es.student_id', '=', 's.id')
                ->join('user_info as ui', 's.student_id', '=', 'ui.id')
                ->join('subjects as sub', 'es.subject_id', '=', 'sub.id')
                ->where('s.year_level', '!=', NULL) // Exclude students with year_level = 'NONE'
                ->select(
                    's.id as student_db_id',
                    's.student_id as student_user_id',
                    'ui.firstname',
                    'ui.lastname',
                    'ui.middlename',
                    's.id_no',
                    's.year_level',
                    'sub.id as subject_id',
                    'sub.code as subject_code',
                    'sub.name as subject_name',
                    'sub.units',
                    'sub.year_level as subject_year',
                    'sub.semester as subject_semester',
                    'es.date_enrolled',
                    'es.grade as current_grade'
                )
                ->distinct();

            // Apply grade status filter
            switch ($gradeStatus) {
                case 'ungraded':
                    $query->whereNull('es.grade');
                    break;
                case 'graded':
                    $query->whereNotNull('es.grade');
                    break;
                case 'all':
                    // No grade filter applied
                    break;
            }

            // Apply year level filter
            if ($yearLevel && $yearLevel !== '') {
                $query->where('s.year_level', $yearLevel);
            }

            // Apply student search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('ui.firstname', 'LIKE', "%{$search}%")
                    ->orWhere('ui.lastname', 'LIKE', "%{$search}%")
                    ->orWhere('ui.middlename', 'LIKE', "%{$search}%")
                    ->orWhere('s.id_no', 'LIKE', "%{$search}%");
                });
            }

            // Apply subject search filter
            if ($subjectSearch) {
                $query->where(function($q) use ($subjectSearch) {
                    $q->where('sub.code', 'LIKE', "%{$subjectSearch}%")
                    ->orWhere('sub.name', 'LIKE', "%{$subjectSearch}%");
                });
            }

            // Apply sorting
            switch ($sortBy) {
                case 'firstname':
                    $query->orderBy('ui.firstname');
                    break;
                case 'middlename':
                    $query->orderBy('ui.middlename');
                    break;
                case 'subject':
                    $query->orderBy('sub.code')->orderBy('sub.name');
                    break;
                default:
                    $query->orderBy('ui.lastname');
            }

            // Add ordering by year level and semester
            $query->orderByRaw("
                CASE 
                    WHEN sub.year_level = '1st Year' THEN 1
                    WHEN sub.year_level = '2nd Year' THEN 2
                    WHEN sub.year_level = '3rd Year' THEN 3
                    WHEN sub.year_level = '4th Year' THEN 4
                    WHEN sub.year_level = '5th Year' THEN 5
                    ELSE 6
                END
            ")
            ->orderByRaw("
                CASE 
                    WHEN sub.semester = '1st Sem' THEN 1
                    WHEN sub.semester = '2nd Sem' THEN 2
                    WHEN sub.semester = 'Summer' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('sub.id');

            $students = $query->get();

            // Group students and their subjects
            $groupedStudents = [];
            foreach ($students as $student) {
                $key = $student->student_db_id;
                
                if (!isset($groupedStudents[$key])) {
                    $groupedStudents[$key] = [
                        'student_db_id' => $student->student_db_id,
                        'student_user_id' => $student->student_user_id,
                        'firstname' => $student->firstname,
                        'lastname' => $student->lastname,
                        'middlename' => $student->middlename,
                        'id_no' => $student->id_no,
                        'year_level' => $student->year_level,
                        'subjects' => []
                    ];
                }
                
                $groupedStudents[$key]['subjects'][] = [
                    'subject_id' => $student->subject_id,
                    'subject_code' => $student->subject_code,
                    'subject_name' => $student->subject_name,
                    'units' => $student->units,
                    'subject_year' => $student->subject_year,
                    'subject_semester' => $student->subject_semester,
                    'date_enrolled' => $student->date_enrolled,
                    'current_grade' => $student->current_grade
                ];
            }

            return response()->json([
                'students' => array_values($groupedStudents)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching students: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch students'], 500);
        }
    }


    public function saveGrade(Request $request)
    {
        try {
            Log::info('Save grade request received:', $request->all());
            
            $validator = Validator::make($request->all(), [
                'student_id' => 'required|integer',
                'subject_id' => 'required|integer',
                'grade' => 'required|string|max:10'
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input data: ' . $validator->errors()->first()
                ]);
            }

            $instructor = Auth::guard('instructor')->user();
            Log::info('Instructor:', ['instructor_id' => $instructor->instructor_id]);

            // Check if the student-subject combination exists in enrolled_sub
            $enrollment = DB::table('enrolled_sub')
                ->where('student_id', $request->student_id)
                ->where('subject_id', $request->subject_id)
                ->first();

            Log::info('Enrollment check:', [
                'student_id' => $request->student_id,
                'subject_id' => $request->subject_id,
                'enrollment_found' => !is_null($enrollment)
            ]);

            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is not enrolled in this subject'
                ]);
            }

            // Update the grade in enrolled_sub table
            $updated = DB::table('enrolled_sub')
                ->where('student_id', $request->student_id)
                ->where('subject_id', $request->subject_id)
                ->update([
                    'grade' => $request->grade
                ]);

            Log::info('Update result:', ['rows_updated' => $updated]);

            // if (!$updated) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'No rows were updated. Please check if the student and subject exist.'
            //     ]);
            // }

            // Get student and subject details for logging
            $student = DB::table('students')
                ->where('id', $request->student_id)
                ->first();

            $subject = DB::table('subjects')
                ->where('id', $request->subject_id)
                ->first();

            $stud_info = Student::where('id', $request->student_id)
                        ->first();

            $name = $stud_info->userInfo->firstname;

            Log::info('Student and subject found:', [
                'student' => ''.$name,
                'subject' => !is_null($subject)
            ]);

            // Log the action
            $clientInfo = $this->collectClientInformation();
            AuditLog::create([
                'user_id' => $stud_info->id_no ?? 'unknown',
                'action' => 'Grade input for subject: ' . ($subject->code ?? 'unknown'),
                'details' => 'Grade: ' . $request->grade . ' - ' . $clientInfo['operating_system'] . '/' . $clientInfo['device_type'] . '/' . $clientInfo['user_agent'],
                'ip_address' => $clientInfo['ip_address'],
                'date' => now(),
                'access_by' => '107568'
            ]);

            Log::info('Grade saved successfully');

            return response()->json([
                'success' => true,
                'message' => 'Grade saved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving grade: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save grade: ' . $e->getMessage()
            ], 500);
        }
    }
    


    private function checkPasskey(Request $request) 
    {
        try {
            if(!$request->has('passkey')){
                return response()->json(['success' => false, 'message' => 'Passkey is required']);
            }

            $y = Passkey::where('passkey', $request->passkey)->exists();

            if(!$y) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Passkey'
                ]);
            }
            $y = null;

            return response()->json(['success' => true, 'message' => 'Valid Passkey']);

        } catch (\Exception $e) {
            Log::error('Passkey check error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error checking passkey']);
        }

    }

    private function registerInstructor(Request $request) {
        try {
            Log::info('Starting instructor registration', ['email' => $request->email]);
            
            $passkey = Passkey::where('passkey', $request->email)->first();
            
            if(!$passkey) {
                Log::warning('Invalid passkey attempted', ['passkey' => $request->email]);
                return response()->json(['success' => false, 'message' => 'Invalid Passkey']);
            }
            
            Log::info('Passkey found', ['passkey_id' => $passkey->id, 'email3' => $passkey->email3]);
            
            $instructorEmail = $passkey->email3;

            if($passkey->user_type !== 'instructor') {
                Log::warning('Passkey user type mismatch', ['expected' => 'instructor', 'found' => $passkey->user_type]);
                return response()->json(['success' => false, 'message' => 'Passkey not valid for instructor registration']);
            } 

            $check = Instructor::where('email5', $instructorEmail)->exists();
            if($check) {
                Log::warning('Email already registered', ['email' => $instructorEmail]);
                return response()->json(['success' => false, 'message' => 'Email is already registered']);
            }

            // Check if passwords match
            if ($request->input('password') !== $request->input('repeatPassword')) {
                return response()->json(['success' => false, 'message' => 'Passwords do not match']);
            }

            DB::beginTransaction();

            $instructorId = $this->generateUniqueInstructorId();
            $currentDate = now()->toDateTimeString();

            Log::info('Creating instructor record', ['instructor_id' => $instructorId]);
            
            // Create instructor
            $instructor = Instructor::create([
                'instructor_id' => $instructorId,
                'email5' => $instructorEmail,
                'password' => Hash::make($request->input('password')),
                'profile' => 'default.png',
                'date_created' => $currentDate,
                'user_type' => $passkey->user_type,
                'is_active' => 1,    
                'last_login' => $currentDate,
            ]);

            Log::info('Creating instructor info record', ['instructor_id' => $instructorId]);
            
            // Create instructor info
            InstructorInfo::create([
                'instructor_id' => $instructorId,
                'firstname' => $request->input('givenName'),
                'lastname' => $request->input('lastName'),
                'middlename' => $request->input('middleName') ?? null,
                'birthdate' => 'null',
                'age' => 'null',
                'address' => 'null',
                'department' => 'Computer Studies Department',
            ]);

            // $clientInfo = $this->getClientDeviceInfoWithRequest($request);
            $clientInfo = $this->collectClientInformation($request);
            // $ipaddress = $this->getClientRealIp();
            $ipaddress = $this->getClientDeviceInfo();

            // Save to AuditLogs!
            AuditLog::create([
                'user_id' => $instructorId,
                'action' => 'New Instuctor Account Created: '.$instructorEmail,
                'details' => '' .$clientInfo['operating_system'] .'/' .$clientInfo['device_type'] .'/' .$clientInfo['user_agent'],
                'ip_address' => $ipaddress['ip_address'],
                'birthdate' => 'null',
                'date' => $currentDate,
                'access_by' => '107568'
            ]);

            // Invalidate the used passkey
            Passkey::where('passkey', $request->input('email'))
                    ->orWhere('email3', $instructorEmail)
                    ->delete();
            
            DB::commit();
            
            Log::info('Instructor registration successful', ['instructor_id' => $instructorId]);
            
            return response()->json(['success' => true, 'message' => 'Registration successful. You can now log in.']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Instructor registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Error during registration. Please try again later.']);
        }
    }

    // private function registerInstructor(Request $request) {
    //     // $validator = Validator::make($request->all(), [
    //     //     'givenName' => 'required|string|max:10',
    //     //     'lastName' => 'required|string|max:255',
    //     //     'middleName' => 'nullable|string|max:255',
    //     //     'email' => 'required|max:255',
    //     //     'password' => 'required|string|min:8|confirmed',
    //     //     'repeatPassword' => 'required|string|min:8'
    //     // ], [
    //     //     'givenName.required' => 'Given Name is required.',
    //     //     'givenName.string' => 'Given Name must be a valid text.',
    //     //     'givenName.max' => 'Given Name cannot exceed 10 characters.',
    //     //     'lastName.required' => 'Last Name is required.',
    //     //     'lastName.string' => 'Last Name must be a valid text.',
    //     //     'lastName.max' => 'Last Name cannot exceed 255 characters.',
    //     //     'email.required' => 'Passkey is required.',
    //     //     'email.max' => 'Passkey cannot exceed 255 characters.',
    //     //     'password.required' => 'Password is required.',
    //     //     'password.string' => 'Password must be a valid text.',
    //     //     'password.min' => 'Password must be at least 8 characters.',
    //     //     'password.confirmed' => 'Password confirmation does not match.',
    //     //     'repeatPassword.required' => 'Repeat Password is required.',
    //     //     'repeatPassword.string' => 'Repeat Password must be a valid text.',
    //     //     'repeatPassword.min' => 'Repeat Password must be at least 8 characters.',
    //     //     'middleName.string' => 'Middle Name must be a valid text.',
    //     //     'middleName.max' => 'Middle Name cannot exceed 255 characters.',
    //     // ]);

    //     // if ($validator->fails()) {
    //     //     return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
    //     // }
    //     $y = Passkey::where('passkey', $request->email)->exists();
    //     if(!$y) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Invalid Passkey'
    //         ]);
    //     }
    //     $instructorEmail = $y->email3;

    //     // Check if passwords match
    //     if ($request->input('password') !== $request->input('repeatPassword')) {
    //         return response()->json(['success' => false, 'message' => 'Passwords do not match']);
    //     }

    //     // Validate EVSU email domain
    //     // $email = $request->input('email');
    //     // if (!preg_match('/^[a-zA-Z0-9._%+-]+@evsu\.edu\.ph$/', $email)) {
    //     //     return response()->json(['success' => false, 'message' => 'Email must be a valid EVSU email address']);
    //     // }

    //     try {
    //         DB::beginTransaction();
    //         $studentId = $this->generateUniqueInstructorId();
    //         $currentDate = now()->toDateTimeString();

    //         // // Create user
    //         $user = Instructor::create([
    //             'instructor_id' => $studentId,
    //             'email5' => $instructorEmail,
    //             'password' => Hash::make($request->input('password')),
    //             'profile' => 'default.png',
    //             'date_created' => $currentDate,
    //             'user_type' => 'instructor',
    //             'is_active' => 1,    
    //             'last_login' => null,
    //         ]);

    //         // // Create user info
    //         InstructorInfo::create([
    //             'instructor_id' => $studentId,
    //             'firstname' => $request->input('givenName'),
    //             'lastname' => $request->input('lastName'),
    //             'middlename' => $request->input('middleName') ?? null,
    //             'birthdate' => null,
    //             'age' => null,
    //             'address' => null,
    //             'department' => 'Computer Studies Department',
    //             // Add other fields as necessary
    //         ]);

    //         // Invalidate the used passkey
    //         Passkey::where('passkey', $request->input('email'))->delete();
 
            
    //          DB::commit();
            
    //         return response()->json(['success' => true, 'message' => 'Registration successful. You can now log in.']);
    //     } catch (\Exception $e) {
    //         Log::error('Instructor registration error: ' . $e->getMessage());
    //         return response()->json(['success' => false, 'message' => 'Error during registration. Please try again later.']);
    //     }
    // }

    private function generateUniqueInstructorId()
    {
        $maxAttempts = 10;
        $attempt = 0;

        do {
            $instructorID = mt_rand(100000, 999999);
            $exists = User::where('id', $instructorID)->exists();
            $attempt++;

            if ($attempt >= $maxAttempts) {
                throw new \Exception('Unable to generate unique student ID after ' . $maxAttempts . ' attempts');
            }
        } while ($exists);

        return $instructorID;
    }


    // Enrollment request
    public function getEnrollmentRequests(Request $request)
    {
        try {
            $instructor = Auth::guard('instructor')->user();
            
            // Get pending enrollment requests with student details
            $requests = DB::table('enrollmentrequests as er')
            ->join('students as s', 'er.student_id', '=', 's.id')
            ->join('user_info as ui', 's.student_id', '=', 'ui.id')
            ->where('er.status', 'Pending')
            ->select(
                'er.id as request_id',
                'er.student_id',
                'er.request_date',
                's.id_no',
                's.year_level',
                's.curriculum',
                's.is_regular',
                'ui.firstname',
                'ui.lastname',
                'ui.middlename',
                // Get FHE document with full web path (add leading slash)
                // DB::raw("(SELECT CONCAT('/', file_path) 
                //     FROM documents 
                //     WHERE student_id = s.id AND type = 'FHE' 
                //     ORDER BY upload_date DESC LIMIT 1) as fhe_document"),
                // DB::raw("(SELECT status FROM documents 
                //     WHERE student_id = s.id AND type = 'FHE' 
                //     ORDER BY upload_date DESC LIMIT 1) as fhe_status"),
                // Get payment receipt with full web path (add leading slash)
                // DB::raw("(SELECT CONCAT('/', file_path) 
                //     FROM payments 
                //     WHERE student_id = s.id AND type = 'PAYMENT_RECEIPT' 
                //     ORDER BY upload_date DESC LIMIT 1) as payment_receipt"),
                // DB::raw("(SELECT status FROM payments 
                //     WHERE student_id = s.id AND type = 'PAYMENT_RECEIPT' 
                //     ORDER BY upload_date DESC LIMIT 1) as payment_status"),
                DB::raw('(SELECT COUNT(*) FROM enrollments WHERE student_id = s.id AND status = "Enrolled") as enrolled_subjects_count')
            )
            ->get();

            // Get enrolled subjects for each student with prerequisites
            foreach ($requests as $request) {
                $subjects = DB::table('enrollments as e')
                    ->join('subjects as sub', 'e.subject_id', '=', 'sub.id')
                    ->join('sections as sec', 'e.section_id', '=', 'sec.id')
                    ->where('e.student_id', $request->student_id)
                    ->where('e.status', 'Pending')
                    ->select(
                        'sub.id as subject_id',
                        'sub.code as subject_code',
                        'sub.name as subject_name',
                        'sub.units',
                        'sub.year_level',
                        'sub.semester',
                        'sec.section_name'
                    )
                    ->get();

                // Get FHE document for this specific student
                $fheDocument = DB::table('documents')
                    ->where('student_id', $request->student_id)
                    ->where('type', 'FHE')
                    ->orderBy('upload_date', 'desc')
                    ->first();

                // Get Prospectus document for this specific student
                $prospectus = DB::table('documents')
                    ->where('student_id', $request->student_id)
                    ->where('type', 'Prospectus')
                    ->orderBy('upload_date', 'desc')
                    ->first();

                // Get payment receipt for this specific student
                $paymentReceipt = DB::table('payments')
                    ->where('student_id', $request->student_id)
                    ->where('type', 'PAYMENT_RECEIPT')
                    ->orderBy('upload_date', 'desc')
                    ->first();

                // Add web_path for file access
                if ($fheDocument) {
                    $filename = basename($fheDocument->file_path);
                    $fheDocument->web_path = "/documents/fhe/{$filename}";
                    $fheDocument->status = $fheDocument->status ?: 'Pending';
                }

                if ($prospectus) {
                    $filename = basename($prospectus->file_path);
                    $prospectus->web_path = "/documents/prospectus/{$filename}";
                    // $prospectus->status = $prospectus->status ?: 'Pending';
                }

                if ($paymentReceipt) {
                    $filename = basename($paymentReceipt->file_path);
                    $paymentReceipt->web_path = "/documents/payment_receipts/{$filename}";
                    $paymentReceipt->status = $paymentReceipt->status ?: 'Pending';
                }

                // Attach documents to the request object
                $request->fhe_document = $fheDocument;
                $request->prospectus = $prospectus;
                $request->payment_receipt = $paymentReceipt;
                
                // Get prerequisites for each subject and check if student has passed them
                foreach ($subjects as $subject) {
                    // Get prerequisites for this subject
                    $prerequisites = DB::table('subjectprerequisites as sp')
                        ->join('subjects as s', 'sp.prerequisite_id', '=', 's.id')
                        ->where('sp.subject_id', $subject->subject_id)
                        ->select(
                            's.id as prereq_id',
                            's.code as prereq_code',
                            's.name as prereq_name'
                        )
                        ->get();
                    
                    // Check each prerequisite if student has passed it
                    $prerequisiteStatus = [];
                    foreach ($prerequisites as $prereq) {
                        // Check if student has taken this prerequisite
                        $gradeRecord = DB::table('enrolled_sub')
                            ->where('student_id', $request->student_id)
                            ->where('subject_id', $prereq->prereq_id)
                            ->orderBy('date_enrolled', 'desc')
                            ->first();
                        
                        $passed = false;
                        $grade = null;
                        $status = 'Not Taken';
                        
                        if ($gradeRecord) {
                            $grade = $gradeRecord->grade;
                            $status = 'Taken';
                            
                            // Check if grade is passing (1.0 to 3.0) and not 5.0
                            if (is_numeric($grade)) {
                                $gradeValue = floatval($grade);
                                if ($gradeValue >= 1.0 && $gradeValue <= 3.0) {
                                    $passed = true;
                                    $status = 'Passed';
                                } else if ($gradeValue == 5.0) {
                                    $status = 'Failed (5.0)';
                                } else {
                                    $status = 'Failed (' . $grade . ')';
                                }
                            } else if (in_array(strtoupper($grade), ['INC', 'DRP', 'DROP'])) {
                                $status = 'Incomplete/Dropped';
                            }
                        }
                        
                        $prerequisiteStatus[] = [
                            'code' => $prereq->prereq_code,
                            'name' => $prereq->prereq_name,
                            'passed' => $passed,
                            'grade' => $grade,
                            'status' => $status,
                            'has_grade' => !is_null($grade)
                        ];
                    }
                    
                    // Check if all prerequisites are passed
                    $allPrerequisitesPassed = true;
                    if (count($prerequisiteStatus) > 0) {
                        foreach ($prerequisiteStatus as $prereq) {
                            if (!$prereq['passed']) {
                                $allPrerequisitesPassed = false;
                                break;
                            }
                        }
                    }
                    
                    $subject->prerequisites = $prerequisiteStatus;
                    $subject->all_prerequisites_passed = $allPrerequisitesPassed;
                    $subject->has_prerequisites = count($prerequisiteStatus) > 0;
                }
                
                $request->subjects = $subjects;
            }

            return response()->json([
                'requests' => $requests
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching enrollment requests: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch enrollment requests'], 500);
        }
    }

    public function getEnrollmentRequests2(Request $request)
    {
        try {
            $instructor = Auth::guard('instructor')->user();
            
            // Get count of pending enrollment requests
            $count = DB::table('enrollmentrequests')
                ->where('status', 'Pending')
                ->count();

            return response()->json([
                'count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching enrollment requests: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch enrollment requests'], 500);
        }
    }

    // Get student's subjects history
    public function getStudentSubjectsHistory($studentId)
    {
        try {
            // Get all enrolled subjects for the student
            $subjects = DB::table('enrolled_sub')
                ->where('student_id', $studentId)
                ->orderBy('year_level', 'asc')
                ->orderBy('semester', 'asc')
                ->select(
                    'subject_code',
                    'subject_name',
                    'units',
                    'year_level',
                    'semester',
                    'grade',
                    'date_enrolled'
                )
                ->get();

            return response()->json([
                'success' => true,
                'subjects' => $subjects
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching student subjects history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch subjects history'
            ], 500);
        }
    }

    // public function getEnrollmentRequests(Request $request)
    // {
    //     try {
    //         $instructor = Auth::guard('instructor')->user();
            
    //         // Get pending enrollment requests with student details
    //         $requests = DB::table('enrollmentrequests as er')
    //         ->join('students as s', 'er.student_id', '=', 's.id')
    //         ->join('user_info as ui', 's.student_id', '=', 'ui.id')
    //         ->where('er.status', 'Pending')
    //         ->select(
    //             'er.id as request_id',
    //             'er.student_id',
    //             'er.request_date',
    //             's.id_no',
    //             's.year_level',
    //             's.curriculum',
    //             's.is_regular',
    //             'ui.firstname',
    //             'ui.lastname',
    //             'ui.middlename',
    //             // Get FHE document with full web path (add leading slash)
    //             DB::raw("(SELECT CONCAT('/', file_path) 
    //                 FROM documents 
    //                 WHERE student_id = s.id AND type = 'FHE' 
    //                 ORDER BY upload_date DESC LIMIT 1) as fhe_document"),
    //             DB::raw("(SELECT status FROM documents 
    //                 WHERE student_id = s.id AND type = 'FHE' 
    //                 ORDER BY upload_date DESC LIMIT 1) as fhe_status"),
    //             // Get payment receipt with full web path (add leading slash)
    //             DB::raw("(SELECT CONCAT('/', file_path) 
    //                 FROM payments 
    //                 WHERE student_id = s.id AND type = 'PAYMENT_RECEIPT' 
    //                 ORDER BY upload_date DESC LIMIT 1) as payment_receipt"),
    //             DB::raw("(SELECT status FROM payments 
    //                 WHERE student_id = s.id AND type = 'PAYMENT_RECEIPT' 
    //                 ORDER BY upload_date DESC LIMIT 1) as payment_status"),
    //             DB::raw('(SELECT COUNT(*) FROM enrollments WHERE student_id = s.id AND status = "Enrolled") as enrolled_subjects_count')
    //         )
    //         ->get();

    //         // Get enrolled subjects for each student
    //         foreach ($requests as $request) {
    //             $subjects = DB::table('enrollments as e')
    //                 ->join('subjects as sub', 'e.subject_id', '=', 'sub.id')
    //                 ->join('sections as sec', 'e.section_id', '=', 'sec.id')
    //                 ->where('e.student_id', $request->student_id)
    //                 ->where('e.status', 'Pending')
    //                 ->select(
    //                     'sub.code as subject_code',
    //                     'sub.name as subject_name',
    //                     'sub.units',
    //                     'sub.year_level',
    //                     'sub.semester',
    //                     'sec.section_name'
    //                 )
    //                 ->get();
                
    //             $request->subjects = $subjects;
    //         }

    //         return response()->json([
    //             'requests' => $requests
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Error fetching enrollment requests: ' . $e->getMessage());
    //         return response()->json(['error' => 'Failed to fetch enrollment requests'], 500);
    //     }
    // }


    // public function approveEnrollment(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'request_id' => 'required|integer',
    //             'student_id' => 'required|integer'
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Invalid input data'
    //             ]);
    //         }

    //         $instructor = Auth::guard('instructor')->user();

    //         // Update enrollment request
    //         DB::table('enrollmentrequests')
    //             ->where('id', $request->request_id)
    //             ->update([
    //                 'status' => 'Approved',
    //                 'instructor_id' => $instructor->instructor_id,
    //                 'processed_date' => now()
    //             ]);

    //         // Update student status
    //         DB::table('students')
    //             ->where('id', $request->student_id)
    //             ->update([
    //                 'status' => 'Officially Enrolled'
    //             ]);

    //         // Create notification for student
    //         DB::table('notifications')->insert([
    //             'user_id' => '7',
    //             'title' => 'Enrollment Approved',
    //             'message' => 'Your enrollment request has been approved. You are now officially enrolled.',
    //             'is_read' => '0',
    //             'created_at' => now()
    //         ]);

    //         // Log the action
    //         // $clientInfo = $this->collectClientInformation();
    //         // AuditLog::create([
    //         //     'user_id' => $request->student_id,
    //         //     'action' => 'Enrollment request approved by instructor',
    //         //     'details' => 'Student ID: ' . $request->student_id . ' - ' . $clientInfo['operating_system'] . '/' . $clientInfo['device_type'] . '/' . $clientInfo['user_agent'],
    //         //     'ip_address' => $clientInfo['ip_address'],
    //         //     'date' => now(),
    //         //     'access_by' => $instructor->instructor_id
    //         // ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Enrollment approved successfully'
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Error approving enrollment: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to approve enrollment'
    //         ], 500);
    //     }
    // }

    public function approveEnrollment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'request_id' => 'required|integer',
                'student_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input data'
                ]);
            }

            $instructor = Auth::guard('instructor')->user();

            // Get the student's user_id
            $student = DB::table('students')
                ->where('id', $request->student_id)
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ]);
            }

            // Get the user_id from user_info table
            $userInfo = DB::table('user_info')
                ->where('id', $student->student_id) // student_id in students table = id in user_info table
                ->first();

            if (!$userInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'User information not found'
                ]);
            }

            // Update enrollment request
            DB::table('enrollmentrequests')
                ->where('id', $request->request_id)
                ->update([
                    'status' => 'Approved',
                    'instructor_id' => $instructor->instructor_id,
                    'processed_date' => now()
                ]);

            // Update student status
            DB::table('students')
                ->where('id', $request->student_id)
                ->update([
                    'status' => 'Officially Enrolled'
                ]);

            // Create notification for student using the correct user_id
            DB::table('notifications')->insert([
                'user_id' => $userInfo->user_id, // Use the actual user_id
                'title' => 'Enrollment Approved',
                'message' => 'Your enrollment request has been approved. You are now officially enrolled.',
                'is_read' => 0, // Use integer 0, not string '0'
                'created_at' => now()
            ]);

            // Log the action
            $clientInfo = $this->collectClientInformation();
            AuditLog::create([
                'user_id' => $request->student_id,
                'action' => 'Enrollment request approved by instructor',
                'details' => 'Student ID: ' . $request->id_no . ' - ' . $clientInfo['operating_system'] . '/' . $clientInfo['device_type'] . '/' . $clientInfo['user_agent'],
                'ip_address' => $clientInfo['ip_address'],
                'date' => now(),
                'access_by' => '107568'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Enrollment approved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error approving enrollment: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve enrollment: ' . $e->getMessage()
            ], 500);
        }
    }


    public function rejectEnrollment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'request_id' => 'required|integer',
                'student_id' => 'required|integer',
                'rejection_reason' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input data'
                ]);
            }

            $instructor = Auth::guard('instructor')->user();
            
            // Get the student's user_id
            $student = DB::table('students')
                ->where('id', $request->student_id)
                ->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ]);
            }

            // Get the user_id from user_info table
            $userInfo = DB::table('user_info')
                ->where('id', $student->student_id) // student_id in students table = id in user_info table
                ->first();

            if (!$userInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'User information not found'
                ]);
            }

            // Update enrollment request
            DB::table('enrollmentrequests')
                ->where('id', $request->request_id)
                ->update([
                    'status' => 'Rejected',
                    'instructor_id' => $instructor->instructor_id,
                    'rejection_reason' => $request->rejection_reason,
                    'processed_date' => now()
                ]);

            // Update student status
            DB::table('students')
                ->where('id', $request->student_id)
                ->update([
                    'status' => 'Rejected'
                ]);

            // Create notification for student
            DB::table('notifications')->insert([
                'user_id' => $userInfo->user_id,
                'title' => 'Enrollment Rejected',
                'message' => 'Your enrollment request has been rejected. Reason: ' . $request->rejection_reason,
                'is_read' => 0,
                'created_at' => now()
            ]);

            // Log the action
            $clientInfo = $this->collectClientInformation();
            AuditLog::create([
                'user_id' => $request->student_id,
                'action' => 'Enrollment request rejected by instructor',
                'details' => 'Student ID: ' . $request->id_no . ' - Reason: ' . $request->rejection_reason . ' - ' . $clientInfo['operating_system'] . '/' . $clientInfo['device_type'] . '/' . $clientInfo['user_agent'],
                'ip_address' => $clientInfo['ip_address'],
                'date' => now(),
                'access_by' => '107568'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Enrollment rejected successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error rejecting enrollment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject enrollment'
            ], 500);
        }
    }

    // End of enrollment request


    public function getStudentProfile($studentId)
    {
        if (!Auth::guard('instructor')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Fetch student details
        $student = DB::table('students as s')
            ->join('user_info as ui', 's.student_id', '=', 'ui.id')
            ->leftJoin('users as u', 'ui.user_id', '=', 'u.id')
            ->leftJoin('student_count_year as scy', 's.id', '=', 'scy.student_id')
            ->where('s.id', $studentId)
            ->select(
                's.*',
                'ui.*',
                'u.email2 as email',
                'scy.total_year'
            )
            ->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Get enrolled subjects with grades
        $subjects = DB::table('enrolled_sub')
            ->where('student_id', $studentId)
            ->select('subject_code', 'subject_name', 'grade', 'year_level', 'semester')
            ->get();

        // Format curriculum display
        $curriculumDisplay = $student->curriculum;
        if ($student->curriculum == '2018') {
            $curriculumDisplay = '2018-2019';
        }

        // Format student type
        $studentType = '';
        if ($student->is_regular == 1) {
            $studentType = 'Regular';
        } elseif ($student->is_regular == 2) {
            $studentType = 'Irregular';
        } elseif ($student->is_regular == 3) {
            $studentType = 'Transferee';
        }

        return response()->json([
            'student' => [
                'id_no' => $student->id_no,
                'name' => $student->firstname . ' ' . $student->lastname,
                'firstname' => $student->firstname,
                'lastname' => $student->lastname,
                'middlename' => $student->middlename,
                'year_level' => $student->year_level,
                'student_type' => $studentType,
                'curriculum' => $student->curriculum,
                'curriculum_formatted' => $curriculumDisplay,
                'enrollment_status' => $student->status,
                'email' => $student->email,
                'total_years' => $student->total_year
            ],
            'subjects' => $subjects,
            'average_grade' => $subjects->whereNotNull('grade')
                ->where('grade', '!=', 'INC')
                ->where('grade', '!=', 'DRP')
                ->where('grade', '!=', '')
                ->avg(function($subject) {
                    return is_numeric($subject->grade) ? (float)$subject->grade : null;
                })
        ]);
    }

    private function checkEmailExists(Request $request) 
    {
        try {
            
            $validator = Validator::make($request->all(), [
                'email' => 'required',
            ], [
                'email.required' => 'Email is required',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
            }

            $y = Instructor::where('email5', $request->email)->exists();

            if(!$y) {
                return response()->json([
                    'success' => false,
                    'message' => "You don't have an account with that email"
                ]);
            }
            $y = null;

            return response()->json(['success' => true, 'message' => 'Email is available']);

        } catch (\Exception $e) {
            Log::error('Email check error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error checking email']);
        }

    }


    private function sendInstructorOtpForgotPass(Request $request) {
        try {
            // Validate the registration data first
            $validator = Validator::make($request->all(), [
                'email' => [
                    'required',
                    'email',
                    'regex:/^[^\s@]+@evsu\.edu\.ph$/'
                ],
                'password' => [
                    'required',
                    'min:8'
                ]
            ], [
                'email.regex' => 'Please enter a valid EVSUmail address (username@evsu.edu.ph).',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ]);
            }

            // Check if email already exists
            $emailExists = Instructor::where('email5', $request->email)->exists();
            if (!$emailExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email account!'
                ]);
            }

            // Check password confirmation
            if ($request->password !== $request->repeatPassword) {
                return response()->json([
                    'success' => false,
                    'message' => 'Passwords do not match.'
                ]);
            }

            // Generate verification code (6 digits)
            $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);


            Cache::put('if_' . $request->email, [
                'otp' => $verificationCode,
                'password' => Hash::make($request->password),
                'email' => $request->email,
                'attempts' => 0
            ], now()->addMinutes(10)); 

            session(['rpi' => $request->email]);

            // Send verification email
            try {
                Mail::to($request->email)->send(new PasswordResetOtp($verificationCode));

                // Check if email was actually sent
                if (count(Mail::failures()) > 0) {
                    Log::error('Email failed to send to: ' . $request->email);
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to send verification email. Please try again.'
                    ]);
                }

                Log::info('Verification code sent successfully to: ' . $request->email);

                return response()->json([
                    'success' => true,
                    'message' => 'Verification code sent to your email!',
                    'email' => $request->email,
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to send verification email: ' . $e->getMessage());
                Log::error('Email error details: ', ['exception' => $e]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send verification email: ' . $e->getMessage()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Send verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    public function getEnrollmentRequests3(Request $request) {
        try {
            $instructor = Auth::guard('instructor')->user();
            
            // Get count of pending enrollment requests
            $count = DB::table('students')
                ->count();

            return response()->json([
                'count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching enrollment requests: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch enrollment requests'], 500);
        }
    }

    public function getPendingRequests(Request $request) {
        try {
            $instructor = Auth::guard('instructor')->user();
            
            // Get count of pending enrollment requests
            $count = DB::table('enrollmentrequests')
                ->where('status', 'Pending')
                ->count();

            return response()->json([
                'count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching enrollment requests: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch enrollment requests'], 500);
        }
    }


    private function checkActiveEnrollmentPeriod()
    {
        try {
            $currentDate = now()->format('Y-m-d');
            
            // Check if there's an active enrollment period where current date is between start and end
            $enrollmentPeriod = DB::table('enrollment_date')
                ->where('is_active', 1)
                ->whereDate('start', '<=', $currentDate)
                ->whereDate('end', '>=', $currentDate)
                ->first();
                
            Log::info('Enrollment period check', [
                'current_date' => $currentDate,
                'found_period' => $enrollmentPeriod ? true : false,
                'period_details' => $enrollmentPeriod
            ]);
            
            return $enrollmentPeriod;
            
        } catch (\Exception $e) {
            Log::error('Error checking enrollment period: ' . $e->getMessage());
            return null;
        }
    }


    private function InstructorResetPass(Request $request) {

        $registrationData = Cache::get('if_' . $request->email);
        if (!$registrationData) {
            $x = '0';
            return $x;
        }

        // Code is valid, proceed with registration
            DB::beginTransaction();

            $user = Instructor::where('email5', $request->email)->first();
            if (!$user) {
                DB::rollBack();
                $x = '7';
                return $x;
            }

            $user->update([
                'password' => $registrationData['password']
            ]);


            date_default_timezone_set('Asia/Manila');
            $todays_date=date("Y-m-d h:i:sa");
            $today=strtotime($todays_date);
            $date=date("Y-m-d h:i:sa", $today);

            
            // $clientInfo = $this->getClientDeviceInfoWithRequest($request);
            $clientInfo = $this->collectClientInformation($request);
            // $ipaddress = $this->getClientRealIp();
            $ipaddress = $this->getClientDeviceInfo();
            


            //Save to AuditLogs!
            $audit = new AuditLog();
            $audit->user_id = $user->instructor_id;
            $audit->action = $registrationData['email'] .' Update New Password!';
            $audit->details = '' .$clientInfo['operating_system'] .'/' .$clientInfo['device_type'] .'/' .$clientInfo['user_agent'];
            $audit->ip_address = $ipaddress['ip_address'];
            $audit->date = $date;
            $audit->access_by = '107568';

            if (!$audit->save()) {
                $x = '7';
                return $x;
            }

            DB::commit();

            // Clear the cache
            Cache::forget('if_' . $request->email);

            $x = '9';
            return $x;
    }

    
}// End of Class
