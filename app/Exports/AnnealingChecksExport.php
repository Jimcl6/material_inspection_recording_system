<?php

namespace App\Exports;

use App\Models\AnnealingCheck;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class AnnealingChecksExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return AnnealingCheck::with(['pic', 'checkedBy', 'verifiedBy', 'temperatureReadings'])->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Item Code',
            'Receiving Date',
            'Supplier Lot #',
            'Quantity',
            'Annealing Date',
            'Machine #',
            'Machine Setting',
            'PIC',
            'Checked By',
            'Verified By',
            'Temperature Readings',
            'Remarks',
            'Created At',
            'Updated At',
        ];
    }

    /**
     * @param mixed $annealingCheck
     *
     * @return array
     */
    public function map($annealingCheck): array
    {
        $temperatureReadings = $annealingCheck->temperatureReadings
            ->map(function ($reading) {
                return sprintf('%s: %.2f°C', 
                    $reading->reading_time->format('H:i'), 
                    $reading->temperature
                );
            })
            ->implode(' | ');

        return [
            $annealingCheck->item_code,
            $annealingCheck->receiving_date->format('Y-m-d'),
            $annealingCheck->supplier_lot_number,
            $annealingCheck->quantity,
            $annealingCheck->annealing_date->format('Y-m-d'),
            $annealingCheck->machine_number,
            $annealingCheck->machine_setting,
            $annealingCheck->pic->name ?? '',
            $annealingCheck->checkedBy->name ?? '',
            $annealingCheck->verifiedBy->name ?? '',
            $temperatureReadings,
            $annealingCheck->remarks,
            $annealingCheck->created_at->format('Y-m-d H:i:s'),
            $annealingCheck->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Annealing Checks';
    }
}
