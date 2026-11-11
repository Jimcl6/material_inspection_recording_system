<?php

namespace App\Http\Controllers;

use App\Models\WeldingChecksheet;
use App\Models\WeldingChecksheetType;
use App\Services\ActivityService;
use App\Services\CasingTankPdfExport;
use App\Support\CasingTankPdfTemplates;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WeldingChecksheetPdfController extends Controller
{
    public function monthly(Request $request, CasingTankPdfTemplates $templates, CasingTankPdfExport $export): Response
    {
        $data = $request->validate([
            'item_code' => ['required', 'string', Rule::in($templates->itemCodes())],
            'month' => ['required', 'date_format:Y-m'],
        ]);
        $start = CarbonImmutable::createFromFormat('!Y-m', $data['month']);
        $records = WeldingChecksheet::query()
            ->with(['type', 'samples', 'operator', 'technician', 'checkedBy'])
            ->whereHas('type', fn ($query) => $query->where('key', 'casing_tank'))
            ->where('item_code', $data['item_code'])
            ->where('production_date', '>=', $start->format('Y-m-d'))
            ->where('production_date', '<', $start->addMonth()->format('Y-m-d'))
            ->orderBy('production_date')->orderBy('id')->get();

        return $this->download($records, $export, 'casing-tank-'.Str::slug($data['item_code']).'-'.$data['month'].'.pdf');
    }

    public function show(WeldingChecksheet $welding_checksheet, CasingTankPdfTemplates $templates, CasingTankPdfExport $export): Response
    {
        $welding_checksheet->load(['type', 'samples', 'operator', 'technician', 'checkedBy']);
        $type = $welding_checksheet->type;
        if (! $type instanceof WeldingChecksheetType || $type->key !== 'casing_tank' || ! $templates->supports($welding_checksheet->item_code)) {
            throw ValidationException::withMessages(['pdf' => 'This record does not have a supported five-sample Casing-Tank PDF form.']);
        }

        return $this->download(collect([$welding_checksheet]), $export, 'casing-tank-'.Str::slug($welding_checksheet->item_code).'-record-'.$welding_checksheet->id.'.pdf');
    }

    /** @param Collection<int, WeldingChecksheet> $records */
    private function download(Collection $records, CasingTankPdfExport $export, string $filename): Response
    {
        $bytes = $export->render($records);
        ActivityService::logExport('welding', $records->count(), ['format' => 'pdf', 'filename' => $filename]);

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }
}
