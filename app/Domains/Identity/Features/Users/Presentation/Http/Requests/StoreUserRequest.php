<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Users\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
        return [
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8'],
            'name' => ['nullable', 'string', 'max:255'],
            'firebase_uid' => ['nullable', 'string', 'max:255', 'unique:users,firebase_uid'],
            'avatar' => ['nullable', 'string', 'max:255'],
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ];
    }
}
