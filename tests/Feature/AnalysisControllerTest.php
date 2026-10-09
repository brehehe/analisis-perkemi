<?php

use App\Models\Analysis;
use App\Models\Athlete;
use App\Models\MatchRecord;
use App\Models\Video;
use Inertia\Testing\AssertableInertia as Assert;

it('renders detailed match intelligence and training recommendations', function () {
    $user = userWithPermissions(['analysis.view', 'analysis.validate']);
    $athlete = Athlete::factory()->create();
    $match = MatchRecord::factory()->create(['athlete_id' => $athlete->getKey()]);
    $video = Video::factory()->create([
        'match_record_id' => $match->getKey(),
        'focus_description' => 'Atlet sudut merah.',
    ]);
    $analysis = Analysis::factory()->create([
        'match_record_id' => $match->getKey(),
        'video_id' => $video->getKey(),
        'athlete_id' => $athlete->getKey(),
        'status' => 'completed',
        'match_intelligence' => [
            'executive_summary' => 'Atlet memiliki guard yang stabil.',
            'analysis_scope' => 'Dua puluh empat frame sampel dianalisis.',
            'frames_analyzed' => 24,
            'target_identification' => [
                'label' => 'Atlet sudut merah',
                'basis' => 'Warna pelindung sesuai petunjuk.',
                'confidence' => 0.92,
                'caveat' => 'Tetap perlu validasi pelatih.',
            ],
            'strengths' => [],
            'weaknesses' => [],
            'athlete_profile' => [],
            'opponent_profile' => [],
            'match_dynamics' => [],
            'risk_flags' => [],
            'limitations' => [],
        ],
    ]);
    $analysis->trainingRecommendations()->create([
        'athlete_id' => $athlete->getKey(),
        'priority' => 'high',
        'drill' => 'Recovery guard setelah serangan',
        'frequency' => '3 sesi per minggu',
        'duration_minutes' => 20,
        'target_metric' => 'Kesiapan guard',
        'target_score' => 90,
    ]);

    $this->actingAs($user)
        ->get("/analyses/{$analysis->getKey()}")
        ->assertInertia(fn (Assert $page) => $page
            ->component('Analysis/Show')
            ->where('analysis.video.focus_description', 'Atlet sudut merah.')
            ->where('analysis.match_intelligence.frames_analyzed', 24)
            ->where('analysis.match_intelligence.executive_summary', 'Atlet memiliki guard yang stabil.')
            ->has('analysis.training_recommendations', 1, fn (Assert $recommendation) => $recommendation
                ->where('priority', 'high')
                ->where('drill', 'Recovery guard setelah serangan')
                ->etc())
            ->where('can.validate', true));
});
