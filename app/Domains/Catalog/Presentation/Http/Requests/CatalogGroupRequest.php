<?php

namespace App\Domains\Catalog\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CatalogGroupRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
