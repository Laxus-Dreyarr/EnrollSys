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
use App\Mail\SendQuestion;
use Illuminate\Support\Facades\Log;
use App\Mail\PasswordResetOtp;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use DateTime;

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

            case 'complete_student_info2':
                return $this->completeStudentInfo2($request);

            case 'complete_student_info_starter':
                return $this->completeStudentInformStarter($request);

            case 'send_question':
                return $this->SendQuestion2($request);
                
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action'
                ]);
        }
    }

    // View Subject Information
    public function getSubjectDetails($id)
    {
        try {
            if (!Auth::guard('student')->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Get subject details
            $subject = Subject::find($id);
            
            if (!$subject) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not found'
                ]);
            }

            // Get prerequisites
            $prerequisites = DB::table('subjectprerequisites as sp')
                ->join('subjects as s', 'sp.prerequisite_id', '=', 's.id')
                ->where('sp.subject_id', $id)
                ->select('s.code', 's.name', 's.units', 's.year_level')
                ->get();

            return response()->json([
                'success' => true,
                'subject' => [
                    'code' => $subject->code,
                    'name' => $subject->name,
                    'description' => $subject->description,
                    'units' => $subject->units,
                    'year_level' => $subject->year_level,
                    'semester' => $subject->semester
                ],
                'prerequisites' => $prerequisites
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching subject details: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching subject details'
            ], 500);
        }
    }


    private function sendVerificationCode(Request $request)
    {
        try {
            // Validate the registration data
            $validator = Validator::make($request->all(), [
                'studentNo' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        try {
                            // Check if student number contains a dash
                            if (strpos($value, '-') === false) {
                                // $fail('The student number must contain a dash (e.g., 2020-30617).');
                                $fail('Invalid Student Number');
                                return;
                            }
                            
                            // Split the student number into year and number parts
                            $parts = explode('-', $value);
                            
                            if (count($parts) !== 2) {
                                // $fail('Invalid student number format. Use format: YYYY-NNNNN.');
                                $fail('Invalid Student Number');
                                return;
                            }
                            
                            $year = $parts[0];
                            $number = $parts[1];
                            
                            // Check if year is numeric and within range
                            if (!is_numeric($year) || strlen($year) !== 4) {
                                $fail('Invalid Student Number');
                                // $fail('The year part must be a 4-digit number.');
                                return;
                            }
                            
                            $year = (int)$year;
                            if ($year < 2013 || $year > 2050) {
                                $fail('Invalid Student Number');
                                // $fail('The year must be between 2013 and 2050.');
                                return;
                            }
                            
                            // Check if number part is valid
                            if (!is_numeric($number) || strlen($number) !== 5) {
                                // $fail('The number part must be a 5-digit number.');
                                $fail('Invalid Student Number');
                                return;
                            }
                            
                            // Check if student number already exists in database
                            if (DB::table('students')->where('id_no', $value)->exists()) {
                                $fail('This student number already exists.');
                                return;
                            }
                            
                        } catch (\Exception $e) {
                            $fail('Invalid student number format.');
                        }
                    }
                ],
                'email' => [
                    'required',
                    'email'
                ],
                'password' => [
                    'required',
                    'min:8'
                ],
                'birthDate' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) {
                        try {
                            $birthDate = Carbon::parse($value);
                            $today = Carbon::now();
                            $age = $birthDate->diffInYears($today);
                            
                            // Check if born in 2018 or earlier
                            $birthYear = $birthDate->year;
                            
                            if ($birthYear > 2008) {
                                // $fail('You must be born in 2018 or earlier to register.');
                                $fail('Input your real birthdate');
                            }
                            
                            // Check minimum age of 6 years
                            if ($age < 17) {
                                // $fail('You must be at least 17 years old to register.');
                                $fail('Input your real birthdate');
                            }
                        } catch (\Exception $e) {
                            $fail('Invalid date format.');
                        }
                    }
                ],
                'sex' => [
                    'required'
                ],
                'status' => [
                    'required'
                ],
                'houseStreet' => [
                    'required'
                ],
                'region' => [
                    'required'
                ],
                'province' => [
                    'required'
                ],
                'municipality' => [
                    'required'
                ],
                'barangay' => [
                    'required'
                ],
                'zip_code' => [
                    'required'
                ]
            ], [
                // Custom messages if needed
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ]);
            }

            // Calculate and store age for later use
            $birthDate = Carbon::parse($request->birthDate);
            $age = $birthDate->diffInYears(Carbon::now());

            // First, extract the year from the student number
            $studentYear = (int) explode('-', $request->studentNo)[0]; // Gets "2020" from "2020-30617"

            // Get all active curricula ordered by year (descending = newest first)
            $activeCurricula = DB::table('curriculum')
                                ->where('is_active', 1)
                                ->orderBy('curriculum_year', 'desc')
                                ->get();

            $assignedCurriculum = null;

            // Find the appropriate curriculum
            foreach ($activeCurricula as $curriculum) {
                if ($studentYear >= (int)$curriculum->curriculum_year) {
                    $assignedCurriculum = $curriculum->curriculum_year;
                    break;
                }
            }

            // If no curriculum found (student year is before all active curricula), use the oldest active one
            if (!$assignedCurriculum && $activeCurricula->isNotEmpty()) {
                $assignedCurriculum = $activeCurricula->last()->curriculum_year;
            }


            $find = DB::table('csv')
                    ->where('email', $request->email)
                    ->exists();

            // If email doesn't exist in CSV, check if it's a valid evsu.edu.ph email
            if (!$find) {
                // Check if it's an evsu.edu.ph email
                if (!preg_match('/^[^\s@]+@evsu\.edu\.ph$/i', $request->email)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Email not found in our records.'
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
                    'studentNo' => $request->studentNo,
                    'password' => $request->password,
                    'email' => $request->email,
                    'birthDate' => $request->birthDate,
                    'age' => $age, // Store calculated age
                    'sex' => $request->sex,
                    'houseStreet' => $request->houseStreet,
                    'region' => $request->region,
                    'province' => $request->province,
                    'municipality' => $request->municipality,
                    'barangay' => $request->barangay,
                    'zip_code' => $request->zip_code,
                    'relationship_status' => $request->status,
                    'is_regular' => '2',
                    'year_level' => "NONE",
                    'curriculum' => $assignedCurriculum,
                    'attempts' => 0,
                    'ip_address' => request()->ip(),
                    'device_info' => $deviceInfo,
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
                
            } else {

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
                    'studentNo' => $request->studentNo,
                    'password' => $request->password,
                    'email' => $request->email,
                    'birthDate' => $request->birthDate,
                    'age' => $age, // Store calculated age
                    'sex' => $request->sex,
                    'houseStreet' => $request->houseStreet,
                    'region' => $request->region,
                    'province' => $request->province,
                    'municipality' => $request->municipality,
                    'barangay' => $request->barangay,
                    'zip_code' => $request->zip_code,
                    'relationship_status' => $request->status,
                    'is_regular' => '1',
                    'year_level' => "1st Year",
                    'curriculum' => $assignedCurriculum,
                    'attempts' => 0,
                    'ip_address' => request()->ip(),
                    'device_info' => $deviceInfo,
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

                // ------ else
            }


        } catch (\Exception $e) {
            Log::error('Send verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }



    private function SendQuestion2(Request $request) 
    {
        try {
            Mail::to('carljamesduallo661@gmail.com')->send(new SendQuestion($request->q_name, $request->q_email, $request->q_subject, $request->q_ms));

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
                'message' => 'Verification code sent to your email!'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send verification email: ' . $e->getMessage());
            Log::error('Email error details: ', ['exception' => $e]);
                
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email: ' . $e->getMessage()
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

            $find = DB::table('csv')
            ->where('email', $registrationData['email'])
            ->first();

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
            // $userInfo = new UserInfo();
            // $userInfo->user_id = $studentId;
            // $userInfo->firstname = ucfirst(strtolower($registrationData['givenName']));
            // $userInfo->lastname = ucfirst(strtolower($registrationData['lastName']));
            // $userInfo->middlename = $registrationData['middleName'] ? ucfirst(strtolower($registrationData['middleName'])) : null;
            // $userInfo->birthdate = null;
            // $userInfo->age = null;
            // $userInfo->address = null;

            $userInfo = new UserInfo();
            $userInfo->user_id = $studentId;
            $userInfo->firstname = ucfirst(strtolower($find->firstname));
            $userInfo->lastname = ucfirst(strtolower($find->lastname));
            $userInfo->middlename = $find->middlename 
                ? ucfirst(strtolower($find->middlename)) 
                : null;
            $userInfo->phone_number = $find->contact_number ? $find->contact_number : null;
            $userInfo->birthdate = $registrationData['birthDate'];
            $userInfo->age = $registrationData['age'];
            $userInfo->sex = $registrationData['sex'];
            $userInfo->address = ''.$registrationData['houseStreet'].', '.$registrationData['barangay'] .', '.$registrationData['municipality'] .', '.$registrationData['province'];
            $userInfo->relationship_status = $registrationData['relationship_status'];

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
            $student->id_no = $registrationData['studentNo'];
            $student->year_level = $registrationData['year_level'];
            $student->is_regular = 6;
            $student->status = 'Not Enrolled';
            $student->curriculum = $registrationData['curriculum'];

            
            

            if (!$student->save()) {
                $x = '7';
                return $x;
            }

            DB::table('address')->insert([
                    'user_id' => $studentId,
                    'street_no' => $registrationData['houseStreet'],
                    'region' => $registrationData['region'],
                    'province' => $registrationData['province'],
                    'municipality' => $registrationData['municipality'],
                    'brgy' => $registrationData['barangay'],
                    'zipcode' => $registrationData['zip_code']
                ]);

            
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

            $this->autoInsertSubjectsForIrregularStudent($student->id, $registrationData['curriculum']);


            DB::commit();

            // Clear the cache
            Cache::forget('registration_' . $request->email);

            $x = '9';
            return $x;

    }
    

    private function sendStudentOtpForgotPass(Request $request) {
        try {
            // Validate the registration data first
            // $validator = Validator::make($request->all(), [
            //     'email' => [
            //         'required',
            //         'email',
            //         'regex:/^[^\s@]+@evsu\.edu\.ph$/'
            //     ],
            //     'password' => [
            //         'required',
            //         'min:8'
            //     ]
            // ], [
            //     'email.regex' => 'Please enter a valid EVSUmail address (username@evsu.edu.ph).',
            // ]);

            // if ($validator->fails()) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => $validator->errors()->first()
            //     ]);
            // }

            $validator = Validator::make($request->all(), [
                'email' => [
                    'required',
                    'email'
                ],
                'password' => [
                    'required',
                    'min:8'
                ]
            ], [
                
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
            // $exists = User::where('email2', $email)
            //   ->whereNotNull('password')
            //   ->exists();

            $find = DB::table('csv')
                    ->where('email', $email)
                    ->first();

            if(!$find) {
                return response()->json(['success' => false, 'message' => 'You are not allowed to register with this email!']);
            } 

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
        $userInfo = $user->user_information;

        // Get current date for SY and SEM calculation
        $currentYear = date('Y');
        $currentMonth = date('n'); // 1-12
        
        // Calculate School Year and Semester
        if ($currentMonth >= 7 && $currentMonth <= 12) {
            // July to December: First Semester of current school year
            $schoolYear = $currentYear . '-' . ($currentYear + 1);
            $semester = 'SEM 1';
        } else {
            // January to June: Second Semester of previous school year
            $schoolYear = ($currentYear - 1) . '-' . $currentYear;
            $semester = 'SEM 2';
        }
        
        $pageTitle = "SY: $schoolYear $semester";

        // Get notifications
        $notifications = DB::table('notifications')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $unreadCount = DB::table('notifications')
            ->where('user_id', $user->id)
            ->where('is_read', 0)
            ->count();

        // Get enrolled subjects with grades
        $enrolledSubjects = DB::table('enrolled_sub')
            ->where('student_id', $student->id)
            ->orderBy('year_level')
            ->orderBy('semester')
            ->get();

        // Calculate average grade (excluding non-numeric grades like INC, DRP)
        $totalGradePoints = 0;
        $totalUnits = 0;
        $gradeCount = 0;

        foreach ($enrolledSubjects as $subject) {
            // Check if grade is numeric (1.0, 2.0, 3.0, 4.0, 5.0)
            if (is_numeric($subject->grade)) {
                $gradeValue = (float)$subject->grade;
                $units = $subject->units;
                
                // For Philippine grading system: 1.0 is highest, 5.0 is lowest
                // Calculate grade points
                $totalGradePoints += ($gradeValue * $units);
                $totalUnits += $units;
                $gradeCount++;
            }
            // Handle INC, DRP, etc. (optional - you might want to exclude these)
        }

        // Calculate weighted average
        $averageGrade = ($totalUnits > 0) ? $totalGradePoints / $totalUnits : 0;

        // For gauge display, convert to percentage (1.0 = 100%, 5.0 = 0%)
        $gaugePercentage = 0;
        if ($averageGrade > 0) {
            // Convert 1.0-5.0 scale to 0-100% for gauge
            // Formula: 100 * (5.0 - grade) / 4.0
            $gaugePercentage = 100 * (5.0 - $averageGrade) / 4.0;
            $gaugePercentage = max(0, min(100, $gaugePercentage)); // Clamp between 0-100
        }
        
        // Check if there's an active enrollment period
        $enrollmentPeriod = $this->checkActiveEnrollmentPeriod();
        $isEnrollmentActive = $enrollmentPeriod && $enrollmentPeriod->is_active == 1;
        
        return view('student.dashboard.dashboard', compact(
            'user', 
            'isEnrollmentActive', 
            'enrollmentPeriod', 
            'enrolledSubjects', 
            'averageGrade', 
            'gaugePercentage', 
            'notifications', 
            'unreadCount', 
            'student', 
            'userInfo',
            'pageTitle'
        ));
    }


    public function getAverageGrade()
    {
        if (!Auth::guard('student')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;

        // Get enrolled subjects with grades
        $enrolledSubjects = DB::table('enrolled_sub')
            ->where('student_id', $student->id)
            ->get();
        
        // Calculate average grade
        $totalGradePoints = 0;
        $totalUnits = 0;
        $gradeCount = 0;
        
        foreach ($enrolledSubjects as $subject) {
            if (is_numeric($subject->grade)) {
                $gradeValue = (float)$subject->grade;
                $units = $subject->units;
                
                $totalGradePoints += ($gradeValue * $units);
                $totalUnits += $units;
                $gradeCount++;
            }
        }
        
        $averageGrade = ($totalUnits > 0) ? $totalGradePoints / $totalUnits : 0;
        
        // Format to 2 decimal places
        $formattedAverage = number_format($averageGrade, 2);
        
        // Calculate gauge percentage
        $gaugePercentage = 0;
        if ($averageGrade > 0) {
            $gaugePercentage = 100 * (5.0 - $averageGrade) / 4.0;
            $gaugePercentage = max(0, min(100, $gaugePercentage));
        }
        
        return response()->json([
            'average_grade' => $formattedAverage,
            'gauge_percentage' => $gaugePercentage,
            'grade_count' => $gradeCount,
            'total_units' => $totalUnits
        ]);
    }

    public function logout(Request $request)
    {
        // Check if user is logged in
        if (Auth::guard('student')->check()) {
            // Logout the student
            Auth::guard('student')->logout();
            
            // Invalidate the session
            $request->session()->invalidate();
            
            // Regenerate the CSRF token
            $request->session()->regenerateToken();
            
            // Return JSON response for AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Logged out successfully',
                    'redirect' => '/'
                ]);
            }
            
            // Redirect to login page
            return redirect('/')->with('success', 'Logged out successfully');
        }
        
        return response()->json([
            'success' => false,
            'message' => 'No user logged in'
        ], 401);
    }

    // private function checkActiveEnrollmentPeriod()
    // {
    //     try {
    //         $currentDate = now()->format('Y-m-d');
            
    //         // Check if there's an active enrollment period where current date is between start and end
    //         $enrollmentPeriod = DB::table('enrollment_date')
    //             ->where('is_active', 1)
    //             ->whereDate('start', '<=', $currentDate)
    //             ->whereDate('end', '>=', $currentDate)
    //             ->first();

                
    //         Log::info('Enrollment period check', [
    //             'current_date' => $currentDate,
    //             'found_period' => $enrollmentPeriod ? true : false,
    //             'period_details' => $enrollmentPeriod
    //         ]);
            
    //         return $enrollmentPeriod;
            
    //     } catch (\Exception $e) {
    //         Log::error('Error checking enrollment period: ' . $e->getMessage());
    //         return null;
    //     }
    // }

    private function checkActiveEnrollmentPeriod()
    {
        try {
            $currentDate = now()->format('Y-m-d');
            
            // First: Find and deactivate any expired enrollment periods
            DB::table('enrollment_date')
                ->where('is_active', 1)
                ->whereDate('end', '<', $currentDate)  // Past the end date
                ->update(['is_active' => 0]);
            
            // Also deactivate any not-yet-started periods if needed
            // DB::table('enrollment_date')
            //     ->where('is_active', 1)
            //     ->whereDate('start', '>', $currentDate)
            //     ->update(['is_active' => 0]);
            
            // Then: Check for current active enrollment period
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
                'curriculum2' => [
                    'required'
                ]
            ], [
                'school_id.required' => 'School ID is required.',
                'school_id.max' => 'School ID must not exceed 50 characters.',
                'school_id.regex' => 'School ID must be in the format: YYYY-XXXXX (e.g., 2020-30617).',
                'curriculum2.required' => 'Please select your curriculum.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ]);
            }

            // Validate curriculum exists and is active
            $curriculum = DB::table('curriculum')
                ->where('curriculum_year', $request->curriculum2)
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

            $curriculum = DB::table('students')
                ->where('id_no', $schoolId)
                ->first();

            if ($curriculum) {
                return response()->json([
                    'success' => false,
                    'message' => 'This School ID already exist!'
                ]);
            }
            
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
                // $studentType = match($request->student_type) {
                //     'Regular' => '1',
                //     'Irregular' => '2',
                //     'Transferee' => '5',
                //     default => $request->student_type
                // };

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
                        'status' => 'Not Enrolled',
                        'is_regular' => '5',
                        'curriculum' => $request->curriculum2 // Use the selected curriculum, not school ID year
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

                $this->autoInsertSubjectsForIrregularStudent($studentInfo->id, $request->curriculum2);

                // Auto-insert subjects based on student type - USE THE SELECTED CURRICULUM
                // if ($studentType == '1') { // Regular student
                //     $this->autoInsertSubjectsForRegularStudent($studentInfo->id, $request->year_level, $request->curriculum);
                // } elseif ($studentType == '2') { // Irregular student
                //     $this->autoInsertSubjectsForIrregularStudent($studentInfo->id, $request->curriculum);
                // } elseif ($studentType == '5') {
                //     $this->autoInsertSubjectsForIrregularStudent($studentInfo->id, $request->curriculum);
                // }

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

    private function completeStudentInfo2(Request $request)
    {
        try {
            // Validate the registration data first
            $validator = Validator::make($request->all(), [
                // 'year_level' => [
                //     'required'
                // ],
                'student_type' => [
                    'required'
                ],
            ], [
                // 'year_level.required' => 'Please select your year level.',
                'student_type.required' => 'Please select your student type.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ]);
            }

            DB::beginTransaction();

            $get_year_level = DB::table('enrollment_date')
                ->where('is_active', 1)
                ->first();
            
            $year_level = $get_year_level->year_level;

            try {
                $studentType = match($request->student_type) {
                    'Regular' => '1',
                    'Irregular' => '2',
                    // 'Transferee' => '0',
                    // 'Not Set' => '5',
                    default => $request->student_type
                };

                // Get the current authenticated user
                $user = Auth::guard('student')->user();
                
                // Find the user_info record for this user
                $userInfo = UserInfo::where('user_id', $user->id)->first();
                // Add debugging
                Log::info('User: ' . $user->id);
                Log::info('UserInfo: ' . ($userInfo ? $userInfo->id : 'Not found'));
                
                if (!$userInfo) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'User information not found.'
                    ]);
                }

                // Update using the user_info id as student_id - USE THE SELECTED CURRICULUM
                 $student = Student::where('student_id', $userInfo->id)->first();
                Log::info('Student record: ' . ($student ? 'Found' : 'Not found'));
                $save = Student::where('student_id', $userInfo->id)
                    ->update([
                        'year_level' => $year_level,
                        'status' => 'Not Enrolled',
                        'is_regular' => $studentType,
                        'enrolled' => '5'
                    ]);
                Log::error('Student complete_info2 update error: ');
                

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
                    'message' => 'Completed successfully'
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
            $enrollment_date = DB::table('enrollment_date')
                ->where('is_active', 1)
                ->first();

            // Determine current semester
            $currentSemester = $enrollment_date->semester;

            
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
                ->get(['id', 'curriculum_year']);

                Log::info('Curricula found: ' . $curricula->count());
        Log::info('Curricula data: ', $curricula->toArray());
                
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
            // $yearLevel = $student->year_level;

            //Get the year level from enrollment_date table
            $yearLevelRecord = DB::table('enrollment_date')
                ->where('is_active', 1)
                ->first();
            $yearLevel = $yearLevelRecord->year_level;

            $enrollment_date = DB::table('enrollment_date')
                ->where('is_active', 1)
                ->first();

            
            // Determine current semester
            $currentSemester = $enrollment_date->semester;
            
            // Get all subjects the student has taken with their grades
            $studentGrades = DB::table('enrolled_sub')
                ->where('student_id', $student->id)
                ->whereNotNull('grade')
                ->get(['subject_id', 'grade', 'subject_code'])
                ->keyBy('subject_id');
            
            // Define passing grades (1.0 to 3.0 are passing)
            $passingGrades = ['1.0', '1.1', '1.2', '1.3', '1.4', '1.5', '1.6', '1.7', '1.8', '1.9', '2.0', '2.1', '2.2', '2.3', '2.4', '2.5', '2.6', '2.7', '2.8', '2.9', '3.0'];
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

            if($student->enrolled == '1') {
                $subjects = $this->getAvailableSubjectsForFreshmen($student, $passedSubjects, $allTakenSubjects, $failedSubjects);
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

            $currentDate = now()->format('Y-m-d');

            $enrollmentPeriod2 = DB::table('enrollment_date')
                ->where('is_active', 1)
                ->whereDate('start', '<=', $currentDate)
                ->whereDate('end', '>=', $currentDate)
                ->first();
            
            // Create enrollment request
            $enrollmentRequest = new EnrollmentRequest();
            $enrollmentRequest->student_id = $student->id;
            $enrollmentRequest->status = 'Pending';
            $enrollmentRequest->request_date = now();
            $enrollmentRequest->$enrollmentPeriod2->year_level;
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

            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Get file paths before deleting records
            $documentFiles = DB::table('documents')
                ->where('student_id', $student->id)
                ->pluck('file_path')
                ->toArray();

            $paymentFiles = DB::table('payments')
                ->where('student_id', $student->id)
                ->pluck('file_path')
                ->toArray();

            DB::table('documents')
                ->where('student_id', $student->id)
                ->delete();

            DB::table('payments')
                ->where('student_id', $student->id)
                ->delete();

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Delete actual files from storage
            foreach ($documentFiles as $filePath) {
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }

            foreach ($paymentFiles as $filePath) {
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }


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

            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Delete records
            DB::table('enrollmentrequests')
                ->where('student_id', $student->id)
                ->delete();

            DB::table('enrollments')
                ->where('student_id', $student->id)
                ->delete();
                
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $enrollment_year = DB::table('enrollment_date')
                ->where('is_active', 1)
                ->first();

            // Create enrollment request
            $enrollmentRequest = new EnrollmentRequest();
            $enrollmentRequest->student_id = $student->id;
            $enrollmentRequest->status = 'Pending';
            $enrollmentRequest->request_date = now();
            $enrollmentRequest->year_level = $enrollment_year->year_level;
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
                    $enrollment->status = 'Pending';
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

            // Find an id for instructor
            $instructor = DB::table('instructor')
                ->whereNotNull('instructor_id')
                ->orderBy('date_created', 'asc')
                ->first();

            // Insert to notify the instructor
            DB::table('notifications_instructor')->insert([
                'user_id' => $instructor->instructor_id, // Use the actual user_id
                'title' => $user->user_information->lastname .' (Enrollment Request)',
                'message' => 'Enrollment Request - '.$student->year_level,
                'is_read' => 0, // Use integer 0, not string '0'
                'created_at' => now()
            ]);

            // NEW: Generate and save prospectus PDF
            $prospectusPath = $this->generateProspectusPDF($student->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Enrollment submitted successfully! Your enrollment and payment receipt are pending verification.',
                'prospectus_url' => asset('storage/' . $prospectusPath)
            ]);

            // return response()->json([
            //     'success' => true,
            //     'message' => 'Enrollment submitted successfully! Your enrollment and payment receipt are pending verification.'
            // ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Final enrollment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Enrollment failed: ' . $e->getMessage()
            ]);
        }
    }

    private function generateProspectusPDF($studentId)
    {
        try {
            // Get student information - convert to array
            $student = DB::table('students')
                ->join('user_info', 'students.student_id', '=', 'user_info.id')
                ->join('users', 'user_info.user_id', '=', 'users.id')
                ->select('students.*', 'user_info.firstname', 'user_info.lastname', 'user_info.middlename', 'users.email2')
                ->where('students.id', $studentId)
                ->first();

            if (!$student) {
                throw new \Exception('Student not found');
            }

            // Convert student object to array
            $student = (array) $student;

            // Get all enrolled subjects with grades
            $enrolledSubjects = DB::table('enrolled_sub')
                ->where('student_id', $studentId)
                ->orderBy('year_level')
                ->orderBy('semester')
                ->orderBy('subject_code')
                ->get();

            // Convert enrolled subjects to array format
            $enrolledSubjectsArray = [];
            foreach ($enrolledSubjects as $subject) {
                $enrolledSubjectsArray[] = (array) $subject;
            }

            // Get enrollment period info
            $enrollmentPeriod = DB::table('enrollment_date')
                ->where('is_active', 1)
                ->orderBy('id', 'desc')
                ->first();
            
            if ($enrollmentPeriod) {
                $enrollmentPeriod = (array) $enrollmentPeriod;
            }

            // Get prerequisites for all subjects
            $prerequisites = DB::table('subjectprerequisites')
                ->join('subjects as s1', 'subjectprerequisites.subject_id', '=', 's1.id')
                ->join('subjects as s2', 'subjectprerequisites.prerequisite_id', '=', 's2.id')
                ->select('subjectprerequisites.subject_id', 's2.code as prereq_code')
                ->get();

            // Group prerequisites by subject_id
            $prerequisitesBySubject = [];
            foreach ($prerequisites as $prereq) {
                $prereq = (array) $prereq;
                $subjectId = $prereq['subject_id'];
                if (!isset($prerequisitesBySubject[$subjectId])) {
                    $prerequisitesBySubject[$subjectId] = [];
                }
                $prerequisitesBySubject[$subjectId][] = $prereq['prereq_code'];
            }

            // Organize subjects by year level and semester
            $organizedSubjects = [];
            $yearLevelOrder = ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'];
            $semesterOrder = ['1st Sem', '2nd Sem', 'Summer'];
            
            foreach ($yearLevelOrder as $year) {
                foreach ($semesterOrder as $semester) {
                    $semesterSubjects = array_filter($enrolledSubjectsArray, function($subject) use ($year, $semester) {
                        return $subject['year_level'] == $year && $subject['semester'] == $semester;
                    });
                    
                    if (count($semesterSubjects) > 0) {
                        // Re-index the array and add prerequisites
                        $semesterSubjects = array_values($semesterSubjects);
                        
                        // Get subject IDs to find prerequisites
                        $subjectIds = array_column($semesterSubjects, 'subject_id');
                        
                        foreach ($semesterSubjects as &$subject) {
                            $subjectId = $subject['subject_id'];
                            if (isset($prerequisitesBySubject[$subjectId]) && !empty($prerequisitesBySubject[$subjectId])) {
                                $subject['prerequisites'] = implode(', ', $prerequisitesBySubject[$subjectId]);
                            } else {
                                $subject['prerequisites'] = 'None';
                            }
                        }
                        
                        $organizedSubjects[$year][$semester] = $semesterSubjects;
                    }
                }
            }

            // Calculate totals
            $totalUnits = array_sum(array_column($enrolledSubjectsArray, 'units'));
            $totalSubjects = count($enrolledSubjectsArray);
            $subjectsWithGrades = 0;
            foreach ($enrolledSubjectsArray as $subject) {
                if (!empty($subject['grade'])) {
                    $subjectsWithGrades++;
                }
            }

            // ✅ Format curriculum year to academic year range
            $curriculumYear = $student['curriculum'] ?? 'Not Set';
            $academicYearRange = 'Not Set';
            
            if ($curriculumYear && is_numeric($curriculumYear)) {
                $startYear = (int)$curriculumYear;
                $endYear = $startYear + 1;
                $academicYearRange = $startYear . '-' . $endYear;
            } else {
                $academicYearRange = $curriculumYear;
            }

            // ✅ Format the header academic year from enrollment period
            $enrollmentAcademicYear = $enrollmentPeriod['academic_year'] ?? '2025-2026';
            
            // If enrollment period has just a single year, format it to range
            if ($enrollmentAcademicYear && strpos($enrollmentAcademicYear, '-') === false && is_numeric($enrollmentAcademicYear)) {
                $startYear = (int)$enrollmentAcademicYear;
                $endYear = $startYear + 1;
                $enrollmentAcademicYear = $startYear . '-' . $endYear;
            }

            // Prepare data for PDF
            $data = [
                'student' => $student,
                'organizedSubjects' => $organizedSubjects,
                'curriculumYear' => $academicYearRange, // ✅ Now formatted as "2018-2019"
                'enrollmentPeriod' => $enrollmentPeriod,
                'enrollmentAcademicYear' => $enrollmentAcademicYear, // ✅ New formatted academic year for display
                'totalUnits' => $totalUnits,
                'totalSubjects' => $totalSubjects,
                'subjectsWithGrades' => $subjectsWithGrades,
                'dateGenerated' => now()->format('F d, Y h:i A'),
                'yearLevelOrder' => $yearLevelOrder,
                'semesterOrder' => $semesterOrder
            ];

            // Generate PDF
            $pdf = PDF::loadView('student.prospectus', $data);
            
            // Create directory if it doesn't exist
            $directory = storage_path('app/public/documents/prospectus/');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Save PDF to storage
            $fileName = 'prospectus_' . $studentId . '_' . time() . '.pdf';
            $filePath = 'documents/prospectus/' . $fileName;
            
            Storage::disk('public')->put($filePath, $pdf->output());

            // Save to documents table
            DB::table('documents')->insert([
                'student_id' => $studentId,
                'type' => 'Prospectus',
                'file_path' => $filePath,
                'upload_date' => now(),
                'status' => 'Approved'
            ]);

            return $filePath;

        } catch (\Exception $e) {
            Log::error('Prospectus generation error: ' . $e->getMessage());
            throw $e;
        }
    }

    // private function generateProspectusPDF($studentId)
    // {
    //     try {
    //         // Get student information
    //         $student = DB::table('students')
    //             ->join('user_info', 'students.student_id', '=', 'user_info.id')
    //             ->join('users', 'user_info.user_id', '=', 'users.id')
    //             ->select('students.*', 'user_info.firstname', 'user_info.lastname', 'user_info.middlename', 'users.email2')
    //             ->where('students.id', $studentId)
    //             ->first();

    //         if (!$student) {
    //             throw new \Exception('Student not found');
    //         }

    //         // Get all enrolled subjects with grades
    //         $enrolledSubjects = DB::table('enrolled_sub')
    //             ->where('student_id', $studentId)
    //             ->orderBy('year_level')
    //             ->orderBy('semester')
    //             ->orderBy('subject_code')
    //             ->get();

    //         // Get enrollment period info
    //         $enrollmentPeriod = DB::table('enrollment_date')
    //             ->where('is_active', 1)
    //             ->orderBy('id', 'desc')
    //             ->first();

    //         // Get curriculum info
    //         $curriculum = DB::table('curriculum')
    //             ->where('curriculum_year', $student->curriculum)
    //             ->first();

    //         // Organize subjects by year level and semester
    //         $organizedSubjects = [];
    //         $yearLevelOrder = ['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year'];
    //         $semesterOrder = ['1st Sem', '2nd Sem', 'Summer'];
            
    //         foreach ($yearLevelOrder as $year) {
    //             foreach ($semesterOrder as $semester) {
    //                 $semesterSubjects = $enrolledSubjects
    //                     ->where('year_level', $year)
    //                     ->where('semester', $semester)
    //                     ->values();
                    
    //                 if ($semesterSubjects->count() > 0) {
    //                     if (!isset($organizedSubjects[$year])) {
    //                         $organizedSubjects[$year] = [];
    //                     }
    //                     $organizedSubjects[$year][$semester] = $semesterSubjects->toArray();
    //                 }
    //             }
    //         }

    //         // Calculate totals
    //         $totalUnits = $enrolledSubjects->sum('units');
    //         $totalSubjects = $enrolledSubjects->count();
    //         $subjectsWithGrades = $enrolledSubjects->whereNotNull('grade')->count();

    //         // Prepare data for PDF
    //         $data = [
    //             'student' => $student,
    //             'organizedSubjects' => $organizedSubjects,
    //             'curriculumYear' => $student->curriculum,
    //             'curriculum' => $curriculum,
    //             'enrollmentPeriod' => $enrollmentPeriod,
    //             'totalUnits' => $totalUnits,
    //             'totalSubjects' => $totalSubjects,
    //             'subjectsWithGrades' => $subjectsWithGrades,
    //             'dateGenerated' => now()->format('F d, Y h:i A'),
    //             'yearLevelOrder' => $yearLevelOrder,
    //             'semesterOrder' => $semesterOrder
    //         ];

    //         // Generate PDF
    //         $pdf = PDF::loadView('student.prospectus', $data);
            
    //         // Create directory if it doesn't exist
    //         $directory = storage_path('app/public/documents/prospectus/');
    //         if (!file_exists($directory)) {
    //             mkdir($directory, 0755, true);
    //         }

    //         // Save PDF to storage
    //         $fileName = 'prospectus_' . $studentId . '_' . time() . '.pdf';
    //         $filePath = 'documents/prospectus/' . $fileName;
            
    //         Storage::disk('public')->put($filePath, $pdf->output());

    //         // Save to documents table
    //         DB::table('documents')->insert([
    //             'student_id' => $studentId,
    //             'type' => 'Prospectus',
    //             'file_path' => $filePath,
    //             'upload_date' => now(),
    //             'status' => 'Approved'
    //         ]);

    //         return $filePath;

    //     } catch (\Exception $e) {
    //         Log::error('Prospectus generation error: ' . $e->getMessage());
    //         throw $e;
    //     }
    // }

    private function hasExistingEnrollmentRequest($studentId)
    {
        try {
            $existingRequest = EnrollmentRequest::where('student_id', $studentId)
                ->whereIn('status', ['Pending', 'Approved'])
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

            $currentEnrollment = DB::table('enrollment_date')
                ->where('is_active', 1)
                ->first();

            $currentSemester = $currentEnrollment->semester;

            $allSubjects = Subject::where('is_active', 1)
                ->whereIn('year_level', ['1st Year', '2nd Year', '3rd Year', '4th Year', ''])
                ->where('curriculum_id', $curriculum_id)
                ->where('semester', $currentSemester)
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


    // For freshmen:
    private function getAvailableSubjectsForFreshmen($student, $passedSubjects, $allTakenSubjects, $failedSubjects)
    {
        Log::info('Getting available subjects for freshmen student', [
            'student_id' => $student->id,
            'passed_subjects_count' => count($passedSubjects),
            'failed_subjects_count' => count($failedSubjects),
            'all_taken_subjects_count' => count($allTakenSubjects)
        ]);

        try {
            $curriculum_year = $student->curriculum;
            $curriculum = DB::table('curriculum')
                ->where('curriculum_year', $curriculum_year)
                ->where('is_active', 1)
                ->first();

            if (!$curriculum) {
                Log::error('No active curriculum found for student', ['student_id' => $student->id]);
                return collect();
            }

            $curriculum_id = $curriculum->id;

            // Get only 1st Year 1st Semester subjects
            $allSubjects = Subject::where('is_active', 1)
                ->where('year_level', '1st Year')  // Only 1st Year
                ->where('curriculum_id', $curriculum_id)
                ->where('semester', '1st Sem')  // Only 1st Semester
                ->whereNotIn('id', $passedSubjects)
                ->with(['schedules', 'prerequisites'])
                ->get();

            Log::info('Total 1st Year 1st Semester subjects found for freshmen', [
                'count' => $allSubjects->count(),
                'subject_codes' => $allSubjects->pluck('code')->toArray()
            ]);

            // Filter subjects based on prerequisites
            $availableSubjects = $allSubjects->filter(function($subject) use ($passedSubjects, $failedSubjects) {
                // If this is a failed subject that needs retaking, always include it
                if (in_array($subject->id, $failedSubjects)) {
                    return true;
                }
                
                // If subject has no prerequisites, it's available
                if ($subject->prerequisites->isEmpty()) {
                    return true;
                }
                
                // Check if all prerequisites are completed WITH PASSING GRADES
                $prerequisiteIds = $subject->prerequisites->pluck('id')->toArray();
                $completedPrerequisites = array_intersect($prerequisiteIds, $passedSubjects);
                
                return count($prerequisiteIds) === count($completedPrerequisites);
            });

            return $availableSubjects;

        } catch (\Exception $e) {
            Log::error('Error in getAvailableSubjectsForFreshmen: ' . $e->getMessage());
            return collect();
        }
    }

    private function calculateTotalUnitsForSemester($yearLevel, $semester)
    {

        if($yearLevel == '') {
            // Calculate total units for the specific semester and year level
            $totalUnits = Subject::where('year_level', '')
                ->where('semester', $semester)
                ->where('is_active', 1)
                ->where('curriculum_id', function($query) {
                    $user = Auth::guard('student')->user();
                    $student = $user->user_information->student;
                    $curriculum_year = $student->curriculum;
                    $curriculum = DB::table('curriculum')
                        ->where('curriculum_year', $curriculum_year)
                        ->where('is_active', 1)
                        ->first();
                    $query->select('id')
                        ->from('curriculum')
                        ->where('curriculum_year', $curriculum_year)
                        ->where('is_active', 1)
                        ->limit(1);
                })
                ->sum('units');
            
            return $totalUnits;
        }

        // Calculate total units for the specific semester and year level
        $totalUnits = Subject::where('year_level', $yearLevel)
            ->where('semester', $semester)
            ->where('is_active', 1)
            ->where('curriculum_id', function($query) {
                $user = Auth::guard('student')->user();
                $student = $user->user_information->student;
                $curriculum_year = $student->curriculum;
                $curriculum = DB::table('curriculum')
                    ->where('curriculum_year', $curriculum_year)
                    ->where('is_active', 1)
                    ->first();
                $query->select('id')
                    ->from('curriculum')
                    ->where('curriculum_year', $curriculum_year)
                    ->where('is_active', 1)
                    ->limit(1);
            })
            ->sum('units');
        
        return $totalUnits;
    }

    public function student_status (Request $request)
    {
        $action = $request->input('action');

        switch ($action) {
            case 'status_now':
                return $this->student_status_update ();
                
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action'
                ]);
        }
    }

    private function student_status_update () 
    {
        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;
        try {
            $students_status = DB::table('enrollmentrequests')
                ->where('student_id', $student->id)
                ->orderBy('request_date', 'desc')
                ->get(); // This returns a Collection
            
            // ERROR: You can't do $students_status->status on a Collection
            // You need to get the first item or pluck the status
            
            // Fix:
            $latest_request = $students_status->first();
            $status = $latest_request->status;
            
            return response()->json([
                'success' => true,
                'students_status' => $status
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching student_status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get enrollment status'
            ]);
        }
    }

    public function getEnrolledSub() {
        try {
            $user = Auth::guard('student')->user();
            $student = $user->user_information->student;
            
            // Get count of pending enrollment requests
            $count = DB::table('enrollments')
                ->where('status', 'Enrolled')
                ->where('student_id', $student->id)
                ->count();

            return response()->json([
                'count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching enrollment requests: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch enrollment requests'], 500);
        }
    }

    public function countDocuments() {
        try {
            $user = Auth::guard('student')->user();
            $student = $user->user_information->student;
            
            // Get count of pending enrollment requests
            $count = DB::table('student_files')
                ->where('student_id', $student->id)
                ->count();

            return response()->json([
                'count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching enrollment requests: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch enrollment requests'], 500);
        }
    }

    public function getNotifications(Request $request)
    {
        if (!Auth::guard('student')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;

        // Get notifications for this student
        $query = DB::table('notifications')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Apply filters
        $filter = $request->input('filter', 'all');
        if ($filter === 'unread') {
            $query->where('is_read', 0);
        } elseif ($filter === 'enrollment') {
            $query->where('title', 'like', '%Enrollment%');
        } elseif ($filter === 'grades') {
            $query->where('title', 'like', '%Grade%');
        } elseif ($filter === 'documents') {
            $query->where('title', 'like', '%Document%');
        }

        // Get total counts
        $total = $query->count();
        $unread = DB::table('notifications')
            ->where('user_id', $user->id)
            ->where('is_read', 0)
            ->count();

        // Apply pagination
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $notifications = $query->offset($offset)->limit($limit)->get();

        return response()->json([
            'notifications' => $notifications,
            'total' => $total,
            'unread' => $unread
        ]);
    }

    public function markNotificationAsRead(Request $request)
    {
        if (!Auth::guard('student')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notificationId = $request->input('id');
        
        // Mark notification as read
        DB::table('notifications')
            ->where('id', $notificationId)
            ->update(['is_read' => 1]);

        // Get updated counts
        $user = Auth::guard('student')->user();
        $total = DB::table('notifications')
            ->where('user_id', $user->id)
            ->count();
        $unread = DB::table('notifications')
            ->where('user_id', $user->id)
            ->where('is_read', 0)
            ->count();

        return response()->json([
            'success' => true,
            'total' => $total,
            'unread' => $unread
        ]);
    }

    public function markAllNotificationsAsRead(Request $request)
    {
        if (!Auth::guard('student')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::guard('student')->user();
        
        // Mark all notifications as read
        DB::table('notifications')
            ->where('user_id', $user->id)
            ->update(['is_read' => 1]);

        return response()->json([
            'success' => true,
            'total' => DB::table('notifications')->where('user_id', $user->id)->count(),
            'unread' => 0
        ]);
    }

    public function deleteNotification(Request $request)
    {
        if (!Auth::guard('student')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notificationId = $request->input('id');
        
        // Delete notification
        DB::table('notifications')
            ->where('id', $notificationId)
            ->delete();

        // Get updated counts
        $user = Auth::guard('student')->user();
        $total = DB::table('notifications')
            ->where('user_id', $user->id)
            ->count();
        $unread = DB::table('notifications')
            ->where('user_id', $user->id)
            ->where('is_read', 0)
            ->count();

        return response()->json([
            'success' => true,
            'total' => $total,
            'unread' => $unread
        ]);
    }

    public function clearAllNotifications(Request $request)
    {
        if (!Auth::guard('student')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::guard('student')->user();
        
        // Delete all notifications
        DB::table('notifications')
            ->where('user_id', $user->id)
            ->delete();

        return response()->json([
            'success' => true,
            'total' => 0,
            'unread' => 0
        ]);
    }

    public function checkNewNotifications(Request $request)
    {
        if (!Auth::guard('student')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::guard('student')->user();
        
        // Check for notifications created in the last 5 minutes
        $newCount = DB::table('notifications')
            ->where('user_id', $user->id)
            ->where('created_at', '>', now()->subMinutes(5))
            ->count();

        return response()->json([
            'hasNew' => $newCount > 0,
            'count' => $newCount
        ]);
    }

    public function getAcademicFiles()
    {
        if (!Auth::guard('student')->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;

        // Get academic files from student_files table
        $academicFiles = DB::table('student_files')
            ->where('student_id', $student->id)
            ->orderBy('year_level')
            ->orderBy('upload_date', 'desc')
            ->get();

        // Format the files data
        $files = $academicFiles->map(function ($file) {
            return [
                'id' => $file->id,
                'type' => $file->type,
                'year_level' => $file->year_level,
                'file_path' => asset($file->file_path),
                'upload_date' => $file->upload_date,
                'title' => $file->type . ' - ' . $file->year_level,
                'description' => ucfirst($file->type) . ' document for ' . $file->year_level
            ];
        });

        return response()->json([
            'success' => true,
            'files' => $files
        ]);
    }

    public function getPaymentFiles()
    {
        if (!Auth::guard('student')->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;

        // Get payment files from organizationfees and payments tables
        $organizationPayments = DB::table('organizationfees')
            ->where('student_id', $student->id)
            ->orderBy('uploaded_date', 'desc')
            ->get();

        $enrollmentPayments = DB::table('payments')
            ->where('student_id', $student->id)
            ->orderBy('upload_date', 'desc')
            ->get();

        // Combine and format payments
        $payments = collect();
        
        // Add organization fees
        foreach ($organizationPayments as $payment) {
            $payments->push([
                'id' => $payment->id,
                'type' => 'Organization Fee',
                'amount' => $payment->amount,
                'status' => $payment->status,
                'receipt_url' => $payment->receipt_url ? asset($payment->receipt_url) : null,
                'payment_date' => $payment->uploaded_date,
                'red_flag' => $payment->red_flag_reason
            ]);
        }

        // Add enrollment payments
        // foreach ($enrollmentPayments as $payment) {
        //     $payments->push([
        //         'id' => $payment->id,
        //         'type' => 'Enrollment Payment',
        //         'amount' => 0, // You might need to adjust this
        //         'status' => $payment->status,
        //         'receipt_url' => $payment->file_path ? asset($payment->file_path) : null,
        //         'payment_date' => $payment->upload_date,
        //         'red_flag' => null
        //     ]);
        // }

        return response()->json([
            'success' => true,
            'payments' => $payments
        ]);
    }


    public function getFileStatistics()
    {
        if (!Auth::guard('student')->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;

        // Count academic files
        $academicFilesCount = DB::table('student_files')
            ->where('student_id', $student->id)
            ->count();

        // Count payment files
        $organizationPaymentsCount = DB::table('organizationfees')
            ->where('student_id', $student->id)
            ->count();

        $paymentFilesCount = $organizationPaymentsCount;
        
        // Count required documents
        $requiredDocumentsCount = DB::table('important_documents')
            ->where('student_id', $student->id)
            ->count();
        
        // Calculate total files
        $totalFiles = $academicFilesCount + $paymentFilesCount + $requiredDocumentsCount;
        
        // You might want to update the total size calculation to include required documents
        $totalSize = $totalFiles * 0.5; // Estimate 0.5 MB per file

        return response()->json([
            'success' => true,
            'totalFiles' => $totalFiles,
            'academicFiles' => $academicFilesCount,
            'paymentFiles' => $paymentFilesCount,
            'requiredDocuments' => $requiredDocumentsCount, // New field
            'totalSize' => number_format($totalSize, 1)
        ]);
    }

    public function getRequiredDocuments()
    {
        try {
            Log::info('getRequiredDocuments called');
            
            if (!Auth::guard('student')->check()) {
                Log::warning('Student not authenticated');
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $user = Auth::guard('student')->user();
            Log::info('Student user:', ['user_id' => $user->id]);
            
            if (!$user->user_information || !$user->user_information->student) {
                Log::warning('Student information not found', ['user_id' => $user->id]);
                return response()->json(['success' => false, 'message' => 'Student information not found']);
            }
            
            $student = $user->user_information->student;
            Log::info('Student ID:', ['student_id' => $student->id]);

            // Check if table exists
            if (!Schema::hasTable('important_documents')) {
                Log::error('important_documents table does not exist');
                return response()->json(['success' => false, 'message' => 'Database table not found']);
            }

            // Get required documents from important_documents table
            $requiredDocuments = DB::table('important_documents')
                ->where('student_id', $student->id)
                ->orderBy('year_level')
                ->orderBy('upload_date', 'desc')
                ->get();

            Log::info('Documents found:', ['count' => $requiredDocuments->count()]);

            // Format the documents data
            $documents = $requiredDocuments->map(function ($doc) {
                // Map document type to human-readable name
                $typeNames = [
                    'FORM138A' => 'Form 138A (High School Card)',
                    'FORM138B' => 'Form 138B (Photocopy)',
                    'GOOD_MORAL' => 'Good Moral Certificate',
                    'PSA_NSO' => 'PSA/NSO Birth Certificate',
                    'ID_PICTURE' => '2x2 ID Picture',
                    'BIRTH_CERTIFICATE' => 'Birth Certificate'
                ];

                $filePath = $doc->file_path;
                
                // Ensure the file path is valid
                if ($filePath && !filter_var($filePath, FILTER_VALIDATE_URL)) {
                    // Make it a full URL if it's a relative path
                    $filePath = asset($filePath);
                }

                return [
                    'id' => $doc->id,
                    'type' => $doc->type,
                    'type_name' => $typeNames[$doc->type] ?? $doc->type,
                    'year_level' => $doc->year_level,
                    'file_path' => $filePath,
                    'upload_date' => $doc->upload_date,
                    'file_size' => $this->getFileSize($doc->file_path)
                ];
            });

            return response()->json([
                'success' => true,
                'documents' => $documents
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getRequiredDocuments: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper function to get file size (optional)
    private function getFileSize($filePath)
    {
        if (empty($filePath)) {
            return 'N/A';
        }
        
        Log::info('Getting file size for:', ['path' => $filePath]);
        
        // Remove the asset() part if it's already been added
        $relativePath = str_replace(asset(''), '', $filePath);
        $fullPath = public_path($relativePath);
        
        Log::info('Full path:', ['full' => $fullPath]);
        
        if (file_exists($fullPath) && is_file($fullPath)) {
            $size = filesize($fullPath);
            return $this->formatBytes($size);
        }
        
        Log::warning('File not found:', ['path' => $fullPath]);
        return 'N/A';
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function uploadAvatar(Request $request)
    {
        try {
            // Check if user is authenticated
            $user = Auth::guard('student')->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized. Please login again.'
                ], 401);
            }

            // Validate the uploaded file
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
            ]);

            // Handle the file upload
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                
                // Generate unique filename
                $filename = $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                
                // Define the storage path
                $storagePath = 'profile/student/' . $filename;
                
                // Delete old profile picture if not default
                if ($user->profile && $user->profile !== 'default.png' && $user->profile !== 'default.jpg') {
                    // Check if old image exists in storage and delete it
                    $oldImagePath = 'profile/student/' . $user->profile;
                    if (Storage::disk('public')->exists($oldImagePath)) {
                        Storage::disk('public')->delete($oldImagePath);
                    }
                    
                    // Also delete from public_path if it exists (for old files)
                    $oldPublicPath = public_path('profile/' . $user->profile);
                    if (file_exists($oldPublicPath)) {
                        @unlink($oldPublicPath);
                    }
                }
                
                // Store the file in storage/app/public/profile/student directory
                $file->storeAs('profile/student', $filename, 'public');
                
                // Update user's profile in database - store the filename only
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'profile' => $filename
                    ]);
                
                // Generate the URL using Storage facade
                $profile_url = Storage::url('profile/student/' . $filename) . '?v=' . time();
                
                return response()->json([
                    'success' => true,
                    'profile_url' => $profile_url,
                    'message' => 'Profile picture updated successfully!'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'error' => 'No file uploaded.'
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('Profile upload error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while uploading. Please try again.'
            ], 500);
        }
    }

    public function getAverageGrade2(Request $request)
    {
        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;
        
        // Get all enrolled subjects with grades
        $subjectsWithGrades = DB::table('enrolled_sub')
            ->where('student_id', $student->id)
            ->whereNotNull('grade')
            ->get();
        
        $totalGradePoints = 0;
        $totalUnits = 0;
        $numericGradeCount = 0;
        
        foreach ($subjectsWithGrades as $subject) {
            // Check if grade is numeric (1.0, 2.0, 3.0, 4.0, 5.0)
            if (is_numeric($subject->grade)) {
                $gradeValue = (float)$subject->grade;
                $units = $subject->units;
                
                $totalGradePoints += ($gradeValue * $units);
                $totalUnits += $units;
                $numericGradeCount++;
            }
        }
        
        $averageGrade = ($totalUnits > 0) ? $totalGradePoints / $totalUnits : 0;
        
        return response()->json([
            'success' => true,
            'average_grade' => number_format($averageGrade, 2),
            'numeric_grade_count' => $numericGradeCount,
            'total_units' => $totalUnits
        ]);
    }

    public function getEnrolledSub2(Request $request)
    {
        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;
        
        // Count courses with grades (not NULL)
        $coursesCount = DB::table('enrolled_sub')
            ->where('student_id', $student->id)
            ->whereNotNull('grade')
            ->count();
        
        // Calculate total units for subjects with grades
        $totalUnits = DB::table('enrolled_sub')
            ->where('student_id', $student->id)
            ->whereNotNull('grade')
            ->sum('units');
        
        // Get student type
        $studentType = '';
        switch($student->is_regular) {
            case 1: $studentType = 'Regular'; break;
            case 2: $studentType = 'Irregular'; break;
            case 3: $studentType = 'Transferee'; break;
            default: $studentType = 'Unknown';
        }
        
        return response()->json([
            'success' => true,
            'courses_count' => $coursesCount,
            'total_units' => $totalUnits,
            'year_level' => $student->year_level,
            'student_type' => $studentType
        ]);
    }

    public function getProfileData()
    {
        if (!Auth::guard('student')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;
        $userInfo = $user->user_information;

        // Format curriculum: add 1 year (e.g., 2018 -> 2018-2019)
        $curriculumYear = $student->curriculum;
        $formattedCurriculum = $curriculumYear . '-' . ((int)$curriculumYear + 1);

        return response()->json([
            'success' => true,
            'profile' => [
                'firstname' => $userInfo->firstname ?? '',
                'lastname' => $userInfo->lastname ?? '',
                'middlename' => $userInfo->middlename ?? '',
                'email' => $user->email2 ?? '',
                'phone' => $userInfo->phone_number ?? '',
                'address' => $userInfo->address ?? '',
                'program' => 'BS in Information Technology',
                'year_level' => $student->year_level ?? '',
                'curriculum' => $formattedCurriculum,
                'curriculum_year' => $student->curriculum ?? '',
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        if (!Auth::guard('student')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'middlename' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        try {
            $user = Auth::guard('student')->user();
            $userInfo = $user->user_information;

            // Update user_info table
            DB::table('user_info')
                ->where('user_id', $user->id)
                ->update([
                    'firstname' => $validated['firstname'],
                    'lastname' => $validated['lastname'],
                    'middlename' => $validated['middlename'] ?? '',
                    'phone_number' => $validated['phone'] ?? '',
                    'address' => $validated['address'] ?? '',
                ]);


            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    private function completeStudentInformStarter(Request $request)
    {
        
        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;

        $save = DB::table('curriculum')
            ->orderBy('curriculum_year', 'desc')
            ->first();

        $update = DB::table('students')
            ->where('id', $student->id)
            ->update([
                'curriculum' => $save->curriculum_year,
                'is_regular' => 6, //Set to Regular;
                'year_level' => '1st Year',
                'id_no' => $request->school_id3
            ]);
            

        $this->autoInsertSubjectsForIrregularStudent($student->id, $save->curriculum_year);

        DB::commit();

                
        return response()->json([
            'success' => true,
            'message' => 'Student information completed successfully'
        ]);


    }


    public function uploadStudentDocuments(Request $request)
    {
        // Validate the request - making all required fields nullable except form138a
        $validator = Validator::make($request->all(), [
            'form138a' => 'required|file|mimes:pdf,jpg,jpeg,png,docx,docs,msword|max:5120', // 5MB max
            'good_moral' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx,docs,msword|max:5120',
            'psa_nso' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx,docs,msword|max:5120',
            'id_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // 2MB max for images
            'marriage_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx,docs,msword|max:5120',
        ], [
            'form138a.required' => 'Form 138A is required',
            'id_picture.image' => 'ID Picture must be an image file',
            '*.max' => 'File size must not exceed :max kilobytes',
            '*.mimes' => 'File must be one of these types: :values',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please check the uploaded files',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Get authenticated student
        $user = Auth::guard('student')->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please log in again.'
            ], 401);
        }
        
        // Check if student exists
        if (!$user->user_information || !$user->user_information->student) {
            return response()->json([
                'success' => false,
                'message' => 'Student record not found.'
            ], 404);
        }
        
        $student = $user->user_information->student;
        $yearLevel = $student->year_level ?? 1;
        
        // Start transaction for database operations
        DB::beginTransaction();
        
        try {
            // Define document types and their file inputs
            $documents = [
                [
                    'field' => 'form138a',
                    'type' => 'FORM138A',
                    'required' => true
                ],
                [
                    'field' => 'good_moral',
                    'type' => 'GOOD_MORAL',
                    'required' => false
                ],
                [
                    'field' => 'psa_nso',
                    'type' => 'PSA_NSO',
                    'required' => false
                ],
                [
                    'field' => 'id_picture',
                    'type' => 'ID_PICTURE',
                    'required' => false
                ],
                [
                    'field' => 'marriage_certificate',
                    'type' => 'MARRIAGE_CERTIFICATE',
                    'required' => false
                ],
            ];
            
            $uploadedDocuments = [];
            $uploadedCount = 0;
            $now = now();
            $timestamp = $now->timestamp;
            
            // Variable to track if ID picture was uploaded
            $idPictureUploaded = false;
            $idPictureFilePath = null;
            
            foreach ($documents as $document) {
                $field = $document['field'];
                
                // Skip if file not provided
                if (!$request->hasFile($field)) {
                    continue;
                }
                
                $file = $request->file($field);
                
                // Generate unique filename with random component to prevent collisions
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                
                $fileName = "{$field}_{$student->id}_{$timestamp}_{$random}.{$extension}";
                
                // Store file
                $filePath = $file->storeAs('documents/requirements', $fileName, 'public');
                
                if (!$filePath) {
                    throw new \Exception("Failed to store {$document['type']} file");
                }
                
                // If this is the ID picture, we also need to update the user's profile
                if ($field === 'id_picture') {
                    $idPictureUploaded = true;
                    $idPictureFilePath = $filePath;
                }
                
                // Add to array for batch insert
                $uploadedDocuments[] = [
                    'student_id' => $student->id,
                    'year_level' => $yearLevel,
                    'type' => $document['type'],
                    'file_path' => $filePath,
                    'upload_date' => $now,
                ];
                
                $uploadedCount++;
            }
            
            // Check if at least Form 138A was uploaded
            if ($uploadedCount === 0) {
                throw new \Exception('No documents were uploaded');
            }
            
            // Batch insert for better performance
            DB::table('important_documents')->insert($uploadedDocuments);
            
            // If ID picture was uploaded, update the user's profile in users table
            if ($idPictureUploaded && $idPictureFilePath) {
                // Update the profile column in users table
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['profile' => $idPictureFilePath]);
            }
            
            // Check if all required documents are now uploaded
            $requiredDocumentTypes = ['FORM138A', 'GOOD_MORAL', 'PSA_NSO', 'ID_PICTURE'];
            
            $existingDocuments = DB::table('important_documents')
                ->where('student_id', $student->id)
                ->where('year_level', $yearLevel)
                ->whereIn('type', $requiredDocumentTypes)
                ->pluck('type')
                ->toArray();
            
            // Check if all required types are present (unique)
            $hasAllRequiredDocuments = count(array_intersect($requiredDocumentTypes, array_unique($existingDocuments))) === count($requiredDocumentTypes);
            
            // Update student status if all required documents are uploaded
            if ($hasAllRequiredDocuments) {
                DB::table('students')
                    ->where('id', $student->id)
                    ->update([
                        'is_regular' => 1,
                        'enrolled' => 1,
                    ]);
            }
            
            // Commit transaction
            DB::commit();
            
            // Log successful upload
            Log::info('Documents uploaded successfully', [
                'student_id' => $student->id,
                'documents_count' => count($uploadedDocuments),
                'year_level' => $yearLevel,
                'has_all_required' => $hasAllRequiredDocuments,
                'profile_updated' => $idPictureUploaded
            ]);
            
            $message = $hasAllRequiredDocuments 
                ? 'All required documents submitted successfully! You are now marked as a regular student.'
                : 'Documents uploaded successfully! You can upload remaining documents later.';
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'documents_uploaded' => count($uploadedDocuments),
                    'student_id' => $student->id,
                    'is_regular' => $hasAllRequiredDocuments ? 1 : 0,
                    'enrolled' => $hasAllRequiredDocuments ? 1 : 0,
                    'profile_updated' => $idPictureUploaded
                ]
            ]);
            
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            // Log the error with context
            Log::error('Document upload failed: ' . $e->getMessage(), [
                'student_id' => $student->id ?? 'unknown',
                'year_level' => $yearLevel ?? 'unknown',
                'error_trace' => $e->getTraceAsString(),
                'request_files' => array_keys($request->allFiles())
            ]);
            
            $errorMessage = env('APP_ENV') === 'production' 
                ? 'Failed to upload documents. Please try again.'
                : $e->getMessage();
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error' => env('APP_DEBUG') ? [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ] : null
            ], 500);
        }
    }


    public function getStudentDocuments()
    {
        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;
        
        $documents = DB::table('important_documents')
            ->where('student_id', $student->id)
            ->orderBy('upload_date', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'documents' => $documents,
            'count' => $documents->count()
        ]);
    }


    public function deleteDocument($documentId)
    {
        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;
        
        // Find the document
        $document = DB::table('important_documents')
            ->where('id', $documentId)
            ->where('student_id', $student->id)
            ->first();
        
        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        }
        
        try {
            // Delete file from storage
            Storage::disk('public')->delete($document->file_path);
            
            // Delete record from database
            DB::table('important_documents')->where('id', $documentId)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Document deletion failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document'
            ], 500);
        }
    }


    public function downloadDocument($documentId)
    {
        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;
        
        $document = DB::table('important_documents')
            ->where('id', $documentId)
            ->where('student_id', $student->id)
            ->first();
        
        if (!$document) {
            abort(404, 'Document not found');
        }
        
        $filePath = storage_path('app/public/' . $document->file_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }
        
        return response()->download($filePath, $this->getDocumentFileName($document->type));
    }


    private function getDocumentFileName($type)
    {
        $names = [
            'FORM138A' => 'Form-138A-High-School-Report-Card.pdf',
            'GOOD_MORAL' => 'Good-Moral-Certificate.pdf',
            'PSA_NSO' => 'PSA-NSO-Birth-Certificate.pdf',
            'ID_PICTURE' => '2x2-ID-Picture.jpg',
            'MARRIAGE_CERTIFICATE' => 'PSA-Marriage-Certificate.pdf',
            'FHE' => 'FHE-Certificate.pdf'
        ];
        
        return $names[$type] ?? 'document.pdf';
    }

    //
    public function uploadRequiredDocument(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:FORM138A,GOOD_MORAL,PSA_NSO,ID_PICTURE,MARRIAGE_CERTIFICATE,HONOR_DISMISSAL,TOR', // Added TOR
            'document_file' => 'required|file|mimes:jpeg,jpg,png,pdf|max:2048',
            'year_level' => 'required|in:1st Year,2nd Year,3rd Year,4th Year,5th Year'
        ]);

        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;

        // Check if the student already has a document of the same type for the same year level
        $existingDocument = DB::table('important_documents')
            ->where('student_id', $student->id)
            ->where('year_level', $request->year_level)
            ->where('type', $request->document_type)
            ->first();

        // Handle file upload
        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            // Generate a unique file name
            $fileName = strtolower($request->document_type) . '_' . $student->id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Store the file in the public/documents/requirements directory
            $filePath = $file->storeAs('documents/requirements', $fileName, 'public');

            // If there's an existing document, delete the old file and record
            // if ($existingDocument) {
            //     // Delete the old file from storage
            //     Storage::disk('public')->delete($existingDocument->file_path);
            //     // Delete the old record
            //     DB::table('important_documents')->where('id', $existingDocument->id)->delete();
            // }

            // Insert the new document record
            DB::table('important_documents')->insert([
                'student_id' => $student->id,
                'year_level' => $request->year_level,
                'type' => $request->document_type,
                'file_path' => $filePath,
                'upload_date' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Document uploaded successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'File upload failed.'], 400);
    }

    public function showInputGrades()
    {
        if (!Auth::guard('student')->check()) {
            return response()->json(['error' => 'Please login first.'], 401);
        }

        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;
        
        // Get student info for the modal
        $userInfo = $user->user_information;

        // Return JSON response with student info
        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'id_no' => $student->id_no,
                'year_level' => $student->year_level,
            ],
            'userInfo' => [
                'firstname' => $userInfo->firstname,
                'lastname' => $userInfo->lastname,
                'full_name' => $userInfo->firstname . ' ' . $userInfo->lastname,
            ],
            'initial_subjects' => DB::table('enrolled_sub')
                ->where('student_id', $student->id)
                ->orderBy('year_level')
                ->orderBy('semester')
                ->get()->toArray()
        ]);
    }

    // For initial page load (returns student info)
    public function getInitialGradeInputData()
    {
        if (!Auth::guard('student')->check()) {
            return response()->json(['error' => 'Please login first.'], 401);
        }

        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;
        $userInfo = $user->user_information;

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'id_no' => $student->id_no,
                'year_level' => $student->year_level,
                'student_id' => $student->id_no, // For display
            ],
            'userInfo' => [
                'firstname' => $userInfo->firstname,
                'lastname' => $userInfo->lastname,
                'full_name' => $userInfo->firstname . ' ' . $userInfo->lastname,
            ]
        ]);
    }

    public function getEnrolledSubjectsForGrades(Request $request)
    {
        if (!Auth::guard('student')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;

        $yearLevel = $request->get('year_level');
        $subjectSearch = $request->get('subject_search');
        $gradeStatus = $request->get('grade_status', 'all');

        // First, get the enrolled subjects
        $query = DB::table('enrolled_sub')
            ->select('enrolled_sub.*')
            ->where('enrolled_sub.student_id', $student->id);

        // Apply filters
        if ($yearLevel && $yearLevel !== 'all') {
            $query->where('year_level', $yearLevel);
        }

        if ($subjectSearch) {
            $query->where(function($q) use ($subjectSearch) {
                $q->where('subject_code', 'like', "%{$subjectSearch}%")
                ->orWhere('subject_name', 'like', "%{$subjectSearch}%");
            });
        }

        if ($gradeStatus === 'ungraded') {
            $query->whereNull('grade')->orWhere('grade', '');
        } elseif ($gradeStatus === 'graded') {
            $query->whereNotNull('grade')->where('grade', '!=', '');
        }

        $subjects = $query->orderBy('year_level')
            ->orderBy('semester')
            ->orderBy('subject_code')
            ->get();

        // Now get prerequisites for each subject
        $subjectIds = $subjects->pluck('subject_id')->toArray();
        
        if (!empty($subjectIds)) {
            // Get prerequisites for all subjects
            $prerequisites = DB::table('subjectprerequisites as sp')
                ->select(
                    'sp.subject_id',
                    DB::raw('GROUP_CONCAT(DISTINCT s.code ORDER BY s.code SEPARATOR ", ") as prerequisite_codes')
                )
                ->leftJoin('subjects as s', 'sp.prerequisite_id', '=', 's.id')
                ->whereIn('sp.subject_id', $subjectIds)
                ->groupBy('sp.subject_id')
                ->get()
                ->keyBy('subject_id');
            
            // Add prerequisites to each subject
            $subjects->transform(function ($subject) use ($prerequisites) {
                $subject->prerequisites = $prerequisites[$subject->subject_id]->prerequisite_codes ?? 'None';
                return $subject;
            });
        } else {
            // If no subjects, set default empty prerequisites
            $subjects->transform(function ($subject) {
                $subject->prerequisites = 'None';
                return $subject;
            });
        }

        return response()->json($subjects->toArray());
    }

    public function getSubjectDetails2($subjectId)
    {
        if (!Auth::guard('student')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;

        // Get subject details from enrolled_sub table
        $subject = DB::table('enrolled_sub')
            ->where('id', $subjectId)
            ->where('student_id', $student->id)
            ->first();

        if (!$subject) {
            return response()->json(['error' => 'Subject not found'], 404);
        }

        // Get student info
        $userInfo = $user->user_information;

        return response()->json([
            'subject' => $subject,
            'student' => [
                'id' => $student->student_id,
                'full_name' => $userInfo->firstname . ' ' . $userInfo->lastname,
                'student_id' => $student->id_no,
                'year_level' => $student->year_level
            ]
        ]);
    }

    public function updateGrade(Request $request)
    {
        if (!Auth::guard('student')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;

        $validated = $request->validate([
            'subject_id' => 'required|integer',
            'grade' => 'nullable|string'
        ]);

        // Get the enrolled subject record to get the actual subject_id
        $enrolledSubject = DB::table('enrolled_sub')
            ->where('id', $validated['subject_id'])
            ->where('student_id', $student->id)
            ->first();

        if (!$enrolledSubject) {
            return response()->json(['error' => 'Subject not found or unauthorized'], 404);
        }

        // Check prerequisites for this subject
        $prerequisites = DB::table('subjectprerequisites')
            ->where('subject_id', $enrolledSubject->subject_id)
            ->pluck('prerequisite_id');

        $failedGrades = ['4.0', '5.0', 'INC', 'DRP'];
        
        foreach ($prerequisites as $prerequisiteId) {
            $prerequisiteRecord = DB::table('enrolled_sub')
                ->where('student_id', $student->id)
                ->where('subject_id', $prerequisiteId)
                ->first();

            // If prerequisite doesn't exist in student's enrolled subjects
            if (!$prerequisiteRecord) {
                // return response()->json([
                //     'error' => 'Cannot grade this subject. Prerequisite subject (ID: ' . $prerequisiteId . ') has not been taken.'
                // ], 400);
                return response()->json([
                    'error' => 'Cannot grade this subject. Prerequisite subject has not been taken.'
                ], 400);
            }

            // If prerequisite grade is null (not graded yet)
            if ($prerequisiteRecord->grade === null) {
                // return response()->json([
                //     'error' => 'Cannot grade this subject. Prerequisite subject (ID: ' . $prerequisiteId . ') has not been graded yet.'
                // ], 400);
                return response()->json([
                    'error' => 'Cannot grade this subject. Prerequisite subject has not been graded yet.'
                ], 400);
            }

            // If prerequisite has a failed grade
            if (in_array($prerequisiteRecord->grade, $failedGrades)) {
                // return response()->json([
                //     'error' => 'Cannot grade this subject. Prerequisite subject (ID: ' . $prerequisiteId . ') has a failing grade (' . $prerequisiteRecord->grade . ').'
                // ], 400);
                return response()->json([
                    'error' => 'Cannot grade this subject. Prerequisite subject has a failing grade (' . $prerequisiteRecord->grade . ').'
                ], 400);
            }
        }

        // If we're setting grade to null/empty
        if($validated['grade'] === null || $validated['grade'] === '') {
            DB::table('enrolled_sub')
                ->where('id', $validated['subject_id'])
                ->where('student_id', $student->id)
                ->update(['grade' => null]);

            // Remove from failed_subjects if it exists
            DB::table('failed_subjects')
                ->where('student_id', $student->id)
                ->where('subject_id', $enrolledSubject->subject_id)
                ->delete();

            return response()->json(['success' => 'Grade updated successfully']);
        }

        // Update the grade
        DB::table('enrolled_sub')
            ->where('id', $validated['subject_id'])
            ->where('student_id', $student->id)
            ->update(['grade' => $validated['grade']]);

        // Check if this is a failed grade and add to failed_subjects if needed
        if (in_array($validated['grade'], $failedGrades)) {
            DB::table('failed_subjects')->updateOrInsert(
                [
                    'student_id' => $student->id,
                    'subject_id' => $enrolledSubject->subject_id
                ],
                [
                    'grade' => $validated['grade'],
                    'date' => now()->format('Y-m-d H:i:s')
                ]
            );
        }
        // } else {
        //     // If grade is not failed, remove from failed_subjects if it exists
        //     DB::table('failed_subjects')
        //         ->where('student_id', $student->id)
        //         ->where('subject_id', $enrolledSubject->subject_id)
        //         ->delete();
        // }

        return response()->json(['success' => 'Grade updated successfully']);
    }

    public function getStudentEnrolledSubjects()
    {
        if (!Auth::guard('student')->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::guard('student')->user();
        $student = $user->user_information->student;

        $subjects = DB::table('enrolled_sub')
            ->where('student_id', $student->id)
            ->select('id', 'subject_code', 'subject_name', 'grade')
            ->orderBy('subject_code')
            ->get();

        return response()->json($subjects);
    }


}//END OF Class