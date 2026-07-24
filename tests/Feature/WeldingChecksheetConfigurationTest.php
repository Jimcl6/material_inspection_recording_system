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
        $this->assertSame(3, WeldingItemConfig::where('checksheet_type_id', $casingTankId)->count());
        $this->assertDatabaseHas('welding_item_configs', [
            'checksheet_type_id' => $casingTankId,
            'item_code' => 'CSB29046P3',
            'is_active' => true,
        ]);

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
