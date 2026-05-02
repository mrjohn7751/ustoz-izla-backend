<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Elon;
use App\Models\Ustoz;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EnrollmentController extends Controller
{
    /**
     * Kursga yozilish
     * POST /api/v1/enrollments
     */
    public function enroll(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'elon_id' => 'required|exists:elonlar,id',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $user = $request->user();
            $elonId = $request->elon_id;

            // E'lonni tekshirish
            $elon = Elon::with('ustoz')->find($elonId);

            if (!$elon) {
                return response()->json([
                    'success' => false,
                    'message' => 'E\'lon topilmadi'
                ], 404);
            }

            if ($elon->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu e\'lon hali tasdiqlanmagan'
                ], 400);
            }

            // Allaqachon yozilganligini tekshirish (soft delete ni hisobga olgan holda)
            $existingEnrollment = Enrollment::where('user_id', $user->id)
                                           ->where('elon_id', $elonId)
                                           ->whereNull('deleted_at')
                                           ->whereIn('status', ['active', 'completed'])
                                           ->first();

            if ($existingEnrollment) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Siz allaqachon bu kursga yozilgansiz',
                    'data' => $existingEnrollment
                ], 409);
            }

            // Yangi yozilish yaratish
            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'elon_id' => $elonId,
                'ustoz_id' => $elon->ustoz_id,
                'status' => 'active',
                'enrolled_at' => Carbon::now(),
                'can_rate' => false,
                'can_rate_from' => Carbon::now()->addMonth(), // 1 oydan keyin
                'has_rated' => false,
                'notes' => $request->notes,
                'payment_status' => 'pending',
            ]);

            // Ustoz o'quvchilar sonini oshirish
            $ustoz = $elon->ustoz;
            $ustoz->increment('oquvchilar_soni');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kursga muvaffaqiyatli yozildingiz!',
                'data' => $enrollment->load(['elon.ustoz', 'elon.fan'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Kursga yozilishda xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mening kurslarim
     * GET /api/v1/my-enrollments
     */
    public function myEnrollments(Request $request)
    {
        try {
            $user = $request->user();

            $status = $request->get('status'); // active, completed, cancelled
            $perPage = $request->get('per_page', 20);

            $query = Enrollment::with(['elon.ustoz', 'elon.fan', 'ustoz'])
                              ->where('user_id', $user->id);

            if ($status) {
                $query->where('status', $status);
            }

            $enrollments = $query->latest('enrolled_at')->paginate($perPage);

            // Bulk update: faqat yangilanishi kerak bo'lganlarni yangilash
            $enrollmentsToUpdate = [];
            $oneMonthAgo = Carbon::now()->subMonth();

            foreach ($enrollments as $enrollment) {
                if (!$enrollment->has_rated &&
                    !$enrollment->can_rate &&
                    $enrollment->enrolled_at <= $oneMonthAgo) {
                    $enrollmentsToUpdate[] = $enrollment->id;
                }
            }

            // Bulk update
            if (!empty($enrollmentsToUpdate)) {
                Enrollment::whereIn('id', $enrollmentsToUpdate)->update([
                    'can_rate' => true,
                    'can_rate_from' => $oneMonthAgo,
                ]);

                // Refresh data
                $enrollments = $query->latest('enrolled_at')->paginate($perPage);
            }

            return response()->json([
                'success' => true,
                'data' => $enrollments
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kurslarni yuklashda xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kurs tafsilotlari
     * GET /api/v1/enrollments/{id}
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();

            $enrollment = Enrollment::with(['elon.ustoz', 'elon.fan', 'ustoz', 'rating'])
                                   ->where('user_id', $user->id)
                                   ->find($id);

            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kurs topilmadi'
                ], 404);
            }

            // Baholash imkoniyatini tekshirish
            $enrollment->checkIfCanRate();
            $enrollment->refresh();

            return response()->json([
                'success' => true,
                'data' => $enrollment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kurs ma\'lumotlarini yuklashda xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kursni bekor qilish
     * DELETE /api/v1/enrollments/{id}
     */
    public function cancel(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();

            $enrollment = Enrollment::where('user_id', $user->id)->find($id);

            if (!$enrollment) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Kurs topilmadi'
                ], 404);
            }

            if ($enrollment->status !== 'active') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Bu kursni bekor qilish mumkin emas'
                ], 400);
            }

            $enrollment->markAsCancelled();

            // Ustoz o'quvchilar sonini kamaytirish
            $enrollment->ustoz->decrement('oquvchilar_soni');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kurs bekor qilindi'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Kursni bekor qilishda xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Kursni tugatish
     * POST /api/v1/enrollments/{id}/complete
     */
    public function complete(Request $request, $id)
    {
        try {
            $user = $request->user();

            $enrollment = Enrollment::where('user_id', $user->id)->find($id);

            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kurs topilmadi'
                ], 404);
            }

            if ($enrollment->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bu kursni tugatish mumkin emas'
                ], 400);
            }

            $enrollment->markAsCompleted();

            return response()->json([
                'success' => true,
                'message' => 'Kurs tugallandi',
                'data' => $enrollment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kursni tugatishda xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * O'qituvchini baholash (faqat 1 oydan keyin)
     * POST /api/v1/enrollments/{id}/rate
     */
    public function rateTeacher(Request $request, $id)
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

        DB::beginTransaction();

        try {
            $user = $request->user();

            $enrollment = Enrollment::where('user_id', $user->id)->find($id);

            if (!$enrollment) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Kurs topilmadi'
                ], 404);
            }

            // Baholash imkoniyatini tekshirish
            $enrollment->checkIfCanRate();
            $enrollment->refresh();

            if (!$enrollment->can_rate) {
                DB::rollBack();
                $daysLeft = $enrollment->days_until_rating;
                return response()->json([
                    'success' => false,
                    'message' => "O'qituvchini baholash uchun yana {$daysLeft} kun kutishingiz kerak",
                    'days_left' => $daysLeft,
                    'can_rate_from' => $enrollment->rating_available_date
                ], 403);
            }

            if ($enrollment->has_rated) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Siz allaqachon bu o\'qituvchini baholagansiz'
                ], 409);
            }

            // Baholash yaratish yoki yangilash (enrollment_id bilan)
            $rating = Rating::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'enrollment_id' => $enrollment->id,
                ],
                [
                    'ustoz_id' => $enrollment->ustoz_id,
                    'rating' => $request->rating,
                    'comment' => $request->comment,
                ]
            );

            // Enrollment ni yangilash
            $enrollment->markAsRated();

            // Ustoz reytingini yangilash
            $this->updateUstozRating($enrollment->ustoz);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Baholash muvaffaqiyatli qo\'shildi!',
                'data' => [
                    'rating' => $rating,
                    'enrollment' => $enrollment->fresh(['rating']),
                    'ustoz_new_rating' => $enrollment->ustoz->fresh()->rating
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Baholashda xatolik yuz berdi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ustoz reytingini yangilash
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
        } else {
            // Agar barcha ratinglar o'chirilsa, 0 ga qaytarish
            $ustoz->update([
                'rating' => 0,
                'rating_count' => 0,
            ]);
        }
    }

    /**
     * Baholash imkoniyatini tekshirish
     * GET /api/v1/enrollments/{id}/can-rate
     */
    public function checkCanRate(Request $request, $id)
    {
        try {
            $user = $request->user();

            $enrollment = Enrollment::where('user_id', $user->id)->find($id);

            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kurs topilmadi'
                ], 404);
            }

            $enrollment->checkIfCanRate();
            $enrollment->refresh();

            return response()->json([
                'success' => true,
                'data' => [
                    'can_rate' => $enrollment->can_rate,
                    'has_rated' => $enrollment->has_rated,
                    'can_show_rating_button' => $enrollment->can_show_rating_button,
                    'days_until_rating' => $enrollment->days_until_rating,
                    'rating_available_date' => $enrollment->rating_available_date,
                    'enrolled_at' => $enrollment->enrolled_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tekshirishda xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ustoz o'quvchilari ro'yxati (faqat ustoz uchun)
     * GET /api/v1/ustoz/my-students
     */
    public function myStudents(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->role !== 'ustoz') {
                return response()->json([
                    'success' => false,
                    'message' => 'Faqat o\'qituvchilar uchun'
                ], 403);
            }

            $ustoz = Ustoz::where('user_id', $user->id)->first();

            if (!$ustoz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ustoz profili topilmadi'
                ], 404);
            }

            $status = $request->get('status'); // active, completed, cancelled
            $perPage = $request->get('per_page', 20);

            $query = Enrollment::with(['user', 'elon', 'rating'])
                              ->where('ustoz_id', $ustoz->id);

            if ($status) {
                $query->where('status', $status);
            }

            $students = $query->latest('enrolled_at')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $students
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'O\'quvchilarni yuklashda xatolik',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
