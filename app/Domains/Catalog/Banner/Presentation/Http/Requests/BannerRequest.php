<?php

namespace App\Domains\Catalog\Banner\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Set true jika belum ada auth
    }

    public function rules(): array
    {
        return [
            'image_url' => 'required|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ];
    }
}
