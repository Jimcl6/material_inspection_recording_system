<?php

namespace Tests\Feature;

use App\Exports\AnnealingChecksExport;
use App\Http\Requests\StoreAnnealingCheckRequest;
use App\Http\Requests\UpdateAnnealingCheckRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AnnealingCheckValidationTest extends TestCase
{
    public function test_store_validation_accepts_an_annealing_check_without_retired_fields(): void
    {
        $request = new StoreAnnealingCheckRequest();
        $rules = $request->rules();
        $validator = Validator::make($this->validPayload(), $rules);

        $this->assertTrue($validator->passes(), implode(' ', $validator->errors()->all()));
        $this->assertArrayNotHasKey('machine_setting', $rules);
        $this->assertArrayNotHasKey('temperature_readings', $rules);
        $this->assertArrayHasKey('temperature_setting', $rules);
    }

    public function test_update_validation_accepts_an_annealing_check_without_retired_fields(): void
    {
        $request = new UpdateAnnealingCheckRequest();
        $rules = $request->rules();
        $validator = Validator::make($this->validPayload(), $rules);

        $this->assertTrue($validator->passes(), implode(' ', $validator->errors()->all()));
        $this->assertArrayNotHasKey('machine_setting', $rules);
        $this->assertArrayNotHasKey('temperature_readings', $rules);
        $this->assertArrayHasKey('temperature_setting', $rules);
    }

    public function test_temperature_setting_remains_validated_and_exported(): void
    {
        $request = new StoreAnnealingCheckRequest();
        $payload = $this->validPayload();
        $payload['temperature_setting'] = 2500;

        $validator = Validator::make($payload, $request->rules());
        $headings = (new AnnealingChecksExport())->headings();

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('temperature_setting'));
        $this->assertContains('Temperature Setting', $headings);
        $this->assertNotContains('Machine Setting', $headings);
        $this->assertNotContains('Temperature Readings', $headings);
    }

    private function validPayload(): array
    {
        return [
            'item_code' => 'TEST-001',
            'receiving_date' => '2026-06-20',
            'supplier_lot_number' => 'LOT-001',
            'quantity' => 10,
            'annealing_date' => '2026-06-21',
            'machine_number' => 'AN-01',
            'temperature_setting' => 850,
            'annealing_time' => '03:00',
            'damper_setting' => '50%',
            'time_in' => '08:00',
            'time_out' => '11:00',
            'pic_id' => 'Test Operator',
            'checked_by_id' => 'Test Checker',
            'verified_by_id' => 'Test Verifier',
            'remarks' => 'Validation test',
        ];
    }
}
