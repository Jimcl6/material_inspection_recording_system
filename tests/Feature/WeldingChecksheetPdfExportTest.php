<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserPermission;
use App\Models\WeldingChecksheet;
use App\Models\WeldingChecksheetType;
use App\Models\WeldingItemConfig;
use App\Services\CasingTankPdfExport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WeldingChecksheetPdfExportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_monthly_download_selects_only_the_requested_item_month_and_type_across_all_statuses(): void
    {
        $type = WeldingChecksheetType::firstOrCreate(['key' => 'casing_tank'], ['name' => 'Casing-Tank']);
        $user = $this->exportUser();
        $ids = [];
        foreach (['pending', 'approved', 'rejected'] as $status) {
            $ids[] = $this->record($type, $user, ['status' => $status])->id;
        }
        $this->record($type, $user, ['item_code' => 'CSB290054P']);
        $this->record($type, $user, ['production_date' => '2026-10-31']);
        $this->record($type, $user, ['production_date' => '2026-12-01']);
        $this->record($type, $user)->delete();
        $otherType = WeldingChecksheetType::firstOrCreate(['key' => 'diaphragm'], ['name' => 'Diaphragm']);
        $this->record($otherType, $user);

        $this->mock(CasingTankPdfExport::class)->shouldReceive('render')->once()
            ->withArgs(function ($records) use ($ids) {
                $this->assertEqualsCanonicalizing($ids, $records->pluck('id')->all());

                return true;
            })->andReturn('%PDF-synthetic-test');
        $this->actingAs($user)->get(route('welding-checksheets.pdf.monthly', ['item_code' => 'CSB290053P', 'month' => '2026-11']))
            ->assertOk()->assertHeader('Content-Type', 'application/pdf')
            ->assertDownload('casing-tank-csb290053p-2026-11.pdf');
        $this->assertDatabaseHas('activities', ['type' => 'export', 'module' => 'welding', 'description' => 'Exported 3 records from welding']);
    }

    public function test_individual_download_renders_a_rejected_record(): void
    {
        $user = $this->exportUser();
        $type = WeldingChecksheetType::firstOrCreate(['key' => 'casing_tank'], ['name' => 'Casing-Tank']);
        $record = $this->record($type, $user, ['status' => 'rejected']);
        $response = $this->actingAs($user)->get(route('welding-checksheets.pdf.show', $record));

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_export_permission_is_enforced_for_monthly_and_individual_downloads(): void
    {
        $user = User::factory()->create();
        $type = WeldingChecksheetType::firstOrCreate(['key' => 'casing_tank'], ['name' => 'Casing-Tank']);
        $record = $this->record($type, $user);
        foreach ([route('welding-checksheets.pdf.monthly'), route('welding-checksheets.pdf.show', $record)] as $url) {
            $this->actingAs($user)->getJson($url)->assertForbidden();
        }
    }

    public function test_invalid_month_unsupported_item_and_empty_result_do_not_download_a_pdf(): void
    {
        $this->actingAs($this->exportUser());
        $this->getJson(route('welding-checksheets.pdf.monthly', ['item_code' => 'CSB641001P', 'month' => '2026-11']))
            ->assertUnprocessable()->assertJsonValidationErrors('item_code');
        $this->getJson(route('welding-checksheets.pdf.monthly', ['item_code' => 'CSB290053P', 'month' => '2026-13']))
            ->assertUnprocessable()->assertJsonValidationErrors('month');
        $this->getJson(route('welding-checksheets.pdf.monthly', ['item_code' => 'CSB290054P', 'month' => '1900-01']))
            ->assertUnprocessable()->assertJsonValidationErrors('pdf');
    }

    public function test_index_filter_and_pdf_options_share_all_registered_item_codes(): void
    {
        $user = $this->exportUser();
        $permission = UserPermission::firstOrCreate(['slug' => 'welding.view'], ['name' => 'View Welding', 'module' => 'welding', 'action' => 'view']);
        $user->role->permissions()->syncWithoutDetaching([$permission->id]);
        $type = WeldingChecksheetType::firstOrCreate(['key' => 'casing_tank'], ['name' => 'Casing-Tank']);
        WeldingItemConfig::create(['checksheet_type_id' => $type->id, 'item_code' => 'NEW-PDF-ITEM', 'is_active' => true]);

        $response = $this->actingAs($user)->get(route('welding-checksheets.index'))->assertOk();
        $props = $response->viewData('page')['props'];
        $this->assertSame(collect($props['itemCodes'])->all(), array_column($props['pdfItemOptions'], 'value'));
        $newItem = collect($props['pdfItemOptions'])->firstWhere('value', 'NEW-PDF-ITEM');
        $this->assertFalse($newItem['supported']);
        $this->assertSame('Reference form needed', $newItem['reason']);
    }

    private function exportUser(): User
    {
        $user = User::factory()->create();
        $permission = UserPermission::firstOrCreate(['slug' => 'welding.export'], ['name' => 'Export Welding', 'module' => 'welding', 'action' => 'export']);
        $user->role->permissions()->syncWithoutDetaching([$permission->id]);

        return $user;
    }

    private function record(WeldingChecksheetType $type, User $user, array $overrides = []): WeldingChecksheet
    {
        return WeldingChecksheet::create(array_merge([
            'checksheet_type_id' => $type->id,
            'item_code' => 'CSB290053P',
            'production_date' => '2026-11-10',
            'machine_no' => '3',
            'job_number' => 'TEST-JOB',
            'letter_code' => 'A',
            'created_by' => $user->id,
        ], $overrides));
    }
}
