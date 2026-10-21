<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Stores user cover photos for events and recipes on the public disk.
 */
class CoverImageStore
{
    /**
     * Returns the cover path to persist: the new upload, null when removed, or the current one.
     */
    public function replace(?UploadedFile $upload, ?string $currentPath, string $directory, bool $remove = false): ?string
    {
        if ($upload === null && ! $remove) {
            return $currentPath;
        }

        $this->delete($currentPath);

        return $upload?->store($directory, 'public') ?: null;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
