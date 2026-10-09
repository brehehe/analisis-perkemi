<?php

use App\Models\Analysis;
use App\Models\Athlete;
use App\Models\MatchRecord;
use App\Models\Video;
use App\Services\OpenAiVideoAnalyzer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class);

it('does not contact OpenAI when the API key is missing', function () {
    config()->set('services.openai.api_key');
    Http::preventStrayRequests();

    $analysis = (new Analysis)->forceFill(['id' => '01JTESTANALYSIS000000000001']);

    expect(fn () => (new OpenAiVideoAnalyzer)->analyze($analysis, [[
        'index' => 1,
        'timestamp_seconds' => 0.0,
        'absolute_path' => '/not-read-without-key.jpg',
        'mime_type' => 'image/jpeg',
    ]]))->toThrow(RuntimeException::class, 'OpenAI API key belum dikonfigurasi.');
    Http::assertNothingSent();
});

it('sends sampled frames to the Responses API using GPT-6 Sol', function () {
    config()->set('services.openai.api_key', 'test-openai-key');
    config()->set('services.openai.base_url', 'https://api.openai.com/v1');
    config()->set('services.openai.model', 'gpt-6-sol');
    Http::preventStrayRequests();
    Http::fake([
        'api.openai.com/v1/responses' => Http::response([
            'status' => 'completed',
            'output' => [[
                'type' => 'message',
                'content' => [[
                    'type' => 'output_text',
                    'text' => json_encode(openAiAnalysisResult(), JSON_THROW_ON_ERROR),
                ]],
            ]],
        ]),
    ]);
    Storage::fake('local');
    Storage::disk('local')->put('frames/frame-001.jpg', 'jpeg-frame');

    $analysis = (new Analysis)->forceFill(['id' => '01JTESTANALYSIS000000000001']);
    $athlete = (new Athlete)->forceFill([
        'name' => 'Raka Pratama',
        'gender' => 'male',
        'category' => 'Randori Putra',
    ]);
    $matchRecord = (new MatchRecord)->forceFill([
        'opponent_name' => 'Dimas Arya',
        'opponent_club' => 'Dojo Garuda',
        'category' => 'Randori Putra',
        'match_type' => 'randori',
        'division' => 'male',
    ]);
    $matchRecord->setRelation('athletes', new Collection([$athlete]));
    $analysis->setRelation('athlete', $athlete);
    $analysis->setRelation('matchRecord', $matchRecord);
    $analysis->setRelation('video', (new Video)->forceFill([
        'focus_description' => 'Atlet sudut merah dengan pelindung merah.',
    ]));

    $result = (new OpenAiVideoAnalyzer)->analyze($analysis, [[
        'index' => 1,
        'timestamp_seconds' => 12.5,
        'absolute_path' => Storage::disk('local')->path('frames/frame-001.jpg'),
        'mime_type' => 'image/jpeg',
    ]]);

    expect($result['overall_score'])->toBe(84)
        ->and($result['match_intelligence']['frames_analyzed'])->toBe(1);
    Http::assertSent(function (Request $request): bool {
        $data = $request->data();
        $content = $data['input'][0]['content'];

        return $request->url() === 'https://api.openai.com/v1/responses'
            && $request->hasHeader('Authorization', 'Bearer test-openai-key')
            && $data['model'] === 'gpt-6-sol'
            && $data['store'] === false
            && $data['reasoning']['effort'] === 'high'
            && $data['text']['verbosity'] === 'high'
            && $data['text']['format']['type'] === 'json_schema'
            && array_key_exists('match_intelligence', $data['text']['format']['schema']['properties'])
            && array_key_exists('training_recommendations', $data['text']['format']['schema']['properties'])
            && str_contains($content[0]['text'], 'Atlet sudut merah dengan pelindung merah.')
            && $content[1]['type'] === 'input_text'
            && $content[2]['type'] === 'input_image'
            && $content[2]['detail'] === 'high'
            && str_starts_with($content[2]['image_url'], 'data:image/jpeg;base64,');
    });
});

it('normalizes omitted evidence lists to empty collections', function () {
    config()->set('services.openai.api_key', 'test-openai-key');
    config()->set('services.openai.base_url', 'https://api.openai.com/v1');
    Http::preventStrayRequests();
    $openAiResult = openAiAnalysisResult();
    unset($openAiResult['metrics'], $openAiResult['events'], $openAiResult['opportunities']);
    Http::fake([
        'api.openai.com/v1/responses' => Http::response([
            'status' => 'completed',
            'output' => [[
                'type' => 'message',
                'content' => [[
                    'type' => 'output_text',
                    'text' => json_encode($openAiResult, JSON_THROW_ON_ERROR),
                ]],
            ]],
        ]),
    ]);
    Storage::fake('local');
    Storage::disk('local')->put('frames/frame-001.jpg', 'jpeg-frame');

    $analysis = (new Analysis)->forceFill(['id' => '01JTESTANALYSIS000000000001']);
    $athlete = (new Athlete)->forceFill([
        'name' => 'Raka Pratama',
        'gender' => 'male',
        'category' => 'Randori Putra',
    ]);
    $matchRecord = (new MatchRecord)->forceFill([
        'opponent_name' => 'Dimas Arya',
        'opponent_club' => 'Dojo Garuda',
        'category' => 'Randori Putra',
        'match_type' => 'randori',
        'division' => 'male',
    ]);
    $matchRecord->setRelation('athletes', new Collection([$athlete]));
    $analysis->setRelation('athlete', $athlete);
    $analysis->setRelation('matchRecord', $matchRecord);

    $result = (new OpenAiVideoAnalyzer)->analyze($analysis, [[
        'index' => 1,
        'timestamp_seconds' => 12.5,
        'absolute_path' => Storage::disk('local')->path('frames/frame-001.jpg'),
        'mime_type' => 'image/jpeg',
    ]]);

    expect($result['metrics'])->toBe([])
        ->and($result['events'])->toBe([])
        ->and($result['opportunities'])->toBe([]);
});

/** @return array<string, mixed> */
function openAiAnalysisResult(): array
{
    return [
        'overall_score' => 84,
        'match_intelligence' => [
            'executive_summary' => 'Atlet menunjukkan guard stabil dan kontrol jarak yang cukup baik.',
            'analysis_scope' => 'Satu frame sampel dianalisis.',
            'frames_analyzed' => 1,
            'target_identification' => [
                'label' => 'Atlet sudut merah',
                'basis' => 'Petunjuk target cocok dengan warna pelindung.',
                'confidence' => 0.92,
                'caveat' => 'Identifikasi terbatas pada satu frame.',
            ],
            'strengths' => [[
                'title' => 'Guard stabil',
                'detail' => 'Tangan melindungi garis tengah.',
                'confidence' => 0.82,
                'evidence' => ['Guard terlihat pada frame 1.'],
            ]],
            'weaknesses' => [],
            'athlete_profile' => [[
                'aspect' => 'Kamae',
                'assessment' => 'Posisi guard terlihat siap.',
                'confidence' => 0.82,
                'evidence' => ['Frame 1 menunjukkan kedua tangan terangkat.'],
            ]],
            'opponent_profile' => [],
            'match_dynamics' => [],
            'risk_flags' => [],
            'limitations' => ['Hanya satu frame tersedia.'],
        ],
        'metrics' => [[
            'category' => 'technical',
            'name' => 'Kesiapan guard',
            'score' => 82,
            'evidence' => ['Guard terlihat stabil pada frame pertama.'],
        ]],
        'events' => [[
            'event_type' => 'guard_recovery',
            'frame_index' => 1,
            'timestamp_seconds' => 12.5,
            'confidence' => 0.81,
            'title' => 'Pemulihan guard',
            'description' => 'Atlet kembali ke guard setelah pertukaran.',
            'evidence' => ['Posisi tangan kembali melindungi garis tengah.'],
        ]],
        'opportunities' => [[
            'opportunity_type' => 'counter_window',
            'frame_index' => 1,
            'timestamp_seconds' => 12.5,
            'confidence' => 0.76,
            'trigger' => 'Ruang terbuka setelah gerak lawan.',
            'explanation' => 'Terlihat jalur counter pada sampel frame.',
            'recommended_action' => 'Latih counter langsung dengan kontrol jarak.',
            'evidence' => ['Jarak kedua atlet memungkinkan counter.'],
        ]],
        'strategy' => [
            'summary' => 'Pertahankan guard dan kontrol jarak.',
            'attack_strategy' => 'Gunakan entry terukur.',
            'counter_strategy' => 'Counter setelah lawan membuka garis tengah.',
            'defensive_strategy' => 'Pulihkan guard setelah setiap aksi.',
            'what_to_avoid' => 'Hindari entry tanpa kontrol jarak.',
            'priority_points' => ['Guard', 'Jarak', 'Timing'],
        ],
        'training_recommendations' => [[
            'priority' => 'high',
            'drill' => 'Latihan recovery guard setelah serangan',
            'frequency' => '3 sesi per minggu',
            'duration_minutes' => 20,
            'target_metric' => 'Kesiapan guard',
            'target_score' => 90,
        ]],
    ];
}
