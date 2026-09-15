<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\Engagement\Mission\Application\Services\MissionService;
use App\Domains\Engagement\Mission\Infrastructure\Persistence\Models\MissionModel;
use Illuminate\Database\Seeder;

/**
 * Membuat misi harian (daily missions) untuk tanggal berjalan.
 * Misi hanya aktif pada hari dibuat, sehingga menjalankan seeder lagi
 * keesokan hari akan menghasilkan misi baru tanggal tersebut.
 */
final class DailyMissionSeeder extends Seeder
{
    public function run(): void
    {
        $date = now()->format('Ymd');

        $missions = [
            [
                'code' => "DAILY_{$date}_PLAY_3",
                'name' => 'Main Game 3x Hari Ini',
                'description' => 'Mainkan game edukasi mana pun sebanyak 3 kali hari ini.',
                'event_type' => 'game_completed',
                'target_value' => 3,
            ],
            [
                'code' => "DAILY_{$date}_QUIZ",
                'name' => 'Selesaikan 1 Kuis SDG',
                'description' => 'Selesaikan satu sesi Kuis SDG sampai tuntas.',
                'event_type' => 'quiz_completed',
                'target_value' => 1,
            ],
            [
                'code' => "DAILY_{$date}_TRASH",
                'name' => 'Selesaikan 1 Trash Sort',
                'description' => 'Selesaikan satu sesi permainan pilah sampah.',
                'event_type' => 'trash_sort_completed',
                'target_value' => 1,
            ],
            [
                'code' => "DAILY_{$date}_MATCH",
                'name' => 'Pasangkan 1 Kartu SDG',
                'description' => 'Selesaikan satu sesi Match Card SDG.',
                'event_type' => 'match_card_completed',
                'target_value' => 1,
            ],
            [
                'code' => "DAILY_{$date}_VERIFIED",
                'name' => 'Tantangan Game Tervalidasi',
                'description' => 'Selesaikan satu permainan tervalidasi server (Arithmetic Kilat atau Sudoku).',
                'event_type' => 'game.*',
                'target_value' => 1,
            ],
        ];

        $startsAt = now()->startOfDay()->subMinutes(1);
        $endsAt = now()->addDay()->startOfDay()->addMinutes(1);

        foreach ($missions as $mission) {
            MissionModel::updateOrCreate(
                ['code' => $mission['code']],
                [
                    'name' => $mission['name'],
                    'description' => $mission['description'],
                    'event_type' => $mission['event_type'],
                    'target_value' => $mission['target_value'],
                    'conditions' => null,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'is_active' => true,
                ],
            );
        }

        $this->command?->info(sprintf(
            'Misi harian %s dibuat: %d misi (event types disokong: %s).',
            $date,
            count($missions),
            implode(', ', MissionService::GAME_COMPLETION_EVENTS),
        ));
    }
}