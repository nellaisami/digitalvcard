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
            'price' => ['required', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'html_code' => ['required', 'string', 'max:500000'],
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
