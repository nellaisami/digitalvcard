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
        $user = request()->user();
        if ($user->role !== 'superadmin') {
            return $this->organizationDashboard($user);
        }

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

    private function organizationDashboard(User $user): View
    {
        $organizationIds = $user->organizations()
            ->where('organizations.status', 'active')
            ->wherePivot('status', 'active')
            ->pluck('organizations.id');
        $hasMembership = DB::table('organization_users')->where('user_id', $user->id)->exists();
        if ($organizationIds->isNotEmpty()) {
            $userIds = DB::table('organization_users')
                ->whereIn('organization_id', $organizationIds)
                ->where('status', 'active')
                ->pluck('user_id')
                ->unique()
                ->values();
            $vcards = Vcard::whereIn('organization_id', $organizationIds);
            $planIds = Organization::whereIn('id', $organizationIds)->whereNotNull('plan_id')->pluck('plan_id');
            $activeOrganizationCount = $organizationIds->count();
            $activeOrganizationUserCount = DB::table('organization_users')
                ->whereIn('organization_id', $organizationIds)
                ->where('status', 'active')
                ->count();
            $inactiveOrganizationUserCount = DB::table('organization_users')
                ->whereIn('organization_id', $organizationIds)
                ->where('status', 'inactive')
                ->count();
        } elseif ($hasMembership) {
            $userIds = collect();
            $vcards = Vcard::whereRaw('1 = 0');
            $planIds = collect();
            $activeOrganizationCount = 0;
            $activeOrganizationUserCount = 0;
            $inactiveOrganizationUserCount = 0;
        } else {
            $userIds = collect([$user->id]);
            $vcards = Vcard::where('user_id', $user->id);
            $planIds = collect([$user->plan_id])->filter();
            $activeOrganizationCount = 0;
            $activeOrganizationUserCount = 0;
            $inactiveOrganizationUserCount = 0;
        }

        $activeUserCount = User::whereIn('id', $userIds)->where('status', 'active')->count();
        $inactiveUserCount = User::whereIn('id', $userIds)->where('status', 'inactive')->count();
        $activeVcardCount = (clone $vcards)->where('status', 'active')->count();
        $inactiveVcardCount = (clone $vcards)->where('status', 'inactive')->count();

        return view('dashboard', [
            'stats' => [
                ['label' => 'Active users', 'value' => $activeUserCount, 'icon' => 'users', 'tone' => 'blue'],
                ['label' => 'Active vCards', 'value' => $activeVcardCount, 'icon' => 'id-card', 'tone' => 'violet'],
                ['label' => 'Inactive users', 'value' => $inactiveUserCount, 'icon' => 'user-xmark', 'tone' => 'amber'],
                ['label' => 'Inactive vCards', 'value' => $inactiveVcardCount, 'icon' => 'id-card', 'tone' => 'rose'],
                ['label' => 'Active organizations', 'value' => $activeOrganizationCount, 'icon' => 'building', 'tone' => 'emerald'],
                ['label' => 'Inactive organizations', 'value' => 0, 'icon' => 'building', 'tone' => 'slate'],
                ['label' => 'Active org. users', 'value' => $activeOrganizationUserCount, 'icon' => 'user-check', 'tone' => 'cyan'],
                ['label' => 'Inactive org. users', 'value' => $inactiveOrganizationUserCount, 'icon' => 'user-minus', 'tone' => 'orange'],
            ],
            'recentUsers' => User::whereIn('id', $userIds)->latest()->limit(6)->get(),
            'userCounts' => [$activeUserCount, $inactiveUserCount],
            'planCount' => Plan::whereIn('id', $planIds)->count(),
        ]);
    }
}
