<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TelegramAuthController extends Controller
{
    /**
     * Telegram bot orqali telefon raqami bilan login/register
     * Faqat bot tomonidan chaqirilishi mumkin (BOT_SECRET header bilan)
     */
    public function login(Request $request)
    {
        // Bot secret tekshirish
        $expectedSecret = config('services.telegram.bot_secret');
        $providedSecret = $request->header('X-Bot-Secret');

        if (empty($expectedSecret) || $providedSecret !== $expectedSecret) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:20',
            'telegram_chat_id' => 'required|integer',
            'telegram_username' => 'nullable|string|max:100',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Telefonni normallashtirish (faqat raqamlar va +)
        $phone = $this->normalizePhone($request->phone);

        try {
            // Mavjud foydalanuvchini topish
            $user = User::where('phone', $phone)->first();

            if (!$user) {
                // Yangi foydalanuvchi yaratish
                $name = trim(
                    ($request->first_name ?? '') . ' ' . ($request->last_name ?? '')
                );
                if (empty($name)) {
                    $name = 'Foydalanuvchi';
                }

                // Email avtomatik yaratiladi (Telegram orqali ro'yxatdan o'tganda)
                $email = 'tg_' . $request->telegram_chat_id . '@ustoz-izla.uz';

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => Hash::make(Str::random(32)),
                    'role' => 'fan',
                    'is_active' => true,
                ]);
            }

            // Akkaunt faolligini tekshirish
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is deactivated',
                ], 403);
            }

            // Admin yoki ustoz Telegram orqali kira olmaydi
            if ($user->role !== 'fan') {
                return response()->json([
                    'success' => false,
                    'message' => 'Telegram bot faqat o\'quvchilar uchun',
                ], 403);
            }

            // Eski tokenlarni o'chirish (faqat bot uchun)
            $user->tokens()->where('name', 'telegram_bot')->delete();

            // Yangi token yaratish
            $token = $user->createToken('telegram_bot')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'is_new_user' => $user->wasRecentlyCreated,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kirishda xatolik yuz berdi',
            ], 500);
        }
    }

    /**
     * Telefon raqamini normallashtirish
     */
    private function normalizePhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9+]/', '', $phone);

        if (!str_starts_with($clean, '+')) {
            if (str_starts_with($clean, '998')) {
                $clean = '+' . $clean;
            } elseif (strlen($clean) === 9) {
                $clean = '+998' . $clean;
            }
        }

        return $clean;
    }
}
