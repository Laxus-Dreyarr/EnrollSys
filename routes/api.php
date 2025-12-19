<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\GradeApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public API routes (no auth required for these)
// Route::post('/api/generate-key', [GradeApiController::class, 'generateApiKey']);
// Route::get('/api/health', [GradeApiController::class, 'healthCheck']);
Route::get('/health', [GradeApiController::class, 'healthCheck']);
Route::get('/failed-grades', [GradeApiController::class, 'getFailedGrades']);

// Route::get('/failed-grades', [GradeApiController::class, 'getFailedGrades']);
//     Route::get('/retention-alerts', [GradeApiController::class, 'getRetentionAlerts']);
//     Route::get('/student/{id}/grades', [GradeApiController::class, 'getStudentGrades']);
//     Route::get('/inc-grades', [GradeApiController::class, 'getIncGrades']);

// Protected API routes
Route::middleware(['cors', 'api.key'])->group(function () {
    Route::get('/failed-grades', [GradeApiController::class, 'getFailedGrades']);
    Route::get('/retention-alerts', [GradeApiController::class, 'getRetentionAlerts']);
    Route::get('/student/{id}/grades', [GradeApiController::class, 'getStudentGrades']);
    Route::get('/inc-grades', [GradeApiController::class, 'getIncGrades']);

    Route::get('/all-grades', [GradeApiController::class, 'getAllGrades']);
    Route::get('/student/{id}/complete-grades', [GradeApiController::class, 'getStudentCompleteGrades']);
});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::post('register', [AuthController::class, 'register']);
// Route::post('login', [AuthController::class, 'login']);
// Route::middleware('auth:api')->get('user', [AuthController::class, 'getUser']);
// Route::middleware('auth:api')->post('logout', [AuthController::class, 'logout']);
