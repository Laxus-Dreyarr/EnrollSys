<?php
use App\Http\Controllers\AdminController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;


// Public routes
Route::get('/', function () {
    return view('welcome');
});

Route::get('/welcome_admin', function () {
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

Route::get('/forgot_acc_student', function (){
    return view('student.student_forgot'); 
});

Route::post('/log', [AdminController::class, 'login']);
Route::post('/forgot', [AdminController::class, 'forgotPassword']);
Route::get('/reset_admin_password', function () {
    $email = session('password_reset_email');
    $resetData = $email ? Cache::get('password_reset_' . $email) : null;
    if (!$resetData) {
        return redirect('/welcome_admin');
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

    return redirect('/welcome_admin');
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



// Student Routes
Route::prefix('/exe')->group(function (){
    Route::post('/student', [StudentController::class, 'register']);
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

Route::get('/instructor', function () {
    return view('instructor.index');
});
