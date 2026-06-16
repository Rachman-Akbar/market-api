<?php

namespace App\Domains\Catalog\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Ambil ID kategori dari route jika ini adalah request UPDATE (contoh: /categories/{id})
        $categoryId = $this->route('id') ?? $this->route('category');

        return [
            // Wajib diisi jika parent_id kosong, karena DB meminta NOT NULL
            'catalog_group_id' => [
                'required_without:parent_id',
                'nullable',
                'exists:catalog_groups,id',
            ],

            'parent_id' => [
                'nullable',
                'exists:categories,id',
                // Cegah kategori memilih dirinya sendiri sebagai parent saat update
                $categoryId ? 'not_in:' . $categoryId : '',
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
                // Unik di tabel categories, abaikan ID diri sendiri saat update
                Rule::unique('categories', 'slug')->ignore($categoryId),
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

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
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

    /**
     * Set default nilai untuk field opsional sebelum masuk ke Use Case
     */
    protected function passedValidation(): void
    {
        $this->merge([
            'sort_order' => $this->input('sort_order', 0),
            'is_active' => $this->boolean('is_active', true),
            'is_visible_in_menu' => $this->boolean('is_visible_in_menu', true),
        ]);
    }
}
