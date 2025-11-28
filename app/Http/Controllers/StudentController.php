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
use Illuminate\Support\Facades\Http;

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


    public function dashboard()
    {
        if (!Auth::guard('student')->check()) {
            return redirect('/')->with('error', 'Please login first.');
        }

        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;
        
        // Check if there's an active enrollment period
        $enrollmentPeriod = $this->checkActiveEnrollmentPeriod();
        $isEnrollmentActive = $enrollmentPeriod && $enrollmentPeriod->is_active == 1;
        
        return view('student.dashboard.dashboard', compact('user', 'isEnrollmentActive', 'enrollmentPeriod'));
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

    private function checkCompleteGrades($studentId)
    {
        try {
            Log::info('Checking complete grades for student', ['student_id' => $studentId]);

            // Get all enrolled subjects for the student
            $enrolledSubjects = DB::table('enrolled_sub')
                ->where('student_id', $studentId)
                ->get();

            Log::info('Enrolled subjects found', [
                'student_id' => $studentId,
                'enrolled_count' => $enrolledSubjects->count(),
                'subjects' => $enrolledSubjects->toArray()
            ]);

            // If no enrolled subjects, consider it as complete (no grades needed)
            if ($enrolledSubjects->isEmpty()) {
                Log::info('No enrolled subjects found for student', ['student_id' => $studentId]);
                return true;
            }

            // Check if all enrolled subjects have grades (not null and not empty)
            $incompleteSubjects = [];
            foreach ($enrolledSubjects as $subject) {
                $grade = $subject->grade;
                
                // If grade is null or empty string, mark as incomplete
                if ($grade === null || $grade === '' || trim($grade) === '') {
                    $incompleteSubjects[] = [
                        'subject_id' => $subject->subject_id,
                        'subject_code' => $subject->subject_code,
                        'grade' => $grade
                    ];
                    Log::info('Student has incomplete grade', [
                        'student_id' => $studentId, 
                        'subject_id' => $subject->subject_id,
                        'subject_code' => $subject->subject_code,
                        'grade' => $grade
                    ]);
                }
            }

            if (!empty($incompleteSubjects)) {
                Log::info('Student has incomplete grades', [
                    'student_id' => $studentId,
                    'incomplete_count' => count($incompleteSubjects),
                    'incomplete_subjects' => $incompleteSubjects
                ]);
                return false;
            }

            Log::info('Student has complete grades for all subjects', [
                'student_id' => $studentId,
                'total_subjects' => $enrolledSubjects->count()
            ]);
            return true;

        } catch (\Exception $e) {
            Log::error('Error checking complete grades: ' . $e->getMessage(), [
                'student_id' => $studentId,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    private function completeStudentInfo(Request $request)
    {
        try {
            // Validate the registration data first
            $validator = Validator::make($request->all(), [
                'school_id' => [
                    'required',
                    'max:50',
                    'regex:/^\d{4}-\d+$/'
                ],
                'year_level' => [
                    'required'
                ],
                'student_type' => [
                    'required'
                ],
                'curriculum' => [
                    'required'
                ]
            ], [
                'school_id.required' => 'School ID is required.',
                'school_id.max' => 'School ID must not exceed 50 characters.',
                'school_id.regex' => 'School ID must be in the format: YYYY-XXXXX (e.g., 2020-30617).',
                'year_level.required' => 'Please select your year level.',
                'student_type.required' => 'Please select your student type.',
                'curriculum.required' => 'Please select your curriculum.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ]);
            }

            // Validate curriculum exists and is active
            $curriculum = DB::table('curriculum')
                ->where('curriculum_year', $request->curriculum)
                ->where('is_active', 1)
                ->first();

            if (!$curriculum) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected curriculum is not available or inactive.'
                ]);
            }

            // Extract year from school ID for validation only (not for curriculum)
            $schoolId = $request->school_id;
            $schoolIdYear = explode('-', $schoolId)[0];
            $currentYear = date('Y');
            
            // Validate school ID year range (optional validation)
            if ($schoolIdYear < 2013) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID year must be 2013 or later.'
                ]);
            }
            
            if ($schoolIdYear > $currentYear) {
                return response()->json([
                    'success' => false,
                    'message' => 'School ID year cannot exceed the current year.'
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

                // Update using the user_info id as student_id - USE THE SELECTED CURRICULUM
                $save = Student::where('student_id', $userInfo->id)
                    ->update([
                        'id_no' => $request->school_id,
                        'year_level' => $request->year_level,
                        'status' => 'Not Enrolled',
                        'is_regular' => $studentType,
                        'curriculum' => $request->curriculum // Use the selected curriculum, not school ID year
                    ]);

                if(!$save) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Not able to update student information.'
                    ]);
                }

                // Find the student record for this user
                $studentInfo = Student::where('student_id', $userInfo->id)->first();

                // Auto-insert subjects based on student type - USE THE SELECTED CURRICULUM
                if ($studentType == '1') { // Regular student
                    $this->autoInsertSubjectsForRegularStudent($studentInfo->id, $request->year_level, $request->curriculum);
                } elseif ($studentType == '2') { // Irregular student
                    $this->autoInsertSubjectsForIrregularStudent($studentInfo->id, $request->curriculum);
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

    private function autoInsertSubjectsForRegularStudent($studentId, $selectedYearLevel, $selectedCurriculumYear)
    {
        try {
            // Determine current semester
            $currentSemester = '2nd Sem';
            
            Log::info('Auto-inserting subjects for regular student', [
                'student_id' => $studentId,
                'selected_year_level' => $selectedYearLevel,
                'current_semester' => $currentSemester,
                'selected_curriculum_year' => $selectedCurriculumYear // Changed variable name for clarity
            ]);

            $subjectsToInsert = [];
            $now = now();

            // Get the curriculum ID based on the SELECTED curriculum year
            $curriculum = DB::table('curriculum')
                ->where('curriculum_year', $selectedCurriculumYear) // Use the selected curriculum
                ->where('is_active', 1)
                ->first();

            if (!$curriculum) {
                Log::error('Selected curriculum not found or inactive for regular student', [
                    'student_id' => $studentId,
                    'selected_curriculum_year' => $selectedCurriculumYear
                ]);
                return;
            }

            $curriculumId = $curriculum->id;

            // Get all active subjects for the curriculum
            $allSubjects = Subject::where('is_active', 1)
                ->whereIn('year_level', ['1st Year', '2nd Year', '3rd Year', '4th Year'])
                ->where('curriculum_id', $curriculumId)
                ->get();

            Log::info('Total subjects found for irregular student', [
                'total_subjects' => $allSubjects->count(),
                'curriculum_id' => $curriculumId,
                'selected_curriculum_year' => $selectedCurriculumYear
            ]);

            // Filter subjects based on exclusion criteria
            $filteredSubjects = $allSubjects->filter(function($subject) {
                // Exclude Capstone Project and Research 2 (IT 433) and Practicum (IT 429)
                if (in_array($subject->code, ['IT500', 'IT100'])) {
                    Log::info('Excluding subject due to exclusion list', [
                        'subject_code' => $subject->code,
                        'subject_name' => $subject->name
                    ]);
                    return false;
                }
                return true;
            });

            Log::info('Filtered subjects for irregular student', [
                'filtered_count' => $filteredSubjects->count(),
                'included_subjects' => $filteredSubjects->pluck('code')->toArray(),
                'selected_curriculum_year' => $selectedCurriculumYear
            ]);

            // Prepare data for insertion
            foreach ($filteredSubjects as $subject) {
                $subjectsToInsert[] = [
                    'student_id' => $studentId,
                    'subject_id' => $subject->id,
                    'subject_code' => $subject->code,
                    'subject_name' => $subject->name,
                    'units' => $subject->units,
                    'year_level' => $subject->year_level,
                    'semester' => $subject->semester,
                    'date_enrolled' => $now
                ];
            }

            // Insert into enrolled_sub table
            if (!empty($subjectsToInsert)) {
                DB::table('enrolled_sub')->insert($subjectsToInsert);
                Log::info('Successfully inserted subjects for irregular student', [
                    'student_id' => $studentId,
                    'subjects_count' => count($subjectsToInsert),
                    'selected_curriculum_year' => $selectedCurriculumYear
                ]);
            } else {
                Log::warning('No subjects to insert for irregular student', [
                    'student_id' => $studentId,
                    'selected_curriculum_year' => $selectedCurriculumYear
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error auto-inserting subjects for regular student: ' . $e->getMessage(), [
                'student_id' => $studentId,
                'selected_year_level' => $selectedYearLevel,
                'selected_curriculum_year' => $selectedCurriculumYear
            ]);
            throw $e;
        }
    }

    public function getAvailableCurricula()
    {
        try {
            $curricula = DB::table('curriculum')
                ->where('is_active', 1)
                ->orderBy('curriculum_year', 'desc')
                ->get(['id', 'curriculum_year']);
                
            return response()->json([
                'success' => true,
                'curricula' => $curricula
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching curricula: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load curricula'
            ]);
        }
    }

    private function autoInsertSubjectsForIrregularStudent($studentId, $selectedCurriculumYear)
    {
        try {
            Log::info('Auto-inserting subjects for irregular student', [
                'student_id' => $studentId,
                'selected_curriculum_year' => $selectedCurriculumYear // Changed variable name
            ]);

            $subjectsToInsert = [];
            $now = now();

            // Get the curriculum ID based on the SELECTED curriculum year
            $curriculum = DB::table('curriculum')
                ->where('curriculum_year', $selectedCurriculumYear) // Use the selected curriculum
                ->where('is_active', 1)
                ->first();

            if (!$curriculum) {
                Log::error('Selected curriculum not found or inactive', [
                    'student_id' => $studentId,
                    'selected_curriculum_year' => $selectedCurriculumYear
                ]);
                return;
            }

            $curriculumId = $curriculum->id;

            Log::info('Found curriculum', [
                'curriculum_id' => $curriculumId,
                'selected_curriculum_year' => $selectedCurriculumYear
            ]);

            // Get all active subjects from 1st to 4th year for the selected curriculum
            $allSubjects = Subject::where('is_active', 1)
                ->whereIn('year_level', ['1st Year', '2nd Year', '3rd Year', '4th Year'])
                ->where('curriculum_id', $curriculumId)
                ->get();

            Log::info('Total subjects found for irregular student', [
                'total_subjects' => $allSubjects->count(),
                'curriculum_id' => $curriculumId,
                'selected_curriculum_year' => $selectedCurriculumYear
            ]);

            // Filter subjects based on exclusion criteria
            $filteredSubjects = $allSubjects->filter(function($subject) {
                // Exclude Capstone Project and Research 2 (IT 433) and Practicum (IT 429)
                if (in_array($subject->code, ['IT500', 'IT100'])) {
                    Log::info('Excluding subject due to exclusion list', [
                        'subject_code' => $subject->code,
                        'subject_name' => $subject->name
                    ]);
                    return false;
                }
                return true;
            });

            Log::info('Filtered subjects for irregular student', [
                'filtered_count' => $filteredSubjects->count(),
                'included_subjects' => $filteredSubjects->pluck('code')->toArray(),
                'selected_curriculum_year' => $selectedCurriculumYear
            ]);

            // Prepare data for insertion
            foreach ($filteredSubjects as $subject) {
                $subjectsToInsert[] = [
                    'student_id' => $studentId,
                    'subject_id' => $subject->id,
                    'subject_code' => $subject->code,
                    'subject_name' => $subject->name,
                    'units' => $subject->units,
                    'year_level' => $subject->year_level,
                    'semester' => $subject->semester,
                    'date_enrolled' => $now
                ];
            }

            // Insert into enrolled_sub table
            if (!empty($subjectsToInsert)) {
                DB::table('enrolled_sub')->insert($subjectsToInsert);
                Log::info('Successfully inserted subjects for irregular student', [
                    'student_id' => $studentId,
                    'subjects_count' => count($subjectsToInsert),
                    'selected_curriculum_year' => $selectedCurriculumYear
                ]);
            } else {
                Log::warning('No subjects to insert for irregular student', [
                    'student_id' => $studentId,
                    'selected_curriculum_year' => $selectedCurriculumYear
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error auto-inserting subjects for irregular student: ' . $e->getMessage(), [
                'student_id' => $studentId,
                'selected_curriculum_year' => $selectedCurriculumYear,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function getFailedGrades()
    {
        return ['4.0', '5.0', 'INC', 'DRP']; // Removed 'ND' from failed grades
    }

    private function hasFailedPrerequisites($subject, $studentId)
    {
        if (!$subject->prerequisites || $subject->prerequisites->isEmpty()) {
            return false;
        }

        $failedGrades = $this->getFailedGrades();
        
        foreach ($subject->prerequisites as $prerequisite) {
            $grade = DB::table('enrolled_sub')
                ->where('student_id', $studentId)
                ->where('subject_id', $prerequisite->id)
                ->whereNotNull('grade')
                ->value('grade');
            
            // If the prerequisite exists and has a failed grade, return true
            if ($grade && in_array($grade, $failedGrades)) {
                return true;
            }
        }
        
        return false;
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

    // Update the getEnrollmentSubjects method for irregular students
    // public function getEnrollmentSubjects(Request $request)
    // {
    //     try {
    //         $user = Auth::guard('student')->user();
    //         $student = $user->user_information->student;
            
    //         Log::info('Get enrollment subjects called', [
    //             'user_id' => $user->id,
    //             'student_id' => $student->id,
    //             'student_table_id' => $student->id, // This is the ID from students table
    //             'is_regular' => $student->is_regular,
    //             'year_level' => $student->year_level
    //         ]);

    //         // Get student's year level
    //         $yearLevel = $student->year_level;
            
    //         // Determine current semester
    //         $currentSemester = '2nd Sem';
            
    //         // Get completed subjects from enrolled_sub table
    //         $completedSubjects = DB::table('enrolled_sub')
    //             ->where('student_id', $student->id) // This should match students.id
    //             ->pluck('subject_id')
    //             ->toArray();

    //         Log::info('Completed subjects retrieved', [
    //             'student_id_used' => $student->id,
    //             'completed_count' => count($completedSubjects),
    //             'completed_subjects' => $completedSubjects
    //         ]);

    //         // Debug: Check what's actually in enrolled_sub for this student
    //         $enrolledSubRecords = DB::table('enrolled_sub')
    //             ->where('student_id', $student->id)
    //             ->get();

    //         Log::info('Enrolled_sub records for student', [
    //             'records' => $enrolledSubRecords->toArray()
    //         ]);
            
    //         if ($student->is_regular == 1) {
    //             // Regular student logic
    //             $subjects = Subject::where('year_level', $yearLevel)
    //                 ->where('semester', $currentSemester)
    //                 ->where('is_active', 1)
    //                 ->with(['schedules', 'prerequisites'])
    //                 ->get();

    //             Log::info('Regular student subjects', ['count' => $subjects->count()]);
    //         } else {
    //             // Irregular student logic
    //             $subjects = $this->getAvailableSubjectsForIrregular($student, $yearLevel, $currentSemester, $completedSubjects);
    //         }
            
    //         // Calculate total units for the semester
    //         $totalUnits = $this->calculateTotalUnitsForSemester($yearLevel, $currentSemester);
            
    //         $response = [
    //             'success' => true,
    //             'subjects' => $subjects,
    //             'completed_subjects' => $completedSubjects,
    //             'total_units' => $totalUnits,
    //             'is_regular' => $student->is_regular == 1,
    //             'year_level' => $yearLevel,
    //             'semester' => $currentSemester
    //         ];

    //         Log::info('Enrollment subjects response', [
    //             'subjects_count' => $subjects->count(),
    //             'is_regular' => $student->is_regular == 1
    //         ]);

    //         return response()->json($response);
            
    //     } catch (\Exception $e) {
    //         Log::error('Get enrollment subjects error: ' . $e->getMessage(), [
    //             'trace' => $e->getTraceAsString()
    //         ]);
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to load enrollment data'
    //         ]);
    //     }
    // }
    public function getEnrollmentSubjects(Request $request)
    {
        try {
            $user = Auth::guard('student')->user();
            $student = $user->user_information->student;
            
            Log::info('Get enrollment subjects called', [
                'user_id' => $user->id,
                'student_table_id' => $student->id,
                'is_regular' => $student->is_regular,
                'year_level' => $student->year_level
            ]);

            // CHECK FOR EXISTING ENROLLMENT REQUEST FIRST
            $existingRequest = $this->hasExistingEnrollmentRequest($student->id);
            if ($existingRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an existing enrollment request with status: ' . $existingRequest->status
                ]);
            }

            // Get student's year level
            $yearLevel = $student->year_level;
            
            // Determine current semester
            $currentSemester = '2nd Sem';
            
            // Get all subjects the student has taken with their grades
            $studentGrades = DB::table('enrolled_sub')
                ->where('student_id', $student->id)
                ->whereNotNull('grade')
                ->get(['subject_id', 'grade', 'subject_code'])
                ->keyBy('subject_id');
            
            // Define passing grades (1.0 to 3.0 are passing)
            $passingGrades = ['1.0', '1.25', '1.5', '1.75', '2.0', '2.25', '2.5', '2.75', '3.0'];
            $failingGrades = ['4.0', '5.0', 'INC', 'DRP'];
            
            // Get subjects with passing grades
            $passedSubjects = $studentGrades->filter(function ($item) use ($passingGrades) {
                return in_array($item->grade, $passingGrades);
            })->keys()->toArray();

            // Get subjects with failing grades (need to be retaken)
            $failedSubjects = $studentGrades->filter(function ($item) use ($failingGrades) {
                return in_array($item->grade, $failingGrades);
            })->keys()->toArray();

            // Get all taken subjects (any grade) for exclusion (but we'll allow failed subjects to be retaken)
            $allTakenSubjects = $studentGrades->keys()->toArray();

            Log::info('Student subject data', [
                'student_id' => $student->id,
                'passed_subjects_count' => count($passedSubjects),
                'failed_subjects_count' => count($failedSubjects),
                'all_taken_subjects_count' => count($allTakenSubjects),
                'passed_subjects' => $passedSubjects,
                'failed_subjects' => $failedSubjects
            ]);
            
            // In the getEnrollmentSubjects method, update the subject query to include sections:
            if ($student->is_regular == 1) {
                // Regular student logic
                // $subjects = Subject::where('year_level', $yearLevel)
                //     ->where('semester', $currentSemester)
                //     ->where('is_active', 1)
                //     ->with(['schedules' => function($query) {
                //         $query->select('id', 'subject_id', 'Section', 'day', 'start_time', 'end_time', 'room');
                //     }, 'prerequisites'])
                //     ->get();
                // $subjects = Subject::where('is_active', 1)
                //     ->with(['schedules' => function($query) {
                //         $query->select('id', 'subject_id', 'Section', 'day', 'start_time', 'end_time', 'room');
                //     }, 'prerequisites'])
                //     ->orderBy('year_level', 'asc')
                //     ->get();
                $subjects = $this->getAvailableSubjectsForIrregular($student, $yearLevel, $currentSemester, $passedSubjects, $allTakenSubjects, $failedSubjects);
            } else {
                // Irregular student logic
                $subjects = $this->getAvailableSubjectsForIrregular($student, $yearLevel, $currentSemester, $passedSubjects, $allTakenSubjects, $failedSubjects);
            }
            
            // Calculate total units for the semester
            $totalUnits = $this->calculateTotalUnitsForSemester($yearLevel, $currentSemester);
            
            // Format the response with proper data structure
            $response = [
                'success' => true,
                'subjects' => $subjects->map(function($subject) use ($passedSubjects, $failedSubjects, $studentGrades) {
                    $hasPrerequisites = $subject->prerequisites && $subject->prerequisites->isNotEmpty();
                    
                    // Check if all prerequisites are met with passing grades
                    $prerequisitesMet = $hasPrerequisites ? 
                        $subject->prerequisites->every(function($prereq) use ($passedSubjects) {
                            return in_array($prereq->id, $passedSubjects);
                        }) : true;
                    
                    // Check if this subject was previously failed and needs retaking
                    $isFailedSubject = in_array($subject->id, $failedSubjects);
                    $previousGrade = $isFailedSubject ? ($studentGrades[$subject->id]->grade ?? 'Unknown') : null;
                    
                    // For failed subjects, we should display them regardless of prerequisites
                    // because they need to be retaken
                    $isSelectable = $isFailedSubject ? true : $prerequisitesMet;
                    
                    return [
                        'id' => $subject->id,
                        'code' => $subject->code,
                        'name' => $subject->name,
                        'units' => $subject->units,
                        'year_level' => $subject->year_level,
                        'semester' => $subject->semester,
                        'description' => $subject->description,
                        'has_prerequisites' => $hasPrerequisites,
                        'prerequisites_met' => $prerequisitesMet,
                        'is_failed_subject' => $isFailedSubject,
                        'previous_grade' => $previousGrade,
                        'schedules' => $subject->schedules ? $subject->schedules->map(function($schedule) {
                            return [
                                'day' => $schedule->day,
                                'start_time' => $schedule->start_time,
                                'end_time' => $schedule->end_time,
                                'room' => $schedule->room,
                                'section' => $schedule->Section // Ensure section is included
                            ];
                        }) : [],
                        'prerequisites' => $subject->prerequisites ? $subject->prerequisites->map(function($prereq) {
                            return [
                                'id' => $prereq->id,
                                'code' => $prereq->code,
                                'name' => $prereq->name
                            ];
                        }) : []
                    ];
                })->values()->toArray(),
                'passed_subjects' => $passedSubjects,
                'failed_subjects' => $failedSubjects,
                'total_units' => $totalUnits,
                'is_regular' => $student->is_regular == 1,
                'year_level' => $yearLevel,
                'semester' => $currentSemester
            ];

            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Get enrollment subjects error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load enrollment data: ' . $e->getMessage(),
                'subjects' => []
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
                'subjects' => 'required|string', // We'll parse this JSON
                'fhe_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // 5MB max
                'total_units' => 'required|integer'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data submitted: ' . $validator->errors()->first()
                ]);
            }
            
            // Parse subjects from JSON string
            $subjects = json_decode($request->subjects, true);
            
            if (!is_array($subjects)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid subjects data'
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
                    'message' => 'You already have an existing enrollment request with status: ' . $existingRequest->status
                ]);
            }
            
            // Handle FHE file upload
            if ($request->hasFile('fhe_file')) {
                $fheFile = $request->file('fhe_file');
                $fileName = 'fhe_' . $student->id . '_' . time() . '.' . $fheFile->getClientOriginalExtension();
                $filePath = $fheFile->storeAs('documents/fhe', $fileName, 'public');
                
                // Save to documents table
                DB::table('documents')->insert([
                    'student_id' => $student->id,
                    'type' => 'FHE',
                    'file_path' => $filePath,
                    'upload_date' => now()->format('Y-m-d H:i:s'),
                    'status' => 'Pending'
                ]);
            }
            
            // Create enrollment request
            $enrollmentRequest = new EnrollmentRequest();
            $enrollmentRequest->student_id = $student->id;
            $enrollmentRequest->status = 'Pending';
            $enrollmentRequest->request_date = now();
            $enrollmentRequest->save();
            
            // Create enrollment records for each subject with selected section
            foreach ($subjects as $enrollmentData) {
                $subjectId = $enrollmentData['subjectId'];
                $selectedSection = $enrollmentData['section'];
                
                $sectionId = $this->getOrCreateSection($subjectId, $selectedSection);
                
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

    private $paymongoSecretKey = 'sk_test_p2AqozyTfsgpqEePGv4A1pVF';


    public function createPaymentIntent(Request $request)
    {
        try {
            $user = Auth::guard('student')->user();
            $student = $user->user_information->student;

            $response = Http::withBasicAuth($this->paymongoSecretKey, '')
                ->post('https://api.paymongo.com/v1/payment_intents', [
                    'data' => [
                        'attributes' => [
                            'amount' => $request->amount,
                            'payment_method_allowed' => ['gcash'],
                            'payment_method_options' => [
                                'card' => [
                                    'request_three_d_secure' => 'any'
                                ]
                            ],
                            'currency' => 'PHP',
                            'description' => 'Organizational Fee - ' . $student->id_no,
                            'metadata' => [
                                'student_id' => $student->id,
                                'student_name' => $user->user_information->firstname . ' ' . $user->user_information->lastname
                            ]
                        ]
                    ]
                ]);

            $data = $response->json();

            if (isset($data['data'])) {
                $paymentIntent = $data['data'];

                // Create payment record
                DB::table('payments')->insert([
                    'student_id' => $student->id,
                    'payment_intent_id' => $paymentIntent['id'],
                    'amount' => $request->amount / 100, // Convert back to pesos
                    'currency' => 'PHP',
                    'payment_method' => 'gcash',
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'clientSecret' => $paymentIntent['attributes']['client_key'],
                    'paymentIntentId' => $paymentIntent['id']
                ]);
            } else {
                throw new \Exception('Failed to create payment intent: ' . ($data['errors'][0]['detail'] ?? 'Unknown error'));
            }

        } catch (\Exception $e) {
            Log::error('Payment intent creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed: ' . $e->getMessage()
            ]);
        }
    }

    public function confirmPayment(Request $request)
    {
        try {
            $user = Auth::guard('student')->user();
            $student = $user->user_information->student;

            // Verify payment with Paymongo
            $response = Http::withBasicAuth($this->paymongoSecretKey, '')
                ->get('https://api.paymongo.com/v1/payment_intents/' . $request->paymentIntentId);

            $data = $response->json();

            if (isset($data['data'])) {
                $paymentIntent = $data['data'];
                $status = $paymentIntent['attributes']['status'];

                // Update payment record
                DB::table('payments')
                    ->where('payment_intent_id', $request->paymentIntentId)
                    ->where('student_id', $student->id)
                    ->update([
                        'status' => $status,
                        'payment_details' => json_encode($paymentIntent),
                        'receipt_url' => $paymentIntent['attributes']['next_action']['redirect']['url'] ?? null,
                        'paid_at' => $status === 'succeeded' ? now() : null,
                        'updated_at' => now()
                    ]);

                if ($status === 'succeeded') {
                    return response()->json([
                        'success' => true,
                        'transactionId' => $request->paymentIntentId,
                        'message' => 'Payment confirmed successfully'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment not completed: ' . $status
                    ]);
                }
            } else {
                throw new \Exception('Payment verification failed');
            }

        } catch (\Exception $e) {
            Log::error('Payment confirmation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment confirmation failed: ' . $e->getMessage()
            ]);
        }
    }

    public function finalEnroll(Request $request)
    {
        try {
            $user = Auth::guard('student')->user();
            $student = $user->user_information->student;

            DB::beginTransaction();

            // Handle FHE file upload
            if ($request->hasFile('fhe_file')) {
                $fheFile = $request->file('fhe_file');
                $fheFileName = 'fhe_' . $student->id . '_' . time() . '.' . $fheFile->getClientOriginalExtension();
                $fheFilePath = $fheFile->storeAs('documents/fhe', $fheFileName, 'public');
                
                DB::table('documents')->insert([
                    'student_id' => $student->id,
                    'type' => 'FHE',
                    'file_path' => $fheFilePath,
                    'upload_date' => now()->format('Y-m-d H:i:s'),
                    'status' => 'Pending'
                ]);
            }

            // Handle payment receipt upload
            if ($request->hasFile('payment_receipt')) {
                $receiptFile = $request->file('payment_receipt');
                $receiptFileName = 'payment_receipt_' . $student->id . '_' . time() . '.' . $receiptFile->getClientOriginalExtension();
                $receiptFilePath = $receiptFile->storeAs('documents/payment_receipts', $receiptFileName, 'public');
                
                DB::table('payments')->insert([
                    'student_id' => $student->id,
                    'type' => 'PAYMENT_RECEIPT',
                    'file_path' => $receiptFilePath,
                    'upload_date' => now()->format('Y-m-d H:i:s'),
                    'status' => 'Pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Create enrollment request
            $enrollmentRequest = new EnrollmentRequest();
            $enrollmentRequest->student_id = $student->id;
            $enrollmentRequest->status = 'Pending';
            $enrollmentRequest->request_date = now();
            $enrollmentRequest->save();

            // Create enrollment records for each subject
            $subjects = json_decode($request->subjects, true);
            foreach ($subjects as $enrollmentData) {
                $subjectId = $enrollmentData['subjectId'];
                $selectedSection = $enrollmentData['section'];
                
                $sectionId = $this->getOrCreateSection($subjectId, $selectedSection);
                
                if ($sectionId) {
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
            }

            // Update student status
            $student->status = 'Pending';
            $student->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Enrollment submitted successfully! Your enrollment and payment receipt are pending verification.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Final enrollment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Enrollment failed: ' . $e->getMessage()
            ]);
        }
    }

    private function hasExistingEnrollmentRequest($studentId)
    {
        try {
            $existingRequest = EnrollmentRequest::where('student_id', $studentId)
                ->whereIn('status', ['Pending', 'Approved', 'Rejected'])
                ->first();
                
            return $existingRequest ? $existingRequest : false;
            
        } catch (\Exception $e) {
            Log::error('Error checking existing enrollment request: ' . $e->getMessage());
            return false;
        }
    }

    private function getOrCreateSection($subjectId, $sectionName)
    {
        try {
            // First, try to get existing section for this subject and section name
            $section = Section::where('subsched_id', function($query) use ($subjectId, $sectionName) {
                $query->select('id')
                    ->from('subjectschedules')
                    ->where('subject_id', $subjectId)
                    ->where('Section', $sectionName)
                    ->limit(1);
            })->first();

            if ($section) {
                return $section->id;
            }

            // If no section exists, create one
            $subjectSchedule = SubjectSchedule::where('subject_id', $subjectId)
                ->where('Section', $sectionName)
                ->first();
            
            if (!$subjectSchedule) {
                // Create a default schedule for this section
                $subjectSchedule = new SubjectSchedule();
                $subjectSchedule->subject_id = $subjectId;
                $subjectSchedule->Section = $sectionName;
                $subjectSchedule->Type = 'Lecture';
                $subjectSchedule->day = 'Monday';
                $subjectSchedule->start_time = '08:00:00';
                $subjectSchedule->end_time = '09:30:00';
                $subjectSchedule->room = 'TBA';
                $subjectSchedule->save();
            }

            // Create the section
            $section = new Section();
            $section->subsched_id = $subjectSchedule->id;
            $section->section_name = $sectionName;
            $section->max_students = 50;
            $section->current_students = 0;
            $section->save();

            return $section->id;

        } catch (\Exception $e) {
            Log::error('Section creation error: ' . $e->getMessage());
            return null;
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
    

    public function checkPastSubjects(Request $request)
    {
        try {
            $user = Auth::guard('student')->user();
            $student = $user->user_information->student;
            
            // Check if student has submitted past subjects
            $hasSubmitted = DB::table('enrolled_sub')
                ->where('student_id', $student->id)
                ->exists();
                
            return response()->json([
                'success' => true,
                'has_submitted' => $hasSubmitted
            ]);
            
        } catch (\Exception $e) {
            Log::error('Check past subjects error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check past subjects status'
            ]);
        }
    }

    public function getAllSubjects(Request $request)
    {
        try {
            $user = Auth::guard('student')->user();
            $student = $user->user_information->student;
            
            // Get the subjects that the student has already accomplished
            $completedSubjectIds = DB::table('enrolled_sub')
                ->where('student_id', $student->id)
                ->pluck('subject_id')
                ->toArray();

            Log::info('Completed subject IDs for student', [
                'student_id' => $student->id,
                'completed_count' => count($completedSubjectIds),
                'completed_subjects' => $completedSubjectIds
            ]);

            // Get all subjects except IT 433, IT 429 AND subjects already accomplished
            $subjects = Subject::whereNotIn('code', ['IT 433', 'IT 429'])
                ->where('is_active', 1)
                ->whereNotIn('id', $completedSubjectIds) // Exclude already accomplished subjects
                ->with(['prerequisites'])
                ->get()
                ->map(function($subject) {
                    return [
                        'id' => $subject->id,
                        'code' => $subject->code,
                        'name' => $subject->name,
                        'units' => $subject->units,
                        'year_level' => $subject->year_level,
                        'semester' => $subject->semester,
                        'description' => $subject->description,
                        'prerequisites' => $subject->prerequisites->map(function($prereq) {
                            return [
                                'id' => $prereq->id,
                                'code' => $prereq->code
                            ];
                        })
                    ];
                });

            Log::info('Filtered subjects for irregular student', [
                'total_subjects' => $subjects->count(),
                'excluded_completed' => count($completedSubjectIds)
            ]);
                
            return response()->json([
                'success' => true,
                'subjects' => $subjects
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get all subjects error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load subjects'
            ]);
        }
    }

    public function savePastSubjects(Request $request)
    {
        try {
            $user = Auth::guard('student')->user();
            $student = $user->user_information->student;
            
            Log::info('Save past subjects request received', [
                'student_id' => $student->id,
                'data' => $request->all()
            ]);
            
            $validator = Validator::make($request->all(), [
                'past_subjects' => 'required|array',
                'past_subjects.*.id' => 'required|exists:subjects,id',
                'past_subjects.*.code' => 'required|string',
                'past_subjects.*.name' => 'required|string',
                'past_subjects.*.units' => 'required|integer'
            ]);
            
            if ($validator->fails()) {
                Log::error('Validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid subjects data: ' . $validator->errors()->first()
                ]);
            }
            
            DB::beginTransaction();
            
            // Delete any existing past subjects for this student
            DB::table('enrolled_sub')->where('student_id', $student->id)->delete();
            
            // Get the actual subject details to use valid year_level and semester
            $subjectIds = collect($request->past_subjects)->pluck('id')->toArray();
            $subjectsDetails = Subject::whereIn('id', $subjectIds)
                ->get(['id', 'year_level', 'semester'])
                ->keyBy('id');
            
            // Insert new past subjects with valid year_level and semester
            foreach ($request->past_subjects as $subject) {
                $subjectDetail = $subjectsDetails[$subject['id']] ?? null;
                
                if (!$subjectDetail) {
                    Log::warning('Subject details not found for ID: ' . $subject['id']);
                    continue;
                }
                
                DB::table('enrolled_sub')->insert([
                    'student_id' => $student->id,
                    'subject_id' => $subject['id'],
                    'subject_code' => $subject['code'],
                    'subject_name' => $subject['name'],
                    'units' => $subject['units'],
                    'year_level' => $subjectDetail->year_level, // Use actual year level
                    'semester' => $subjectDetail->semester,     // Use actual semester
                    'date_enrolled' => now()
                ]);
            }
            
            DB::commit();
            
            Log::info('Past subjects saved successfully', [
                'student_id' => $student->id,
                'subjects_count' => count($request->past_subjects)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Past subjects saved successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Save past subjects error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save past subjects: ' . $e->getMessage()
            ]);
        }
    }

    private function getAvailableSubjectsForIrregular($student, $yearLevel, $semester, $passedSubjects, $allTakenSubjects, $failedSubjects)
    {
        Log::info('Getting available subjects for irregular student', [
            'student_id' => $student->id,
            'current_year_level' => $yearLevel,
            'current_semester' => $semester,
            'passed_subjects_count' => count($passedSubjects),
            'failed_subjects_count' => count($failedSubjects),
            'all_taken_subjects_count' => count($allTakenSubjects)
        ]);

        try {
            // For irregular students, get all subjects from 1st to 4th year for the current semester
            // that the student hasn't already PASSED (allow failed subjects to be retaken)
            // $allSubjects = Subject::where('is_active', 1)
            //     ->where('semester', $semester)
            //     ->whereIn('year_level', ['1st Year', '2nd Year', '3rd Year', '4th Year'])
            //     ->whereNotIn('id', $passedSubjects) // Only exclude passed subjects, not failed ones
            //     ->with(['schedules', 'prerequisites'])
            //     ->get();
            $curriculum_year = $student->curriculum;
            $curriculum = DB::table('curriculum')
                ->where('curriculum_year', $curriculum_year) // Use the selected curriculum
                ->where('is_active', 1)
                ->first();

            $curriculum_id = $curriculum->id;

            $allSubjects = Subject::where('is_active', 1)
                ->whereIn('year_level', ['1st Year', '2nd Year', '3rd Year', '4th Year'])
                ->where('curriculum_id', $curriculum_id)
                ->whereNotIn('id', $passedSubjects) // Only exclude passed subjects, not failed ones
                ->with(['schedules', 'prerequisites'])
                ->get();

            Log::info('Total subjects found for irregular student before prerequisite check', [
                'count' => $allSubjects->count(),
                'semester' => $semester
            ]);

            // Filter subjects based on prerequisites met with PASSING grades
            // BUT include failed subjects regardless of prerequisites (they need to be retaken)
            $availableSubjects = $allSubjects->filter(function($subject) use ($passedSubjects, $failedSubjects, $student) {
                // If this is a failed subject that needs retaking, always include it
                if (in_array($subject->id, $failedSubjects)) {
                    Log::info("Subject {$subject->code} ({$subject->id}) available - needs retaking (failed subject)");
                    return true;
                }
                
                // If subject has no prerequisites, it's available
                if ($subject->prerequisites->isEmpty()) {
                    Log::info("Subject {$subject->code} ({$subject->id}) available - no prerequisites");
                    return true;
                }
                
                // Check if all prerequisites are completed WITH PASSING GRADES
                $prerequisiteIds = $subject->prerequisites->pluck('id')->toArray();
                $completedPrerequisites = array_intersect($prerequisiteIds, $passedSubjects);
                
                $allPrerequisitesMet = count($prerequisiteIds) === count($completedPrerequisites);
                
                Log::info("Subject {$subject->code} ({$subject->id}) prerequisites check", [
                    'prerequisites' => $prerequisiteIds,
                    'passed_prerequisites' => $completedPrerequisites,
                    'all_met' => $allPrerequisitesMet
                ]);

                return $allPrerequisitesMet;
            });

            Log::info('Available subjects after filtering', [
                'count' => $availableSubjects->count(),
                'available_codes' => $availableSubjects->pluck('code')->toArray(),
                'failed_subjects_included' => $availableSubjects->whereIn('id', $failedSubjects)->pluck('code')->toArray()
            ]);

            return $availableSubjects;

        } catch (\Exception $e) {
            Log::error('Error in getAvailableSubjectsForIrregular: ' . $e->getMessage());
            return collect();
        }
    }

    private function calculateTotalUnitsForSemester($yearLevel, $semester)
    {
        // Calculate total units for the specific semester and year level
        $totalUnits = Subject::where('year_level', $yearLevel)
            ->where('semester', $semester)
            ->where('is_active', 1)
            ->sum('units');
        
        return $totalUnits;
    }




}//END OF Class