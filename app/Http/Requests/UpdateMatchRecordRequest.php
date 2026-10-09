<?php

namespace App\Http\Requests;

use App\Models\MatchRecord;

class UpdateMatchRecordRequest extends MatchRecordRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $matchRecord = $this->route('match_record');

        return $matchRecord instanceof MatchRecord && ($this->user()?->can('update', $matchRecord) ?? false);
    }
}
