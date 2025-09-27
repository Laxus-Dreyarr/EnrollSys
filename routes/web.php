<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});





// New
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

Route::get('/ad-dashboard', [AdminController::class, 'dashboard'])->middleware('admin.auth');




