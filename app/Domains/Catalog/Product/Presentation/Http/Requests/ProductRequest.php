<?php

namespace App\Domains\Catalog\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'store_id' => [
                'nullable',
                'exists:stores,id',
            ],
            'primary_category_id' => [
                'required',
                'exists:categories,id',
            ],
            'category_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'category_ids.*' => [
                'integer',
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
            'sku' => [
                'nullable',
                'string',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'short_description' => [
                'nullable',
                'string',
                'max:255',
            ],
            'brand' => [
                'nullable',
                'string',
                'max:100',
            ],
            'weight_gram' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'stock' => [
                'required',
                'integer',
                'min:0',
            ],
            'thumbnail' => [
                'nullable',
                'string',
                'max:255',
            ],
            'status' => [
                'nullable',
                'string',
                'in:draft,published,archived',
            ],
            'is_featured' => [
                'nullable',
                'boolean',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'images' => [
                'nullable',
                'array',
            ],
            'images.*' => [
                'url',
            ],
        ];
    }
}
