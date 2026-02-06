<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ElonController;
use App\Http\Controllers\API\UstozController;
use App\Http\Controllers\API\VideoController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\FavoriteController;
use App\Http\Controllers\API\SearchController;

Route::prefix('v1')->group(function () {

    // PUBLIC ROUTES with rate limiting
    Route::middleware('throttle:10,1')->group(function () {
        // Auth routes - 10 requests per minute
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::post('/auth/login', [AuthController::class, 'login']);
    });

    // Public elonlar
    Route::get('/elonlar', [ElonController::class, 'index']);
    Route::get('/elonlar/{id}', [ElonController::class, 'show']);

    // Public ustozlar
    Route::get('/ustozlar', [UstozController::class, 'index']);
    Route::get('/ustozlar/{id}', [UstozController::class, 'show']);

    // PUBLIC VIDEO ROUTES
    Route::get('/videos', [VideoController::class, 'index']);
    Route::get('/videos/{id}', [VideoController::class, 'show']);

    // Rate limited actions to prevent spam
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/videos/{id}/views', [VideoController::class, 'incrementViews']);
        Route::post('/videos/{id}/like', [VideoController::class, 'likeVideo']);
    });

    // Search
    Route::get('/search', [SearchController::class, 'search']);

    // PROTECTED ROUTES
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Elonlar CRUD
        Route::post('/elonlar', [ElonController::class, 'store']);
        Route::put('/elonlar/{id}', [ElonController::class, 'update']);
        Route::delete('/elonlar/{id}', [ElonController::class, 'destroy']);
        Route::get('/my-elonlar', [ElonController::class, 'myElonlar']);

        // VIDEO CRUD
        Route::post('/videos', [VideoController::class, 'store']);
        Route::put('/videos/{id}', [VideoController::class, 'update']);
        Route::delete('/videos/{id}', [VideoController::class, 'destroy']);
        Route::get('/my-videos', [VideoController::class, 'myVideos']);

        // Comments
        Route::post('/comments', [CommentController::class, 'store']);
        Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

        // Favorites
        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::post('/favorites/{elonId}', [FavoriteController::class, 'store']);
        Route::delete('/favorites/{elonId}', [FavoriteController::class, 'destroy']);
    });
});
