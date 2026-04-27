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
    // Supported format types
    public const FORMAT_HPI_PR03_01 = 'HPI-PR03-01';
    public const FORMAT_HPI_PR05_03 = 'HPI-PR05-03';
    public const DEFAULT_FORMAT = self::FORMAT_HPI_PR05_03;

    // Format-specific column mappings
    protected const FORMAT_MAPPINGS = [
        self::FORMAT_HPI_PR03_01 => [
            'header_row' => 6,
            'data_start_row' => 9,
            'date_col' => 'A',
            'letter_col' => 'I',
            'material_lot_col' => 'J',
            'qr_code_col' => 'K',
            'produce_qty_col' => 'L',
            'job_number_col' => 'M',
            'total_qty_col' => 'N',
            'checkpoint_col' => 'O',
            'first_samples_start_col' => 'P',
            'operator_first_col' => 'U',
            'judgment_first_col' => 'V',
            'last_samples_start_col' => 'W',
            'operator_last_col' => 'AB',
            'judgment_last_col' => 'AC',
            'checked_by_col' => 'AD',
            'remarks_col' => 'AE',
            'has_total_qty' => true,
            'has_operator_cols' => true,
        ],
        self::FORMAT_HPI_PR05_03 => [
            'header_row' => 8,
            'data_start_row' => 11,
            'date_col' => 'A',
            'letter_col' => 'I',
            'material_lot_col' => 'J',
            'qr_code_col' => 'K',
            'produce_qty_col' => 'L',
            'job_number_col' => 'M',
            'total_qty_col' => null,
            'checkpoint_col' => 'U',
            'first_samples_start_col' => 'V',
            'operator_first_col' => null,
            'judgment_first_col' => 'AA',
            'last_samples_start_col' => 'AB',
            'operator_last_col' => null,
            'judgment_last_col' => 'AG',
            'checked_by_col' => 'AH',
            'remarks_col' => 'AI',
            'has_total_qty' => false,
            'has_operator_cols' => false,
        ],
    ];

    protected $results = [
        'new_batches' => [],
        'new_checkpoints' => [],
        'duplicate_batches' => [],
        'duplicate_checkpoints' => [],
        'total_batches_parsed' => 0,
        'total_checkpoints_parsed' => 0,
        'errors' => [],
        'sheets_processed' => [],
        'detected_format' => null,
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
    protected $format;
    protected $formatMapping;

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
     *
     * @param string $filePath Path to Excel file
     * @param string $itemCode Item code
     * @param string $itemName Item name
     * @param string $machineNo Machine number
     * @param string|null $format Format type (HPI-PR03-01 or HPI-PR05-03), null for auto-detect
     */
    public function preview(string $filePath, string $itemCode, string $itemName, string $machineNo, ?string $format = null): array
    {
        $this->itemCode = $itemCode;
        $this->itemName = $itemName;
        $this->machineNo = $machineNo;

        try {
            $spreadsheet = IOFactory::load($filePath);

            // Auto-detect or validate format
            $this->format = $this->resolveFormat($spreadsheet, $format);
            $this->formatMapping = self::FORMAT_MAPPINGS[$this->format];
            $this->results['detected_format'] = $this->format;

            Log::info('Magnetism Import Preview - Excel file loaded', [
                'total_sheets' => $spreadsheet->getSheetCount(),
                'sheet_names' => $spreadsheet->getSheetNames(),
                'format' => $this->format,
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
     *
     * @param string $filePath Path to Excel file
     * @param string $itemCode Item code
     * @param string $itemName Item name
     * @param string $machineNo Machine number
     * @param bool $updateDuplicates Whether to update duplicate records
     * @param string|null $format Format type (HPI-PR03-01 or HPI-PR05-03)
     */
    public function execute(string $filePath, string $itemCode, string $itemName, string $machineNo, bool $updateDuplicates = false, ?string $format = null): array
    {
        $this->itemCode = $itemCode;
        $this->itemName = $itemName;
        $this->machineNo = $machineNo;

        try {
            $spreadsheet = IOFactory::load($filePath);

            // Use provided format or auto-detect
            $this->format = $this->resolveFormat($spreadsheet, $format);
            $this->formatMapping = self::FORMAT_MAPPINGS[$this->format];

            Log::info('Magnetism Import Execute - Excel file loaded', [
                'total_sheets' => $spreadsheet->getSheetCount(),
                'update_duplicates' => $updateDuplicates,
                'format' => $this->format,
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
     * Resolve format - use provided format or auto-detect
     */
    protected function resolveFormat($spreadsheet, ?string $format): string
    {
        // If format is provided and valid, use it
        if ($format && isset(self::FORMAT_MAPPINGS[$format])) {
            return $format;
        }

        // Auto-detect format from spreadsheet structure
        $detectedFormat = $this->detectFormat($spreadsheet);

        Log::info('Magnetism Import - Format resolved', [
            'provided' => $format,
            'detected' => $detectedFormat,
        ]);

        return $detectedFormat;
    }

    /**
     * Auto-detect format from spreadsheet structure
     * Checks the position of "Checkpoint" header to determine format
     */
    protected function detectFormat($spreadsheet): string
    {
        // Get the first data sheet (skip master/template)
        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $lowerName = strtolower($sheetName);
            if (str_contains($lowerName, 'master') || str_contains($lowerName, 'template')) {
                continue;
            }

            // Check if this sheet has month/year data
            if (!$this->parseMonthYearFromSheetName($sheetName)) {
                continue;
            }

            $sheet = $spreadsheet->getSheetByName($sheetName);

            // Check for HPI-PR03-01 format: Checkpoint in column O, header row 6
            $checkpointO = $this->getCellValue($sheet, 'O6');
            if ($checkpointO && stripos($checkpointO, 'checkpoint') !== false) {
                return self::FORMAT_HPI_PR03_01;
            }

            // Check for HPI-PR05-03 format: Checkpoint in column U, header row 8
            $checkpointU = $this->getCellValue($sheet, 'U8');
            if ($checkpointU && stripos($checkpointU, 'checkpoint') !== false) {
                return self::FORMAT_HPI_PR05_03;
            }

            // Alternative detection: Check for "N=5 (First Inspection)" position
            // HPI-PR03-01: Column P, row 6
            // HPI-PR05-03: Column V, row 8
            $firstInspP = $this->getCellValue($sheet, 'P6');
            if ($firstInspP && stripos($firstInspP, 'first') !== false && stripos($firstInspP, 'inspection') !== false) {
                return self::FORMAT_HPI_PR03_01;
            }

            $firstInspV = $this->getCellValue($sheet, 'V8');
            if ($firstInspV && stripos($firstInspV, 'first') !== false && stripos($firstInspV, 'inspection') !== false) {
                return self::FORMAT_HPI_PR05_03;
            }

            // Check header row position - HPI-PR03-01 has headers at row 6, HPI-PR05-03 at row 8
            $lotNumberRow6 = $this->getCellValue($sheet, 'A6');
            if ($lotNumberRow6 && stripos($lotNumberRow6, 'lot') !== false) {
                return self::FORMAT_HPI_PR03_01;
            }

            $lotNumberRow8 = $this->getCellValue($sheet, 'A8');
            if ($lotNumberRow8 && stripos($lotNumberRow8, 'lot') !== false) {
                return self::FORMAT_HPI_PR05_03;
            }

            break; // Only check first valid sheet
        }

        // Default to HPI-PR05-03 if detection fails
        return self::DEFAULT_FORMAT;
    }

    /**
     * Get available formats for UI
     */
    public static function getAvailableFormats(): array
    {
        return [
            self::FORMAT_HPI_PR03_01 => 'HPI-PR03-01 (Tesla 120~150mT)',
            self::FORMAT_HPI_PR05_03 => 'HPI-PR05-03 (Tesla 160~210mT)',
        ];
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
        // Find or create checksheet - include soft-deleted records to handle unique constraint
        $checksheet = MagnetismChecksheet::withTrashed()
            ->where('item_code', $this->itemCode)
            ->where('machine_no', $this->machineNo)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($checksheet) {
            // Restore if soft-deleted
            if ($checksheet->trashed()) {
                $checksheet->restore();
            }
            // Update item_name
            $checksheet->update(['item_name' => $this->itemName]);
        } else {
            // Create new checksheet
            try {
                $checksheet = MagnetismChecksheet::create([
                    'item_code' => $this->itemCode,
                    'machine_no' => $this->machineNo,
                    'month' => $month,
                    'year' => $year,
                    'item_name' => $this->itemName,
                ]);

                $this->executeResults['checksheets_created'][] = [
                    'id' => $checksheet->id,
                    'month' => $month,
                    'year' => $year,
                ];
            } catch (\Exception $e) {
                $this->executeResults['errors'][] = "Sheet '{$sheetName}': Failed to create checksheet - " . $e->getMessage();
                return;
            }
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
     * Get column mapping from format configuration
     * Uses the pre-defined format mappings based on detected/selected format
     */
    protected function detectColumnMapping($sheet): array
    {
        // Use the format mapping directly
        return $this->formatMapping;
    }

    /**
     * Parse date columns from the sheet
     */
    protected function parseDateColumns($sheet, array $columnMap): array
    {
        $dateColumns = [];
        $highestRow = $sheet->getHighestRow();

        // Use format-specific data start row
        $dataStartRow = $columnMap['data_start_row'] ?? 10;
        $dateCol = $columnMap['date_col'] ?? 'A';

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
        $dateCol = $columnMap['date_col'] ?? 'A';
        $dataStartRow = $columnMap['data_start_row'] ?? 10;
        $productionDate = $dateInfo['date'];

        // Find all rows with this date
        for ($row = $dataStartRow; $row <= $highestRow; $row++) {
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
        $dateCol = $columnMap['date_col'] ?? 'A';
        $dataStartRow = $columnMap['data_start_row'] ?? 10;
        $productionDate = $dateInfo['date'];

        // Track letter codes for this import
        $letterCodeTracker = [];

        for ($row = $dataStartRow; $row <= $highestRow; $row++) {
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
            'letter_code' => $this->getCellValue($sheet, ($columnMap['letter_col'] ?? 'I') . $row),
            'material_lot_number' => $this->getCellValue($sheet, ($columnMap['material_lot_col'] ?? 'J') . $row),
            'qr_code' => $this->getCellValue($sheet, ($columnMap['qr_code_col'] ?? 'K') . $row),
            'produce_qty' => $this->parseInteger($this->getCellValue($sheet, ($columnMap['produce_qty_col'] ?? 'L') . $row)),
            'job_number' => $this->getCellValue($sheet, ($columnMap['job_number_col'] ?? 'M') . $row),
            'remarks' => $this->getCellValue($sheet, ($columnMap['remarks_col'] ?? null) ? $columnMap['remarks_col'] . $row : null),
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
        $checkpointRow = $baseRow + (($checkpointNum - 1) * 2); // Each checkpoint has 2 rows (data + empty)

        // Get sample columns from format mapping
        $firstSamplesStartCol = $columnMap['first_samples_start_col'] ?? 'P';
        $lastSamplesStartCol = $columnMap['last_samples_start_col'] ?? 'W';

        $firstStartIndex = Coordinate::columnIndexFromString($firstSamplesStartCol);
        $lastStartIndex = Coordinate::columnIndexFromString($lastSamplesStartCol);

        // Get operator columns (may be null for some formats)
        $operatorFirstCol = $columnMap['operator_first_col'] ?? null;
        $operatorLastCol = $columnMap['operator_last_col'] ?? null;
        $judgmentFirstCol = $columnMap['judgment_first_col'] ?? 'V';
        $judgmentLastCol = $columnMap['judgment_last_col'] ?? 'AC';
        $checkedByCol = $columnMap['checked_by_col'] ?? null;

        return [
            'sample1_first' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($firstStartIndex) . $checkpointRow)),
            'sample2_first' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($firstStartIndex + 1) . $checkpointRow)),
            'sample3_first' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($firstStartIndex + 2) . $checkpointRow)),
            'sample4_first' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($firstStartIndex + 3) . $checkpointRow)),
            'sample5_first' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($firstStartIndex + 4) . $checkpointRow)),
            'operator_first' => $operatorFirstCol ? $this->getCellValue($sheet, $operatorFirstCol . $checkpointRow) : null,
            'judgment_first' => $this->getCellValue($sheet, $judgmentFirstCol . $checkpointRow),
            'sample1_last' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($lastStartIndex) . $checkpointRow)),
            'sample2_last' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($lastStartIndex + 1) . $checkpointRow)),
            'sample3_last' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($lastStartIndex + 2) . $checkpointRow)),
            'sample4_last' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($lastStartIndex + 3) . $checkpointRow)),
            'sample5_last' => $this->parseDecimal($this->getCellValue($sheet, Coordinate::stringFromColumnIndex($lastStartIndex + 4) . $checkpointRow)),
            'operator_last' => $operatorLastCol ? $this->getCellValue($sheet, $operatorLastCol . $checkpointRow) : null,
            'judgment_last' => $this->getCellValue($sheet, $judgmentLastCol . $checkpointRow),
            'checked_by' => $checkedByCol ? $this->getCellValue($sheet, $checkedByCol . $checkpointRow) : null,
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
