<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ustoz;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UstozController extends Controller
{
    /**
     * Get all ustozlar
     */
    public function index(Request $request)
    {
        $query = Ustoz::with('user');

        // Filters
        if ($request->has('location')) {
            $query->byLocation($request->location);
        }

        if ($request->has('min_experience')) {
            $query->byExperience($request->min_experience);
        }

        if ($request->has('verified')) {
            $query->verified();
        }

        if ($request->has('top')) {
            $query->top();
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'latest');
        switch ($sortBy) {
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'experience':
                $query->orderBy('tajriba', 'desc');
                break;
            case 'students':
                $query->orderBy('oquvchilar_soni', 'desc');
                break;
            default:
                $query->latest();
        }

        $ustozlar = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $ustozlar
        ]);
    }

    /**
     * Get single ustoz
     */
    public function show($id)
    {
        $ustoz = Ustoz::with(['user', 'elonlar' => function($query) {
            $query->approved()->latest()->limit(5);
        }, 'videos' => function($query) {
            $query->approved()->latest()->limit(5);
        }, 'ratings.user'])->find($id);

        if (!$ustoz) {
            return response()->json([
                'success' => false,
                'message' => 'Ustoz not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ustoz
        ]);
    }

    /**
     * Register as Ustoz
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ism' => 'required|string|max:255',
            'familiya' => 'required|string|max:255',
            'telefon' => 'required|string|max:20',
            'bio' => 'nullable|string',
            'tajriba' => 'required|integer|min:0',
            'joylashuv' => 'required|string|max:255',
            'fanlar' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();

            // Check if already registered as ustoz
            if (Ustoz::where('user_id', $user->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already registered as ustoz'
                ], 409);
            }

            // Update user role
            $user->role = 'ustoz';
            $user->save();

            // Create ustoz profile
            $ustoz = Ustoz::create([
                'user_id' => $user->id,
                'ism' => $request->ism,
                'familiya' => $request->familiya,
                'telefon' => $request->telefon,
                'bio' => $request->bio,
                'tajriba' => $request->tajriba,
                'joylashuv' => $request->joylashuv,
                'fanlar' => $request->fanlar ?? [],
                'rating' => 0,
                'rating_count' => 0,
                'oquvchilar_soni' => 0,
                'sertifikatlar_soni' => 0,
                'is_verified' => false,
                'status' => 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ustoz profile created successfully',
                'data' => $ustoz
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create ustoz profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update ustoz profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $ustoz = Ustoz::where('user_id', $user->id)->first();

        if (!$ustoz) {
            return response()->json([
                'success' => false,
                'message' => 'Ustoz profile not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'ism' => 'sometimes|string|max:255',
            'familiya' => 'sometimes|string|max:255',
            'telefon' => 'sometimes|string|max:20',
            'bio' => 'nullable|string',
            'tajriba' => 'sometimes|integer|min:0',
            'joylashuv' => 'sometimes|string|max:255',
            'fanlar' => 'nullable|array',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Faqat fillable maydonlarni yangilash
            $updateData = $request->only([
                'ism', 'familiya', 'telefon', 'bio',
                'tajriba', 'joylashuv', 'fanlar'
            ]);

            // Avatar yuklash
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $updateData['avatar'] = $avatarPath;
            }

            $ustoz->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $ustoz
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get my ustoz profile
     */
    public function myProfile(Request $request)
    {
        $user = $request->user();
        $ustoz = Ustoz::with(['elonlar', 'videos'])->where('user_id', $user->id)->first();

        if (!$ustoz) {
            return response()->json([
                'success' => false,
                'message' => 'Ustoz profile not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ustoz
        ]);
    }

    /**
     * Rate ustoz
     */
    public function rate(Request $request, $ustozId)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $ustoz = Ustoz::find($ustozId);
            if (!$ustoz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ustoz not found'
                ], 404);
            }

            $rating = $ustoz->ratings()->updateOrCreate(
                ['user_id' => $request->user()->id],
                [
                    'rating' => $request->rating,
                    'comment' => $request->comment,
                ]
            );

            // Ustoz reytingini yangilash
            $this->updateUstozRating($ustoz);

            return response()->json([
                'success' => true,
                'message' => 'Rating submitted successfully',
                'data' => $rating
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit rating',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ustoz reytingini hisoblash va yangilash
     */
    private function updateUstozRating(Ustoz $ustoz)
    {
        $ratings = $ustoz->ratings;
        if ($ratings->count() > 0) {
            $averageRating = $ratings->avg('rating');
            $ustoz->update([
                'rating' => round($averageRating, 2),
                'rating_count' => $ratings->count(),
            ]);
        }
    }

    /**
     * Update rating
     */
    public function updateRating(Request $request, $ustozId)
    {
        return $this->rate($request, $ustozId);
    }
}
