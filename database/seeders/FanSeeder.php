<?php
// database/seeders/FanSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Fan;

class FanSeeder extends Seeder
{
    public function run(): void
    {
        $fanlar = [
            [
                'nomi' => 'Matematika',
                'kod' => 'MAT',
                'rasm' => 'subjects/matematika.png',
                'tavsif' => 'Algebra, geometriya va matematik tahlil',
                'is_active' => true,
                'order' => 1
            ],
            [
                'nomi' => 'Ingliz tili',
                'kod' => 'ENG',
                'rasm' => 'subjects/ingliz_tili.png',
                'tavsif' => 'IELTS, CEFR va umumiy ingliz tili',
                'is_active' => true,
                'order' => 2
            ],
            [
                'nomi' => 'Fizika',
                'kod' => 'FIZ',
                'rasm' => 'subjects/fizika.png',
                'tavsif' => 'Mexanika, elektr va optika',
                'is_active' => true,
                'order' => 3
            ],
            [
                'nomi' => 'Kimyo',
                'kod' => 'KIM',
                'rasm' => 'subjects/kimyo.png',
                'tavsif' => 'Organik va noorganik kimyo',
                'is_active' => true,
                'order' => 4
            ],
            [
                'nomi' => 'Biologiya',
                'kod' => 'BIO',
                'rasm' => 'subjects/biologiya.png',
                'tavsif' => 'Botanika, zoologiya va anatomiya',
                'is_active' => true,
                'order' => 5
            ],
            [
                'nomi' => 'Rus tili',
                'kod' => 'RUS',
                'rasm' => 'subjects/rus_tili.png',
                'tavsif' => 'Grammatika va adabiyot',
                'is_active' => true,
                'order' => 6
            ],
            [
                'nomi' => 'Tarix',
                'kod' => 'TAR',
                'rasm' => 'subjects/tarix.png',
                'tavsif' => 'O\'zbekiston va jahon tarixi',
                'is_active' => true,
                'order' => 7
            ],
            [
                'nomi' => 'Informatika',
                'kod' => 'INF',
                'rasm' => 'subjects/informatika.png',
                'tavsif' => 'Dasturlash va kompyuter savodxonligi',
                'is_active' => true,
                'order' => 8
            ],
        ];

        foreach ($fanlar as $fan) {
            Fan::create($fan);
        }
    }
}
