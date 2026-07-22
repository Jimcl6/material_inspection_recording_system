<?php

namespace Tests\Feature;

use App\Exports\AnnealingChecksExport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class SpreadsheetExportRegressionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_annealing_export_remains_a_readable_xlsx_workbook(): void
    {
        $bytes = Excel::raw(new AnnealingChecksExport, ExcelFormat::XLSX);
        $path = tempnam(sys_get_temp_dir(), 'mirs-export-test-');

        try {
            file_put_contents($path, $bytes);
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();

            $this->assertSame('Item Code', $sheet->getCell('A1')->getValue());
            $this->assertSame('Temperature Setting', $sheet->getCell('G1')->getValue());
            $this->assertSame('Updated At', $sheet->getCell('R1')->getValue());

            $spreadsheet->disconnectWorksheets();
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
