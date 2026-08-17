<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckModulePermission;
use App\Models\User;
use App\Models\WeldingChecksheet;
use App\Models\WeldingChecksheetType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WeldingChecksheetDuplicateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_store_populates_first_letter_code_as_a(): void
    {
        $type = $this->createType();

        $this->withoutMiddleware(CheckModulePermission::class)
            ->actingAs(User::factory()->create())
            ->post(route('welding-checksheets.store'), $this->payload($type, [
                'letter_code' => '',
            ]))
            ->assertRedirect(route('welding-checksheets.index'));

        $this->assertDatabaseHas('welding_checksheets', [
            'checksheet_type_id' => $type->id,
            'item_code' => 'WELD-001',
            'production_date' => '2026-08-12',
            'machine_no' => 'M-01',
            'job_number' => 'JOB-100',
            'letter_code' => 'A',
        ]);
    }

    public function test_store_increments_letter_code_for_same_record_keys(): void
    {
        $type = $this->createType();
        $this->createChecksheet($type, ['letter_code' => 'A']);
        $this->createChecksheet($type, ['letter_code' => 'B']);

        $this->withoutMiddleware(CheckModulePermission::class)
            ->actingAs(User::factory()->create())
            ->post(route('welding-checksheets.store'), $this->payload($type, [
                'letter_code' => '',
            ]))
            ->assertRedirect(route('welding-checksheets.index'));

        $this->assertDatabaseHas('welding_checksheets', [
            'checksheet_type_id' => $type->id,
            'item_code' => 'WELD-001',
            'production_date' => '2026-08-12',
            'machine_no' => 'M-01',
            'job_number' => 'JOB-100',
            'letter_code' => 'C',
        ]);
    }

    public function test_next_letter_code_is_scoped_to_same_record_keys(): void
    {
        $type = $this->createType();
        $this->createChecksheet($type, ['letter_code' => 'A']);
        $this->createChecksheet($type, [
            'letter_code' => 'Z',
            'machine_no' => 'M-02',
        ]);

        $response = $this->withoutMiddleware(CheckModulePermission::class)
            ->actingAs(User::factory()->create())
            ->getJson(route('welding-checksheets.next-letter-code', [
                'checksheet_type_id' => $type->id,
                'item_code' => 'WELD-001',
                'production_date' => '2026-08-12',
                'machine_no' => 'M-01',
                'job_number' => 'JOB-100',
            ]));

        $response->assertOk()->assertJson([
            'letter_code' => 'B',
        ]);
    }

    public function test_duplicate_form_preserves_samples_and_assigns_next_letter_code(): void
    {
        $type = $this->createType();
        $source = $this->createChecksheet($type, ['letter_code' => 'A', 'prod_qty' => 100]);
        $source->samples()->create([
            'check_item_key' => 'appearance',
            'check_item_label' => 'Appearance',
            'requirement_text' => 'No visible defect',
            'sample_values' => ['P', '/', 'P', '', 'P'],
            'sort_order' => 0,
        ]);

        $response = $this->withoutMiddleware(CheckModulePermission::class)
            ->actingAs(User::factory()->create())
            ->get(route('welding-checksheets.duplicate', $source));

        $response->assertOk();

        $props = $response->viewData('page')['props'];
        $this->assertSame('duplicate', $props['formMode']);
        $this->assertSame($source->id, $props['sourceChecksheetId']);
        $this->assertSame('B', $props['checksheet']['letter_code']);
        $this->assertSame('next_letter', $props['duplicateSequenceMode']);
        $this->assertSame(100, $props['checksheet']['prod_qty']);
        $this->assertSame('JOB-100', $props['checksheet']['job_number']);
        $this->assertSame(['P', '/', 'P', '', 'P'], $props['checksheet']['samples'][0]['sample_values']);
    }

    public function test_duplicate_form_can_keep_same_letter_and_clear_new_run_details(): void
    {
        $type = $this->createType();
        $source = $this->createChecksheet($type, [
            'letter_code' => 'A',
            'job_number' => 'JOB-100',
            'prod_qty' => 100,
        ]);

        $response = $this->withoutMiddleware(CheckModulePermission::class)
            ->actingAs(User::factory()->create())
            ->get(route('welding-checksheets.duplicate', [
                'welding_checksheet' => $source,
                'sequence_mode' => 'same_letter_new_run',
            ]));

        $response->assertOk();

        $props = $response->viewData('page')['props'];
        $this->assertSame('duplicate', $props['formMode']);
        $this->assertSame('same_letter_new_run', $props['duplicateSequenceMode']);
        $this->assertSame($source->id, $props['sourceChecksheetId']);
        $this->assertSame('A', $props['checksheet']['letter_code']);
        $this->assertSame('', $props['checksheet']['job_number']);
        $this->assertNull($props['checksheet']['prod_qty']);
        $this->assertSame('JOB-100', $props['sourceJobNumber']);
        $this->assertSame(100, $props['sourceProdQty']);
        $this->assertSame('A', $props['sourceLetterCode']);
    }

    public function test_same_letter_new_run_store_reuses_letter_with_new_job_number_and_prod_qty(): void
    {
        $type = $this->createType();
        $source = $this->createChecksheet($type, [
            'letter_code' => 'A',
            'job_number' => 'JOB-100',
            'prod_qty' => 100,
        ]);

        $this->withoutMiddleware(CheckModulePermission::class)
            ->actingAs(User::factory()->create())
            ->post(route('welding-checksheets.store'), $this->payload($type, [
                'letter_code' => 'A',
                'job_number' => 'JOB-200',
                'prod_qty' => 150,
                'duplicate_sequence_mode' => 'same_letter_new_run',
                'source_checksheet_id' => $source->id,
            ]))
            ->assertRedirect(route('welding-checksheets.index'));

        $this->assertDatabaseHas('welding_checksheets', [
            'checksheet_type_id' => $type->id,
            'item_code' => 'WELD-001',
            'production_date' => '2026-08-12',
            'machine_no' => 'M-01',
            'letter_code' => 'A',
            'job_number' => 'JOB-200',
            'prod_qty' => 150,
        ]);
    }

    public function test_same_letter_new_run_store_requires_changed_job_number_and_prod_qty(): void
    {
        $type = $this->createType();
        $source = $this->createChecksheet($type, [
            'letter_code' => 'A',
            'job_number' => 'JOB-100',
            'prod_qty' => 100,
        ]);

        $this->withoutMiddleware(CheckModulePermission::class)
            ->actingAs(User::factory()->create())
            ->from(route('welding-checksheets.duplicate', [
                'welding_checksheet' => $source,
                'sequence_mode' => 'same_letter_new_run',
            ]))
            ->post(route('welding-checksheets.store'), $this->payload($type, [
                'letter_code' => 'A',
                'job_number' => 'JOB-100',
                'prod_qty' => 100,
                'duplicate_sequence_mode' => 'same_letter_new_run',
                'source_checksheet_id' => $source->id,
            ]))
            ->assertSessionHasErrors(['job_number', 'prod_qty']);
    }

    public function test_same_letter_new_run_store_requires_same_production_date(): void
    {
        $type = $this->createType();
        $source = $this->createChecksheet($type, [
            'letter_code' => 'A',
            'job_number' => 'JOB-100',
            'prod_qty' => 100,
        ]);

        $this->withoutMiddleware(CheckModulePermission::class)
            ->actingAs(User::factory()->create())
            ->from(route('welding-checksheets.duplicate', [
                'welding_checksheet' => $source,
                'sequence_mode' => 'same_letter_new_run',
            ]))
            ->post(route('welding-checksheets.store'), $this->payload($type, [
                'production_date' => '2026-08-13',
                'letter_code' => 'A',
                'job_number' => 'JOB-200',
                'prod_qty' => 150,
                'duplicate_sequence_mode' => 'same_letter_new_run',
                'source_checksheet_id' => $source->id,
            ]))
            ->assertSessionHasErrors(['production_date']);
    }

    private function createType(): WeldingChecksheetType
    {
        return WeldingChecksheetType::create([
            'key' => 'test_welding',
            'name' => 'Test Welding',
            'material_fields' => [
                ['key' => 'material_lot', 'label' => 'Material Lot'],
                ['key' => 'rubber_lot', 'label' => 'Rubber Lot'],
            ],
            'check_items' => [
                ['key' => 'appearance', 'label' => 'Appearance', 'requirement_text' => 'No visible defect'],
            ],
            'is_active' => true,
        ]);
    }

    private function payload(WeldingChecksheetType $type, array $overrides = []): array
    {
        return array_merge([
            'checksheet_type_id' => $type->id,
            'item_code' => 'WELD-001',
            'item_name' => 'Welding Part',
            'production_date' => '2026-08-12',
            'machine_no' => 'M-01',
            'letter_code' => null,
            'prod_qty' => 100,
            'job_number' => 'JOB-100',
            'material_fields' => [
                'material_lot' => 'MAT-1',
                'rubber_lot' => 'RUB-1',
            ],
            'samples' => [
                [
                    'check_item_key' => 'appearance',
                    'check_item_label' => 'Appearance',
                    'requirement_text' => 'No visible defect',
                    'sort_order' => 0,
                    'sample_values' => ['P', 'P', 'P', 'P', 'P'],
                ],
            ],
        ], $overrides);
    }

    private function createChecksheet(WeldingChecksheetType $type, array $overrides = []): WeldingChecksheet
    {
        return WeldingChecksheet::create(array_merge([
            'checksheet_type_id' => $type->id,
            'item_code' => 'WELD-001',
            'item_name' => 'Welding Part',
            'production_date' => '2026-08-12',
            'machine_no' => 'M-01',
            'letter_code' => 'A',
            'prod_qty' => 100,
            'job_number' => 'JOB-100',
            'material_fields' => [
                'material_lot' => 'MAT-1',
                'rubber_lot' => 'RUB-1',
            ],
            'status' => 'approved',
        ], $overrides));
    }
}
