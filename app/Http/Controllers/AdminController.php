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
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Services\AdminService;


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

    // AJAX endpoints
    public function getStats(Request $request)
    {
        $stats = $this->getStatistics();
        return response()->json(['success' => true, 'stats' => $stats]);
    }

    public function getPrerequisites(Request $request)
    {
        $prerequisites = $this->adminService->getPrerequisiteOptions();
        return response()->json(['success' => true, 'prerequisites' => $prerequisites]);
    }

    public function getSubjects(Request $request)
    {
        $subjects = $this->adminService->getAllSubjectsWithSchedules();
        return response()->json(['success' => true, 'subjects' => $subjects]);
    }

    // public function getSubject(Request $request, $id)
    // {
    //     $subject = $this->adminService->getSubjectWithDetails($id);
        
    //     if (!$subject) {
    //         return response()->json(['success' => false, 'message' => 'Subject not found']);
    //     }

    //     return response()->json(['success' => true, 'subject' => $subject]);
    // }

     public function createSubject(Request $request)
    {
        $success = $this->adminService->createSubject($request->all());

        if ($success) {
            return response()->json(['success' => true, 'message' => 'Subject created successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to create subject']);
    }

    // public function updateSubject(Request $request, $id)
    // {
    //     $success = $this->adminService->updateSubject($id, $request->all());

    //     if ($success) {
    //         return response()->json(['success' => true, 'message' => 'Subject updated successfully']);
    //     }

    //     return response()->json(['success' => false, 'message' => 'Failed to update subject']);
    // }

    // public function deleteSubject(Request $request, $id)
    // {
    //     $success = $this->adminService->deleteSubject($id);

    //     if ($success) {
    //         return response()->json(['success' => true, 'message' => 'Subject deleted successfully']);
    //     }

    //     return response()->json(['success' => false, 'message' => 'Failed to delete subject']);
    // }

    // public function generatePasskey(Request $request)
    // {
    //     $validated = $request->validate([
    //         'email' => 'required|email',
    //         'user_type' => 'required|in:instructor,organization'
    //     ]);

    //     $passkey = $this->adminService->generatePasskey(
    //         $validated['email'],
    //         $validated['user_type'],
    //         Auth::guard('admin')->id()
    //     );

    //     if ($passkey) {
    //         return response()->json([
    //             'success' => true, 
    //             'message' => 'Passkey generated and sent',
    //             'passkey' => $passkey->passkey
    //         ]);
    //     }

    //     return response()->json(['success' => false, 'message' => 'Failed to generate passkey']);
    // }


    // public function getAuditLogs(Request $request)
    // {
    //     $logs = $this->adminService->getAuditLogs();
    //     return response()->json(['success' => true, 'logs' => $logs]);
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
