@extends('layouts.app')
@php($editing = $template->exists)
@section('title', ($editing ? 'Edit' : 'Add').' vCard template')
@section('breadcrumb')
<a href="{{ route('vcard-templates.index') }}">vCard Templates</a><i class="fa-solid fa-chevron-right"></i>{{ $editing ? 'Edit' : 'Add' }} template
@endsection
@section('heading', $editing ? 'Edit vCard template' : 'Add vCard template')
@section('subheading', 'Add a preview image and control whether plans can select this design.')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('vcard-templates.update', $template) : route('vcard-templates.store') }}" class="panel form-panel compact-form">
    @csrf @if($editing) @method('PUT') @endif
    <div class="form-section-title"><span class="form-section-icon"><i class="fa-regular fa-images"></i></span><div><h2>Template details</h2><p>Template names and previews appear in plan configuration</p></div></div>
    <div class="template-image-editor">
        <img src="{{ $template->image_url }}" alt="Template preview" data-template-preview data-template-image>
        <div><label class="button button-outline button-small photo-upload-button" for="template_image"><i class="fa-solid fa-arrow-up-from-bracket"></i> {{ $editing ? 'Change preview' : 'Choose preview' }}</label><input id="template_image" class="visually-hidden" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-template-image-input><small>JPG, PNG, or WebP. Maximum 2 MB; image dimensions up to 4096 × 4096. {{ $editing ? 'Leave blank to keep the current image.' : '' }}</small>@error('image')<small class="field-error">{{ $message }}</small>@enderror</div>
    </div>
    <div class="form-grid grid grid-cols-1 gap-4">
        <div class="form-field"><label for="name">Template name <span>*</span></label><input id="name" name="name" value="{{ old('name', $template->name) }}" required>@error('name')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="description">Description</label><textarea id="description" name="description" rows="3" maxlength="2000">{{ old('description', $template->description) }}</textarea>@error('description')<small class="field-error">{{ $message }}</small>@enderror</div>
        <div class="form-field"><label for="status">Status <span>*</span></label><select id="status" name="status" required><option value="active" @selected(old('status', $template->status) === 'active')>Active</option><option value="inactive" @selected(old('status', $template->status) === 'inactive')>Inactive</option></select>@error('status')<small class="field-error">{{ $message }}</small>@enderror</div>
    </div>
    <div class="form-actions"><a href="{{ route('vcard-templates.index') }}" class="button button-outline">Cancel</a><button type="submit" class="button button-primary"><i class="fa-solid fa-check"></i>{{ $editing ? 'Save changes' : 'Create template' }}</button></div>
</form>
@endsection
