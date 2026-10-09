<?php

namespace Database\Seeders;

use App\Models\Analysis;
use App\Models\Athlete;
use App\Models\Club;
use App\Models\Coach;
use App\Models\CompetitionEvent;
use App\Models\MatchRecord;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmartPerkemiSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $club = Club::query()->firstOrCreate(
                ['name' => 'Dojo Nusantara Jakarta'],
                ['city' => 'Jakarta', 'province' => 'DKI Jakarta', 'status' => 'active'],
            );

            $coachUser = User::query()->firstOrCreate(
                ['email' => 'pelatih@perkemi.test'],
                ['name' => 'Pelatih Utama', 'password' => 'password'],
            );
            $coachUser->syncRoles('coach');

            $coach = Coach::query()->firstOrCreate(
                ['identifier' => 'PLT-0001'],
                [
                    'user_id' => $coachUser->getKey(),
                    'club_id' => $club->getKey(),
                    'name' => $coachUser->name,
                    'email' => $coachUser->email,
                    'certifications' => 'Pelatih Kempo tingkat nasional',
                    'status' => 'active',
                ],
            );

            $athletes = collect([
                ['identifier' => 'ATL-1001', 'name' => 'Raka Pratama', 'gender' => 'male', 'category' => 'Randori Putra', 'weight_class' => 60],
                ['identifier' => 'ATL-1002', 'name' => 'Nadia Maharani', 'gender' => 'female', 'category' => 'Randori Putri', 'weight_class' => 55],
                ['identifier' => 'ATL-1003', 'name' => 'Bima Santoso', 'gender' => 'male', 'category' => 'Embu Pasangan', 'weight_class' => 65],
            ])->map(fn (array $data): Athlete => Athlete::query()->firstOrCreate(
                ['identifier' => $data['identifier']],
                [
                    ...$data,
                    'club_id' => $club->getKey(),
                    'coach_id' => $coach->getKey(),
                    'date_of_birth' => '2003-04-12',
                    'experience_years' => 6,
                    'status' => 'active',
                ],
            ));

            $event = CompetitionEvent::query()->firstOrCreate(
                ['name' => 'Kejurnas Kempo 2026'],
                [
                    'starts_at' => '2026-09-20',
                    'ends_at' => '2026-09-22',
                    'venue' => 'GOR Cendrawasih',
                    'city' => 'Jakarta',
                    'level' => 'national',
                    'status' => 'completed',
                ],
            );

            $match = MatchRecord::query()->firstOrCreate(
                [
                    'athlete_id' => $athletes[0]->getKey(),
                    'opponent_name' => 'Dimas Arya',
                    'match_date' => '2026-09-21 14:30:00',
                ],
                [
                    'competition_event_id' => $event->getKey(),
                    'opponent_club' => 'Dojo Garuda Bandung',
                    'category' => 'Randori Putra',
                    'match_type' => 'randori',
                    'division' => 'male',
                    'result' => 'win',
                    'athlete_score' => 5,
                    'opponent_score' => 3,
                    'status' => 'completed',
                ],
            );
            $match->syncTeamMembers([$athletes[0]->getKey()]);

            $video = Video::query()->firstOrCreate(
                ['match_record_id' => $match->getKey(), 'source' => 'youtube'],
                [
                    'external_url' => 'https://www.youtube.com/watch?v=YoZGDqWRLUo',
                    'processing_status' => 'completed',
                    'duration_seconds' => 327,
                ],
            );

            $analysis = Analysis::query()->firstOrCreate(
                ['video_id' => $video->getKey()],
                [
                    'match_record_id' => $match->getKey(),
                    'athlete_id' => $athletes[0]->getKey(),
                    'status' => 'completed',
                    'progress' => 100,
                    'current_step' => 'Analisis selesai dan siap divalidasi',
                    'model_version' => 'baseline-1.0.0',
                    'overall_score' => 82,
                    'started_at' => '2026-09-21 16:00:00',
                    'completed_at' => '2026-09-21 16:08:00',
                ],
            );

            foreach ([
                ['category' => 'technical', 'name' => 'Timing', 'score' => 88],
                ['category' => 'tactical', 'name' => 'Kontrol ma-ai', 'score' => 84],
                ['category' => 'technical', 'name' => 'Respons counter', 'score' => 86],
                ['category' => 'defensive', 'name' => 'Recovery', 'score' => 73],
            ] as $metric) {
                $analysis->metrics()->firstOrCreate(
                    ['category' => $metric['category'], 'name' => $metric['name']],
                    ['score' => $metric['score']],
                );
            }

            $analysis->opportunities()->firstOrCreate(
                ['occurred_at_ms' => 66000, 'opportunity_type' => 'counter_window'],
                [
                    'confidence' => 0.91,
                    'trigger' => 'Pemulihan guard lawan terlambat setelah entry.',
                    'explanation' => 'Jarak dan arah gerak memberi ruang untuk counter setelah entry lawan.',
                    'recommended_action' => 'Geser lateral dan lakukan counter pada fase recovery.',
                    'validation_status' => 'pending',
                ],
            );
        });
    }
}
