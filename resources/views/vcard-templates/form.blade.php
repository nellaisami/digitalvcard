@extends('layouts.app')
@php($editing = $template->exists)
@section('title', ($editing ? 'Edit' : 'Add').' vCard template')
@section('breadcrumb')
<a href="{{ route('vcard-templates.index') }}">vCard Templates</a><i class="fa-solid fa-chevron-right"></i><span>{{ $editing ? 'Edit' : 'Add' }} template</span>
@endsection
@section('heading', $editing ? 'Edit vCard template' : 'Add vCard template')
@section('subheading', 'Add a preview image and control whether plans can select this design.')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('vcard-templates.update', $template) : route('vcard-templates.store') }}" class="panel form-panel compact-form" data-template-validation data-validation-editing="{{ $editing ? 'true' : 'false' }}" novalidate>
    @csrf @if($editing) @method('PUT') @endif
    <div class="form-section-title"><span class="form-section-icon"><i class="fa-regular fa-images"></i></span><div><h2>Template details</h2><p>Template names and previews appear in plan configuration</p></div></div>
    <div class="template-image-editor">
        <img src="{{ $template->image_url }}" alt="Template preview" data-template-preview data-template-image>
        <div>
            <label class="button button-outline button-small photo-upload-button" for="template_image" tabindex="0"><i class="fa-solid fa-arrow-up-from-bracket"></i> {{ $editing ? 'Change preview' : 'Choose preview' }}</label>
            <input id="template_image" class="visually-hidden @error('image') is-invalid @enderror" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-template-image-input aria-describedby="validation-image" aria-invalid="{{ $errors->has('image') ? 'true' : 'false' }}">
            <small class="field-validation-error" id="validation-image" data-validation-message="image" aria-live="polite">@error('image'){{ $message }}@enderror</small>
            <small>JPG, PNG, or WebP. Maximum 2 MB; image dimensions up to 8192 × 8192. {{ $editing ? 'Leave blank to keep the current image.' : '' }}</small>
        </div>
    </div>
    <div class="form-grid grid grid-cols-1 gap-4">
        <div class="form-field">
            <label for="name">Template name <span>*</span></label>
            <input id="name" name="name" value="{{ old('name', $template->name) }}" maxlength="255" required class="@error('name') is-invalid @enderror" aria-describedby="validation-name" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
            <small class="field-validation-error" id="validation-name" data-validation-message="name" aria-live="polite">@error('name'){{ $message }}@enderror</small>
        </div>
        <div class="form-field">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3" maxlength="2000" class="@error('description') is-invalid @enderror" aria-describedby="validation-description" aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}">{{ old('description', $template->description) }}</textarea>
            <small class="field-validation-error" id="validation-description" data-validation-message="description" aria-live="polite">@error('description'){{ $message }}@enderror</small>
        </div>
        <div class="form-field">
            <label for="status">Status <span>*</span></label>
            <select id="status" name="status" required class="@error('status') is-invalid @enderror" aria-describedby="validation-status" aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}">
                <option value="active" @selected(old('status', $template->status) === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $template->status) === 'inactive')>Inactive</option>
            </select>
            <small class="field-validation-error" id="validation-status" data-validation-message="status" aria-live="polite">@error('status'){{ $message }}@enderror</small>
        </div>
    </div>
    <div class="form-actions"><a href="{{ route('vcard-templates.index') }}" class="button button-outline">Cancel</a><button type="submit" class="button button-primary"><i class="fa-solid fa-check"></i>{{ $editing ? 'Save changes' : 'Create template' }}</button></div>
</form>
@endsection
