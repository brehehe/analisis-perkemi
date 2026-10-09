<?php

namespace App\Enums;

enum ValidationStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Corrected = 'corrected';
}
