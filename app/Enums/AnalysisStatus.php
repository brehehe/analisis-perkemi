<?php

namespace App\Enums;

enum AnalysisStatus: string
{
    case Uploaded = 'uploaded';
    case Queued = 'queued';
    case Downloading = 'downloading';
    case Preprocessing = 'preprocessing';
    case Detecting = 'detecting';
    case Tracking = 'tracking';
    case PoseAnalysis = 'pose_analysis';
    case EventAnalysis = 'event_analysis';
    case PerformanceAnalysis = 'performance_analysis';
    case StrategyGeneration = 'strategy_generation';
    case ReportGeneration = 'report_generation';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Uploaded => 'Video diterima',
            self::Queued => 'Menunggu antrean',
            self::Downloading => 'Mengambil video',
            self::Preprocessing => 'Menyiapkan video',
            self::Detecting => 'Mendeteksi atlet',
            self::Tracking => 'Melacak gerakan',
            self::PoseAnalysis => 'Menganalisis pose',
            self::EventAnalysis => 'Mendeteksi peristiwa',
            self::PerformanceAnalysis => 'Mengukur performa',
            self::StrategyGeneration => 'Menyusun strategi',
            self::ReportGeneration => 'Menyusun laporan',
            self::Completed => 'Selesai',
            self::Failed => 'Gagal',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Failed, self::Cancelled], true);
    }
}
