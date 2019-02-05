<?php

namespace Arbory\Base\Admin\Exports\Type;

use Arbory\Base\Admin\Exports\ExportInterface;
use Arbory\Base\Admin\Exports\DataSetExport;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JsonExport implements ExportInterface
{
    const EXTENSION = 'json';

    /**
     * @var DataSetExport
     */
    protected $export;

    /**
     * ExcelExport constructor.
     * @param DataSetExport $export
     */
    public function __construct(DataSetExport $export)
    {
        $this->export = $export;
    }

    /**
     * @param string $fileName
     * @return BinaryFileResponse
     */
    public function download(string $fileName): BinaryFileResponse
    {
        $fileName = vsprintf('%s-%s.%s', [
            $fileName,
            time(),
            self::EXTENSION
        ]);

        $tempPath = storage_path('app/cache/' . $fileName);

        File::put($tempPath, $this->export->getItems()->toJson(JSON_PRETTY_PRINT));

        return response()->download($tempPath)->deleteFileAfterSend();
    }
}