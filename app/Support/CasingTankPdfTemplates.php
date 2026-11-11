<?php

namespace App\Support;

use App\Models\WeldingChecksheetType;
use App\Models\WeldingItemConfig;

class CasingTankPdfTemplates
{
    /** Five-sample reference workbooks only. The ten-sample CSB641001P is deferred. */
    private const REFERENCES = [
        'CSB290053P' => 'HPI-PR20',
        'CSB290054P' => 'HPI-PR20',
        'CSB2900560-P' => 'HPI-PR20',
        'CSB290057P' => 'JANUARY 2026',
        'CSB2900580-P' => 'HPI-PR20',
        'CSB2900590-P' => 'HPI-PR20 (2)',
        'CSB2900610-P' => 'JANUARY 2026',
        'CSB2900620-P' => 'HPI-PR20',
        'CSB29043P1-P' => 'HPI-PR20',
        'CSB29044P1-P' => 'HPI-PR20',
        'CSB2904511-P' => 'JANUARY 2026',
        'CSB29045P1-P' => 'HPI-PR20',
        'CSB29046P1-P' => 'HPI-PR20',
        'CSB29046P2-P' => 'HPI-PR20',
        'CSB29046P3' => 'HPI-PR20',
        'CSB29052P1' => 'HPI-PR20',
        'CSB29052P2' => 'HPI-PR20',
        'CSB29052P3' => 'HPI-PR20',
        'CSB29053P3' => 'HPI-PR20',
    ];

    /** Additional item codes explicitly mapped to existing printed forms. */
    private const TEMPLATE_MAPPINGS = [
        'CSB29046P3-P' => 'CSB29046P3',
        'CSB6401012' => 'CSB290053P',
    ];

    public function itemCodes(): array
    {
        $codes = array_merge(array_keys(self::REFERENCES), array_keys(self::TEMPLATE_MAPPINGS));
        sort($codes);

        return $codes;
    }

    public function supports(?string $itemCode): bool
    {
        return in_array($itemCode, $this->itemCodes(), true);
    }

    /** @param iterable<WeldingItemConfig> $itemConfigs */
    public function optionsFor(iterable $itemConfigs): array
    {
        return collect($itemConfigs)->groupBy('item_code')->map(function ($configs, string $code): array {
            $isCasingTank = $configs->contains(function (WeldingItemConfig $config): bool {
                $type = $config->type;

                return $type instanceof WeldingChecksheetType && $type->key === 'casing_tank';
            });
            $reason = match (true) {
                ! $isCasingTank => 'Diaphragm deferred',
                $code === 'CSB641001P' => 'Ten-sample form deferred',
                ! $this->supports($code) => 'Reference form needed',
                default => null,
            };

            return ['value' => $code, 'supported' => $reason === null, 'reason' => $reason];
        })->values()->all();
    }

    public function forItem(string $itemCode): array
    {
        $reference = self::TEMPLATE_MAPPINGS[$itemCode] ?? $itemCode;
        if (! isset(self::REFERENCES[$reference])) {
            throw new \InvalidArgumentException('This item has no approved five-sample PDF reference form.');
        }

        $recordingOnly = in_array($reference, ['CSB2900620-P', 'CSB29043P1-P'], true);

        return [
            'source_file' => $reference.'.xlsx',
            'source_sheet' => self::REFERENCES[$reference],
            'depth_label' => $recordingOnly ? 'DEPTH (mm)' : 'DEPTH ≥ 0.37(mm)',
            'time_label' => $recordingOnly ? 'TIME (sec.)' : 'TIME 1.3~1.7(s)',
            // These keys follow the existing five-sample import column mapping.
            'materials' => ['casing' => 'TANK', 'valve_cover' => 'CD PARTITION', 'air_valve' => 'VCR'],
        ];
    }
}
