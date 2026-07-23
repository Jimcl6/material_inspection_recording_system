<?php

namespace App\Imports;

use App\Models\User;
use App\Models\WeldingChecksheet;
use App\Models\WeldingChecksheetType;
use App\Models\WeldingItemConfig;
use App\Services\ApprovalNotificationService;
use App\Services\ApprovalWorkflowService;
use App\Support\SpreadsheetImportSecurity;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class WeldingChecksheetImport
{
    public function __construct(protected ?string $sourceFileName = null)
    {
    }

    protected array $previewResults = [
        'new_records' => [],
        'duplicate_records' => [],
        'total_parsed' => 0,
        'errors' => [],
    ];

    protected array $executeResults = [
        'imported' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    public function preview(string $filePath, WeldingChecksheetType $type, ?WeldingItemConfig $itemConfig, ?string $itemCode, ?string $itemName): array
    {
        try {
            $this->processWorkbook($filePath, $type, $itemConfig, $itemCode, $itemName, false, false);
        } catch (\Throwable $e) {
            $this->previewResults['errors'][] = SpreadsheetImportSecurity::safeFailure(
                'welding.importer.preview',
                $e,
                'The spreadsheet could not be previewed.'
            );
        }

        return $this->previewResults;
    }

    public function execute(string $filePath, WeldingChecksheetType $type, ?WeldingItemConfig $itemConfig, ?string $itemCode, ?string $itemName, bool $updateDuplicates = false): array
    {
        try {
            $this->processWorkbook($filePath, $type, $itemConfig, $itemCode, $itemName, true, $updateDuplicates);
        } catch (\Throwable $e) {
            $this->executeResults['errors'][] = SpreadsheetImportSecurity::safeFailure(
                'welding.importer.execute',
                $e,
                'The spreadsheet could not be imported.'
            );
        }

        return $this->executeResults;
    }

    protected function processWorkbook(string $filePath, WeldingChecksheetType $type, ?WeldingItemConfig $itemConfig, ?string $itemCode, ?string $itemName, bool $execute, bool $updateDuplicates): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $sourceFile = $this->sourceFileName ?: basename($filePath);

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $lowerName = strtolower($sheetName);
            if (str_contains($lowerName, 'master') || str_contains($lowerName, 'template') || str_contains($lowerName, 'item code')) {
                continue;
            }

            $sheet = $spreadsheet->getSheetByName($sheetName);
            $this->processSheet($sheet, $sheetName, $sourceFile, $type, $itemConfig, $itemCode, $itemName, $execute, $updateDuplicates);
        }
    }

    protected function processSheet($sheet, string $sheetName, string $sourceFile, WeldingChecksheetType $type, ?WeldingItemConfig $itemConfig, ?string $itemCode, ?string $itemName, bool $execute, bool $updateDuplicates): void
    {
        $config = $type->import_config ?? [];
        $startRow = (int) ($config['data_start_row'] ?? 10);
        $recordSpan = (int) ($config['record_span'] ?? ($type->key === 'diaphragm' ? 10 : 5));
        $highestRow = $sheet->getHighestRow();
        $currentRow = $startRow;

        while ($currentRow <= $highestRow) {
            $dateValue = $sheet->getCell('A'.$currentRow)->getValue();
            if (empty($dateValue)) {
                $currentRow++;

                continue;
            }

            try {
                $record = $this->parseRecord($sheet, $currentRow, $type, $itemConfig, $itemCode, $itemName, $sheetName, $sourceFile);
                if (! $record) {
                    $currentRow += $recordSpan;

                    continue;
                }

                $existing = $this->findExistingRecord($record);

                if ($execute) {
                    $this->handleExecuteRecord($record, $existing, $updateDuplicates);
                } else {
                    $this->handlePreviewRecord($record, $existing);
                }
            } catch (\Throwable $e) {
                $message = SpreadsheetImportSecurity::safeFailure(
                    'welding.importer.row',
                    $e,
                    "Row {$currentRow} could not be processed."
                );
                if ($execute) {
                    $this->executeResults['errors'][] = $message;
                } else {
                    $this->previewResults['errors'][] = $message;
                }
            }

            $currentRow += $recordSpan;
        }
    }

    protected function parseRecord($sheet, int $startRow, WeldingChecksheetType $type, ?WeldingItemConfig $itemConfig, ?string $itemCode, ?string $itemName, string $sheetName, string $sourceFile): ?array
    {
        $productionDate = $this->parseDate($sheet->getCell('A'.$startRow)->getValue());
        if (! $productionDate) {
            return null;
        }

        if ($type->key === 'casing_tank') {
            return $this->parseCasingTankRecord($sheet, $startRow, $type, $itemConfig, $itemCode, $itemName, $productionDate, $sheetName, $sourceFile);
        }

        return $this->parseDiaphragmRecord($sheet, $startRow, $type, $itemConfig, $itemCode, $itemName, $productionDate, $sheetName, $sourceFile);
    }

    protected function parseCasingTankRecord($sheet, int $startRow, WeldingChecksheetType $type, ?WeldingItemConfig $itemConfig, ?string $itemCode, ?string $itemName, string $productionDate, string $sheetName, string $sourceFile): array
    {
        $record = $this->baseRecord($type, $itemConfig, $itemCode, $itemName, $productionDate, $sheetName, $sourceFile, $startRow);
        $record['machine_no'] = $this->getCellValue($sheet, 'I'.$startRow);
        $record['letter_code'] = $this->getCellValue($sheet, 'J'.$startRow);
        $record['prod_qty'] = $this->getNumericValue($sheet, 'N'.$startRow);
        $record['job_number'] = $this->getCellValue($sheet, 'O'.$startRow);
        $record['quantity'] = $this->getNumericValue($sheet, 'P'.$startRow);
        $record['temperature'] = $this->getDecimalValue($sheet, 'X'.$startRow);
        $record['operator_name_raw'] = $this->getCellValue($sheet, 'Y'.$startRow);
        $record['checked_by_name_raw'] = $this->getCellValue($sheet, 'Z'.$startRow);
        $record['remarks'] = $this->getCellValue($sheet, 'AA'.$startRow);
        $record['operator_id'] = $this->findUserIdByName($record['operator_name_raw']);
        $record['checked_by_id'] = $this->findUserIdByName($record['checked_by_name_raw']);
        $record['material_fields'] = [
            'tank' => $this->getCellValue($sheet, 'K'.$startRow),
            'cd_partition' => $this->getCellValue($sheet, 'L'.$startRow),
            'vcr' => $this->getCellValue($sheet, 'M'.$startRow),
        ];

        $record['samples'] = $this->parseSamples($sheet, $startRow, $type, ['S', 'T', 'U', 'V', 'W']);

        return $record;
    }

    protected function parseDiaphragmRecord($sheet, int $startRow, WeldingChecksheetType $type, ?WeldingItemConfig $itemConfig, ?string $itemCode, ?string $itemName, string $productionDate, string $sheetName, string $sourceFile): array
    {
        $record = $this->baseRecord($type, $itemConfig, $itemCode, $itemName, $productionDate, $sheetName, $sourceFile, $startRow);
        $record['machine_no'] = $this->getCellValue($sheet, 'I'.$startRow);
        $record['letter_code'] = $this->getCellValue($sheet, 'J'.$startRow);
        $record['prod_qty'] = $this->getNumericValue($sheet, 'N'.$startRow);
        $record['job_number'] = $this->getCellValue($sheet, 'O'.$startRow);
        $record['quantity'] = $this->getNumericValue($sheet, 'P'.$startRow);
        $record['temperature'] = $this->getDecimalValue($sheet, 'X'.$startRow);
        $record['operator_name_raw'] = $this->getCellValue($sheet, 'Y'.$startRow);
        $record['checked_by_name_raw'] = $this->getCellValue($sheet, 'Z'.$startRow);
        $record['remarks'] = $this->getCellValue($sheet, 'AA'.$startRow);
        $record['operator_id'] = $this->findUserIdByName($record['operator_name_raw']);
        $record['checked_by_id'] = $this->findUserIdByName($record['checked_by_name_raw']);
        $record['material_fields'] = [
            'lasermark_lot_number' => $this->getCellValue($sheet, 'I'.$startRow),
            'df_rubber_lot' => $this->getCellValue($sheet, 'M'.$startRow),
            'center_plate_a_lot' => $this->getCellValue($sheet, 'N'.$startRow),
            'center_plate_b_lot' => $this->getCellValue($sheet, 'O'.$startRow),
        ];

        $record['samples'] = $this->parseSamples($sheet, $startRow, $type, ['V', 'W', 'X', 'Y', 'Z']);

        return $record;
    }

    protected function baseRecord(WeldingChecksheetType $type, ?WeldingItemConfig $itemConfig, ?string $itemCode, ?string $itemName, string $productionDate, string $sheetName, string $sourceFile, int $sourceRow): array
    {
        $approvalState = app(ApprovalWorkflowService::class)->initialState();

        return [
            'checksheet_type_id' => $type->id,
            'item_config_id' => $itemConfig?->id,
            'item_code' => $itemConfig?->item_code ?? $itemCode,
            'item_name' => $itemName ?: $itemConfig?->item_name,
            'production_date' => $productionDate,
            'status' => $approvalState['status'],
            'submitted_at' => $approvalState['submitted_at'],
            'approved_at' => $approvalState['approved_at'],
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
            'source_file' => $sourceFile,
            'source_sheet' => $sheetName,
            'source_row' => $sourceRow,
        ];
    }

    protected function parseSamples($sheet, int $startRow, WeldingChecksheetType $type, array $sampleColumns): array
    {
        $samples = [];
        foreach ($type->check_items ?? [] as $index => $item) {
            $row = $startRow + $index;
            $values = [];
            foreach ($sampleColumns as $column) {
                $values[] = $this->getCellValue($sheet, $column.$row);
            }

            $samples[] = [
                'check_item_key' => $item['key'],
                'check_item_label' => $item['label'],
                'requirement_text' => $item['requirement_text'] ?? null,
                'sample_values' => $values,
                'sort_order' => $item['sort_order'] ?? ($index + 1),
            ];
        }

        return $samples;
    }

    protected function handlePreviewRecord(array $record, ?WeldingChecksheet $existing): void
    {
        $previewRecord = $this->previewRecord($record);
        $this->previewResults['total_parsed']++;

        if ($existing) {
            $this->previewResults['duplicate_records'][] = [
                'existing_id' => $existing->id,
                'existing_data' => $this->previewRecord($existing->toArray()),
                'new_data' => $previewRecord,
            ];

            return;
        }

        $this->previewResults['new_records'][] = $previewRecord;
    }

    protected function handleExecuteRecord(array $record, ?WeldingChecksheet $existing, bool $updateDuplicates): void
    {
        if ($existing) {
            if (! $updateDuplicates) {
                $this->executeResults['skipped']++;

                return;
            }

            $this->saveRecord($record, $existing);
            $this->executeResults['updated']++;

            return;
        }

        $this->saveRecord($record);
        $this->executeResults['imported']++;
    }

    protected function saveRecord(array $record, ?WeldingChecksheet $existing = null): WeldingChecksheet
    {
        $samples = $record['samples'];
        unset($record['samples']);

        $checksheet = $existing;
        if ($checksheet) {
            $record['updated_by'] = auth()->id();
            $checksheet->update($record);
            $checksheet->samples()->delete();
        } else {
            $checksheet = WeldingChecksheet::create($record);
            app(ApprovalNotificationService::class)->notifyApprovers($checksheet, 'new_submission', 'welding');
        }

        foreach ($samples as $sample) {
            $checksheet->samples()->create($sample);
        }

        return $checksheet;
    }

    protected function findExistingRecord(array $record): ?WeldingChecksheet
    {
        return WeldingChecksheet::where('checksheet_type_id', $record['checksheet_type_id'])
            ->where('item_code', $record['item_code'])
            ->whereDate('production_date', $record['production_date'])
            ->where('machine_no', $record['machine_no'])
            ->where('letter_code', $record['letter_code'])
            ->where('job_number', $record['job_number'])
            ->first();
    }

    protected function previewRecord(array $record): array
    {
        return [
            'checksheet_type_id' => $record['checksheet_type_id'] ?? null,
            'item_code' => $record['item_code'] ?? null,
            'production_date' => isset($record['production_date']) && $record['production_date'] instanceof \Carbon\Carbon
                ? $record['production_date']->format('Y-m-d')
                : ($record['production_date'] ?? null),
            'machine_no' => $record['machine_no'] ?? null,
            'letter_code' => $record['letter_code'] ?? null,
            'job_number' => $record['job_number'] ?? null,
            'prod_qty' => $record['prod_qty'] ?? null,
            'quantity' => $record['quantity'] ?? null,
            'material_fields' => $record['material_fields'] ?? [],
            'operator_name_raw' => $record['operator_name_raw'] ?? null,
            'checked_by_name_raw' => $record['checked_by_name_raw'] ?? null,
            'remarks' => $record['remarks'] ?? null,
        ];
    }

    protected function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        try {
            return (new \DateTime((string) $value))->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function getCellValue($sheet, string $cell): ?string
    {
        $value = $sheet->getCell($cell)->getFormattedValue();
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return trim((string) $value);
    }

    protected function getNumericValue($sheet, string $cell): ?int
    {
        $value = $sheet->getCell($cell)->getValue();
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    protected function getDecimalValue($sheet, string $cell): ?float
    {
        $value = $sheet->getCell($cell)->getValue();
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    protected function findUserIdByName(?string $name): ?int
    {
        if (! $name) {
            return null;
        }

        $cleanName = trim(strtolower($name));

        return User::whereRaw('LOWER(name) = ?', [$cleanName])->value('id');
    }
}
