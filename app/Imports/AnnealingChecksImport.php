<?php

namespace App\Imports;

use App\Models\AnnealingCheck;
use App\Models\TemperatureReading;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AnnealingChecksImport implements ToCollection, WithHeadingRow, WithValidation
{
    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Skip empty rows
            if (empty($row['item_code'])) {
                continue;
            }

            $annealingCheck = AnnealingCheck::create([
                'item_code' => $row['item_code'],
                'receiving_date' => $this->parseDate($row['receiving_date']),
                'supplier_lot_number' => $row['supplier_lot_number'] ?? null,
                'quantity' => $row['quantity'] ?? 0,
                'annealing_date' => $this->parseDate($row['annealing_date'] ?? now()),
                'machine_number' => $row['machine_number'] ?? null,
                'machine_setting' => $row['machine_setting'] ?? null,
                'remarks' => $row['remarks'] ?? null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            // Parse temperature readings if available
            if (isset($row['temperature_readings'])) {
                $this->parseTemperatureReadings($annealingCheck, $row['temperature_readings']);
            }
        }
    }

    /**
     * Parse temperature readings from string
     */
    protected function parseTemperatureReadings(AnnealingCheck $annealingCheck, ?string $readings): void
    {
        if (empty($readings)) {
            return;
        }

        // Split readings by pipe (|) and process each one
        $readingPairs = explode('|', $readings);
        
        foreach ($readingPairs as $pair) {
            $pair = trim($pair);
            if (empty($pair)) continue;
            
            // Expected format: "HH:MM: 25.50°C"
            if (preg_match('/(\d{1,2}:\d{2})\s*:\s*([\d.]+)/', $pair, $matches)) {
                $annealingCheck->temperatureReadings()->create([
                    'reading_time' => $matches[1],
                    'temperature' => (float) $matches[2],
                    'recorded_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Parse date from various formats
     */
    protected function parseDate($date)
    {
        if ($date instanceof \DateTime) {
            return $date->format('Y-m-d');
        }

        if (is_numeric($date)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date))->toDateString();
        }

        try {
            return Carbon::parse($date)->toDateString();
        } catch (\Exception $e) {
            return now()->toDateString();
        }
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            '*.item_code' => 'required|string|max:50',
            '*.supplier_lot_number' => 'nullable|string|max:100',
            '*.quantity' => 'nullable|integer|min:0',
            '*.machine_number' => 'nullable|string|max:50',
            '*.machine_setting' => 'nullable|string|max:100',
            '*.temperature_readings' => 'nullable|string',
        ];
    }
}
