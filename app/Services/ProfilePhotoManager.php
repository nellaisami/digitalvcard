<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProfilePhotoManager
{
    public function sync(User $user, ?UploadedFile $photo, bool $remove): void
    {
        if (! $photo && ! $remove) {
            return;
        }

        $previousPath = $user->profile_photo;
        $newPath = $photo ? $this->store($photo) : null;

        $user->forceFill(['profile_photo' => $newPath])->save();

        if ($previousPath && $previousPath !== $newPath) {
            $this->delete($previousPath);
        }
    }

    public function delete(?string $path): void
    {
        if (! $path || ! str_starts_with($path, 'profile-photos/')) {
            return;
        }

        if (! Storage::disk('public')->delete($path)) {
            throw new RuntimeException('The old profile photo could not be deleted.');
        }
    }

    private function store(UploadedFile $photo): string
    {
        $path = $photo->store('profile-photos', 'public');

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('The profile photo could not be stored.');
        }

        return $path;
    }
}
