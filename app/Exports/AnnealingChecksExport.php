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
            'Temperature Setting',
            'Annealing Time',
            'Damper Setting',
            'Time In',
            'Time Out',
            'PIC',
            'Checked By',
            'Verified By',
            'Temperature Readings',
            'Remarks',
            'Status',
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
            $annealingCheck->temperature_setting,
            $annealingCheck->annealing_time ? $annealingCheck->annealing_time->format('H:i') : '',
            $annealingCheck->damper_setting,
            $annealingCheck->time_in ? $annealingCheck->time_in->format('H:i') : '',
            $annealingCheck->time_out ? $annealingCheck->time_out->format('H:i') : '',
            $annealingCheck->pic->name ?? '',
            $annealingCheck->checkedBy->name ?? '',
            $annealingCheck->verifiedBy->name ?? '',
            $temperatureReadings,
            $annealingCheck->remarks,
            ucfirst($annealingCheck->status),
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
