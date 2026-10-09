<?php

namespace App\Enums;

enum AthleteGender: string
{
    case Male = 'male';
    case Female = 'female';

    public function label(): string
    {
        return match ($this) {
            self::Male => 'Putra',
            self::Female => 'Putri',
        };
    }
}
