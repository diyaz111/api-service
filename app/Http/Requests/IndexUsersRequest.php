<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'sortBy' => ['sometimes', 'string', 'in:name,email,created_at'],
        ];
    }

    public function getSortBy(): string
    {
        return $this->input('sortBy', 'created_at');
    }

    public function getPage(): int
    {
        return (int) $this->input('page', 1);
    }

    public function getSearch(): ?string
    {
        $search = $this->input('search');

        return $search !== null && $search !== '' ? $search : null;
    }
}
