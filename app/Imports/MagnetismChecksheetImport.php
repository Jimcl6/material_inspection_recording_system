<?php

namespace App\Imports;

use App\Models\MagnetismChecksheet;
use App\Models\MagnetismBatch;
use App\Models\MagnetismCheckpoint;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;

class MagnetismChecksheetImport
{
    protected $results = [
        'new_batches' => [],
        'new_checkpoints' => [],
        'duplicate_batches' => [],
        'duplicate_checkpoints' => [],
        'total_batches_parsed' => 0,
        'total_checkpoints_parsed' => 0,
        'errors' => [],
        'sheets_processed' => [],
    ];

    protected $executeResults = [
        'batches_imported' => 0,
        'batches_updated' => 0,
        'batches_skipped' => 0,
        'checkpoints_imported' => 0,
        'checkpoints_updated' => 0,
        'checkpoints_skipped' => 0,
        'checksheets_created' => [],
        'errors' => [],
    ];

    protected $itemCode;
    protected $itemName;
    protected $machineNo;

    // Month name to number mapping
    protected const MONTH_MAP = [
        'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
        'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
        'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
        'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4,
        'jun' => 6, 'jul' => 7, 'aug' => 8, 'sep' => 9,
        'oct' => 10, 'nov' => 11, 'dec' => 12,
    ];

    /**
     * Preview import - Phase 1: Parse file and detect duplicates
     */
    public function preview(string $filePath, string $itemCode, string $itemName, string $machineNo): array
    {
        $this->itemCode = $itemCode;
        $this->itemName = $itemName;
        $this->machineNo = $machineNo;

        try {
            $spreadsheet = IOFactory::load($filePath);

            Log::info('Magnetism Import Preview - Excel file loaded', [
                'total_sheets' => $spreadsheet->getSheetCount(),
                'sheet_names' => $spreadsheet->getSheetNames(),
            ]);

            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                // Skip master/template sheets
                $lowerName = strtolower($sheetName);
                if (str_contains($lowerName, 'master') || str_contains($lowerName, 'template')) {
                    continue;
                }

                // Try to detect month/year from sheet name
                $monthYear = $this->parseMonthYearFromSheetName($sheetName);
                if (!$monthYear) {
                    Log::info("Skipping sheet '{$sheetName}': Could not detect month/year");
                    continue;
                }

                $sheet = $spreadsheet->getSheetByName($sheetName);
                $this->processSheetPreview($sheet, $sheetName, $monthYear['month'], $monthYear['year']);
            }

            return $this->results;

        } catch (\Exception $e) {
            Log::error('Magnetism Import Preview failed', [
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
    public function execute(string $filePath, string $itemCode, string $itemName, string $machineNo, bool $updateDuplicates = false): array
    {
        $this->itemCode = $itemCode;
        $this->itemName = $itemName;
        $this->machineNo = $machineNo;

        try {
            $spreadsheet = IOFactory::load($filePath);

            Log::info('Magnetism Import Execute - Excel file loaded', [
                'total_sheets' => $spreadsheet->getSheetCount(),
                'update_duplicates' => $updateDuplicates,
            ]);

            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                // Skip master/template sheets
                $lowerName = strtolower($sheetName);
                if (str_contains($lowerName, 'master') || str_contains($lowerName, 'template')) {
                    continue;
                }

                // Try to detect month/year from sheet name
                $monthYear = $this->parseMonthYearFromSheetName($sheetName);
                if (!$monthYear) {
                    continue;
                }

                $sheet = $spreadsheet->getSheetByName($sheetName);
                $this->processSheetExecute($sheet, $sheetName, $monthYear['month'], $monthYear['year'], $updateDuplicates);
            }

            return $this->executeResults;

        } catch (\Exception $e) {
            Log::error('Magnetism Import Execute failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->executeResults['errors'][] = 'Failed to process file: ' . $e->getMessage();
            return $this->executeResults;
        }
    }

    /**
     * Parse month and year from sheet name (e.g., "December 2025", "Dec 2025", "2025-12")
     */
    protected function parseMonthYearFromSheetName(string $sheetName): ?array
    {
        // Try "Month Year" format (e.g., "December 2025", "Dec 2025")
        if (preg_match('/(\w+)\s+(\d{4})/i', $sheetName, $matches)) {
            $monthName = strtolower($matches[1]);
            $year = (int) $matches[2];

            if (isset(self::MONTH_MAP[$monthName])) {
                return ['month' => self::MONTH_MAP[$monthName], 'year' => $year];
            }
        }

        // Try "Year-Month" format (e.g., "2025-12")
        if (preg_match('/(\d{4})-(\d{1,2})/', $sheetName, $matches)) {
            return ['month' => (int) $matches[2], 'year' => (int) $matches[1]];
        }

        // Try "Month-Year" format (e.g., "12-2025")
        if (preg_match('/(\d{1,2})-(\d{4})/', $sheetName, $matches)) {
            return ['month' => (int) $matches[1], 'year' => (int) $matches[2]];
        }

        return null;
    }

    /**
     * Process sheet for preview
     */
    protected function processSheetPreview($sheet, string $sheetName, int $month, int $year): void
    {
        $this->results['sheets_processed'][] = [
            'name' => $sheetName,
            'month' => $month,
            'year' => $year,
        ];

        // Find or create checksheet reference for duplicate checking
        $checksheet = MagnetismChecksheet::where('item_code', $this->itemCode)
            ->where('machine_no', $this->machineNo)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        $checksheetId = $checksheet ? $checksheet->id : null;

        // Detect column mapping
        $columnMap = $this->detectColumnMapping($sheet);
        if (empty($columnMap)) {
            $this->results['errors'][] = "Sheet '{$sheetName}': Could not detect column mapping";
            return;
        }

        // Parse date columns (production dates are in specific columns)
        $dateColumns = $this->parseDateColumns($sheet, $columnMap);

        // Process each date column
        foreach ($dateColumns as $dateInfo) {
            $productionDate = $dateInfo['date'];

            // Parse batches for this date
            $this->parseBatchesPreview($sheet, $dateInfo, $columnMap, $checksheetId, $month, $year);

            // Parse checkpoints for this date
            $this->parseCheckpointsPreview($sheet, $dateInfo, $columnMap, $checksheetId, $month, $year);
        }

        Log::info("Preview: Sheet '{$sheetName}' complete", [
            'month' => $month,
            'year' => $year,
            'batches' => $this->results['total_batches_parsed'],
            'checkpoints' => $this->results['total_checkpoints_parsed'],
        ]);
    }

    /**
     * Process sheet for execute
     */
    protected function processSheetExecute($sheet, string $sheetName, int $month, int $year, bool $updateDuplicates): void
    {
        // Find or create checksheet
        $checksheet = MagnetismChecksheet::firstOrCreate(
            [
                'item_code' => $this->itemCode,
                'machine_no' => $this->machineNo,
                'month' => $month,
                'year' => $year,
            ],
            [
                'item_name' => $this->itemName,
            ]
        );

        if ($checksheet->wasRecentlyCreated) {
            $this->executeResults['checksheets_created'][] = [
                'id' => $checksheet->id,
                'month' => $month,
                'year' => $year,
            ];
        }

        // Detect column mapping
        $columnMap = $this->detectColumnMapping($sheet);
        if (empty($columnMap)) {
            $this->executeResults['errors'][] = "Sheet '{$sheetName}': Could not detect column mapping";
            return;
        }

        // Parse date columns
        $dateColumns = $this->parseDateColumns($sheet, $columnMap);

        // Process each date column
        foreach ($dateColumns as $dateInfo) {
            // Import batches for this date
            $this->importBatches($sheet, $dateInfo, $columnMap, $checksheet, $updateDuplicates);

            // Import checkpoints for this date
            $this->importCheckpoints($sheet, $dateInfo, $columnMap, $checksheet, $updateDuplicates);
        }

        Log::info("Execute: Sheet '{$sheetName}' complete", [
            'checksheet_id' => $checksheet->id,
            'batches_imported' => $this->executeResults['batches_imported'],
            'checkpoints_imported' => $this->executeResults['checkpoints_imported'],
        ]);
    }

    /**
     * Detect column mapping from header rows
     */
    protected function detectColumnMapping($sheet): array
    {
        $map = [];
        $highestCol = $sheet->getHighestColumn();
        $highestColIndex = Coordinate::columnIndexFromString($highestCol);

        // Scan rows 8-9 for headers
        for ($rowNum = 7; $rowNum <= 10; $rowNum++) {
            for ($colIndex = 1; $colIndex <= $highestColIndex; $colIndex++) {
                $col = Coordinate::stringFromColumnIndex($colIndex);
                $value = $this->getCellValue($sheet, $col . $rowNum);

                if (empty($value)) continue;

                $upperVal = strtoupper(trim($value));

                // Batch columns
                if (str_contains($upperVal, 'DATE') && !isset($map['date_col'])) {
                    $map['date_col'] = $col;
                    $map['date_row'] = $rowNum;
                }
                if (str_contains($upperVal, 'LETTER') || $upperVal === 'L') {
                    $map['letter_col'] = $col;
                }
                if (str_contains($upperVal, 'MATERIAL') || str_contains($upperVal, 'LOT')) {
                    $map['material_lot_col'] = $col;
                }
                if (str_contains($upperVal, 'QR')) {
                    $map['qr_code_col'] = $col;
                }
                if (str_contains($upperVal, 'PRODUCE') || str_contains($upperVal, 'QTY')) {
                    $map['produce_qty_col'] = $col;
                }
                if (str_contains($upperVal, 'JOB') || str_contains($upperVal, 'ORDER')) {
                    $map['job_number_col'] = $col;
                }

                // Checkpoint headers (typically numbered 1st, 2nd, 3rd, Last or Front/Back)
                if (preg_match('/^1ST|FRONT\s*1|CHECKPOINT\s*1/i', $upperVal)) {
                    $map['checkpoint1_col'] = $col;
                    $map['checkpoint1_row'] = $rowNum;
                }
                if (preg_match('/^2ND|FRONT\s*2|CHECKPOINT\s*2/i', $upperVal)) {
                    $map['checkpoint2_col'] = $col;
                }
                if (preg_match('/^3RD|BACK\s*1|CHECKPOINT\s*3/i', $upperVal)) {
                    $map['checkpoint3_col'] = $col;
                }
                if (preg_match('/^LAST|4TH|BACK\s*2|CHECKPOINT\s*4/i', $upperVal)) {
                    $map['checkpoint4_col'] = $col;
                }

                // Sample and operator columns
                if (str_contains($upperVal, 'FIRST') && str_contains($upperVal, 'INSPECTION')) {
                    $map['first_inspection_start_col'] = $col;
                }
                if (str_contains($upperVal, 'LAST') && str_contains($upperVal, 'INSPECTION')) {
                    $map['last_inspection_start_col'] = $col;
                }
                if (str_contains($upperVal, 'OPERATOR')) {
                    if (!isset($map['operator_first_col'])) {
                        $map['operator_first_col'] = $col;
                    } else {
                        $map['operator_last_col'] = $col;
                    }
                }
                if (str_contains($upperVal, 'JUDGMENT') || str_contains($upperVal, 'JUDGE')) {
                    if (!isset($map['judgment_first_col'])) {
                        $map['judgment_first_col'] = $col;
                    } else {
                        $map['judgment_last_col'] = $col;
                    }
                }
            }
        }

        return $map;
    }

    /**
     * Parse date columns from the sheet
     */
    protected function parseDateColumns($sheet, array $columnMap): array
    {
        $dateColumns = [];
        $highestRow = $sheet->getHighestRow();

        // Find the data start row (typically row 10 or after headers)
        $dataStartRow = 10;

        // Scan for dates in the date column
        if (isset($columnMap['date_col'])) {
            $dateCol = $columnMap['date_col'];

            for ($row = $dataStartRow; $row <= $highestRow; $row++) {
                $dateValue = $this->getCellValue($sheet, $dateCol . $row);
                $parsedDate = $this->parseDate($dateValue);

                if ($parsedDate && !in_array($parsedDate, array_column($dateColumns, 'date'))) {
                    $dateColumns[] = [
                        'date' => $parsedDate,
                        'start_row' => $row,
                        'col' => $dateCol,
                    ];
                }
            }
        }

        // Sort by date
        usort($dateColumns, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        return $dateColumns;
    }

    /**
     * Parse batches for preview
     */
    protected function parseBatchesPreview($sheet, array $dateInfo, array $columnMap, ?int $checksheetId, int $month, int $year): void
    {
        $highestRow = $sheet->getHighestRow();
        $dateCol = $columnMap['date_col'] ?? 'C';
        $productionDate = $dateInfo['date'];

        // Find all rows with this date
        for ($row = 10; $row <= $highestRow; $row++) {
            $cellDate = $this->parseDate($this->getCellValue($sheet, $dateCol . $row));
            if ($cellDate !== $productionDate) continue;

            $batchData = $this->extractBatchData($sheet, $row, $columnMap, $productionDate);
            if (!$this->isValidBatch($batchData)) continue;

            $this->results['total_batches_parsed']++;

            // Check for duplicate
            $existing = $checksheetId ? $this->findExistingBatch($checksheetId, $batchData) : null;

            $previewRecord = [
                'production_date' => $productionDate,
                'letter_code' => $batchData['letter_code'],
                'material_lot_number' => $batchData['material_lot_number'],
                'qr_code' => $batchData['qr_code'],
                'produce_qty' => $batchData['produce_qty'],
                'job_number' => $batchData['job_number'],
                'month' => $month,
                'year' => $year,
            ];

            if ($existing) {
                $this->results['duplicate_batches'][] = [
                    'existing_id' => $existing->id,
                    'existing_data' => $existing->toArray(),
                    'new_data' => $previewRecord,
                ];
            } else {
                $this->results['new_batches'][] = $previewRecord;
            }
        }
    }

    /**
     * Parse checkpoints for preview
     */
    protected function parseCheckpointsPreview($sheet, array $dateInfo, array $columnMap, ?int $checksheetId, int $month, int $year): void
    {
        $productionDate = $dateInfo['date'];

        // Parse 4 checkpoints for this date
        for ($checkpointNum = 1; $checkpointNum <= 4; $checkpointNum++) {
            $checkpointData = $this->extractCheckpointData($sheet, $dateInfo, $columnMap, $checkpointNum);
            if (!$this->hasCheckpointData($checkpointData)) continue;

            $this->results['total_checkpoints_parsed']++;

            // Check for duplicate
            $existing = $checksheetId ? $this->findExistingCheckpoint($checksheetId, $productionDate, $checkpointNum) : null;

            $previewRecord = array_merge($checkpointData, [
                'production_date' => $productionDate,
                'checkpoint_number' => $checkpointNum,
                'month' => $month,
                'year' => $year,
            ]);

            if ($existing) {
                $this->results['duplicate_checkpoints'][] = [
                    'existing_id' => $existing->id,
                    'existing_data' => $existing->toArray(),
                    'new_data' => $previewRecord,
                ];
            } else {
                $this->results['new_checkpoints'][] = $previewRecord;
            }
        }
    }

    /**
     * Import batches
     */
    protected function importBatches($sheet, array $dateInfo, array $columnMap, MagnetismChecksheet $checksheet, bool $updateDuplicates): void
    {
        $highestRow = $sheet->getHighestRow();
        $dateCol = $columnMap['date_col'] ?? 'C';
        $productionDate = $dateInfo['date'];

        // Track letter codes for this import
        $letterCodeTracker = [];

        for ($row = 10; $row <= $highestRow; $row++) {
            $cellDate = $this->parseDate($this->getCellValue($sheet, $dateCol . $row));
            if ($cellDate !== $productionDate) continue;

            $batchData = $this->extractBatchData($sheet, $row, $columnMap, $productionDate);
            if (!$this->isValidBatch($batchData)) continue;

            try {
                // Get letter code for this material lot
                $letterCode = $batchData['letter_code'];
                if (empty($letterCode) && !empty($batchData['material_lot_number'])) {
                    $letterCode = MagnetismBatch::getLetterForMaterialLot($checksheet->id, $batchData['material_lot_number']);
                }

                $existing = $this->findExistingBatch($checksheet->id, $batchData);

                if ($existing) {
                    if ($updateDuplicates) {
                        $existing->update([
                            'letter_code' => $letterCode,
                            'produce_qty' => $batchData['produce_qty'],
                            'job_number' => $batchData['job_number'],
                            'remarks' => $batchData['remarks'],
                        ]);
                        $this->executeResults['batches_updated']++;
                    } else {
                        $this->executeResults['batches_skipped']++;
                    }
                } else {
                    MagnetismBatch::create([
                        'checksheet_id' => $checksheet->id,
                        'production_date' => $productionDate,
                        'letter_code' => $letterCode,
                        'material_lot_number' => $batchData['material_lot_number'],
                        'qr_code' => $batchData['qr_code'],
                        'produce_qty' => $batchData['produce_qty'],
                        'job_number' => $batchData['job_number'],
                        'remarks' => $batchData['remarks'],
                    ]);
                    $this->executeResults['batches_imported']++;
                }
            } catch (\Exception $e) {
                $this->executeResults['errors'][] = "Batch row {$row}: " . $e->getMessage();
            }
        }
    }

    /**
     * Import checkpoints
     */
    protected function importCheckpoints($sheet, array $dateInfo, array $columnMap, MagnetismChecksheet $checksheet, bool $updateDuplicates): void
    {
        $productionDate = $dateInfo['date'];

        for ($checkpointNum = 1; $checkpointNum <= 4; $checkpointNum++) {
            $checkpointData = $this->extractCheckpointData($sheet, $dateInfo, $columnMap, $checkpointNum);
            if (!$this->hasCheckpointData($checkpointData)) continue;

            try {
                $existing = $this->findExistingCheckpoint($checksheet->id, $productionDate, $checkpointNum);

                if ($existing) {
                    if ($updateDuplicates) {
                        $existing->update($checkpointData);
                        $this->executeResults['checkpoints_updated']++;
                    } else {
                        $this->executeResults['checkpoints_skipped']++;
                    }
                } else {
                    MagnetismCheckpoint::create(array_merge($checkpointData, [
                        'checksheet_id' => $checksheet->id,
                        'production_date' => $productionDate,
                        'checkpoint_number' => $checkpointNum,
                    ]));
                    $this->executeResults['checkpoints_imported']++;
                }
            } catch (\Exception $e) {
                $this->executeResults['errors'][] = "Checkpoint {$checkpointNum} for {$productionDate}: " . $e->getMessage();
            }
        }
    }

    /**
     * Extract batch data from a row
     */
    protected function extractBatchData($sheet, int $row, array $columnMap, string $productionDate): array
    {
        return [
            'production_date' => $productionDate,
            'letter_code' => $this->getCellValue($sheet, ($columnMap['letter_col'] ?? 'D') . $row),
            'material_lot_number' => $this->getCellValue($sheet, ($columnMap['material_lot_col'] ?? 'E') . $row),
            'qr_code' => $this->getCellValue($sheet, ($columnMap['qr_code_col'] ?? 'F') . $row),
            'produce_qty' => $this->parseInteger($this->getCellValue($sheet, ($columnMap['produce_qty_col'] ?? 'G') . $row)),
            'job_number' => $this->getCellValue($sheet, ($columnMap['job_number_col'] ?? 'H') . $row),
            'remarks' => null,
        ];
    }

    /**
     * Extract checkpoint data
     */
    protected function extractCheckpointData($sheet, array $dateInfo, array $columnMap, int $checkpointNum): array
    {
        // The checkpoint rows are typically offset from the date row
        // Checkpoint 1 (1st/Front 1), Checkpoint 2 (2nd/Front 2), Checkpoint 3 (3rd/Back 1), Checkpoint 4 (Last/Back 2)
        $baseRow = $dateInfo['start_row'];
        $checkpointRow = $baseRow + ($checkpointNum - 1);

        // Get sample columns - typically 5 samples for first inspection, 5 for last
        $firstInspectionStartCol = $columnMap['first_inspection_start_col'] ?? 'I';
        $lastInspectionStartCol = $columnMap['last_inspection_start_col'] ?? 'N';

        $firstStartIndex = Coordinate::columnIndexFromString($firstInspectionStartCol);
        $lastStartIndex = Coordinate::columnIndexFromString($lastInspectionStartCol);

        return [
            'sample1_first' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($firstStartIndex) . $checkpointRow)),
            'sample2_first' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($firstStartIndex + 1) . $checkpointRow)),
            'sample3_first' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($firstStartIndex + 2) . $checkpointRow)),
            'sample4_first' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($firstStartIndex + 3) . $checkpointRow)),
            'sample5_first' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($firstStartIndex + 4) . $checkpointRow)),
            'operator_first' => $this->getCellValue($sheet, ($columnMap['operator_first_col'] ?? Coordinate::stringFromColumnIndex($firstStartIndex + 5)) . $checkpointRow),
            'judgment_first' => $this->getCellValue($sheet, ($columnMap['judgment_first_col'] ?? Coordinate::stringFromColumnIndex($firstStartIndex + 6)) . $checkpointRow),
            'sample1_last' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($lastStartIndex) . $checkpointRow)),
            'sample2_last' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($lastStartIndex + 1) . $checkpointRow)),
            'sample3_last' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($lastStartIndex + 2) . $checkpointRow)),
            'sample4_last' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($lastStartIndex + 3) . $checkpointRow)),
            'sample5_last' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($lastStartIndex + 4) . $checkpointRow)),
            'operator_last' => $this->getCellValue($sheet, ($columnMap['operator_last_col'] ?? Coordinate::stringFromColumnIndex($lastStartIndex + 5)) . $checkpointRow),
            'judgment_last' => $this->getCellValue($sheet, ($columnMap['judgment_last_col'] ?? Coordinate::stringFromColumnIndex($lastStartIndex + 6)) . $checkpointRow),
            'checked_by' => null,
        ];
    }

    /**
     * Check if batch data is valid
     */
    protected function isValidBatch(array $data): bool
    {
        return !empty($data['material_lot_number']) || !empty($data['qr_code']);
    }

    /**
     * Check if checkpoint has any data
     */
    protected function hasCheckpointData(array $data): bool
    {
        return $data['sample1_first'] !== null
            || $data['sample1_last'] !== null
            || !empty($data['operator_first'])
            || !empty($data['operator_last']);
    }

    /**
     * Find existing batch by duplicate key
     */
    protected function findExistingBatch(int $checksheetId, array $data): ?MagnetismBatch
    {
        return MagnetismBatch::where('checksheet_id', $checksheetId)
            ->where('production_date', $data['production_date'])
            ->where('material_lot_number', $data['material_lot_number'])
            ->where('qr_code', $data['qr_code'])
            ->first();
    }

    /**
     * Find existing checkpoint by duplicate key
     */
    protected function findExistingCheckpoint(int $checksheetId, string $productionDate, int $checkpointNumber): ?MagnetismCheckpoint
    {
        return MagnetismCheckpoint::where('checksheet_id', $checksheetId)
            ->where('production_date', $productionDate)
            ->where('checkpoint_number', $checkpointNumber)
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
     * Parse date from various formats
     */
    protected function parseDate($value): ?string
    {
        if ($value === null || $value === '') return null;

        // Handle Excel numeric date
        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->toDateString();
            } catch (\Exception $e) {
                return null;
            }
        }

        // Handle string formats
        if (is_string($value)) {
            // DD/MM/YYYY
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $m)) {
                return sprintf('%s-%02d-%02d', $m[3], $m[2], $m[1]);
            }

            // YYYY-MM-DD
            if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value, $m)) {
                return sprintf('%s-%02d-%02d', $m[1], $m[2], $m[3]);
            }

            try {
                return Carbon::parse($value)->toDateString();
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Parse integer value
     */
    protected function parseInteger($value): int
    {
        if ($value === null || $value === '') return 0;
        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * Parse decimal value
     */
    protected function parseDecimal($value): ?float
    {
        if ($value === null || $value === '') return null;
        $cleaned = preg_replace('/[^0-9.-]/', '', (string) $value);
        return is_numeric($cleaned) ? round((float) $cleaned, 1) : null;
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
