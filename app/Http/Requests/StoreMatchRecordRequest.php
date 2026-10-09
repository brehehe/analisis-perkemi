<?php

namespace App\Http\Requests;

use App\Models\MatchRecord;

class StoreMatchRecordRequest extends MatchRecordRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', MatchRecord::class) ?? false;
    }
}
