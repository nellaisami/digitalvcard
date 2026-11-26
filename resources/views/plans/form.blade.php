@extends('layouts.app')
@php
    $editing = $plan->exists;
    $featureSelection = old('features', array_fill_keys($plan->features ?? [], '1'));
    $selectedFeatures = is_array($featureSelection) ? array_keys(array_filter($featureSelection)) : [];
    $allSelected = count(array_intersect(array_keys($features), $selectedFeatures)) === count($features);
    $selectedTemplateIds = old('templates', $editing ? $plan->vcardTemplates->modelKeys() : []);
    $selectedTemplateIds = is_array($selectedTemplateIds) ? array_map('strval', $selectedTemplateIds) : [];
    $allTemplatesSelected = $templates->isNotEmpty()
        && count(array_intersect($templates->modelKeys(), array_map('intval', $selectedTemplateIds))) === $templates->count();
@endphp
@section('title', ($editing ? 'Edit' : 'Add').' plan')
@section('breadcrumb')
<a href="{{ route('plans.index') }}">Plans</a><i class="fa-solid fa-chevron-right"></i><span>{{ $editing ? 'Edit' : 'Add' }} plan</span>
@endsection
@section('heading', $editing ? 'Edit plan' : 'Add new plan')
@section('subheading', 'Set plan pricing, billing frequency, feature access, and limits.')
@section('content')
<form method="POST" action="{{ $editing ? route('plans.update', $plan) : route('plans.store') }}" class="panel form-panel plan-form">
    @csrf @if($editing) @method('PUT') @endif
    <div class="form-section-title"><span class="form-section-icon"><i class="fa-solid fa-layer-group"></i></span><div><h2>Basic information</h2><p>Name and subscription billing details</p></div></div>
    <div class="form-grid grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="form-field"><label for="name">Plan name <span>*</span></label><input id="name" name="name" value="{{ old('name', $plan->name) }}" required>@error('name')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="price">Price ({{ strtoupper(config('app.currency', 'USD')) }}) <span>*</span></label><input id="price" name="price" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('price', $plan->price ?? '0.00') }}" required>@error('price')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="frequency">Frequency <span>*</span></label><select id="frequency" name="frequency" required>@foreach(['monthly' => 'Monthly', 'yearly' => 'Yearly', 'unlimited' => 'Unlimited'] as $value => $label)<option value="{{ $value }}" @selected(old('frequency', $plan->frequency) === $value)>{{ $label }}</option>@endforeach</select>@error('frequency')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="trial_days">Trial days <span>*</span></label><input id="trial_days" name="trial_days" type="number" min="0" max="3650" step="1" value="{{ old('trial_days', $plan->trial_days ?? 0) }}" required>@error('trial_days')<small class="field-error">{{ $message }}</small>@enderror</div>
    </div>
    <div class="form-divider"></div>
    <div class="plan-editor-columns">
        <section class="plan-feature-column">
            <div class="form-section-title"><span class="form-section-icon form-icon-purple"><i class="fa-solid fa-toggle-on"></i></span><div><h2>Feature configuration</h2><p>Choose the features included in this plan</p></div></div>
            <label class="select-all-features"><input type="checkbox" data-feature-master @checked($allSelected)> <strong>Select all features</strong></label>
            <div class="plan-feature-grid">
                @foreach($features as $key => $label)
                    <label class="plan-feature-option">
                        <input type="checkbox" name="features[{{ $key }}]" value="1" data-plan-feature="{{ $key }}" @checked(in_array($key, $selectedFeatures, true))>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @error('features')<small class="field-error">{{ $message }}</small>@enderror
            <div class="plan-limit-grid">
                @foreach($limitFeatures as $key => $label)
                    <div class="plan-limit-card" data-limit-card="{{ $key }}">
                        <label for="{{ $key }}">{{ $label }}</label>
                        <div class="plan-limit-controls"><input id="{{ $key }}" name="feature_limits[{{ $key }}]" type="number" min="0" step="1" value="{{ old('feature_limits.'.$key, data_get($plan->feature_limits, $key)) }}" data-limit-input="{{ $key }}"><label><input type="hidden" name="unlimited_limits[{{ $key }}]" value="0"><input type="checkbox" name="unlimited_limits[{{ $key }}]" value="1" data-unlimited-toggle="{{ $key }}" @checked(old('unlimited_limits.'.$key, $plan->exists ? data_get($plan->feature_limits, $key) === null : true))> Unlimited</label></div>
                        @error('feature_limits.'.$key)<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                @endforeach
            </div>
        </section>
        <aside class="plan-template-panel">
            <div class="form-section-title"><span class="form-section-icon"><i class="fa-regular fa-address-card"></i></span><div><h2>Multi Templates</h2><p>Select the vCard designs available on this plan</p></div></div>
            <label class="select-all-features template-select-all"><input type="checkbox" data-template-master @checked($allTemplatesSelected)> <strong>Select all templates</strong></label>
            @if($templates->isEmpty())
                <div class="template-empty-state"><p>No active vCard templates available.</p><a class="text-link" href="{{ route('vcard-templates.create') }}">Add a vCard template</a></div>
            @else
                <div class="plan-template-grid">
                    @foreach($templates as $template)
                        <label class="plan-template-option">
                            <input type="checkbox" name="templates[]" value="{{ $template->id }}" data-plan-template @checked(in_array((string) $template->id, $selectedTemplateIds, true))>
                            <img src="{{ $template->image_url }}" alt="{{ $template->name }} template preview" data-template-image>
                            <span class="plan-template-name">{{ $template->name }}</span>
                            @if($template->status !== 'active')<small class="template-inactive-badge">Inactive</small>@endif
                        </label>
                    @endforeach
                </div>
            @endif
            @error('templates')<small class="field-error">{{ $message }}</small>@enderror
            @error('templates.*')<small class="field-error">{{ $message }}</small>@enderror
            <small class="template-help-text">Choose multiple templates. Plans without a selection will not include vCard template access.</small>
        </aside>
    </div>
    <div class="form-divider"></div>
    <div class="form-section-title"><span class="form-section-icon"><i class="fa-solid fa-sliders"></i></span><div><h2>Plan access</h2><p>Set the plan's availability and default selection</p></div></div>
    <div class="form-grid grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="form-field"><span class="form-label">Status <span>*</span></span><label class="plan-status-toggle"><input type="hidden" name="status" value="inactive"><input type="checkbox" name="status" value="active" @checked(old('status', $plan->status) === 'active')><span class="plan-status-slider"></span><span data-plan-status-label>{{ old('status', $plan->status) === 'active' ? 'Active' : 'Inactive' }}</span></label>@error('status')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label class="plan-default-option"><input type="hidden" name="is_default" value="0"><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $plan->is_default))> Make default plan</label>@error('is_default')<small class="field-error">{{ $message }}</small>@enderror</div>
    </div>
    <div class="form-actions"><a href="{{ route('plans.index') }}" class="button button-outline">Cancel</a><button type="submit" class="button button-primary"><i class="fa-solid fa-check"></i>{{ $editing ? 'Save changes' : 'Create plan' }}</button></div>
</form>
@endsection
