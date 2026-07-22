<?php

namespace Tests\Feature;

use App\Support\SpreadsheetImportSecurity;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Mockery;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

class SpreadsheetImportSecurityTest extends TestCase
{
    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_valid_excel_spreadsheet_passes_the_import_gate(): void
    {
        $validator = Validator::make(
            ['file' => $this->workbookUpload()],
            ['file' => SpreadsheetImportSecurity::rules()]
        );

        $this->assertTrue($validator->passes(), $validator->errors()->first('file'));
    }

    public function test_upload_with_invalid_mime_type_is_rejected(): void
    {
        $upload = $this->textUpload('not-a-spreadsheet.xlsx', 'confidential-test-marker');

        $this->assertImportGateRejects($upload);
    }

    public function test_upload_with_invalid_extension_is_rejected(): void
    {
        $this->assertImportGateRejects($this->workbookUpload('spreadsheet.txt'));
    }

    public function test_oversized_upload_is_rejected(): void
    {
        $upload = UploadedFile::fake()->create(
            'oversized.xlsx',
            SpreadsheetImportSecurity::MAX_FILE_SIZE_KILOBYTES + 1,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $this->assertImportGateRejects($upload);
    }

    public function test_malformed_spreadsheet_is_rejected(): void
    {
        $this->assertImportGateRejects($this->malformedZipUpload());
    }

    public function test_browser_validation_response_does_not_expose_file_contents_or_a_stack_trace(): void
    {
        $response = $this->withoutMiddleware()->postJson('/temp-records-import/preview', [
            'file' => $this->textUpload('malformed.xlsx', 'confidential-test-marker'),
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('file');

        $body = $response->getContent();
        $this->assertStringNotContainsString('confidential-test-marker', $body);
        $this->assertStringNotContainsString('trace', strtolower($body));
        $this->assertStringNotContainsString('exception', strtolower($body));
    }

    public function test_browser_import_failure_uses_safe_correlation_message(): void
    {
        $relativePath = 'temp/imports/temperature/'.Str::uuid().'.xlsx';
        Storage::disk('local')->put($relativePath, 'confidential-test-marker');
        session(['temp_record_import_file' => $relativePath]);

        try {
            $response = $this->withoutMiddleware()->postJson('/temp-records-import/execute', [
                'equipment_type' => 'Soldering Iron',
            ]);

            $response->assertStatus(500)->assertJson(['success' => false]);

            $body = $response->getContent();
            $this->assertStringContainsString('Reference:', $body);
            $this->assertStringNotContainsString('confidential-test-marker', $body);
            $this->assertStringNotContainsString('PhpSpreadsheet', $body);
            $this->assertStringNotContainsString('trace', strtolower($body));
        } finally {
            SpreadsheetImportSecurity::delete($relativePath);
        }
    }

    public function test_failure_logging_contains_safe_diagnostics_and_correlation_id_only(): void
    {
        Log::spy();
        $exception = new RuntimeException('confidential-test-marker');

        $correlationId = SpreadsheetImportSecurity::reportFailure('test.import', $exception);

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $correlationId
        );

        Log::shouldHaveReceived('error')->once()->with(
            'Spreadsheet import operation failed.',
            Mockery::on(function (array $context): bool {
                $encoded = json_encode($context);

                return isset(
                    $context['correlation_id'],
                    $context['operation'],
                    $context['exception_class'],
                    $context['exception_source'],
                    $context['exception_line'],
                    $context['exception_fingerprint']
                )
                    && ! array_key_exists('message', $context)
                    && ! array_key_exists('trace', $context)
                    && ! str_contains($encoded, 'confidential-test-marker');
            })
        );

        $browserMessage = SpreadsheetImportSecurity::browserError($correlationId);
        $this->assertStringContainsString($correlationId, $browserMessage);
        $this->assertStringNotContainsString('confidential-test-marker', $browserMessage);
    }

    public function test_temporary_paths_and_original_filenames_are_constrained(): void
    {
        $upload = $this->workbookUpload('../confidential<script>.xlsx');

        [$relativePath, $fullPath] = SpreadsheetImportSecurity::store($upload, 'test-scope');

        try {
            $this->assertStringStartsWith('temp/imports/test-scope/', $relativePath);
            $this->assertSame($fullPath, SpreadsheetImportSecurity::resolve($relativePath));
            $this->assertNull(SpreadsheetImportSecurity::resolve('../'.$relativePath));
            $this->assertSame('confidential_script_.xlsx', SpreadsheetImportSecurity::safeOriginalName($upload));
        } finally {
            SpreadsheetImportSecurity::delete($relativePath);
        }

        $this->assertNull(SpreadsheetImportSecurity::resolve($relativePath));
    }

    private function assertImportGateRejects(UploadedFile $upload): void
    {
        $validator = Validator::make(
            ['file' => $upload],
            ['file' => SpreadsheetImportSecurity::rules()]
        );

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('file'));
    }

    private function workbookUpload(string $clientName = 'valid.xlsx'): UploadedFile
    {
        $path = $this->temporaryPath();
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->setCellValue('A1', 'Test heading');
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return new UploadedFile(
            $path,
            $clientName,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    private function malformedZipUpload(): UploadedFile
    {
        $path = $this->temporaryPath();
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('not-a-workbook.txt', 'confidential-test-marker');
        $zip->close();

        return new UploadedFile($path, 'malformed.xlsx', 'application/zip', null, true);
    }

    private function textUpload(string $clientName, string $contents): UploadedFile
    {
        $path = $this->temporaryPath();
        file_put_contents($path, $contents);

        return new UploadedFile($path, $clientName, 'text/plain', null, true);
    }

    private function temporaryPath(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'mirs-import-test-');
        $this->temporaryFiles[] = $path;

        return $path;
    }
}
