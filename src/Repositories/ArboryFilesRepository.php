<?php

namespace Arbory\Base\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Arbory\Base\Files\ArboryFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ArboryFilesRepository.
 */
class ArboryFilesRepository extends AbstractModelsRepository
{
    /**
     * @var string
     */
    protected $modelClass = ArboryFile::class;

    /**
     * @var string
     */
    protected $diskName;

    /**
     * @var string
     */
    protected $disk;

    /**
     * ArboryFilesRepository constructor.
     * @param string $diskName
     * @param string $modelClass
     */
    public function __construct($diskName, $modelClass = ArboryFile::class)
    {
        $this->diskName = $diskName;
        $this->modelClass = $modelClass;

        parent::__construct();
    }

    /**
     * @return \Illuminate\Filesystem\FilesystemAdapter|string
     */
    public function getDisk()
    {
        if (! $this->disk) {
            $this->disk = Storage::disk($this->diskName);
        }

        return $this->disk;
    }

    /**
     * @param UploadedFile $file
     * @param Model $owner
     * @return ArboryFile|null
     * @throws RuntimeException
     */
    public function createFromUploadedFile(UploadedFile $file, Model $owner)
    {
        if (! $file->getRealPath()) {
            throw new RuntimeException('Uploaded file does not have real path');
        }

        if (! $file->getSize()) {
            throw new RuntimeException(sprintf(
                'The uploaded file size must be between 1 and %d (see "upload_max_filesize" in "php.ini") bytes',
                UploadedFile::getMaxFilesize()
            ));
        }

        $localFileName = $this->getLocalFilenameForUploadedFile($file);

        if (! $this->getDisk()->put($localFileName, file_get_contents($file->getRealPath()))) {
            throw new RuntimeException('Could not store local file "'.$localFileName.'"');
        }

        $modelClass = $this->modelClass;
        $arboryFile = new $modelClass(
            $this->getCreateAttributesForCreatedFile($file, $localFileName, $owner),
            $localFileName
        );

        /* @var $arboryFile ArboryFile */
        if (! $arboryFile->save()) {
            throw new RuntimeException('Could not save "'.$modelClass.'" to database');
        }

        return $arboryFile;
    }

    /**
     * @param $fileName
     * @param $fileContents
     * @param Model $owner
     * @return ArboryFile|null
     * @throws RuntimeException
     */
    public function createFromBlob($fileName, $fileContents, Model $owner)
    {
        $localFileName = $this->getLocalFilenameForBlob($fileContents);

        if (! $this->getDisk()->put($localFileName, $fileContents)) {
            throw new RuntimeException('Could not store local file "'.$localFileName.'"');
        }

        $modelClass = $this->modelClass;
        $arboryFile = new $modelClass(
            $this->getCreateAttributesForBlob($fileName, $fileContents, $localFileName, $owner)
        );

        /* @var $arboryFile ArboryFile */
        if (! $arboryFile->save()) {
            throw new RuntimeException('Could not save "'.$modelClass.'" to database');
        }

        return $arboryFile;
    }

    /**
     * @param string $fileName
     * @return string
     */
    protected function getFreeFileName($fileName)
    {
        $uploadsDisk = $this->getDisk();

        while ($uploadsDisk->exists($fileName)) {
            $fileNameParts = pathinfo($fileName);
            $fileName = $fileNameParts['filename'].'-'.Str::random(10);

            if (($extension = Arr::get($fileNameParts, 'extension', false))) {
                $fileName .= '.'.$extension;
            }
        }

        return $fileName;
    }

    /**
     * @param UploadedFile $file
     * @param string $localFileName
     * @return array
     */
    protected function getCreateAttributesForCreatedFile(UploadedFile $file, $localFileName, Model $owner)
    {
        $realPath = $file->getRealPath();

        return [
            'disk' => $this->diskName,
            'sha1' => sha1_file($realPath),
            'original_name' => $file->getClientOriginalName(),
            'local_name' => $localFileName,
            'size' => $file->getSize(),
            'owner_id' => $owner->getKey(),
            'owner_type' => $owner->getMorphClass(),
        ];
    }

    /**
     * @param string $originalFileName
     * @param string $fileContents
     * @param string $localFileName
     * @return array
     */
    protected function getCreateAttributesForBlob($originalFileName, $fileContents, $localFileName, Model $owner)
    {
        return [
            'disk' => $this->diskName,
            'sha1' => sha1($fileContents),
            'original_name' => $originalFileName,
            'local_name' => $localFileName,
            'size' => strlen($fileContents),
            'owner_id' => $owner->getKey(),
            'owner_type' => $owner->getMorphClass(),
        ];
    }

    /**
     * @param $fileContents
     * @return string
     */
    protected function getLocalFilenameForBlob($fileContents): string
    {
        return $this->getFreeFileName(sha1($fileContents));
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    protected function getLocalFilenameForUploadedFile(UploadedFile $file): string
    {
        return $this->getFreeFileName(sha1_file($file->getRealPath()).'.'.$file->getClientOriginalExtension());
    }

    /**
     * @param string $arboryFileId
     * @return int
     */
    public function delete($arboryFileId)
    {
        $arboryFile = $this->find($arboryFileId);

        $this->getDisk()->delete($arboryFile->getLocalName());

        return parent::delete($arboryFileId);
    }
}
