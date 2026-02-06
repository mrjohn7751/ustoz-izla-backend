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
        $favorites = Favorite::with(['favoritable.ustoz', 'favoritable.fan'])
            ->where('user_id', $request->user()->id)
            ->where('favoritable_type', Elon::class)
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

        // Tekshirish - allaqachon qo'shilganmi
        $existing = Favorite::where('user_id', $request->user()->id)
            ->where('favoritable_type', Elon::class)
            ->where('favoritable_id', $elonId)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Already in favorites'
            ], 409);
        }

        try {
            $favorite = Favorite::create([
                'user_id' => $request->user()->id,
                'favoritable_type' => Elon::class,
                'favoritable_id' => $elonId,
            ]);

            // E'lonning favorites_count ni oshirish
            $elon->increment('favorites_count');

            return response()->json([
                'success' => true,
                'message' => 'Added to favorites',
                'data' => $favorite
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add to favorites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove elon from favorites
     */
    public function destroy(Request $request, $elonId)
    {
        $favorite = Favorite::where('user_id', $request->user()->id)
            ->where('favoritable_type', Elon::class)
            ->where('favoritable_id', $elonId)
            ->first();

        if (!$favorite) {
            return response()->json([
                'success' => false,
                'message' => 'Favorite not found'
            ], 404);
        }

        $favorite->delete();

        // E'lonning favorites_count ni kamaytirish
        $elon = Elon::find($elonId);
        if ($elon && $elon->favorites_count > 0) {
            $elon->decrement('favorites_count');
        }

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
            ->where('favoritable_type', Elon::class)
            ->where('favoritable_id', $elonId)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'is_favorited' => $isFavorited
            ]
        ]);
    }
}
