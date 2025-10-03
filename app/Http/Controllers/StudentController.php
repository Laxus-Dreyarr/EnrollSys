<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    public function register(Request $request)
    {
        $action = $request->input('action');

        switch ($action) {
            case 'check_email':
                return $this->checkEmail($request);

            case 'register':
                return $this->processRegistration($request);

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action'
                ]);
        }
    }

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
        // First, let's log what we're receiving
        Log::info('Registration attempt', [
            'email' => $request->email,
            'givenName' => $request->givenName,
            'lastName' => $request->lastName
        ]);

        // Simplified validation - remove complex password validation for now
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
            'givenName.required' => 'Given name is required.',
            'givenName.min' => 'Given name must be at least 2 characters long.',
            'givenName.regex' => 'Given name must contain only letters, spaces, hyphens, and apostrophes.',

            'lastName.required' => 'Last name is required.',
            'lastName.min' => 'Last name must be at least 2 characters long.',
            'lastName.regex' => 'Last name must contain only letters, spaces, hyphens, and apostrophes.',

            'middleName.min' => 'Middle name must be at least 2 characters long.',
            'middleName.regex' => 'Middle name must contain only letters, spaces, hyphens, and apostrophes.',

            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.regex' => 'Please enter a valid EVSUmail address (username@evsu.edu.ph).',

            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.'
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', ['errors' => $validator->errors()->all()]);
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

        DB::beginTransaction();

        try {
            // Generate a better unique student ID
            $studentId = $this->generateUniqueStudentId();

            $user_id = rand(100000, 999999);
            date_default_timezone_set('Asia/Manila');
            $todays_date = date("Y-m-d h:i:sa");
            $today = strtotime($todays_date);
            $currentDate = date("Y-m-d h:i:sa", $today);


            Log::info('Creating user with ID: ' . $studentId);

            // Create user account
            $user = new User();
            $user->id = $studentId;
            $user->email2 = $request->email;
            $user->password = Hash::make($request->password);
            $user->profile = 'default.png';
            $user->date_created = $currentDate;
            $user->user_type = 'student';
            $user->is_active = 1;
            $user->last_login = null;

            // Save user and check if successful
            if (!$user->save()) {
                throw new \Exception('Failed to save user record');
            }

            Log::info('User saved successfully, creating user info');

            // Create user info
            $userInfo = new UserInfo();
            $userInfo->user_id = $studentId;
            $userInfo->firstname = ucfirst(strtolower($request->givenName));
            $userInfo->lastname = ucfirst(strtolower($request->lastName));
            $userInfo->middlename = $request->middleName ? ucfirst(strtolower($request->middleName)) : null;
            $userInfo->birthdate = null;
            $userInfo->age = null;
            $userInfo->address = null;

            // Save user info and check if successful
            if (!$userInfo->save()) {
                throw new \Exception('Failed to save user info record');
            }

            DB::commit();

            Log::info('Student registered successfully', [
                'student_id' => $studentId,
                'email' => $request->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Welcome to EnrollSys.',
                'data' => [
                    'student_id' => $studentId,
                    'name' => $request->givenName . ' ' . $request->lastName,
                    'email' => $request->email
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the actual error for debugging
            Log::error('Registration error: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage() // Show actual error for debugging
            ]);
        }
    }

    /**
     * Generate a unique student ID
     */
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
}
