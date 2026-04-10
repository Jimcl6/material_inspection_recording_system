<?php

namespace App\Imports;

use App\Models\DiaphragmWeldingChecksheet;
use App\Models\DiaphragmWeldingSample;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DiaphragmWeldingImport
{
    protected $results = [
        'imported' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    /**
     * Import the Excel file
     */
    public function import(string $filePath)
    {
        $spreadsheet = IOFactory::load($filePath);
        
        // Process all sheets except Master/template sheets
        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $lowerName = strtolower($sheetName);
            if (str_contains($lowerName, 'master') || str_contains($lowerName, 'template') || str_contains($lowerName, 'item code')) {
                continue;
            }
            
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $this->processSheet($sheet, $sheetName);
        }

        return $this;
    }

    /**
     * Process a single sheet
     */
    protected function processSheet($sheet, $sheetName)
    {
        $highestRow = $sheet->getHighestRow();
        $currentRow = 10; // Data starts at row 10

        while ($currentRow <= $highestRow) {
            // Check if this row starts a new record (has date in column A)
            $dateValue = $sheet->getCell('A' . $currentRow)->getValue();
            
            if (empty($dateValue)) {
                $currentRow++;
                continue;
            }

            try {
                $record = $this->parseRecord($sheet, $currentRow);
                
                if ($record) {
                    $this->createChecksheet($record);
                    $this->results['imported']++;
                }
                
                // Each record spans 10 rows
                $currentRow += 10;
                
            } catch (\Exception $e) {
                $this->results['errors'][] = "Row {$currentRow}: " . $e->getMessage();
                Log::error("Import error at row {$currentRow}", ['error' => $e->getMessage()]);
                $currentRow += 10;
            }
        }
    }

    /**
     * Parse a record from the sheet (spans 10 rows)
     */
    protected function parseRecord($sheet, $startRow)
    {
        // Parse date
        $dateValue = $sheet->getCell('A' . $startRow)->getValue();
        $productionDate = $this->parseDate($dateValue);
        
        if (!$productionDate) {
            return null;
        }

        // Material Monitoring data (Row 1 of record)
        $record = [
            'production_date' => $productionDate,
            'lasermark_lot_number' => $this->getCellValue($sheet, 'I' . $startRow),
            'machine_no' => $this->getCellValue($sheet, 'J' . $startRow),
            'letter_code' => $this->getCellValue($sheet, 'L' . $startRow),
            'df_rubber_lot' => $this->getCellValue($sheet, 'M' . $startRow),
            'center_plate_a_lot' => $this->getCellValue($sheet, 'N' . $startRow),
            'center_plate_b_lot' => $this->getCellValue($sheet, 'O' . $startRow),
            'prod_qty' => $this->getNumericValue($sheet, 'P' . $startRow),
            'jo_number' => $this->getCellValue($sheet, 'Q' . $startRow),
            'temperature' => null, // Will be parsed from column AA
            'operator_name' => $this->getCellValue($sheet, 'AB' . $startRow),
            'technician_name' => $this->getCellValue($sheet, 'AC' . $startRow),
            'checked_by_name' => $this->getCellValue($sheet, 'AD' . $startRow),
            'remarks' => $this->getCellValue($sheet, 'AE' . $startRow),
        ];

        // Parse samples (10 check items, each with 5 samples in columns V-Z)
        $samples = [];
        
        // Row offsets for each check item relative to startRow
        $checkItemRows = [
            'collapse_depth' => 0,
            'collapse_time' => 1,
            'strength' => 2,
            'appearance' => 3,
            'welding_condition' => 4,
            'measurement_1' => 5,
            'measurement_2' => 6,
            'measurement_3' => 7,
            'measurement_4' => 8,
            'measurement_5' => 9,
        ];

        foreach ($checkItemRows as $checkItem => $rowOffset) {
            $row = $startRow + $rowOffset;
            $samples[] = [
                'check_item' => $checkItem,
                'sample_1' => $this->getCellValue($sheet, 'V' . $row),
                'sample_2' => $this->getCellValue($sheet, 'W' . $row),
                'sample_3' => $this->getCellValue($sheet, 'X' . $row),
                'sample_4' => $this->getCellValue($sheet, 'Y' . $row),
                'sample_5' => $this->getCellValue($sheet, 'Z' . $row),
            ];
        }

        $record['samples'] = $samples;

        return $record;
    }

    /**
     * Create a checksheet from parsed record
     */
    protected function createChecksheet(array $record)
    {
        $samples = $record['samples'];
        unset($record['samples']);
        
        // Remove name fields that need to be converted
        $operatorName = $record['operator_name'] ?? null;
        $technicianName = $record['technician_name'] ?? null;
        $checkedByName = $record['checked_by_name'] ?? null;
        
        unset($record['operator_name'], $record['technician_name'], $record['checked_by_name']);

        $record['status'] = 'pending';
        $record['submitted_at'] = now();

        $checksheet = DiaphragmWeldingChecksheet::create($record);

        // Create samples
        foreach ($samples as $sample) {
            $checksheet->samples()->create($sample);
        }

        return $checksheet;
    }

    /**
     * Parse date value from Excel
     */
    protected function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        // If it's a numeric Excel date
        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // If it's already a DateTime object
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }

        // Try to parse string date
        try {
            return (new \DateTime($value))->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get cell value as string
     */
    protected function getCellValue($sheet, $cell)
    {
        $value = $sheet->getCell($cell)->getValue();
        
        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }

    /**
     * Get numeric value from cell
     */
    protected function getNumericValue($sheet, $cell)
    {
        $value = $sheet->getCell($cell)->getValue();
        
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    /**
     * Get import results
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
