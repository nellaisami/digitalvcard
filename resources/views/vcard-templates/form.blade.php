@extends('layouts.app')
@php($editing = $template->exists)
@section('title', ($editing ? 'Edit' : 'Add').' vCard template')
@section('breadcrumb')
<a href="{{ route('vcard-templates.index') }}">vCard Templates</a><i class="fa-solid fa-chevron-right"></i><span>{{ $editing ? 'Edit' : 'Add' }} template</span>
@endsection
@section('heading', $editing ? 'Edit vCard template' : 'Add vCard template')
@section('subheading', 'Set template pricing and HTML, then review the live preview.')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('vcard-templates.update', $template) : route('vcard-templates.store') }}" class="panel form-panel template-editor-form" data-template-validation data-validation-editing="{{ $editing ? 'true' : 'false' }}" novalidate>
    @csrf @if($editing) @method('PUT') @endif
    <div class="template-editor-layout">
        <div class="template-editor-main">
            <div class="form-section-title"><span class="form-section-icon"><i class="fa-regular fa-images"></i></span><div><h2>Template details</h2><p>Users select a template from account settings; organizations can select one from their workspace.</p></div></div>
            <div class="template-image-editor">
                <img src="{{ $template->image_url }}" alt="Template preview" data-template-preview data-template-image>
                <div>
                    <label class="button button-outline button-small photo-upload-button" for="template_image" tabindex="0"><i class="fa-solid fa-arrow-up-from-bracket"></i> {{ $editing ? 'Change preview' : 'Choose preview' }}</label>
                    <input id="template_image" class="visually-hidden @error('image') is-invalid @enderror" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-template-image-input aria-describedby="validation-image" aria-invalid="{{ $errors->has('image') ? 'true' : 'false' }}">
                    <small class="field-validation-error" id="validation-image" data-validation-message="image" aria-live="polite">@error('image'){{ $message }}@enderror</small>
                    <small>JPG, PNG, or WebP. Maximum 2 MB; image dimensions up to 8192 × 8192. {{ $editing ? 'Leave blank to keep the current image.' : '' }}</small>
                </div>
            </div>
            <div class="form-grid grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="form-field">
                    <label for="name">Template name <span>*</span></label>
                    <input id="name" name="name" value="{{ old('name', $template->name) }}" maxlength="255" required class="@error('name') is-invalid @enderror" aria-describedby="validation-name" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
                    <small class="field-validation-error" id="validation-name" data-validation-message="name" aria-live="polite">@error('name'){{ $message }}@enderror</small>
                </div>
                <div class="form-field">
                    <label for="price">Price <span>*</span></label>
                    <input id="price" name="price" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('price', $template->price ?? '0.00') }}" required class="@error('price') is-invalid @enderror" aria-describedby="validation-price" aria-invalid="{{ $errors->has('price') ? 'true' : 'false' }}">
                    <small class="field-validation-error" id="validation-price" data-validation-message="price" aria-live="polite">@error('price'){{ $message }}@enderror</small>
                </div>
                <div class="form-field">
                    <label for="used_count">Used count</label>
                    <input id="used_count" name="used_count" type="number" value="{{ $usedCount }}" readonly aria-readonly="true">
                    <small class="field-hint">Distinct users who selected this template in their account settings.</small>
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
            <div class="form-divider"></div>
            <div class="form-section-title"><span class="form-section-icon form-icon-purple"><i class="fa-solid fa-code"></i></span><div><h2>HTML code</h2><p>Edit the template HTML with syntax highlighting. Scripts are disabled in the preview.</p></div></div>
            <div class="form-field">
                <label for="html_code">HTML code <span>*</span></label>
                <textarea id="html_code" name="html_code" rows="24" maxlength="500000" required spellcheck="false" class="font-mono text-sm @error('html_code') is-invalid @enderror" data-template-html-editor aria-describedby="validation-html_code" aria-invalid="{{ $errors->has('html_code') ? 'true' : 'false' }}">{{ old('html_code', $template->html_code) }}</textarea>
                <small class="field-validation-error" id="validation-html_code" data-validation-message="html_code" aria-live="polite">@error('html_code'){{ $message }}@enderror</small>
            </div>
        </div>
        <aside class="template-editor-preview">
            <label>Live preview</label>
            <iframe title="Live template preview" sandbox="" data-template-html-preview srcdoc="{{ old('html_code', $template->html_code) }}"></iframe>
            <small>Scripts are disabled in the preview for safety.</small>
        </aside>
    </div>
    <div class="form-actions"><a href="{{ route('vcard-templates.index') }}" class="button button-outline">Cancel</a><button type="submit" class="button button-primary"><i class="fa-solid fa-check"></i>{{ $editing ? 'Save changes' : 'Create template' }}</button></div>
</form>
@endsection
