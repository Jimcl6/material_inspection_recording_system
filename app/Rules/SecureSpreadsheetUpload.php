<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class SecureSpreadsheetUpload implements Rule
{
    private const MIME_TYPES = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'application/zip',
        'application/x-zip-compressed',
        'application/x-ole-storage',
        'application/CDFV2',
    ];

    public function __construct(private bool $allowCsv = false)
    {
    }

    public function passes($attribute, $value): bool
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            return false;
        }

        $extension = strtolower($value->getClientOriginalExtension());
        $extensions = $this->allowCsv ? ['xlsx', 'xls', 'csv'] : ['xlsx', 'xls'];
        if (! in_array($extension, $extensions, true)) {
            return false;
        }

        $mimeTypes = self::MIME_TYPES;
        if ($this->allowCsv) {
            $mimeTypes = array_merge($mimeTypes, ['text/csv', 'text/plain']);
        }

        if (! in_array($value->getMimeType(), $mimeTypes, true)) {
            return false;
        }

        try {
            $path = $value->getRealPath();
            if ($path === false || ! is_file($path)) {
                return false;
            }

            $type = IOFactory::identify($path);
            $types = $this->allowCsv ? ['Xlsx', 'Xls', 'Csv'] : ['Xlsx', 'Xls'];
            if (! in_array($type, $types, true)) {
                return false;
            }

            $reader = IOFactory::createReader($type);
            $reader->setReadDataOnly(true);
            if (! is_callable([$reader, 'listWorksheetInfo'])) {
                return false;
            }

            $worksheets = call_user_func([$reader, 'listWorksheetInfo'], $path);
            if (! is_array($worksheets)) {
                return false;
            }

            return count($worksheets) > 0
                && collect($worksheets)->contains(fn (array $sheet) => ($sheet['totalRows'] ?? 0) > 0 && ($sheet['totalColumns'] ?? 0) > 0
                );
        } catch (Throwable) {
            return false;
        }
    }

    public function message(): string
    {
        return 'The file must be a valid Excel spreadsheet.';
    }
}
