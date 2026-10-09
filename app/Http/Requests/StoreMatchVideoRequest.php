<?php

namespace App\Http\Requests;

use App\Enums\VideoSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMatchVideoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('videos.upload') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'source' => ['required', Rule::enum(VideoSource::class)],
            'youtube_url' => [
                Rule::requiredIf($this->string('source')->toString() === VideoSource::Youtube->value),
                'nullable',
                'url:http,https',
                'max:2048',
            ],
            'video_file' => [
                Rule::requiredIf($this->string('source')->toString() === VideoSource::Upload->value),
                'nullable',
                'file',
                'mimes:mp4,mov,mkv,webm',
                'extensions:mp4,mov,mkv,webm',
                'max:512000',
            ],
            'focus_description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('youtube_url') || ! $this->filled('youtube_url')) {
                    return;
                }

                $host = parse_url($this->string('youtube_url')->toString(), PHP_URL_HOST);

                if (! in_array($host, ['youtube.com', 'www.youtube.com', 'youtu.be', 'm.youtube.com'], true)) {
                    $validator->errors()->add('youtube_url', 'Gunakan URL video YouTube yang valid.');
                }
            },
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'video_file.max' => 'Ukuran video maksimal 500 MB.',
            'video_file.mimes' => 'Format video harus MP4, MOV, MKV, atau WebM.',
            'video_file.extensions' => 'Ekstensi video harus mp4, mov, mkv, atau webm.',
            'focus_description.max' => 'Petunjuk target maksimal 1.000 karakter.',
        ];
    }
}
