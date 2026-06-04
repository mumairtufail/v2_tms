<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class BrandingHelper
{
    /**
     * Get the URL for a branding asset.
     * 
     * Handles cases where symlinks might be disabled by checking for file existence
     * and providing a fallback mechanism.
     *
     * @param string|null $path The relative path to the asset in the disk.
     * @param string $disk The storage disk to use.
     * @return string|null
     */
    public static function getUrl(?string $path, string $disk = 'public'): ?string
    {
        if (!$path) {
            return null;
        }

        // If symlink is disabled, asset('storage/...') might fail.
        // We check if the file exists on the disk.
        if (!Storage::disk($disk)->exists($path)) {
            return null;
        }

        // In environments where symlink is disabled, the 'storage/{path}' route in web.php
        // will handle serving the file via PHP. We use 'url' instead of 'asset'
        // to ensure it hits our custom route if pathing is strict.
        return url('storage/' . $path);
    }

    /**
     * Get the absolute local path for a branding asset.
     * Useful for PDF generation (DomPHP / Snappy).
     *
     * @param string|null $path
     * @param string $disk
     * @return string|null
     */
    public static function getLocalPath(?string $path, string $disk = 'public'): ?string
    {
        if (!$path) {
            return null;
        }

        if (!Storage::disk($disk)->exists($path)) {
            return null;
        }

        return Storage::disk($disk)->path($path);
    }
}
