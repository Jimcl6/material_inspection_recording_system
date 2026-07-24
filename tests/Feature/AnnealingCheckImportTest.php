<?php

namespace Tests\Feature;

use App\Imports\AnnealingChecksWithHeadersImport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use ReflectionClass;
use Tests\TestCase;

class AnnealingCheckImportTest extends TestCase
{
    public function test_import_preserves_temperature_setting_without_machine_setting(): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A8', 'ITEM CODE');
        $sheet->setCellValue('B8', 'RECEIVING DATE');
        $sheet->setCellValue('C8', 'SUPPLIER LOT');
        $sheet->setCellValue('D8', 'QUANTITY');
        $sheet->setCellValue('E8', 'ANNEALING DATE');
        $sheet->setCellValue('F8', 'MACHINE #');
        $sheet->setCellValue('G8', 'MACHINE SETTING');
        $sheet->setCellValue('G9', 'TEMPERATURE');
        $sheet->setCellValue('H9', 'ANNEALING TIME');
        $sheet->setCellValue('I9', 'DAMPER');
        $sheet->setCellValue('J9', 'IN');
        $sheet->setCellValue('K9', 'OUT');

        $sheet->setCellValue('A10', 'TEST-001');
        $sheet->setCellValue('B10', '2026-06-20');
        $sheet->setCellValue('C10', 'LOT-001');
        $sheet->setCellValue('D10', 10);
        $sheet->setCellValue('E10', '2026-06-21');
        $sheet->setCellValue('F10', 'AN-01');
        $sheet->setCellValue('G10', '850°C');
        $sheet->setCellValue('H10', '3 HRS');
        $sheet->setCellValue('I10', '50%');
        $sheet->setCellValue('J10', '08:00');
        $sheet->setCellValue('K10', '11:00');

        $import = new AnnealingChecksWithHeadersImport;
        $reflection = new ReflectionClass($import);

        $currentUser = $reflection->getProperty('currentUser');
        $currentUser->setAccessible(true);
        $currentUser->setValue($import, (object) ['id' => 1]);

        $detectColumnMapping = $reflection->getMethod('detectColumnMapping');
        $detectColumnMapping->setAccessible(true);
        $columnMap = $detectColumnMapping->invoke($import, $sheet);

        $extractRowData = $reflection->getMethod('extractRowData');
        $extractRowData->setAccessible(true);
        $data = $extractRowData->invoke($import, $sheet, 10, $columnMap, 'Annealing');

        $this->assertSame(850.0, $data['temperature_setting']);
        $this->assertArrayNotHasKey('machine_setting', $data);
    }
}
