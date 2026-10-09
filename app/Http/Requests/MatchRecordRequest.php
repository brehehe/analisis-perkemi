<?php

namespace App\Http\Requests;

use App\Enums\AthleteGender;
use App\Enums\MatchDivision;
use App\Enums\MatchResult;
use App\Enums\MatchStatus;
use App\Enums\MatchType;
use App\Models\Athlete;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class MatchRecordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $matchType = MatchType::tryFrom($this->string('match_type')->toString());
        $hasOpponent = $matchType?->hasOpponent() ?? false;

        return [
            'competition_event_id' => ['nullable', 'ulid', 'exists:competition_events,id'],
            'athlete_ids' => ['required', 'array', 'size:'.($matchType?->teamSize() ?? 1)],
            'athlete_ids.*' => ['required', 'ulid', 'distinct', 'exists:athletes,id'],
            'opponent_name' => [
                Rule::requiredIf($hasOpponent),
                Rule::excludeIf(! $hasOpponent),
                'nullable',
                'string',
                'max:255',
            ],
            'opponent_club' => [Rule::excludeIf(! $hasOpponent), 'nullable', 'string', 'max:255'],
            'match_date' => ['required', 'date'],
            'match_type' => ['required', Rule::enum(MatchType::class)],
            'division' => ['required', Rule::enum(MatchDivision::class)],
            'result' => ['required', Rule::enum(MatchResult::class)],
            'athlete_score' => ['nullable', 'integer', 'min:0', 'max:999'],
            'opponent_score' => [Rule::excludeIf(! $hasOpponent), 'nullable', 'integer', 'min:0', 'max:999'],
            'status' => ['required', Rule::enum(MatchStatus::class)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['match_type', 'division', 'athlete_ids', 'athlete_ids.*'])) {
                return;
            }

            $matchType = MatchType::tryFrom($this->string('match_type')->toString());
            $division = MatchDivision::tryFrom($this->string('division')->toString());
            $athleteIds = collect($this->input('athlete_ids', []))
                ->filter(fn (mixed $id): bool => is_string($id) && $id !== '')
                ->values();

            if ($matchType === null || $division === null || $athleteIds->count() !== $matchType->teamSize()) {
                return;
            }

            if ($division === MatchDivision::Mixed && ! $matchType->allowsMixedDivision()) {
                $validator->errors()->add('division', 'Kategori Campuran hanya tersedia untuk Embu Pasangan atau Embu Beregu.');

                return;
            }

            $athletes = Athlete::query()
                ->select(['id', 'gender'])
                ->whereKey($athleteIds)
                ->get();

            if ($athletes->count() !== $athleteIds->count()) {
                return;
            }

            $genders = $athletes->pluck('gender')->map(
                fn (AthleteGender $gender): string => $gender->value,
            );

            if ($division === MatchDivision::Male && $genders->contains(AthleteGender::Female->value)) {
                $validator->errors()->add('athlete_ids', 'Kategori Putra hanya dapat diisi oleh atlet putra.');
            }

            if ($division === MatchDivision::Female && $genders->contains(AthleteGender::Male->value)) {
                $validator->errors()->add('athlete_ids', 'Kategori Putri hanya dapat diisi oleh atlet putri.');
            }

            if ($division === MatchDivision::Mixed && $genders->unique()->count() !== 2) {
                $validator->errors()->add('athlete_ids', 'Kategori Campuran harus memuat atlet putra dan putri.');
            }
        }];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'athlete_ids.required' => 'Pilih atlet yang mengikuti pertandingan.',
            'athlete_ids.size' => 'Jenis pertandingan ini membutuhkan tepat :size atlet.',
            'athlete_ids.*.required' => 'Setiap posisi tim harus diisi.',
            'athlete_ids.*.distinct' => 'Satu atlet tidak dapat dipilih dua kali dalam tim yang sama.',
            'athlete_ids.*.exists' => 'Atlet yang dipilih tidak ditemukan.',
            'opponent_name.required' => 'Nama lawan wajib diisi untuk pertandingan Randori.',
        ];
    }
}
