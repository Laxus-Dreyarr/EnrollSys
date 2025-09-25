<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
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

// Route::get('/index', [HomeController::class, 'index']);
// Route::get('/register', [HomeController::class, 'register']);
// // Route::post('/reg', [HomeController::class, 'form'])->name('x.form');
// Route::post('/reg', [HomeController::class, 'form']);
// // Route::get('/login', [HomeController::class, 'login']);
// Route::get('/login', [HomeController::class, 'login'])->name('login');
// Route::post('/log', [HomeController::class, 'log']);
// // Route::get('/dashboard', [HomeController::class, 'dashboard'])->middleware('auth');
// // Route::get('/dashboard', [HomeController::class, 'dashboard']);
// Route::get('/dashboard', [HomeController::class, 'dashboard'])->middleware('auth');

// // Route::post('/logout', [HomeController::class, 'logout'])->name('logout');
// Route::post('/logout', function () {
//     Auth::logout();
//     return redirect('/login')->with('success', 'You have been logged out.');
// })->name('logout');

// Route::middleware(['auth'])->group(function () {
//     Route::get('/dashboard', [HomeController::class, 'dashboard']);
//     // Add other protected routes here
// });


// New
Route::get('/welcome_admin', function () {
    return view('welcome_admin');
});

Route::post('/log', [AdminController::class, 'login']);

Route::post('/forgot', [AdminController::class, 'forgotPassword']);

// Route::get('///', function () {
//     return view('welcome_admin');
// })->middleware('auth');

