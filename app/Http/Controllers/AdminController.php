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
                
            case 'get_prerequisites':
                $prerequisites = Subject::where('is_active', 1)
                    ->select('id', 'code', 'name')
                    ->orderBy('code')
                    ->get();
                return response()->json(['success' => true, 'prerequisites' => $prerequisites]);
                
            case 'get_subjects':
                $subjects = Subject::with(['schedules', 'prerequisites'])
                    ->where('is_active', 1)
                    ->orderBy('year_level')
                    ->get()
                    ->map(function($subject) {
                        return [
                            'id' => $subject->id,
                            'code' => $subject->code,
                            'name' => $subject->name,
                            'units' => $subject->units,
                            'year_level' => $subject->year_level,
                            'semester' => $subject->semester,
                            'schedules' => $subject->schedules,
                            'prerequisites' => $subject->prerequisites
                        ];
                    });
                return response()->json(['success' => true, 'subjects' => $subjects]);
                
            case 'get_subject':
                $subjectId = $request->input('subject_id');
                $subject = Subject::with(['schedules', 'prerequisites'])
                    ->where('id', $subjectId)
                    ->first();
                    
                if (!$subject) {
                    return response()->json(['success' => false, 'message' => 'Subject not found']);
                }
                
                return response()->json([
                    'success' => true, 
                    'subject' => [
                        'id' => $subject->id,
                        'code' => $subject->code,
                        'name' => $subject->name,
                        'description' => $subject->description,
                        'units' => $subject->units,
                        'max_students' => $subject->max_students,
                        'year_level' => $subject->year_level,
                        'semester' => $subject->semester,
                        'schedules' => $subject->schedules,
                        'prerequisites' => $subject->prerequisites->map(function($prereq) {
                            return [
                                'id' => $prereq->id,
                                'code' => $prereq->code,
                                'name' => $prereq->name
                            ];
                        })
                    ]
                ]);
                
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
                
            default:
                return response()->json(['success' => false, 'message' => 'Invalid action']);
        }
    }

    public function createSubject(Request $request)
    {
        DB::beginTransaction();
        
        try {
            // Check if subject code already exists
            if (Subject::where('code', $request->code)->exists()) {
                return response()->json(['success' => false, 'message' => 'Subject code already exists']);
            }

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

            // Check for schedule conflicts
            $conflicts = $this->checkScheduleConflicts($uniqueSchedules);
            if (!empty($conflicts)) {
                DB::rollBack();
                return response()->json([
                    'success' => false, 
                    'message' => 'Schedule conflicts detected',
                    'conflicts' => $conflicts,
                    'available_slots' => $this->getAvailableTimeSlots($uniqueSchedules)
                ]);
            }

            // Create subject
            $subject = Subject::create([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description ?? '',
                'units' => $request->units,
                'year_level' => $request->year_level,
                'semester' => $request->semester,
                'max_students' => $request->max_students,
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

            // Add schedules
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

            // $clientInfo = $this->getClientDeviceInfoWithRequest($request);
            $clientInfo = $this->collectClientInformation($request);
            // $ipaddress = $this->getClientRealIp();
            $ipaddress = $this->getClientDeviceInfo();
            
            $admin = Auth::guard('admin')->user();

            date_default_timezone_set('Asia/Manila');
            $todays_date=date("Y-m-d h:i:sa");
            $today=strtotime($todays_date);
            $date=date("Y-m-d h:i:sa", $today);

            //Save to AuditLogs!
            $audit = new AuditLog();
            $audit->user_id = $admin->admin_id;
            $audit->action = 'New Subject Created: ' .$request->name .'('.$request->code.')';
            $audit->details = '' .$clientInfo['operating_system'] .'/' .$clientInfo['device_type'] .'/' .$clientInfo['user_agent'];
            $audit->ip_address = $ipaddress['ip_address'];
            $audit->date = $date;
            $audit->access_by = '107568';

            if (!$audit->save()) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Failed to create subject: Unable to log audit trail']);
            }



            DB::commit();
            return response()->json(['success' => true, 'message' => 'Subject created successfully']);
            
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
            if (Subject::where('code', $request->code)->where('id', '!=', $request->subject_id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Subject code already exists']);
            }

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

            // Check for schedule conflicts (excluding current subject's schedules)
            $conflicts = $this->checkScheduleConflicts($uniqueSchedules, $subject->id);
            if (!empty($conflicts)) {
                DB::rollBack();
                return response()->json([
                    'success' => false, 
                    'message' => 'Schedule conflicts detected',
                    'conflicts' => $conflicts,
                    'available_slots' => $this->getAvailableTimeSlots($uniqueSchedules)
                ]);
            }

            // ... rest of your update method remains the same
            // Update subject
            $subject->update([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description ?? '',
                'units' => $request->units,
                'year_level' => $request->year_level,
                'semester' => $request->semester,
                'max_students' => $request->max_students,
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

            // Update schedules
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
            return response()->json(['success' => true, 'message' => 'Subject updated successfully']);
            
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
            $logs = AuditLog::with(['user' => function($query) {
                    $query->select('id', 'firstname', 'lastname');
                }])
                ->orderBy('timestamp', 'desc')
                ->limit(100)
                ->get()
                ->map(function($log) {
                    return [
                        'timestamp' => $log->timestamp,
                        'action' => $log->action,
                        'details' => $log->details,
                        'ip_address' => $log->ip_address,
                        'firstname' => $log->user ? $log->user->firstname : null,
                        'lastname' => $log->user ? $log->user->lastname : null,
                        'user_id' => $log->user_id
                    ];
                });

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
        return redirect('/welcome_admin');
    }


    //EDIT
    


    
}
