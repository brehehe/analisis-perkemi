<?php

namespace App\Enums;

enum MatchStatus: string
{
    case Scheduled = 'scheduled';
    case Ready = 'ready';
    case InAnalysis = 'in_analysis';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
