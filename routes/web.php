<?php
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;


// Public routes
Route::get('/', function () {
    return view('welcome');
});

Route::get('/welcome_admin', function () {
    return view('welcome_admin');
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
