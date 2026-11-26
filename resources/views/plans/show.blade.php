@extends('layouts.app')
@section('title', $plan->name)
@section('breadcrumb')
<a href="{{ route('plans.index') }}">Plans</a><i class="fa-solid fa-chevron-right"></i><span>{{ $plan->name }}</span>
@endsection
@section('heading', $plan->name)
@section('subheading', 'Plan details, enabled features, limits, and current tax estimate.')
@section('heading-action')
<a class="button button-primary" href="{{ route('plans.edit', $plan) }}"><i class="fa-regular fa-pen-to-square"></i> Edit plan</a>
@endsection
@section('content')
<div class="plan-detail-grid">
    <section class="panel plan-detail-card">
        <div class="form-section-title"><span class="form-section-icon"><i class="fa-solid fa-circle-info"></i></span><div><h2>Plan information</h2><p>Pricing and account availability</p></div></div>
        <dl class="plan-detail-list">
            <div><dt>Plan name</dt><dd>{{ $plan->name }}</dd></div>
            <div><dt>Base price</dt><dd>{{ $currency }} {{ number_format((float) $plan->price, 2) }}</dd></div>
            <div><dt>Frequency</dt><dd>{{ ucfirst($plan->frequency) }}</dd></div>
            <div><dt>Multi Templates</dt><dd>{{ $plan->vcardTemplates->count() }} selected</dd></div>
            <div><dt>Trial days</dt><dd>{{ $plan->trial_days }}</dd></div>
            <div><dt>Status</dt><dd><span class="status-badge {{ $plan->status === 'active' ? 'status-active' : 'status-inactive' }}">{{ ucfirst($plan->status) }}</span></dd></div>
            <div><dt>Default plan</dt><dd>{{ $plan->is_default ? 'Yes' : 'No' }}</dd></div>
            <div><dt>Assigned users</dt><dd>{{ $plan->users_count }}</dd></div>
            <div><dt>Created</dt><dd>{{ $plan->created_at->format('M j, Y') }}</dd></div>
            <div><dt>Updated</dt><dd>{{ $plan->updated_at->format('M j, Y') }}</dd></div>
        </dl>
    </section>
    <section class="panel plan-detail-card">
        <div class="form-section-title"><span class="form-section-icon form-icon-purple"><i class="fa-solid fa-list-check"></i></span><div><h2>Enabled features</h2><p>{{ count($plan->features ?? []) }} features included</p></div></div>
        @if(count($plan->features ?? []))
            <ul class="plan-feature-list">@foreach($plan->features as $key)<li><i class="fa-solid fa-check"></i><span>{{ $features[$key] ?? str($key)->headline() }}</span>
                @if($key === 'no_of_vcards')<strong>{{ $plan->vcardTemplates->count() }} selected</strong>
                @elseif(array_key_exists($key, $plan->feature_limits ?? []))<strong>{{ $plan->feature_limits[$key] === null ? 'Unlimited' : number_format($plan->feature_limits[$key]) }}</strong>@endif
            </li>@endforeach</ul>
        @else
            <p class="plan-muted-copy">No features are enabled for this plan.</p>
        @endif
        @if($plan->vcardTemplates->isNotEmpty())
            <div class="detail-template-grid">@foreach($plan->vcardTemplates as $template)<div class="detail-template-card"><img src="{{ $template->image_url }}" alt="{{ $template->name }} template preview" data-template-image><strong>{{ $template->name }}</strong></div>@endforeach</div>
        @endif
    </section>
    <section class="panel plan-detail-card tax-estimate-card">
        <div class="form-section-title"><span class="form-section-icon"><i class="fa-solid fa-percent"></i></span><div><h2>Tax estimate</h2><p>Active taxes applied additively to the base plan price</p></div></div>
        <dl class="plan-detail-list">
            <div><dt>Base plan price</dt><dd>{{ $currency }} {{ number_format($taxCalculation['base'], 2) }}</dd></div>
            @forelse($taxCalculation['taxes'] as $tax)
                <div><dt>{{ $tax['name'] }} ({{ number_format($tax['rate'], 4) }}%)</dt><dd>{{ $currency }} {{ number_format($tax['amount'], 2) }}</dd></div>
            @empty
                <div><dt>Active taxes</dt><dd>None configured</dd></div>
            @endforelse
            <div class="plan-total-row"><dt>Estimated total</dt><dd>{{ $currency }} {{ number_format($taxCalculation['total'], 2) }}</dd></div>
        </dl>
        <small class="plan-muted-copy">This is a current estimate. Updating taxes does not change historical transaction amounts.</small>
    </section>
</div>
<div class="plan-detail-footer"><a class="button button-outline" href="{{ route('plans.index') }}"><i class="fa-solid fa-arrow-left"></i> Back to plans</a></div>
@endsection
