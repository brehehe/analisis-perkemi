<?php

use App\Jobs\PrepareVideoAnalysisJob;
use App\Models\Analysis;
use Illuminate\Support\Facades\Queue;

it('queues a failed analysis again and clears its failure state', function () {
    Queue::fake([PrepareVideoAnalysisJob::class]);
    $user = userWithPermissions(['analysis.validate']);
    $analysis = Analysis::factory()->create([
        'status' => 'failed',
        'progress' => 85,
        'current_step' => 'Analisis OpenAI gagal',
        'failed_at' => now(),
        'error_message' => 'Respons OpenAI tidak lengkap.',
    ]);
    $analysis->video()->update(['processing_status' => 'failed']);

    $response = $this->actingAs($user)->post("/analyses/{$analysis->getKey()}/retry", [
        'focus_description' => 'Atlet sudut biru dengan pelindung biru.',
    ]);

    $response->assertRedirect("/analyses/{$analysis->getKey()}")
        ->assertSessionHas('success', 'Analisis masuk kembali ke antrean.');
    $analysis->refresh()->load('video');
    expect($analysis->status->value)->toBe('queued')
        ->and($analysis->progress)->toBe(0)
        ->and($analysis->failed_at)->toBeNull()
        ->and($analysis->error_message)->toBeNull()
        ->and($analysis->video->processing_status->value)->toBe('queued')
        ->and($analysis->video->focus_description)->toBe('Atlet sudut biru dengan pelindung biru.');
    $this->assertDatabaseHas('audit_logs', [
        'action' => 'analysis.retried',
        'auditable_id' => $analysis->getKey(),
    ]);
    Queue::assertPushed(PrepareVideoAnalysisJob::class, fn (PrepareVideoAnalysisJob $job): bool => $job->analysis->is($analysis));
});

it('queues a completed analysis for a more detailed result', function () {
    Queue::fake([PrepareVideoAnalysisJob::class]);
    $user = userWithPermissions(['analysis.validate']);
    $analysis = Analysis::factory()->create([
        'status' => 'completed',
        'progress' => 100,
        'completed_at' => now(),
    ]);
    $analysis->video()->update(['processing_status' => 'completed']);

    $response = $this->actingAs($user)->post("/analyses/{$analysis->getKey()}/retry", [
        'focus_description' => 'Atlet sudut merah.',
    ]);

    $response->assertRedirect("/analyses/{$analysis->getKey()}")
        ->assertSessionHas('success', 'Analisis masuk kembali ke antrean.');
    $analysis->refresh()->load('video');
    expect($analysis->status->value)->toBe('queued')
        ->and($analysis->completed_at)->toBeNull()
        ->and($analysis->video->focus_description)->toBe('Atlet sudut merah.');
    Queue::assertPushed(PrepareVideoAnalysisJob::class, fn (PrepareVideoAnalysisJob $job): bool => $job->analysis->is($analysis));
});

it('rejects retrying an analysis that is still queued', function () {
    Queue::fake([PrepareVideoAnalysisJob::class]);
    $user = userWithPermissions(['analysis.validate']);
    $analysis = Analysis::factory()->create(['status' => 'queued']);

    $this->actingAs($user)
        ->from("/analyses/{$analysis->getKey()}")
        ->post("/analyses/{$analysis->getKey()}/retry")
        ->assertRedirect("/analyses/{$analysis->getKey()}")
        ->assertSessionHasErrors([
            'analysis' => 'Hanya analisis yang selesai atau gagal yang dapat dijalankan ulang.',
        ]);

    Queue::assertNothingPushed();
});

it('forbids retrying an analysis without validation permission', function () {
    Queue::fake([PrepareVideoAnalysisJob::class]);
    $user = userWithPermissions([]);
    $analysis = Analysis::factory()->create(['status' => 'failed']);

    $this->actingAs($user)
        ->post("/analyses/{$analysis->getKey()}/retry")
        ->assertForbidden();

    Queue::assertNothingPushed();
});
