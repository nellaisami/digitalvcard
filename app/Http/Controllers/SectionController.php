<?php

namespace App\Http\Controllers;

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
            'rows' => User::where('role', 'admin')->latest()->get()
                ->map(fn (User $user) => [$user->name, $user->email, ucfirst($user->role), ucfirst($user->status)]),
            'createUrl' => route('users.create'),
            'createLabel' => 'Add administrator',
        ]);
    }

    public function vcards(): View
    {
        $user = request()->user();
        $cards = Vcard::with('user');
        if ($user->role !== 'superadmin') {
            $memberships = $user->organizations()->get(['organizations.id', 'organizations.status']);
            $activeOrganizationIds = $memberships
                ->filter(fn ($organization) => $organization->status === 'active' && $organization->pivot->status === 'active')
                ->modelKeys();
            if ($activeOrganizationIds !== []) {
                $cards->whereIn('organization_id', $activeOrganizationIds);
            } elseif ($memberships->isNotEmpty()) {
                $cards->whereRaw('1 = 0');
            } else {
                $cards->where('user_id', $user->id);
            }
        }

        return view('sections.list', [
            'title' => 'vCards',
            'description' => 'Digital business cards created by your users.',
            'headers' => ['Card', 'Owner', 'Status', 'Created'],
            'rows' => $cards->latest()->get()
                ->map(fn (Vcard $item) => [$item->title, $item->user?->name ?? '—', ucfirst($item->status), $item->created_at->format('M j, Y')]),
        ]);
    }
}
