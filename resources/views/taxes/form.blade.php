@extends('layouts.app')
@php($editing = $tax->exists)
@section('title', ($editing ? 'Edit' : 'Add').' tax')
@section('breadcrumb')
<a href="{{ route('plans.index') }}">Plans</a><i class="fa-solid fa-chevron-right"></i><a href="{{ route('taxes.index') }}">Tax settings</a><i class="fa-solid fa-chevron-right"></i>{{ $editing ? 'Edit' : 'Add' }} tax
@endsection
@section('heading', $editing ? 'Edit tax' : 'Add tax')
@section('subheading', 'Tax rates are calculated against the plan base price; multiple active taxes are additive.')
@section('content')
<form method="POST" action="{{ $editing ? route('taxes.update', $tax) : route('taxes.store') }}" class="panel form-panel compact-form">
    @csrf @if($editing) @method('PUT') @endif
    <div class="form-section-title"><span class="form-section-icon"><i class="fa-solid fa-percent"></i></span><div><h2>Tax details</h2><p>Enter the tax name and percentage</p></div></div>
    <div class="form-grid grid grid-cols-1 gap-4">
        <div class="form-field"><label for="tax_name">Tax name <span>*</span></label><input id="tax_name" name="tax_name" value="{{ old('tax_name', $tax->tax_name) }}" placeholder="GST, VAT, Sales Tax" required>@error('tax_name')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="tax_value">Tax percentage <span>*</span></label><div class="percentage-input"><input id="tax_value" name="tax_value" type="number" min="0" max="100" step="0.0001" value="{{ old('tax_value', $tax->tax_value) }}" required><span>%</span></div>@error('tax_value')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="status">Status <span>*</span></label><select id="status" name="status" required><option value="active" @selected(old('status', $tax->status) === 'active')>Active</option><option value="inactive" @selected(old('status', $tax->status) === 'inactive')>Inactive</option></select>@error('status')<small class="field-error">{{ $message }}</small>@enderror</div>
    </div>
    <div class="form-actions"><a href="{{ route('taxes.index') }}" class="button button-outline">Cancel</a><button type="submit" class="button button-primary"><i class="fa-solid fa-check"></i>{{ $editing ? 'Save changes' : 'Add tax' }}</button></div>
</form>
@endsection
