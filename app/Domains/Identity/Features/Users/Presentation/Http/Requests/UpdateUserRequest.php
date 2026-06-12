<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Users\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password' => ['nullable', 'string', 'min:8'],
            'name' => ['nullable', 'string', 'max:255'],
            'firebase_uid' => ['nullable', 'string', 'max:255', 'unique:users,firebase_uid,' . $userId],
            'avatar' => ['nullable', 'string', 'max:255'],
            'is_email_verified' => ['sometimes', 'required', 'boolean'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ];
    }
}
