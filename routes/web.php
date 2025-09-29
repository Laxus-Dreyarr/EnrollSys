<?php
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;


// Public routes
Route::get('/', function () {
    return view('welcome');
});

Route::get('/welcome_admin', function () {
    return view('welcome_admin');
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
        Route::post('/get-prerequisites', [AdminController::class, 'getPrerequisites']);
        Route::post('/get-subjects', [AdminController::class, 'getSubjects']);
        Route::post('/get-subject/{id}', [AdminController::class, 'getSubject']);
        Route::post('/create-subject', [AdminController::class, 'createSubject']);
        Route::post('/update-subject/{id}', [AdminController::class, 'updateSubject']);
        Route::post('/delete-subject/{id}', [AdminController::class, 'deleteSubject']);
        Route::post('/generate-passkey', [AdminController::class, 'generatePasskey']);
        Route::post('/get-audit-logs', [AdminController::class, 'getAuditLogs']);
    });
});