<?php

namespace App\Http\Requests;

use App\Enums\AccountStatus;
use App\Enums\AthleteGender;
use App\Models\Athlete;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAthleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Athlete::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'club_id' => ['nullable', 'ulid', 'exists:clubs,id'],
            'coach_id' => ['nullable', 'ulid', 'exists:coaches,id'],
            'identifier' => ['required', 'string', 'max:64', 'unique:athletes,identifier'],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::enum(AthleteGender::class)],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'category' => ['required', 'string', 'max:100'],
            'weight_class' => ['nullable', 'numeric', 'min:20', 'max:250'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:80'],
            'status' => ['required', Rule::enum(AccountStatus::class)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'identifier.unique' => 'NIK atau identitas atlet sudah digunakan.',
            'date_of_birth.before' => 'Tanggal lahir harus sebelum hari ini.',
        ];
    }
}
