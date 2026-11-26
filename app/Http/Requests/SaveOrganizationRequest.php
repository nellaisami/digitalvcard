<?php

namespace App\Http\Requests;

use App\Models\Organization;
use App\Models\SuperAdmin;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'superadmin';
    }

    public function rules(): array
    {
        /** @var Organization|null $organization */
        $organization = $this->route('organization');
        $owner = $organization?->owner;
        $needsOwner = ! $organization || ! $owner;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('organizations', 'email')->ignore($organization)],
            'owner_first_name' => ['required', 'string', 'max:100'],
            'owner_last_name' => ['required', 'string', 'max:100'],
            'owner_email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($owner),
                Rule::unique(SuperAdmin::class, 'email'),
            ],
            'contact_no' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s().-]{5,28}$/'],
            'password' => [$needsOwner ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'plan_id' => [
                'nullable',
                'integer',
                Rule::exists('plans', 'id')->where(function ($query) use ($organization): void {
                    $query->where('status', 'active');
                    if ($organization?->plan_id) {
                        $query->orWhere('id', $organization->plan_id);
                    }
                }),
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
