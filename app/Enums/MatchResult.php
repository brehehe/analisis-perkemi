<?php

namespace App\Enums;

enum MatchResult: string
{
    case Pending = 'pending';
    case Win = 'win';
    case Loss = 'loss';
    case Draw = 'draw';
}
