<?php

namespace App\Http\Controllers;

use App\Actions\ArchiveAthleteAction;
use App\Actions\CreateAthleteAction;
use App\Actions\UpdateAthleteAction;
use App\Enums\AccountStatus;
use App\Enums\AthleteGender;
use App\Http\Requests\StoreAthleteRequest;
use App\Http\Requests\UpdateAthleteRequest;
use App\Models\Athlete;
use App\Models\Club;
use App\Models\Coach;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class AthleteController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Athlete::class);

        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();
        $category = $request->string('category')->toString();

        $athletes = Athlete::query()
            ->select([
                'id',
                'club_id',
                'coach_id',
                'identifier',
                'name',
                'gender',
                'category',
                'weight_class',
                'status',
                'created_at',
            ])
            ->with(['club:id,name', 'coach:id,name'])
            ->withCount('matches')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('identifier', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array($status, array_column(AccountStatus::cases(), 'value'), true),
                fn ($query) => $query->where('status', $status),
            )
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Athletes/Index', [
            'athletes' => $athletes,
            'filters' => compact('search', 'status', 'category'),
            'categories' => Athlete::query()->distinct()->orderBy('category')->pluck('category'),
            'can' => [
                'create' => $request->user()?->can('create', Athlete::class) ?? false,
            ],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Athlete::class);

        return Inertia::render('Athletes/Form', $this->formOptions());
    }

    public function store(StoreAthleteRequest $request, CreateAthleteAction $createAthlete): RedirectResponse
    {
        $athlete = $createAthlete->execute($request->user(), $request->validated());

        return redirect()
            ->route('athletes.show', $athlete)
            ->with('success', 'Atlet berhasil ditambahkan.');
    }

    public function show(Athlete $athlete): Response
    {
        Gate::authorize('view', $athlete);

        $athlete->load([
            'club:id,name,city',
            'coach:id,name,email',
            'matches' => fn ($query) => $query
                ->select([
                    'match_records.id',
                    'match_records.athlete_id',
                    'match_records.competition_event_id',
                    'match_records.opponent_name',
                    'match_records.match_type',
                    'match_records.category',
                    'match_records.match_date',
                    'match_records.result',
                    'match_records.status',
                ])
                ->with('athletes:id,name,identifier')
                ->with('competitionEvent:id,name')
                ->latest('match_date')
                ->limit(8),
        ])->loadCount(['matches', 'analyses']);

        return Inertia::render('Athletes/Show', [
            'athlete' => $athlete,
            'can' => [
                'update' => auth()->user()?->can('update', $athlete) ?? false,
                'delete' => auth()->user()?->can('delete', $athlete) ?? false,
            ],
        ]);
    }

    public function edit(Athlete $athlete): Response
    {
        Gate::authorize('update', $athlete);

        return Inertia::render('Athletes/Form', [
            ...$this->formOptions(),
            'athlete' => $athlete,
        ]);
    }

    public function update(
        UpdateAthleteRequest $request,
        Athlete $athlete,
        UpdateAthleteAction $updateAthlete,
    ): RedirectResponse {
        $updateAthlete->execute($request->user(), $athlete, $request->validated());

        return redirect()
            ->route('athletes.show', $athlete)
            ->with('success', 'Data atlet berhasil diperbarui.');
    }

    public function destroy(
        Request $request,
        Athlete $athlete,
        ArchiveAthleteAction $archiveAthlete,
    ): RedirectResponse {
        Gate::authorize('delete', $athlete);
        $archiveAthlete->execute($request->user(), $athlete);

        return redirect()
            ->route('athletes.index')
            ->with('success', 'Atlet berhasil diarsipkan.');
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        return [
            'clubs' => Club::query()->select(['id', 'name'])->orderBy('name')->get(),
            'coaches' => Coach::query()->select(['id', 'name', 'club_id'])->orderBy('name')->get(),
            'genderOptions' => collect(AthleteGender::cases())->map(fn (AthleteGender $gender): array => [
                'value' => $gender->value,
                'label' => $gender->label(),
            ]),
            'statusOptions' => array_column(AccountStatus::cases(), 'value'),
        ];
    }
}
