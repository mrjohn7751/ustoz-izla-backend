<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Elon;
use App\Models\Ustoz;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search for elons
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required'
            ], 400);
        }

        $elonlar = Elon::with(['ustoz.user', 'fan'])
            ->approved()
            ->where(function($q) use ($query) {
                $q->where('sarlavha', 'LIKE', "%{$query}%")
                  ->orWhere('tavsif', 'LIKE', "%{$query}%")
                  ->orWhere('joylashuv', 'LIKE', "%{$query}%")
                  ->orWhereHas('fan', function($fanQuery) use ($query) {
                      $fanQuery->where('nomi', 'LIKE', "%{$query}%");
                  });
            })
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $elonlar
        ]);
    }

    /**
     * Search ustozlar
     */
    public function searchUstozlar(Request $request)
    {
        $query = $request->get('q', '');

        $ustozlar = Ustoz::with('user')
            ->where(function($q) use ($query) {
                $q->where('ism', 'LIKE', "%{$query}%")
                  ->orWhere('familiya', 'LIKE', "%{$query}%")
                  ->orWhere('joylashuv', 'LIKE', "%{$query}%")
                  ->orWhere('bio', 'LIKE', "%{$query}%");
            })
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $ustozlar
        ]);
    }

    /**
     * Get filter options
     */
    public function filterOptions()
    {
        $subjects = Elon::approved()
            ->distinct()
            ->pluck('subject');

        $locations = Elon::approved()
            ->distinct()
            ->pluck('location');

        $priceRange = [
            'min' => Elon::approved()->min('price'),
            'max' => Elon::approved()->max('price'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'subjects' => $subjects,
                'locations' => $locations,
                'price_range' => $priceRange,
            ]
        ]);
    }
}
