<?php

namespace App\Http\Controllers;

use App\Actions\StartVideoAnalysisAction;
use App\Http\Requests\StoreMatchVideoRequest;
use App\Models\MatchRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class MatchVideoController extends Controller
{
    public function store(
        StoreMatchVideoRequest $request,
        MatchRecord $matchRecord,
        StartVideoAnalysisAction $startVideoAnalysis,
    ): RedirectResponse {
        Gate::authorize('update', $matchRecord);

        $analysis = $startVideoAnalysis->execute(
            $request->user(),
            $matchRecord,
            $request->validated(),
        );

        return redirect()
            ->route('analyses.show', $analysis)
            ->with('success', 'Video diterima dan masuk antrean analisis.');
    }
}
