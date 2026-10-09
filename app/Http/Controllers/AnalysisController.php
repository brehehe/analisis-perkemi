<?php

namespace App\Http\Controllers;

use App\Actions\RetryVideoAnalysisAction;
use App\Enums\AnalysisStatus;
use App\Models\Analysis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class AnalysisController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Analysis::class);

        $status = $request->string('status')->toString();
        $activeStatuses = [
            AnalysisStatus::Downloading->value,
            AnalysisStatus::Preprocessing->value,
            AnalysisStatus::Detecting->value,
            AnalysisStatus::Tracking->value,
            AnalysisStatus::PoseAnalysis->value,
            AnalysisStatus::EventAnalysis->value,
            AnalysisStatus::PerformanceAnalysis->value,
            AnalysisStatus::StrategyGeneration->value,
            AnalysisStatus::ReportGeneration->value,
        ];

        $analyses = Analysis::query()
            ->select([
                'id',
                'match_record_id',
                'video_id',
                'athlete_id',
                'status',
                'progress',
                'current_step',
                'overall_score',
                'model_version',
                'created_at',
            ])
            ->with([
                'athlete:id,name,identifier',
                'matchRecord:id,athlete_id,opponent_name,match_date,category,match_type,division',
                'matchRecord.athletes:id,name,identifier',
                'video:id,source,processing_status',
            ])
            ->when($status === 'active', fn ($query) => $query->whereIn('status', $activeStatuses))
            ->when(
                in_array($status, array_column(AnalysisStatus::cases(), 'value'), true),
                fn ($query) => $query->where('status', $status),
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Analysis/Index', [
            'analyses' => $analyses,
            'filter' => $status,
            'statusOptions' => collect(AnalysisStatus::cases())->map(fn (AnalysisStatus $item): array => [
                'value' => $item->value,
                'label' => $item->label(),
            ]),
        ]);
    }

    public function show(Analysis $analysis): Response
    {
        Gate::authorize('view', $analysis);

        $analysis->load([
            'athlete:id,name,identifier,category',
            'matchRecord:id,competition_event_id,athlete_id,opponent_name,opponent_club,match_date,category,match_type,division,result,status',
            'matchRecord.athletes:id,name,identifier',
            'matchRecord.competitionEvent:id,name,venue,city',
            'video:id,match_record_id,source,original_name,external_url,focus_description,processing_status,duration_seconds',
            'metrics:id,analysis_id,category,name,score,evidence',
            'events:id,analysis_id,event_type,occurred_at_ms,confidence,title,description,evidence,validation_status',
            'opportunities:id,analysis_id,analysis_event_id,opportunity_type,occurred_at_ms,confidence,trigger,explanation,recommended_action,evidence,validation_status',
            'latestStrategy',
            'trainingRecommendations:id,analysis_id,athlete_id,priority,drill,frequency,duration_minutes,target_metric,target_score',
            'coachReport',
        ]);

        return Inertia::render('Analysis/Show', [
            'analysis' => $analysis,
            'can' => [
                'validate' => auth()->user()?->can('update', $analysis) ?? false,
            ],
        ]);
    }

    public function retry(
        Request $request,
        Analysis $analysis,
        RetryVideoAnalysisAction $retryVideoAnalysis,
    ): RedirectResponse {
        Gate::authorize('update', $analysis);
        $validated = $request->validate([
            'focus_description' => ['nullable', 'string', 'max:1000'],
        ]);
        $focusDescription = trim((string) ($validated['focus_description'] ?? '')) ?: null;

        $retryVideoAnalysis->execute($request->user(), $analysis, $focusDescription);

        return redirect()
            ->route('analyses.show', $analysis)
            ->with('success', 'Analisis masuk kembali ke antrean.');
    }
}
