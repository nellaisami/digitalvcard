<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Plan;
use App\Models\User;
use App\Models\Vcard;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            ['label' => 'Active users', 'value' => User::where('status', 'active')->count(), 'icon' => 'users', 'tone' => 'blue'],
            ['label' => 'Active vCards', 'value' => Vcard::where('status', 'active')->count(), 'icon' => 'id-card', 'tone' => 'violet'],
            ['label' => 'Inactive users', 'value' => User::where('status', 'inactive')->count(), 'icon' => 'user-xmark', 'tone' => 'amber'],
            ['label' => 'Inactive vCards', 'value' => Vcard::where('status', 'inactive')->count(), 'icon' => 'id-card', 'tone' => 'rose'],
            ['label' => 'Active organizations', 'value' => Organization::where('status', 'active')->count(), 'icon' => 'building', 'tone' => 'emerald'],
            ['label' => 'Inactive organizations', 'value' => Organization::where('status', 'inactive')->count(), 'icon' => 'building', 'tone' => 'slate'],
            ['label' => 'Active org. users', 'value' => DB::table('organization_users')->where('status', 'active')->count(), 'icon' => 'user-check', 'tone' => 'cyan'],
            ['label' => 'Inactive org. users', 'value' => DB::table('organization_users')->where('status', 'inactive')->count(), 'icon' => 'user-minus', 'tone' => 'orange'],
        ];

        return view('dashboard', [
            'stats' => $stats,
            'recentUsers' => User::latest()->limit(6)->get(),
            'userCounts' => [
                User::where('status', 'active')->count(),
                User::where('status', 'inactive')->count(),
            ],
            'planCount' => Plan::count(),
        ]);
    }
}
