<?php

namespace App\Exports;

use App\Models\DiaphragmWeldingChecksheet;
use App\Models\DiaphragmWeldingSample;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DiaphragmWeldingExport implements FromCollection, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
    /**
     * @return Collection
     */
    public function collection()
    {
        return DiaphragmWeldingChecksheet::with(['samples', 'operator', 'technician', 'checkedBy'])
            ->orderByDesc('production_date')
            ->get();
    }

    public function headings(): array
    {
        $headers = [
            'ID',
            'Production Date',
            'Lasermark Lot #',
            'Machine No.',
            'Letter Code',
            'DF Rubber Lot',
            'Center Plate A',
            'Center Plate B',
            'Prod Qty',
            'JO Number',
            'Temperature',
            'Operator',
            'Technician',
            'Checked By',
            'Status',
        ];

        // Add sample headers
        foreach (DiaphragmWeldingSample::CHECK_ITEM_LABELS as $key => $label) {
            for ($i = 1; $i <= 5; $i++) {
                $headers[] = "{$label} - Sample {$i}";
            }
        }

        $headers[] = 'Remarks';
        $headers[] = 'Created At';

        return $headers;
    }

    /**
     * @param  DiaphragmWeldingChecksheet  $checksheet
     */
    public function map($checksheet): array
    {
        $row = [
            $checksheet->id,
            $checksheet->production_date->format('Y-m-d'),
            $checksheet->lasermark_lot_number,
            $checksheet->machine_no,
            $checksheet->letter_code,
            $checksheet->df_rubber_lot,
            $checksheet->center_plate_a_lot,
            $checksheet->center_plate_b_lot,
            $checksheet->prod_qty,
            $checksheet->jo_number,
            $checksheet->temperature,
            $checksheet->operator?->name,
            $checksheet->technician?->name,
            $checksheet->checkedBy?->name,
            $checksheet->status,
        ];

        // Add sample values
        $samplesIndexed = $checksheet->samples->keyBy('check_item');

        foreach (array_keys(DiaphragmWeldingSample::CHECK_ITEM_LABELS) as $checkItem) {
            $sample = $samplesIndexed[$checkItem] ?? null;
            for ($i = 1; $i <= 5; $i++) {
                $row[] = $sample ? $sample->{"sample_{$i}"} : '';
            }
        }

        $row[] = $checksheet->remarks;
        $row[] = $checksheet->created_at?->format('Y-m-d H:i:s');

        return $row;
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
            'B' => 15,
            'C' => 20,
            'D' => 12,
            'E' => 12,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 10,
            'J' => 18,
            'K' => 12,
            'L' => 20,
            'M' => 20,
            'N' => 20,
            'O' => 12,
        ];
    }
}
