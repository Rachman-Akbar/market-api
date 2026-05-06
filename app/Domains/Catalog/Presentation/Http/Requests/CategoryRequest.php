<?php

namespace App\Domains\Catalog\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'catalog_group_id' => [
                'nullable',
                'exists:catalog_groups,id',
            ],
            'parent_id' => [
                'nullable',
                'exists:categories,id',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'image_url' => [
                'nullable',
                'string',
                'max:255',
            ],
            'icon_url' => [
                'nullable',
                'string',
                'max:255',
            ],
            'cover_image_url' => [
                'nullable',
                'string',
                'max:255',
            ],
            'sort_order' => [
                'nullable',
                'integer',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'is_visible_in_menu' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}
