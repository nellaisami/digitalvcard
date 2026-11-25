<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Models\Vcard;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function admin(): View
    {
        return view('sections.list', [
            'title' => 'Admin',
            'description' => 'Administrator accounts and access roles.',
            'headers' => ['Name', 'Email', 'Role', 'Status'],
            'rows' => User::whereIn('role', ['superadmin', 'admin'])->latest()->get()
                ->map(fn (User $user) => [$user->name, $user->email, ucfirst($user->role), ucfirst($user->status)]),
            'createUrl' => route('users.create'),
            'createLabel' => 'Add administrator',
        ]);
    }

    public function organizations(): View
    {
        return view('sections.list', [
            'title' => 'Organizations',
            'description' => 'Organizations registered in your workspace.',
            'headers' => ['Organization', 'Members', 'Status', 'Created'],
            'rows' => Organization::withCount('users')->latest()->get()
                ->map(fn (Organization $item) => [$item->name, $item->users_count, ucfirst($item->status), $item->created_at->format('M j, Y')]),
        ]);
    }

    public function vcards(): View
    {
        return view('sections.list', [
            'title' => 'vCards',
            'description' => 'Digital business cards created by your users.',
            'headers' => ['Card', 'Owner', 'Status', 'Created'],
            'rows' => Vcard::with('user')->latest()->get()
                ->map(fn (Vcard $item) => [$item->title, $item->user?->name ?? '—', ucfirst($item->status), $item->created_at->format('M j, Y')]),
        ]);
    }
}
