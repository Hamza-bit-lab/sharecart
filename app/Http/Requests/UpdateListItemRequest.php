<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for updating a list item (name, quantity, completed).
 */
class UpdateListItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        // For authenticated users, defer to the policy.
        if ($this->user()) {
            return $this->user()->can('update', $this->route('item'));
        }

        // Guests reach this via the list.access middleware (web) or list.access.api (API).
        // Those middleware already verified that this list is accessible for the current session/token,
        // so we allow the request here.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:9999'],
            'completed' => ['sometimes', 'boolean'],
        ];
    }
}
