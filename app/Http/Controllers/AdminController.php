<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Mail\PasswordResetOtp;
use App\Mail\PassKeyCreate;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\SubjectPrerequisite;
use App\Models\SubjectSchedule;
use App\Models\Passkey;
use App\Models\AuditLog;
use App\Models\AdminInfo;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Services\AdminService;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;


//NOTE: This Controller can detect schedule conflicts when creating/updating subjects.

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

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

    public function login(Request $request)
    {   
        $credentials = $request->only('email', 'password');
        $user = Admin::where('email', $credentials['email'])->first();

        if(!$user) {
            $x = 1;
            return $x;
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            $x = 2;
            return $x;  
        }

        if(!$user->is_active) {
            $x = 0;
            return $x;  
        }

        // if(Auth::guard('admin')->login($user)){
        //     $request->session()->put('admin', $user);
        //     $request->session()->regenerate();
        //     $x = 3;
        //     return $x;  

        // }

        if (Auth::guard('admin')->attempt([
            'email' => $request->email,
            'password' => $request->password
            ])) {
            $request->session()->regenerate();
            return response()->json(3);
        }


    }

    public function dashboard(){

        if (!Auth::guard('admin')->check()) {
            return redirect('/welcome_admin')->with('error', 'Please login first.');
        }

        $user = Auth::guard('admin')->user();
        $stats = $this->get_statistics();
        return view('admin.dashboard', compact('user', 'stats'));
        
    }


    public function cleanupExpiredPasskeys()
    {
        Log::info("=== CLEANUP STARTED ===");
        Log::info("Current time: " . now()->toDateTimeString());
        
        $lastCleanup = Cache::get('last_passkeys_cleanup_global');
        Log::info("Last cleanup time from cache: " . ($lastCleanup ? $lastCleanup->toDateTimeString() : 'Never'));
        
        // Run cleanup only once per 5 minutes to avoid overhead
        if (!$lastCleanup || now()->diffInMinutes($lastCleanup) >= 5) {
            Log::info("Proceeding with cleanup...");
            
            try {
                // Test the expired scope directly
                $expiredQuery = Passkey::expired()->toSql();
                Log::info("Expired query SQL: " . $expiredQuery);
                
                $expiredCount = Passkey::expired()->count();
                Log::info("Found {$expiredCount} expired passkeys");
                
                // Also check what's actually in the database
                $allPasskeys = Passkey::all();
                Log::info("Total passkeys in database: " . $allPasskeys->count());
                
                foreach ($allPasskeys as $passkey) {
                    Log::info("Passkey ID: {$passkey->id}, Expiration: {$passkey->expiration_date}, Is Expired: " . ($passkey->isExpired() ? 'Yes' : 'No'));
                }
                
                if ($expiredCount > 0) {
                    $deletedCount = Passkey::expired()->delete();
                    Log::info("SUCCESS: Deleted {$deletedCount} expired records");
                } else {
                    Log::info("No expired passkeys to delete");
                }
                
                Cache::put('last_passkeys_cleanup_global', now(), now()->addMinutes(5));
                Log::info("Cache updated with current time");
                
            } catch (\Exception $e) {
                Log::error("Cleanup failed: " . $e->getMessage());
                Log::error("Stack trace: " . $e->getTraceAsString());
            }
        } else {
            $minutesSinceLastCleanup = now()->diffInMinutes($lastCleanup);
            Log::info("Skipping cleanup - last cleanup was {$minutesSinceLastCleanup} minutes ago");
        }
        
        Log::info("=== CLEANUP FINISHED ===");
    }


    // public function cleanupExpiredPasskeys()
    // {
    //     $lastCleanup = Cache::get('last_passkeys_cleanup_global');
        
    //     // Run cleanup only once per hour to avoid overhead
    //     if (!$lastCleanup || now()->diffInMinutes($lastCleanup) >= 5) {
    //         try {
    //             $expiredCount = Passkey::expired()->count();
                
    //             if ($expiredCount > 0) {
    //                 $deletedCount = Passkey::expired()->delete();
    //                 Log::info("Global passkeys cleanup: Deleted {$deletedCount} expired records");
    //             }
                
    //             Cache::put('last_passkeys_cleanup_global', now(), now()->addMinutes(5));
                
    //         } catch (\Exception $e) {
    //             Log::error("Global passkeys cleanup failed: " . $e->getMessage());
    //         }
    //     }
    // }


    public function get_statistics() 
    {
        // Use Eloquent instead of raw SQL
        $students = Student::count();
        $instructors = Instructor::count();
        $subjects = Subject::where('is_active', 1)->count();
        $enrollments = Enrollment::where('status', 'Enrolled')->count();
        
        return [
            'students' => $students,
            'instructors' => $instructors,
            'subjects' => $subjects,
            'enrollments' => $enrollments
        ];
    }

    // Handle all AJAX requests from dashboard.js
    public function getStats(Request $request)
    {
        $action = $request->input('action');
        
        switch($action) {
            case 'get_stats':
                $stats = $this->get_statistics();
                return response()->json(['success' => true, 'stats' => $stats]);
            case 'get_curriculums':
                return $this->getCurriculums($request);
                
            case 'create_curriculum':
                return $this->createCurriculum($request);
                
            case 'get_prerequisites':
                $curriculumId = $request->input('curriculum_id');
    
                $query = Subject::where('is_active', 1);
                
                // Filter by curriculum if provided
                if ($curriculumId) {
                    $query->where('curriculum_id', $curriculumId);
                }
                
                $prerequisites = $query->select('id', 'code', 'name')
                    ->orderBy('year_level')
                    ->orderBy('semester')
                    ->orderBy('code')
                    ->get();
                    
                return response()->json(['success' => true, 'prerequisites' => $prerequisites]);
                
            case 'get_subjects':
                $curriculumId = $request->input('curriculum_id');
                Log::info('Fetching subjects with curriculum_id: ' . $curriculumId);

                // Build query with curriculum filter
                $query = Subject::with(['schedules', 'prerequisites'])
                    ->where('is_active', 1);
                
                // Add curriculum filter if provided
                if ($curriculumId) {
                    $query->where('curriculum_id', $curriculumId); // FIXED: Use 'curriculum_id' instead of 'curriculum'
                }
                
                $subjects = $query->orderBy('year_level')
                    ->orderBy('semester')
                    ->orderBy('code')
                    ->get()
                    ->map(function($subject) {
                        // Get curriculum year from curriculum table
                        $curriculumYear = DB::table('curriculum')
                            ->where('id', $subject->curriculum_id) // FIXED: Use 'curriculum_id' instead of 'curriculum'
                            ->value('curriculum_year');
                        
                        return [
                            'id' => $subject->id,
                            'code' => $subject->code,
                            'name' => $subject->name,
                            'units' => $subject->units,
                            'year_level' => $subject->year_level,
                            'semester' => $subject->semester,
                            'curriculum_id' => $subject->curriculum_id, // FIXED: Use 'curriculum_id' instead of 'curriculum'
                            'curriculum_year' => $curriculumYear,
                            'schedules' => $subject->schedules,
                            'prerequisites' => $subject->prerequisites
                        ];
                    });
                
                return response()->json(['success' => true, 'subjects' => $subjects]);
                
            case 'get_subject':
                $subjectId = $request->input('subject_id');
                $curriculumId = $request->input('curriculum_id');
                
                if ($subjectId) {
                    // Get single subject
                    $subject = Subject::with(['schedules', 'prerequisites'])
                        ->where('id', $subjectId)
                        ->where('is_active', 1)
                        ->first();
                        
                    if ($subject) {
                        $curriculumYear = DB::table('curriculum')
                            ->where('id', $subject->curriculum_id)
                            ->value('curriculum_year');
                            
                        $subjectData = [
                            'id' => $subject->id,
                            'code' => $subject->code,
                            'name' => $subject->name,
                            'units' => $subject->units,
                            'year_level' => $subject->year_level,
                            'semester' => $subject->semester,
                            'curriculum_id' => $subject->curriculum_id,
                            'curriculum_year' => $curriculumYear,
                            'max_students' => $subject->max_students,
                            'description' => $subject->description,
                            'schedules' => $subject->schedules,
                            'prerequisites' => $subject->prerequisites
                        ];
                        
                        return response()->json(['success' => true, 'subject' => $subjectData]);
                    }
                    
                    return response()->json(['success' => false, 'message' => 'Subject not found']);
                } else {
                    // Get all subjects with curriculum filter
                    $query = Subject::with(['schedules', 'prerequisites'])
                        ->where('is_active', 1);
                        
                    if ($curriculumId) {
                        $query->where('curriculum', $curriculumId);
                    }
                    
                    $subjects = $query->orderBy('year_level')
                        ->orderBy('semester')
                        ->orderBy('code')
                        ->get()
                        ->map(function($subject) {
                            $curriculumYear = DB::table('curriculum')
                                ->where('id', $subject->curriculum_id)
                                ->value('curriculum_year');
                                
                            return [
                                'id' => $subject->id,
                                'code' => $subject->code,
                                'name' => $subject->name,
                                'units' => $subject->units,
                                'year_level' => $subject->year_level,
                                'semester' => $subject->semester,
                                'curriculum_id' => $subject->curriculum_id,
                                'curriculum_year' => $curriculumYear,
                                'schedules' => $subject->schedules,
                                'prerequisites' => $subject->prerequisites
                            ];
                        });
                        
                    return response()->json(['success' => true, 'subjects' => $subjects]);
                }
                
            case 'create_subject':
                return $this->createSubject($request);
                
            case 'update_subject':
                return $this->updateSubject($request);
                
            case 'delete_subject':
                return $this->deleteSubject($request);
                
            case 'generate_passkey':
                return $this->generatePasskey($request);
                
            case 'get_audit_logs':
                return $this->getAuditLogs($request);

            case 'get_enrollment_periods':
                return $this->getEnrollmentPeriods($request);

            case 'get_enrollment_period':
                return $this->getEnrollmentPeriod($request);

            case 'save_enrollment_period':
                return $this->saveEnrollmentPeriod($request);

            case 'delete_enrollment_period':
                return $this->deleteEnrollmentPeriod($request);
                
            default:
                return response()->json(['success' => false, 'message' => 'Invalid action']);
        }
    }

    public function createSubject(Request $request)
    {
        DB::beginTransaction();
        
        try {
            // Check if subject code already exists
            // if (Subject::where('code', $request->code)->exists()) {
            //     return response()->json(['success' => false, 'message' => 'Subject code already exists']);
            // }

            // Check for duplicate schedules
            $schedules = $request->schedules;
            $uniqueSchedules = [];
            foreach ($schedules as $schedule) {
                $key = $schedule['section'] . '-' . $schedule['day'] . '-' . $schedule['start_time'] . '-' . $schedule['end_time'];
                if (!isset($uniqueSchedules[$key])) {
                    $uniqueSchedules[$key] = $schedule;
                } else {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Duplicate schedule found in your input']);
                }
            }

            // Check for schedule conflicts but don't block creation
            $conflicts = $this->checkScheduleConflicts($uniqueSchedules);
            $hasConflicts = !empty($conflicts);

            // Create subject even with conflicts
            $subject = Subject::create([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description ?? '',
                'units' => $request->units,
                'year_level' => $request->year_level,
                'semester' => $request->semester,
                'max_students' => $request->max_students,
                'curriculum_id' => $request->curr,
                'created_by' => Auth::guard('admin')->id(),
                'is_active' => 1
            ]);

            // Add prerequisites
            if (!empty($request->prerequisites)) {
                $prerequisites = $request->prerequisites;
                foreach ($prerequisites as $prereqId) {
                    SubjectPrerequisite::create([
                        'subject_id' => $subject->id,
                        'prerequisite_id' => $prereqId
                    ]);
                }
            }

            // Add schedules even with conflicts
            foreach ($uniqueSchedules as $schedule) {
                SubjectSchedule::create([
                    'subject_id' => $subject->id,
                    'Section' => $schedule['section'],
                    'day' => $schedule['day'],
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time'],
                    'room' => $schedule['room'] ?? null,
                    'Type' => $schedule['type']
                ]);
            }

            DB::commit();
            
            // Return success with conflict info if any
            $response = ['success' => true, 'message' => 'Subject created successfully'];
            if ($hasConflicts) {
                $response['has_conflicts'] = true;
                $response['conflicts'] = $conflicts;
                $response['available_slots'] = $this->getAvailableTimeSlots($uniqueSchedules);
            }
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Subject creation failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create subject: ' . $e->getMessage()]);
        }
    }

    private function checkScheduleConflicts($newSchedules, $excludeSubjectId = null)
    {
        $conflicts = [];
        
        foreach ($newSchedules as $index => $newSchedule) {
            // Check for room conflicts
            $roomQuery = SubjectSchedule::where('day', $newSchedule['day'])
                ->where('room', $newSchedule['room'])
                ->where(function($query) use ($newSchedule) {
                    $query->where(function($q) use ($newSchedule) {
                        $q->where('start_time', '<', $newSchedule['end_time'])
                        ->where('end_time', '>', $newSchedule['start_time']);
                    });
                })
                ->with('subject');

            if ($excludeSubjectId) {
                $roomQuery->whereHas('subject', function($q) use ($excludeSubjectId) {
                    $q->where('id', '!=', $excludeSubjectId);
                });
            }

            $roomConflicts = $roomQuery->get();

            foreach ($roomConflicts as $conflict) {
                // FIX: Check if subject exists before accessing its code
                $subjectCode = $conflict->subject ? $conflict->subject->code : 'Unknown Subject';
                $conflicts[] = [
                    'type' => 'room',
                    'message' => "Room {$newSchedule['room']} is already occupied on {$newSchedule['day']} from {$conflict->start_time} to {$conflict->end_time} by {$subjectCode}",
                    'conflicting_schedule' => $conflict,
                    'new_schedule' => $newSchedule
                ];
            }

            // Check for time overlap in the same section
            if (isset($newSchedule['section'])) {
                $sectionQuery = SubjectSchedule::where('day', $newSchedule['day'])
                    ->where('Section', $newSchedule['section'])
                    ->where(function($query) use ($newSchedule) {
                        $query->where(function($q) use ($newSchedule) {
                            $q->where('start_time', '<', $newSchedule['end_time'])
                            ->where('end_time', '>', $newSchedule['start_time']);
                        });
                    })
                    ->with('subject');

                if ($excludeSubjectId) {
                    $sectionQuery->whereHas('subject', function($q) use ($excludeSubjectId) {
                        $q->where('id', '!=', $excludeSubjectId);
                    });
                }

                $sectionConflicts = $sectionQuery->get();

                foreach ($sectionConflicts as $conflict) {
                    // FIX: Check if subject exists before accessing its code
                    $subjectCode = $conflict->subject ? $conflict->subject->code : 'Unknown Subject';
                    $conflicts[] = [
                        'type' => 'section_time',
                        'message' => "Section {$newSchedule['section']} already has a class on {$newSchedule['day']} from {$conflict->start_time} to {$conflict->end_time} for {$subjectCode}",
                        'conflicting_schedule' => $conflict,
                        'new_schedule' => $newSchedule
                    ];
                }
            }
        }
        
        return $conflicts;
    }

    // private function checkScheduleConflicts($newSchedules, $excludeSubjectId = null)
    // {
    //     $conflicts = [];
        
    //     foreach ($newSchedules as $index => $newSchedule) {
    //         // Check for room conflicts
    //         $roomQuery = SubjectSchedule::where('day', $newSchedule['day'])
    //             ->where('room', $newSchedule['room'])
    //             ->where(function($query) use ($newSchedule) {
    //                 $query->where(function($q) use ($newSchedule) {
    //                     $q->where('start_time', '<', $newSchedule['end_time'])
    //                     ->where('end_time', '>', $newSchedule['start_time']);
    //                 });
    //             })
    //             ->with('subject');

    //         if ($excludeSubjectId) {
    //             $roomQuery->whereHas('subject', function($q) use ($excludeSubjectId) {
    //                 $q->where('id', '!=', $excludeSubjectId);
    //             });
    //         }

    //         $roomConflicts = $roomQuery->get();

    //         foreach ($roomConflicts as $conflict) {
    //             $conflicts[] = [
    //                 'type' => 'room',
    //                 'message' => "Room {$newSchedule['room']} is already occupied on {$newSchedule['day']} from {$conflict->start_time} to {$conflict->end_time} by {$conflict->subject->code}",
    //                 'conflicting_schedule' => $conflict,
    //                 'new_schedule' => $newSchedule
    //             ];
    //         }

    //         // Check for time overlap in the same section
    //         if (isset($newSchedule['section'])) {
    //             $sectionQuery = SubjectSchedule::where('day', $newSchedule['day'])
    //                 ->where('Section', $newSchedule['section'])
    //                 ->where(function($query) use ($newSchedule) {
    //                     $query->where(function($q) use ($newSchedule) {
    //                         $q->where('start_time', '<', $newSchedule['end_time'])
    //                         ->where('end_time', '>', $newSchedule['start_time']);
    //                     });
    //                 })
    //                 ->with('subject');

    //             if ($excludeSubjectId) {
    //                 $sectionQuery->whereHas('subject', function($q) use ($excludeSubjectId) {
    //                     $q->where('id', '!=', $excludeSubjectId);
    //                 });
    //             }

    //             $sectionConflicts = $sectionQuery->get();

    //             foreach ($sectionConflicts as $conflict) {
    //                 $conflicts[] = [
    //                     'type' => 'section_time',
    //                     'message' => "Section {$newSchedule['section']} already has a class on {$newSchedule['day']} from {$conflict->start_time} to {$conflict->end_time} for {$conflict->subject->code}",
    //                     'conflicting_schedule' => $conflict,
    //                     'new_schedule' => $newSchedule
    //                 ];
    //             }
    //         }
    //     }
        
    //     return $conflicts;
    // }

    private function getAvailableTimeSlots($conflictingSchedules)
    {
        $suggestions = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $timeSlots = [
            ['08:00', '09:30'], ['09:30', '11:00'], ['11:00', '12:30'],
            ['13:00', '14:30'], ['14:30', '16:00'], ['16:00', '17:30']
        ];
        
        foreach ($conflictingSchedules as $schedule) {
            $day = $schedule['day'];
            $room = $schedule['room'];
            
            foreach ($timeSlots as $slot) {
                [$start, $end] = $slot;
                
                // Check if this time slot is available
                $isAvailable = !SubjectSchedule::where('day', $day)
                    ->where('room', $room)
                    ->where(function($query) use ($start, $end) {
                        $query->where(function($q) use ($start, $end) {
                            $q->where('start_time', '<', $end)
                            ->where('end_time', '>', $start);
                        });
                    })
                    ->exists();
                
                if ($isAvailable) {
                    $suggestions[] = [
                        'day' => $day,
                        'room' => $room,
                        'start_time' => $start,
                        'end_time' => $end,
                        'message' => "Available slot: {$day} {$start} - {$end} in Room {$room}"
                    ];
                }
            }
        }
        
        return array_slice($suggestions, 0, 5); // Return top 5 suggestions
    }

    private function getCurriculums(Request $request)
    {
        try {
            $curriculums = DB::table('curriculum')
                ->select('id', 'curriculum_year', 'is_active')
                ->orderBy('curriculum_year', 'desc')
                ->get();
                
            return response()->json(['success' => true, 'curriculums' => $curriculums]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch curriculums: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch curriculums']);
        }
    }

    private function createCurriculum(Request $request)
    {
        try {
            $curriculumYear = $request->input('curriculum_year');
            
            // Check if curriculum year already exists
            $exists = DB::table('curriculum')
                ->where('curriculum_year', $curriculumYear)
                ->exists();
                
            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Curriculum year already exists']);
            }
            
            // Create new curriculum
            DB::table('curriculum')->insert([
                'curriculum_year' => $curriculumYear,
                'is_active' => 1
            ]);
            
            return response()->json(['success' => true, 'message' => 'Curriculum created successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to create curriculum: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create curriculum']);
        }
    }

    // public function createSubject(Request $request)
    // {
    //     DB::beginTransaction();
        
    //     try {
    //         // Check if subject code already exists
    //         if (Subject::where('code', $request->code)->exists()) {
    //             return response()->json(['success' => false, 'message' => 'Subject code already exists']);
    //         }

    //         // Check for duplicate schedules
    //         $schedules = json_decode($request->schedules, true);
    //         $uniqueSchedules = [];
    //         foreach ($schedules as $schedule) {
    //             $key = $schedule['section'] . '-' . $schedule['day'] . '-' . $schedule['start_time'] . '-' . $schedule['end_time'];
    //             if (!isset($uniqueSchedules[$key])) {
    //                 $uniqueSchedules[$key] = $schedule;
    //             } else {
    //                 // Duplicate schedule found
    //                 DB::rollBack();
    //                 return response()->json(1); // This matches your JavaScript check for response == 1
    //             }
    //         }

    //         // Create subject
    //         $subject = Subject::create([
    //             'code' => $request->code,
    //             'name' => $request->name,
    //             'description' => $request->description ?? '',
    //             'units' => $request->units,
    //             'year_level' => $request->year_level,
    //             'semester' => $request->semester,
    //             'max_students' => $request->max_students,
    //             'created_by' => Auth::guard('admin')->id(),
    //             'is_active' => 1
    //         ]);

    //         // Add prerequisites
    //         if (!empty($request->prerequisites)) {
    //             $prerequisites = json_decode($request->prerequisites, true);
    //             foreach ($prerequisites as $prereqId) {
    //                 SubjectPrerequisite::create([
    //                     'subject_id' => $subject->id,
    //                     'prerequisite_id' => $prereqId
    //                 ]);
    //             }
    //         }

    //         // Add schedules
    //         foreach ($uniqueSchedules as $schedule) {
    //             SubjectSchedule::create([
    //                 'subject_id' => $subject->id,
    //                 'Section' => $schedule['section'],
    //                 'day' => $schedule['day'],
    //                 'start_time' => $schedule['start_time'],
    //                 'end_time' => $schedule['end_time'],
    //                 'room' => $schedule['room'] ?? null,
    //                 'Type' => $schedule['type']
    //             ]);
    //         }

    //         DB::commit();
    //         return response()->json(['success' => true, 'message' => 'Subject created successfully']);
            
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Subject creation failed: ' . $e->getMessage());
    //         return response()->json(['success' => false, 'message' => 'Failed to create subject: ' . $e->getMessage()]);
    //     }
    // }


    public function updateSubject(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $subject = Subject::find($request->subject_id);
            if (!$subject) {
                return response()->json(['success' => false, 'message' => 'Subject not found']);
            }

            // Check if subject code exists (excluding current subject)
            // if (Subject::where('code', $request->code)->where('id', '!=', $request->subject_id)->exists()) {
            //     return response()->json(['success' => false, 'message' => 'Subject code already exists']);
            // }

            // Check for duplicate schedules
            $schedules = $request->schedules;
            $uniqueSchedules = [];
            foreach ($schedules as $schedule) {
                $key = $schedule['section'] . '-' . $schedule['day'] . '-' . $schedule['start_time'] . '-' . $schedule['end_time'];
                if (!isset($uniqueSchedules[$key])) {
                    $uniqueSchedules[$key] = $schedule;
                } else {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Duplicate schedule found in your input']);
                }
            }

            // Check for schedule conflicts but don't block update
            $conflicts = $this->checkScheduleConflicts($uniqueSchedules, $subject->id);
            $hasConflicts = !empty($conflicts);

            // Update subject
             $subject->update([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description ?? '',
                'units' => $request->units,
                'year_level' => $request->year_level,
                'semester' => $request->semester,
                'max_students' => $request->max_students,
                // 'curriculum_id' => $request->curr, // Add this line
            ]);


            // Update prerequisites
            SubjectPrerequisite::where('subject_id', $subject->id)->delete();
            if (!empty($request->prerequisites)) {
                $prerequisites = $request->prerequisites;
                foreach ($prerequisites as $prereqId) {
                    SubjectPrerequisite::create([
                        'subject_id' => $subject->id,
                        'prerequisite_id' => $prereqId
                    ]);
                }
            }

            // Update schedules even with conflicts
            SubjectSchedule::where('subject_id', $subject->id)->delete();
            foreach ($uniqueSchedules as $schedule) {
                SubjectSchedule::create([
                    'subject_id' => $subject->id,
                    'Section' => $schedule['section'],
                    'day' => $schedule['day'],
                    'start_time' => $schedule['start_time'],
                    'end_time' => $schedule['end_time'],
                    'room' => $schedule['room'] ?? null,
                    'Type' => $schedule['type']
                ]);
            }

            DB::commit();
            
            // Return success with conflict info if any
            $response = ['success' => true, 'message' => 'Subject updated successfully'];
            if ($hasConflicts) {
                $response['has_conflicts'] = true;
                $response['conflicts'] = $conflicts;
                $response['available_slots'] = $this->getAvailableTimeSlots($uniqueSchedules);
            }
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Subject update failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update subject: ' . $e->getMessage()]);
        }
    }

    private function checkScheduleConflicts2($newSchedules, $excludeSubjectId = null)
    {
        $conflicts = [];
        
        foreach ($newSchedules as $index => $newSchedule) {
            // Check for room conflicts
            $roomQuery = SubjectSchedule::where('day', $newSchedule['day'])
                ->where('room', $newSchedule['room'])
                ->where(function($query) use ($newSchedule) {
                    $query->where(function($q) use ($newSchedule) {
                        $q->where('start_time', '<', $newSchedule['end_time'])
                        ->where('end_time', '>', $newSchedule['start_time']);
                    });
                })
                ->with('subject');

            if ($excludeSubjectId) {
                $roomQuery->whereHas('subject', function($q) use ($excludeSubjectId) {
                    $q->where('id', '!=', $excludeSubjectId);
                });
            }

            $roomConflicts = $roomQuery->get();

            foreach ($roomConflicts as $conflict) {
                $conflicts[] = [
                    'type' => 'room',
                    'message' => "Room {$newSchedule['room']} is already occupied on {$newSchedule['day']} from {$conflict->start_time} to {$conflict->end_time} by {$conflict->subject->code}",
                    'conflicting_schedule' => $conflict,
                    'new_schedule' => $newSchedule
                ];
            }

            // Check for time overlap in the same section
            if (isset($newSchedule['section'])) {
                $sectionQuery = SubjectSchedule::where('day', $newSchedule['day'])
                    ->where('Section', $newSchedule['section'])
                    ->where(function($query) use ($newSchedule) {
                        $query->where(function($q) use ($newSchedule) {
                            $q->where('start_time', '<', $newSchedule['end_time'])
                            ->where('end_time', '>', $newSchedule['start_time']);
                        });
                    })
                    ->with('subject');

                if ($excludeSubjectId) {
                    $sectionQuery->whereHas('subject', function($q) use ($excludeSubjectId) {
                        $q->where('id', '!=', $excludeSubjectId);
                    });
                }

                $sectionConflicts = $sectionQuery->get();

                foreach ($sectionConflicts as $conflict) {
                    $conflicts[] = [
                        'type' => 'section_time',
                        'message' => "Section {$newSchedule['section']} already has a class on {$newSchedule['day']} from {$conflict->start_time} to {$conflict->end_time} for {$conflict->subject->code}",
                        'conflicting_schedule' => $conflict,
                        'new_schedule' => $newSchedule
                    ];
                }
            }
        }
        
        return $conflicts;
    }

    // public function updateSubject(Request $request)
    // {
    //     DB::beginTransaction();
        
    //     try {
    //         $subject = Subject::find($request->subject_id);
    //         if (!$subject) {
    //             return response()->json(['success' => false, 'message' => 'Subject not found']);
    //         }

    //         // Check if subject code exists (excluding current subject)
    //         if (Subject::where('code', $request->code)->where('id', '!=', $request->subject_id)->exists()) {
    //             return response()->json(['success' => false, 'message' => 'Subject code already exists']);
    //         }

    //         // Check for duplicate schedules
    //         $schedules = $request->schedules;
    //         $uniqueSchedules = [];
    //         foreach ($schedules as $schedule) {
    //             $key = $schedule['section'] . '-' . $schedule['day'] . '-' . $schedule['start_time'] . '-' . $schedule['end_time'];
    //             if (!isset($uniqueSchedules[$key])) {
    //                 $uniqueSchedules[$key] = $schedule;
    //             } else {
    //                 // Duplicate schedule found
    //                 DB::rollBack();
    //                 return response()->json(1);
    //             }
    //         }

    //         // Update subject
    //         $subject->update([
    //             'code' => $request->code,
    //             'name' => $request->name,
    //             'description' => $request->description ?? '',
    //             'units' => $request->units,
    //             'year_level' => $request->year_level,
    //             'semester' => $request->semester,
    //             'max_students' => $request->max_students,
    //         ]);

    //         // Update prerequisites
    //         SubjectPrerequisite::where('subject_id', $subject->id)->delete();
    //         if (!empty($request->prerequisites)) {
    //             $prerequisites = json_decode($request->prerequisites, true);
    //             foreach ($prerequisites as $prereqId) {
    //                 SubjectPrerequisite::create([
    //                     'subject_id' => $subject->id,
    //                     'prerequisite_id' => $prereqId
    //                 ]);
    //             }
    //         }

    //         // Update schedules
    //         SubjectSchedule::where('subject_id', $subject->id)->delete();
    //         foreach ($uniqueSchedules as $schedule) {
    //             SubjectSchedule::create([
    //                 'subject_id' => $subject->id,
    //                 'Section' => $schedule['section'],
    //                 'day' => $schedule['day'],
    //                 'start_time' => $schedule['start_time'],
    //                 'end_time' => $schedule['end_time'],
    //                 'room' => $schedule['room'] ?? null,
    //                 'Type' => $schedule['type']
    //             ]);
    //         }

    //         DB::commit();
    //         return response()->json(['success' => true, 'message' => 'Subject updated successfully']);
            
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Subject update failed: ' . $e->getMessage());
    //         return response()->json(['success' => false, 'message' => 'Failed to update subject: ' . $e->getMessage()]);
    //     }
    // }

    public function deleteSubject(Request $request)
    {
        try {
            $subject = Subject::find($request->subject_id);
            if (!$subject) {
                return response()->json(['success' => false, 'message' => 'Subject not found']);
            }

            // Check if subject has enrollments
            if (\App\Models\Enrollment::where('subject_id', $subject->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Cannot delete subject with existing enrollments']);
            }

            // Soft delete by setting is_active to 0
            // $subject->update(['is_active' => 0]);

            // Hard delete
            Subject::where('id', $request->subject_id)->delete();

            return response()->json(['success' => true, 'message' => 'Subject deleted successfully']);
            
        } catch (\Exception $e) {
            Log::error('Subject deletion failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete subject: ' . $e->getMessage()]);
        }
    }

    public function generatePasskey(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validate required fields
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'passkey' => 'required',
                'userType' => 'required'
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                Log::error('Passkey validation failed: ' . json_encode($validator->errors()));
                return response()->json(1);
            }

            // Get the authenticated admin
            $admin = Auth::guard('admin')->user();

            if (!$admin) {
                DB::rollBack();
                Log::error('No authenticated admin found');
                return response()->json(1);
            }

            // Find the admin_info record for this admin
            $adminInfo = AdminInfo::where('admin_id', $admin->admin_id)->first();

            if (!$adminInfo) {
                DB::rollBack();
                Log::error('AdminInfo record not found for admin ID: ' . $admin->admin_id);
                return response()->json(1);
            }

            $now = now()->setTimezone('Asia/Manila');


            try {
                Mail::to($request->email)->send(new PassKeyCreate($request->passkey));

                $savePasskey = Passkey::create([
                    'passkey' => $request->passkey,
                    'email3' => $request->email,
                    'created_by' => $adminInfo->id,
                    'date_created' => $now,
                    'expiration_date' => $now->copy()->addDay(1),
                    'is_used' => 0,
                    'user_type' => $request->userType
                ]);

                if(!$savePasskey){
                    DB::rollBack();
                    Log::error('Failed to create passkey record');
                    return response()->json(1);
                }

                 DB::commit();
                Log::info('Passkey generated successfully for email: ' . $request->email);
                return response()->json(7);

            } catch (\Exception $e) {
                return response()->json(1);
            }
            
            
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Passkey generation failed: ' . $e->getMessage());
            return response()->json(1);
            
        }


    }


    // public function generatePasskey(Request $request)
    // {
    //     try {
    //         $passkey = Str::random(15);
            
    //         Passkey::create([
    //             'passkey' => $passkey,
    //             'email3' => $request->email,
    //             'created_by' => Auth::guard('admin')->id(),
    //             'expiration_date' => now()->addDays(7),
    //             'user_type' => $request->user_type
    //         ]);

    //         // Here you would typically send the email with the passkey
    //         // For now, we'll just return success
            
    //         return response()->json([
    //             'success' => true, 
    //             'message' => 'Passkey generated successfully',
    //             'passkey' => $passkey
    //         ]);
            
    //     } catch (\Exception $e) {
    //         Log::error('Passkey generation failed: ' . $e->getMessage());
    //         return response()->json(['success' => false, 'message' => 'Failed to generate passkey']);
    //     }
    // }

    public function getAuditLogs(Request $request)
    {
        try {
            $logs = DB::table('auditlogs')
                ->select('date as timestamp', 'action', 'details', 'ip_address', 'user_id')
                ->limit(100)
                ->get()
                ->map(function($log) {
                    return (array) $log;
                });

            return response()->json(['success' => true, 'logs' => $logs]);
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch audit logs: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch audit logs']);
        }
    }

    public function getAuditLogs2(Request $request)
    {
        try {
            Log::info('getAuditLogs method called'); // Add this line
            
            $logs = DB::table('auditlogs')
                ->select('date as timestamp', 'action', 'details', 'ip_address', 'user_id')
                ->orderBy('date', 'desc')
                ->limit(100)
                ->get()
                ->map(function($log) {
                    return (array) $log;
                });

            Log::info('Logs count: ' . $logs->count()); // Add this line
            Log::info('Logs data: ', $logs->toArray()); // Add this line

            return response()->json(['success' => true, 'logs' => $logs]);
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch audit logs: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch audit logs']);
        }
    }

    // public function getPrerequisites(Request $request)
    // {
    //     $prerequisites = $this->adminService->getPrerequisiteOptions();
    //     return response()->json(['success' => true, 'prerequisites' => $prerequisites]);
    // }

    // public function getSubjects(Request $request)
    // {
    //     $subjects = $this->adminService->getAllSubjectsWithSchedules();
    //     return response()->json(['success' => true, 'subjects' => $subjects]);
    // }



    public function forgotPassword(Request $request){
        $credentials = $request->only('email', 'password', 'confirmPassword');
        $user = Admin::where('email', $credentials['email'])->first();
        if(!$user) {
            $x = 1;
            return $x;
        }

        if($credentials['password'] !== $credentials['confirmPassword']){
            $x = 2;
            return $x;
        }
        $thePassword = $user->password = Hash::make($credentials['password']);
        // $thePassword = Hash::make($credentials['password']);
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store in cache with email
        Cache::put('password_reset_' . $request->email, [
            'otp' => $otp,
            'password' => Hash::make($request->password),
            'email' => $request->email
        ], now()->addMinutes(10));

        // Store email in Laravel session for the reset page
        session(['password_reset_email' => $request->email]);

        try {
            Mail::to($request->email)->send(new PasswordResetOtp($otp));
            $x = 5;
            return $x;
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send email'], 500);
        }

    }


    public function resetPassword(Request $request){
        $credentials = $request->only('email', 'password', 'otp');
        $user = Admin::where('email', $credentials['email'])->first();
        if(!$user) {
            $x = 0;
            return $x;
        }
        
        $cachedData = Cache::get('password_reset_' . $request->email);
        
        if (!$cachedData || $cachedData['otp'] !== $request->otp) {
            $x = 0;
            return $x;
        }

        // Update password
        // $user->update(['password' => Hash::make($credentials['password'])]);
        $user->update([
            'password' => $credentials['password']
        ]);
        
        // Clear OTP from cache
        Cache::forget('password_reset_' . $request->email);

        $x = 1;
        return $x;

    }

    public function logout(Request $request){
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin');
    }



    public function getEnrollmentPeriods(Request $request)
    {
        try {
            $periods = DB::table('enrollment_date')
                ->orderBy('start', 'desc') // Changed from 'start_date' to 'Start'
                ->get()
                ->map(function($period) {
                    return [
                        'id' => $period->id,
                        'semester' => $period->semester, // Note the capital 'S'
                        'academic_year' => $period->academic_year ?? null,
                        'start_date' => $period->start, // Note the capital 'S'
                        'end_date' => $period->end,     // Note the capital 'E'
                        'is_active' => $period->is_active ?? true,
                        'admin_id' => $period->admin_id
                    ];
                });

            return response()->json(['success' => true, 'enrollment_periods' => $periods]);
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch enrollment periods: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch enrollment periods']);
        }
    }

    public function getEnrollmentPeriod(Request $request)
    {
        try {
            $period = DB::table('enrollment_date')
                ->where('ID', $request->enrollment_id)
                ->first();

            if (!$period) {
                return response()->json(['success' => false, 'message' => 'Enrollment period not found']);
            }

            return response()->json([
                'success' => true,
                'enrollment_period' => [
                    'id' => $period->id,
                    'semester' => $period->semester, // Capital 'S'
                    'academic_year' => $period->academic_year ?? null,
                    'start_date' => $period->start, // Capital 'S'
                    'end_date' => $period->end,     // Capital 'E'
                    'is_active' => $period->is_active ?? true,
                    'admin_id' => $period->admin_id
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch enrollment period: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch enrollment period']);
        }
    }

    public function saveEnrollmentPeriod(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $admin = Auth::guard('admin')->user();
            
            $data = [
                'semester' => $request->semester,
                'academic_year' => $request->academic_year,
                'start' => $request->start_date,
                'end' => $request->end_date,
                'is_active' => $request->is_active,
                'admin_id' => $admin->admin_id
            ];

                DB::table('enrollment_date')
                ->delete();
                // Create new period
                DB::table('enrollment_date')->insert($data); 
                
                // Always update student year count when creating new enrollment period
                $this->updateStudentYearCountWithTransaction();

                // Update student status
                // Student::whereNotIn('is_regular', ['5', '6'])
                //     ->update([
                //         'status' => 'Not Enrolled',
                //         'is_regular' => 5
                //     ]);
                Student::whereNotIn('is_regular', ['7', '8'])
                    ->update([
                        'status' => 'Not Enrolled',
                        'is_regular' => 5
                    ]);
                    
                // Disable foreign key checks
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');

                // Delete records
                DB::table('enrollmentrequests')->delete();
                DB::table('enrollments')->delete();
                DB::table('documents')->delete();
                DB::table('payments')->delete();

                // Re-enable foreign key checks
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                

                $message = 'Enrollment period created successfully';
            

            DB::commit();
            return response()->json(['success' => true, 'message' => $message]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save enrollment period: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save enrollment period: ' . $e->getMessage()]);
        }
    }

    public function updateStudentYearCountWithTransaction()
    {
        return DB::transaction(function () {
            $currentSemester = DB::table('enrollment_date')
                ->where('is_active', 1)
                ->value('semester');

            Log::info("Current semester: " . $currentSemester);

            // Get all officially enrolled students, not just from enrollmentrequests
            $enrolledStudents = DB::table('students')
                ->where('status', 'Officially Enrolled')
                ->select('id', 'year_level')
                ->get();

            Log::info("Enrolled students count: " . $enrolledStudents->count());

            foreach ($enrolledStudents as $student) {
                // Extract numeric part from year level (e.g., "3rd Year" -> 3)
                $yearLevel = (int) filter_var($student->year_level, FILTER_SANITIZE_NUMBER_INT);
                
                // Increment year level if it's 1st semester
                $totalYear = ($currentSemester === '1st Sem') ? $yearLevel + 1 : $yearLevel;
                
                Log::info("Student ID: {$student->id}, Year Level: {$yearLevel}, Total Year: {$totalYear}");

                // Check if record exists
                $existingRecord = DB::table('student_count_year')
                    ->where('student_id', $student->id)
                    ->first();
            
                if ($existingRecord) {
                    if($existingRecord->total_year > $totalYear) {
                        Log::info("Don't update student_count_year for student: {$existingRecord->total_year}");
                    }else{
                        DB::table('student_count_year')
                            ->where('student_id', $student->id)
                            ->update([
                            'total_year' => $totalYear,
                            'is_active' => 1
                            ]);
                        Log::info("Updated student_count_year for student: {$student->id}");
                    }
                } else {
                    DB::table('student_count_year')
                        ->insert([
                            'student_id' => $student->id,
                            'total_year' => $totalYear,
                            'is_active' => 1
                        ]);
                    Log::info("Inserted student_count_year for student: {$student->id}");
                }
            }
            
            Log::info("Completed student_count_year update for " . $enrolledStudents->count() . " students");
        });
    }

    // public function updateStudentYearCountWithTransaction()
    // {
    //     return DB::transaction(function () {
    //         $currentSemester = DB::table('enrollment_date')
    //             ->where('is_active', 1)
    //             ->value('semester');

    //         $approvedStudents = DB::table('enrollmentrequests')
    //             ->where('status', 'Approved')
    //             ->join('students', 'enrollmentrequests.student_id', '=', 'students.id')
    //             ->select('enrollmentrequests.student_id', 'students.year_level')
    //             ->get();

    //         foreach ($approvedStudents as $student) {
    //             $yearLevel = (int) filter_var($student->year_level, FILTER_SANITIZE_NUMBER_INT);
    //             $totalYear = ($currentSemester === '1st Sem') ? $yearLevel + 1 : $yearLevel;
                
    //             // Using DB::table for updateOrCreate equivalent
    //             $existingRecord = DB::table('student_count_year')
    //                 ->where('student_id', $student->student_id)
    //                 ->first();
                
    //             if ($existingRecord) {
    //                 DB::table('student_count_year')
    //                     ->where('student_id', $student->student_id)
    //                     ->update([
    //                         'total_year' => $totalYear,
    //                         'is_active' => 1
    //                     ]);
    //             } else {
    //                 DB::table('student_count_year')
    //                     ->insert([
    //                         'student_id' => $student->student_id,
    //                         'total_year' => $totalYear,
    //                         'is_active' => 1
    //                     ]);
    //             }
    //         }
            
    //         // return ['success' => true, 'updated_count' => count($approvedStudents)];
    //         return;
    //     });
    // }

    public function deleteEnrollmentPeriod(Request $request)
    {
        try {
            // DB::table('enrollment_date')
            //     ->where('ID', $request->enrollment_id)
            //     ->delete();

            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            DB::table('enrollment_date')
                ->delete();

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');


            return response()->json(['success' => true, 'message' => 'Enrollment period deleted successfully']);
            
        } catch (\Exception $e) {
            Log::error('Failed to delete enrollment period: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete enrollment period']);
        }
    }
    
    
    // public function uploadCSV(Request $request)
    // {
    //     // Validation
    //     $validator = Validator::make($request->all(), [
    //         'csvFile' => 'required|file|mimes:csv,txt'
    //         // 'skipHeaders' => 'boolean',
    //         // 'updateExisting' => 'boolean'
    //     ]);
        
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed: ' . $validator->errors()->first()
    //         ], 422);
    //     }
        
    //     try {
    //         $file = $request->file('csvFile');
    //         // $skipHeaders = $request->boolean('skipHeaders', true);
    //         // $updateExisting = $request->boolean('updateExisting', true);
            
    //         // Generate unique filename
    //         $fileName = 'applications_' . time() . '_' . uniqid() . '.csv';
            
    //         // Store file using Laravel's storage system (public disk)
    //         $filePath = $file->storeAs('documents/csv', $fileName, 'public');
            
    //         // Get the full path to the stored file
    //         $fullPath = storage_path('app/public/' . $filePath);
            
    //         // Open and process CSV file
    //         if (($handle = fopen($fullPath, 'r')) !== FALSE) {
    //             // Read headers
    //             $headers = fgetcsv($handle);
                
    //             // Skip headers if option is checked
                
    //             if (!$headers) {
    //                 fclose($handle);
    //                 Storage::disk('public')->delete($filePath);
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'Error: CSV file is empty or invalid.'
    //                 ]);
    //             }
                
    //             // Clean and normalize headers
    //             $cleanedHeaders = array_map(function($header) {
    //                 // Remove BOM if present
    //                 $header = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header);
    //                 // Trim whitespace and special characters
    //                 $header = trim($header);
    //                 // Convert to lowercase and replace spaces/punctuation with underscores
    //                 $header = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $header));
    //                 // Remove multiple underscores
    //                 $header = preg_replace('/_+/', '_', $header);
    //                 // Remove leading/trailing underscores
    //                 $header = trim($header, '_');
    //                 return $header;
    //             }, $headers);
                
    //             // Debug: Log cleaned headers
    //             Log::info('Cleaned Headers:', $cleanedHeaders);
                
    //             // Expected headers in various possible formats
    //             $expectedHeaders = [
    //                 'id' => ['no.', 'no_', 'number', 'id', 'csv_no'],
    //                 'student_number' => [
    //                     'no','student_no', 'student_no_', 'stud_no', 'student_number', 'application'
    //                 ],
                    
    //                 'application_number' => ['application_no', 'application_no_', 'app_no', 'application_number', 'application'],
    //                 'preferred_program' => ['preferred_program', 'program', 'course', 'preferred_course'],
    //                 'lastname' => ['last_name', 'lastname', 'surname', 'family_name'],
    //                 'firstname' => ['first_name', 'firstname', 'given_name'],
    //                 'middlename' => ['middle_name', 'middlename', 'middle_initial'],
    //                 'email' => ['email', 'e_mail', 'email_address', 'e_mail_address'],
    //                 'contact_number' => ['contact_number', 'contact', 'phone', 'mobile', 'phone_number']
    //             ];
                
    //             // Map actual headers to expected headers
    //             $headerMapping = [];
    //             $missingHeaders = [];
                
    //             foreach ($expectedHeaders as $expectedKey => $possibleNames) {
    //                 $found = false;
    //                 foreach ($cleanedHeaders as $index => $actualHeader) {
    //                     if (in_array($actualHeader, $possibleNames)) {
    //                         $headerMapping[$expectedKey] = $index;
    //                         $found = true;
    //                         break;
    //                     }
    //                 }
    //                 if (!$found) {
    //                     $missingHeaders[] = $expectedKey;
    //                 }
    //             }
                
    //             // Check for missing required headers
    //             // $requiredHeaders = ['student_number', 'lastname', 'firstname', 'email'];
    //             // $missingRequired = array_intersect($requiredHeaders, $missingHeaders);
                
    //             // if (!empty($missingRequired)) {
    //             //     fclose($handle);
    //             //     Storage::disk('public')->delete($filePath);
    //             //     return response()->json([
    //             //         'success' => false,
    //             //         'message' => 'Error: Missing required columns: ' . implode(', ', $missingRequired) . 
    //             //                     '<br>Found columns: ' . implode(', ', $cleanedHeaders) .
    //             //                     '<br>Expected columns: student_number, application_number, preffered_program, lastname, firstname, middlename, email, contact_number'
    //             //     ]);
    //             // }
                
    //             // Statistics
    //             $totalRows = 0;
    //             $successfulRows = 0;
    //             $failedRows = 0;
    //             $duplicateRows = 0;
    //             $errors = [];
                
    //             // Start transaction for database operations
    //             DB::beginTransaction();
                
    //             try {
    //                 // Process each row
    //                 while (($row = fgetcsv($handle)) !== FALSE) {
    //                     $totalRows++;
                        
    //                     // Skip empty rows
    //                     if (empty(array_filter($row))) {
    //                         continue;
    //                     }
                        
    //                     // Prepare data array
    //                     $data = [];
    //                     foreach ($headerMapping as $columnName => $columnIndex) {
    //                         if (isset($row[$columnIndex]) && $row[$columnIndex] !== '') {
    //                             $data[$columnName] = trim($row[$columnIndex]);
    //                         } else {
    //                             $data[$columnName] = null;
    //                         }
    //                     }
                        
    //                     // Validate required data
    //                     $rowErrors = [];
                        
    //                     // Check required fields
    //                     // foreach ($requiredHeaders as $required) {
    //                     //     if (empty($data[$required])) {
    //                     //         $rowErrors[] = "Missing $required";
    //                     //     }
    //                     // }
                        
    //                     // Validate email if present
    //                     if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    //                         $rowErrors[] = "Invalid email format: " . $data['email'];
    //                     }
                        
    //                     // If validation errors, skip this row
    //                     if (!empty($rowErrors)) {
    //                         $failedRows++;
    //                         $errors[] = "Row $totalRows: " . implode('; ', $rowErrors);
    //                         continue;
    //                     }
                        
    //                     // Sanitize data
    //                     foreach ($data as $key => $value) {
    //                         if (is_string($value)) {
    //                             $data[$key] = htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
    //                         }
    //                     }
                        
    //                     // Check if record exists
    //                     // $existing = DB::table('csv')
    //                     //     ->where('student_number', $data['student_number'])
    //                     //     ->orWhere('email', $data['email'])
    //                     //     ->first();
                        
    //                     // if ($existing) {
    //                     //     if ($updateExisting) {
    //                     //         // Update existing record
    //                     //         DB::table('csv')
    //                     //             ->where('id', $existing->id)
    //                     //             ->update($data);
    //                     //         $successfulRows++;
    //                     //     } else {
    //                     //         $duplicateRows++;
    //                     //         $errors[] = "Row $totalRows: Duplicate entry for student_number: {$data['student_number']}";
    //                     //     }
    //                     // } else {
    //                     //     // Insert new record
    //                     //     DB::table('csv')->insert($data);
    //                     //     $successfulRows++;
    //                     // }

                    
    //                         // Insert new record
    //                         DB::table('csv')->insert($data);
    //                         $successfulRows++;
    //                 }
                    
    //                 DB::commit();
                    
    //             } catch (\Exception $e) {
    //                 DB::rollBack();
    //                 fclose($handle);
    //                 Storage::disk('public')->delete($filePath);
                    
    //                 return response()->json([
    //                     'success' => false,
    //                     'message' => 'Database error: ' . $e->getMessage()
    //                 ], 500);
    //             }
                
    //             fclose($handle);
                
    //             // Get admin user
    //             $admin = Auth::guard('admin')->user();
                
    //             // Log the upload in audit logs
    //             $details = "CSV File: " . $file->getClientOriginalName() . 
    //                     " (Stored as: " . $fileName . ")" .
    //                     " - Total rows: $totalRows, " .
    //                     "Successfully imported/updated: $successfulRows, " .
    //                     "Duplicates: $duplicateRows, " .
    //                     "Failed: $failedRows";
                
    //             if (!empty($errors)) {
    //                 $details .= ", Errors: " . count($errors) . " rows failed";
    //             }
                
    //             // Store file info in csv_uploads table
    //             // $uploadLog = DB::table('csv_uploads')->insertGetId([
    //             //     'admin_id' => $admin->admin_id,
    //             //     'original_filename' => $file->getClientOriginalName(),
    //             //     'stored_filename' => $fileName,
    //             //     'file_path' => $filePath,
    //             //     'total_rows' => $totalRows,
    //             //     'successful_rows' => $successfulRows,
    //             //     'duplicate_rows' => $duplicateRows,
    //             //     'failed_rows' => $failedRows,
    //             //     'error_log' => !empty($errors) ? json_encode(array_slice($errors, 0, 20)) : null,
    //             //     'created_at' => now(),
    //             //     'updated_at' => now()
    //             // ]);
                
    //             // Prepare response message
    //             $message = "<div style='background-color: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;'>";
    //             $message .= "<h4><i class='fas fa-check-circle me-2'></i> Import Summary</h4>";
    //             $message .= "<p><strong>File:</strong> " . htmlspecialchars($file->getClientOriginalName()) . "</p>";
    //             $message .= "<p><strong>Total rows processed:</strong> $totalRows</p>";
    //             $message .= "<p><strong>Successfully imported/updated:</strong> $successfulRows</p>";
    //             $message .= "<p><strong>Duplicates skipped:</strong> $duplicateRows</p>";
    //             $message .= "<p><strong>Failed rows:</strong> $failedRows</p>";
    //             $message .= "</div>";
                
    //             if (!empty($errors)) {
    //                 $message .= "<h4><i class='fas fa-exclamation-triangle me-2'></i> Error Details:</h4>";
    //                 $message .= "<div style='background-color: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; max-height: 200px; overflow-y: auto;'>";
    //                 foreach (array_slice($errors, 0, 10) as $error) {
    //                     $message .= "<p class='mb-1'>$error</p>";
    //                 }
    //                 if (count($errors) > 10) {
    //                     $message .= "<p class='text-muted'>... and " . (count($errors) - 10) . " more errors</p>";
    //                 }
    //                 $message .= "</div>";
    //             }
                
    //             // Add action buttons
    //             $message .= "<div class='mt-3 d-flex gap-2'>";
    //             $message .= "<a href='/admin/view-csv-data' class='btn btn-primary' target='_blank'>";
    //             $message .= "<i class='fas fa-eye me-2'></i>View Imported Data";
    //             $message .= "</a>";
    //             $message .= "<button type='button' class='btn btn-outline-primary' onclick='loadCSVData()'>";
    //             $message .= "<i class='fas fa-sync-alt me-2'></i>Refresh Table";
    //             $message .= "</button>";
    //             $message .= "<a href='" . Storage::url($filePath) . "' class='btn btn-outline-success' download>";
    //             $message .= "<i class='fas fa-download me-2'></i>Download Stored File";
    //             $message .= "</a>";
    //             $message .= "</div>";
                
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => $message,
    //                 'totalRows' => $totalRows,
    //                 'successfulRows' => $successfulRows,
    //                 'duplicateRows' => $duplicateRows,
    //                 'failedRows' => $failedRows,
    //                 'errors' => $errors,
    //                 'filePath' => $filePath,
    //                 'fileName' => $fileName
    //                 // 'uploadId' => $uploadLog
    //             ]);
                
    //         } else {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Error: Could not open CSV file.'
    //             ]);
    //         }
            
    //     } catch (\Exception $e) {
    //         // Delete stored file if error occurred
    //         if (isset($filePath) && Storage::disk('public')->exists($filePath)) {
    //             Storage::disk('public')->delete($filePath);
    //         }
            
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Upload failed: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
    
    public function uploadCSV(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'csvFile' => 'required|file|mimes:csv,txt'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first()
            ], 422);
        }
        
        try {
            $file = $request->file('csvFile');
            
            // Generate unique filename
            $fileName = 'applications_' . time() . '_' . uniqid() . '.csv';
            
            // Store file
            $filePath = $file->storeAs('documents/csv', $fileName, 'public');
            $fullPath = storage_path('app/public/' . $filePath);
            
            // Open and process CSV
            if (($handle = fopen($fullPath, 'r')) !== FALSE) {
                // Read headers
                $headers = fgetcsv($handle);
                
                if (!$headers) {
                    fclose($handle);
                    return response()->json([
                        'success' => false,
                        'message' => 'CSV file is empty'
                    ]);
                }
                
                // Process each row
                $totalRows = 0;
                $insertedRows = 0;
                $updatedRows = 0;
                $errors = [];
                
                while (($row = fgetcsv($handle)) !== FALSE) {
                    $totalRows++;
                    
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    
                    try {
                        // Prepare data
                        $data = [
                            'student_number' => $row[0] ?? null,
                            'application_number' => $row[1] ?? null,
                            'preferred_program' => $row[2] ?? null,
                            'lastname' => $row[3] ?? null,
                            'firstname' => $row[4] ?? null,
                            'middlename' => $row[5] ?? null,
                            'email' => $row[6] ?? null,
                            'contact_number' => $row[7] ?? null,
                        ];
                        
                        // Check if record exists by any of the unique fields
                        $existingRecord = DB::table('csv')
                            ->where(function($query) use ($data) {
                                $query->where('student_number', $data['student_number'])
                                      ->orWhere('application_number', $data['application_number'])
                                      ->orWhere('email', $data['email'])
                                      ->orWhere('contact_number', $data['contact_number']);
                            })
                            ->first();
                        
                        if ($existingRecord) {
                            // Update existing record
                            DB::table('csv')
                                ->where('id', $existingRecord->id)
                                ->update($data);
                            $updatedRows++;
                        } else {
                            // Insert new record
                            DB::table('csv')->insert($data);
                            $insertedRows++;
                        }
                        
                    } catch (\Exception $e) {
                        $errors[] = "Row $totalRows: " . $e->getMessage();
                    }
                }
                
                fclose($handle);
                
                // Log the upload to database
                $uploadLogId = DB::table('csv_uploads')->insertGetId([
                    'admin_id' => Auth::guard('admin')->id() ?? null,
                    'file_name' => $file->getClientOriginalName(),
                    'stored_name' => $fileName,
                    'file_path' => $filePath,
                    'total_records' => $totalRows,
                    'inserted_records' => $insertedRows,
                    'updated_records' => $updatedRows,
                    'failed_records' => count($errors),
                    'status' => count($errors) == 0 ? 'success' : ($insertedRows + $updatedRows > 0 ? 'partial' : 'failed'),
                    'error_log' => !empty($errors) ? json_encode(array_slice($errors, 0, 10)) : null,
                    'created_at' => now(),
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => "CSV processed successfully. Total: $totalRows rows, Inserted: $insertedRows, Updated: $updatedRows",
                    'totalRows' => $totalRows,
                    'insertedRows' => $insertedRows,
                    'updatedRows' => $updatedRows,
                    'errors' => $errors,
                    'uploadId' => $uploadLogId
                ]);
                
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not open CSV file'
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getRecentUploads()
    {
        try {
            $uploads = DB::table('csv_uploads')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($upload) {
                    return [
                        'id' => $upload->id,
                        'file_name' => $upload->file_name,
                        'stored_name' => $upload->stored_name,
                        'date' => date('M d, Y H:i', strtotime($upload->created_at)),
                        'records' => $upload->inserted_records + $upload->updated_records,
                        'total' => $upload->total_records,
                        'status' => $upload->status,
                        'download_url' => Storage::url($upload->file_path),
                        'details' => "Total: {$upload->total_records}, Inserted: {$upload->inserted_records}, Updated: {$upload->updated_records}, Failed: {$upload->failed_records}"
                    ];
                });
            
            return response()->json([
                'success' => true,
                'uploads' => $uploads
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load uploads: ' . $e->getMessage()
            ], 500);
        }
    }

    // Download uploaded CSV file
    public function downloadCSV($id)
    {
        try {
            $upload = DB::table('csv_uploads')->find($id);
            
            if (!$upload) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }
            
            // Check if file exists
            if (!Storage::disk('public')->exists($upload->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File has been deleted or does not exist'
                ], 404);
            }
            
            // Get the file path
            $filePath = storage_path('app/public/' . $upload->file_path);
            
            // Return file download
            return response()->download($filePath, $upload->file_name);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Download failed: ' . $e->getMessage()
            ], 500);
        }
    }


    // Get upload details
    public function getUploadDetails($id)
    {
        try {
            $upload = DB::table('csv_uploads')->find($id);
            
            if (!$upload) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload record not found'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'upload' => [
                    'file_name' => $upload->file_name,
                    'date' => date('M d, Y H:i:s', strtotime($upload->created_at)),
                    'total_records' => $upload->total_records,
                    'inserted_records' => $upload->inserted_records,
                    'updated_records' => $upload->updated_records,
                    'failed_records' => $upload->failed_records,
                    'status' => $upload->status,
                    'error_log' => $upload->error_log ? json_decode($upload->error_log) : []
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteUpload($id)
    {
        try {
            $upload = DB::table('csv_uploads')->find($id);
            
            if (!$upload) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload record not found'
                ], 404);
            }
            
            // Delete the stored file
            if (Storage::disk('public')->exists($upload->file_path)) {
                Storage::disk('public')->delete($upload->file_path);
            }
            
            // Delete the upload record
            DB::table('csv_uploads')->where('id', $id)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Upload record deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadCSVTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_import_template.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fwrite($file, "\xEF\xBB\xBF");
            
            // Write headers
            fputcsv($file, [
                'student_number',
                'application_number', 
                'preffered_program',
                'lastname',
                'firstname',
                'middlename',
                'email',
                'contact_number'
            ]);
            
            // Write sample data
            fputcsv($file, [
                '2020-30617',
                'APP-2024-001',
                'BSIT',
                'Donquixote',
                'Doflamingo',
                'Doffy',
                'student@example.com',
                '09464930679'
            ]);
            
            // Write another sample
            fputcsv($file, [
                '2020-30618',
                'APP-2024-002',
                'BSCS',
                'Smith',
                'John',
                'Michael',
                'john.smith@example.com',
                '09123456789'
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    public function recentCSVUploads()
    {
        try {
            // Get recent uploads from csv_uploads table
            $recentUploads = DB::table('csv_uploads')
                ->join('admin', 'csv_uploads.admin_id', '=', 'admin.admin_id')
                ->select(
                    'csv_uploads.*',
                    'admin.email as admin_email'
                )
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($upload) {
                    return [
                        'id' => $upload->id,
                        'file_name' => $upload->original_filename,
                        'stored_name' => $upload->stored_filename,
                        'admin' => $upload->admin_email,
                        'date' => date('M d, Y H:i', strtotime($upload->created_at)),
                        'records' => $upload->successful_rows,
                        'total' => $upload->total_rows,
                        'status' => $upload->successful_rows > 0 ? 
                                   ($upload->failed_rows == 0 ? 'success' : 'partial') : 
                                   'failed',
                        'download_url' => Storage::url($upload->file_path),
                        'details' => "Total: {$upload->total_rows}, Success: {$upload->successful_rows}, Duplicates: {$upload->duplicate_rows}, Failed: {$upload->failed_rows}"
                    ];
                });
            
            return response()->json([
                'success' => true,
                'uploads' => $recentUploads
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'uploads' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    public function viewCSVData()
    {
        try {
            $csvData = DB::table('csv')
                ->select('*')
                ->orderBy('id', 'asc')
                ->limit(100)
                ->get();
            
            $totalCount = DB::table('csv')->count();
            
            return view('admin.csv-data', [
                'csvData' => $csvData,
                'totalCount' => $totalCount
            ]);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error loading CSV data: ' . $e->getMessage());
        }
    }

    public function deleteCSV($id)
    {
        try {
            $upload = DB::table('csv_uploads')->where('id', $id)->first();
            
            if (!$upload) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload record not found'
                ], 404);
            }
            
            // Delete file from storage
            if (Storage::disk('public')->exists($upload->file_path)) {
                Storage::disk('public')->delete($upload->file_path);
            }
            
            // Delete record from database
            DB::table('csv_uploads')->where('id', $id)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'CSV upload record deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting CSV upload: ' . $e->getMessage()
            ], 500);
        }
    }

    
}
