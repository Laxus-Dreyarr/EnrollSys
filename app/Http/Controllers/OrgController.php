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
use App\Models\Organization;
use App\Models\OrgInfo;
use App\Models\Student;
use App\Mail\RegistrationVerification;
use Illuminate\Support\Facades\Log;
use App\Mail\PasswordResetOtp;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class OrgController extends Controller
{
    
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
                return $this->registerOrg($request);
            case 'check_email':
                return $this->checkEmailExists($request);
            case 'forgot_verification':
                return $this->sendOrgOtpForgotPass($request);
            case 'resetPassword_account':
                return $this->OrgResetPass($request);
            case 'login':
                return $this->loginStudent($request);
        }

    }

    private function loginStudent(Request $request) {
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

        $y = Organization::where('email4', $email)->first();

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

        if (Auth::guard('org')->attempt([
            'email4' => $request->email,
            'password' => $request->password
            ])) {
            $request->session()->regenerate();
            return response()->json(10);
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

    private function registerOrg(Request $request) {
        try {
            Log::info('Starting instructor registration', ['email' => $request->email]);
            
            $passkey = Passkey::where('passkey', $request->email)->first();
            
            if(!$passkey) {
                Log::warning('Invalid passkey attempted', ['passkey' => $request->email]);
                return response()->json(['success' => false, 'message' => 'Invalid Passkey']);
            }
            
            Log::info('Passkey found', ['passkey_id' => $passkey->id, 'email3' => $passkey->email3]);
            
            $orgEmail = $passkey->email3;

            if($passkey->user_type !== 'organization') {
                Log::warning('Passkey user type mismatch', ['expected' => 'organization', 'found' => $passkey->user_type]);
                return response()->json(['success' => false, 'message' => 'Passkey not valid for organization registration']);
            }   

            $check = Organization::where('email4', $orgEmail)->exists();
            if($check) {
                Log::warning('Email already registered', ['email' => $orgEmail]);
                return response()->json(['success' => false, 'message' => 'Email is already registered']);
            }

            // Check if passwords match
            if ($request->input('password') !== $request->input('repeatPassword')) {
                return response()->json(['success' => false, 'message' => 'Passwords do not match']);
            }

            DB::beginTransaction();

            $orgId = $this->generateUniqueOrgId();
            $currentDate = now()->toDateTimeString();

            Log::info('Creating instructor record', ['instructor_id' => $orgId]);
            
            // Create instructor
            $organization = Organization::create([
                'org_id' => $orgId,
                'email4' => $orgEmail,
                'password' => Hash::make($request->input('password')),
                'profile' => 'default.png',
                'date_created' => $currentDate,
                'user_type' => $passkey->user_type,
                'is_active' => 1,    
                'last_login' => $currentDate,
            ]);

            Log::info('Creating instructor info record', ['instructor_id' => $orgId]);
            
            // Create instructor info
            OrgInfo::create([
                'organization_id' => $orgId,
                'firstname' => $request->input('givenName'),
                'lastname' => $request->input('lastName'),
                'middlename' => $request->input('middleName') ?? null,
                'birthdate' => 'null',
                'age' => 'null',
                'address' => 'null'
            ]);

            // $clientInfo = $this->getClientDeviceInfoWithRequest($request);
            $clientInfo = $this->collectClientInformation($request);
            // $ipaddress = $this->getClientRealIp();
            $ipaddress = $this->getClientDeviceInfo();

            // Save to AuditLogs!
            AuditLog::create([
                'user_id' => $orgId,
                'action' => 'New Org Member Account Created: '.$orgEmail,
                'details' => '' .$clientInfo['operating_system'] .'/' .$clientInfo['device_type'] .'/' .$clientInfo['user_agent'],
                'ip_address' => $ipaddress['ip_address'],
                'birthdate' => 'null',
                'date' => $currentDate,
                'access_by' => '107568'
            ]);

            // Invalidate the used passkey
            Passkey::where('passkey', $request->input('email'))
                    ->orWhere('email3', $orgEmail)
                    ->delete();
            
            DB::commit();
            
            Log::info('Instructor registration successful', ['instructor_id' => $orgId]);
            
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


    private function generateUniqueOrgId()
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

            $y = Organization::where('email4', $request->email)->exists();

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


    private function sendOrgOtpForgotPass(Request $request) {
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
            $emailExists = Organization::where('email4', $request->email)->exists();
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


            Cache::put('of_' . $request->email, [
                'otp' => $verificationCode,
                'password' => Hash::make($request->password),
                'email' => $request->email,
                'attempts' => 0
            ], now()->addMinutes(10)); 

            session(['rpo' => $request->email]);

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


    private function OrgResetPass(Request $request) {

        $registrationData = Cache::get('of_' . $request->email);
        if (!$registrationData) {
            $x = '0';
            return $x;
        }

        // Code is valid, proceed with registration
            DB::beginTransaction();

            $user = Organization::where('email4', $request->email)->first();
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
            $audit->user_id = $user->org_id;
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
            Cache::forget('of_' . $request->email);

            $x = '9';
            return $x;
    }

    public function dashboard()
    {
        if (!Auth::guard('org')->check()) {
            return redirect('/org')->with('error', 'Please login first.');
        }

        $user = Auth::guard('org')->user();
        
        // Check if there's an active enrollment period
        
        return view('org.dashboard.org-dashboard', compact('user'));
    }

    public function logout(Request $request)
    {
        // Check if user is logged in
        if (Auth::guard('org')->check()) {
            // Logout the org
            Auth::guard('org')->logout();
            
            // Invalidate the session
            $request->session()->invalidate();
            
            // Regenerate the CSRF token
            $request->session()->regenerateToken();
            
            // Return JSON response for AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Logged out successfully',
                    'redirect' => '/org'
                ]);
            }
            
            // Redirect to login page
            return redirect('/org')->with('success', 'Logged out successfully');
        }
        
        return response()->json([
            'success' => false,
            'message' => 'No user logged in'
        ], 401);
    }

    public function getDashboardData()
    {
        if (!Auth::guard('org')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // Get current organization
            $org = Auth::guard('org')->user();
            
            // Pending Enrollment Requests
            $pendingEnrollmentRequests = DB::table('enrollmentrequests')
                ->where('status', 'Pending')
                ->count();

            // Pending Payment Verifications
            $pendingPaymentVerifications = DB::table('payments')
                ->where('status', 'Pending')
                ->count();

            // Total Students (all students in the system)
            $totalStudents = DB::table('students')->count();

            // Approved This Week (enrollment requests approved in the current week)
            $approvedThisWeek = DB::table('payments')
                ->where('status', 'Approved')
                ->whereBetween('upload_date', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])
                ->count();

            // Recent Activity - Students with Pending Payment Verifications
            $recentActivities = DB::table('payments')
                ->join('students', 'payments.student_id', '=', 'students.id')
                ->where('payments.status', 'Pending')
                ->where('payments.type', 'PAYMENT_RECEIPT')
                ->select(
                    'payments.upload_date',
                    'students.id_no',
                    'students.year_level',
                    'payments.file_path',
                    DB::raw('(SELECT CONCAT(firstname, " ", lastname) FROM admin_info WHERE admin_id = 107568) as processed_by')
                )
                ->orderBy('payments.upload_date', 'desc')
                ->limit(5)
                ->get()
                ->map(function($payment) {
                    return [
                        'time' => date('h:i A', strtotime($payment->upload_date)),
                        'action' => 'Payment Verification Required',
                        'details' => "Student ID: {$payment->id_no} - {$payment->year_level}",
                        'date' => $payment->upload_date,
                        'student_id' => $payment->id_no,
                        'year_level' => $payment->year_level,
                        'file_path' => $payment->file_path,
                        'processed_by' => $payment->processed_by
                    ];
                });

            // If no pending payments, show a message
            if ($recentActivities->isEmpty()) {
                $recentActivities = collect([
                    [
                        'time' => '--:--',
                        'action' => 'No Pending Payments',
                        'details' => 'All payments have been verified',
                        'date' => now(),
                        'student_id' => null,
                        'year_level' => null,
                        'file_path' => null,
                        'processed_by' => null
                    ]
                ]);
            }

            // Quick Actions data
            $processingRate = $totalStudents > 0 ? 
                round((($totalStudents - $pendingEnrollmentRequests) / $totalStudents) * 100) : 0;
            
            $verificationRate = ($pendingPaymentVerifications + $approvedThisWeek) > 0 ? 
                round(($approvedThisWeek / ($pendingPaymentVerifications + $approvedThisWeek)) * 100) : 0;
            
            $activeStudentsRate = 92; // This would need more complex calculation

            return response()->json([
                'stats' => [
                    'pendingEnrollmentRequests' => $pendingEnrollmentRequests,
                    'pendingPaymentVerifications' => $pendingPaymentVerifications,
                    'totalStudents' => $totalStudents,
                    'approvedThisWeek' => $approvedThisWeek
                ],
                'recentActivities' => $recentActivities,
                'quickActions' => [
                    'processingRate' => $processingRate,
                    'verificationRate' => $verificationRate,
                    'activeStudentsRate' => $activeStudentsRate,
                    'pendingRequests' => $pendingEnrollmentRequests,
                    'pendingPayments' => $pendingPaymentVerifications
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching org dashboard data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch dashboard data'], 500);
        }
    }

    public function verifyPayment(Request $request)
    {
        if (!Auth::guard('org')->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $request->validate([
                'student_id' => 'required|string'
            ]);

            $studentId = $request->student_id;

            // Update payment status to Approved
            $updated = DB::table('payments')
                ->where('student_id', function($query) use ($studentId) {
                    $query->select('id')
                        ->from('students')
                        ->where('id_no', $studentId);
                })
                ->where('status', 'Pending')
                ->update([
                    'status' => 'Approved',
                    'updated_at' => now()->toDateTimeString()
                ]);

            if ($updated) {
                // Log the activity
                $clientInfo = $this->collectClientInformation();
                $ipaddress = $this->getClientDeviceInfo();

                AuditLog::create([
                    'user_id' => $studentId,
                    'action' => 'Payment verified by organization',
                    'details' => $clientInfo['operating_system'] . '/' . $clientInfo['device_type'] . '/' . $clientInfo['user_agent'],
                    'ip_address' => $ipaddress['ip_address'],
                    'date' => now()->toDateTimeString(),
                    'access_by' => Auth::guard('org')->user()->org_id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment verified successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending payment found for this student'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error verifying payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify payment'
            ], 500);
        }
    }


    // Student Management

    public function getStudentsData()
    {
        if (!Auth::guard('org')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // Fetch students with their basic information
            $students = DB::table('students')
                ->select(
                    'students.id',
                    'students.id_no',
                    'students.year_level',
                    'students.status',
                    'students.curriculum',
                    'students.is_regular'
                )
                ->where('students.id_no', '!=', 'None') // Exclude invalid student records
                ->orderBy('students.id_no', 'asc')
                ->get()
                ->map(function($student) {
                    return [
                        'id' => $student->id,
                        'student_id' => $student->id_no,
                        'year_level' => $student->year_level,
                        'status' => $student->status,
                        'curriculum' => $student->curriculum,
                        'is_regular' => $student->is_regular ? 'Regular' : 'Irregular',
                        'program' => 'BS Information Technology' // Default program since it's not stored in students table
                    ];
                });

            return response()->json([
                'students' => $students
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching students data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch students data'], 500);
        }
    }

    public function getStudentDetails($studentId)
    {
        try {
            // Get student basic info
            $student = DB::table('students')
                ->where('id_no', $studentId)
                ->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            // Get enrolled subjects from enrollments table (current enrollment)
            $subjects = DB::table('enrollments')
                ->join('subjects', 'enrollments.subject_id', '=', 'subjects.id')
                ->join('sections', 'enrollments.section_id', '=', 'sections.id')
                ->leftJoin('instructor_info', 'sections.instructor_id', '=', 'instructor_info.instructor_id')
                ->where('enrollments.student_id', $student->id)
                ->where('enrollments.status', 'Pending') // Only current enrollments
                ->select(
                    'subjects.code as subject_code',
                    'subjects.name as subject_name', 
                    'subjects.units',
                    'sections.section_name',
                    DB::raw('CONCAT(instructor_info.firstname, " ", instructor_info.lastname) as instructor_name')
                )
                ->get();

            // Get FHE document
            $fheDocument = DB::table('documents')
                ->where('student_id', $student->id)
                ->where('type', 'FHE')
                ->where('status', 'Pending')
                ->first();

            // Get payment receipt
            $paymentReceipt = DB::table('payments')
                ->where('student_id', $student->id)
                ->where('type', 'PAYMENT_RECEIPT')
                ->where('status', 'Pending')
                ->first();

            

            // Get payment receipt
            // $paymentReceipt = DB::table('payments')
            //     ->where('student_id', $student->id)
            //     ->where('type', 'PAYMENT_RECEIPT')
            //     ->where('status', 'Pending')
            //     ->first();

            // Get enrollment request details
            $enrollmentRequest = DB::table('enrollmentrequests')
                ->where('student_id', $student->id)
                ->where('status', 'Pending')
                ->first();

            // Use custom route for file access
            if ($fheDocument) {
                $filename = basename($fheDocument->file_path);
                $fheDocument->web_path = "/documents/fhe/{$filename}";
                
                // Debug: Check if file exists
                $fullPath = storage_path('app/public/' . $fheDocument->file_path);
                Log::info("FHE File:", [
                    'db_path' => $fheDocument->file_path,
                    'filename' => $filename,
                    'web_path' => $fheDocument->web_path,
                    'full_path' => $fullPath,
                    'exists' => file_exists($fullPath)
                ]);
            }

            if ($paymentReceipt) {
                $filename = basename($paymentReceipt->file_path);
                $paymentReceipt->web_path = "/documents/payment_receipts/{$filename}";
                
                // Debug: Check if file exists
                $fullPath = storage_path('app/public/' . $paymentReceipt->file_path);
                Log::info("Receipt File:", [
                    'db_path' => $paymentReceipt->file_path,
                    'filename' => $filename,
                    'web_path' => $paymentReceipt->web_path,
                    'full_path' => $fullPath,
                    'exists' => file_exists($fullPath)
                ]);
            }

            return response()->json([
                'student' => $student,
                'subjects' => $subjects,
                'fheDocument' => $fheDocument,
                'paymentReceipt' => $paymentReceipt,
                'enrollmentRequest' => $enrollmentRequest
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching student details: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch student details'], 500);
        }
    }

    public function approvePayment(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|string'
            ]);

            $studentId = $request->student_id;

            // Get student record
            $student = DB::table('students')
                ->where('id_no', $studentId)
                ->first();

            if (!$student) {
                return response()->json(['success' => false, 'message' => 'Student not found']);
            }

            DB::beginTransaction();

            // Update payment status
            $paymentUpdated = DB::table('payments')
                ->where('student_id', $student->id)
                ->where('status', 'Pending')
                ->update([
                    'status' => 'Approved',
                    'updated_at' => now()->toDateTimeString()
                ]);

            // Update document status
            $documentUpdated = DB::table('documents')
                ->where('student_id', $student->id)
                ->where('status', 'Pending')
                ->update([
                    'status' => 'Approved'
                ]);

            // Update enrollment request status if exists
            // $enrollmentUpdated = DB::table('enrollmentrequests')
            //     ->where('student_id', $student->id)
            //     ->where('status', 'Pending')
            //     ->update([
            //         'status' => 'Approved',
            //         'processed_date' => now()->toDateTimeString()
            //     ]);

            DB::commit();

            // Log the activity
            $clientInfo = $this->collectClientInformation();
            $ipaddress = $this->getClientDeviceInfo();

            AuditLog::create([
                'user_id' => $studentId,
                'action' => 'Payment approved by organization',
                'details' => $clientInfo['operating_system'] . '/' . $clientInfo['device_type'] . '/' . $clientInfo['user_agent'],
                'ip_address' => $ipaddress['ip_address'],
                'date' => now()->toDateTimeString(),
                'access_by' => '107568'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment and documents approved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve payment'
            ], 500);
        }
    }

    public function declinePayment(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|string'
            ]);

            $studentId = $request->student_id;

            // Get student record
            $student = DB::table('students')
                ->where('id_no', $studentId)
                ->first();

            if (!$student) {
                return response()->json(['success' => false, 'message' => 'Student not found']);
            }

            DB::beginTransaction();

            // Update payment status
            $paymentUpdated = DB::table('payments')
                ->where('student_id', $student->id)
                ->where('status', 'Pending')
                ->update([
                    'status' => 'Rejected',
                    'updated_at' => now()->toDateTimeString()
                ]);

            // Update document status
            $documentUpdated = DB::table('documents')
                ->where('student_id', $student->id)
                ->where('status', 'Pending')
                ->update([
                    'status' => 'Rejected'
                ]);


            DB::commit();

            // Log the activity
            $clientInfo = $this->collectClientInformation();
            $ipaddress = $this->getClientDeviceInfo();

            AuditLog::create([
                'user_id' => $studentId,
                'action' => 'Payment declined by organization',
                'details' => $clientInfo['operating_system'] . '/' . $clientInfo['device_type'] . '/' . $clientInfo['user_agent'],
                'ip_address' => $ipaddress['ip_address'],
                'date' => now()->toDateTimeString(),
                'access_by' => Auth::guard('org')->user()->org_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment and documents declined successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error declining payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to decline payment'
            ], 500);
        }
    }

    private function formatActivityAction($action)
    {
        if (str_contains($action, 'New Student Account Created')) {
            return 'New student account created';
        } elseif (str_contains($action, 'Grade input')) {
            return 'Grade input for subject';
        } elseif (str_contains($action, 'Enrollment')) {
            return 'Enrollment activity';
        } else {
            return $action;
        }
    }



}//End of Class
