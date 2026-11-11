<?php

namespace App\Services;

use App\Models\User;
use App\Models\WeldingChecksheet;
use App\Models\WeldingChecksheetSample;
use App\Models\WeldingChecksheetType;
use App\Support\CasingTankPdfTemplates;
use Dompdf\Canvas;
use Dompdf\Dompdf;
use Dompdf\FontMetrics;
use Dompdf\Options;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CasingTankPdfExport
{
    private Canvas $canvas;

    private FontMetrics $metrics;

    private string $font;

    private string $bold;

    private array $xs = [];

    private string $recordContext = '';

    private const ROW_HEIGHT = 11.65;

    private const TABLE_TOP = 77.276;

    private const SAMPLE_KEYS = ['collapse_depth', 'collapse_time', 'airleak', 'appearance', 'welding_condition'];

    public function __construct(private CasingTankPdfTemplates $templates) {}

    /** @param Collection<int, WeldingChecksheet> $records */
    public function pages(Collection $records): array
    {
        $groups = [];
        foreach ($records->sortBy(fn (WeldingChecksheet $record) => [$record->production_date->format('Y-m-d'), $record->id]) as $record) {
            $type = $record->type;
            if (! $type instanceof WeldingChecksheetType || $type->key !== 'casing_tank' || ! $this->templates->supports($record->item_code)) {
                throw ValidationException::withMessages(['pdf' => 'PDF export currently supports the reference five-sample Casing-Tank items only.']);
            }

            // Material lots, quantities and incrementing letters stay within the same run.
            $key = json_encode([
                $record->item_code, $record->production_date->format('Y-m'),
                trim((string) $record->machine_no), trim((string) $record->job_number),
            ], JSON_THROW_ON_ERROR);
            $groups[$key][] = $record;
        }

        $pages = [];
        foreach ($groups as $group) {
            $page = [];
            foreach ($group as $record) {
                $startsNewRun = strtoupper(trim((string) $record->letter_code)) === 'A';
                if ($page !== [] && ($startsNewRun || count($page) === 6)) {
                    $pages[] = $page;
                    $page = [];
                }
                $page[] = $record;
            }
            $pages[] = $page;
        }

        return $pages;
    }

    /** @param Collection<int, WeldingChecksheet> $records */
    public function render(Collection $records): string
    {
        $pages = $this->pages($records);
        if ($pages === []) {
            throw ValidationException::withMessages(['pdf' => 'No Casing-Tank records were found for the selected item and month.']);
        }

        $options = new Options;
        $options->setIsRemoteEnabled(false);
        $options->setIsPhpEnabled(false);
        $options->setIsFontSubsettingEnabled(true);
        $options->setDefaultFont('DejaVu Sans');
        $options->setFontHeightRatio(1.0);
        $options->setFontCache(storage_path('framework/cache'));
        $pdf = new Dompdf($options);
        $pdf->setPaper('a4', 'landscape');
        $pdf->loadHtml('<html><head><style>@page { margin: 0; }</style></head><body></body></html>');
        $pdf->render();
        $this->canvas = $pdf->getCanvas();
        $this->metrics = $pdf->getFontMetrics();
        $this->font = $this->metrics->getFont('DejaVu Sans', 'normal');
        $this->bold = $this->metrics->getFont('DejaVu Sans', 'bold');
        $this->xs = [14.0];
        $widths = [13, 13, 24, 32, 23, 38, 38, 34, 27, 58, 34, 36, 51, 30, 30, 30, 30, 30, 24, 40, 40, 36, 55];
        $factor = ($this->canvas->get_width() - 28) / array_sum($widths);
        foreach ($widths as $width) {
            $this->xs[] = end($this->xs) + $width * $factor;
        }

        foreach ($pages as $index => $page) {
            if ($index > 0) {
                $this->canvas->new_page();
            }
            $this->drawPage($page, $index + 1, count($pages));
        }

        return $pdf->output();
    }

    private function drawPage(array $records, int $number, int $total): void
    {
        $record = $records[0];
        $this->recordContext = 'record '.$record->id;
        $template = $this->templates->forItem($record->item_code);
        $this->text(14, 21, 813.89, 23, 'CASING-TANK WELDING CHECKSHEET', 14, true);
        $this->text(14, 46, 600, 14, 'ITEM NAME: '.($record->item_name ?? ''), 8, false, 'left');
        $this->text(14, 59, 500, 14, 'ITEM CODE: '.$record->item_code, 8, false, 'left');
        $this->text(640, 59, 187.89, 14, 'MONTH/YEAR: '.strtoupper($record->production_date->format('F Y')), 8);
        $this->drawHeader($template);

        $bodyTop = self::TABLE_TOP + 58;
        for ($block = 0; $block < 6; $block++) {
            $this->drawBlock($records[$block] ?? null, $bodyTop + $block * 5 * self::ROW_HEIGHT, $template);
        }

        $bottom = $bodyTop + 30 * self::ROW_HEIGHT;
        $this->canvas->rectangle(14, self::TABLE_TOP, 813.89, $bottom - self::TABLE_TOP, [0, 0, 0], .7);
        $this->text(14, $bottom + 3, 120, 12, 'HPI-PR20-01', 6.7, false, 'left');
        $reminders = [
            'Reminders: Applicable for Casing/Tank',
            '1. No. 5 and 10 samples must be kept unless instructed to dispose.',
            "2. Dispose all samples when there's change in parameter setting.",
            '3. Make new set of samples for new setting of parameter.',
            '4. 1st 4 Samples of letter code "A" must be disposed even if judgement is OK.',
        ];
        foreach ($reminders as $index => $reminder) {
            $this->text(144, $bottom + 3 + $index * 10, 520, 11, $reminder, 6.7, false, 'left');
        }
        $this->canvas->line(681.89, $bottom + 38, 827.89, $bottom + 38, [0, 0, 0], .5);
        $this->text(681.89, $bottom + 40, 146, 13, 'Production Supervisor', 7);
        $this->text(741.89, 572, 86, 10, 'Page '.$number.' / '.$total, 6);
    }

    private function drawHeader(array $template): void
    {
        $t = self::TABLE_TOP;
        $this->box(0, 11, $t, 14, 'MATERIAL MONITORING', 7, true);
        $this->box(11, 18, $t, 14, 'WELDING DATA', 7, true);
        $this->box(0, 5, $t + 14, 17, 'HPI LOT NUMBER');
        $this->box(5, 9, $t + 14, 17, 'MATERIAL LOT NUMBER');
        $this->box(9, 10, $t + 14, 44, 'Job Number');
        $this->box(10, 11, $t + 14, 44, 'QUANTITY', 6.1, false, true);
        $this->box(11, 13, $t + 14, 44, 'CHECK ITEM');
        $this->box(13, 18, $t + 14, 17, 'Note: Put a ( / ) for not applicable item', 5.7);
        $this->box(13, 18, $t + 31, 15, 'Sample No. First/ Last N=5   Judgement [ ✓ ][ X ]', 5.3);
        $headers = ['DD', 'MM', 'YYYY', "MACHINE\nNO.", "LETTER\nCODE", ...array_values($template['materials'])];
        foreach ($headers as $column => $label) {
            $this->box($column, $column + 1, $t + 31, 27, $label, in_array($column, [0, 1, 4], true) ? 5.4 : 6);
        }
        $this->box(8, 9, $t + 31, 15, 'PROD', 6);
        $this->box(8, 9, $t + 46, 12, 'QTY.', 6);
        for ($sample = 0; $sample < 5; $sample++) {
            $this->box(13 + $sample, 14 + $sample, $t + 46, 12, (string) ($sample + 1), 7);
        }
        foreach ([18 => 'TEMP (°C)', 19 => 'OPERATOR NAME', 20 => 'TECHNICIAN', 21 => 'CHECKED BY', 22 => 'REMARKS'] as $column => $label) {
            $this->box($column, $column + 1, $t, 58, $label, 6.7, false, true);
        }
    }

    private function drawBlock(?WeldingChecksheet $record, float $top, array $template): void
    {
        $height = 5 * self::ROW_HEIGHT;
        $this->recordContext = $record ? 'record '.$record->id : 'blank block';
        $date = $record?->production_date;
        $values = [
            $date?->format('d'), $date?->format('m'), $date?->format('Y'),
            $record?->machine_no, $record?->letter_code,
        ];
        foreach (array_keys($template['materials']) as $key) {
            $values[] = $record?->getMaterialFieldValue($key);
        }
        $values[] = $record?->prod_qty;
        $values[] = $record?->job_number;
        foreach ($values as $column => $value) {
            $value = (string) ($value ?? '');
            $width = $this->xs[$column + 1] - $this->xs[$column];
            $size = $column === 9 ? 5.9 : 6.2;
            while ($size > 5 && count($this->wrap($value, $this->font, $size, $width - 4)) > 1) {
                $size -= .2;
            }
            if (count($this->wrap($value, $this->font, $size, $width - 4)) > 1) {
                $this->box($column, $column + 1, $top, $height, $value, $size);
            } else {
                for ($row = 0; $row < 5; $row++) {
                    $this->box($column, $column + 1, $top + $row * self::ROW_HEIGHT, self::ROW_HEIGHT, $row === 0 ? $value : '', $size);
                }
            }
        }
        $this->box(10, 11, $top, $height, (string) ($record->quantity ?? ''), 7);
        $this->box(11, 12, $top, self::ROW_HEIGHT * 2, 'COLLAPSE', 6);
        $this->box(12, 13, $top, self::ROW_HEIGHT, $template['depth_label'], 5.7);
        $this->box(12, 13, $top + self::ROW_HEIGHT, self::ROW_HEIGHT, $template['time_label'], 5.7);
        foreach ([2 => 'AIRLEAK', 3 => 'APPEARANCE', 4 => 'WELDING CONDITION'] as $row => $label) {
            $this->box(11, 13, $top + $row * self::ROW_HEIGHT, self::ROW_HEIGHT, $label, 6);
        }

        $samples = $record?->samples->keyBy('check_item_key') ?? collect();
        foreach (self::SAMPLE_KEYS as $row => $key) {
            $sample = $samples->get($key);
            $sampleValues = $sample instanceof WeldingChecksheetSample ? ($sample->sample_values ?? []) : [];
            for ($index = 0; $index < 5; $index++) {
                $value = (string) ($sampleValues[$index] ?? '');
                if ($row >= 2) {
                    $value = $this->judgement($value);
                }
                $this->box(13 + $index, 14 + $index, $top + $row * self::ROW_HEIGHT, self::ROW_HEIGHT, $value, 6.8);
            }
        }
        for ($row = 0; $row < 5; $row++) {
            $this->box(18, 19, $top + $row * self::ROW_HEIGHT, self::ROW_HEIGHT, $row === 0 ? (string) ($record->temperature ?? '') : '', 6.2);
        }
        foreach ([19 => 'operator', 20 => 'technician', 21 => 'checkedBy'] as $column => $relation) {
            $person = $record?->getRelationValue($relation);
            $raw = match ($relation) {
                'operator' => $record?->operator_name_raw,
                'technician' => $record?->technician_name_raw,
                default => $record?->checked_by_name_raw,
            };
            $this->box($column, $column + 1, $top, $height, (string) ($person instanceof User ? $person->name : $raw), 7);
        }
        $remarks = (string) ($record->remarks ?? '');
        if ($remarks !== '') {
            $this->box(22, 23, $top, $height, $remarks, 6.5);
        } else {
            for ($row = 0; $row < 5; $row++) {
                $this->box(22, 23, $top + $row * self::ROW_HEIGHT, self::ROW_HEIGHT);
            }
        }
    }

    public function judgement(string $value): string
    {
        return match (strtoupper(trim($value))) {
            'P', 'PASS', 'OK', '√', '✓', '✔' => '✓',
            'F', 'FAIL', 'NG', 'N.G.', 'N.G', 'X', '✗', '×' => 'X',
            '/', 'N/A', 'NA' => '/',
            default => $value,
        };
    }

    private function box(int $first, int $last, float $top, float $height, string $text = '', float $size = 6.3, bool $bold = false, bool $rotate = false): void
    {
        $width = $this->xs[$last] - $this->xs[$first];
        $this->canvas->rectangle($this->xs[$first], $top, $width, $height, [0, 0, 0], .35);
        $this->text($this->xs[$first], $top, $width, $height, $text, $size, $bold, 'center', $rotate);
    }

    private function text(float $x, float $y, float $width, float $height, string $text, float $size, bool $bold = false, string $align = 'center', bool $rotate = false): void
    {
        if ($text === '') {
            return;
        }
        $font = $bold ? $this->bold : $this->font;
        if ($rotate) {
            $this->canvas->save();
            $this->canvas->rotate(-90, $x + $width / 2, $y + $height / 2);
            $textWidth = $this->metrics->getTextWidth($text, $font, $size);
            $this->canvas->text($x + $width / 2 - $textWidth / 2, $y + $height / 2 - $this->metrics->getFontHeight($font, $size) / 2, $text, $font, $size);
            $this->canvas->restore();

            return;
        }
        do {
            $lines = $this->wrap($text, $font, $size, $width - 4);
            $lineHeight = $this->metrics->getFontHeight($font, $size);
            if (count($lines) * $lineHeight <= $height - 1) {
                break;
            }
            $size -= .25;
        } while ($size >= 4.5);
        if ($size < 4.5) {
            throw ValidationException::withMessages(['pdf' => 'Text in '.$this->recordContext.' is too long to fit the printed form. Please review the record before exporting.']);
        }
        $lineY = $y + ($height - count($lines) * $lineHeight) / 2;
        foreach ($lines as $line) {
            $lineWidth = $this->metrics->getTextWidth($line, $font, $size);
            $lineX = $align === 'left' ? $x + 2.5 : $x + ($width - $lineWidth) / 2;
            $this->canvas->text($lineX, $lineY, $line, $font, $size);
            $lineY += $lineHeight;
        }
    }

    private function wrap(string $text, string $font, float $size, float $width): array
    {
        $lines = [];
        foreach (preg_split('/\R/u', $text) ?: [''] as $paragraph) {
            $line = '';
            foreach (preg_split('/\s+/u', trim($paragraph)) ?: [] as $word) {
                $candidate = ltrim($line.' '.$word);
                if ($line !== '' && $this->metrics->getTextWidth($candidate, $font, $size) > $width) {
                    $lines[] = $line;
                    $line = '';
                }
                foreach (mb_str_split(($line === '' ? '' : ' ').$word) as $character) {
                    if ($line !== '' && $this->metrics->getTextWidth($line.$character, $font, $size) > $width) {
                        $lines[] = $line;
                        $line = '';
                    }
                    $line .= $character;
                }
            }
            $lines[] = $line;
        }

        return $lines;
    }
}
