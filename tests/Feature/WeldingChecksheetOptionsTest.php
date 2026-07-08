<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckModulePermission;
use App\Models\User;
use App\Models\WeldingChecksheetType;
use App\Models\WeldingItemConfig;
use Database\Seeders\WeldingChecksheetTypeSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class WeldingChecksheetOptionsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_import_page_receives_checksheet_type_options_even_when_inactive(): void
    {
        $this->seed(WeldingChecksheetTypeSeeder::class);

        WeldingChecksheetType::where('key', 'casing_tank')->update(['is_active' => false]);
        WeldingItemConfig::whereIn('checksheet_type_id', WeldingChecksheetType::where('key', 'casing_tank')->pluck('id'))
            ->update(['is_active' => false]);

        $response = $this
            ->withoutMiddleware(CheckModulePermission::class)
            ->actingAs(User::factory()->create())
            ->get(route('welding-checksheets.import.form'));

        $response->assertOk();

        $props = $response->viewData('page')['props'];
        $types = collect($props['types']);

        $this->assertTrue($types->contains('key', 'diaphragm'));
        $this->assertTrue($types->contains('key', 'casing_tank'));
        $this->assertNotEmpty($types->firstWhere('key', 'casing_tank')['item_configs']);
    }

    public function test_import_preview_accepts_inactive_checksheet_type_options(): void
    {
        $this->seed(WeldingChecksheetTypeSeeder::class);

        $type = WeldingChecksheetType::where('key', 'casing_tank')->firstOrFail();
        $itemConfig = WeldingItemConfig::where('checksheet_type_id', $type->id)
            ->where('item_code', 'CSB29046P3')
            ->firstOrFail();

        $type->update(['is_active' => false]);
        $itemConfig->update(['is_active' => false]);

        $response = $this
            ->withoutMiddleware(CheckModulePermission::class)
            ->actingAs(User::factory()->create())
            ->post(route('welding-checksheets.import.preview'), [
                'file' => $this->weldingWorkbookUpload(),
                'checksheet_type_id' => $type->id,
                'item_config_id' => $itemConfig->id,
            ]);

        $response
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_welding_type_seeder_repairs_missing_active_dropdown_options(): void
    {
        $this->seed(WeldingChecksheetTypeSeeder::class);

        WeldingChecksheetType::whereIn('key', ['diaphragm', 'casing_tank'])->update(['is_active' => false]);
        WeldingItemConfig::whereIn('checksheet_type_id', WeldingChecksheetType::whereIn('key', ['diaphragm', 'casing_tank'])->pluck('id'))
            ->update(['is_active' => false]);

        $this->seed(WeldingChecksheetTypeSeeder::class);

        $this->assertDatabaseHas('welding_checksheet_types', [
            'key' => 'diaphragm',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('welding_checksheet_types', [
            'key' => 'casing_tank',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('welding_item_configs', [
            'item_code' => 'CSB29046P3',
            'is_active' => true,
        ]);
    }

    public function test_import_preview_does_not_mutate_type_or_item_config_options(): void
    {
        $this->seed(WeldingChecksheetTypeSeeder::class);

        $type = WeldingChecksheetType::where('key', 'casing_tank')->firstOrFail();
        $itemConfig = WeldingItemConfig::where('checksheet_type_id', $type->id)
            ->where('item_code', 'CSB29046P3')
            ->firstOrFail();

        $typesBefore = $this->typeSnapshot();
        $itemConfigsBefore = $this->itemConfigSnapshot();
        $uploadedFile = $this->weldingWorkbookUpload();

        $response = $this
            ->withoutMiddleware(CheckModulePermission::class)
            ->actingAs(User::factory()->create())
            ->post(route('welding-checksheets.import.preview'), [
                'file' => $uploadedFile,
                'checksheet_type_id' => $type->id,
                'item_config_id' => $itemConfig->id,
            ]);

        $response
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame($typesBefore, $this->typeSnapshot());
        $this->assertSame($itemConfigsBefore, $this->itemConfigSnapshot());
    }

    private function typeSnapshot(): array
    {
        return WeldingChecksheetType::query()
            ->whereIn('key', ['diaphragm', 'casing_tank'])
            ->orderBy('key')
            ->get(['key', 'name', 'is_active', 'material_fields', 'check_items', 'import_config'])
            ->map(fn (WeldingChecksheetType $type) => [
                'key' => $type->key,
                'name' => $type->name,
                'is_active' => $type->is_active,
                'material_fields' => $type->material_fields,
                'check_items' => $type->check_items,
                'import_config' => $type->import_config,
            ])
            ->all();
    }

    private function itemConfigSnapshot(): array
    {
        return WeldingItemConfig::query()
            ->whereHas('type', fn ($query) => $query->whereIn('key', ['diaphragm', 'casing_tank']))
            ->orderBy('checksheet_type_id')
            ->orderBy('item_code')
            ->get(['checksheet_type_id', 'item_code', 'item_name', 'validation_rules', 'is_active'])
            ->map(fn (WeldingItemConfig $config) => [
                'checksheet_type_id' => $config->checksheet_type_id,
                'item_code' => $config->item_code,
                'item_name' => $config->item_name,
                'validation_rules' => $config->validation_rules,
                'is_active' => $config->is_active,
            ])
            ->all();
    }

    private function weldingWorkbookUpload(): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Welding Data');
        $sheet->setCellValue('A10', '2026-07-01');
        $sheet->setCellValue('I10', 'WM-01');
        $sheet->setCellValue('J10', 'A');
        $sheet->setCellValue('N10', 100);
        $sheet->setCellValue('O10', 'JOB-TEST-001');
        $sheet->setCellValue('P10', 100);

        $path = tempnam(sys_get_temp_dir(), 'welding-import-') . '.xlsx';
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);

        return new UploadedFile(
            $path,
            'welding-import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }
}
