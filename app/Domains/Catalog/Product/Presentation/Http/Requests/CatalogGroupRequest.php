<?php

namespace App\Domains\Catalog\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CatalogGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Pastikan ini true agar tidak Error 403 Forbidden
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255',
            'is_active'   => 'nullable|boolean', // Tambahkan ini agar lolos dari $request->validated()
        ];
    }
}
