<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fan;
use Illuminate\Http\Request;

class FanManagementController extends Controller
{
    public function index()
    {
        $fanlar = Fan::orderBy('order')->get();

        return response()->json([
            'success' => true,
            'data' => $fanlar,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomi' => 'required|string|max:255|unique:fanlar,nomi',
            'kod' => 'required|string|max:10|unique:fanlar,kod',
            'tavsif' => 'nullable|string',
            'rasm' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['order'] = $validated['order'] ?? (Fan::max('order') + 1);

        if ($request->hasFile('rasm')) {
            $validated['rasm'] = $request->file('rasm')->store('subjects', 'public');
        }

        $fan = Fan::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Fan muvaffaqiyatli qo\'shildi',
            'data' => $fan,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $fan = Fan::findOrFail($id);

        $validated = $request->validate([
            'nomi' => 'sometimes|string|max:255|unique:fanlar,nomi,' . $id,
            'kod' => 'sometimes|string|max:10|unique:fanlar,kod,' . $id,
            'tavsif' => 'nullable|string',
            'rasm' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'order' => 'integer',
        ]);

        if ($request->hasFile('rasm')) {
            $validated['rasm'] = $request->file('rasm')->store('subjects', 'public');
        }

        $fan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Fan yangilandi',
            'data' => $fan,
        ]);
    }

    public function destroy($id)
    {
        $fan = Fan::findOrFail($id);

        if ($fan->elonlar()->count() > 0 || $fan->videos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bu fanga bog\'langan e\'lonlar yoki videolar mavjud. Avval ularni o\'chiring.',
            ], 422);
        }

        $fan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Fan o\'chirildi',
        ]);
    }
}
