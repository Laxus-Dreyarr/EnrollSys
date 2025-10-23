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
use App\Models\AuditLog;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectSchedule;
use App\Models\SubjectPrerequisite;
use App\Models\Section;
use App\Models\Enrollment;
use App\Models\EnrollmentRequest;
use App\Mail\RegistrationVerification;
use Illuminate\Support\Facades\Log;
use App\Mail\PasswordResetOtp;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

class StudentController extends Controller
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

    public function register(Request $request)
    {
        $action = $request->input('action');

        switch ($action) {
            case 'check_email':
                return $this->checkEmail($request);
            case 'send_verification':
                return $this->sendVerificationCode($request);
            case 'confirm_account':
                return $this->verifyRegister($request);
            case 'forgot_verification':
                return $this->sendStudentOtpForgotPass($request);
            case 'resetPassword_account':
                return $this->studentResetPass($request);
            case 'login':
                return $this->loginStudent($request);

            case 'verify_code':
                return $this->verifyCodeAndRegister($request);
            case 'register':
                return $this->processRegistration($request);
            case 'complete_student_info':
                return $this->completeStudentInfo($request);
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action'
                ]);
        }
    }


    private function sendVerificationCode(Request $request)
    {
        try {
            // Validate the registration data first
            $validator = Validator::make($request->all(), [
                'givenName' => [
                    'required',
                    'string',
                    'min:2',
                    'max:50',
                    'regex:/^[A-Za-z\s\-\']+$/'
                ],
                'lastName' => [
                    'required',
                    'string',
                    'min:2',
                    'max:50',
                    'regex:/^[A-Za-z\s\-\']+$/'
                ],
                'middleName' => [
                    'nullable',
                    'string',
                    'min:2',
                    'max:50',
                    'regex:/^[A-Za-z\s\-\']+$/'
                ],
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
            $emailExists = User::where('email2', $request->email)->exists();
            if ($emailExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already registered!'
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

            // Get device information
            $deviceInfo = $this->getDeviceInfo();

            Cache::put('registration_' . $request->email, [
                'otp' => $verificationCode,
                'givenName' => $request->givenName,
                'lastName' => $request->lastName,
                'middleName' => $request->middleName,
                'password' => $request->password,
                'email' => $request->email,
                'attempts' => 0,
                'ip_address' => request()->ip(),
                'device_info' => $deviceInfo, // Store device info in cache
                'device_summary' => $this->getDeviceSummary()
            ], now()->addMinutes(10));

            session(['registration_email' => $request->email]);

            // Send verification email
            try {
                Mail::to($request->email)->send(new RegistrationVerification($verificationCode, $request->givenName, $request->lastName));

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

    // private function sendVerificationCode(Request $request)
    // {
    //     try {
    //         // Validate the registration data first
    //         $validator = Validator::make($request->all(), [
    //             'givenName' => [
    //                 'required',
    //                 'string',
    //                 'min:2',
    //                 'max:50',
    //                 'regex:/^[A-Za-z\s\-\']+$/'
    //             ],
    //             'lastName' => [
    //                 'required',
    //                 'string',
    //                 'min:2',
    //                 'max:50',
    //                 'regex:/^[A-Za-z\s\-\']+$/'
    //             ],
    //             'middleName' => [
    //                 'nullable',
    //                 'string',
    //                 'min:2',
    //                 'max:50',
    //                 'regex:/^[A-Za-z\s\-\']+$/'
    //             ],
    //             'email' => [
    //                 'required',
    //                 'email',
    //                 'regex:/^[^\s@]+@evsu\.edu\.ph$/'
    //             ],
    //             'password' => [
    //                 'required',
    //                 'min:8'
    //             ]
    //         ], [
    //             'email.regex' => 'Please enter a valid EVSUmail address (username@evsu.edu.ph).',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => $validator->errors()->first()
    //             ]);
    //         }

    //         // Check if email already exists
    //         $emailExists = User::where('email2', $request->email)->exists();
    //         if ($emailExists) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'This email is already registered!'
    //             ]);
    //         }

    //         // Check password confirmation
    //         if ($request->password !== $request->repeatPassword) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Passwords do not match.'
    //             ]);
    //         }

    //         // Generate verification code (6 digits)
    //         $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
    //         // Store registration data in cache for 10 minutes
    //         $cacheKey = 'registration_' . $request->email;
    //         $registrationData = [
    //             'givenName' => $request->givenName,
    //             'lastName' => $request->lastName,
    //             'middleName' => $request->middleName,
    //             'email' => $request->email,
    //             'password' => $request->password,
    //             'verification_code' => $verificationCode,
    //             'attempts' => 0,
    //             'created_at' => now()
    //         ];

    //         Cache::put($cacheKey, $registrationData, 600); // 10 minutes

    //         // Send verification email
    //         try {
    //             Mail::to($request->email)->send(new RegistrationVerification($verificationCode, $request->givenName, $request->lastName));

    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Verification code sent to your email!',
    //                 'email' => $request->email
    //             ]);

    //         } catch (\Exception $e) {
    //             Log::error('Failed to send verification email: ' . $e->getMessage());
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Failed to send verification email. Please try again.'
    //             ]);
    //         }

    //     } catch (\Exception $e) {
    //         Log::error('Send verification error: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An error occurred. Please try again.'
    //         ]);
    //     }
    // }


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

    private function verifyRegister(Request $request){

        $code = $request->code;


        $registrationData = Cache::get('registration_' . $request->email);
        if (!$registrationData) {
            $x = '0';
            return $x;
        }

        // Code is valid, proceed with registration
            DB::beginTransaction();

            $studentId = $this->generateUniqueStudentId();
            $currentDate = now()->toDateTimeString();

            // Create user account
            $user = new User();
            $user->id = $studentId;
            $user->email2 = $registrationData['email'];
            $user->password = Hash::make($registrationData['password']);
            $user->profile = 'default.png';
            $user->date_created = $currentDate;
            $user->user_type = 'student';
            $user->is_active = 1;
            $user->last_login = null;

            if (!$user->save()) {
                $x = '7';
                return $x;
            }

            // Create user info
            $userInfo = new UserInfo();
            $userInfo->user_id = $studentId;
            $userInfo->firstname = ucfirst(strtolower($registrationData['givenName']));
            $userInfo->lastname = ucfirst(strtolower($registrationData['lastName']));
            $userInfo->middlename = $registrationData['middleName'] ? ucfirst(strtolower($registrationData['middleName'])) : null;
            $userInfo->birthdate = null;
            $userInfo->age = null;
            $userInfo->address = null;

            if (!$userInfo->save()) {
                $x = '7';
                return $x;
            }

            date_default_timezone_set('Asia/Manila');
            $todays_date=date("Y-m-d h:i:sa");
            $today=strtotime($todays_date);
            $date=date("Y-m-d h:i:sa", $today);


            //Save to Student!
            $student = new Student();
            $student->student_id = $userInfo->id;
            $student->id_no = 'None';
            $student->year_level = "NONE";
            $student->status = 'Not Enrolled';
            $student->is_regular = '1';

            if (!$student->save()) {
                $x = '7';
                return $x;
            }

            
            // $clientInfo = $this->getClientDeviceInfoWithRequest($request);
            $clientInfo = $this->collectClientInformation($request);
            // $ipaddress = $this->getClientRealIp();
            $ipaddress = $this->getClientDeviceInfo();
            


            //Save to AuditLogs!
            $audit = new AuditLog();
            $audit->user_id = $studentId;
            $audit->action = 'New Student Account Created '.$registrationData['email'];
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
            Cache::forget('registration_' . $request->email);

            $x = '9';
            return $x;

    }
    

    private function sendStudentOtpForgotPass(Request $request) {
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
            $emailExists = User::where('email2', $request->email)->exists();
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


            Cache::put('studentForgot_' . $request->email, [
                'otp' => $verificationCode,
                'password' => Hash::make($request->password),
                'email' => $request->email,
                'attempts' => 0
            ], now()->addMinutes(10));

            session(['reset_pass' => $request->email]);

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


    private function studentResetPass(Request $request) {
        $registrationData = Cache::get('studentForgot_' . $request->email);
        if (!$registrationData) {
            $x = '0';
            return $x;
        }

        // Code is valid, proceed with registration
            DB::beginTransaction();

            $user = User::where('email2', $request->email)->first();
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
            $audit->user_id = $user->id;
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
            Cache::forget('studentForgot_' . $request->email);

            $x = '9';
            return $x;
    }

    public function getClientDeviceInfoWithRequest(Request $request) {
        $userAgent = $request->userAgent() ?? 'Unknown';
        $ipAddress = $request->ip(); // Laravel handles proxy headers automatically
        
        $deviceInfo = $this->parseUserAgent($userAgent);
        
        return [
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_info' => $deviceInfo,
            'request_time' => now()->toDateTimeString(),
        ];
    }


    

    // Keep your existing methods (checkEmail, processRegistration, generateUniqueStudentId)
    private function checkEmail(Request $request)
    {
        try {
            if (!$request->has('email')) {
                return response()->json(['success' => false, 'message' => 'Email is required']);
            }

            $email = $request->input('email');
            $exists = User::where('email2', $email)->exists();

            return response()->json([
                'exists' => $exists,
                'message' => $exists ? 'This email is already registered!' : 'Email is available'
            ]);
        } catch (\Exception $e) {
            Log::error('Email check error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error checking email']);
        }
    }

    private function processRegistration(Request $request)
    {
        // This method is now deprecated in favor of the verification flow
        return response()->json([
            'success' => false,
            'message' => 'Please use the verification process'
        ]);
    }

    private function generateUniqueStudentId()
    {
        $maxAttempts = 10;
        $attempt = 0;

        do {
            $studentId = mt_rand(100000, 999999);
            $exists = User::where('id', $studentId)->exists();
            $attempt++;

            if ($attempt >= $maxAttempts) {
                throw new \Exception('Unable to generate unique student ID after ' . $maxAttempts . ' attempts');
            }
        } while ($exists);

        return $studentId;
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

        $y = User::where('email2', $email)->first();

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

        if (Auth::guard('student')->attempt([
            'email2' => $request->email,
            'password' => $request->password
            ])) {
            $request->session()->regenerate();
            return response()->json(10);
        }
        
    }


    public function dashboard(){

        if (!Auth::guard('student')->check()) {
            return redirect('/')->with('error', 'Please login first.');
        }

        $user = Auth::guard('student')->user();
        return view('student.dashboard.dashboard', compact('user'));
        
    }

    private function completeStudentInfo(Request $request)
    {
        try {
            // Validate the registration data first
            $validator = Validator::make($request->all(), [
                'school_id' => [
                    'required',
                    'max:50'
                ],
                'year_level' => [
                    'required'
                ],
                'student_type' => [
                    'required'
                ]
            ], [
                'school_id.required' => 'School ID is required.',
                'school_id.max' => 'School ID must not exceed 50 characters.',
                'year_level.required' => 'Please select your year level.',
                'student_type.required' => 'Please select your student type.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ]);
            }

            DB::beginTransaction();

            try {
                $studentType = match($request->student_type) {
                    'Regular' => '1',
                    'Irregular' => '2',
                    'Transferee' => '0',
                    default => $request->student_type
                };

                // Get the current authenticated user
                $user = Auth::guard('student')->user();
                
                // Find the user_info record for this user
                $userInfo = UserInfo::where('user_id', $user->id)->first();
                
                if (!$userInfo) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'User information not found.'
                    ]);
                }

                // Update using the user_info id as student_id
                $save = Student::where('student_id', $userInfo->id)
                    ->update([
                        'id_no' => $request->school_id,
                        'year_level' => $request->year_level,
                        'status' => 'Not Enrolled',
                        'is_regular' => $studentType
                    ]);

                if(!$save) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Not able to update student information.'
                    ]);
                }

                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Student information completed successfully'
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Student info update error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update student: ' . $e->getMessage()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Complete student info error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }


    // private function completeStudentInfo(Request $request)
    // {
    //     try {
    //         // Validate the registration data first
    //         $validator = Validator::make($request->all(), [
    //             'school_id' => [
    //                 'required',
    //                 'max:50'
    //             ],
    //             'year_level' => [
    //                 'required'
    //             ],
    //             'student_type' => [
    //                 'required'
    //             ]
    //         ], [
    //             'school_id.required' => 'School ID is required.',
    //             'school_id.max' => 'School ID must not exceed 50 characters.',
    //             'year_level.required' => 'Please select your year level.',
    //             'student_type.required' => 'Please select your student type.'
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => $validator->errors()->first()
    //             ]);
    //         }

    //         DB::beginTransaction();

    //         try {
    //             $studentType = match($request->student_type) {
    //                 'Regular' => '1',
    //                 'Irregular' => '2',
    //                 'Transferee' => '0',
    //                 default => $request->student_type
    //             };

    //             $save = Student::where('student_id', Auth::guard('student')->user()->id)
    //                 ->update([
    //                     'id_no' => $request->school_id,
    //                     'year_level' => $request->year_level,
    //                     'status' => 'Not Enrolled',
    //                     'is_regular' => $studentType
    //                 ]);

    //             if(!$save) {
    //                 DB::rollback();
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'Not able to update student information.'
    //                 ]);
    //             }

    //             DB::commit();
                
    //             // Implement the logic to complete student information
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Student information completed successfully'
    //             ]);

    //         } catch (\Exception $e) {
    //             DB::rollback();
    //             return redirect()->back()->with('error', 'Failed to update student: ' . $e->getMessage());
    //         }


    //     } catch (\Exception $e) {
    //         Log::error('Save error: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An error occurred: ' . $e->getMessage()
    //         ]);
    //     }
        
        
    // }

    public function getEnrollmentSubjects(Request $request)
    {
        try {
            $user = Auth::guard('student')->user();
            $student = $user->user_information->student;
            
            // Get student's year level
            $yearLevel = $student->year_level;
            
            // Determine current semester (you might want to make this dynamic)
            $currentSemester = '2nd Sem'; // or get from system settings
            
            // Get subjects for student's year level and current semester
            $subjects = Subject::where('year_level', $yearLevel)
                            ->where('semester', $currentSemester)
                            ->where('is_active', 1)
                            ->with(['schedules', 'prerequisites'])
                            ->get();
            
            // Get student's completed subjects (for prerequisite checking)
            $completedSubjects = [];
            if ($student->is_regular != 1) { // Irregular student
                $completedSubjects = Enrollment::where('student_id', $student->id)
                                            ->where('status', 'Enrolled')
                                            ->whereNotNull('grade')
                                            ->where('grade', '<=', 3.0) // Assuming passing grade
                                            ->pluck('subject_id')
                                            ->toArray();
            }
            
            // Calculate total units for the semester
            $totalUnits = $subjects->sum('units');
            
            return response()->json([
                'success' => true,
                'subjects' => $subjects,
                'completed_subjects' => $completedSubjects,
                'total_units' => $totalUnits,
                'is_regular' => $student->is_regular == 1,
                'year_level' => $yearLevel,
                'semester' => $currentSemester
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get enrollment subjects error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load enrollment data'
            ]);
        }
    }

    // public function enrollSubjects(Request $request)
    // {
    //     try {
    //         $user = Auth::guard('student')->user();
    //         $student = $user->user_information->student;
            
    //         $validator = Validator::make($request->all(), [
    //             'subjects' => 'required|array',
    //             'subjects.*' => 'exists:subjects,id'
    //         ]);
            
    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Invalid subjects selected'
    //             ]);
    //         }
            
    //         DB::beginTransaction();
            
    //         // Create enrollment request
    //         $enrollmentRequest = new EnrollmentRequest();
    //         $enrollmentRequest->student_id = $student->id;
    //         $enrollmentRequest->status = 'Pending';
    //         $enrollmentRequest->save();
            
    //         // Create enrollment records for each subject
    //         foreach ($request->subjects as $subjectId) {
    //             $enrollment = new Enrollment();
    //             $enrollment->student_id = $student->id;
    //             $enrollment->subject_id = $subjectId;
    //             $enrollment->section_id = 1; // Default section, you might want to implement section selection
    //             $enrollment->status = 'Enrolled';
    //             $enrollment->save();
    //         }
            
    //         // Update student status
    //         $student->status = 'Pending';
    //         $student->save();
            
    //         DB::commit();
            
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Enrollment submitted successfully! Waiting for approval.'
    //         ]);
            
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         Log::error('Enrollment error: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Enrollment failed. Please try again.'
    //         ]);
    //     }
    // }

    public function enrollSubjects(Request $request)
    {
        try {
            $user = Auth::guard('student')->user();
            $student = $user->user_information->student;
            
            $validator = Validator::make($request->all(), [
                'subjects' => 'required|array',
                'subjects.*' => 'exists:subjects,id'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid subjects selected'
                ]);
            }
            
            DB::beginTransaction();
            
            // Check if student already has a pending enrollment request
            $existingRequest = EnrollmentRequest::where('student_id', $student->id)
                                            ->where('status', 'Pending')
                                            ->first();
            
            if ($existingRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a pending enrollment request. Please wait for approval.'
                ]);
            }
            
            // Create enrollment request
            $enrollmentRequest = new EnrollmentRequest();
            $enrollmentRequest->student_id = $student->id;
            $enrollmentRequest->status = 'Pending';
            $enrollmentRequest->request_date = now();
            $enrollmentRequest->save();
            
            // Create enrollment records for each subject
            foreach ($request->subjects as $subjectId) {
                $sectionId = $this->getOrCreateDefaultSection($subjectId);
                
                if (!$sectionId) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to assign section for subject. Please contact administrator.'
                    ]);
                }
                
                $enrollment = new Enrollment();
                $enrollment->student_id = $student->id;
                $enrollment->subject_id = $subjectId;
                $enrollment->section_id = $sectionId;
                $enrollment->status = 'Enrolled';
                $enrollment->enrollment_date = now();
                $enrollment->save();

                // Update section student count
                $section = Section::find($sectionId);
                if ($section) {
                    $section->current_students += 1;
                    $section->save();
                }
            }
            
            // Update student status
            $student->status = 'Pending';
            $student->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Enrollment submitted successfully! Waiting for approval.'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Enrollment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Enrollment failed: ' . $e->getMessage()
            ]);
        }
    }

    private function getOrCreateDefaultSection($subjectId)
    {
        try {
            // First, try to get any existing section for this subject
            $section = Section::where('subsched_id', function($query) use ($subjectId) {
                $query->select('id')
                    ->from('subjectschedules')
                    ->where('subject_id', $subjectId)
                    ->limit(1);
            })->first();

            if ($section) {
                return $section->id;
            }

            // If no section exists, create a default one
            $subjectSchedule = SubjectSchedule::where('subject_id', $subjectId)->first();
            
            if (!$subjectSchedule) {
                // Create a default schedule first
                $subjectSchedule = new SubjectSchedule();
                $subjectSchedule->subject_id = $subjectId;
                $subjectSchedule->Section = 'A';
                $subjectSchedule->Type = 'Lecture';
                $subjectSchedule->day = 'Monday';
                $subjectSchedule->start_time = '08:00:00';
                $subjectSchedule->end_time = '09:30:00';
                $subjectSchedule->room = 'TBA';
                $subjectSchedule->save();
            }

            // Create a default section
            $section = new Section();
            $section->subsched_id = $subjectSchedule->id;
            $section->section_name = 'A';
            $section->max_students = 50;
            $section->current_students = 0;
            $section->save();

            return $section->id;

        } catch (\Exception $e) {
            Log::error('Section creation error: ' . $e->getMessage());
            return null;
        }
    }
    




}//END OF Class