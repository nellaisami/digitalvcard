<?php

namespace App\Http\Requests;

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

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'contact_no' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'plan_id' => $this->routeIs('admin.*') ? ['prohibited'] : [
                    'nullable',
                    'integer',
                    Rule::exists('plans', 'id')->where(function ($query) use ($user): void {
                        $query->where('status', 'active');
                        if ($user?->plan_id) {
                            $query->orWhere('id', $user->plan_id);
                        }
                    }),
                ],
            'profile_photo' => ProfilePhotoRules::rules(),
            'remove_photo' => ['sometimes', 'boolean'],
        ];
    }
}
