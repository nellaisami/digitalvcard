<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'superadmin';
    }

    public function rules(): array
    {
        return ['role' => ['required', Rule::in(['admin', 'manager', 'editor', 'staff'])]];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $user = $this->route('user');
            if ($user && $this->input('role') === 'admin' && $user->ownedOrganizations()->exists()) {
                $validator->errors()->add('role', 'An organization owner cannot be assigned the Admin role.');
            }
        });
    }
}
