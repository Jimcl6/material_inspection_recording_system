<?php

namespace Tests\Support;

use App\Models\WeldingChecksheet;
use App\Models\WeldingChecksheetSample;
use App\Models\WeldingChecksheetType;
use Illuminate\Database\Eloquent\Collection;

trait CasingTankPdfFixtures
{
    private function pdfRecord(int $id = 1, array $overrides = []): WeldingChecksheet
    {
        $record = new WeldingChecksheet;
        $record->forceFill(array_merge([
            'id' => $id,
            'item_code' => 'CSB290053P',
            'item_name' => 'EXAMPLE ITEM',
            'production_date' => '2026-11-10',
            'machine_no' => '3',
            'job_number' => 'DEMO-JO-001',
            'letter_code' => 'A',
            'prod_qty' => 300,
            'quantity' => 300,
            'temperature' => '26.50',
            'material_fields' => ['casing' => 'DEMO-T01', 'valve_cover' => 'DEMO-C01', 'air_valve' => 'DEMO-V01'],
            'operator_name_raw' => 'ANA SANTOS',
            'technician_name_raw' => 'PAOLO REYES',
            'checked_by_name_raw' => 'MARIA CRUZ',
            'status' => 'pending',
        ], $overrides));
        $record->setRelation('type', new WeldingChecksheetType(['key' => 'casing_tank']));
        foreach (['operator', 'technician', 'checkedBy'] as $relation) {
            $record->setRelation($relation, null);
        }
        $rows = [
            'collapse_depth' => ['0.385', '0.39', '0.4000', '', '0.412'],
            'collapse_time' => ['1.3', '1.40', '1.500', '', '1.6'],
            'airleak' => ['P', 'NG', '/', '', 'OK'],
            'appearance' => ['P', 'P', '/', '', 'P'],
            'welding_condition' => ['P', 'P', '/', '', 'P'],
        ];
        $samples = [];
        foreach ($rows as $key => $values) {
            $samples[] = new WeldingChecksheetSample(['check_item_key' => $key, 'sample_values' => $values]);
        }
        $record->setRelation('samples', new Collection($samples));

        return $record;
    }
}
