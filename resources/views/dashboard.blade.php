@extends('layouts.app')
@section('heading', 'Dashboard overview')
@section('subheading', 'Here’s what’s happening across your workspace today.')
@section('content')
<div class="welcome-banner">
    <div><div class="welcome-kicker"><span class="live-dot"></span> YOUR WORKSPACE IS LOOKING GOOD</div><h2>Good {{ now()->format('H') < 12 ? 'morning' : (now()->format('H') < 18 ? 'afternoon' : 'evening') }}, {{ explode(' ', auth()->user()->name)[0] }} <span>✦</span></h2><p>Here’s your latest overview. Keep up the great work.</p></div>
    <div class="welcome-art" aria-hidden="true"><div class="art-card art-card-back"><i class="fa-solid fa-arrow-trend-up"></i></div><div class="art-card art-card-front"><i class="fa-solid fa-chart-pie"></i><span></span><span></span><span></span></div><div class="art-sparkle">✦</div></div>
</div>
<div class="section-title-row"><div><h2>Workspace overview</h2><p>A snapshot of your key metrics</p></div><span class="period-chip"><i class="fa-regular fa-calendar"></i> All time <i class="fa-solid fa-chevron-down"></i></span></div>
<div class="stats-grid grid grid-cols-2 gap-3 md:grid-cols-2 lg:grid-cols-4">
    @foreach($stats as $stat)
        <article class="stat-card"><div class="stat-top"><span class="stat-label">{{ $stat['label'] }}</span><span class="stat-icon tone-{{ $stat['tone'] }}"><i class="fa-solid fa-{{ $stat['icon'] }}"></i></span></div><div class="stat-value">{{ number_format($stat['value']) }}</div><div class="stat-meta"><span class="stat-meta-dot tone-dot-{{ $stat['tone'] }}"></span><span>{{ $stat['label'] === 'Active users' ? 'Across all roles' : 'Current total' }}</span></div></article>
    @endforeach
</div>
<div class="dashboard-grid grid grid-cols-1 gap-4 lg:grid-cols-2">
    <section class="panel chart-panel"><div class="panel-heading"><div><h2>User activity</h2><p>Account status distribution</p></div><span class="chart-badge"><i class="fa-solid fa-circle"></i> User accounts</span></div><div class="chart-wrap"><canvas id="usersChart" aria-label="Active and inactive user chart"></canvas></div><div class="chart-legend"><span><i class="legend-active"></i> Active <strong>{{ $userCounts[0] }}</strong></span><span><i class="legend-inactive"></i> Inactive <strong>{{ $userCounts[1] }}</strong></span><span><i class="legend-plans"></i> Plans <strong>{{ $planCount }}</strong></span></div></section>
    <section class="panel activity-panel"><div class="panel-heading"><div><h2>Quick insights</h2><p>Your workspace at a glance</p></div><span class="insight-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></span></div><div class="insight-list"><div class="insight-item"><span class="insight-mark insight-purple"><i class="fa-solid fa-users"></i></span><div><strong>{{ number_format($userCounts[0]) }} active users</strong><small>People ready to collaborate</small></div><span class="insight-arrow"><i class="fa-solid fa-arrow-up-right-from-square"></i></span></div><div class="insight-item"><span class="insight-mark insight-green"><i class="fa-solid fa-id-card"></i></span><div><strong>{{ number_format($stats[1]['value']) }} active vCards</strong><small>Digital cards in circulation</small></div><span class="insight-arrow"><i class="fa-solid fa-arrow-up-right-from-square"></i></span></div><div class="insight-item"><span class="insight-mark insight-blue"><i class="fa-solid fa-building"></i></span><div><strong>{{ number_format($stats[4]['value']) }} organizations</strong><small>Active teams in your space</small></div><span class="insight-arrow"><i class="fa-solid fa-arrow-up-right-from-square"></i></span></div></div><a class="insight-link" href="{{ route('users.index') }}">Explore your workspace <i class="fa-solid fa-arrow-right"></i></a></section>
</div>
<section class="panel table-panel"><div class="panel-heading table-panel-heading"><div><h2>Recently joined</h2><p>The latest people to join your workspace</p></div><a class="button button-outline button-small" href="{{ route('users.index') }}">View all <i class="fa-solid fa-arrow-right"></i></a></div>
    @if($recentUsers->isEmpty())<div class="empty-state"><span><i class="fa-regular fa-face-smile"></i></span><strong>It’s quiet here for now</strong><p>New users will show up here when they join.</p></div>
    @else <div class="table-scroll"><table><thead><tr><th>USER</th><th>ROLE</th><th>STATUS</th><th>JOINED</th><th></th></tr></thead><tbody>
    @foreach($recentUsers as $user)<tr><td><div class="user-cell"><span class="avatar avatar-small">{{ strtoupper(substr($user->name, 0, 1)) }}</span><span><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></span></div></td><td><span class="role-badge">{{ ucfirst($user->role) }}</span></td><td><span class="status-badge status-{{ $user->status }}"><i></i>{{ ucfirst($user->status) }}</span></td><td class="date-cell">{{ $user->created_at->format('M j, Y') }}</td><td><a class="table-link" href="{{ auth()->user()->role === 'superadmin' ? route('users.show', $user) : route('account.edit') }}" aria-label="View {{ $user->name }}"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></td></tr>@endforeach
    </tbody></table></div>@endif
</section>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
if (window.Chart) {
    const chart = document.getElementById('usersChart');
    new Chart(chart, {
        type: 'doughnut',
        data: { labels: ['Active', 'Inactive'], datasets: [{ data: @json($userCounts), backgroundColor: ['#6c63ff', '#e7e8f0'], hoverBackgroundColor: ['#554cf2', '#d2d3df'], borderWidth: 0, borderRadius: 8, spacing: 5, cutout: '76%' }] },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { backgroundColor: '#17182a', padding: 12, cornerRadius: 8 } } }
    });
}
</script>
@endpush
