<?php

namespace App\Http\Requests;

use App\Models\SuperAdmin;
use App\Models\User;
use App\Support\ProfilePhotoRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'superadmin';
    }

    public function rules(): array
    {
        $user = $this->route('user') ?? $this->route('admin');
        $adminMode = $this->routeIs('admin.*');
        $canAssignOrganizations = $adminMode || ! ($user instanceof User) || $user->role === 'staff';
        $passwordRules = [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'];
        if ($adminMode) {
            $passwordRules[] = 'regex:/^(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}$/';
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($user),
                Rule::unique(SuperAdmin::class, 'email'),
            ],
            'password' => $passwordRules,
            'contact_no' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'plan_id' => ['prohibited'],
            'organization_assignments' => $canAssignOrganizations ? ['nullable', 'array'] : ['prohibited'],
            'organization_assignments.*' => ['required', 'array:id,role'],
            'organization_assignments.*.id' => ['required', 'integer', 'distinct', Rule::exists('organizations', 'id')],
            'organization_assignments.*.role' => ['nullable', Rule::in(['member', 'manager'])],
            'profile_photo' => ProfilePhotoRules::rules(),
            'remove_photo' => ['sometimes', 'boolean'],
        ];
    }
}
