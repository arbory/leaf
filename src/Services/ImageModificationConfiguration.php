<?php

namespace Arbory\Base\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class ImageModificationConfiguration
{
    /**
     * @param string $preset
     * @return array|null
     */
    public function getPreset(string $preset = ''): ?array
    {
        return config('arbory.glide.preset.' . $preset);
    }

    /**
     * @return Filesystem
     */
    public function getOutputDisk(): Filesystem
    {
        return Storage::disk(config('arbory.glide.output_disk_name'));
    }
}
