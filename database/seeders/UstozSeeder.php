<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Ustoz;
use Illuminate\Support\Facades\Hash;

class UstozSeeder extends Seeder
{
    public function run(): void
    {
        // Ustoz 1 - Matematika
        $user1 = User::firstOrCreate(
            ['email' => 'alisher@ustoz.uz'],
            [
                'name' => 'Alisher Karimov',
                'phone' => '+998901111111',
                'password' => Hash::make('password123'),
                'role' => 'ustoz',
                'is_active' => true,
            ]
        );

        Ustoz::firstOrCreate(
            ['user_id' => $user1->id],
            [
                'ism' => 'Alisher',
                'familiya' => 'Karimov',
                'telefon' => '+998901111111',
                'joylashuv' => 'Toshkent, Yunusobod',
                'tajriba' => 5,
                'fanlar' => json_encode([1]),
                'rating' => 4.8,
                'rating_count' => 45,
                'oquvchilar_soni' => 120,
                'sertifikatlar_soni' => 3,
                'is_verified' => true,
                'status' => 'active',
                'bio' => 'Matematika bo\'yicha 5 yillik tajribaga ega. TATU bitiruvchisi.',
            ]
        );

        // Ustoz 2 - Ingliz tili
        $user2 = User::firstOrCreate(
            ['email' => 'madina@ustoz.uz'],
            [
                'name' => 'Madina Rahimova',
                'phone' => '+998902222222',
                'password' => Hash::make('password123'),
                'role' => 'ustoz',
                'is_active' => true,
            ]
        );

        Ustoz::firstOrCreate(
            ['user_id' => $user2->id],
            [
                'ism' => 'Madina',
                'familiya' => 'Rahimova',
                'telefon' => '+998902222222',
                'joylashuv' => 'Toshkent, Chilonzor',
                'tajriba' => 7,
                'fanlar' => json_encode([2]),
                'rating' => 4.9,
                'rating_count' => 68,
                'oquvchilar_soni' => 150,
                'sertifikatlar_soni' => 5,
                'is_verified' => true,
                'status' => 'active',
                'bio' => 'IELTS 8.0, 7 yillik o\'qitish tajribasi. Cambridge sertifikatlangan.',
            ]
        );

        // Ustoz 3 - Fizika
        $user3 = User::firstOrCreate(
            ['email' => 'bobur@ustoz.uz'],
            [
                'name' => 'Bobur Toshmatov',
                'phone' => '+998903333333',
                'password' => Hash::make('password123'),
                'role' => 'ustoz',
                'is_active' => true,
            ]
        );

        Ustoz::firstOrCreate(
            ['user_id' => $user3->id],
            [
                'ism' => 'Bobur',
                'familiya' => 'Toshmatov',
                'telefon' => '+998903333333',
                'joylashuv' => 'Toshkent, Mirzo Ulug\'bek',
                'tajriba' => 3,
                'fanlar' => json_encode([3]),
                'rating' => 4.6,
                'rating_count' => 28,
                'oquvchilar_soni' => 75,
                'sertifikatlar_soni' => 2,
                'is_verified' => true,
                'status' => 'active',
                'bio' => 'Fizika-matematika fakulteti bitiruvchisi. DTM tayyorlash.',
            ]
        );
    }
}
