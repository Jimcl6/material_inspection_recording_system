<?php

namespace App\Imports;

use App\Models\AnnealingCheck;
use App\Models\TemperatureReading;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class AnnealingChecksImport implements ToCollection, WithHeadingRow, WithValidation, WithMultipleSheets, SkipsEmptyRows
{
    private $importResults = [];
    private $currentUser;

    public function __construct()
    {
        $this->currentUser = auth()->user();
    }

    /**
     * Handle multiple sheets
     */
    public function sheets(): array
    {
        // Process all sheets in the Excel file
        return [
            0 => $this, // This will handle all sheets
        ];
    }

    /**
     * @param Collection $rows
     * @param string $sheetName
     */
    public function collection(Collection $rows, string $sheetName = null)
    {
        $sheetResults = [
            'sheet_name' => $sheetName ?? 'Unknown',
            'imported' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        foreach ($rows as $index => $row) {
            try {
                // Skip empty rows
                if (empty($row['item_code'])) {
                    $sheetResults['skipped']++;
                    continue;
                }

                // Parse the row data
                $data = $this->parseRow($row);
                
                // Create the annealing check
                $annealingCheck = AnnealingCheck::create($data);

                // Parse temperature readings if available
                if (isset($row['temperature_readings'])) {
                    $this->parseTemperatureReadings($annealingCheck, $row['temperature_readings']);
                }

                $sheetResults['imported']++;
                
                Log::info("Imported annealing check from sheet '{$sheetName}': {$annealingCheck->item_code}");
                
            } catch (\Exception $e) {
                $sheetResults['errors'][] = "Row " . ($index + 1) . ": " . $e->getMessage();
                Log::error("Import error on sheet '{$sheetName}', row " . ($index + 1) . ": " . $e->getMessage());
            }
        }

        $this->importResults[] = $sheetResults;
    }

    /**
     * Parse a single row of data
     */
    private function parseRow($row): array
    {
        return [
            'item_code' => $row['item_code'],
            'receiving_date' => $this->parseDate($row['receiving_date']),
            'supplier_lot_number' => $row['supplier_lot_number'] ?? null,
            'quantity' => $row['quantity'] ?? 0,
            'annealing_date' => $this->parseDate($row['annealing_date'] ?? now()),
            'machine_number' => $row['machine_number'] ?? null,
            'machine_setting' => $row['machine_setting'] ?? null,
            'temperature_setting' => $this->parseDecimal($row['temperature'] ?? null), // Column J
            'annealing_time' => $this->parseTime($row['annealing_time'] ?? null), // Column K
            'damper_setting' => $row['damper_setting'] ?? null, // Column L
            'time_in' => $this->parseTime($row['time_in'] ?? null), // Column M
            'time_out' => $this->parseTime($row['time_out'] ?? null), // Column N
            'pic_id' => $this->currentUser->id, // Auto-populate with logged-in user
            'checked_by_id' => $this->convertNameToId($row['checked_by'] ?? null),
            'verified_by_id' => $this->convertNameToId($row['verified_by'] ?? null),
            'remarks' => $row['remarks'] ?? null,
            'status' => 'approved', // Imported data is automatically approved
            'approved_at' => now(),
            'created_by' => $this->currentUser->id,
            'updated_by' => $this->currentUser->id,
        ];
    }

    /**
     * Parse decimal value
     */
    private function parseDecimal($value): ?float
    {
        if (empty($value)) return null;
        
        // Remove any non-numeric characters except decimal point
        $value = preg_replace('/[^0-9.]/', '', $value);
        
        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Parse time value
     */
    private function parseTime($value): ?string
    {
        if (empty($value)) return null;
        
        try {
            // Try to parse various time formats
            if (preg_match('/(\d{1,2}):(\d{2})/', $value, $matches)) {
                return sprintf('%02d:%02d:00', $matches[1], $matches[2]);
            }
            
            // If it's a decimal time (e.g., 8.5 = 8:30)
            if (is_numeric($value)) {
                $hours = floor($value);
                $minutes = round(($value - $hours) * 60);
                return sprintf('%02d:%02d:00', $hours, $minutes);
            }
            
            return null;
        } catch (\Exception $e) {
            return null;
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
                    'recorded_by' => $this->currentUser->id,
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
     * Convert user name to user ID
     */
    private function convertNameToId($name): ?int
    {
        if (empty($name)) {
            return null;
        }

        // If it's already a number, return as-is
        if (is_numeric($name)) {
            return (int) $name;
        }

        // Clean up the name
        $cleanName = trim(strtolower($name));
        
        // Try to find user by name (case-insensitive)
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
            '*.temperature' => 'nullable|numeric',
            '*.annealing_time' => 'nullable|string',
            '*.damper_setting' => 'nullable|string|max:100',
            '*.time_in' => 'nullable|string',
            '*.time_out' => 'nullable|string',
            '*.temperature_readings' => 'nullable|string',
        ];
    }
}
