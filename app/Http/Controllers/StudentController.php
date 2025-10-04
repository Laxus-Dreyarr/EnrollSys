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
use App\Mail\RegistrationVerification;
use Illuminate\Support\Facades\Log;
use App\Mail\PasswordResetOtp;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function register(Request $request)
    {
        $action = $request->input('action');

        switch ($action) {
            case 'check_email':
                return $this->checkEmail($request);
            case 'send_verification':
                return $this->sendVerificationCode($request);
            case 'verify_code':
                return $this->verifyCodeAndRegister($request);
            case 'register':
                return $this->processRegistration($request);
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
            
            // Store registration data in cache for 10 minutes
            // $cacheKey = 'registration_' . $request->email;

            Cache::put('registration_' . $request->email, [
                'otp' => $verificationCode,
                'password' => Hash::make($request->password),
                'email' => $request->email
            ], now()->addMinutes(10));

            session(['registration_email' => $request->email]);

            // Send verification email
            try {
                Mail::to($request->email)->send(new RegistrationVerification($verificationCode));

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
                    'debug_code' => $verificationCode // Remove this in production
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
    //             Mail::to($request->email)->send(new RegistrationVerification(
    //                 $verificationCode,
    //                 $request->givenName . ' ' . $request->lastName
    //             ));

    //             Log::info('Verification code sent to: ' . $request->email);

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

    private function verifyCodeAndRegister(Request $request)
    {
        try {
            $email = $request->input('email');
            $code = $request->input('code');
            
            $cacheKey = 'registration_' . $email;
            $registrationData = Cache::get($cacheKey);

            if (!$registrationData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification session expired. Please register again.'
                ]);
            }

            // Check attempts
            if ($registrationData['attempts'] >= 3) {
                Cache::forget($cacheKey);
                return response()->json([
                    'success' => false,
                    'message' => 'Too many failed attempts. Please register again.'
                ]);
            }

            // Verify code
            if ($registrationData['verification_code'] !== $code) {
                $registrationData['attempts']++;
                Cache::put($cacheKey, $registrationData, 600);
                
                $remainingAttempts = 3 - $registrationData['attempts'];
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification code. ' . $remainingAttempts . ' attempts remaining.'
                ]);
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
                throw new \Exception('Failed to save user record');
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
                throw new \Exception('Failed to save user info record');
            }

            DB::commit();

            // Clear the cache
            Cache::forget($cacheKey);

            Log::info('Student registered successfully after verification', [
                'student_id' => $studentId,
                'email' => $registrationData['email']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Welcome to EnrollSys.',
                'data' => [
                    'student_id' => $studentId,
                    'name' => $registrationData['givenName'] . ' ' . $registrationData['lastName'],
                    'email' => $registrationData['email']
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Verification and registration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ]);
        }
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
}