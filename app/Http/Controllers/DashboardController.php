<?php

namespace App\Http\Controllers;

use App\Enums\AnalysisStatus;
use App\Models\Analysis;
use App\Models\Athlete;
use App\Models\MatchRecord;
use App\Models\Video;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $completedAnalyses = Analysis::query()->where('status', AnalysisStatus::Completed)->count();

        $recentMatches = MatchRecord::query()
            ->select([
                'id',
                'athlete_id',
                'competition_event_id',
                'opponent_name',
                'match_type',
                'division',
                'category',
                'match_date',
                'result',
                'status',
            ])
            ->with([
                'athlete:id,name,identifier',
                'athletes:id,name,identifier',
                'competitionEvent:id,name',
            ])
            ->latest('match_date')
            ->latest('id')
            ->limit(6)
            ->get();

        $processing = Analysis::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $performanceTrend = Analysis::query()
            ->select(['id', 'match_record_id', 'overall_score', 'completed_at'])
            ->where('status', AnalysisStatus::Completed)
            ->whereNotNull('overall_score')
            ->with('matchRecord:id,match_date')
            ->latest('completed_at')
            ->limit(8)
            ->get()
            ->reverse()
            ->values();

        return Inertia::render('Dashboard', [
            'stats' => [
                'athletes' => Athlete::query()->count(),
                'matches' => MatchRecord::query()->count(),
                'videos' => Video::query()->count(),
                'completed_analyses' => $completedAnalyses,
                'average_score' => round((float) Analysis::query()
                    ->where('status', AnalysisStatus::Completed)
                    ->avg('overall_score'), 1),
            ],
            'processing' => [
                'queued' => (int) ($processing[AnalysisStatus::Queued->value] ?? 0),
                'active' => collect([
                    AnalysisStatus::Downloading,
                    AnalysisStatus::Preprocessing,
                    AnalysisStatus::Detecting,
                    AnalysisStatus::Tracking,
                    AnalysisStatus::PoseAnalysis,
                    AnalysisStatus::EventAnalysis,
                    AnalysisStatus::PerformanceAnalysis,
                    AnalysisStatus::StrategyGeneration,
                    AnalysisStatus::ReportGeneration,
                ])->sum(fn (AnalysisStatus $status): int => (int) ($processing[$status->value] ?? 0)),
                'failed' => (int) ($processing[AnalysisStatus::Failed->value] ?? 0),
            ],
            'recentMatches' => $recentMatches,
            'performanceTrend' => $performanceTrend,
        ]);
    }
}
