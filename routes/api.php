<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the API Router middleware group. Build something
| great!
|
*/

// Attendance API Routes
Route::middleware('auth:api')->group(function () {
    // Attendance Settings
    Route::get('/attendance/settings', [\App\Http\Controllers\Attendance\Api\AttendanceApi::class, 'getSettings']);
    Route::post('/attendance/settings', [\App\Http\Controllers\Attendance\Api\AttendanceApi::class, 'updateSettings']);

    // GPS Validation
    Route::post('/attendance/validate-gps', [\App\Http\Controllers\Attendance\Api\AttendanceApi::class, 'validateGPS']);

    // Foto Upload
    Route::post('/attendance/upload-foto', [\App\Http\Controllers\Attendance\Api\AttendanceApi::class, 'uploadFoto']);

    // Activity Log
    Route::get('/activity/log', [\App\Http\Controllers\Attendance\Api\AttendanceApi::class, 'getActivityLog']);
});

// Public routes (no auth guard)
Route::middleware('guest:api')->group(function () {
    // Health check atau routes publik lainnya
});