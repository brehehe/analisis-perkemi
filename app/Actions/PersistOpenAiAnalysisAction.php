<?php

namespace App\Actions;

use App\Enums\AnalysisStatus;
use App\Enums\MatchStatus;
use App\Enums\ValidationStatus;
use App\Models\Analysis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PersistOpenAiAnalysisAction
{
    /**
     * @param  array<string, mixed>  $result
     */
    public function execute(Analysis $analysis, array $result): void
    {
        $validated = Validator::make($result, [
            'overall_score' => ['nullable', 'numeric', 'between:0,100'],
            'match_intelligence' => ['required', 'array'],
            'match_intelligence.executive_summary' => ['required', 'string', 'max:10000'],
            'match_intelligence.analysis_scope' => ['required', 'string', 'max:5000'],
            'match_intelligence.frames_analyzed' => ['required', 'integer', 'min:1', 'max:48'],
            'match_intelligence.target_identification' => ['required', 'array'],
            'match_intelligence.target_identification.label' => ['required', 'string', 'max:255'],
            'match_intelligence.target_identification.basis' => ['required', 'string', 'max:5000'],
            'match_intelligence.target_identification.confidence' => ['required', 'numeric', 'between:0,1'],
            'match_intelligence.target_identification.caveat' => ['required', 'string', 'max:5000'],
            'match_intelligence.strengths' => ['present', 'array', 'max:20'],
            'match_intelligence.strengths.*.title' => ['required', 'string', 'max:255'],
            'match_intelligence.strengths.*.detail' => ['required', 'string', 'max:5000'],
            'match_intelligence.strengths.*.confidence' => ['required', 'numeric', 'between:0,1'],
            'match_intelligence.strengths.*.evidence' => ['present', 'array', 'max:20'],
            'match_intelligence.strengths.*.evidence.*' => ['string', 'max:1000'],
            'match_intelligence.weaknesses' => ['present', 'array', 'max:20'],
            'match_intelligence.weaknesses.*.title' => ['required', 'string', 'max:255'],
            'match_intelligence.weaknesses.*.detail' => ['required', 'string', 'max:5000'],
            'match_intelligence.weaknesses.*.confidence' => ['required', 'numeric', 'between:0,1'],
            'match_intelligence.weaknesses.*.evidence' => ['present', 'array', 'max:20'],
            'match_intelligence.weaknesses.*.evidence.*' => ['string', 'max:1000'],
            'match_intelligence.athlete_profile' => ['present', 'array', 'max:30'],
            'match_intelligence.athlete_profile.*.aspect' => ['required', 'string', 'max:255'],
            'match_intelligence.athlete_profile.*.assessment' => ['required', 'string', 'max:5000'],
            'match_intelligence.athlete_profile.*.confidence' => ['required', 'numeric', 'between:0,1'],
            'match_intelligence.athlete_profile.*.evidence' => ['present', 'array', 'max:20'],
            'match_intelligence.athlete_profile.*.evidence.*' => ['string', 'max:1000'],
            'match_intelligence.opponent_profile' => ['present', 'array', 'max:30'],
            'match_intelligence.opponent_profile.*.aspect' => ['required', 'string', 'max:255'],
            'match_intelligence.opponent_profile.*.assessment' => ['required', 'string', 'max:5000'],
            'match_intelligence.opponent_profile.*.confidence' => ['required', 'numeric', 'between:0,1'],
            'match_intelligence.opponent_profile.*.evidence' => ['present', 'array', 'max:20'],
            'match_intelligence.opponent_profile.*.evidence.*' => ['string', 'max:1000'],
            'match_intelligence.match_dynamics' => ['present', 'array', 'max:30'],
            'match_intelligence.match_dynamics.*.phase' => ['required', 'string', 'max:255'],
            'match_intelligence.match_dynamics.*.start_timestamp_seconds' => ['required', 'numeric', 'min:0'],
            'match_intelligence.match_dynamics.*.end_timestamp_seconds' => ['required', 'numeric', 'min:0'],
            'match_intelligence.match_dynamics.*.momentum' => ['required', 'string', 'max:1000'],
            'match_intelligence.match_dynamics.*.athlete_actions' => ['present', 'array', 'max:20'],
            'match_intelligence.match_dynamics.*.athlete_actions.*' => ['string', 'max:1000'],
            'match_intelligence.match_dynamics.*.opponent_actions' => ['present', 'array', 'max:20'],
            'match_intelligence.match_dynamics.*.opponent_actions.*' => ['string', 'max:1000'],
            'match_intelligence.match_dynamics.*.coaching_note' => ['required', 'string', 'max:5000'],
            'match_intelligence.risk_flags' => ['present', 'array', 'max:20'],
            'match_intelligence.risk_flags.*.risk' => ['required', 'string', 'max:255'],
            'match_intelligence.risk_flags.*.impact' => ['required', 'string', 'max:5000'],
            'match_intelligence.risk_flags.*.mitigation' => ['required', 'string', 'max:5000'],
            'match_intelligence.risk_flags.*.timestamp_seconds' => ['nullable', 'numeric', 'min:0'],
            'match_intelligence.risk_flags.*.confidence' => ['required', 'numeric', 'between:0,1'],
            'match_intelligence.risk_flags.*.evidence' => ['present', 'array', 'max:20'],
            'match_intelligence.risk_flags.*.evidence.*' => ['string', 'max:1000'],
            'match_intelligence.limitations' => ['present', 'array', 'max:30'],
            'match_intelligence.limitations.*' => ['string', 'max:2000'],
            'metrics' => ['present', 'array', 'max:20'],
            'metrics.*.category' => ['required', 'string', 'max:100'],
            'metrics.*.name' => ['required', 'string', 'max:255'],
            'metrics.*.score' => ['required', 'numeric', 'between:0,100'],
            'metrics.*.evidence' => ['present', 'array', 'max:20'],
            'metrics.*.evidence.*' => ['string', 'max:1000'],
            'events' => ['present', 'array', 'max:50'],
            'events.*.event_type' => ['required', 'string', 'max:100'],
            'events.*.frame_index' => ['required', 'integer', 'min:1'],
            'events.*.timestamp_seconds' => ['required', 'numeric', 'min:0'],
            'events.*.confidence' => ['required', 'numeric', 'between:0,1'],
            'events.*.title' => ['required', 'string', 'max:255'],
            'events.*.description' => ['required', 'string', 'max:5000'],
            'events.*.evidence' => ['present', 'array', 'max:20'],
            'events.*.evidence.*' => ['string', 'max:1000'],
            'opportunities' => ['present', 'array', 'max:50'],
            'opportunities.*.opportunity_type' => ['required', 'string', 'max:100'],
            'opportunities.*.frame_index' => ['required', 'integer', 'min:1'],
            'opportunities.*.timestamp_seconds' => ['required', 'numeric', 'min:0'],
            'opportunities.*.confidence' => ['required', 'numeric', 'between:0,1'],
            'opportunities.*.trigger' => ['required', 'string', 'max:255'],
            'opportunities.*.explanation' => ['required', 'string', 'max:5000'],
            'opportunities.*.recommended_action' => ['required', 'string', 'max:5000'],
            'opportunities.*.evidence' => ['present', 'array', 'max:20'],
            'opportunities.*.evidence.*' => ['string', 'max:1000'],
            'strategy' => ['required', 'array'],
            'strategy.summary' => ['required', 'string', 'max:5000'],
            'strategy.attack_strategy' => ['required', 'string', 'max:5000'],
            'strategy.counter_strategy' => ['required', 'string', 'max:5000'],
            'strategy.defensive_strategy' => ['required', 'string', 'max:5000'],
            'strategy.what_to_avoid' => ['required', 'string', 'max:5000'],
            'strategy.priority_points' => ['present', 'array', 'max:20'],
            'strategy.priority_points.*' => ['string', 'max:1000'],
            'training_recommendations' => ['present', 'array', 'max:12'],
            'training_recommendations.*.priority' => ['required', 'string', Rule::in(['high', 'medium', 'low'])],
            'training_recommendations.*.drill' => ['required', 'string', 'max:255'],
            'training_recommendations.*.frequency' => ['nullable', 'string', 'max:255'],
            'training_recommendations.*.duration_minutes' => ['nullable', 'integer', 'between:1,600'],
            'training_recommendations.*.target_metric' => ['nullable', 'string', 'max:255'],
            'training_recommendations.*.target_score' => ['nullable', 'numeric', 'between:0,100'],
        ])->validate();

        DB::transaction(function () use ($analysis, $validated): void {
            $analysis->opportunities()->delete();
            $analysis->events()->delete();
            $analysis->metrics()->delete();
            $analysis->strategies()->delete();
            $analysis->trainingRecommendations()->delete();

            $analysis->metrics()->createMany(collect($validated['metrics'])->map(fn (array $metric): array => [
                'category' => $metric['category'],
                'name' => $metric['name'],
                'score' => $metric['score'],
                'evidence' => ['observations' => $metric['evidence']],
            ])->all());

            $analysis->events()->createMany(collect($validated['events'])->map(fn (array $event): array => [
                'event_type' => $event['event_type'],
                'occurred_at_ms' => (int) round($event['timestamp_seconds'] * 1000),
                'confidence' => $event['confidence'],
                'title' => $event['title'],
                'description' => $event['description'],
                'evidence' => [
                    'frame_index' => $event['frame_index'],
                    'observations' => $event['evidence'],
                ],
                'validation_status' => ValidationStatus::Pending,
            ])->all());

            $analysis->opportunities()->createMany(collect($validated['opportunities'])->map(fn (array $opportunity): array => [
                'opportunity_type' => $opportunity['opportunity_type'],
                'occurred_at_ms' => (int) round($opportunity['timestamp_seconds'] * 1000),
                'confidence' => $opportunity['confidence'],
                'trigger' => $opportunity['trigger'],
                'explanation' => $opportunity['explanation'],
                'recommended_action' => $opportunity['recommended_action'],
                'evidence' => [
                    'frame_index' => $opportunity['frame_index'],
                    'observations' => $opportunity['evidence'],
                ],
                'validation_status' => ValidationStatus::Pending,
            ])->all());

            $analysis->strategies()->create([
                'version' => 1,
                'summary' => $validated['strategy']['summary'],
                'attack_strategy' => $validated['strategy']['attack_strategy'],
                'counter_strategy' => $validated['strategy']['counter_strategy'],
                'defensive_strategy' => $validated['strategy']['defensive_strategy'],
                'what_to_avoid' => $validated['strategy']['what_to_avoid'],
                'priority_points' => $validated['strategy']['priority_points'],
                'generated_at' => now(),
            ]);

            $analysis->trainingRecommendations()->createMany(collect($validated['training_recommendations'])->map(fn (array $recommendation): array => [
                'athlete_id' => $analysis->athlete_id,
                'priority' => $recommendation['priority'],
                'drill' => $recommendation['drill'],
                'frequency' => $recommendation['frequency'] ?? null,
                'duration_minutes' => $recommendation['duration_minutes'] ?? null,
                'target_metric' => $recommendation['target_metric'] ?? null,
                'target_score' => $recommendation['target_score'] ?? null,
            ])->all());

            $analysis->update([
                'status' => AnalysisStatus::Completed,
                'progress' => 100,
                'current_step' => 'Analisis OpenAI selesai dan siap divalidasi',
                'model_version' => (string) config('services.openai.model', 'gpt-6-sol'),
                'overall_score' => $validated['overall_score'],
                'match_intelligence' => $validated['match_intelligence'],
                'completed_at' => now(),
                'failed_at' => null,
                'error_message' => null,
            ]);

            $analysis->video()->update(['processing_status' => AnalysisStatus::Completed]);
            $analysis->matchRecord()->update(['status' => MatchStatus::Completed]);
        });
    }
}
