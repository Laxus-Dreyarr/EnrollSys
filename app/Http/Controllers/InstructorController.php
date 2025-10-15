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

    public function register(Request $request){

        $action = $request->input('action');

        switch ($action) {
            case 'check_passkey':
                return $this->checkPasskey($request);

            case 'registering':
                return $this->registerInstructor($request);
        }

    }


    private function checkPasskey(Request $request) 
    {
        try {
            if(!$request->has('passkey')){
                return response()->json(['success' => false, 'message' => 'Passkey is required']);
            }

            $passkey = $request->input('passkey');
            $exists = Passkey::where('passkey', $passkey)->exists();

            return response()->json([
                'exists' => $exists,
                'message' => $exists ? 'Passkey is available' : 'This passkey is already registered!'
            ]);

        } catch (\Exception $e) {
            Log::error('Passkey check error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error checking passkey']);
        }

    }

    private function registerInstructor(Request $requset) {
        // $validator = Validator::make($requset->all(), [
        //     'givenName' => 'required|string|max:10',
        //     'lastName' => 'required|string|max:255',
        //     'middleName' => 'nullable|string|max:255',
        //     'email' => 'required|max:255',
        //     'password' => 'required|string|min:8|confirmed',
        //     'repeatPassword' => 'required|string|min:8'
        // ], [
        //     'givenName.required' => 'Given Name is required.',
        //     'givenName.string' => 'Given Name must be a valid text.',
        //     'givenName.max' => 'Given Name cannot exceed 10 characters.',
        //     'lastName.required' => 'Last Name is required.',
        //     'lastName.string' => 'Last Name must be a valid text.',
        //     'lastName.max' => 'Last Name cannot exceed 255 characters.',
        //     'email.required' => 'Passkey is required.',
        //     'email.max' => 'Passkey cannot exceed 255 characters.',
        //     'password.required' => 'Password is required.',
        //     'password.string' => 'Password must be a valid text.',
        //     'password.min' => 'Password must be at least 8 characters.',
        //     'password.confirmed' => 'Password confirmation does not match.',
        //     'repeatPassword.required' => 'Repeat Password is required.',
        //     'repeatPassword.string' => 'Repeat Password must be a valid text.',
        //     'repeatPassword.min' => 'Repeat Password must be at least 8 characters.',
        //     'middleName.string' => 'Middle Name must be a valid text.',
        //     'middleName.max' => 'Middle Name cannot exceed 255 characters.',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        // }
        

        // Check if passwords match
        if ($requset->input('password') !== $requset->input('repeatPassword')) {
            return response()->json(['success' => false, 'message' => 'Passwords do not match']);
        }

        // Validate EVSU email domain
        // $email = $requset->input('email');
        // if (!preg_match('/^[a-zA-Z0-9._%+-]+@evsu\.edu\.ph$/', $email)) {
        //     return response()->json(['success' => false, 'message' => 'Email must be a valid EVSU email address']);
        // }

        // try {
        //     // Create user
        //     $user = User::create([
        //         'name' => $requset->input('givenName') . ' ' . $requset->input('lastName'),
        //         'email' => $email,
        //         'password' => Hash::make($requset->input('password')),
        //         'role' => 'instructor',
        //         'status' => 'active'
        //     ]);

        //     // Create user info
        //     UserInfo::create([
        //         'user_id' => $user->id,
        //         'given_name' => $requset->input('givenName'),
        //         'last_name' => $requset->input('lastName'),
        //         'middle_name' => $requset->input('middleName'),
        //         // Add other fields as necessary
        //     ]);

        //     // Invalidate the used passkey
        //     Passkey::where('passkey', $requset->input('passkey'))->delete();

        //     // Log the registration action
        //     AuditLog::create([
        //         'user_id' => $user->id,
        //         'action' => 'Instructor Registration',
        //         'ip_address' => $requset->ip(),
        //         'user_agent' => $requset->header('User-Agent'),
        //         'created_at' => now(),
        //     ]);     
        //     return response()->json(['success' => true, 'message' => 'Registration successful. You can now log in.']);
        // } catch (\Exception $e) {
        //     Log::error('Instructor registration error: ' . $e->getMessage());
        //     return response()->json(['success' => false, 'message' => 'Error during registration. Please try again later.']);
        // }
    }

    
}// End of Class
