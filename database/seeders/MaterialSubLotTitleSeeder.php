<?php

namespace Database\Seeders;

use App\Models\MaterialSubLotTitle;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\Console\Output\ConsoleOutput;

class MaterialSubLotTitleSeeder extends Seeder
{
    public function run()
    {
        $output = new ConsoleOutput();
        $referencePath = storage_path('app/reference-excels');

        if (!is_dir($referencePath)) {
            $output->writeln("<error>Reference directory not found: {$referencePath}</error>");
            $output->writeln("<info>Please create the directory and place Excel files (ALARM.xlsx, FRAME.xlsx, etc.) there.</info>");
            return;
        }

        $files = glob($referencePath . '/*.xlsx');
        if (empty($files)) {
            $output->writeln("<error>No .xlsx files found in {$referencePath}</error>");
            return;
        }

        foreach ($files as $file) {
            $filename = basename($file, '.xlsx');
            $output->writeln("<info>Processing file: {$filename}.xlsx</info>");

            try {
                $this->processExcelFile($file, $filename, $output);
            } catch (\Exception $e) {
                $output->writeln("<error>Error processing {$filename}.xlsx: {$e->getMessage()}</error>");
            }
        }

        $output->writeln('<info>MaterialSubLotTitleSeeder completed.</info>');
    }

    protected function processExcelFile(string $filePath, string $materialType, ConsoleOutput $output)
    {
        // Load only the first worksheet with read-only mode to minimize memory usage
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet()->toArray();

        if (empty($sheet)) {
            $output->writeln("<comment>Skipping {$materialType}: empty worksheet</comment>");
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $sheet, $reader);
            gc_collect_cycles();
            return;
        }

        // Find header row (first non-empty row)
        $headerRow = $this->findHeaderRow($sheet);
        if ($headerRow === null) {
            $output->writeln("<comment>Skipping {$materialType}: no header row found</comment>");
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $sheet, $reader);
            gc_collect_cycles();
            return;
        }

        $headers = $sheet[$headerRow];
        $output->writeln("<comment>Header row found at index {$headerRow}</comment>");

        // Locate key columns
        $lotNumberCol = $this->findColumnIndex($headers, 'Lot Number');
        $letterCodeCol = $this->findColumnIndex($headers, 'Letter Code');
        $qtyCol = $this->findColumnIndex($headers, 'QTY');

        if ($lotNumberCol === null) {
            $output->writeln("<error>Skipping {$materialType}: 'Lot Number' column not found</error>");
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $sheet, $reader);
            gc_collect_cycles();
            return;
        }

        // Determine range: between Letter Code (or start) and QTY (or end)
        $startCol = $letterCodeCol !== null ? $letterCodeCol + 1 : 0;
        $endCol = $qtyCol !== null ? $qtyCol : count($headers);

        // Extract sub-headers under Lot Number (next row if present)
        $subHeaders = $this->extractSubHeaders($sheet, $headerRow, $lotNumberCol, $startCol, $endCol, $output);

        if (empty($subHeaders)) {
            $output->writeln("<comment>No sub-headers found under 'Lot Number' for {$materialType}</comment>");
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $sheet, $reader);
            gc_collect_cycles();
            return;
        }

        // Clear existing titles for this material type to ensure idempotency
        MaterialSubLotTitle::where('material_type', $materialType)->delete();

        // Insert titles with sort_order
        foreach ($subHeaders as $index => $title) {
            MaterialSubLotTitle::create([
                'material_type' => $materialType,
                'title' => $title,
                'sort_order' => $index + 1,
            ]);
        }

        $output->writeln("<info>Inserted " . count($subHeaders) . " sub-lot titles for {$materialType}</info>");

        // Explicitly free memory to prevent exhaustion on large files
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet, $sheet, $reader);
        gc_collect_cycles();
    }

    protected function findHeaderRow(array $sheet): ?int
    {
        foreach ($sheet as $index => $row) {
            // Find the first row containing 'Lot Number'
            foreach ($row as $cell) {
                if (is_string($cell) && stripos($cell, 'Lot Number') !== false) {
                    return $index;
                }
            }
        }
        return null;
    }

    protected function findColumnIndex(array $row, string $search): ?int
    {
        foreach ($row as $index => $cell) {
            if (is_string($cell) && stripos($cell, $search) !== false) {
                return $index;
            }
        }
        return null;
    }

    protected function extractSubHeaders(array $sheet, int $headerRow, int $lotNumberCol, int $startCol, int $endCol, ConsoleOutput $output): array
    {
        $subHeaders = [];

        // Check next row for sub-headers under 'Lot Number'
        $subHeaderRow = $sheet[$headerRow + 1] ?? null;
        if (!$subHeaderRow) {
            return $subHeaders;
        }

        // Extract from startCol to endCol-1, excluding empty cells
        for ($col = $startCol; $col < $endCol; $col++) {
            $cell = $subHeaderRow[$col] ?? null;
            if (!empty($cell) && is_string($cell)) {
                $subHeaders[] = trim($cell);
            }
        }

        if (empty($subHeaders)) {
            $output->writeln("<comment>No sub-headers found between columns {$startCol} and " . ($endCol - 1) . "</comment>");
        }

        return $subHeaders;
    }
}
