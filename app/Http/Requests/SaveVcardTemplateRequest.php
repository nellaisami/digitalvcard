<?php

namespace App\Http\Requests;

use App\Models\VcardTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveVcardTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'superadmin';
    }

    public function rules(): array
    {
        /** @var VcardTemplate|null $template */
        $template = $this->route('vcard_template');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('vcard_templates', 'name')->ignore($template)],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => [
                $template ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:min_width=1,min_height=1,max_width=8192,max_height=8192',
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
