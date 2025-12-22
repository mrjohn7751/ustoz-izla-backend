<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Elon;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ElonManagementController extends Controller
{
    /**
     * Get all elonlar (with filters)
     */
    public function index(Request $request)
    {
        $query = Elon::with('ustoz.user');

        // Filter by status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'approved':
                    $query->approved();
                    break;
                case 'pending':
                    $query->pending();
                    break;
                case 'rejected':
                    $query->rejected();
                    break;
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('subject', 'LIKE', "%{$search}%");
            });
        }

        $elonlar = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $elonlar
        ]);
    }

    /**
     * Get single elon
     */
    public function show($id)
    {
        $elon = Elon::with(['ustoz.user', 'comments.user'])->find($id);

        if (!$elon) {
            return response()->json([
                'success' => false,
                'message' => 'Elon not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $elon
        ]);
    }

    /**
     * Approve elon
     */
    public function approve(Request $request, $id)
    {
        $elon = Elon::find($id);

        if (!$elon) {
            return response()->json([
                'success' => false,
                'message' => 'Elon not found'
            ], 404);
        }

        try {
            $adminNote = $request->input('admin_note', 'E\'lon tasdiqlandi');
            $elon->approve($adminNote);

            // Send notification to ustoz
            Notification::create([
                'user_id' => $elon->ustoz->user_id,
                'type' => 'elon_approved',
                'title' => 'E\'lon tasdiqlandi',
                'message' => "Sizning '{$elon->title}' e\'loningiz admin tomonidan tasdiqlandi.",
                'data' => [
                    'elon_id' => $elon->id,
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Elon approved successfully',
                'data' => $elon
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve elon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject elon
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'admin_note' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $elon = Elon::find($id);

        if (!$elon) {
            return response()->json([
                'success' => false,
                'message' => 'Elon not found'
            ], 404);
        }

        try {
            $elon->reject($request->admin_note);

            // Send notification to ustoz
            Notification::create([
                'user_id' => $elon->ustoz->user_id,
                'type' => 'elon_rejected',
                'title' => 'E\'lon rad etildi',
                'message' => "Sizning '{$elon->title}' e\'loningiz rad etildi. Sabab: {$request->admin_note}",
                'data' => [
                    'elon_id' => $elon->id,
                    'reason' => $request->admin_note,
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Elon rejected successfully',
                'data' => $elon
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject elon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete elon
     */
    public function destroy($id)
    {
        $elon = Elon::find($id);

        if (!$elon) {
            return response()->json([
                'success' => false,
                'message' => 'Elon not found'
            ], 404);
        }

        try {
            $elon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Elon deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete elon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set badge for elon
     */
    public function setBadge(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'badge' => 'nullable|in:yangi,top,chegirma,tavsiya',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $elon = Elon::find($id);

        if (!$elon) {
            return response()->json([
                'success' => false,
                'message' => 'Elon not found'
            ], 404);
        }

        try {
            $elon->badge = $request->badge;
            $elon->save();

            return response()->json([
                'success' => true,
                'message' => 'Badge set successfully',
                'data' => $elon
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set badge',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk approve elonlar
     */
    public function bulkApprove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'elon_ids' => 'required|array',
            'elon_ids.*' => 'integer|exists:elonlar,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $elonlar = Elon::whereIn('id', $request->elon_ids)->get();

            foreach ($elonlar as $elon) {
                $elon->approve('Bulk approved');

                // Send notification
                Notification::create([
                    'user_id' => $elon->ustoz->user_id,
                    'type' => 'elon_approved',
                    'title' => 'E\'lon tasdiqlandi',
                    'message' => "Sizning '{$elon->title}' e\'loningiz admin tomonidan tasdiqlandi.",
                    'data' => ['elon_id' => $elon->id],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Elonlar approved successfully',
                'count' => $elonlar->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve elonlar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
