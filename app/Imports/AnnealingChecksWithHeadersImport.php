<?php

namespace App\Imports;

use App\Models\AnnealingCheck;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AnnealingChecksWithHeadersImport
{
    private $importResults = [];
    private $currentUser;

    // Preview results
    protected $previewResults = [
        'new_records' => [],
        'duplicate_records' => [],
        'total_parsed' => 0,
        'errors' => [],
    ];

    // Execute results
    protected $executeResults = [
        'imported' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    public function __construct()
    {
        $this->currentUser = auth()->user();
    }

    /**
     * Import from an uploaded file
     */
    public function import($filePath): void
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $spreadsheet = $reader->load($filePath);

        Log::info('Excel file loaded', [
            'total_sheets' => $spreadsheet->getSheetCount(),
            'sheet_names' => $spreadsheet->getSheetNames(),
        ]);

        foreach ($spreadsheet->getSheetNames() as $index => $sheetName) {
            $sheet = $spreadsheet->getSheet($index);
            $highestRow = $sheet->getHighestRow();

            // Skip sheets with very few rows (no data)
            if ($highestRow < 10) {
                Log::info("Skipping sheet '{$sheetName}': only {$highestRow} rows");
                continue;
            }

            // Detect column mapping from header rows 8 and 9
            $columnMap = $this->detectColumnMapping($sheet);

            if (empty($columnMap) || !isset($columnMap['item_code'])) {
                Log::info("Skipping sheet '{$sheetName}': could not detect column mapping");
                continue;
            }

            Log::info("Processing sheet '{$sheetName}'", [
                'highest_row' => $highestRow,
                'column_map' => $columnMap,
            ]);

            $this->processSheet($sheet, $sheetName, $highestRow, $columnMap);
        }
    }

    /**
     * Detect column mapping by scanning header rows 8 and 9
     */
    private function detectColumnMapping($sheet): array
    {
        $map = [];
        $columns = range('A', 'S');

        // Scan row 8 for main headers
        foreach ($columns as $col) {
            $val = $sheet->getCell($col . '8')->getValue();
            if ($val === null) continue;
            $val = strtoupper(trim(preg_replace('/\s+/', ' ', $val)));

            if (str_contains($val, 'ITEM CODE')) $map['item_code'] = $col;
            elseif (str_contains($val, 'RECEIVING')) $map['receiving_date'] = $col;
            elseif (str_contains($val, 'SUPPLIER LOT') && !isset($map['supplier_lot_number'])) $map['supplier_lot_number'] = $col;
            elseif (str_contains($val, 'VARNISHING')) $map['varnishing_date'] = $col;
            elseif (str_contains($val, 'QUANTITY') && !isset($map['quantity'])) $map['quantity'] = $col;
            elseif (str_contains($val, 'ANNEALING') && str_contains($val, 'DATE')) $map['annealing_date'] = $col;
            elseif (str_contains($val, 'MACHINE') && str_contains($val, '#')) $map['machine_number'] = $col;
            elseif (str_contains($val, 'MACHINE') && str_contains($val, 'SETTING')) $map['machine_setting_start'] = $col;
            elseif (str_contains($val, 'TIME') && !isset($map['time_start'])) $map['time_start'] = $col;
            elseif (str_contains($val, 'PIC')) $map['pic'] = $col;
            elseif (str_contains($val, 'CHECKED')) $map['checked_by'] = $col;
            elseif (str_contains($val, 'VERIFIED')) $map['verified_by'] = $col;
            elseif (str_contains($val, 'REMARKS')) $map['remarks'] = $col;
        }

        // Scan row 9 for sub-headers (temperature, annealing time, damper, time in/out)
        foreach ($columns as $col) {
            $val = $sheet->getCell($col . '9')->getValue();
            if ($val === null) continue;
            $val = strtoupper(trim(preg_replace('/\s+/', ' ', $val)));

            if (str_contains($val, 'TEMPERATURE')) $map['temperature'] = $col;
            elseif (str_contains($val, 'ANNEALING TIME')) $map['annealing_time'] = $col;
            elseif (str_contains($val, 'DAMPER')) $map['damper_setting'] = $col;
            elseif (str_contains($val, 'IN') && !str_contains($val, 'ANNEAL')) $map['time_in'] = $col;
            elseif (str_contains($val, 'OUT')) $map['time_out'] = $col;
        }

        // Also check row 8 for second supplier lot / quantity that appears after annealing date
        // These are the production-side columns
        $annealingDateCol = $map['annealing_date'] ?? null;
        if ($annealingDateCol) {
            $nextColIndex = ord($annealingDateCol) - ord('A') + 1;
            foreach ($columns as $col) {
                if (ord($col) - ord('A') <= ord($annealingDateCol) - ord('A')) continue;
                $val = $sheet->getCell($col . '8')->getValue();
                if ($val === null) continue;
                $val = strtoupper(trim(preg_replace('/\s+/', ' ', $val)));

                if (str_contains($val, 'SUPPLIER LOT') && isset($map['supplier_lot_number'])) {
                    $map['supplier_lot_number_prod'] = $col;
                }
                elseif (str_contains($val, 'QUANTITY') && isset($map['quantity'])) {
                    $map['quantity_prod'] = $col;
                }
            }
        }

        return $map;
    }

    /**
     * Process a single sheet
     */
    private function processSheet($sheet, string $sheetName, int $highestRow, array $columnMap): void
    {
        $sheetResults = [
            'sheet_name' => $sheetName,
            'imported' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        // Data starts at row 10
        for ($rowNum = 10; $rowNum <= $highestRow; $rowNum++) {
            // Read item code first to check if row has data
            $itemCode = trim((string) $sheet->getCell($columnMap['item_code'] . $rowNum)->getValue());

            // Skip empty rows or non-data rows
            if (empty($itemCode) || preg_match('/^HPI-PR/i', $itemCode) || preg_match('/NOTE/i', $itemCode)) {
                $sheetResults['skipped']++;
                continue;
            }

            // Must look like a real item code (letters, numbers, hyphens)
            if (!preg_match('/^[A-Z]{2}[\dA-Z]/', $itemCode)) {
                $sheetResults['skipped']++;
                continue;
            }

            try {
                $data = $this->extractRowData($sheet, $rowNum, $columnMap, $sheetName);
                $annealingCheck = AnnealingCheck::create($data);
                $sheetResults['imported']++;

                Log::info("Imported: {$annealingCheck->item_code} from '{$sheetName}' row {$rowNum}");

            } catch (\Exception $e) {
                $sheetResults['errors'][] = "Row {$rowNum}: " . $e->getMessage();
                Log::error("Import error on '{$sheetName}' row {$rowNum}: " . $e->getMessage());
            }
        }

        $this->importResults[] = $sheetResults;

        Log::info("Sheet '{$sheetName}' complete", [
            'imported' => $sheetResults['imported'],
            'skipped' => $sheetResults['skipped'],
            'errors' => count($sheetResults['errors']),
        ]);
    }

    /**
     * Extract data from a single row using the column map
     */
    private function extractRowData($sheet, int $rowNum, array $columnMap, string $sheetName): array
    {
        $get = function (string $key) use ($sheet, $rowNum, $columnMap) {
            if (!isset($columnMap[$key])) return null;
            return $sheet->getCell($columnMap[$key] . $rowNum)->getCalculatedValue();
        };

        $itemCode = trim((string) $get('item_code'));

        // Use production supplier lot if available, otherwise purchasing
        $supplierLot = $get('supplier_lot_number_prod') ?? $get('supplier_lot_number');
        $quantity = $get('quantity_prod') ?? $get('quantity');

        return [
            'item_code' => $itemCode,
            'receiving_date' => $this->parseDate($get('receiving_date')) ?? now()->toDateString(),
            'supplier_lot_number' => $supplierLot ? (string) $supplierLot : null,
            'quantity' => $this->parseInteger($quantity),
            'annealing_date' => $this->parseDate($get('annealing_date')) ?? now()->toDateString(),
            'machine_number' => $get('machine_number') ? (string) $get('machine_number') : null,
            'machine_setting' => null,
            'temperature_setting' => $this->parseTemperature($get('temperature')),
            'annealing_time' => $this->parseAnnealingTime($get('annealing_time')),
            'damper_setting' => $get('damper_setting') !== null ? (string) $get('damper_setting') : null,
            'time_in' => $this->parseExcelTime($get('time_in')),
            'time_out' => $this->parseExcelTime($get('time_out')),
            'pic_id' => $this->convertNameToId($get('pic')) ?? $this->currentUser->id,
            'checked_by_id' => $this->convertNameToId($get('checked_by')),
            'verified_by_id' => $this->convertNameToId($get('verified_by')),
            'remarks' => $get('remarks') ? (string) $get('remarks') : null,
            'status' => 'approved',
            'approved_at' => now(),
            'created_by' => $this->currentUser->id,
            'updated_by' => $this->currentUser->id,
        ];
    }

    /**
     * Parse temperature value like "120℃" or "80℃"
     */
    private function parseTemperature($value): ?float
    {
        if ($value === null) return null;
        $cleaned = preg_replace('/[^0-9.]/', '', (string) $value);
        return is_numeric($cleaned) ? (float) $cleaned : null;
    }

    /**
     * Parse annealing time like "3 HRS" or "2 HRS"
     */
    private function parseAnnealingTime($value): ?string
    {
        if ($value === null) return null;
        $str = (string) $value;
        // Extract number of hours
        if (preg_match('/(\d+)\s*HR/i', $str, $m)) {
            return sprintf('%02d:00:00', $m[1]);
        }
        if (is_numeric($value)) {
            $hours = floor($value);
            $minutes = round(($value - $hours) * 60);
            return sprintf('%02d:%02d:00', $hours, $minutes);
        }
        return $str;
    }

    /**
     * Parse Excel time fraction (0.708333 = 17:00)
     */
    private function parseExcelTime($value): ?string
    {
        if ($value === null) return null;
        if (is_numeric($value) && $value >= 0 && $value <= 1) {
            $totalMinutes = round($value * 24 * 60);
            $hours = intdiv($totalMinutes, 60);
            $minutes = $totalMinutes % 60;
            return sprintf('%02d:%02d:00', $hours, $minutes);
        }
        // Try HH:MM format
        if (is_string($value) && preg_match('/(\d{1,2}):(\d{2})/', $value, $m)) {
            return sprintf('%02d:%02d:00', $m[1], $m[2]);
        }
        return null;
    }

    private function parseInteger($value): int
    {
        if ($value === null || $value === '') return 0;
        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * Parse date from various formats
     */
    protected function parseDate($date)
    {
        if ($date === null) return null;

        if ($date instanceof \DateTime) {
            return $date->format('Y-m-d');
        }

        if (is_numeric($date)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($date))->toDateString();
            } catch (\Exception $e) {
                return null;
            }
        }

        if (is_string($date)) {
            // Handle dd/mm/yyyy format
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $m)) {
                try {
                    return Carbon::createFromFormat('d/m/Y', $date)->toDateString();
                } catch (\Exception $e) {
                    return null;
                }
            }

            try {
                return Carbon::parse($date)->toDateString();
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Convert user name to user ID
     */
    private function convertNameToId($name): ?int
    {
        if (empty($name)) return null;
        if (is_numeric($name)) return (int) $name;

        $cleanName = trim(strtolower((string) $name));
        $user = User::whereRaw('LOWER(name) = ?', [$cleanName])->first();
        return $user ? $user->id : null;
    }

    /**
     * Get import results
     */
    public function getResults(): array
    {
        return $this->importResults;
    }

    /**
     * Preview import - Phase 1: Parse file and detect duplicates
     */
    public function preview(string $filePath): array
    {
        try {
            $reader = IOFactory::createReaderForFile($filePath);
            $spreadsheet = $reader->load($filePath);

            Log::info('Annealing Import Preview - Excel file loaded', [
                'total_sheets' => $spreadsheet->getSheetCount(),
                'sheet_names' => $spreadsheet->getSheetNames(),
            ]);

            foreach ($spreadsheet->getSheetNames() as $index => $sheetName) {
                $sheet = $spreadsheet->getSheet($index);
                $highestRow = $sheet->getHighestRow();

                // Skip sheets with very few rows (no data)
                if ($highestRow < 10) {
                    Log::info("Preview: Skipping sheet '{$sheetName}': only {$highestRow} rows");
                    continue;
                }

                // Detect column mapping from header rows 8 and 9
                $columnMap = $this->detectColumnMapping($sheet);

                if (empty($columnMap) || !isset($columnMap['item_code'])) {
                    Log::info("Preview: Skipping sheet '{$sheetName}': could not detect column mapping");
                    continue;
                }

                Log::info("Preview: Processing sheet '{$sheetName}'", [
                    'highest_row' => $highestRow,
                    'column_map' => $columnMap,
                ]);

                $this->processSheetPreview($sheet, $sheetName, $highestRow, $columnMap);
            }

            return $this->previewResults;

        } catch (\Exception $e) {
            Log::error('Annealing Import Preview failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->previewResults['errors'][] = 'Failed to process file: ' . $e->getMessage();
            return $this->previewResults;
        }
    }

    /**
     * Execute import - Phase 2: Create/update records based on user choice
     */
    public function execute(string $filePath, bool $updateDuplicates = false): array
    {
        try {
            $reader = IOFactory::createReaderForFile($filePath);
            $spreadsheet = $reader->load($filePath);

            Log::info('Annealing Import Execute - Excel file loaded', [
                'total_sheets' => $spreadsheet->getSheetCount(),
                'update_duplicates' => $updateDuplicates,
            ]);

            foreach ($spreadsheet->getSheetNames() as $index => $sheetName) {
                $sheet = $spreadsheet->getSheet($index);
                $highestRow = $sheet->getHighestRow();

                // Skip sheets with very few rows (no data)
                if ($highestRow < 10) {
                    continue;
                }

                // Detect column mapping from header rows 8 and 9
                $columnMap = $this->detectColumnMapping($sheet);

                if (empty($columnMap) || !isset($columnMap['item_code'])) {
                    continue;
                }

                $this->processSheetExecute($sheet, $sheetName, $highestRow, $columnMap, $updateDuplicates);
            }

            return $this->executeResults;

        } catch (\Exception $e) {
            Log::error('Annealing Import Execute failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->executeResults['errors'][] = 'Failed to process file: ' . $e->getMessage();
            return $this->executeResults;
        }
    }

    /**
     * Process a sheet for preview (detect new/duplicate records)
     */
    private function processSheetPreview($sheet, string $sheetName, int $highestRow, array $columnMap): void
    {
        // Data starts at row 10
        for ($rowNum = 10; $rowNum <= $highestRow; $rowNum++) {
            // Read item code first to check if row has data
            $itemCode = trim((string) $sheet->getCell($columnMap['item_code'] . $rowNum)->getValue());

            // Skip empty rows or non-data rows
            if (empty($itemCode) || preg_match('/^HPI-PR/i', $itemCode) || preg_match('/NOTE/i', $itemCode)) {
                continue;
            }

            // Must look like a real item code (letters, numbers, hyphens)
            if (!preg_match('/^[A-Z]{2}[\dA-Z]/', $itemCode)) {
                continue;
            }

            try {
                $data = $this->extractRowData($sheet, $rowNum, $columnMap, $sheetName);
                $previewRecord = $this->buildPreviewRecord($data);

                $this->previewResults['total_parsed']++;

                // Check for duplicate (by item_code + annealing_date)
                $existing = $this->findExistingRecord($data);

                if ($existing) {
                    $this->previewResults['duplicate_records'][] = [
                        'existing_id' => $existing->id,
                        'existing_data' => [
                            'item_code' => $existing->item_code,
                            'annealing_date' => $existing->annealing_date ? $existing->annealing_date->format('Y-m-d') : null,
                            'receiving_date' => $existing->receiving_date ? $existing->receiving_date->format('Y-m-d') : null,
                            'supplier_lot_number' => $existing->supplier_lot_number,
                            'quantity' => $existing->quantity,
                            'machine_number' => $existing->machine_number,
                            'temperature_setting' => $existing->temperature_setting,
                        ],
                        'new_data' => $previewRecord,
                    ];
                } else {
                    $this->previewResults['new_records'][] = $previewRecord;
                }

            } catch (\Exception $e) {
                $this->previewResults['errors'][] = "Sheet '{$sheetName}' Row {$rowNum}: " . $e->getMessage();
                Log::error("Preview error on '{$sheetName}' row {$rowNum}: " . $e->getMessage());
            }
        }

        Log::info("Preview: Sheet '{$sheetName}' complete", [
            'new_records' => count($this->previewResults['new_records']),
            'duplicates' => count($this->previewResults['duplicate_records']),
            'total_parsed' => $this->previewResults['total_parsed'],
        ]);
    }

    /**
     * Process a sheet for execute (create/update records)
     */
    private function processSheetExecute($sheet, string $sheetName, int $highestRow, array $columnMap, bool $updateDuplicates): void
    {
        // Data starts at row 10
        for ($rowNum = 10; $rowNum <= $highestRow; $rowNum++) {
            // Read item code first to check if row has data
            $itemCode = trim((string) $sheet->getCell($columnMap['item_code'] . $rowNum)->getValue());

            // Skip empty rows or non-data rows
            if (empty($itemCode) || preg_match('/^HPI-PR/i', $itemCode) || preg_match('/NOTE/i', $itemCode)) {
                continue;
            }

            // Must look like a real item code (letters, numbers, hyphens)
            if (!preg_match('/^[A-Z]{2}[\dA-Z]/', $itemCode)) {
                continue;
            }

            try {
                $data = $this->extractRowData($sheet, $rowNum, $columnMap, $sheetName);

                // Check for duplicate (by item_code + annealing_date)
                $existing = $this->findExistingRecord($data);

                if ($existing) {
                    if ($updateDuplicates) {
                        $existing->update($data);
                        $this->executeResults['updated']++;
                        Log::info("Updated: {$data['item_code']} from '{$sheetName}' row {$rowNum}");
                    } else {
                        $this->executeResults['skipped']++;
                    }
                } else {
                    AnnealingCheck::create($data);
                    $this->executeResults['imported']++;
                    Log::info("Imported: {$data['item_code']} from '{$sheetName}' row {$rowNum}");
                }

            } catch (\Exception $e) {
                $this->executeResults['errors'][] = "Sheet '{$sheetName}' Row {$rowNum}: " . $e->getMessage();
                Log::error("Execute error on '{$sheetName}' row {$rowNum}: " . $e->getMessage());
            }
        }

        Log::info("Execute: Sheet '{$sheetName}' complete", [
            'imported' => $this->executeResults['imported'],
            'updated' => $this->executeResults['updated'],
            'skipped' => $this->executeResults['skipped'],
            'errors' => count($this->executeResults['errors']),
        ]);
    }

    /**
     * Build a preview record from extracted data (for display in UI)
     */
    private function buildPreviewRecord(array $data): array
    {
        return [
            'item_code' => $data['item_code'],
            'annealing_date' => $data['annealing_date'],
            'receiving_date' => $data['receiving_date'],
            'supplier_lot_number' => $data['supplier_lot_number'],
            'quantity' => $data['quantity'],
            'machine_number' => $data['machine_number'],
            'temperature_setting' => $data['temperature_setting'],
            'annealing_time' => $data['annealing_time'],
            'damper_setting' => $data['damper_setting'],
            'time_in' => $data['time_in'],
            'time_out' => $data['time_out'],
            'remarks' => $data['remarks'],
        ];
    }

    /**
     * Find existing record by duplicate key (item_code + annealing_date)
     */
    private function findExistingRecord(array $data): ?AnnealingCheck
    {
        return AnnealingCheck::where('item_code', $data['item_code'])
            ->whereDate('annealing_date', $data['annealing_date'])
            ->first();
    }

    /**
     * Get preview results
     */
    public function getPreviewResults(): array
    {
        return $this->previewResults;
    }

    /**
     * Get execute results
     */
    public function getExecuteResults(): array
    {
        return $this->executeResults;
    }
}
