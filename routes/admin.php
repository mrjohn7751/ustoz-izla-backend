<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ElonManagementController;
use App\Http\Controllers\Admin\VideoManagementController;
use App\Http\Controllers\Admin\UserManagementController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| These routes are for admin panel management
| All routes require admin authentication
|
*/

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/charts', [DashboardController::class, 'charts']);
    Route::get('/dashboard/subject-stats', [DashboardController::class, 'subjectStats']);
    Route::get('/dashboard/location-stats', [DashboardController::class, 'locationStats']);
    
    // Elon Management
    Route::prefix('elonlar')->group(function () {
        Route::get('/', [ElonManagementController::class, 'index']);
        Route::get('/{id}', [ElonManagementController::class, 'show']);
        Route::post('/{id}/approve', [ElonManagementController::class, 'approve']);
        Route::post('/{id}/reject', [ElonManagementController::class, 'reject']);
        Route::delete('/{id}', [ElonManagementController::class, 'destroy']);
        Route::post('/bulk-action', [ElonManagementController::class, 'bulkAction']);
    });
    
    // Video Management
    Route::prefix('videos')->group(function () {
        Route::get('/', [VideoManagementController::class, 'index']);
        Route::get('/{id}', [VideoManagementController::class, 'show']);
        Route::post('/{id}/approve', [VideoManagementController::class, 'approve']);
        Route::post('/{id}/reject', [VideoManagementController::class, 'reject']);
        Route::delete('/{id}', [VideoManagementController::class, 'destroy']);
        Route::post('/bulk-action', [VideoManagementController::class, 'bulkAction']);
    });
    
    // User Management
    Route::prefix('users')->group(function () {
        Route::get('/', [UserManagementController::class, 'index']);
        Route::get('/{id}', [UserManagementController::class, 'show']);
        Route::post('/{id}/activate', [UserManagementController::class, 'activate']);
        Route::post('/{id}/deactivate', [UserManagementController::class, 'deactivate']);
        Route::delete('/{id}', [UserManagementController::class, 'destroy']);
        Route::post('/{id}/change-role', [UserManagementController::class, 'changeRole']);
    });
});
