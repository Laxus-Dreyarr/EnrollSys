<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GradeApiController;
use App\Http\Controllers\Api\EnrollmentApiController;

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

// Public API routes (no API key required for these)
Route::get('/health', [GradeApiController::class, 'healthCheck']);
Route::post('/student/login', [AuthController::class, 'studentLogin']);

// Protected API routes (require API key)
Route::middleware(['cors', 'api.key'])->group(function () {
    // Student endpoints
    Route::get('/student/profile', [AuthController::class, 'getStudentProfile']);
    Route::get('/student/grades/by-email', [AuthController::class, 'getStudentGradesByEmail']);
    Route::post('/student/change-password', [AuthController::class, 'changePassword']);
    Route::post('/student/logout', [AuthController::class, 'logout']);

    // New enrollment routes
    Route::get('/enrollments', [EnrollmentApiController::class, 'getEnrolledStudents']);
    Route::get('/enrollments/students', [EnrollmentApiController::class, 'getStudentsWithSubjects']);
    Route::get('/enrollments/stats', [EnrollmentApiController::class, 'getEnrollmentStats']);
    
    // Optional: Get enrollment by student ID
    Route::get('/enrollments/student/{student_id}', [EnrollmentApiController::class, 'getStudentEnrollments']);
    
    // Grade endpoints
    Route::get('/failed-grades', [GradeApiController::class, 'getFailedGrades']);
    Route::get('/retention-alerts', [GradeApiController::class, 'getRetentionAlerts']);
    Route::get('/student/{id}/grades', [GradeApiController::class, 'getStudentGrades']);
    Route::get('/inc-grades', [GradeApiController::class, 'getIncGrades']);
    Route::get('/all-grades', [GradeApiController::class, 'getAllGrades']);
    Route::get('/student/{id}/complete-grades', [GradeApiController::class, 'getStudentCompleteGrades']);
});

// Debug route - REMOVE THIS IN PRODUCTION
// Route::get('/debug-routes', function() {
//     $routes = collect(\Illuminate\Support\Facades\Route::getRoutes()->getRoutes())
//         ->map(function ($route) {
//             return [
//                 'method' => implode('|', $route->methods()),
//                 'uri' => $route->uri(),
//                 'name' => $route->getName(),
//                 'action' => $route->getActionName(),
//             ];
//         })
//         ->filter(function ($route) {
//             return strpos($route['uri'], 'api/') === 0 || strpos($route['uri'], 'student') !== false;
//         })
//         ->values();
    
//     return response()->json($routes);
// });