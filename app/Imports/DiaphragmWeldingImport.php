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

    protected $currentUser;

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

    /**
     * Preview import - Phase 1: Parse file and detect duplicates
     */
    public function preview(string $filePath): array
    {
        $this->currentUser = auth()->user();
        
        try {
            $spreadsheet = IOFactory::load($filePath);

            Log::info('Diaphragm Welding Import Preview - Excel file loaded', [
                'total_sheets' => count($spreadsheet->getSheetNames()),
                'sheet_names' => $spreadsheet->getSheetNames(),
            ]);

            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                $lowerName = strtolower($sheetName);
                if (str_contains($lowerName, 'master') || str_contains($lowerName, 'template') || str_contains($lowerName, 'item code')) {
                    continue;
                }

                $sheet = $spreadsheet->getSheetByName($sheetName);
                $this->processSheetPreview($sheet, $sheetName);
            }

            return $this->previewResults;

        } catch (\Exception $e) {
            Log::error('Diaphragm Welding Import Preview failed', [
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
        $this->currentUser = auth()->user();
        
        try {
            $spreadsheet = IOFactory::load($filePath);

            Log::info('Diaphragm Welding Import Execute - Excel file loaded', [
                'total_sheets' => count($spreadsheet->getSheetNames()),
                'update_duplicates' => $updateDuplicates,
            ]);

            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                $lowerName = strtolower($sheetName);
                if (str_contains($lowerName, 'master') || str_contains($lowerName, 'template') || str_contains($lowerName, 'item code')) {
                    continue;
                }

                $sheet = $spreadsheet->getSheetByName($sheetName);
                $this->processSheetExecute($sheet, $sheetName, $updateDuplicates);
            }

            return $this->executeResults;

        } catch (\Exception $e) {
            Log::error('Diaphragm Welding Import Execute failed', [
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
    protected function processSheetPreview($sheet, string $sheetName): void
    {
        $highestRow = $sheet->getHighestRow();
        $currentRow = 10; // Data starts at row 10

        while ($currentRow <= $highestRow) {
            $dateValue = $sheet->getCell('A' . $currentRow)->getValue();
            
            if (empty($dateValue)) {
                $currentRow++;
                continue;
            }

            try {
                $record = $this->parseRecord($sheet, $currentRow);
                
                if ($record) {
                    $previewRecord = $this->buildPreviewRecord($record);
                    $this->previewResults['total_parsed']++;

                    // Check for duplicate (by production_date + lasermark_lot_number)
                    $existing = $this->findExistingRecord($record);

                    if ($existing) {
                        $this->previewResults['duplicate_records'][] = [
                            'existing_id' => $existing->id,
                            'existing_data' => [
                                'production_date' => $existing->production_date ? $existing->production_date->format('Y-m-d') : null,
                                'lasermark_lot_number' => $existing->lasermark_lot_number,
                                'machine_no' => $existing->machine_no,
                                'jo_number' => $existing->jo_number,
                                'prod_qty' => $existing->prod_qty,
                            ],
                            'new_data' => $previewRecord,
                        ];
                    } else {
                        $this->previewResults['new_records'][] = $previewRecord;
                    }
                }
                
                $currentRow += 10;
                
            } catch (\Exception $e) {
                $this->previewResults['errors'][] = "Sheet '{$sheetName}' Row {$currentRow}: " . $e->getMessage();
                Log::error("Preview error at row {$currentRow}", ['error' => $e->getMessage()]);
                $currentRow += 10;
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
    protected function processSheetExecute($sheet, string $sheetName, bool $updateDuplicates): void
    {
        $highestRow = $sheet->getHighestRow();
        $currentRow = 10; // Data starts at row 10

        while ($currentRow <= $highestRow) {
            $dateValue = $sheet->getCell('A' . $currentRow)->getValue();
            
            if (empty($dateValue)) {
                $currentRow++;
                continue;
            }

            try {
                $record = $this->parseRecord($sheet, $currentRow);
                
                if ($record) {
                    // Check for duplicate
                    $existing = $this->findExistingRecord($record);

                    if ($existing) {
                        if ($updateDuplicates) {
                            $this->updateChecksheet($existing, $record);
                            $this->executeResults['updated']++;
                            Log::info("Updated: {$record['production_date']} - {$record['lasermark_lot_number']} from '{$sheetName}'");
                        } else {
                            $this->executeResults['skipped']++;
                        }
                    } else {
                        $this->createChecksheet($record);
                        $this->executeResults['imported']++;
                        Log::info("Imported: {$record['production_date']} - {$record['lasermark_lot_number']} from '{$sheetName}'");
                    }
                }
                
                $currentRow += 10;
                
            } catch (\Exception $e) {
                $this->executeResults['errors'][] = "Sheet '{$sheetName}' Row {$currentRow}: " . $e->getMessage();
                Log::error("Execute error at row {$currentRow}", ['error' => $e->getMessage()]);
                $currentRow += 10;
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
     * Build a preview record from parsed data (for display in UI)
     */
    protected function buildPreviewRecord(array $record): array
    {
        return [
            'production_date' => $record['production_date'],
            'lasermark_lot_number' => $record['lasermark_lot_number'],
            'machine_no' => $record['machine_no'],
            'letter_code' => $record['letter_code'],
            'df_rubber_lot' => $record['df_rubber_lot'],
            'center_plate_a_lot' => $record['center_plate_a_lot'],
            'center_plate_b_lot' => $record['center_plate_b_lot'],
            'prod_qty' => $record['prod_qty'],
            'jo_number' => $record['jo_number'],
            'remarks' => $record['remarks'],
        ];
    }

    /**
     * Find existing record by duplicate key (production_date + lasermark_lot_number)
     */
    protected function findExistingRecord(array $record): ?DiaphragmWeldingChecksheet
    {
        return DiaphragmWeldingChecksheet::where('production_date', $record['production_date'])
            ->where('lasermark_lot_number', $record['lasermark_lot_number'])
            ->first();
    }

    /**
     * Update an existing checksheet with new data
     */
    protected function updateChecksheet(DiaphragmWeldingChecksheet $checksheet, array $record): DiaphragmWeldingChecksheet
    {
        $samples = $record['samples'];
        unset($record['samples']);
        
        // Remove name fields
        unset($record['operator_name'], $record['technician_name'], $record['checked_by_name']);

        $record['updated_by'] = $this->currentUser?->id;

        $checksheet->update($record);

        // Update samples - delete existing and recreate
        $checksheet->samples()->delete();
        foreach ($samples as $sample) {
            $checksheet->samples()->create($sample);
        }

        return $checksheet;
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
