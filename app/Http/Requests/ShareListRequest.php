<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for sharing a list with another user (by email).
 */
class ShareListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('share', $this->route('list'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
        ];
    }

    /**
     * Ensure user does not share with self and is not already shared.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $list = $this->route('list');
            $user = User::where('email', $this->validated('email'))->first();
            if ($user && $user->id === $this->user()->id) {
                $validator->errors()->add('email', 'You cannot share a list with yourself.');
            }
            if ($user && $list->sharedWith()->where('user_id', $user->id)->exists()) {
                $validator->errors()->add('email', 'This list is already shared with that user.');
            }
        });
    }
}
