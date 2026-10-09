<?php

namespace App\Enums;

enum MatchType: string
{
    case Randori = 'randori';
    case EmbuIndividual = 'embu_individual';
    case EmbuPair = 'embu_pair';
    case EmbuTeam = 'embu_team';

    public function label(): string
    {
        return match ($this) {
            self::Randori => 'Randori',
            self::EmbuIndividual => 'Embu Perorangan',
            self::EmbuPair => 'Embu Pasangan',
            self::EmbuTeam => 'Embu Beregu',
        };
    }

    public function teamSize(): int
    {
        return match ($this) {
            self::Randori, self::EmbuIndividual => 1,
            self::EmbuPair => 2,
            self::EmbuTeam => 4,
        };
    }

    public function hasOpponent(): bool
    {
        return $this === self::Randori;
    }

    public function allowsMixedDivision(): bool
    {
        return in_array($this, [self::EmbuPair, self::EmbuTeam], true);
    }

    public function categoryLabel(MatchDivision $division): string
    {
        return $this->label().' '.$division->label();
    }
}
