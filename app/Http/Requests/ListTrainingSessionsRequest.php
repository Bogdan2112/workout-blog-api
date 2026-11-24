<?php

namespace App\Http\Requests;

class ListTrainingSessionsRequest extends PaginationRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'date' => ['sometimes', 'required', 'date_format:Y-m-d'],
        ];
    }

    public function selectedDate(): ?string
    {
        return $this->validated('date');
    }
}
