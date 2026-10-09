<?php

namespace App\Services;

use App\Contracts\DownloadsYoutubeVideo;
use App\Enums\VideoSource;
use App\Models\Video;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class YtDlpYoutubeVideoDownloader implements DownloadsYoutubeVideo
{
    /**
     * @return array{storage_path: string, original_name: string, mime_type: string, file_size: int}
     */
    public function download(Video $video): array
    {
        if ($video->source !== VideoSource::Youtube || $video->external_url === null) {
            throw new RuntimeException('Video bukan sumber YouTube yang dapat diunduh.');
        }

        $host = parse_url($video->external_url, PHP_URL_HOST);

        if (! in_array($host, ['youtube.com', 'www.youtube.com', 'youtu.be', 'm.youtube.com'], true)) {
            throw new RuntimeException('URL video bukan URL YouTube yang didukung.');
        }

        $disk = Storage::disk('local');
        $directory = "match-videos/{$video->match_record_id}/{$video->getKey()}";
        $disk->deleteDirectory($directory);
        $disk->makeDirectory($directory);

        $outputTemplate = $disk->path("{$directory}/video.%(ext)s");
        $process = new Process([
            (string) config('services.youtube.yt_dlp_binary', 'yt-dlp'),
            '--no-config',
            '--no-playlist',
            '--no-progress',
            '--no-warnings',
            '--restrict-filenames',
            '--socket-timeout',
            '20',
            '--max-filesize',
            ((string) config('services.youtube.max_file_size_mb', 500)).'M',
            '--match-filter',
            'duration <= '.(string) config('services.youtube.max_duration_seconds', 7200),
            '--format',
            'bestvideo[height<=1080]+bestaudio/best[height<=1080]/best',
            '--merge-output-format',
            'mp4',
            '--output',
            $outputTemplate,
            $video->external_url,
        ]);
        $process->setTimeout((int) config('services.youtube.download_timeout', 600));
        $process->run();

        if (! $process->isSuccessful()) {
            $message = trim($process->getErrorOutput() ?: $process->getOutput());

            throw new RuntimeException(
                'yt-dlp gagal mengunduh video YouTube: '.Str::limit($message ?: 'proses berakhir tanpa hasil', 400),
            );
        }

        $storagePath = $this->downloadedStoragePath($disk, $directory);

        return [
            'storage_path' => $storagePath,
            'original_name' => 'youtube-'.$video->getKey().'.'.pathinfo($storagePath, PATHINFO_EXTENSION),
            'mime_type' => $disk->mimeType($storagePath) ?: 'application/octet-stream',
            'file_size' => $disk->size($storagePath),
        ];
    }

    private function downloadedStoragePath(FilesystemAdapter $disk, string $directory): string
    {
        $files = collect($disk->files($directory))
            ->filter(fn (string $path): bool => in_array(
                strtolower(pathinfo($path, PATHINFO_EXTENSION)),
                ['mp4', 'mkv', 'mov', 'webm'],
                true,
            ))
            ->values();

        if ($files->count() !== 1) {
            throw new RuntimeException('yt-dlp selesai tetapi file video hasil unduhan tidak ditemukan.');
        }

        return $files->first();
    }
}
