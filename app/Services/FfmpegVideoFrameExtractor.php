<?php

namespace App\Services;

use App\Contracts\ExtractsVideoFrames;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class FfmpegVideoFrameExtractor implements ExtractsVideoFrames
{
    /**
     * @return list<array{index: int, timestamp_seconds: float, absolute_path: string, mime_type: string}>
     */
    public function extract(string $videoPath, string $analysisId): array
    {
        if (! is_file($videoPath)) {
            throw new RuntimeException('Berkas video tidak ditemukan untuk ekstraksi frame.');
        }

        $frameCount = max(1, min((int) config('services.openai.frame_count', 48), 48));
        $maxWidth = max(320, min((int) config('services.openai.frame_max_width', 1280), 2048));
        $duration = $this->probeDuration($videoPath);
        $samplingInterval = max($duration / $frameCount, 1.0);
        $directory = "analysis-frames/{$analysisId}";

        Storage::disk('local')->deleteDirectory($directory);
        Storage::disk('local')->makeDirectory($directory);

        $outputPattern = Storage::disk('local')->path("{$directory}/frame-%03d.jpg");
        $filter = sprintf('fps=1/%s,scale=min(%d\\,iw):-2', $this->decimal($samplingInterval), $maxWidth);
        $process = new Process([
            (string) config('services.openai.ffmpeg_binary', 'ffmpeg'),
            '-hide_banner',
            '-loglevel',
            'error',
            '-i',
            $videoPath,
            '-vf',
            $filter,
            '-frames:v',
            (string) $frameCount,
            '-q:v',
            '3',
            '-y',
            $outputPattern,
        ]);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('FFmpeg gagal mengekstrak frame: '.Str::limit(trim($process->getErrorOutput()), 400));
        }

        $paths = glob(Storage::disk('local')->path("{$directory}/frame-*.jpg")) ?: [];
        sort($paths, SORT_NATURAL);

        if ($paths === []) {
            throw new RuntimeException('Tidak ada frame yang dapat diekstrak dari video.');
        }

        return array_values(array_map(
            fn (string $path, int $index): array => [
                'index' => $index + 1,
                'timestamp_seconds' => round(min($index * $samplingInterval, $duration), 2),
                'absolute_path' => $path,
                'mime_type' => 'image/jpeg',
            ],
            $paths,
            array_keys($paths),
        ));
    }

    public function cleanup(string $analysisId): void
    {
        Storage::disk('local')->deleteDirectory("analysis-frames/{$analysisId}");
    }

    private function probeDuration(string $videoPath): float
    {
        $process = new Process([
            (string) config('services.openai.ffprobe_binary', 'ffprobe'),
            '-v',
            'error',
            '-show_entries',
            'format=duration',
            '-of',
            'default=noprint_wrappers=1:nokey=1',
            $videoPath,
        ]);
        $process->setTimeout(30);
        $process->run();

        $duration = (float) trim($process->getOutput());

        if (! $process->isSuccessful() || $duration <= 0) {
            throw new RuntimeException('Durasi video tidak dapat dibaca oleh FFprobe.');
        }

        return $duration;
    }

    private function decimal(float $value): string
    {
        return rtrim(rtrim(number_format($value, 4, '.', ''), '0'), '.');
    }
}
