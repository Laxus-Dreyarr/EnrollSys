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

            $y = Passkey::where('passkey', $request->passkey)->exists();

            if(!$y) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Passkey'
                ]);
            }
            $y = null;

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

            // Invalidate the used passkey
            Passkey::where('passkey', $request->input('email'))->delete();
            
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

    
}// End of Class
