<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Engagement\Gaming\Infrastructure\Persistence\Models\GameContentModel;
use Illuminate\Database\Seeder;

/**
 * Memuat konten game Android (soal quiz, myth & fact, item pilah sampah,
 * dan dek Match Card SDG) ke tabel game_contents sehingga tidak lagi
 * dikirim sebagai "dummy data" dari dalam aplikasi.
 */
final class GameContentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = array_merge(
            $this->quizQuestions(),
            $this->mythFactStatements(),
            $this->trashItems(),
            [$this->matchCardDeck()],
        );

        foreach ($rows as $row) {
            GameContentModel::updateOrCreate(
                [
                    'game_type' => $row['game_type'],
                    'title' => $row['title'],
                ],
                [
                    'difficulty' => $row['difficulty'],
                    'payload' => $row['payload'],
                    'is_active' => true,
                ],
            );
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function quizQuestions(): array
    {
        $data = [
            ['question' => 'SDG nomor berapa yang membahas Aksi Iklim?', 'options' => ['SDG 6', 'SDG 13', 'SDG 4', 'SDG 1'], 'correct_answer' => 'SDG 13', 'difficulty' => 'Mudah', 'explanation' => 'Aksi Iklim adalah SDG 13.'],
            ['question' => 'Apa tujuan utama SDG 4?', 'options' => ['Pendidikan berkualitas', 'Energi bersih', 'Kesehatan', 'Infrastruktur'], 'correct_answer' => 'Pendidikan berkualitas', 'difficulty' => 'Mudah', 'explanation' => 'SDG 4 fokus pada pendidikan berkualitas untuk semua.'],
            ['question' => 'Mengurangi plastik sekali pakai mendukung SDG apa?', 'options' => ['SDG 12', 'SDG 9', 'SDG 5', 'SDG 16'], 'correct_answer' => 'SDG 12', 'difficulty' => 'Mudah', 'explanation' => 'SDG 12 adalah konsumsi dan produksi yang bertanggung jawab.'],
            ['question' => 'Hemat air mendukung SDG apa?', 'options' => ['SDG 6', 'SDG 10', 'SDG 2', 'SDG 14'], 'correct_answer' => 'SDG 6', 'difficulty' => 'Mudah', 'explanation' => 'SDG 6 berfokus pada air bersih dan sanitasi.'],
            ['question' => 'SDG 7 membahas tentang apa?', 'options' => ['Energi bersih dan terjangkau', 'Pekerjaan layak', 'Inovasi', 'Keamanan'], 'correct_answer' => 'Energi bersih dan terjangkau', 'difficulty' => 'Mudah', 'explanation' => 'SDG 7 fokus pada akses energi bersih dan terjangkau.'],
            ['question' => 'SDG 3 berhubungan dengan?', 'options' => ['Kesehatan', 'Pendidikan', 'Air', 'Iklim'], 'correct_answer' => 'Kesehatan', 'difficulty' => 'Mudah', 'explanation' => 'SDG 3 tentang kehidupan sehat dan sejahtera.'],
            ['question' => 'Contoh aksi SDG 11 adalah?', 'options' => ['Pilah sampah', 'Menanam pohon', 'Belajar', 'Berhemat energi'], 'correct_answer' => 'Pilah sampah', 'difficulty' => 'Sedang', 'explanation' => 'SDG 11 terkait kota dan permukiman berkelanjutan.'],
            ['question' => 'SDG yang fokus pada kesetaraan gender adalah?', 'options' => ['SDG 5', 'SDG 8', 'SDG 2', 'SDG 12'], 'correct_answer' => 'SDG 5', 'difficulty' => 'Sedang', 'explanation' => 'SDG 5 membahas kesetaraan gender.'],
            ['question' => 'Mengurangi food waste termasuk SDG?', 'options' => ['SDG 12', 'SDG 15', 'SDG 3', 'SDG 1'], 'correct_answer' => 'SDG 12', 'difficulty' => 'Sedang', 'explanation' => 'Food waste terkait konsumsi dan produksi bertanggung jawab.'],
            ['question' => 'SDG 14 berfokus pada?', 'options' => ['Ekosistem laut', 'Energi', 'Pendidikan', 'Kesetaraan'], 'correct_answer' => 'Ekosistem laut', 'difficulty' => 'Sedang', 'explanation' => 'SDG 14 adalah kehidupan bawah laut.'],
            ['question' => 'SDG 15 membahas tentang?', 'options' => ['Ekosistem darat', 'Iklim', 'Kesehatan', 'Pekerjaan'], 'correct_answer' => 'Ekosistem darat', 'difficulty' => 'Sedang', 'explanation' => 'SDG 15 fokus pada ekosistem darat.'],
            ['question' => 'SDG 8 berkaitan dengan?', 'options' => ['Pekerjaan layak dan pertumbuhan ekonomi', 'Air bersih', 'Energi', 'Infrastruktur'], 'correct_answer' => 'Pekerjaan layak dan pertumbuhan ekonomi', 'difficulty' => 'Sedang', 'explanation' => 'SDG 8 menekankan pekerjaan layak dan pertumbuhan ekonomi.'],
            ['question' => 'SDG 10 bertujuan untuk?', 'options' => ['Mengurangi kesenjangan', 'Menghapus kemiskinan', 'Aksi iklim', 'Kota berkelanjutan'], 'correct_answer' => 'Mengurangi kesenjangan', 'difficulty' => 'Sedang', 'explanation' => 'SDG 10 tentang mengurangi ketimpangan.'],
            ['question' => 'SDG 16 membahas tentang?', 'options' => ['Perdamaian dan keadilan', 'Energi', 'Pendidikan', 'Kesehatan'], 'correct_answer' => 'Perdamaian dan keadilan', 'difficulty' => 'Sulit', 'explanation' => 'SDG 16 fokus pada perdamaian, keadilan, dan kelembagaan yang kuat.'],
            ['question' => 'SDG 9 berfokus pada?', 'options' => ['Industri, inovasi, dan infrastruktur', 'Air bersih', 'Iklim', 'Kesehatan'], 'correct_answer' => 'Industri, inovasi, dan infrastruktur', 'difficulty' => 'Sulit', 'explanation' => 'SDG 9 tentang industri, inovasi, dan infrastruktur.'],
            ['question' => 'SDG 2 bertujuan untuk?', 'options' => ['Tanpa kelaparan', 'Kehidupan laut', 'Energi bersih', 'Kota berkelanjutan'], 'correct_answer' => 'Tanpa kelaparan', 'difficulty' => 'Sulit', 'explanation' => 'SDG 2 adalah tanpa kelaparan.'],
            ['question' => 'SDG 1 fokus pada?', 'options' => ['Tanpa kemiskinan', 'Pendidikan', 'Air bersih', 'Kesehatan'], 'correct_answer' => 'Tanpa kemiskinan', 'difficulty' => 'Sulit', 'explanation' => 'SDG 1 adalah tanpa kemiskinan.'],
            ['question' => 'Contoh aksi SDG 7 yang paling tepat?', 'options' => ['Mematikan listrik saat tidak digunakan', 'Menanam pohon', 'Membaca buku', 'Membuang sampah'], 'correct_answer' => 'Mematikan listrik saat tidak digunakan', 'difficulty' => 'Sulit', 'explanation' => 'Hemat listrik mendukung energi bersih dan terjangkau.'],
            ['question' => 'SDG yang terkait konsumsi bertanggung jawab adalah?', 'options' => ['SDG 12', 'SDG 3', 'SDG 14', 'SDG 11'], 'correct_answer' => 'SDG 12', 'difficulty' => 'Sulit', 'explanation' => 'SDG 12 adalah konsumsi dan produksi yang bertanggung jawab.'],
            ['question' => 'Menggunakan transportasi umum mendukung SDG?', 'options' => ['SDG 11', 'SDG 5', 'SDG 2', 'SDG 6'], 'correct_answer' => 'SDG 11', 'difficulty' => 'Sedang', 'explanation' => 'Transportasi umum membantu kota berkelanjutan.'],
        ];

        return array_map(
            static fn (array $q): array => [
                'game_type' => 'quiz',
                'title' => $q['question'],
                'difficulty' => $q['difficulty'],
                'payload' => $q,
            ],
            $data,
        );
    }

    /** @return array<int, array<string, mixed>> */
    private function mythFactStatements(): array
    {
        $data = [
            ['statement' => 'Plastik bisa membutuhkan hingga 500 tahun untuk terurai di laut.', 'is_fact' => true],
            ['statement' => 'Semua plastik dapat terurai dalam waktu satu tahun.', 'is_fact' => false],
            ['statement' => 'Kaca bisa didaur ulang berulang kali tanpa kehilangan kualitas.', 'is_fact' => true],
            ['statement' => 'Semua program daur ulang menerima botol plastik.', 'is_fact' => false],
            ['statement' => 'Sampah elektronik termasuk jenis sampah yang paling cepat meningkat.', 'is_fact' => true],
            ['statement' => 'Kulit pisang membutuhkan ratusan tahun untuk terurai.', 'is_fact' => false],
            ['statement' => 'Mendaur ulang aluminium menghemat jauh lebih banyak energi dibanding membuat yang baru.', 'is_fact' => true],
        ];

        return array_map(
            static fn (array $item): array => [
                'game_type' => 'myth_fact',
                'title' => $item['statement'],
                'difficulty' => null,
                'payload' => $item,
            ],
            $data,
        );
    }

    /** @return array<int, array<string, mixed>> */
    private function trashItems(): array
    {
        $bins = [
            'Botol plastik' => ['Kuning', 'Plastik', 'botolplastik'],
            'Kantong plastik' => ['Kuning', 'Plastik', 'kresek'],
            'Gelas plastik' => ['Kuning', 'Plastik', 'gelasplastik'],
            'Baterai' => ['Merah', 'B3', 'baterai'],
            'Lampu rusak' => ['Merah', 'B3', 'lampurusak'],
            'Kaleng cat' => ['Merah', 'B3', 'kalengcat'],
            'Kulit pisang' => ['Hijau', 'Organik', 'kulitpisang'],
            'Sisa sayur' => ['Hijau', 'Organik', 'sayur'],
            'Daun kering' => ['Hijau', 'Organik', 'daunkering'],
            'Koran bekas' => ['Biru', 'Kertas', 'koran'],
            'Kardus' => ['Biru', 'Kertas', 'kardus'],
            'Buku rusak' => ['Biru', 'Kertas', 'buku'],
            'Popok sekali pakai' => ['Abu', 'Residu', 'popok'],
            'Puntung rokok' => ['Abu', 'Residu', 'puntungrokok'],
            'Masker sekali pakai' => ['Abu', 'Residu', 'masker'],
        ];

        $rows = [];
        foreach ($bins as $name => [$bin, $category, $imageKey]) {
            $rows[] = [
                'game_type' => 'trash_sort',
                'title' => $name,
                'difficulty' => null,
                'payload' => [
                    'name' => $name,
                    'bin_color' => $bin,
                    'category' => $category,
                    'image_key' => $imageKey,
                ],
            ];
        }

        return $rows;
    }

    /** @return array<string, mixed> */
    private function matchCardDeck(): array
    {
        $goals = [
            1 => 'Tanpa Kemiskinan',
            2 => 'Tanpa Kelaparan',
            3 => 'Kehidupan Sehat dan Sejahtera',
            4 => 'Pendidikan Berkualitas',
            5 => 'Kesetaraan Gender',
            6 => 'Air Bersih dan Sanitasi Layak',
            7 => 'Energi Bersih dan Terjangkau',
            8 => 'Pekerjaan Layak dan Pertumbuhan Ekonomi',
            9 => 'Industri, Inovasi, dan Infrastruktur',
            10 => 'Berkurangnya Kesenjangan',
            11 => 'Kota dan Permukiman Berkelanjutan',
            12 => 'Konsumsi dan Produksi Bertanggung Jawab',
            13 => 'Penanganan Perubahan Iklim',
            14 => 'Ekosistem Lautan',
            15 => 'Ekosistem Daratan',
            16 => 'Perdamaian, Keadilan, dan Kelembagaan Tangguh',
            17 => 'Kemitraan untuk Mencapai Tujuan',
        ];

        $seeds = [
            1 => [
                'statement' => 'Program bantuan tunai untuk keluarga miskin.',
                'problem' => 'Akses pendapatan stabil masih rendah di banyak wilayah.',
                'solution' => 'Perluas program perlindungan sosial dan pelatihan kerja.',
            ],
            2 => [
                'statement' => 'Inisiatif pangan lokal untuk mengurangi kelaparan.',
                'problem' => 'Distribusi pangan belum merata dan harga sering naik.',
                'solution' => 'Perkuat lumbung pangan komunitas dan rantai pasok lokal.',
            ],
            3 => [
                'statement' => 'Kampanye imunisasi dan layanan kesehatan dasar.',
                'problem' => 'Fasilitas kesehatan di daerah terpencil terbatas.',
                'solution' => 'Tambah klinik mobile dan tenaga kesehatan desa.',
            ],
            4 => [
                'statement' => 'Pembangunan sekolah dan pelatihan guru.',
                'problem' => 'Kualitas belajar tidak merata di sekolah terpencil.',
                'solution' => 'Sediakan pelatihan rutin dan akses materi digital.',
            ],
            5 => [
                'statement' => 'Kebijakan upah setara untuk perempuan.',
                'problem' => 'Kesenjangan upah dan peluang karier masih tinggi.',
                'solution' => 'Terapkan audit upah dan kebijakan anti diskriminasi.',
            ],
            6 => [
                'statement' => 'Penyediaan air bersih dan toilet layak.',
                'problem' => 'Sumber air bersih belum tersedia di semua desa.',
                'solution' => 'Bangun infrastruktur air dan sanitasi berbasis komunitas.',
            ],
            7 => [
                'statement' => 'Pengembangan energi surya untuk desa.',
                'problem' => 'Akses listrik stabil masih terbatas di wilayah terpencil.',
                'solution' => 'Perluas energi terbarukan dan mikrogrid desa.',
            ],
            8 => [
                'statement' => 'Pelatihan keterampilan kerja bagi pemuda.',
                'problem' => 'Pengangguran muda masih tinggi di beberapa daerah.',
                'solution' => 'Perkuat pelatihan vokasi dan kemitraan industri lokal.',
            ],
            9 => [
                'statement' => 'Pembangunan jaringan transportasi dan inovasi teknologi.',
                'problem' => 'Infrastruktur dan riset belum merata antar daerah.',
                'solution' => 'Investasi infrastruktur dan dukung pusat inovasi regional.',
            ],
            10 => [
                'statement' => 'Program inklusi untuk kelompok rentan.',
                'problem' => 'Kelompok rentan sering terpinggirkan dari layanan publik.',
                'solution' => 'Perluas akses pendidikan, kesehatan, dan layanan keuangan.',
            ],
            11 => [
                'statement' => 'Transportasi publik yang ramah lingkungan.',
                'problem' => 'Kemacetan dan polusi udara meningkat di kota besar.',
                'solution' => 'Kembangkan transportasi massal dan ruang hijau kota.',
            ],
            12 => [
                'statement' => 'Pengurangan sampah plastik sekali pakai.',
                'problem' => 'Sampah sulit terurai masih mendominasi TPA.',
                'solution' => 'Dorong pemilahan sampah dan penggunaan ulang.',
            ],
            13 => [
                'statement' => 'Aksi pengurangan emisi karbon.',
                'problem' => 'Emisi dari transportasi dan industri terus naik.',
                'solution' => 'Percepat transisi energi bersih dan efisiensi energi.',
            ],
            14 => [
                'statement' => 'Perlindungan terumbu karang dan laut.',
                'problem' => 'Pencemaran laut dan penangkapan berlebih merusak ekosistem.',
                'solution' => 'Terapkan kawasan konservasi dan pengawasan penangkapan.',
            ],
            15 => [
                'statement' => 'Rehabilitasi hutan dan konservasi satwa.',
                'problem' => 'Deforestasi dan kebakaran hutan mengurangi keanekaragaman hayati.',
                'solution' => 'Perkuat restorasi hutan dan patroli kawasan lindung.',
            ],
            16 => [
                'statement' => 'Transparansi hukum dan akses keadilan.',
                'problem' => 'Kepercayaan publik menurun karena proses hukum lambat.',
                'solution' => 'Perbaiki layanan hukum dan tingkatkan transparansi proses.',
            ],
            17 => [
                'statement' => 'Kerja sama global untuk pendanaan SDGs.',
                'problem' => 'Kesenjangan pendanaan menghambat program pembangunan.',
                'solution' => 'Perkuat kemitraan dan skema pendanaan lintas sektor.',
            ],
        ];

        $goalRows = [];
        foreach ($goals as $number => $name) {
            $goalRows[] = ['goal_number' => $number, 'name' => $name];
        }

        $statementRows = [];
        $id = 1;
        foreach ($seeds as $goalId => $seed) {
            $statementRows[] = ['id' => $id++, 'text' => "Pernyataan: {$seed['statement']}", 'goal_id' => $goalId];
            $statementRows[] = ['id' => $id++, 'text' => "Masalah: {$seed['problem']}", 'goal_id' => $goalId];
            $statementRows[] = ['id' => $id++, 'text' => "Solusi: {$seed['solution']}", 'goal_id' => $goalId];
        }

        return [
            'game_type' => 'match_card',
            'title' => 'Dek Match Card SDG',
            'difficulty' => null,
            'payload' => ['goals' => $goalRows, 'statements' => $statementRows],
        ];
    }
}
