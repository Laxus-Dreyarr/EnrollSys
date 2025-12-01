<?php
use App\Http\Controllers\AdminController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\OrgController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Http;


// Public routes
Route::get('/', function () {
    return view('welcome');
});


//Admin Routes
Route::get('/admin', function () {
    app(AdminController::class)->cleanupExpiredPasskeys();
    return view('welcome_admin');
});

// Sample
// Add this to web.php temporarily
Route::get('/test-timezone', function() {
    $manilaTime = now()->setTimezone('Asia/Manila');
    $utcTime = now()->setTimezone('UTC');
    
    echo "Manila Time: " . $manilaTime->toDateTimeString() . "<br>";
    echo "UTC Time: " . $utcTime->toDateTimeString() . "<br>";
    echo "App Default: " . now()->toDateTimeString() . "<br>";
    
    // Test creating a passkey that expires in 1 minute
    $expiration = $manilaTime->copy()->addMinute();
    echo "Test Expiration: " . $expiration->toDateTimeString() . "<br>";
    
    return "Timezone test completed";
});
//End Sample

Route::post('/log', [AdminController::class, 'login']);
Route::post('/forgot', [AdminController::class, 'forgotPassword']);
Route::get('/reset_admin_password', function () {
    $email = session('password_reset_email');
    $resetData = $email ? Cache::get('password_reset_' . $email) : null;
    if (!$resetData) {
        return redirect('/admin');
    }

    return view('index-admin-reset', [
        'email' => $email,
        'resetData' => $resetData
    ]);
});
Route::post('/reset', [AdminController::class, 'resetPassword']);

// Admin protected routes
Route::middleware(['admin.auth'])->group(function () {
    Route::get('/ad-dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    
    // AJAX endpoints
    Route::prefix('admin/ajax')->group(function () {
        Route::post('/get-stats', [AdminController::class, 'getStats']);
        // Route::post('/get-prerequisites', [AdminController::class, 'getPrerequisites']);
        // Route::post('/get-subjects', [AdminController::class, 'getSubjects']);
        // Route::post('/get-subject/{id}', [AdminController::class, 'getSubject']);
        // Route::post('/create-subject', [AdminController::class, 'createSubject']);
        // Route::post('/update-subject/{id}', [AdminController::class, 'updateSubject']);
        // Route::post('/delete-subject/{id}', [AdminController::class, 'deleteSubject']);
        // Route::post('/generate-passkey', [AdminController::class, 'generatePasskey']);
        // Route::post('/get-audit-logs', [AdminController::class, 'getAuditLogs']);
    });
});

//Clear Admin Forgot Cache!
Route::get('/clearAdmin', function () {
    $email = session('password_reset_email');
    Cache::forget('password_reset_' . $email);

    return redirect('/admin');
});

//Instructor Routes
Route::prefix('/exe')->group(function (){
    Route::post('/instructor', [InstructorController::class, 'register']);
});


Route::get('/instructor_forgot2', function (){
    return view('instructor.instructor_forgot'); 
});


Route::get('/instructor_verify_otp', function () {
    $email = session('rpi');
    
    if (!$email) {
        // Redirect if no session exists (user accessed directly)
        return redirect('/instructor_forgot2')->with('error', 'Session expired. Please try again.');
    }
    
    $registerData = Cache::get('if_' . $email);
    
    if (!$registerData) {
        // Redirect if cache expired
       return redirect('/instructor_forgot2')->with('error', 'OTP expired. Please request a new one.');
    }
    
    return view('instructor.instructor_verify', [
        'email' => $email,
        'registerData' => $registerData
    ]);
});
//Clear Instructor Registration Cache!
Route::get('/clear2', function () {
    $email = session('rpi');
    Cache::forget('if_' . $email);

    return redirect('/instructor');
});
Route::get('/instructor', function () {
    return view('instructor.index');
});
Route::middleware(['instructor.auth'])->group(function () {
    Route::get('/instructor-dashboard', [InstructorController::class, 'dashboard'])->name('instructor.dashboard');
    Route::post('/instructor/ungraded-students', [InstructorController::class, 'getUngradedStudents'])->name('instructor.ungraded-students');
    Route::post('/instructor/save-grade', [InstructorController::class, 'saveGrade'])->name('instructor.save-grade');

    Route::post('/instructor/enrollment-requests', [InstructorController::class, 'getEnrollmentRequests'])->name('instructor.enrollment-requests');
    Route::post('/instructor/approve-enrollment', [InstructorController::class, 'approveEnrollment'])->name('instructor.approve-enrollment');
    Route::post('/instructor/reject-enrollment', [InstructorController::class, 'rejectEnrollment'])->name('instructor.reject-enrollment');

    // Student subjects history
    Route::get('/instructor/student-subjects-history/{studentId}', 
    [InstructorController::class, 'getStudentSubjectsHistory'])->name('instructor.student.subjects.history');
});


// Student Routes
Route::prefix('/exe')->group(function (){
    Route::post('/student', [StudentController::class, 'register']);
});

Route::get('/forgot_acc_student', function (){
    return view('student.student_forgot'); 
});

Route::get('/register_student_account', function () {
    $email = session('registration_email');
    $registerData = $email ? Cache::get('registration_' . $email) : null;
    if (!$registerData) {
        return redirect('/');
    }

    return view('student-verify', [
        'email' => $email,
        'registerData' => $registerData
    ]);
});

//Clear Registration Cache!
Route::get('/clear', function () {
    $email = session('registration_email');
    Cache::forget('registration_' . $email);

    return redirect('/');
});
// Route::get('/clear', [StudentController::class, 'clearCache']);

Route::get('/register_reset_password', function () {
    $email = session('reset_pass');
    $registerData = $email ? Cache::get('studentForgot_' . $email) : null;
    if (!$registerData) {
        return redirect('/');
    }

    return view('student.student_reset', [
        'email' => $email,
        'registerData' => $registerData
    ]);
});

//Clear Student Reset Cache!
Route::get('/clear_r', function () {
    $email = session('reset_pass');
    Cache::forget('studentForgot_' . $email);

    return redirect('/');
});

Route::middleware(['student.auth'])->group(function () {
    Route::get('/student-dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
    // Route::post('/logout', [StudentController::class, 'logout'])->name('admin.logout');
    
    // AJAX endpoints
    // Route::prefix('admin/ajax')->group(function () {
    //     Route::post('/get-stats', [StudentController::class, 'getStats']);
    // });
    // Enrollment routes
    Route::get('/student/enrollment/subjects', [StudentController::class, 'getEnrollmentSubjects']);
    Route::post('/student/enrollment/enroll', [StudentController::class, 'enrollSubjects']);

    Route::get('/student/enrollment/check-past-subjects', [StudentController::class, 'checkPastSubjects']);
    Route::get('/student/enrollment/all-subjects', [StudentController::class, 'getAllSubjects']);
    Route::post('/student/enrollment/save-past-subjects', [StudentController::class, 'savePastSubjects']);

    Route::get('/student/enrollment/irregular-subjects', [StudentController::class, 'getIrregularEnrollmentSubjects']);
    Route::post('/student/enrollment/enroll-irregular', [StudentController::class, 'enrollIrregularSubjects']);

    // New route for fetching curricula
    Route::get('/student/curricula', [StudentController::class, 'getAvailableCurricula']);

    Route::post('/student/enrollment/final-enroll', [StudentController::class, 'finalEnroll']);

    Route::get('/student/enrollment/check-existing-request', [StudentController::class, 'checkExistingEnrollmentRequest']);

    Route::get('/student/subject/{id}/details', [StudentController::class, 'getSubjectDetails'])->name('student.subject.details');

    Route::post('/exe/student_status', [StudentController::class, 'student_status']);
});

// Debug route - remove after testing
Route::get('/debug-student-data', function() {
    $user = Auth::guard('student')->user();
    if (!$user) {
        return response()->json(['error' => 'Not authenticated']);
    }

    $student = $user->user_information->student;
    
    $data = [
        'user' => [
            'id' => $user->id,
            'email' => $user->email2,
        ],
        'user_info' => [
            'id' => $user->user_information->id,
            'firstname' => $user->user_information->firstname,
        ],
        'student' => [
            'id' => $student->id,
            'student_id' => $student->student_id,
            'id_no' => $student->id_no,
            'year_level' => $student->year_level,
            'is_regular' => $student->is_regular,
        ],
        'enrolled_sub_records' => DB::table('enrolled_sub')
            ->where('student_id', $student->id)
            ->get()->toArray(),
        'all_enrolled_sub' => DB::table('enrolled_sub')->get()->toArray() // For comparison
    ];

    return response()->json($data);
});


//Org Routes
Route::middleware(['org.auth'])->group(function () {
    Route::get('/org-dashboard/data', [OrgController::class, 'getDashboardData'])->name('org.getDashboardData');

    Route::get('/org-dashboard', [OrgController::class, 'dashboard'])->name('org.dashboard');
    Route::get('/org-dashboard/data', [OrgController::class, 'getDashboardData'])->name('org.dashboard.data');
    Route::post('/org/verify-payment', [OrgController::class, 'verifyPayment'])->name('org.verify.payment');

    // Students Management
    Route::get('/org-dashboard/students', [OrgController::class, 'getStudentsData'])->name('org.students.data');

    // Verify docs and payment
    Route::get('/org/student-details/{studentId}', [OrgController::class, 'getStudentDetails']);
    Route::post('/org/approve-payment', [OrgController::class, 'approvePayment']);
    Route::post('/org/decline-payment', [OrgController::class, 'declinePayment']);

    // File serving routes for documents
    Route::get('/documents/{folder}/{filename}', function ($folder, $filename) {
        // Define allowed folders for security
        $allowedFolders = ['fhe', 'payment_receipts'];
        
        if (!in_array($folder, $allowedFolders)) {
            abort(404, 'Folder not allowed');
        }

        $path = storage_path("app/public/documents/{$folder}/{$filename}");
        
        Log::info("File access attempt:", [
            'folder' => $folder,
            'filename' => $filename,
            'full_path' => $path,
            'exists' => file_exists($path)
        ]);

        if (!file_exists($path)) {
            abort(404, 'File not found');
        }

        // Get file mime type
        $mime = mime_content_type($path);
        
        // Create response with proper headers
        $response = response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);

        return $response;
    })->where('filename', '.*')->name('documents.serve');

});
Route::get('/org', function () {
    return view('org.index');
});
Route::prefix('/exe')->group(function (){
    Route::post('/org', [OrgController::class, 'register']);
});
Route::get('/org_forgot', function (){
    return view('org.organization_forgot'); 
});
Route::get('/org_verify_otp', function () {
    $email = session('rpo');
    
    if (!$email) {
        // Redirect if no session exists (user accessed directly)
        return redirect('/org_forgot')->with('error', 'Session expired. Please try again.');
    }
    
    $registerData = Cache::get('of_' . $email);
    
    if (!$registerData) {
        // Redirect if cache expired
       return redirect('/org_forgot')->with('error', 'OTP expired. Please request a new one.');
    }
    
    return view('org.org_verify', [
        'email' => $email,
        'registerData' => $registerData
    ]);
});
//Clear Org Reset Password Cache!
Route::get('/clearOrg', function () {
    $email = session('rpo');
    Cache::forget('of_' . $email);

    return redirect('/org');
});

// //sample
// Route::get('/instructor/d', function () {
//     return view('instructor.dashboard.dashboard'); 
// });
