<?php

namespace App\Http\Controllers;

use App\Exports\WeldingChecksheetsExport;
use App\Http\Requests\ImportWeldingChecksheetRequest;
use App\Http\Requests\StoreWeldingChecksheetRequest;
use App\Http\Requests\UpdateWeldingChecksheetRequest;
use App\Imports\WeldingChecksheetImport;
use App\Models\User;
use App\Models\WeldingChecksheet;
use App\Models\WeldingChecksheetType;
use App\Models\WeldingItemConfig;
use App\Services\ActivityService;
use App\Services\ApprovalWorkflowService;
use App\Services\DuplicateRecordGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class WeldingChecksheetController extends Controller
{
    public function index(Request $request)
    {
        $query = WeldingChecksheet::with(['type', 'itemConfig', 'operator', 'technician', 'checkedBy']);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                    ->orWhere('item_name', 'like', "%{$search}%")
                    ->orWhere('job_number', 'like', "%{$search}%")
                    ->orWhere('machine_no', 'like', "%{$search}%")
                    ->orWhere('letter_code', 'like', "%{$search}%")
                    ->orWhere('operator_name_raw', 'like', "%{$search}%")
                    ->orWhere('checked_by_name_raw', 'like', "%{$search}%");
            });
        }

        if ($request->filled('checksheet_type_id')) {
            $query->where('checksheet_type_id', $request->integer('checksheet_type_id'));
        }

        if ($request->filled('item_code')) {
            $query->where('item_code', $request->input('item_code'));
        }

        if ($request->filled('machine_no')) {
            $query->where('machine_no', 'like', '%' . $request->input('machine_no') . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('production_date', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('production_date', '<=', $request->date('date_to'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return Inertia::render('WeldingChecksheets/Index', [
            'checksheets' => $query->orderByDesc('production_date')->orderByDesc('id')->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'checksheet_type_id', 'item_code', 'machine_no', 'date_from', 'date_to', 'status']),
            'types' => $this->typesForForms(),
            'itemCodes' => WeldingItemConfig::query()->active()->orderBy('item_code')->pluck('item_code')->unique()->values(),
        ]);
    }

    public function create()
    {
        return Inertia::render('WeldingChecksheets/Create', $this->formOptions());
    }

    public function store(
        StoreWeldingChecksheetRequest $request,
        ApprovalWorkflowService $approvalWorkflowService,
        DuplicateRecordGuard $duplicateRecordGuard
    )
    {
        $data = $this->prepareChecksheetData($request->validated());
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        $data = array_merge($data, $approvalWorkflowService->initialState());

        $samples = $data['samples'] ?? [];
        unset($data['samples']);

        $checksheet = $duplicateRecordGuard->create(
            WeldingChecksheet::class,
            [
                'checksheet_type_id' => $data['checksheet_type_id'],
                'item_code' => $data['item_code'] ?? null,
                'production_date' => $data['production_date'],
                'machine_no' => $data['machine_no'] ?? null,
                'letter_code' => $data['letter_code'] ?? null,
                'job_number' => $data['job_number'] ?? null,
            ],
            'welding checksheet with the same production identifiers',
            function () use ($data, $samples) {
                $checksheet = WeldingChecksheet::create($data);
                $this->replaceSamples($checksheet, $samples);

                return $checksheet;
            }
        );

        ActivityService::log(
            'create',
            "Created welding checksheet for {$checksheet->item_code}",
            $checksheet,
            ['item_code' => $checksheet->item_code, 'type' => $checksheet->type?->key],
            'welding'
        );

        return redirect()->route('welding-checksheets.index')
            ->with('success', 'Welding checksheet created successfully.');
    }

    public function show(WeldingChecksheet $welding_checksheet)
    {
        $welding_checksheet->load(['type', 'itemConfig', 'samples', 'operator', 'technician', 'checkedBy', 'createdBy', 'updatedBy']);

        return Inertia::render('WeldingChecksheets/Show', [
            'checksheet' => $welding_checksheet,
        ]);
    }

    public function edit(WeldingChecksheet $welding_checksheet)
    {
        $welding_checksheet->load(['type', 'itemConfig', 'samples']);

        return Inertia::render('WeldingChecksheets/Edit', array_merge($this->formOptions(), [
            'checksheet' => $welding_checksheet,
        ]));
    }

    public function update(UpdateWeldingChecksheetRequest $request, WeldingChecksheet $welding_checksheet)
    {
        $data = $this->prepareChecksheetData($request->validated());
        $data['updated_by'] = Auth::id();

        if (($data['status'] ?? null) === 'approved' && $welding_checksheet->status !== 'approved') {
            $data['approved_at'] = now();
        }

        $samples = $data['samples'] ?? [];
        unset($data['samples']);

        $welding_checksheet->update($data);
        $this->replaceSamples($welding_checksheet, $samples);

        ActivityService::log(
            'update',
            "Updated welding checksheet for {$welding_checksheet->item_code}",
            $welding_checksheet,
            ['item_code' => $welding_checksheet->item_code],
            'welding'
        );

        return redirect()->route('welding-checksheets.index')
            ->with('success', 'Welding checksheet updated successfully.');
    }

    public function destroy(WeldingChecksheet $welding_checksheet)
    {
        $itemCode = $welding_checksheet->item_code;
        $welding_checksheet->delete();

        ActivityService::log(
            'delete',
            "Deleted welding checksheet for {$itemCode}",
            null,
            ['item_code' => $itemCode],
            'welding'
        );

        return redirect()->route('welding-checksheets.index')
            ->with('success', 'Welding checksheet deleted successfully.');
    }

    public function importForm()
    {
        return Inertia::render('WeldingChecksheets/Import', $this->formOptions());
    }

    public function importPreview(ImportWeldingChecksheetRequest $request)
    {
        try {
            $file = $request->file('file');
            $tempPath = $file->store('temp');
            $fullPath = storage_path('app/' . $tempPath);
            $type = WeldingChecksheetType::findOrFail($request->integer('checksheet_type_id'));
            $itemConfig = $request->filled('item_config_id')
                ? WeldingItemConfig::find($request->integer('item_config_id'))
                : null;

            $import = new WeldingChecksheetImport($file->getClientOriginalName());

            session([
                'welding_import' => [
                    'file' => $tempPath,
                    'checksheet_type_id' => $type->id,
                    'item_config_id' => $itemConfig?->id,
                    'item_code' => $request->input('item_code'),
                    'item_name' => $request->input('item_name'),
                    'source_file' => $file->getClientOriginalName(),
                ],
            ]);

            return response()->json([
                'success' => true,
                'preview' => $import->preview($fullPath, $type, $itemConfig, $request->input('item_code'), $request->input('item_name')),
            ]);
        } catch (\Throwable $e) {
            Log::error('Welding import preview failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to preview file: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function importExecute(Request $request)
    {
        $request->validate([
            'update_duplicates' => ['nullable', 'boolean'],
        ]);

        $payload = session('welding_import');
        if (!$payload) {
            return response()->json(['success' => false, 'error' => 'No file to import. Please upload a file first.'], 400);
        }

        $fullPath = storage_path('app/' . $payload['file']);
        if (!file_exists($fullPath)) {
            session()->forget('welding_import');
            return response()->json(['success' => false, 'error' => 'Import file expired. Please upload again.'], 400);
        }

        try {
            $type = WeldingChecksheetType::findOrFail($payload['checksheet_type_id']);
            $itemConfig = !empty($payload['item_config_id']) ? WeldingItemConfig::find($payload['item_config_id']) : null;
            $import = new WeldingChecksheetImport($payload['source_file'] ?? null);

            $results = $import->execute(
                $fullPath,
                $type,
                $itemConfig,
                $payload['item_code'] ?? null,
                $payload['item_name'] ?? null,
                $request->boolean('update_duplicates')
            );

            @unlink($fullPath);
            session()->forget('welding_import');

            ActivityService::logImport('welding', (int) ($results['imported'] ?? 0), [
                'updated' => $results['updated'] ?? 0,
                'skipped' => $results['skipped'] ?? 0,
                'errors' => count($results['errors'] ?? []),
                'type' => $type->key,
                'item_code' => $payload['item_code'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'results' => $results,
                'message' => $this->importMessage($results),
            ]);
        } catch (\Throwable $e) {
            Log::error('Welding import execute failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to import file: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function export()
    {
        ActivityService::logExport('welding', WeldingChecksheet::count());

        return Excel::download(
            new WeldingChecksheetsExport(),
            'welding-checksheets-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function approval()
    {
        return Inertia::render('WeldingChecksheets/Approval', [
            'pendingChecksheets' => WeldingChecksheet::with(['type', 'createdBy', 'operator'])
                ->where('status', 'pending')
                ->latest()
                ->get(),
            'user' => Auth::user(),
        ]);
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'checksheet_ids' => ['required', 'array'],
            'checksheet_ids.*' => ['exists:welding_checksheets,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $checksheets = WeldingChecksheet::whereIn('id', $request->input('checksheet_ids'))->get();
        foreach ($checksheets as $checksheet) {
            $checksheet->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approval_notes' => $request->input('notes'),
                'updated_by' => Auth::id(),
            ]);
        }

        return redirect()->route('welding-checksheets.approval')
            ->with('success', count($checksheets) . ' checksheet(s) approved successfully.');
    }

    public function bulkReject(Request $request)
    {
        $request->validate([
            'checksheet_ids' => ['required', 'array'],
            'checksheet_ids.*' => ['exists:welding_checksheets,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $checksheets = WeldingChecksheet::whereIn('id', $request->input('checksheet_ids'))->get();
        foreach ($checksheets as $checksheet) {
            $checksheet->update([
                'status' => 'rejected',
                'approved_at' => now(),
                'approval_notes' => $request->input('notes'),
                'updated_by' => Auth::id(),
            ]);
        }

        return redirect()->route('welding-checksheets.approval')
            ->with('success', count($checksheets) . ' checksheet(s) rejected successfully.');
    }

    public function itemCodeRules(Request $request)
    {
        $query = WeldingItemConfig::query()->active();

        if ($request->filled('checksheet_type_id')) {
            $query->where('checksheet_type_id', $request->integer('checksheet_type_id'));
        }

        $config = $query->where('item_code', $request->input('item_code'))->first();

        return response()->json($config ?: [
            'item_code' => $request->input('item_code'),
            'validation_rules' => [],
        ]);
    }

    protected function typesForForms()
    {
        return WeldingChecksheetType::with(['itemConfigs' => fn ($query) => $query->active()->orderBy('item_code')])
            ->active()
            ->orderBy('name')
            ->get();
    }

    protected function formOptions(): array
    {
        return [
            'users' => User::select('id', 'name')->orderBy('name')->get(),
            'types' => $this->typesForForms(),
        ];
    }

    protected function prepareChecksheetData(array $data): array
    {
        $itemConfig = null;
        if (!empty($data['item_config_id'])) {
            $itemConfig = WeldingItemConfig::find($data['item_config_id']);
        } elseif (!empty($data['item_code'])) {
            $itemConfig = WeldingItemConfig::where('checksheet_type_id', $data['checksheet_type_id'])
                ->where('item_code', $data['item_code'])
                ->first();
        }

        if ($itemConfig) {
            $data['item_config_id'] = $itemConfig->id;
            $data['item_code'] = $data['item_code'] ?: $itemConfig->item_code;
            $data['item_name'] = $data['item_name'] ?: $itemConfig->item_name;
        }

        $data['material_fields'] = $data['material_fields'] ?? [];

        return $data;
    }

    protected function replaceSamples(WeldingChecksheet $checksheet, array $samples): void
    {
        $checksheet->samples()->delete();

        foreach ($samples as $index => $sample) {
            $checksheet->samples()->create([
                'check_item_key' => $sample['check_item_key'],
                'check_item_label' => $sample['check_item_label'] ?? $sample['check_item_key'],
                'requirement_text' => $sample['requirement_text'] ?? null,
                'sample_values' => $sample['sample_values'] ?? ['', '', '', '', ''],
                'sort_order' => $sample['sort_order'] ?? $index,
            ]);
        }
    }

    protected function importMessage(array $results): string
    {
        $message = "Import completed: {$results['imported']} created";
        if (($results['updated'] ?? 0) > 0) {
            $message .= ", {$results['updated']} updated";
        }
        if (($results['skipped'] ?? 0) > 0) {
            $message .= ", {$results['skipped']} skipped";
        }
        if (count($results['errors'] ?? []) > 0) {
            $message .= ', ' . count($results['errors']) . ' errors';
        }

        return $message;
    }
}
