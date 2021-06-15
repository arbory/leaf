<?php

namespace Arbory\Base\Files;

use Arbory\Base\Services\ImageModificationConfiguration;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Glide\GlideImage;

/**
 * Class ArboryImage.
 */
class ArboryImage extends ArboryFile
{
    /**
     * @return string
     */
    public function getTable()
    {
        return (new parent)->getTable();
    }

    /**
     * @param string $preset
     * @return string
     */
    public function getUrl(string $preset = ''): string
    {
        /** @var ImageModificationConfiguration $configuration */
        $configuration = app(ImageModificationConfiguration::class);

        $pathToFile = Storage::disk($this->getDisk())->path($this->getLocalName());
        $image = GlideImage::create($pathToFile);

        $presetConfiguration = $configuration->getPreset($preset);

        if ($presetConfiguration) {
            $image->modify($presetConfiguration);
        }

        $imagePath = $image->save($configuration->getOutputDisk()->path($this->getLocalName()));

        return $configuration->getOutputDisk()->url(File::basename($imagePath));
    }
}
