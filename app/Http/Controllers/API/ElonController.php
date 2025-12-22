<?php
// backend/app/Http/Controllers/API/ElonController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Elon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ElonController extends Controller
{
    /**
     * Display a listing of elonlar.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Elon::with(['ustoz', 'fan'])
            ->where('status', 'approved')
            ->latest();

        // Filtering
        if ($request->has('fan_id')) {
            $query->where('fan_id', $request->fan_id);
        }

        if ($request->has('min_price')) {
            $query->where('narx', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('narx', '<=', $request->max_price);
        }

        if ($request->has('joylashuv')) {
            $query->where('joylashuv', 'like', '%' . $request->joylashuv . '%');
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sarlavha', 'like', "%{$search}%")
                  ->orWhere('tavsif', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->input('per_page', 20);
        $elonlar = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $elonlar
        ]);
    }

    /**
     * Display the specified elon.
     */
    public function show($id): JsonResponse
    {
        $elon = Elon::with(['ustoz', 'fan', 'comments.user'])
            ->findOrFail($id);

        // Increment views
        $elon->increment('views_count');

        return response()->json([
            'success' => true,
            'data' => $elon
        ]);
    }

    /**
     * Store a newly created elon.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fan_id' => 'required|exists:fanlar,id',
            'sarlavha' => 'required|string|max:255',
            'tavsif' => 'required|string',
            'narx' => 'required|numeric|min:0',
            'joylashuv' => 'required|string',
            'dars_vaqti' => 'required|string',
            'rasm' => 'nullable|image|max:2048',
        ]);

        $validated['ustoz_id'] = auth()->user()->ustoz->id;
        $validated['status'] = 'pending'; // Admin approval required

        if ($request->hasFile('rasm')) {
            $path = $request->file('rasm')->store('elon_images', 'public');
            $validated['rasm'] = $path;
        }

        $elon = Elon::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'E\'lon muvaffaqiyatli yaratildi. Admin tasdiqlashini kuting.',
            'data' => $elon
        ], 201);
    }

    /**
     * Get authenticated user's elonlar.
     */
    public function myElonlar(): JsonResponse
    {
        $ustoz = auth()->user()->ustoz;

        if (!$ustoz) {
            return response()->json([
                'success' => false,
                'message' => 'Siz ustoz sifatida ro\'yxatdan o\'tmagansiz'
            ], 403);
        }

        $elonlar = Elon::where('ustoz_id', $ustoz->id)
            ->with(['fan'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $elonlar
        ]);
    }

    /**
     * Update the specified elon.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $elon = Elon::findOrFail($id);

        // Check ownership
        if ($elon->ustoz_id !== auth()->user()->ustoz->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ruxsat yo\'q'
            ], 403);
        }

        $validated = $request->validate([
            'fan_id' => 'sometimes|exists:fanlar,id',
            'sarlavha' => 'sometimes|string|max:255',
            'tavsif' => 'sometimes|string',
            'narx' => 'sometimes|numeric|min:0',
            'joylashuv' => 'sometimes|string',
            'dars_vaqti' => 'sometimes|string',
            'rasm' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('rasm')) {
            $path = $request->file('rasm')->store('elon_images', 'public');
            $validated['rasm'] = $path;
        }

        $elon->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'E\'lon yangilandi',
            'data' => $elon
        ]);
    }

    /**
     * Remove the specified elon.
     */
    public function destroy($id): JsonResponse
    {
        $elon = Elon::findOrFail($id);

        // Check ownership
        if ($elon->ustoz_id !== auth()->user()->ustoz->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ruxsat yo\'q'
            ], 403);
        }

        $elon->delete();

        return response()->json([
            'success' => true,
            'message' => 'E\'lon o\'chirildi'
        ]);
    }
}
