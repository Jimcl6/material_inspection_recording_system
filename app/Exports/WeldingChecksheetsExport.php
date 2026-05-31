<?php

namespace App\Exports;

use App\Models\WeldingChecksheet;
use App\Models\WeldingChecksheetType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WeldingChecksheetsExport implements FromCollection, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
    protected Collection $materialFields;

    protected Collection $checkItems;

    public function __construct()
    {
        $types = WeldingChecksheetType::query()->orderBy('name')->get();

        $this->materialFields = $types
            ->flatMap(fn (WeldingChecksheetType $type) => $type->material_fields ?? [])
            ->unique('key')
            ->values();

        $this->checkItems = $types
            ->flatMap(fn (WeldingChecksheetType $type) => $type->check_items ?? [])
            ->unique('key')
            ->values();
    }

    public function collection()
    {
        return WeldingChecksheet::with(['type', 'itemConfig', 'samples', 'operator', 'technician', 'checkedBy'])
            ->orderByDesc('production_date')
            ->orderByDesc('id')
            ->get();
    }

    public function headings(): array
    {
        $headers = [
            'ID',
            'Type',
            'Item Code',
            'Item Name',
            'Production Date',
            'Month/Year',
            'Machine No.',
            'Letter Code',
            'Prod Qty',
            'Job Number',
            'Quantity',
            'Temperature',
            'Operator',
            'Operator Raw',
            'Technician',
            'Technician Raw',
            'Checked By',
            'Checked By Raw',
            'Status',
        ];

        foreach ($this->materialFields as $field) {
            $headers[] = $field['label'] ?? $field['key'];
        }

        foreach ($this->checkItems as $item) {
            $label = $item['label'] ?? $item['key'];
            for ($index = 1; $index <= 5; $index++) {
                $headers[] = "{$label} - Sample {$index}";
            }
        }

        return array_merge($headers, [
            'Remarks',
            'Submitted At',
            'Approved At',
            'Approval Notes',
            'Source File',
            'Source Sheet',
            'Source Row',
            'Created At',
        ]);
    }

    public function map($checksheet): array
    {
        $row = [
            $checksheet->id,
            $checksheet->type?->name,
            $checksheet->item_code,
            $checksheet->item_name,
            $checksheet->production_date?->format('Y-m-d'),
            $checksheet->month_year,
            $checksheet->machine_no,
            $checksheet->letter_code,
            $checksheet->prod_qty,
            $checksheet->job_number,
            $checksheet->quantity,
            $checksheet->temperature,
            $checksheet->operator?->name,
            $checksheet->operator_name_raw,
            $checksheet->technician?->name,
            $checksheet->technician_name_raw,
            $checksheet->checkedBy?->name,
            $checksheet->checked_by_name_raw,
            $checksheet->status,
        ];

        foreach ($this->materialFields as $field) {
            $row[] = $checksheet->getMaterialFieldValue($field['key']);
        }

        $samples = $checksheet->samples->keyBy('check_item_key');
        foreach ($this->checkItems as $item) {
            $sampleValues = $samples->get($item['key'])?->sample_values ?? [];
            for ($index = 0; $index < 5; $index++) {
                $row[] = $sampleValues[$index] ?? '';
            }
        }

        return array_merge($row, [
            $checksheet->remarks,
            $checksheet->submitted_at?->format('Y-m-d H:i:s'),
            $checksheet->approved_at?->format('Y-m-d H:i:s'),
            $checksheet->approval_notes,
            $checksheet->source_file,
            $checksheet->source_sheet,
            $checksheet->source_row,
            $checksheet->created_at?->format('Y-m-d H:i:s'),
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 28,
            'C' => 18,
            'D' => 24,
            'E' => 16,
            'F' => 16,
            'G' => 14,
            'H' => 14,
            'I' => 12,
            'J' => 18,
            'K' => 12,
            'L' => 14,
            'M' => 22,
            'N' => 22,
            'O' => 22,
            'P' => 22,
            'Q' => 22,
            'R' => 22,
            'S' => 12,
        ];
    }
}
