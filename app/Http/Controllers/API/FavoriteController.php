<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Elon;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Get user's favorite elons
     */
    public function index(Request $request)
    {
        $favorites = Favorite::with(['elon.ustoz.user'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $favorites
        ]);
    }

    /**
     * Add elon to favorites
     */
    public function store(Request $request, $elonId)
    {
        $elon = Elon::find($elonId);

        if (!$elon) {
            return response()->json([
                'success' => false,
                'message' => 'Elon not found'
            ], 404);
        }

        try {
            $favorite = Favorite::firstOrCreate([
                'user_id' => $request->user()->id,
                'elon_id' => $elonId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Added to favorites',
                'data' => $favorite
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Already in favorites'
            ], 409);
        }
    }

    /**
     * Remove elon from favorites
     */
    public function destroy(Request $request, $elonId)
    {
        $favorite = Favorite::where('user_id', $request->user()->id)
            ->where('elon_id', $elonId)
            ->first();

        if (!$favorite) {
            return response()->json([
                'success' => false,
                'message' => 'Favorite not found'
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Removed from favorites'
        ]);
    }

    /**
     * Check if elon is favorited
     */
    public function check(Request $request, $elonId)
    {
        $isFavorited = Favorite::where('user_id', $request->user()->id)
            ->where('elon_id', $elonId)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'is_favorited' => $isFavorited
            ]
        ]);
    }
}
