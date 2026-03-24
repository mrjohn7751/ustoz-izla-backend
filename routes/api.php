<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ElonController;
use App\Http\Controllers\API\UstozController;
use App\Http\Controllers\API\VideoController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\FavoriteController;
use App\Http\Controllers\API\SearchController;
use App\Http\Controllers\API\ChatApiController;

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

    // Public elon comments
    Route::get('/elonlar/{id}/comments', function ($id) {
        $comments = \App\Models\Comment::with('user')
            ->where('commentable_type', \App\Models\Elon::class)
            ->where('commentable_id', $id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comments,
        ]);
    });

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
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
        Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
        Route::delete('/auth/delete-account', [AuthController::class, 'deleteAccount']);

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

        // Ustoz registration
        Route::post('/ustoz/register', [UstozController::class, 'register']);
        Route::get('/ustoz/profile', [UstozController::class, 'myProfile']);
        Route::put('/ustoz/profile', [UstozController::class, 'updateProfile']);
        Route::post('/ustoz/{id}/rate', [UstozController::class, 'rate']);

        // Comments
        Route::post('/comments', [CommentController::class, 'store']);
        Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

        // Favorites
        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::post('/favorites/{elonId}', [FavoriteController::class, 'store']);
        Route::delete('/favorites/{elonId}', [FavoriteController::class, 'destroy']);

        // Chat
        Route::get('/chats', [ChatApiController::class, 'index']);
        Route::post('/chats', [ChatApiController::class, 'store']);
        Route::get('/chats/{id}', [ChatApiController::class, 'show']);
        Route::get('/chats/{id}/messages', [ChatApiController::class, 'messages']);
        Route::post('/chats/{id}/send-message', [ChatApiController::class, 'sendMessage']);
        Route::post('/chats/{id}/mark-as-read', [ChatApiController::class, 'markAsRead']);
        Route::delete('/chats/{id}', [ChatApiController::class, 'destroy']);

        // Admin: Ustoz statusini o'zgartirish (tasdiqlash/rad etish)
        Route::put('/ustozlar/{id}', [UstozController::class, 'updateStatus']);
    });
});
