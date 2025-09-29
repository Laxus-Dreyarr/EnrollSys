<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Mail\PasswordResetOtp;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\SubjectPrerequisite;
use App\Models\SubjectSchedule;
use App\Models\Passkey;
use App\Models\AuditLog;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Services\AdminService;
use Illuminate\Support\Facades\DB;


class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
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
                    ->orderBy('code')
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

            // Check for duplicate schedules - NO json_decode needed
            $schedules = $request->schedules; // ✅ Already an array
            $uniqueSchedules = [];
            foreach ($schedules as $schedule) {
                $key = $schedule['section'] . '-' . $schedule['day'] . '-' . $schedule['start_time'] . '-' . $schedule['end_time'];
                if (!isset($uniqueSchedules[$key])) {
                    $uniqueSchedules[$key] = $schedule;
                } else {
                    // Duplicate schedule found
                    DB::rollBack();
                    return response()->json(1); // This matches your JavaScript check for response == 1
                }
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

            // Add prerequisites - NO json_decode needed
            if (!empty($request->prerequisites)) {
                $prerequisites = $request->prerequisites; // ✅ Already an array
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

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Subject created successfully']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Subject creation failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create subject: ' . $e->getMessage()]);
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
            if (Subject::where('code', $request->code)->where('id', '!=', $request->subject_id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Subject code already exists']);
            }

            // Check if subject name exists (excluding current subject)
            if (Subject::where('name', $request->name)->where('id', '!=', $request->subject_id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Subject name already exists']);
            }

            // Check for duplicate schedules - NO json_decode needed
            $schedules = $request->schedules; // ✅ Already an array
            $uniqueSchedules = [];
            foreach ($schedules as $schedule) {
                $key = $schedule['section'] . '-' . $schedule['day'] . '-' . $schedule['start_time'] . '-' . $schedule['end_time'];
                if (!isset($uniqueSchedules[$key])) {
                    $uniqueSchedules[$key] = $schedule;
                } else {
                    // Duplicate schedule found
                    DB::rollBack();
                    return response()->json(1);
                }
            }

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

            // Update prerequisites - NO json_decode needed
            SubjectPrerequisite::where('subject_id', $subject->id)->delete();
            if (!empty($request->prerequisites)) {
                $prerequisites = $request->prerequisites; // ✅ Already an array
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
            $subject->update(['is_active' => 0]);

            return response()->json(['success' => true, 'message' => 'Subject deleted successfully']);
            
        } catch (\Exception $e) {
            Log::error('Subject deletion failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete subject: ' . $e->getMessage()]);
        }
    }


    public function generatePasskey(Request $request)
    {
        try {
            $passkey = Str::random(15);
            
            Passkey::create([
                'passkey' => $passkey,
                'email3' => $request->email,
                'created_by' => Auth::guard('admin')->id(),
                'expiration_date' => now()->addDays(7),
                'user_type' => $request->user_type
            ]);

            // Here you would typically send the email with the passkey
            // For now, we'll just return success
            
            return response()->json([
                'success' => true, 
                'message' => 'Passkey generated successfully',
                'passkey' => $passkey
            ]);
            
        } catch (\Exception $e) {
            Log::error('Passkey generation failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate passkey']);
        }
    }

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


    


    
}
