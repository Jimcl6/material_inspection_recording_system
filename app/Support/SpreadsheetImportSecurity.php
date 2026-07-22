<?php

namespace App\Support;

use App\Rules\SecureSpreadsheetUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SpreadsheetImportSecurity
{
    public const MAX_FILE_SIZE_KILOBYTES = 10240;

    public static function rules(bool $allowCsv = false): array
    {
        return [
            'required',
            'file',
            'max:'.self::MAX_FILE_SIZE_KILOBYTES,
            new SecureSpreadsheetUpload($allowCsv),
        ];
    }

    public static function store(UploadedFile $file, string $scope): array
    {
        $scope = preg_replace('/[^a-z0-9_-]/', '-', strtolower($scope));
        $extension = strtolower($file->getClientOriginalExtension());
        $relativePath = $file->storeAs(
            'temp/imports/'.$scope,
            Str::uuid().'.'.$extension,
            'local'
        );

        if (! is_string($relativePath)) {
            throw new RuntimeException('Unable to store the spreadsheet for processing.');
        }

        $fullPath = self::resolve($relativePath);
        if ($fullPath === null) {
            Storage::disk('local')->delete($relativePath);
            throw new RuntimeException('The stored spreadsheet path is invalid.');
        }

        return [$relativePath, $fullPath];
    }

    public static function resolve(?string $relativePath): ?string
    {
        if (! is_string($relativePath)
            || ! preg_match('#^temp/imports/[a-z0-9_-]+/[0-9a-f-]+\.(xlsx|xls|csv)$#i', $relativePath)) {
            return null;
        }

        $root = realpath(Storage::disk('local')->path('temp/imports'));
        $fullPath = realpath(Storage::disk('local')->path($relativePath));
        if ($root === false || $fullPath === false || ! is_file($fullPath)) {
            return null;
        }

        $rootPrefix = rtrim($root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        return str_starts_with($fullPath, $rootPrefix) ? $fullPath : null;
    }

    public static function delete(?string $relativePath): void
    {
        if (is_string($relativePath)
            && preg_match('#^temp/imports/[a-z0-9_-]+/[0-9a-f-]+\.(xlsx|xls|csv)$#i', $relativePath)) {
            Storage::disk('local')->delete($relativePath);
        }
    }

    public static function safeOriginalName(UploadedFile $file): string
    {
        $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $name = preg_replace('/[^A-Za-z0-9._ -]/', '_', $name) ?: 'spreadsheet';

        return Str::limit($name, 150, '');
    }

    public static function reportFailure(string $operation, Throwable $exception): string
    {
        $correlationId = (string) Str::uuid();

        Log::error('Spreadsheet import operation failed.', [
            'correlation_id' => $correlationId,
            'operation' => $operation,
            'exception_class' => $exception::class,
            'exception_code' => (string) $exception->getCode(),
            'exception_source' => basename($exception->getFile()),
            'exception_line' => $exception->getLine(),
            'exception_fingerprint' => hash('sha256', implode('|', [
                $exception::class,
                (string) $exception->getCode(),
                basename($exception->getFile()),
                (string) $exception->getLine(),
            ])),
        ]);

        return $correlationId;
    }

    public static function browserError(string $correlationId): string
    {
        return "The spreadsheet could not be processed. Reference: {$correlationId}.";
    }

    public static function safeFailure(string $operation, Throwable $exception, string $message): string
    {
        $correlationId = self::reportFailure($operation, $exception);

        return "{$message} Reference: {$correlationId}.";
    }
}
