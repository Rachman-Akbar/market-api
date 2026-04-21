<?php

namespace App\Domains\Catalog\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function rules()
    {
        return [
            'catalog_group_id' => 'required|exists:catalog_groups,id',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
