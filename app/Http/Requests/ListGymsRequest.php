<?php

namespace App\Http\Requests;

class ListGymsRequest extends PaginationRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function cityFilter(): ?string
    {
        return $this->validated('city');
    }
}
