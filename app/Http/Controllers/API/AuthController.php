<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Ustoz;
use App\Models\Fan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            // Kuchli parol: min 8, katta harf, kichik harf, raqam
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',      // kamida 1 kichik harf
                'regex:/[A-Z]/',      // kamida 1 katta harf
                'regex:/[0-9]/',      // kamida 1 raqam
            ],
            'role' => 'required|in:fan,ustoz',
        ], [
            'password.regex' => 'Parol kamida 1 katta harf, 1 kichik harf va 1 raqam bo\'lishi kerak',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            // Create related profile based on role
            if ($request->role === 'fan') {
                Fan::create([
                    'user_id' => $user->id,
                    'phone' => $request->phone,
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ]
            ], 201);
        } catch (\Exception $e) {
            // Security: Exception details hidden
            return response()->json([
                'success' => false,
                'message' => 'Ro\'yxatdan o\'tishda xatolik yuz berdi'
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account is deactivated'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user();

        // Faqat ustoz rolida bo'lsa, ustoz profilini yuklash
        if ($user->role === 'ustoz') {
            $user->load('ustoz');
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . $user->id,
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $avatarPath;
            }

            $user->update($request->only(['name', 'phone']));

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            // Security: Exception details hidden
            return response()->json([
                'success' => false,
                'message' => 'Profilni yangilashda xatolik yuz berdi'
            ], 500);
        }
    }

    /**
     * Delete user account and all associated data
     */
    public function deleteAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parolni kiriting',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Parol noto\'g\'ri'
            ], 401);
        }

        if ($user->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admin hisobini o\'chirib bo\'lmaydi'
            ], 403);
        }

        try {
            // Delete related data
            if ($user->role === 'ustoz' && $user->ustoz) {
                // Delete ustoz's elonlar (soft delete)
                $user->ustoz->elonlar()->delete();
                // Delete ustoz's videos (soft delete)
                $user->ustoz->videos()->delete();
                // Delete ustoz ratings
                $user->ustoz->ratings()->delete();
                // Delete ustoz profile
                $user->ustoz->delete();
            }

            // Delete user's comments (soft delete)
            $user->comments()->delete();
            // Delete user's favorites
            $user->favorites()->delete();
            // Delete user's ratings
            $user->ratings()->delete();
            // Delete user's notifications
            $user->notifications()->delete();
            // Delete all access tokens
            $user->tokens()->delete();
            // Delete user
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Hisobingiz muvaffaqiyatli o\'chirildi'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hisobni o\'chirishda xatolik yuz berdi'
            ], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            // Kuchli parol: min 8, katta harf, kichik harf, raqam
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
        ], [
            'new_password.regex' => 'Parol kamida 1 katta harf, 1 kichik harf va 1 raqam bo\'lishi kerak',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 401);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }
}
