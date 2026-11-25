<?php

namespace App\Http\Requests;

use App\Models\Tax;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'superadmin';
    }

    public function rules(): array
    {
        /** @var Tax|null $tax */
        $tax = $this->route('tax');

        return [
            'tax_name' => ['required', 'string', 'max:255', Rule::unique('taxes', 'tax_name')->ignore($tax)],
            'tax_value' => ['required', 'decimal:0,4', 'min:0', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
