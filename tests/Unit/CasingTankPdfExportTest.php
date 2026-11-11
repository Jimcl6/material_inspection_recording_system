<?php

namespace Tests\Unit;

use App\Http\Middleware\CheckModulePermission;
use App\Models\User;
use App\Models\WeldingChecksheetType;
use App\Models\WeldingItemConfig;
use App\Services\CasingTankPdfExport;
use App\Support\CasingTankPdfTemplates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Support\CasingTankPdfFixtures;
use Tests\TestCase;

class CasingTankPdfExportTest extends TestCase
{
    use CasingTankPdfFixtures;

    public function test_lot_changes_keep_separate_blocks_on_the_same_page_in_date_order(): void
    {
        $early = $this->pdfRecord(2, ['production_date' => '2026-11-01', 'letter_code' => 'B', 'quantity' => 125]);
        $late = $this->pdfRecord(1, ['production_date' => '2026-11-10', 'letter_code' => 'B', 'material_fields' => ['casing' => 'DIFFERENT']]);
        $pages = app(CasingTankPdfExport::class)->pages(collect([$late, $early]));

        $this->assertCount(1, $pages);
        $this->assertSame([$early, $late], $pages[0]);
        $this->assertSame(125, $pages[0][0]->quantity);
        $this->assertSame('DIFFERENT', $pages[0][1]->getMaterialFieldValue('casing'));
    }

    public function test_machine_job_item_and_month_each_start_separate_pages(): void
    {
        $records = collect([
            $this->pdfRecord(),
            $this->pdfRecord(2, ['machine_no' => '4']),
            $this->pdfRecord(3, ['job_number' => 'DEMO-JO-002']),
            $this->pdfRecord(5, ['item_code' => 'CSB290054P']),
            $this->pdfRecord(6, ['production_date' => '2026-12-01']),
        ]);
        $pages = app(CasingTankPdfExport::class)->pages($records);

        $this->assertCount(5, $pages);
        foreach ($pages as $page) {
            $this->assertCount(1, $page);
        }
    }

    public function test_seventh_record_continues_on_a_new_page_and_status_does_not_exclude_records(): void
    {
        $records = collect();
        for ($id = 1; $id <= 7; $id++) {
            $records->push($this->pdfRecord($id, ['letter_code' => chr(64 + $id), 'status' => $id % 2 === 0 ? 'rejected' : 'pending']));
        }
        $pages = app(CasingTankPdfExport::class)->pages($records->reverse());

        $this->assertCount(2, $pages);
        $this->assertSame([1, 2, 3, 4, 5, 6], array_column($pages[0], 'id'));
        $this->assertSame(7, $pages[1][0]->id);
    }

    public function test_incrementing_letters_continue_until_a_starts_the_next_checksheet(): void
    {
        $records = collect(['A', 'B', 'C', 'A', 'B'])->map(fn ($letter, $index) => $this->pdfRecord($index + 1, ['letter_code' => $letter]));
        $pages = app(CasingTankPdfExport::class)->pages($records->reverse());

        $this->assertSame([['A', 'B', 'C'], ['A', 'B']], array_map(fn ($page) => array_column($page, 'letter_code'), $pages));
        $this->assertSame([[1, 2, 3], [4, 5]], array_map(fn ($page) => array_column($page, 'id'), $pages));
    }

    public function test_consecutive_a_records_each_start_a_new_checksheet(): void
    {
        $records = collect(['A', 'A', 'B', 'A'])->map(fn ($letter, $index) => $this->pdfRecord($index + 1, ['letter_code' => $letter]));
        $pages = app(CasingTankPdfExport::class)->pages($records);

        $this->assertSame([[1], [2, 3], [4]], array_map(fn ($page) => array_column($page, 'id'), $pages));
    }

    public function test_a_after_a_continuation_page_starts_another_page(): void
    {
        $records = collect(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'A', 'B'])->map(fn ($letter, $index) => $this->pdfRecord($index + 1, ['letter_code' => $letter]));
        $pages = app(CasingTankPdfExport::class)->pages($records);

        $this->assertSame([['A', 'B', 'C', 'D', 'E', 'F'], ['G'], ['A', 'B']], array_map(fn ($page) => array_column($page, 'letter_code'), $pages));
    }

    public function test_partial_sequence_without_a_starts_on_a_fresh_page(): void
    {
        $records = collect(['B', 'C', 'D'])->map(fn ($letter, $index) => $this->pdfRecord($index + 1, ['letter_code' => $letter]));
        $pages = app(CasingTankPdfExport::class)->pages($records);

        $this->assertSame([['B', 'C', 'D']], array_map(fn ($page) => array_column($page, 'letter_code'), $pages));
    }

    public function test_interleaved_machines_keep_their_own_letter_sequences(): void
    {
        $records = collect();
        foreach ([['3', 'A'], ['4', 'A'], ['3', 'B'], ['4', 'B'], ['3', 'A'], ['4', 'C']] as $index => [$machine, $letter]) {
            $records->push($this->pdfRecord($index + 1, ['machine_no' => $machine, 'letter_code' => $letter]));
        }
        $pages = app(CasingTankPdfExport::class)->pages($records);

        $this->assertSame([[1, 3], [5], [2, 4, 6]], array_map(fn ($page) => array_column($page, 'id'), $pages));
    }

    public function test_ten_sample_and_unmapped_items_are_rejected(): void
    {
        foreach (['CSB641001P', 'TKB4000100', 'UNKNOWN'] as $code) {
            try {
                app(CasingTankPdfExport::class)->pages(collect([$this->pdfRecord(1, ['item_code' => $code])]));
                $this->fail('An item without an approved five-sample form was accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('pdf', $exception->errors());
            }
        }
    }

    public function test_dropdown_keeps_registered_items_and_explains_unavailable_forms(): void
    {
        $items = collect();
        foreach ([
            ['CSB290053P', 'casing_tank'],
            ['CSB6401012', 'casing_tank'],
            ['CSB641001P', 'casing_tank'],
            ['DFB4803000', 'diaphragm'],
            ['TKB4000100', 'casing_tank'],
            ['NEW-REGISTERED-CODE', 'casing_tank'],
            ['CSB290053P', 'diaphragm'],
        ] as [$code, $typeKey]) {
            $item = new WeldingItemConfig(['item_code' => $code]);
            $item->setRelation('type', new WeldingChecksheetType(['key' => $typeKey]));
            $items->push($item);
        }
        $options = app(CasingTankPdfTemplates::class)->optionsFor($items);

        $this->assertSame($items->pluck('item_code')->unique()->values()->all(), array_column($options, 'value'));
        $byCode = collect($options)->keyBy('value');
        $this->assertTrue($byCode['CSB290053P']['supported']);
        $this->assertNull($byCode['CSB290053P']['reason']);
        $this->assertTrue($byCode['CSB6401012']['supported']);
        $this->assertNull($byCode['CSB6401012']['reason']);
        foreach (['TKB4000100', 'NEW-REGISTERED-CODE'] as $code) {
            $this->assertFalse($byCode[$code]['supported']);
            $this->assertSame('Reference form needed', $byCode[$code]['reason']);
        }
        $this->assertSame('Diaphragm deferred', $byCode['DFB4803000']['reason']);
        $this->assertSame('Ten-sample form deferred', $byCode['CSB641001P']['reason']);
    }

    public function test_confirmed_csb6401012_mapping_uses_the_approved_form_and_keeps_its_item_code(): void
    {
        $templates = app(CasingTankPdfTemplates::class);
        $this->assertTrue($templates->supports('CSB6401012'));
        $this->assertSame($templates->forItem('CSB290053P'), $templates->forItem('CSB6401012'));
        $record = $this->pdfRecord(42, ['item_code' => 'CSB6401012']);
        $bytes = app(CasingTankPdfExport::class)->render(collect([$record]));

        $this->assertStringStartsWith('%PDF-', $bytes);
        $this->assertSame('CSB6401012', $record->item_code);
    }

    public function test_diaphragm_is_rejected_even_when_the_item_code_matches(): void
    {
        $record = $this->pdfRecord();
        $record->setRelation('type', new WeldingChecksheetType(['key' => 'diaphragm']));
        $this->expectException(ValidationException::class);
        app(CasingTankPdfExport::class)->render(collect([$record]));
    }

    public function test_each_printed_requirement_and_material_heading_matches_its_reference_workbook(): void
    {
        $templates = app(CasingTankPdfTemplates::class);
        foreach ($templates->itemCodes() as $code) {
            $template = $templates->forItem($code);
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true)->setLoadSheetsOnly($template['source_sheet']);
            $workbook = $reader->load(base_path('docs/excel_files/'.$template['source_file']));
            try {
                $sheet = $workbook->getSheetByName($template['source_sheet']);
                $normalize = fn ($value) => trim(preg_replace('/\s+/u', ' ', (string) $value));
                $this->assertSame($template['depth_label'], $normalize($sheet->getCell('R10')->getValue()), $code);
                $this->assertSame($template['time_label'], $normalize($sheet->getCell('R11')->getValue()), $code);
                foreach (['K8' => 'casing', 'L8' => 'valve_cover', 'M8' => 'air_valve'] as $cell => $key) {
                    $this->assertSame($template['materials'][$key], $normalize($sheet->getCell($cell)->getValue()), $code);
                }
            } finally {
                $workbook->disconnectWorksheets();
            }
        }
    }

    public function test_judgements_preserve_blanks_and_do_not_turn_zero_into_missing_data(): void
    {
        $export = app(CasingTankPdfExport::class);
        foreach (['P' => '✓', 'OK' => '✓', 'NG' => 'X', 'X' => 'X', '/' => '/', '' => '', '0' => '0'] as $value => $expected) {
            $this->assertSame($expected, $export->judgement((string) $value));
        }
    }

    public function test_renderer_creates_one_and_multiple_a4_landscape_pages_without_changing_sample_precision(): void
    {
        $record = $this->pdfRecord();
        $valuesBefore = $record->samples->first()->sample_values;
        $bytes = app(CasingTankPdfExport::class)->render(collect([$record]));
        $this->assertStringStartsWith('%PDF-', $bytes);
        $this->assertMatchesRegularExpression('/\/Type \/Pages\b[^>]*\/Count 1\b/s', $bytes);
        $this->assertMatchesRegularExpression('/\/MediaBox \[0\.000 0\.000 841\.890 595\.280\]/', $bytes);
        $this->assertSame($valuesBefore, $record->samples->first()->sample_values);

        $records = collect(range(1, 7))->map(fn ($id) => $this->pdfRecord($id, ['letter_code' => chr(64 + $id)]));
        $bytes = app(CasingTankPdfExport::class)->render($records);
        $this->assertMatchesRegularExpression('/\/Type \/Pages\b[^>]*\/Count 2\b/s', $bytes);
    }

    public function test_empty_export_has_an_actionable_error(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('No Casing-Tank records');
        app(CasingTankPdfExport::class)->render(collect());
    }

    public function test_values_that_cannot_fit_are_reported_with_the_record_number(): void
    {
        $record = $this->pdfRecord(42, ['technician_name_raw' => str_repeat('W', 255)]);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Text in record 42 is too long');
        app(CasingTankPdfExport::class)->render(collect([$record]));
    }

    public function test_both_pdf_routes_require_export_permission_without_an_approval_feature_gate(): void
    {
        foreach (['welding-checksheets.pdf.monthly', 'welding-checksheets.pdf.show'] as $name) {
            $middleware = Route::getRoutes()->getByName($name)->gatherMiddleware();
            $this->assertContains('auth', $middleware);
            $this->assertContains('module.permission:welding,export', $middleware);
            $this->assertNotContains('feature:approvals', $middleware);
        }
        $request = Request::create('/welding-checksheets/export/pdf', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $request->setUserResolver(fn () => new User);
        $response = app(CheckModulePermission::class)->handle($request, fn () => response('unexpected download'), 'welding', 'export');
        $this->assertSame(403, $response->getStatusCode());
    }
}
