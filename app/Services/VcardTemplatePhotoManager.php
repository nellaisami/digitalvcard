<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class VcardTemplatePhotoManager
{
    public function store(UploadedFile $image): string
    {
        $path = $image->store('vcard-templates', 'public');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('The template preview image could not be stored.');
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if (! $path || ! str_starts_with($path, 'vcard-templates/')) {
            return;
        }

        if (! Storage::disk('public')->delete($path)) {
            throw new RuntimeException('The template preview image could not be deleted.');
        }
    }
}
