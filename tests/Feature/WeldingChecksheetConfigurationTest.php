<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckModulePermission;
use App\Models\User;
use App\Models\WeldingChecksheetType;
use App\Models\WeldingItemConfig;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class WeldingChecksheetConfigurationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_repair_creates_missing_configuration_and_is_idempotent(): void
    {
        DB::table('welding_item_configs')->delete();
        DB::table('welding_checksheet_types')->delete();

        $this->runRepairMigration();
        $this->runRepairMigration();

        $this->assertSame(1, WeldingChecksheetType::where('key', 'diaphragm')->count());
        $this->assertSame(1, WeldingChecksheetType::where('key', 'casing_tank')->count());
        $this->assertDatabaseHas('welding_checksheet_types', ['key' => 'diaphragm', 'is_active' => true]);
        $this->assertDatabaseHas('welding_checksheet_types', ['key' => 'casing_tank', 'is_active' => true]);

        $casingTankId = WeldingChecksheetType::where('key', 'casing_tank')->value('id');
        $casingTank = WeldingChecksheetType::findOrFail($casingTankId);
        $this->assertEquals(
            [
                ['key' => 'air_valve', 'label' => 'Airvalve', 'type' => 'text'],
                ['key' => 'casing', 'label' => 'Casing', 'type' => 'text'],
                ['key' => 'valve_cover', 'label' => 'Valve Cover', 'type' => 'text'],
            ],
            $casingTank->material_fields
        );
        $this->assertEquals(
            ['air_valve' => 'M', 'casing' => 'K', 'valve_cover' => 'L'],
            $casingTank->import_config['material_columns']
        );
        $this->assertSame(22, WeldingItemConfig::where('checksheet_type_id', $casingTankId)->count());
        $this->assertDatabaseHas('welding_item_configs', [
            'checksheet_type_id' => $casingTankId,
            'item_code' => 'CSB29046P3',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('welding_item_configs', [
            'checksheet_type_id' => $casingTankId,
            'item_code' => 'CSB29046P3-P',
            'is_active' => true,
        ]);
        $this->assertEquals(
            [
                'collapse_depth_min' => 0.37,
                'collapse_time_min' => 1.3,
                'collapse_time_max' => 1.7,
            ],
            WeldingItemConfig::where('checksheet_type_id', $casingTankId)
                ->where('item_code', 'CSB290053P')
                ->firstOrFail()
                ->validation_rules
        );
        $this->assertEquals(
            [
                'collapse_depth_min' => null,
                'collapse_time_min' => null,
                'collapse_time_max' => null,
            ],
            WeldingItemConfig::where('checksheet_type_id', $casingTankId)
                ->where('item_code', 'CSB641001P')
                ->firstOrFail()
                ->validation_rules
        );

        $diaphragmRules = WeldingItemConfig::whereHas('type', fn ($query) => $query->where('key', 'diaphragm'))
            ->where('item_code', 'DFB4803000')
            ->firstOrFail()
            ->validation_rules;
        $this->assertSame('not_recorded', $diaphragmRules['measurement_1_type']);
    }

    public function test_repair_preserves_existing_customized_and_inactive_rows(): void
    {
        DB::table('welding_item_configs')->delete();
        DB::table('welding_checksheet_types')->delete();

        $type = WeldingChecksheetType::create([
            'key' => 'casing_tank',
            'name' => 'Custom Casing Configuration',
            'description' => 'Keep this configuration.',
            'material_fields' => [['key' => 'custom', 'label' => 'Custom', 'type' => 'text']],
            'check_items' => [['key' => 'custom_check', 'label' => 'Custom Check', 'sort_order' => 1]],
            'import_config' => ['format' => 'custom_format'],
            'is_active' => false,
        ]);
        $itemConfig = WeldingItemConfig::create([
            'checksheet_type_id' => $type->id,
            'item_code' => 'CSB29046P3',
            'item_name' => 'Custom Item',
            'validation_rules' => ['custom_rule' => 123],
            'is_active' => false,
        ]);

        $typeUpdatedAt = $type->getRawOriginal('updated_at');
        $itemUpdatedAt = $itemConfig->getRawOriginal('updated_at');

        $this->runRepairMigration();

        $type->refresh();
        $itemConfig->refresh();

        $this->assertSame('Custom Casing Configuration', $type->name);
        $this->assertSame(['format' => 'custom_format'], $type->import_config);
        $this->assertFalse($type->is_active);
        $this->assertSame($typeUpdatedAt, $type->getRawOriginal('updated_at'));
        $this->assertSame('Custom Item', $itemConfig->item_name);
        $this->assertSame(['custom_rule' => 123], $itemConfig->validation_rules);
        $this->assertFalse($itemConfig->is_active);
        $this->assertSame($itemUpdatedAt, $itemConfig->getRawOriginal('updated_at'));
    }

    public function test_casing_tank_material_field_rename_migration_updates_type_and_existing_records(): void
    {
        $this->runRepairMigration();

        $casingTank = WeldingChecksheetType::where('key', 'casing_tank')->firstOrFail();
        $casingTank->update([
            'material_fields' => [
                ['key' => 'tank', 'label' => 'Tank', 'type' => 'text'],
                ['key' => 'cd_partition', 'label' => 'CD Partition', 'type' => 'text'],
                ['key' => 'vcr', 'label' => 'VCR', 'type' => 'text'],
            ],
            'import_config' => [
                'data_start_row' => 10,
                'record_span' => 5,
                'material_columns' => ['tank' => 'K', 'cd_partition' => 'L', 'vcr' => 'M'],
                'sample_columns' => ['S', 'T', 'U', 'V', 'W'],
                'format' => 'casing_tank',
            ],
        ]);

        $now = now();
        $casingChecksheetId = DB::table('welding_checksheets')->insertGetId([
            'checksheet_type_id' => $casingTank->id,
            'item_code' => 'CSB-OLD-MATERIALS',
            'production_date' => '2026-08-14',
            'material_fields' => json_encode([
                'tank' => 'CASING-LOT',
                'cd_partition' => 'VALVE-COVER-LOT',
                'vcr' => 'AIR-VALVE-LOT',
                'extra' => 'KEEP-ME',
            ], JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $diaphragm = WeldingChecksheetType::where('key', 'diaphragm')->firstOrFail();
        $diaphragmChecksheetId = DB::table('welding_checksheets')->insertGetId([
            'checksheet_type_id' => $diaphragm->id,
            'item_code' => 'DFB-UNCHANGED',
            'production_date' => '2026-08-14',
            'material_fields' => json_encode(['vcr' => 'DO-NOT-TOUCH'], JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->runMaterialRenameMigration();
        $this->runMaterialRenameMigration();

        $casingTank->refresh();
        $this->assertEquals(
            [
                ['key' => 'air_valve', 'label' => 'Airvalve', 'type' => 'text'],
                ['key' => 'casing', 'label' => 'Casing', 'type' => 'text'],
                ['key' => 'valve_cover', 'label' => 'Valve Cover', 'type' => 'text'],
            ],
            $casingTank->material_fields
        );
        $this->assertEquals(
            ['air_valve' => 'M', 'casing' => 'K', 'valve_cover' => 'L'],
            $casingTank->import_config['material_columns']
        );

        $renamedFields = json_decode(
            DB::table('welding_checksheets')->where('id', $casingChecksheetId)->value('material_fields'),
            true,
            flags: JSON_THROW_ON_ERROR
        );

        $this->assertSame('AIR-VALVE-LOT', $renamedFields['air_valve']);
        $this->assertSame('CASING-LOT', $renamedFields['casing']);
        $this->assertSame('VALVE-COVER-LOT', $renamedFields['valve_cover']);
        $this->assertSame('KEEP-ME', $renamedFields['extra']);
        $this->assertArrayNotHasKey('vcr', $renamedFields);
        $this->assertArrayNotHasKey('tank', $renamedFields);
        $this->assertArrayNotHasKey('cd_partition', $renamedFields);

        $diaphragmFields = json_decode(
            DB::table('welding_checksheets')->where('id', $diaphragmChecksheetId)->value('material_fields'),
            true,
            flags: JSON_THROW_ON_ERROR
        );

        $this->assertSame(['vcr' => 'DO-NOT-TOUCH'], $diaphragmFields);
    }

    public function test_import_page_receives_both_active_canonical_types(): void
    {
        $this->runRepairMigration();

        $response = $this
            ->withoutMiddleware(CheckModulePermission::class)
            ->actingAs(User::factory()->create())
            ->get(route('welding-checksheets.import.form'));

        $response->assertOk();

        $types = collect($response->viewData('page')['props']['types']);
        $this->assertTrue($types->contains('key', 'diaphragm'));
        $this->assertTrue($types->contains('key', 'casing_tank'));
    }

    public function test_import_preview_supports_both_canonical_parsers(): void
    {
        $this->runRepairMigration();
        $this->withoutMiddleware(CheckModulePermission::class)
            ->actingAs(User::factory()->create());

        foreach (['diaphragm', 'casing_tank'] as $key) {
            $type = WeldingChecksheetType::where('key', $key)->firstOrFail();
            $response = $this->post(route('welding-checksheets.import.preview'), [
                'file' => $this->weldingWorkbookUpload($key),
                'checksheet_type_id' => $type->id,
                'item_code' => 'TEST-'.$key,
            ]);

            $response
                ->assertOk()
                ->assertJson(['success' => true]);

            if ($key === 'casing_tank') {
                $this->assertSame(
                    [
                        'air_valve' => 'AIR-VALVE-CELL',
                        'casing' => 'CASING-CELL',
                        'valve_cover' => 'VALVE-COVER-CELL',
                    ],
                    $response->json('preview.new_records.0.material_fields')
                );
            }

            $tempPath = session('welding_import.file');
            if ($tempPath) {
                Storage::disk('local')->delete($tempPath);
            }
            session()->forget('welding_import');
        }
    }

    public function test_import_validation_rejects_inactive_types_and_mismatched_item_configs(): void
    {
        $this->runRepairMigration();
        $this->withoutMiddleware(CheckModulePermission::class)
            ->actingAs(User::factory()->create());

        $inactiveType = WeldingChecksheetType::create([
            'key' => 'inactive_test_type',
            'name' => 'Inactive Test Type',
            'material_fields' => [],
            'check_items' => [],
            'import_config' => [],
            'is_active' => false,
        ]);

        $this->postJson(route('welding-checksheets.import.preview'), [
            'file' => UploadedFile::fake()->create('inactive.xlsx', 1, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            'checksheet_type_id' => $inactiveType->id,
        ])->assertStatus(422)->assertJsonValidationErrors('checksheet_type_id');

        $diaphragm = WeldingChecksheetType::where('key', 'diaphragm')->firstOrFail();
        $casingConfig = WeldingItemConfig::whereHas('type', fn ($query) => $query->where('key', 'casing_tank'))
            ->firstOrFail();

        $this->postJson(route('welding-checksheets.import.preview'), [
            'file' => UploadedFile::fake()->create('mismatched.xlsx', 1, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            'checksheet_type_id' => $diaphragm->id,
            'item_config_id' => $casingConfig->id,
        ])->assertStatus(422)->assertJsonValidationErrors('item_config_id');
    }

    public function test_import_execute_revalidates_configuration_and_discards_expired_upload(): void
    {
        $this->runRepairMigration();
        $this->withoutMiddleware(CheckModulePermission::class)
            ->actingAs(User::factory()->create());

        $type = WeldingChecksheetType::where('key', 'casing_tank')->firstOrFail();
        $tempPath = 'temp/imports/welding/'.Str::uuid().'.xlsx';
        Storage::disk('local')->put($tempPath, 'test workbook placeholder');

        session([
            'welding_import' => [
                'file' => $tempPath,
                'checksheet_type_id' => $type->id,
                'item_config_id' => null,
                'source_file' => 'configuration-test.xlsx',
            ],
        ]);
        $type->update(['is_active' => false]);

        try {
            $this->postJson(route('welding-checksheets.import.execute'), [])
                ->assertStatus(422)
                ->assertJson(['success' => false]);

            $this->assertNull(session('welding_import'));
            Storage::disk('local')->assertMissing($tempPath);
        } finally {
            Storage::disk('local')->delete($tempPath);
        }
    }

    private function runRepairMigration(): void
    {
        $migration = require database_path('migrations/2026_07_21_000001_restore_missing_welding_checksheet_configuration.php');
        $migration->up();

        $migration = require database_path('migrations/2026_08_11_000001_add_uploaded_casing_tank_item_configs.php');
        $migration->up();

        $migration = require database_path('migrations/2026_08_12_000001_add_csb29046p3_textbox_item_code.php');
        $migration->up();
    }

    private function runMaterialRenameMigration(): void
    {
        $migration = require database_path('migrations/2026_08_14_000001_rename_casing_tank_material_fields.php');
        $migration->up();
    }

    private function weldingWorkbookUpload(string $key): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Welding Data');
        $sheet->setCellValue('A10', '2026-07-21');
        $sheet->setCellValue('I10', 'WM-01');
        $sheet->setCellValue('J10', 'A');
        $sheet->setCellValue('N10', 100);
        $sheet->setCellValue('O10', 'JOB-'.$key);
        $sheet->setCellValue('P10', 100);

        if ($key === 'casing_tank') {
            $sheet->setCellValue('K10', 'CASING-CELL');
            $sheet->setCellValue('L10', 'VALVE-COVER-CELL');
            $sheet->setCellValue('M10', 'AIR-VALVE-CELL');
        }

        $path = tempnam(sys_get_temp_dir(), 'welding-import-').'.xlsx';
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);

        return new UploadedFile(
            $path,
            $key.'.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }
}
