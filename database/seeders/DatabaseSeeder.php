<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ===== ADMIN USER =====
        $admin = User::firstOrCreate(
            ['email' => 'admin@ustoz.uz'],
            [
                'name' => 'Admin',
                'phone' => '+998901234567',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->command->info('✅ Admin tayyor: admin@ustoz.uz / admin123');

        // ===== TEST O'QUVCHI (FAN) =====
        $testFan = User::firstOrCreate(
            ['email' => 'user@ustoz.uz'],
            [
                'name' => 'Test O\'quvchi',
                'phone' => '+998901234568',
                'password' => Hash::make('password123'),
                'role' => 'fan',
                'is_active' => true,
            ]
        );

        $this->command->info('✅ Test o\'quvchi tayyor: user@ustoz.uz / password123');

        // ===== TEST O'QITUVCHI (USTOZ) =====
        $testUstoz = User::firstOrCreate(
            ['email' => 'ustoz@ustoz.uz'],
            [
                'name' => 'Test Ustoz',
                'phone' => '+998901234569',
                'password' => Hash::make('password123'),
                'role' => 'ustoz',
                'is_active' => true,
            ]
        );

        $this->command->info('✅ Test ustoz yaratildi: ustoz@ustoz.uz / password123');

        // ===== QOLGAN SEEDERLAR =====
        $this->call([
            FanSeeder::class,
            UstozSeeder::class,
            ElonSeeder::class,
            VideoSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ Barcha seederlar muvaffaqiyatli bajarildi!');
        $this->command->info('');
        $this->command->info('=== LOGIN MA\'LUMOTLARI ===');
        $this->command->info('Admin: admin@ustoz.uz / admin123');
        $this->command->info('O\'quvchi: user@ustoz.uz / password123');
        $this->command->info('Ustoz: ustoz@ustoz.uz / password123');
    }
}
