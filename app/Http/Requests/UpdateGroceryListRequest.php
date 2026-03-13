<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for updating a grocery list (e.g. rename).
 */
class UpdateGroceryListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('list'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
            'icon' => ['nullable', 'string', 'max:10'],
        ];
    }
}
