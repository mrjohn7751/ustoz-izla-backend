<?php
// backend/routes/web.php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Privacy Policy
Route::get('/privacy-policy', function () {
    return view('privacy-policy');
});

// Account Deletion
Route::get('/delete-account', function () {
    return view('delete-account');
});

// Login route (API uchun redirect)
Route::get('/login', function () {
    return response()->json([
        'message' => 'This is an API-only application. Please use POST /api/v1/auth/login',
        'login_endpoint' => url('/api/v1/auth/login')
    ], 401);
})->name('login');

// Redirect to API documentation or admin panel
Route::get('/admin', function () {
    return redirect('/admin/dashboard');
});
