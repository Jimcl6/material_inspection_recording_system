<?php

namespace App\Http\Controllers;

use App\Models\DiaphragmWeldingChecksheet;
use App\Models\DiaphragmWeldingSample;
use App\Models\DiaphragmItemCode;
use App\Http\Requests\StoreDiaphragmWeldingRequest;
use App\Http\Requests\UpdateDiaphragmWeldingRequest;
use App\Http\Requests\ImportDiaphragmWeldingRequest;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DiaphragmWeldingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DiaphragmWeldingChecksheet::with(['operator', 'technician', 'checkedBy']);

        // Filters
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                  ->orWhere('lasermark_lot_number', 'like', "%{$search}%")
                  ->orWhere('jo_number', 'like', "%{$search}%")
                  ->orWhere('machine_no', 'like', "%{$search}%");
            });
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

        if ($request->filled('item_code')) {
            $query->where('item_code', $request->input('item_code'));
        }

        $checksheets = $query->orderByDesc('production_date')
                             ->orderByDesc('id')
                             ->paginate(15)
                             ->withQueryString();

        // Get unique item codes for filter dropdown
        $itemCodes = DiaphragmItemCode::orderBy('item_code')->pluck('item_code');

        return Inertia::render('DiaphragmWelding/Index', [
            'checksheets' => $checksheets,
            'filters' => $request->only(['search', 'date_from', 'date_to', 'status', 'item_code']),
            'itemCodes' => $itemCodes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = \App\Models\User::select('id', 'name')->orderBy('name')->get();
        $itemCodes = DiaphragmItemCode::orderBy('item_code')->get();
        $checkItems = DiaphragmWeldingSample::CHECK_ITEM_LABELS;

        return Inertia::render('DiaphragmWelding/Create', [
            'users' => $users,
            'itemCodes' => $itemCodes,
            'checkItems' => $checkItems,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDiaphragmWeldingRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        $data['status'] = 'pending';
        $data['submitted_at'] = now();

        $samples = $data['samples'] ?? [];
        unset($data['samples']);

        $checksheet = DiaphragmWeldingChecksheet::create($data);

        // Create samples
        foreach ($samples as $sample) {
            $checksheet->samples()->create($sample);
        }

        // Log activity
        if (class_exists(ActivityService::class)) {
            ActivityService::log(
                'create',
                "Created diaphragm welding checksheet for {$checksheet->item_code}",
                $checksheet,
                ['item_code' => $checksheet->item_code],
                'diaphragm_welding'
            );
        }

        return redirect()->route('diaphragm-welding.index')
            ->with('success', 'Diaphragm welding checksheet created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DiaphragmWeldingChecksheet $diaphragmWelding)
    {
        $diaphragmWelding->load(['samples', 'operator', 'technician', 'checkedBy', 'createdBy', 'updatedBy', 'itemCodeConfig']);

        $checkItems = DiaphragmWeldingSample::CHECK_ITEM_LABELS;

        return Inertia::render('DiaphragmWelding/Show', [
            'checksheet' => $diaphragmWelding,
            'checkItems' => $checkItems,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DiaphragmWeldingChecksheet $diaphragmWelding)
    {
        $diaphragmWelding->load('samples');

        $users = \App\Models\User::select('id', 'name')->orderBy('name')->get();
        $itemCodes = DiaphragmItemCode::orderBy('item_code')->get();
        $checkItems = DiaphragmWeldingSample::CHECK_ITEM_LABELS;

        return Inertia::render('DiaphragmWelding/Edit', [
            'checksheet' => $diaphragmWelding,
            'users' => $users,
            'itemCodes' => $itemCodes,
            'checkItems' => $checkItems,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDiaphragmWeldingRequest $request, DiaphragmWeldingChecksheet $diaphragmWelding)
    {
        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        // If status is changing to approved, set approved_at
        if (isset($data['status']) && $data['status'] === 'approved' && $diaphragmWelding->status !== 'approved') {
            $data['approved_at'] = now();
        }

        $samples = $data['samples'] ?? [];
        unset($data['samples']);

        $diaphragmWelding->update($data);

        // Update samples - delete existing and create new
        $diaphragmWelding->samples()->delete();
        foreach ($samples as $sample) {
            $diaphragmWelding->samples()->create($sample);
        }

        // Log activity
        if (class_exists(ActivityService::class)) {
            ActivityService::log(
                'update',
                "Updated diaphragm welding checksheet for {$diaphragmWelding->item_code}",
                $diaphragmWelding,
                ['item_code' => $diaphragmWelding->item_code],
                'diaphragm_welding'
            );
        }

        return redirect()->route('diaphragm-welding.index')
            ->with('success', 'Diaphragm welding checksheet updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DiaphragmWeldingChecksheet $diaphragmWelding)
    {
        $itemCode = $diaphragmWelding->item_code;
        
        $diaphragmWelding->delete();

        // Log activity
        if (class_exists(ActivityService::class)) {
            ActivityService::log(
                'delete',
                "Deleted diaphragm welding checksheet for {$itemCode}",
                null,
                ['item_code' => $itemCode],
                'diaphragm_welding'
            );
        }

        return redirect()->route('diaphragm-welding.index')
            ->with('success', 'Diaphragm welding checksheet deleted successfully.');
    }

    /**
     * Show the import form.
     */
    public function importForm()
    {
        return Inertia::render('DiaphragmWelding/Import');
    }

    /**
     * Import checksheets from Excel file.
     */
    public function import(ImportDiaphragmWeldingRequest $request)
    {
        $file = $request->file('file');
        $overwrite = $request->boolean('overwrite', false);

        if (!$file) {
            return Inertia::render('DiaphragmWelding/Import')
                ->with('error', 'No file uploaded.');
        }

        try {
            if ($overwrite) {
                DiaphragmWeldingChecksheet::truncate();
            }

            $import = new \App\Imports\DiaphragmWeldingImport();
            
            $tempPath = $file->store('temp');
            $fullPath = storage_path('app/' . $tempPath);
            
            $import->import($fullPath);
            
            @unlink($fullPath);
            
            $results = $import->getResults();

            $totalImported = $results['imported'] ?? 0;
            $totalSkipped = $results['skipped'] ?? 0;
            $totalErrors = count($results['errors'] ?? []);
            
            $message = "Import completed: {$totalImported} imported";
            if ($totalSkipped > 0) $message .= ", {$totalSkipped} skipped";
            if ($totalErrors > 0) $message .= ", {$totalErrors} errors";

            return Inertia::render('DiaphragmWelding/Import', [
                'import_results' => $results,
                'success' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Diaphragm Welding Import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('DiaphragmWelding/Import')
                ->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    /**
     * Export checksheets to Excel file.
     */
    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\DiaphragmWeldingExport, 
            'diaphragm-welding-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Preview import - Phase 1: Parse file and detect duplicates
     */
    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        $file = $request->file('file');

        try {
            $tempPath = $file->store('temp');
            $fullPath = storage_path('app/' . $tempPath);

            $import = new \App\Imports\DiaphragmWeldingImport();
            $results = $import->preview($fullPath);

            // Store the temp file path in session for execute phase
            session(['diaphragm_welding_import_file' => $tempPath]);

            return response()->json([
                'success' => true,
                'preview' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Diaphragm Welding Import Preview failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to preview file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute import - Phase 2: Create/update records based on user choice
     */
    public function importExecute(Request $request)
    {
        $request->validate([
            'update_duplicates' => ['nullable', 'boolean'],
        ]);

        $tempPath = session('diaphragm_welding_import_file');

        if (!$tempPath) {
            return response()->json([
                'success' => false,
                'error' => 'No file to import. Please upload a file first.',
            ], 400);
        }

        $fullPath = storage_path('app/' . $tempPath);

        if (!file_exists($fullPath)) {
            session()->forget('diaphragm_welding_import_file');
            return response()->json([
                'success' => false,
                'error' => 'Import file expired. Please upload again.',
            ], 400);
        }

        try {
            $updateDuplicates = $request->boolean('update_duplicates', false);

            $import = new \App\Imports\DiaphragmWeldingImport();
            $results = $import->execute($fullPath, $updateDuplicates);

            // Clean up temp file
            @unlink($fullPath);
            session()->forget('diaphragm_welding_import_file');

            $message = "Import completed: {$results['imported']} created";
            if ($results['updated'] > 0) $message .= ", {$results['updated']} updated";
            if ($results['skipped'] > 0) $message .= ", {$results['skipped']} skipped";
            if (count($results['errors']) > 0) $message .= ", " . count($results['errors']) . " errors";

            return response()->json([
                'success' => true,
                'results' => $results,
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            Log::error('Diaphragm Welding Import Execute failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to import file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the approval page for administrators and inspectors
     */
    public function approval()
    {
        $user = Auth::user();

        if (!in_array($user->role?->slug, ['admin', 'inspector', 'super_admin'])) {
            abort(403, 'Unauthorized');
        }

        $pendingChecksheets = DiaphragmWeldingChecksheet::with(['createdBy', 'operator'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return Inertia::render('DiaphragmWelding/Approval', [
            'pendingChecksheets' => $pendingChecksheets,
            'user' => $user,
        ]);
    }

    /**
     * Bulk approve checksheets
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'checksheet_ids' => 'required|array',
            'checksheet_ids.*' => 'exists:diaphragm_welding_checksheets,id',
            'notes' => 'nullable|string',
        ]);

        $checksheets = DiaphragmWeldingChecksheet::whereIn('id', $request->checksheet_ids)->get();

        foreach ($checksheets as $checksheet) {
            $checksheet->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approval_notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);
        }

        return redirect()->route('diaphragm-welding.approval')
            ->with('success', count($checksheets) . ' checksheet(s) approved successfully.');
    }

    /**
     * Bulk reject checksheets
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'checksheet_ids' => 'required|array',
            'checksheet_ids.*' => 'exists:diaphragm_welding_checksheets,id',
            'notes' => 'nullable|string',
        ]);

        $checksheets = DiaphragmWeldingChecksheet::whereIn('id', $request->checksheet_ids)->get();

        foreach ($checksheets as $checksheet) {
            $checksheet->update([
                'status' => 'rejected',
                'approved_at' => now(),
                'approval_notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);
        }

        return redirect()->route('diaphragm-welding.approval')
            ->with('success', count($checksheets) . ' checksheet(s) rejected successfully.');
    }

    /**
     * Get validation rules for an item code (API endpoint)
     */
    public function getItemCodeRules(Request $request)
    {
        $itemCode = $request->input('item_code');
        
        $config = DiaphragmItemCode::where('item_code', $itemCode)->first();
        
        if (!$config) {
            // Return default rules
            return response()->json([
                'item_code' => $itemCode,
                'strength_min' => 0.30,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ]);
        }

        return response()->json($config);
    }
}
