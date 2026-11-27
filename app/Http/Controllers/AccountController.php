<?php

namespace App\Http\Controllers;

use App\Models\SuperAdmin;
use App\Models\User;
use App\Models\VcardTemplate;
use App\Services\ProfilePhotoManager;
use App\Support\ProfilePhotoRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $templates = $user instanceof User
            ? VcardTemplate::query()
                ->where('status', 'active')
                ->when($user->vcard_template_id, fn ($query) => $query->orWhereKey($user->vcard_template_id))
                ->orderBy('name')
                ->get()
            : collect();

        return view('account.edit', [
            'user' => $user,
            'templates' => $templates,
            'canSelectTemplate' => $user instanceof User,
        ]);
    }

    public function update(Request $request, ProfilePhotoManager $photos): RedirectResponse
    {
        $user = $request->user();
        $usernameRules = ['required', 'string', 'max:50'];
        $emailRules = ['required', 'email', 'max:255'];
        if ($user instanceof SuperAdmin) {
            $usernameRules[] = Rule::unique('users', 'username');
            $usernameRules[] = Rule::unique('super_admins', 'username')->ignore($user);
            $emailRules[] = Rule::unique('users', 'email');
            $emailRules[] = Rule::unique('super_admins', 'email')->ignore($user);
        } else {
            $usernameRules[] = Rule::unique('users', 'username')->ignore($user);
            $usernameRules[] = Rule::unique('super_admins', 'username');
            $emailRules[] = Rule::unique('users', 'email')->ignore($user);
            $emailRules[] = Rule::unique('super_admins', 'email');
        }
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => $usernameRules,
            'email' => $emailRules,
            'contact_no' => ['nullable', 'string', 'max:30'],
            'profile_photo' => ProfilePhotoRules::rules(),
            'remove_photo' => ['sometimes', 'boolean'],
            'vcard_template_id' => ['prohibited'],
        ];
        if ($user instanceof User) {
            $rules['vcard_template_id'] = [
                'nullable',
                'integer',
                Rule::exists('vcard_templates', 'id')->where(function ($query) use ($user): void {
                    $query->where('status', 'active');
                    if ($user->vcard_template_id) {
                        $query->orWhere('id', $user->vcard_template_id);
                    }
                }),
            ];
        }
        $data = $request->validate($rules);
        unset($data['profile_photo'], $data['remove_photo']);
        if ($user->email !== $data['email']) {
            $data['email_verified_at'] = null;
        }
        $user->update($data);
        $photos->sync($user, $request->file('profile_photo'), $request->boolean('remove_photo'));

        return back()->with('status', 'Account details updated.');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);
        $request->user()->update(['password' => $data['password']]);

        return back()->with('status', 'Password changed successfully.');
    }
}
