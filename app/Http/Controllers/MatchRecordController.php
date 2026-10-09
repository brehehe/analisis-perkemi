<?php

namespace App\Http\Controllers;

use App\Actions\ArchiveMatchRecordAction;
use App\Actions\CreateMatchRecordAction;
use App\Actions\UpdateMatchRecordAction;
use App\Enums\MatchDivision;
use App\Enums\MatchResult;
use App\Enums\MatchStatus;
use App\Enums\MatchType;
use App\Http\Requests\StoreMatchRecordRequest;
use App\Http\Requests\UpdateMatchRecordRequest;
use App\Models\Athlete;
use App\Models\CompetitionEvent;
use App\Models\MatchRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MatchRecordController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', MatchRecord::class);

        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();

        $matches = MatchRecord::query()
            ->select([
                'id',
                'athlete_id',
                'competition_event_id',
                'opponent_name',
                'opponent_club',
                'match_date',
                'category',
                'match_type',
                'division',
                'result',
                'status',
            ])
            ->with([
                'athlete:id,name,identifier',
                'athletes:id,name,identifier',
                'competitionEvent:id,name',
            ])
            ->withCount(['videos', 'analyses'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('opponent_name', 'like', "%{$search}%")
                        ->orWhereHas('athletes', fn ($athleteQuery) => $athleteQuery
                            ->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(
                in_array($status, array_column(MatchStatus::cases(), 'value'), true),
                fn ($query) => $query->where('status', $status),
            )
            ->latest('match_date')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Matches/Index', [
            'matches' => $matches,
            'filters' => compact('search', 'status'),
            'statusOptions' => array_column(MatchStatus::cases(), 'value'),
            'can' => [
                'create' => $request->user()?->can('create', MatchRecord::class) ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', MatchRecord::class);

        return Inertia::render('Matches/Form', $this->formOptions());
    }

    public function store(
        StoreMatchRecordRequest $request,
        CreateMatchRecordAction $createMatchRecord,
    ): RedirectResponse {
        $matchRecord = $createMatchRecord->execute($request->user(), $request->validated());

        return redirect()
            ->route('matches.show', $matchRecord)
            ->with('success', 'Pertandingan berhasil dibuat.');
    }

    public function show(MatchRecord $matchRecord): Response
    {
        Gate::authorize('view', $matchRecord);

        $matchRecord->load([
            'athlete:id,name,identifier,category,club_id,coach_id',
            'athletes:id,name,identifier,gender,club_id',
            'athletes.club:id,name',
            'athlete.club:id,name',
            'athlete.coach:id,name',
            'competitionEvent:id,name,venue,city',
            'videos' => fn ($query) => $query->latest(),
            'analyses' => fn ($query) => $query
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
                ->latest(),
        ]);

        return Inertia::render('Matches/Show', [
            'match' => $matchRecord,
            'can' => [
                'update' => auth()->user()?->can('update', $matchRecord) ?? false,
                'delete' => auth()->user()?->can('delete', $matchRecord) ?? false,
                'uploadVideo' => auth()->user()?->can('videos.upload') ?? false,
            ],
        ]);
    }

    public function edit(MatchRecord $matchRecord): Response
    {
        Gate::authorize('update', $matchRecord);
        $matchRecord->load('athletes:id');

        return Inertia::render('Matches/Form', [
            ...$this->formOptions(),
            'match' => $matchRecord,
        ]);
    }

    public function update(
        UpdateMatchRecordRequest $request,
        MatchRecord $matchRecord,
        UpdateMatchRecordAction $updateMatchRecord,
    ): RedirectResponse {
        $updateMatchRecord->execute($request->user(), $matchRecord, $request->validated());

        return redirect()
            ->route('matches.show', $matchRecord)
            ->with('success', 'Pertandingan berhasil diperbarui.');
    }

    public function destroy(
        Request $request,
        MatchRecord $matchRecord,
        ArchiveMatchRecordAction $archiveMatchRecord,
    ): RedirectResponse {
        Gate::authorize('delete', $matchRecord);
        $archiveMatchRecord->execute($request->user(), $matchRecord);

        return redirect()
            ->route('matches.index')
            ->with('success', 'Pertandingan berhasil diarsipkan.');
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        return [
            'athletes' => Athlete::query()
                ->select(['id', 'name', 'identifier', 'gender', 'category'])
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'events' => CompetitionEvent::query()
                ->select(['id', 'name', 'starts_at'])
                ->orderByDesc('starts_at')
                ->get(),
            'resultOptions' => array_column(MatchResult::cases(), 'value'),
            'statusOptions' => array_column(MatchStatus::cases(), 'value'),
            'matchTypeOptions' => collect(MatchType::cases())->map(fn (MatchType $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
                'team_size' => $type->teamSize(),
                'has_opponent' => $type->hasOpponent(),
                'allows_mixed' => $type->allowsMixedDivision(),
            ]),
            'divisionOptions' => collect(MatchDivision::cases())->map(fn (MatchDivision $division): array => [
                'value' => $division->value,
                'label' => $division->label(),
            ]),
        ];
    }
}
