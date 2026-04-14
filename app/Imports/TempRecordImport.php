<?php

namespace App\Imports;

use App\Models\TempRecord;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class TempRecordImport
{
    protected $results = [
        'new_records' => [],
        'duplicate_records' => [],
        'total_parsed' => 0,
        'errors' => [],
        'detected_equipment_type' => null,
    ];

    protected $executeResults = [
        'imported' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    /**
     * Preview import - Phase 1: Parse file and detect duplicates
     */
    public function preview(string $filePath, ?string $equipmentType = null): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            
            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                $lowerName = strtolower($sheetName);
                if (str_contains($lowerName, 'master') || str_contains($lowerName, 'template')) {
                    continue;
                }
                
                $sheet = $spreadsheet->getSheetByName($sheetName);
                
                // Detect equipment type if not provided
                if (!$equipmentType && !$this->results['detected_equipment_type']) {
                    $this->results['detected_equipment_type'] = $this->detectEquipmentType($sheet);
                }
                
                $this->processSheetPreview($sheet, $sheetName, $equipmentType ?: $this->results['detected_equipment_type']);
            }

            return $this->results;

        } catch (\Exception $e) {
            Log::error('Temp Record Import Preview failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->results['errors'][] = 'Failed to process file: ' . $e->getMessage();
            return $this->results;
        }
    }

    /**
     * Execute import - Phase 2: Create/update records based on user choice
     */
    public function execute(
        string $filePath, 
        string $equipmentType,
        ?string $lineAssigned = null,
        ?string $processAssigned = null,
        bool $updateDuplicates = false
    ): array {
        try {
            $spreadsheet = IOFactory::load($filePath);
            
            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                $lowerName = strtolower($sheetName);
                if (str_contains($lowerName, 'master') || str_contains($lowerName, 'template')) {
                    continue;
                }
                
                $sheet = $spreadsheet->getSheetByName($sheetName);
                $this->processSheetExecute($sheet, $sheetName, $equipmentType, $lineAssigned, $processAssigned, $updateDuplicates);
            }

            return $this->executeResults;

        } catch (\Exception $e) {
            Log::error('Temp Record Import Execute failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->executeResults['errors'][] = 'Failed to process file: ' . $e->getMessage();
            return $this->executeResults;
        }
    }

    /**
     * Detect equipment type from cell styling
     */
    protected function detectEquipmentType($sheet): ?string
    {
        // Check cells E2 (Soldering Iron) and J2 (Soldering Pot)
        $e2Style = $sheet->getStyle('E2');
        $j2Style = $sheet->getStyle('J2');
        
        $e2Fill = $e2Style->getFill();
        $j2Fill = $j2Style->getFill();
        
        $e2BgColor = $e2Fill->getStartColor()->getRGB();
        $j2BgColor = $j2Fill->getStartColor()->getRGB();
        
        // Check for dark background (black or near-black)
        $e2IsDark = $this->isColorDark($e2BgColor);
        $j2IsDark = $this->isColorDark($j2BgColor);
        
        // Also check font color (white text on dark background)
        $e2FontColor = $e2Style->getFont()->getColor()->getRGB();
        $j2FontColor = $j2Style->getFont()->getColor()->getRGB();
        
        $e2HasWhiteText = $this->isColorLight($e2FontColor);
        $j2HasWhiteText = $this->isColorLight($j2FontColor);
        
        // Determine which is selected
        if ($e2IsDark || $e2HasWhiteText) {
            return 'Soldering Iron';
        }
        
        if ($j2IsDark || $j2HasWhiteText) {
            return 'Soldering Pot';
        }
        
        // Check for checkbox markers or X
        $e2Value = strtolower(trim((string)$sheet->getCell('E2')->getValue()));
        $j2Value = strtolower(trim((string)$sheet->getCell('J2')->getValue()));
        
        // Look for markers in adjacent cells
        $d2Value = strtolower(trim((string)$sheet->getCell('D2')->getValue()));
        $i2Value = strtolower(trim((string)$sheet->getCell('I2')->getValue()));
        
        if (str_contains($d2Value, 'x') || str_contains($d2Value, '✓') || str_contains($d2Value, '√')) {
            return 'Soldering Iron';
        }
        
        if (str_contains($i2Value, 'x') || str_contains($i2Value, '✓') || str_contains($i2Value, '√')) {
            return 'Soldering Pot';
        }
        
        // Default to null if can't detect
        return null;
    }

    /**
     * Check if a hex color is dark
     */
    protected function isColorDark(string $hexColor): bool
    {
        if (empty($hexColor) || $hexColor === 'FFFFFF' || $hexColor === '000000') {
            // 000000 is black which is dark
            return $hexColor === '000000';
        }
        
        // Convert hex to RGB and calculate luminance
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
        
        return $luminance < 0.5;
    }

    /**
     * Check if a hex color is light (for detecting white text)
     */
    protected function isColorLight(string $hexColor): bool
    {
        if (empty($hexColor) || $hexColor === '000000') {
            return false;
        }
        
        if ($hexColor === 'FFFFFF') {
            return true;
        }
        
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
        
        return $luminance > 0.8;
    }

    /**
     * Process sheet for preview
     */
    protected function processSheetPreview($sheet, string $sheetName, ?string $equipmentType): void
    {
        $headerData = $this->parseHeaderData($sheet);
        $dateColumns = $this->parseDateColumns($sheet);
        $footerData = $this->parseFooterData($sheet, $dateColumns);

        if (empty($dateColumns)) {
            $this->results['errors'][] = "Sheet '{$sheetName}': No date columns found";
            return;
        }

        // For each date column, create a record
        foreach ($dateColumns as $dateInfo) {
            $record = $this->buildRecord($sheet, $dateInfo, $headerData, $footerData, $equipmentType);
            
            if ($record && $this->hasValidData($record)) {
                $this->results['total_parsed']++;
                
                // Check for duplicate
                $existing = $this->findExistingRecord($record);
                
                if ($existing) {
                    $this->results['duplicate_records'][] = [
                        'existing_id' => $existing->id,
                        'existing_data' => $existing->toArray(),
                        'new_data' => $record,
                    ];
                } else {
                    $this->results['new_records'][] = $record;
                }
            }
        }
    }

    /**
     * Process sheet for execute
     */
    protected function processSheetExecute(
        $sheet, 
        string $sheetName, 
        string $equipmentType,
        ?string $lineAssigned,
        ?string $processAssigned,
        bool $updateDuplicates
    ): void {
        $headerData = $this->parseHeaderData($sheet);
        $dateColumns = $this->parseDateColumns($sheet);
        $footerData = $this->parseFooterData($sheet, $dateColumns);

        if (empty($dateColumns)) {
            $this->executeResults['errors'][] = "Sheet '{$sheetName}': No date columns found";
            return;
        }

        foreach ($dateColumns as $dateInfo) {
            $record = $this->buildRecord($sheet, $dateInfo, $headerData, $footerData, $equipmentType, $lineAssigned, $processAssigned);
            
            if ($record && $this->hasValidData($record)) {
                try {
                    $existing = $this->findExistingRecord($record);
                    
                    if ($existing) {
                        if ($updateDuplicates) {
                            $existing->update($record);
                            $this->executeResults['updated']++;
                        } else {
                            $this->executeResults['skipped']++;
                        }
                    } else {
                        TempRecord::create($record);
                        $this->executeResults['imported']++;
                    }
                } catch (\Exception $e) {
                    $this->executeResults['errors'][] = "Date {$record['date']}: " . $e->getMessage();
                }
            }
        }
    }

    /**
     * Parse header data from rows 1-5
     */
    protected function parseHeaderData($sheet): array
    {
        // Equipment No. from D4
        $equipmentNo = $this->getCellValue($sheet, 'D4');
        
        // Machine Setting Standard from Q4
        $machineSettingStandard = $this->getCellValue($sheet, 'Q4');
        
        return [
            'equipment_no' => $equipmentNo,
            'machine_setting_standard' => $machineSettingStandard,
        ];
    }

    /**
     * Parse date columns from row 6
     */
    protected function parseDateColumns($sheet): array
    {
        $dateColumns = [];
        $highestCol = $sheet->getHighestColumn();
        $highestColIndex = Coordinate::columnIndexFromString($highestCol);

        // Check columns B onwards (column index 2+)
        for ($colIndex = 2; $colIndex <= $highestColIndex; $colIndex += 2) {
            $col = Coordinate::stringFromColumnIndex($colIndex);
            $value = $sheet->getCell($col . '6')->getValue();

            // Check if it's an Excel serial date (numeric value)
            if ($value !== null && is_numeric($value) && $value > 40000 && $value < 60000) {
                $date = $this->excelDateToString($value);
                
                if ($date) {
                    $pmColIndex = $colIndex + 1;
                    $pmCol = Coordinate::stringFromColumnIndex($pmColIndex);
                    
                    $dateColumns[] = [
                        'date' => $date,
                        'am_col' => $col,
                        'pm_col' => $pmCol,
                    ];
                }
            }
        }

        return $dateColumns;
    }

    /**
     * Parse footer data (MODEL CODE, P.I.C., Checked by) from rows 80-82
     */
    protected function parseFooterData($sheet, array $dateColumns): array
    {
        $footerData = [];
        
        foreach ($dateColumns as $dateInfo) {
            $amCol = $dateInfo['am_col'];
            
            // MODEL CODE from row 80
            $modelCode = $this->getCellValue($sheet, $amCol . '80');
            
            // P.I.C. from row 81
            $pic = $this->getCellValue($sheet, $amCol . '81');
            
            // Checked by from row 82
            $checkedBy = $this->getCellValue($sheet, $amCol . '82');
            
            $footerData[$amCol] = [
                'model_series' => $modelCode,
                'person_in_charge' => $pic,
                'checked_by' => $checkedBy,
            ];
        }
        
        return $footerData;
    }

    /**
     * Build a record from parsed data
     */
    protected function buildRecord(
        $sheet, 
        array $dateInfo, 
        array $headerData, 
        array $footerData, 
        ?string $equipmentType,
        ?string $lineAssigned = null,
        ?string $processAssigned = null
    ): ?array {
        $amCol = $dateInfo['am_col'];
        $pmCol = $dateInfo['pm_col'];
        
        // Time from row 8
        $timeAm = $this->parseTimeValue($this->getCellValue($sheet, $amCol . '8'));
        $timePm = $this->parseTimeValue($this->getCellValue($sheet, $pmCol . '8'));
        
        // Temperature from row 9
        $tempAm = $this->getCellValue($sheet, $amCol . '9');
        $tempPm = $this->getCellValue($sheet, $pmCol . '9');
        
        // Footer data for this date column
        $footer = $footerData[$amCol] ?? [];
        
        return [
            'date' => $dateInfo['date'],
            'model_series' => $footer['model_series'] ?? null,
            'solder_model' => $headerData['equipment_no'],
            'line_assigned' => $lineAssigned,
            'control_no' => $headerData['equipment_no'],
            'equipment_type' => $equipmentType,
            'machine_setting_standard' => $headerData['machine_setting_standard'],
            'process_assigned' => $processAssigned,
            'person_in_charge' => $footer['person_in_charge'] ?? null,
            'time_am' => $timeAm,
            'temp_am' => $tempAm,
            'time_pm' => $timePm,
            'temp_pm' => $tempPm,
            'col_remarks' => null,
            'checked_by' => $footer['checked_by'] ?? null,
        ];
    }

    /**
     * Check if record has valid data (at least one temperature value)
     */
    protected function hasValidData(array $record): bool
    {
        return !empty($record['temp_am']) || !empty($record['temp_pm']);
    }

    /**
     * Find existing record by duplicate key
     */
    protected function findExistingRecord(array $record): ?TempRecord
    {
        return TempRecord::where('date', $record['date'])
            ->where('equipment_type', $record['equipment_type'])
            ->where('control_no', $record['control_no'])
            ->first();
    }

    /**
     * Get cell value as string
     */
    protected function getCellValue($sheet, string $cell): ?string
    {
        $value = $sheet->getCell($cell)->getValue();
        
        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }

    /**
     * Convert Excel serial date to Y-m-d string
     */
    protected function excelDateToString($excelDate): ?string
    {
        if (!is_numeric($excelDate)) {
            return null;
        }
        
        // Excel date serial number to Unix timestamp
        // Excel's epoch is December 30, 1899
        $unixDate = ($excelDate - 25569) * 86400;
        
        return date('Y-m-d', $unixDate);
    }

    /**
     * Parse time value (Excel decimal or string format)
     */
    protected function parseTimeValue($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        
        // Handle Excel numeric time (0.0 - 1.0)
        if (is_numeric($value) && $value >= 0 && $value < 1) {
            $totalMinutes = round((float) $value * 24 * 60);
            $hours = intdiv($totalMinutes, 60);
            $minutes = $totalMinutes % 60;
            return sprintf('%02d:%02d', $hours, $minutes);
        }
        
        // Handle string format like "13;00" or "13:00"
        $value = str_replace(';', ':', $value);
        
        if (preg_match('/(\d{1,2}):(\d{2})/', $value, $matches)) {
            $hour = (int) $matches[1];
            $minute = $matches[2];
            return sprintf('%02d:%s', $hour, $minute);
        }
        
        return null;
    }

    /**
     * Get preview results
     */
    public function getPreviewResults(): array
    {
        return $this->results;
    }

    /**
     * Get execute results
     */
    public function getExecuteResults(): array
    {
        return $this->executeResults;
    }
}
