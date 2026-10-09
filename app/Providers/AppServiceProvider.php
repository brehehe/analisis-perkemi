<?php

namespace App\Providers;

use App\Contracts\AnalyzesMatchVideo;
use App\Contracts\DownloadsYoutubeVideo;
use App\Contracts\ExtractsVideoFrames;
use App\Services\FfmpegVideoFrameExtractor;
use App\Services\OpenAiVideoAnalyzer;
use App\Services\YtDlpYoutubeVideoDownloader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AnalyzesMatchVideo::class, OpenAiVideoAnalyzer::class);
        $this->app->bind(DownloadsYoutubeVideo::class, YtDlpYoutubeVideoDownloader::class);
        $this->app->bind(ExtractsVideoFrames::class, FfmpegVideoFrameExtractor::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());
    }
}
