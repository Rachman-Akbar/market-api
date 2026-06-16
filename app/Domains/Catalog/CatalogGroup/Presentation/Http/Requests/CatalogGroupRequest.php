<?php

namespace App\Domains\Catalog\CatalogGroup\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CatalogGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('isActive')) {
            $data['is_active'] = $this->boolean('isActive');
        }

        if ($this->has('name')) {
            $data['name'] = trim((string) $this->input('name'));
        }

        if ($this->has('slug')) {
            $slug = trim((string) $this->input('slug'));
            $data['slug'] = $slug !== '' ? Str::slug($slug) : null;
        }

        if (! $this->has('slug') && $this->has('name')) {
            $data['slug'] = Str::slug((string) $this->input('name'));
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => [$this->isMethod('post') ? 'required' : 'sometimes', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('catalog_groups', 'slug')->ignore($id),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kelompok katalog wajib diisi.',
            'name.string' => 'Nama kelompok katalog harus berupa teks.',
            'name.max' => 'Nama kelompok katalog maksimal 255 karakter.',
            'slug.string' => 'Slug harus berupa teks.',
            'slug.max' => 'Slug maksimal 255 karakter.',
            'slug.unique' => 'Slug sudah digunakan.',
            'is_active.boolean' => 'Status aktif harus bernilai benar atau salah.',
        ];
    }
}