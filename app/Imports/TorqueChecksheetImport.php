<?php

namespace App\Imports;

use App\Models\TorqueRecord;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class TorqueChecksheetImport
{
    protected $results = [
        'new_records' => [],
        'duplicate_records' => [],
        'total_parsed' => 0,
        'errors' => [],
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
    public function preview(string $filePath): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            
            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                $lowerName = strtolower($sheetName);
                if (str_contains($lowerName, 'master') || str_contains($lowerName, 'template')) {
                    continue;
                }
                
                $sheet = $spreadsheet->getSheetByName($sheetName);
                $this->processSheetPreview($sheet, $sheetName);
            }

            return $this->results;

        } catch (\Exception $e) {
            Log::error('Torque Checksheet Import Preview failed', [
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
    public function execute(string $filePath, bool $updateDuplicates = false): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            
            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                $lowerName = strtolower($sheetName);
                if (str_contains($lowerName, 'master') || str_contains($lowerName, 'template')) {
                    continue;
                }
                
                $sheet = $spreadsheet->getSheetByName($sheetName);
                $this->processSheetExecute($sheet, $sheetName, $updateDuplicates);
            }

            return $this->executeResults;

        } catch (\Exception $e) {
            Log::error('Torque Checksheet Import Execute failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->executeResults['errors'][] = 'Failed to process file: ' . $e->getMessage();
            return $this->executeResults;
        }
    }

    /**
     * Process sheet for preview
     */
    protected function processSheetPreview($sheet, string $sheetName): void
    {
        $headerData = $this->parseHeaderData($sheet);
        $dateColumns = $this->parseDateColumns($sheet);
        $timeData = $this->parseTimeData($sheet, $dateColumns);

        if (empty($dateColumns)) {
            $this->results['errors'][] = "Sheet '{$sheetName}': No date columns found";
            return;
        }

        $highestRow = $sheet->getHighestRow();
        $currentRow = 10;

        while ($currentRow <= $highestRow) {
            $screwType = $this->getCellValue($sheet, 'A' . $currentRow);
            
            // Skip empty rows, notes, or footer rows
            if (empty($screwType) || $this->isFooterRow($screwType)) {
                $currentRow++;
                continue;
            }

            // Check if this looks like a valid screw type (e.g., "M4x40", "M4 U-lock Nut")
            if (!$this->isValidScrewType($screwType)) {
                $currentRow++;
                continue;
            }

            // Extract block data (6 rows per block)
            $blockData = $this->extractBlockData($sheet, $currentRow, $headerData);

            // For each date column, create a record
            foreach ($dateColumns as $dateInfo) {
                $record = $this->buildRecordFromBlock($sheet, $currentRow, $blockData, $dateInfo, $timeData, $headerData);
                
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

            // Move to next block (6 rows)
            $currentRow += 6;
        }
    }

    /**
     * Process sheet for execute
     */
    protected function processSheetExecute($sheet, string $sheetName, bool $updateDuplicates): void
    {
        $headerData = $this->parseHeaderData($sheet);
        $dateColumns = $this->parseDateColumns($sheet);
        $timeData = $this->parseTimeData($sheet, $dateColumns);

        if (empty($dateColumns)) {
            $this->executeResults['errors'][] = "Sheet '{$sheetName}': No date columns found";
            return;
        }

        $highestRow = $sheet->getHighestRow();
        $currentRow = 10;

        while ($currentRow <= $highestRow) {
            $screwType = $this->getCellValue($sheet, 'A' . $currentRow);
            
            if (empty($screwType) || $this->isFooterRow($screwType) || !$this->isValidScrewType($screwType)) {
                $currentRow++;
                continue;
            }

            $blockData = $this->extractBlockData($sheet, $currentRow, $headerData);

            foreach ($dateColumns as $dateInfo) {
                $record = $this->buildRecordFromBlock($sheet, $currentRow, $blockData, $dateInfo, $timeData, $headerData);
                
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
                            TorqueRecord::create($record);
                            $this->executeResults['imported']++;
                        }
                    } catch (\Exception $e) {
                        $this->executeResults['errors'][] = "Row {$currentRow}: " . $e->getMessage();
                    }
                }
            }

            $currentRow += 6;
        }
    }

    /**
     * Parse header data from rows 1-5
     */
    protected function parseHeaderData($sheet): array
    {
        $modelSeries = $this->getCellValue($sheet, 'B2');
        $lineAssigned = $this->getCellValue($sheet, 'B3');

        // Clean up model series (remove "MODEL SERIES :" prefix if present)
        if ($modelSeries) {
            $modelSeries = preg_replace('/^MODEL\s*SERIES\s*:\s*/i', '', $modelSeries);
            $modelSeries = trim($modelSeries);
        }

        // Clean up line (remove "LINE :" prefix if present)
        if ($lineAssigned) {
            $lineAssigned = preg_replace('/^LINE\s*:\s*/i', '', $lineAssigned);
            $lineAssigned = trim($lineAssigned);
        }

        return [
            'model_series' => $modelSeries,
            'line_assigned' => $lineAssigned,
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

        // Check columns D onwards (column index 4+)
        for ($colIndex = 4; $colIndex <= $highestColIndex; $colIndex++) {
            $col = Coordinate::stringFromColumnIndex($colIndex);
            $value = $this->getCellValue($sheet, $col . '6');

            if ($value && preg_match('/Date\s*:\s*(\d{1,2}\/\d{1,2}\/\d{4})/i', $value, $matches)) {
                $dateStr = $matches[1];
                $date = $this->parseDate($dateStr);
                
                if ($date) {
                    // AM column is current, PM column is +2
                    $pmColIndex = $colIndex + 2;
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
     * Parse time data from row 9
     */
    protected function parseTimeData($sheet, array $dateColumns): array
    {
        $timeData = [];

        foreach ($dateColumns as $dateInfo) {
            $amTime = $this->getCellValue($sheet, $dateInfo['am_col'] . '9');
            $pmTime = $this->getCellValue($sheet, $dateInfo['pm_col'] . '9');

            $timeData[$dateInfo['am_col']] = [
                'time_am' => $this->parseTime($amTime),
                'time_pm' => $this->parseTime($pmTime),
            ];
        }

        return $timeData;
    }

    /**
     * Extract data from a 6-row block
     */
    protected function extractBlockData($sheet, int $startRow, array $headerData): array
    {
        $screwType = $this->getCellValue($sheet, 'A' . $startRow);
        $driverModel = $this->getCellValue($sheet, 'B' . ($startRow + 1));
        $driverType = $this->getCellValue($sheet, 'B' . ($startRow + 2));
        $processAssigned = $this->getCellValue($sheet, 'A' . ($startRow + 3));

        // Clean driver model (usually "Electric Driver" or "Electic Driver")
        if ($driverModel) {
            $driverModel = preg_replace('/^Ele[c]?tric\s*/i', 'Electric ', $driverModel);
            $driverModel = trim($driverModel);
        }

        return [
            'screw_type' => $screwType,
            'driver_model' => $driverModel ?: 'Electric Driver',
            'driver_type' => $driverType,
            'process_assigned' => $processAssigned,
        ];
    }

    /**
     * Build a record from block data for a specific date column
     */
    protected function buildRecordFromBlock($sheet, int $startRow, array $blockData, array $dateInfo, array $timeData, array $headerData): ?array
    {
        $amCol = $dateInfo['am_col'];
        $pmCol = $dateInfo['pm_col'];

        $torqueAm = $this->getCellValue($sheet, $amCol . $startRow);
        $torquePm = $this->getCellValue($sheet, $pmCol . $startRow);
        $controlNoAm = $this->getCellValue($sheet, $amCol . ($startRow + 1));
        $controlNoPm = $this->getCellValue($sheet, $pmCol . ($startRow + 1));
        $picAm = $this->getCellValue($sheet, $amCol . ($startRow + 3));
        $picPm = $this->getCellValue($sheet, $pmCol . ($startRow + 3));
        $checkedByAm = $this->getCellValue($sheet, $amCol . ($startRow + 4));
        $checkedByPm = $this->getCellValue($sheet, $pmCol . ($startRow + 4));
        $remarksAm = $this->getCellValue($sheet, $amCol . ($startRow + 5));
        $remarksPm = $this->getCellValue($sheet, $pmCol . ($startRow + 5));

        // Use AM control number primarily, fall back to PM
        $controlNo = $controlNoAm ?: $controlNoPm;
        // Use AM PIC primarily, fall back to PM
        $personInCharge = $picAm ?: $picPm;
        // Use AM checked by primarily, fall back to PM
        $checkedBy = $checkedByAm ?: $checkedByPm;
        // Combine remarks
        $remarks = trim(($remarksAm ?: '') . ' ' . ($remarksPm ?: ''));

        $times = $timeData[$amCol] ?? ['time_am' => null, 'time_pm' => null];

        return [
            'date' => $dateInfo['date'],
            'model_series' => $headerData['model_series'],
            'driver_model' => $blockData['driver_model'],
            'driver_type' => $blockData['driver_type'],
            'line_assigned' => $headerData['line_assigned'],
            'control_no' => $controlNo,
            'screw_type' => $blockData['screw_type'],
            'process_assigned' => $blockData['process_assigned'],
            'person_in_charge' => $personInCharge,
            'time_am' => $times['time_am'],
            'torque_am' => $torqueAm,
            'time_pm' => $times['time_pm'],
            'torque_pm' => $torquePm,
            'col_remarks' => $remarks ?: null,
            'checked_by' => $checkedBy,
        ];
    }

    /**
     * Check if record has valid data (at least one torque value)
     */
    protected function hasValidData(array $record): bool
    {
        return !empty($record['torque_am']) || !empty($record['torque_pm']);
    }

    /**
     * Find existing record by duplicate key
     */
    protected function findExistingRecord(array $record): ?TorqueRecord
    {
        return TorqueRecord::where('date', $record['date'])
            ->where('screw_type', $record['screw_type'])
            ->where('process_assigned', $record['process_assigned'])
            ->where('line_assigned', $record['line_assigned'])
            ->first();
    }

    /**
     * Check if row is a footer/note row
     */
    protected function isFooterRow(?string $value): bool
    {
        if (empty($value)) return true;
        
        $lowerValue = strtolower($value);
        return str_contains($lowerValue, 'hpi-pr') 
            || str_contains($lowerValue, 'note')
            || str_contains($lowerValue, 'approved')
            || str_contains($lowerValue, 'torque check sheet');
    }

    /**
     * Check if value looks like a valid screw type
     */
    protected function isValidScrewType(?string $value): bool
    {
        if (empty($value)) return false;
        
        // Screw types typically start with M (e.g., M4x40, M4 U-lock)
        // Or contain process names like "Fastening", "EM", etc.
        return preg_match('/^M\d/i', $value) 
            || preg_match('/fastening/i', $value)
            || preg_match('/^[A-Z]{2,}/i', $value);
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
     * Parse date from string (DD/MM/YYYY)
     */
    protected function parseDate(?string $dateStr): ?string
    {
        if (empty($dateStr)) return null;

        if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $dateStr, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];
            return "{$year}-{$month}-{$day}";
        }

        return null;
    }

    /**
     * Parse time from string (e.g., "10:00AM", "6:20AM")
     */
    protected function parseTime(?string $timeStr): ?string
    {
        if (empty($timeStr)) return null;

        // Handle format like "10:00AM" or "6:20AM"
        if (preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)?/i', $timeStr, $matches)) {
            $hour = (int) $matches[1];
            $minute = $matches[2];
            $ampm = strtoupper($matches[3] ?? '');

            // Convert to 24-hour format if PM
            if ($ampm === 'PM' && $hour < 12) {
                $hour += 12;
            } elseif ($ampm === 'AM' && $hour === 12) {
                $hour = 0;
            }

            return sprintf('%02d:%s', $hour, $minute);
        }

        // Handle Excel numeric time (0.0 - 1.0)
        if (is_numeric($timeStr)) {
            $totalMinutes = round((float) $timeStr * 24 * 60);
            $hours = intdiv($totalMinutes, 60);
            $minutes = $totalMinutes % 60;
            return sprintf('%02d:%02d', $hours, $minutes);
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
