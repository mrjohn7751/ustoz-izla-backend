<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Video;
use App\Models\User;
use App\Models\Fan;
use Illuminate\Support\Facades\DB;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get ustozlar (teachers)
        $ustozlar = User::where('role', 'ustoz')->get();

        if ($ustozlar->isEmpty()) {
            $this->command->error('❌ Ustoz topilmadi! Avval UstozSeeder ni ishga tushiring.');
            return;
        }

        // Get fanlar (subjects)
        $fanlar = Fan::all();

        if ($fanlar->isEmpty()) {
            $this->command->error('❌ Fanlar topilmadi! Avval FanSeeder ni ishga tushiring.');
            return;
        }

        $this->command->info('📹 Video seeder boshlandi...');

        // Get specific subjects
        $matematika = $fanlar->where('nomi', 'Matematika')->first();
        $ingliz = $fanlar->where('nomi', 'Ingliz tili')->first();
        $fizika = $fanlar->where('nomi', 'Fizika')->first();
        $kimyo = $fanlar->where('nomi', 'Kimyo')->first();
        $informatika = $fanlar->where('nomi', 'Informatika')->first();

        // Use first ustoz
        $ustoz = $ustozlar->first();

        // Test videos data
        $videos = [
            // Matematika
            [
                'ustoz_id' => $ustoz->id,
                'fan_id' => $matematika?->id ?? 1,
                'sarlavha' => 'Kvadrat tenglama yechimlari',
                'tavsif' => 'Bu videoda kvadrat tenglamalarni yechish usullari batafsil tushuntiriladi. Diskriminant formulasi va uning qo\'llanilishi.',
                'video_url' => 'videos/kvadrat_tenglama.mp4',
                'thumbnail' => 'thumbnails/math_kvadrat.jpg',
                'davomiyligi' => 1200, // 20 daqiqa
                'status' => 'approved',
                'views_count' => 245,
                'likes_count' => 38,
            ],
            [
                'ustoz_id' => $ustoz->id,
                'fan_id' => $matematika?->id ?? 1,
                'sarlavha' => 'Trigonometriya asoslari',
                'tavsif' => 'Sinus, kosinus, tangens va kotangens funksiyalari. Trigonometrik ayniyatlar va ularning isboti.',
                'video_url' => 'videos/trigonometriya.mp4',
                'thumbnail' => 'thumbnails/math_trig.jpg',
                'davomiyligi' => 1800, // 30 daqiqa
                'status' => 'approved',
                'views_count' => 167,
                'likes_count' => 29,
            ],
            [
                'ustoz_id' => $ustoz->id,
                'fan_id' => $matematika?->id ?? 1,
                'sarlavha' => 'Logarifmlar va ularning xossalari',
                'tavsif' => 'Logarifm tushunchasi, logarifmlarning asosiy xossalari va logarifmik tenglamalarni yechish.',
                'video_url' => 'videos/logarifm.mp4',
                'thumbnail' => 'thumbnails/math_log.jpg',
                'davomiyligi' => 1500, // 25 daqiqa
                'status' => 'approved',
                'views_count' => 198,
                'likes_count' => 34,
            ],

            // Ingliz tili
            [
                'ustoz_id' => $ustoz->id,
                'fan_id' => $ingliz?->id ?? 2,
                'sarlavha' => 'Present Perfect Tense',
                'tavsif' => 'Present Perfect Tense grammatikasi va amaliy misollar. Hozirgi tugallangan zamon qanday ishlatiladi.',
                'video_url' => 'videos/present_perfect.mp4',
                'thumbnail' => 'thumbnails/english_perfect.jpg',
                'davomiyligi' => 900, // 15 daqiqa
                'status' => 'approved',
                'views_count' => 321,
                'likes_count' => 52,
            ],
            [
                'ustoz_id' => $ustoz->id,
                'fan_id' => $ingliz?->id ?? 2,
                'sarlavha' => 'Irregular Verbs - Qoidabuzar fe\'llar',
                'tavsif' => 'Eng ko\'p ishlatiladigan 100 ta irregular verbs va ularning ishlatilishi. Amaliy mashqlar bilan.',
                'video_url' => 'videos/irregular_verbs.mp4',
                'thumbnail' => 'thumbnails/english_verbs.jpg',
                'davomiyligi' => 600, // 10 daqiqa
                'status' => 'approved',
                'views_count' => 489,
                'likes_count' => 83,
            ],
            [
                'ustoz_id' => $ustoz->id,
                'fan_id' => $ingliz?->id ?? 2,
                'sarlavha' => 'Conditionals - Shartli gaplar',
                'tavsif' => 'Zero, First, Second va Third Conditional. Har bir conditional turi va ishlatilish holatlari.',
                'video_url' => 'videos/conditionals.mp4',
                'thumbnail' => 'thumbnails/english_cond.jpg',
                'davomiyligi' => 1350, // 22.5 daqiqa
                'status' => 'approved',
                'views_count' => 276,
                'likes_count' => 41,
            ],

            // Fizika
            [
                'ustoz_id' => $ustoz->id,
                'fan_id' => $fizika?->id ?? 3,
                'sarlavha' => 'Nyuton qonunlari',
                'tavsif' => 'Nyutonning uchta harakatlanish qonuni va ularning kundalik hayotda qo\'llanilishi. Amaliy misollar.',
                'video_url' => 'videos/newton_laws.mp4',
                'thumbnail' => 'thumbnails/physics_newton.jpg',
                'davomiyligi' => 1500, // 25 daqiqa
                'status' => 'approved',
                'views_count' => 312,
                'likes_count' => 56,
            ],
            [
                'ustoz_id' => $ustoz->id,
                'fan_id' => $fizika?->id ?? 3,
                'sarlavha' => 'Elektr toki va qarshilik',
                'tavsif' => 'Elektr toki nima? Om qonuni va uning amaliy ishlatilishi. Ketma-ket va parallel ulash.',
                'video_url' => 'videos/electric_current.mp4',
                'thumbnail' => 'thumbnails/physics_electric.jpg',
                'davomiyligi' => 1200, // 20 daqiqa
                'status' => 'approved',
                'views_count' => 234,
                'likes_count' => 38,
            ],

            // Kimyo
            [
                'ustoz_id' => $ustoz->id,
                'fan_id' => $kimyo?->id ?? 4,
                'sarlavha' => 'Atom tuzilishi',
                'tavsif' => 'Atom tuzilishi, elementar zarralar, elektronlar va ularning taqsimlanishi.',
                'video_url' => 'videos/atom_structure.mp4',
                'thumbnail' => 'thumbnails/chem_atom.jpg',
                'davomiyligi' => 1080, // 18 daqiqa
                'status' => 'approved',
                'views_count' => 189,
                'likes_count' => 31,
            ],

            // Informatika
            [
                'ustoz_id' => $ustoz->id,
                'fan_id' => $informatika?->id ?? 5,
                'sarlavha' => 'Python dasturlash asoslari',
                'tavsif' => 'Python dasturlash tilining asoslari. O\'zgaruvchilar, ma\'lumot turlari va asosiy operatsiyalar.',
                'video_url' => 'videos/python_basics.mp4',
                'thumbnail' => 'thumbnails/info_python.jpg',
                'davomiyligi' => 2100, // 35 daqiqa
                'status' => 'approved',
                'views_count' => 567,
                'likes_count' => 98,
            ],
        ];

        // Insert videos
        foreach ($videos as $videoData) {
            Video::create($videoData);
            $this->command->info('  ✅ Video yaratildi: ' . $videoData['sarlavha']);
        }

        $this->command->info('');
        $this->command->info('🎉 Jami ' . count($videos) . ' ta video yaratildi!');
        $this->command->info('📊 Status: Barcha videolar tasdiqlangan (approved)');
    }
}
