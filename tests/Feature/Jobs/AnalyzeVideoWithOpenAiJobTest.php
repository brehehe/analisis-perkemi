<?php

use App\Actions\PersistOpenAiAnalysisAction;
use App\Contracts\AnalyzesMatchVideo;
use App\Contracts\ExtractsVideoFrames;
use App\Enums\AnalysisStatus;
use App\Jobs\AnalyzeVideoWithOpenAiJob;
use App\Models\Analysis;
use App\Models\Athlete;
use App\Models\MatchRecord;
use App\Models\Video;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\mock;

it('retries a failed uploaded-video analysis and persists completed results', function () {
    config()->set('services.openai.model', 'gpt-6-sol');
    Storage::fake('local');
    Storage::disk('local')->put('match-videos/test.mp4', 'video-content');
    Storage::disk('local')->put('analysis-frames/frame-001.jpg', 'jpeg-frame');
    $athlete = Athlete::factory()->create();
    $match = MatchRecord::factory()->create([
        'athlete_id' => $athlete->getKey(),
        'status' => 'in_analysis',
    ]);
    $video = Video::factory()->create([
        'match_record_id' => $match->getKey(),
        'source' => 'upload',
        'external_url' => null,
        'storage_path' => 'match-videos/test.mp4',
        'processing_status' => 'failed',
    ]);
    $analysis = Analysis::factory()->create([
        'match_record_id' => $match->getKey(),
        'video_id' => $video->getKey(),
        'athlete_id' => $athlete->getKey(),
        'status' => 'failed',
        'progress' => 85,
        'failed_at' => now(),
        'error_message' => 'Respons sebelumnya tidak lengkap.',
    ]);
    $frame = [
        'index' => 1,
        'timestamp_seconds' => 12.5,
        'absolute_path' => Storage::disk('local')->path('analysis-frames/frame-001.jpg'),
        'mime_type' => 'image/jpeg',
    ];
    $frameExtractor = mock(ExtractsVideoFrames::class);
    $frameExtractor->shouldReceive('extract')
        ->once()
        ->with(Storage::disk('local')->path('match-videos/test.mp4'), $analysis->getKey())
        ->andReturn([$frame]);
    $frameExtractor->shouldReceive('cleanup')
        ->once()
        ->with($analysis->getKey());
    $videoAnalyzer = mock(AnalyzesMatchVideo::class);
    $videoAnalyzer->shouldReceive('analyze')
        ->once()
        ->withArgs(fn (Analysis $receivedAnalysis, array $frames): bool => $receivedAnalysis->is($analysis) && $frames === [$frame])
        ->andReturn(openAiJobResult());

    (new AnalyzeVideoWithOpenAiJob($analysis))->handle(
        $frameExtractor,
        $videoAnalyzer,
        new PersistOpenAiAnalysisAction,
    );

    $analysis->refresh();
    expect($analysis->status)->toBe(AnalysisStatus::Completed)
        ->and($analysis->progress)->toBe(100)
        ->and($analysis->model_version)->toBe('gpt-6-sol')
        ->and($analysis->overall_score)->toBe('84.00')
        ->and($analysis->match_intelligence['executive_summary'])->toBe('Atlet menjaga guard dan jarak dengan cukup baik.')
        ->and($analysis->failed_at)->toBeNull()
        ->and($analysis->error_message)->toBeNull();
    $this->assertDatabaseHas('analysis_metrics', [
        'analysis_id' => $analysis->getKey(),
        'name' => 'Kesiapan guard',
    ]);
    $this->assertDatabaseHas('point_opportunities', [
        'analysis_id' => $analysis->getKey(),
        'occurred_at_ms' => 12500,
    ]);
    $this->assertDatabaseHas('strategies', [
        'analysis_id' => $analysis->getKey(),
        'version' => 1,
    ]);
    $this->assertDatabaseHas('training_recommendations', [
        'analysis_id' => $analysis->getKey(),
        'athlete_id' => $athlete->getKey(),
        'priority' => 'high',
        'drill' => 'Recovery guard setelah serangan',
    ]);
});

it('persists an analysis when OpenAI returns empty evidence collections', function () {
    $athlete = Athlete::factory()->create();
    $match = MatchRecord::factory()->create([
        'athlete_id' => $athlete->getKey(),
        'status' => 'in_analysis',
    ]);
    $video = Video::factory()->create([
        'match_record_id' => $match->getKey(),
        'processing_status' => 'performance_analysis',
    ]);
    $analysis = Analysis::factory()->create([
        'match_record_id' => $match->getKey(),
        'video_id' => $video->getKey(),
        'athlete_id' => $athlete->getKey(),
        'status' => 'strategy_generation',
        'progress' => 85,
    ]);
    $result = openAiJobResult();
    $result['overall_score'] = null;
    $result['metrics'] = [];
    $result['events'] = [];
    $result['opportunities'] = [];
    $result['strategy']['priority_points'] = [];
    $result['training_recommendations'] = [];

    (new PersistOpenAiAnalysisAction)->execute($analysis, $result);

    $analysis->refresh();
    expect($analysis->status)->toBe(AnalysisStatus::Completed)
        ->and($analysis->progress)->toBe(100)
        ->and($analysis->overall_score)->toBeNull()
        ->and($analysis->metrics()->count())->toBe(0)
        ->and($analysis->events()->count())->toBe(0)
        ->and($analysis->opportunities()->count())->toBe(0)
        ->and($analysis->trainingRecommendations()->count())->toBe(0)
        ->and($analysis->strategies()->sole()->priority_points)->toBe([]);
});

it('includes validation details in the final failed-job log', function () {
    Log::spy();
    $athlete = Athlete::factory()->create();
    $match = MatchRecord::factory()->create(['athlete_id' => $athlete->getKey()]);
    $video = Video::factory()->create(['match_record_id' => $match->getKey()]);
    $analysis = Analysis::factory()->create([
        'match_record_id' => $match->getKey(),
        'video_id' => $video->getKey(),
        'athlete_id' => $athlete->getKey(),
    ]);
    $exception = ValidationException::withMessages([
        'metrics' => ['The metrics field must be an array.'],
    ]);

    (new AnalyzeVideoWithOpenAiJob($analysis))->failed($exception);

    Log::shouldHaveReceived('error')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'OpenAI video analysis failed.'
            && $context['validation_errors'] === $exception->errors());
});

it('logs the safe response shape when OpenAI result validation fails', function () {
    Log::spy();
    Storage::fake('local');
    Storage::disk('local')->put('match-videos/invalid-result.mp4', 'video-content');
    Storage::disk('local')->put('analysis-frames/invalid-result.jpg', 'jpeg-frame');
    $athlete = Athlete::factory()->create();
    $match = MatchRecord::factory()->create(['athlete_id' => $athlete->getKey()]);
    $video = Video::factory()->create([
        'match_record_id' => $match->getKey(),
        'storage_path' => 'match-videos/invalid-result.mp4',
    ]);
    $analysis = Analysis::factory()->create([
        'match_record_id' => $match->getKey(),
        'video_id' => $video->getKey(),
        'athlete_id' => $athlete->getKey(),
    ]);
    $frame = [
        'index' => 1,
        'timestamp_seconds' => 5.0,
        'absolute_path' => Storage::disk('local')->path('analysis-frames/invalid-result.jpg'),
        'mime_type' => 'image/jpeg',
    ];
    $frameExtractor = mock(ExtractsVideoFrames::class);
    $frameExtractor->shouldReceive('extract')->once()->andReturn([$frame]);
    $frameExtractor->shouldReceive('cleanup')->once()->with($analysis->getKey());
    $result = openAiJobResult();
    $result['metrics'] = 'invalid';
    $result['events'] = [];
    $result['opportunities'] = [];
    $videoAnalyzer = mock(AnalyzesMatchVideo::class);
    $videoAnalyzer->shouldReceive('analyze')->once()->andReturn($result);

    expect(fn () => (new AnalyzeVideoWithOpenAiJob($analysis))->handle(
        $frameExtractor,
        $videoAnalyzer,
        new PersistOpenAiAnalysisAction,
    ))->toThrow(ValidationException::class);

    Log::shouldHaveReceived('error')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'OpenAI analysis response validation failed.'
            && array_key_exists('metrics', $context['validation_errors'])
            && $context['response_shape']['metrics'] === ['type' => 'string', 'count' => null]
            && $context['response_shape']['events'] === ['type' => 'array', 'count' => 0]);
});

/** @return array<string, mixed> */
function openAiJobResult(): array
{
    return [
        'overall_score' => 84,
        'match_intelligence' => [
            'executive_summary' => 'Atlet menjaga guard dan jarak dengan cukup baik.',
            'analysis_scope' => 'Satu frame sampel dianalisis.',
            'frames_analyzed' => 1,
            'target_identification' => [
                'label' => 'Atlet target',
                'basis' => 'Petunjuk target sesuai tampilan frame.',
                'confidence' => 0.9,
                'caveat' => 'Identifikasi perlu divalidasi pelatih.',
            ],
            'strengths' => [[
                'title' => 'Guard stabil',
                'detail' => 'Posisi tangan kembali ke garis tengah.',
                'confidence' => 0.82,
                'evidence' => ['Guard terlihat pada frame 1.'],
            ]],
            'weaknesses' => [],
            'athlete_profile' => [[
                'aspect' => 'Kamae',
                'assessment' => 'Posisi guard terlihat siap.',
                'confidence' => 0.82,
                'evidence' => ['Frame 1 menunjukkan guard.'],
            ]],
            'opponent_profile' => [],
            'match_dynamics' => [[
                'phase' => 'Pertengahan',
                'start_timestamp_seconds' => 10,
                'end_timestamp_seconds' => 15,
                'momentum' => 'Netral',
                'athlete_actions' => ['Memulihkan guard.'],
                'opponent_actions' => ['Menjaga jarak.'],
                'coaching_note' => 'Pertahankan disiplin jarak.',
            ]],
            'risk_flags' => [],
            'limitations' => ['Analisis berasal dari frame sampel.'],
        ],
        'metrics' => [[
            'category' => 'technical',
            'name' => 'Kesiapan guard',
            'score' => 82,
            'evidence' => ['Guard stabil.'],
        ]],
        'events' => [[
            'event_type' => 'guard_recovery',
            'frame_index' => 1,
            'timestamp_seconds' => 12.5,
            'confidence' => 0.81,
            'title' => 'Pemulihan guard',
            'description' => 'Atlet kembali ke guard.',
            'evidence' => ['Tangan melindungi garis tengah.'],
        ]],
        'opportunities' => [[
            'opportunity_type' => 'counter_window',
            'frame_index' => 1,
            'timestamp_seconds' => 12.5,
            'confidence' => 0.76,
            'trigger' => 'Ruang terbuka.',
            'explanation' => 'Terlihat jalur counter.',
            'recommended_action' => 'Latih counter langsung.',
            'evidence' => ['Jarak memungkinkan counter.'],
        ]],
        'strategy' => [
            'summary' => 'Pertahankan guard dan kontrol jarak.',
            'attack_strategy' => 'Gunakan entry terukur.',
            'counter_strategy' => 'Counter pada garis tengah.',
            'defensive_strategy' => 'Pulihkan guard.',
            'what_to_avoid' => 'Hindari entry tanpa kontrol.',
            'priority_points' => ['Guard', 'Jarak'],
        ],
        'training_recommendations' => [[
            'priority' => 'high',
            'drill' => 'Recovery guard setelah serangan',
            'frequency' => '3 sesi per minggu',
            'duration_minutes' => 20,
            'target_metric' => 'Kesiapan guard',
            'target_score' => 90,
        ]],
    ];
}
