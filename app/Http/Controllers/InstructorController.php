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
                'user_type' => 'instructor',
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
