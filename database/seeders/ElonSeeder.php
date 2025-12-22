<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Elon;

class ElonSeeder extends Seeder
{
    public function run(): void
    {
        $elonlar = [
            // Alisher Karimov - Matematika (ustoz_id: 1, fan_id: 1)
            [
                'ustoz_id' => 1,
                'fan_id' => 1,
                'sarlavha' => 'Matematikadan shaxsiy darslar',
                'tavsif' => 'DTM va OTM imtihonlariga tayyorgarlik. Algebra, geometriya va matematik tahlil bo\'yicha chuqur bilim.',
                'narx' => 150000,
                'joylashuv' => 'Toshkent, Yunusobod tumani',
                'markaz_nomi' => 'Algoritmika o\'quv markazi',
                'dars_kunlari' => json_encode(['Dushanba', 'Chorshanba', 'Juma']),
                'dars_vaqti' => '18:00 - 20:00',
                'status' => 'approved',
                'badge' => 'Top Ustoz',
                'views_count' => 245,
                'favorites_count' => 18,
            ],
            [
                'ustoz_id' => 1,
                'fan_id' => 1,
                'sarlavha' => 'Matematika olimpiadasiga tayyorgarlik',
                'tavsif' => 'Respublika va xalqaro olimpiadalar uchun maxsus dastur. Murakkab masalalar yechish texnikasi.',
                'narx' => 250000,
                'joylashuv' => 'Toshkent, Yunusobod tumani',
                'markaz_nomi' => 'Olimpiada Academy',
                'dars_kunlari' => json_encode(['Seshanba', 'Payshanba']),
                'dars_vaqti' => '15:00 - 17:00',
                'status' => 'approved',
                'views_count' => 89,
                'favorites_count' => 8,
            ],

            // Madina Rahimova - Ingliz tili (ustoz_id: 2, fan_id: 2)
            [
                'ustoz_id' => 2,
                'fan_id' => 2,
                'sarlavha' => 'IELTS 7.0+ kafolati',
                'tavsif' => 'IELTS imtihoniga professional tayyorgarlik. Speaking, Writing, Reading va Listening bo\'limlari bo\'yicha maxsus mashg\'ulotlar.',
                'narx' => 200000,
                'joylashuv' => 'Toshkent, Chilonzor tumani',
                'markaz_nomi' => 'British Council style',
                'dars_kunlari' => json_encode(['Seshanba', 'Payshanba', 'Shanba']),
                'dars_vaqti' => '17:00 - 19:00',
                'status' => 'approved',
                'badge' => 'Tavsiya',
                'chegirma_foiz' => 20,
                'views_count' => 312,
                'favorites_count' => 25,
            ],
            [
                'ustoz_id' => 2,
                'fan_id' => 2,
                'sarlavha' => 'Bolalar uchun ingliz tili (6-12 yosh)',
                'tavsif' => 'Kichik yoshdagi bolalar uchun o\'yin va interaktiv usullarda ingliz tili o\'rgatish.',
                'narx' => 130000,
                'joylashuv' => 'Toshkent, Chilonzor tumani',
                'markaz_nomi' => 'Kids English Club',
                'dars_kunlari' => json_encode(['Dushanba', 'Chorshanba', 'Juma']),
                'dars_vaqti' => '14:00 - 15:30',
                'status' => 'approved',
                'badge' => 'Yangi',
                'views_count' => 134,
                'favorites_count' => 15,
            ],

            // Bobur Toshmatov - Fizika (ustoz_id: 3, fan_id: 3)
            [
                'ustoz_id' => 3,
                'fan_id' => 3,
                'sarlavha' => 'Fizika - DTM tayyorlov',
                'tavsif' => 'DTM fizika fanidan yuqori ball olish uchun maxsus dastur. Mexanika, molekulyar fizika, elektr va optika.',
                'narx' => 120000,
                'joylashuv' => 'Toshkent, Mirzo Ulug\'bek tumani',
                'markaz_nomi' => 'Fizika Lab',
                'dars_kunlari' => json_encode(['Dushanba', 'Juma']),
                'dars_vaqti' => '16:00 - 18:00',
                'status' => 'approved',
                'badge' => 'Yangi',
                'views_count' => 156,
                'favorites_count' => 12,
            ],
            [
                'ustoz_id' => 3,
                'fan_id' => 3,
                'sarlavha' => 'Fizika olimpiada tayyorlovi',
                'tavsif' => 'Fizika olimpiadalariga tayyorgarlik. Mexanika, termodinamika va elektrodinamika.',
                'narx' => 180000,
                'joylashuv' => 'Toshkent, Mirzo Ulug\'bek tumani',
                'markaz_nomi' => 'Fizika Pro',
                'dars_kunlari' => json_encode(['Chorshanba', 'Shanba']),
                'dars_vaqti' => '19:00 - 21:00',
                'status' => 'approved',
                'badge' => 'Chegirma',
                'chegirma_foiz' => 15,
                'views_count' => 98,
                'favorites_count' => 10,
            ],
        ];

        foreach ($elonlar as $elon) {
            Elon::create($elon);
        }

        $this->command->info('✅ ' . count($elonlar) . ' ta e\'lon yaratildi');
    }
}
