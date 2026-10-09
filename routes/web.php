<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\AthleteController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MatchRecordController;
use App\Http\Controllers\MatchVideoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)
        ->middleware('can:dashboard.view')
        ->name('dashboard');

    Route::resource('athletes', AthleteController::class);
    Route::resource('matches', MatchRecordController::class)
        ->parameters(['matches' => 'match_record']);
    Route::post('/matches/{match_record}/videos', [MatchVideoController::class, 'store'])
        ->name('matches.videos.store');
    Route::get('/analyses', [AnalysisController::class, 'index'])
        ->name('analyses.index');
    Route::post('/analyses/{analysis}/retry', [AnalysisController::class, 'retry'])
        ->name('analyses.retry');
    Route::get('/analyses/{analysis}', [AnalysisController::class, 'show'])
        ->name('analyses.show');
});
