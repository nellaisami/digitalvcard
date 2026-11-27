<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class OrganizationLogoManager
{
    public function sync(Organization $organization, ?UploadedFile $logo, bool $remove): void
    {
        if (! $logo && ! $remove) {
            return;
        }

        $previousPath = $organization->logo_path;
        $newPath = $logo?->store('organization-logos', 'public');
        if ($logo && ! $newPath) {
            throw new RuntimeException('The organization logo could not be stored.');
        }

        $organization->update(['logo_path' => $newPath]);

        if ($previousPath && $previousPath !== $newPath) {
            $this->delete($previousPath);
        }
    }

    public function delete(?string $path): void
    {
        if (! $path || ! str_starts_with($path, 'organization-logos/')) {
            return;
        }

        if (! Storage::disk('public')->delete($path)) {
            throw new RuntimeException('The organization logo could not be deleted.');
        }
    }
}
