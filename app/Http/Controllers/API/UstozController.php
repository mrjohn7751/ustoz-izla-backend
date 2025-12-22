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
                $query->orderBy('average_rating', 'desc');
                break;
            case 'experience':
                $query->orderBy('experience_years', 'desc');
                break;
            case 'students':
                $query->orderBy('students_count', 'desc');
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
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'telegram' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'education' => 'nullable|string',
            'experience_years' => 'required|integer|min:0',
            'location' => 'required|string|max:255',
            'center_name' => 'nullable|string|max:255',
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
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'telegram' => $request->telegram,
                'bio' => $request->bio,
                'education' => $request->education,
                'experience_years' => $request->experience_years,
                'location' => $request->location,
                'center_name' => $request->center_name,
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
            'full_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'telegram' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'education' => 'nullable|string',
            'experience_years' => 'sometimes|integer|min:0',
            'location' => 'sometimes|string|max:255',
            'center_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $ustoz->update($request->all());

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
            'review' => 'nullable|string|max:500',
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
                    'review' => $request->review,
                ]
            );

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
     * Update rating
     */
    public function updateRating(Request $request, $ustozId)
    {
        return $this->rate($request, $ustozId);
    }
}
