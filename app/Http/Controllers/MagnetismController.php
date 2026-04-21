<?php

namespace App\Http\Controllers;

use App\Models\MagnetismChecksheet;
use App\Models\MagnetismBatch;
use App\Models\MagnetismCheckpoint;
use App\Http\Requests\MagnetismCheckSheetRequest;
use App\Http\Requests\MagnetismBatchRequest;
use App\Imports\MagnetismChecksheetImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MagnetismController extends Controller
{
    /**
     * Display a listing of checksheets.
     */
    public function index(Request $request)
    {
        $query = MagnetismChecksheet::query();

        // Filters
        if ($request->filled('item_code')) {
            $query->where('item_code', 'like', '%' . $request->input('item_code') . '%');
        }
        if ($request->filled('machine_no')) {
            $query->where('machine_no', 'like', '%' . $request->input('machine_no') . '%');
        }
        if ($request->filled('month')) {
            $query->where('month', $request->input('month'));
        }
        if ($request->filled('year')) {
            $query->where('year', $request->input('year'));
        }

        $checksheets = $query->orderByDesc('year')
            ->orderByDesc('month')
            ->orderBy('item_code')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Magnetism/Index', [
            'checksheets' => $checksheets,
            'filters' => $request->only(['item_code', 'machine_no', 'month', 'year']),
        ]);
    }

    /**
     * Show the form for creating a new checksheet.
     */
    public function create()
    {
        return Inertia::render('Magnetism/Create');
    }

    /**
     * Store a newly created checksheet.
     */
    public function store(MagnetismCheckSheetRequest $request)
    {
        $checksheet = MagnetismChecksheet::create($request->validated());

        return redirect()->route('magnetism-checksheet.show', $checksheet->id)
            ->with('success', 'Checksheet created successfully.');
    }

    /**
     * Display the specified checksheet with batches and checkpoints.
     */
    public function show(Request $request, MagnetismChecksheet $magnetism_checksheet)
    {
        $checksheet = $magnetism_checksheet;
        
        // Get all unique production dates
        $productionDates = $checksheet->batches()
            ->select('production_date')
            ->distinct()
            ->orderByDesc('production_date')
            ->pluck('production_date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->toArray();

        // Get selected date (default to most recent)
        $selectedDate = $request->input('date', $productionDates[0] ?? null);

        // Get batches for selected date
        $batches = [];
        $checkpoints = [];
        $totalQty = 0;

        if ($selectedDate) {
            $batches = $checksheet->batches()
                ->where('production_date', $selectedDate)
                ->orderBy('letter_code')
                ->get()
                ->map(fn($batch) => [
                    'id' => $batch->id,
                    'letter_code' => $batch->letter_code,
                    'material_lot_number' => $batch->material_lot_number,
                    'qr_code' => $batch->qr_code,
                    'produce_qty' => $batch->produce_qty,
                    'job_number' => $batch->job_number,
                    'remarks' => $batch->remarks,
                ]);

            $totalQty = $checksheet->batches()
                ->where('production_date', $selectedDate)
                ->sum('produce_qty');

            // Get checkpoints for selected date (always 4)
            $existingCheckpoints = $checksheet->checkpoints()
                ->where('production_date', $selectedDate)
                ->orderBy('checkpoint_number')
                ->get()
                ->keyBy('checkpoint_number');

            for ($i = 1; $i <= 4; $i++) {
                $cp = $existingCheckpoints->get($i);
                $checkpoints[] = [
                    'id' => $cp?->id,
                    'checkpoint_number' => $i,
                    'position_label' => MagnetismCheckpoint::POSITION_LABELS[$i],
                    'samples_first' => $cp ? $cp->first_samples : [null, null, null, null, null],
                    'operator_first' => $cp?->operator_first,
                    'judgment_first' => $cp?->judgment_first,
                    'samples_last' => $cp ? $cp->last_samples : [null, null, null, null, null],
                    'operator_last' => $cp?->operator_last,
                    'judgment_last' => $cp?->judgment_last,
                    'checked_by' => $cp?->checked_by,
                ];
            }
        }

        return Inertia::render('Magnetism/Show', [
            'checksheet' => [
                'id' => $checksheet->id,
                'item_code' => $checksheet->item_code,
                'item_name' => $checksheet->item_name,
                'machine_no' => $checksheet->machine_no,
                'month' => $checksheet->month,
                'year' => $checksheet->year,
                'month_year_display' => $checksheet->month_year_display,
            ],
            'productionDates' => $productionDates,
            'selectedDate' => $selectedDate,
            'batches' => $batches,
            'totalQty' => $totalQty,
            'checkpoints' => $checkpoints,
            'teslaStandard' => [
                'min' => MagnetismCheckpoint::TESLA_MIN,
                'max' => MagnetismCheckpoint::TESLA_MAX,
            ],
        ]);
    }

    /**
     * Show the form for editing the checksheet header.
     */
    public function edit(MagnetismChecksheet $magnetism_checksheet)
    {
        return Inertia::render('Magnetism/Edit', [
            'checksheet' => [
                'id' => $magnetism_checksheet->id,
                'item_code' => $magnetism_checksheet->item_code,
                'item_name' => $magnetism_checksheet->item_name,
                'machine_no' => $magnetism_checksheet->machine_no,
                'month' => $magnetism_checksheet->month,
                'year' => $magnetism_checksheet->year,
            ],
        ]);
    }

    /**
     * Update the checksheet header.
     */
    public function update(MagnetismCheckSheetRequest $request, MagnetismChecksheet $magnetism_checksheet)
    {
        $magnetism_checksheet->update($request->validated());

        return redirect()->route('magnetism-checksheet.show', $magnetism_checksheet->id)
            ->with('success', 'Checksheet updated successfully.');
    }

    /**
     * Remove the checksheet.
     */
    public function destroy(MagnetismChecksheet $magnetism_checksheet)
    {
        $magnetism_checksheet->delete();

        return redirect()->route('magnetism-checksheet.index')
            ->with('success', 'Checksheet deleted successfully.');
    }

    /**
     * Store a new batch.
     */
    public function storeBatch(MagnetismBatchRequest $request, MagnetismChecksheet $magnetism_checksheet)
    {
        $data = $request->validated();
        $data['checksheet_id'] = $magnetism_checksheet->id;

        MagnetismBatch::create($data);

        return redirect()->route('magnetism-checksheet.show', [
            'magnetism_checksheet' => $magnetism_checksheet->id,
            'date' => $data['production_date'],
        ])->with('success', 'Batch added successfully.');
    }

    /**
     * Update a batch.
     */
    public function updateBatch(MagnetismBatchRequest $request, MagnetismChecksheet $magnetism_checksheet, MagnetismBatch $batch)
    {
        $batch->update($request->validated());

        return redirect()->route('magnetism-checksheet.show', [
            'magnetism_checksheet' => $magnetism_checksheet->id,
            'date' => $batch->production_date->format('Y-m-d'),
        ])->with('success', 'Batch updated successfully.');
    }

    /**
     * Delete a batch.
     */
    public function destroyBatch(MagnetismChecksheet $magnetism_checksheet, MagnetismBatch $batch)
    {
        $date = $batch->production_date->format('Y-m-d');
        $batch->delete();

        return redirect()->route('magnetism-checksheet.show', [
            'magnetism_checksheet' => $magnetism_checksheet->id,
            'date' => $date,
        ])->with('success', 'Batch deleted successfully.');
    }

    /**
     * Update checkpoints for a production date (bulk save).
     */
    public function updateCheckpoints(Request $request, MagnetismChecksheet $magnetism_checksheet)
    {
        $request->validate([
            'production_date' => ['required', 'date'],
            'checkpoints' => ['required', 'array', 'size:4'],
            'checkpoints.*.checkpoint_number' => ['required', 'integer', 'min:1', 'max:4'],
            'checkpoints.*.samples_first' => ['nullable', 'array', 'size:5'],
            'checkpoints.*.samples_first.*' => ['nullable', 'numeric'],
            'checkpoints.*.operator_first' => ['nullable', 'string', 'max:100'],
            'checkpoints.*.judgment_first' => ['nullable', 'string', 'in:OK,NG'],
            'checkpoints.*.samples_last' => ['nullable', 'array', 'size:5'],
            'checkpoints.*.samples_last.*' => ['nullable', 'numeric'],
            'checkpoints.*.operator_last' => ['nullable', 'string', 'max:100'],
            'checkpoints.*.judgment_last' => ['nullable', 'string', 'in:OK,NG'],
            'checkpoints.*.checked_by' => ['nullable', 'string', 'max:100'],
        ]);

        $productionDate = $request->input('production_date');

        DB::transaction(function () use ($request, $magnetism_checksheet, $productionDate) {
            foreach ($request->input('checkpoints') as $cpData) {
                $checkpoint = MagnetismCheckpoint::updateOrCreate(
                    [
                        'checksheet_id' => $magnetism_checksheet->id,
                        'production_date' => $productionDate,
                        'checkpoint_number' => $cpData['checkpoint_number'],
                    ],
                    [
                        'sample1_first' => $cpData['samples_first'][0] ?? null,
                        'sample2_first' => $cpData['samples_first'][1] ?? null,
                        'sample3_first' => $cpData['samples_first'][2] ?? null,
                        'sample4_first' => $cpData['samples_first'][3] ?? null,
                        'sample5_first' => $cpData['samples_first'][4] ?? null,
                        'operator_first' => $cpData['operator_first'] ?? null,
                        'judgment_first' => $cpData['judgment_first'] ?? null,
                        'sample1_last' => $cpData['samples_last'][0] ?? null,
                        'sample2_last' => $cpData['samples_last'][1] ?? null,
                        'sample3_last' => $cpData['samples_last'][2] ?? null,
                        'sample4_last' => $cpData['samples_last'][3] ?? null,
                        'sample5_last' => $cpData['samples_last'][4] ?? null,
                        'operator_last' => $cpData['operator_last'] ?? null,
                        'judgment_last' => $cpData['judgment_last'] ?? null,
                        'checked_by' => $cpData['checked_by'] ?? null,
                    ]
                );
            }
        });

        return redirect()->route('magnetism-checksheet.show', [
            'magnetism_checksheet' => $magnetism_checksheet->id,
            'date' => $productionDate,
        ])->with('success', 'Checkpoints saved successfully.');
    }

    /**
     * Get next available letter code (for new lots).
     */
    public function nextLetter(Request $request, MagnetismChecksheet $magnetism_checksheet)
    {
        $letter = MagnetismBatch::getNextAvailableLetter($magnetism_checksheet->id);

        return response()->json(['letter' => $letter]);
    }

    /**
     * Get letter code for a material lot number.
     * Returns existing letter if lot exists, or next available letter if new.
     */
    public function getLetterForLot(Request $request, MagnetismChecksheet $magnetism_checksheet)
    {
        $request->validate(['material_lot_number' => ['required', 'string', 'max:50']]);
        
        $materialLotNumber = $request->input('material_lot_number');
        $letter = MagnetismBatch::getLetterForMaterialLot(
            $magnetism_checksheet->id,
            $materialLotNumber
        );

        // Check if this is an existing lot
        $isExisting = MagnetismBatch::where('checksheet_id', $magnetism_checksheet->id)
            ->where('material_lot_number', $materialLotNumber)
            ->exists();

        return response()->json([
            'letter' => $letter,
            'is_existing_lot' => $isExisting,
        ]);
    }

    /**
     * Show import form.
     */
    public function importForm()
    {
        return Inertia::render('Magnetism/Import');
    }

    /**
     * Handle import (legacy - redirects to preview).
     */
    public function import(Request $request)
    {
        return $this->importPreview($request);
    }

    /**
     * Preview import - Phase 1: Parse file and detect duplicates.
     */
    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'item_code' => ['required', 'string', 'max:50'],
            'item_name' => ['required', 'string', 'max:100'],
            'machine_no' => ['required', 'string', 'max:50'],
        ]);

        try {
            // Store file temporarily
            $file = $request->file('file');
            $tempPath = $file->store('temp/magnetism-imports');
            $fullPath = Storage::path($tempPath);

            // Store file path in session for execute phase
            session(['magnetism_import_file' => $tempPath]);
            session(['magnetism_import_item_code' => $request->input('item_code')]);
            session(['magnetism_import_item_name' => $request->input('item_name')]);
            session(['magnetism_import_machine_no' => $request->input('machine_no')]);

            // Run preview
            $importer = new MagnetismChecksheetImport();
            $preview = $importer->preview(
                $fullPath,
                $request->input('item_code'),
                $request->input('item_name'),
                $request->input('machine_no')
            );

            return response()->json([
                'success' => true,
                'preview' => $preview,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to process file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute import - Phase 2: Create/update records.
     */
    public function importExecute(Request $request)
    {
        $request->validate([
            'update_duplicates' => ['nullable', 'boolean'],
        ]);

        // Get stored file path from session
        $tempPath = session('magnetism_import_file');
        $itemCode = session('magnetism_import_item_code');
        $itemName = session('magnetism_import_item_name');
        $machineNo = session('magnetism_import_machine_no');

        if (!$tempPath || !Storage::exists($tempPath)) {
            return response()->json([
                'success' => false,
                'error' => 'Import session expired. Please upload the file again.',
            ], 400);
        }

        try {
            $fullPath = Storage::path($tempPath);
            $updateDuplicates = $request->boolean('update_duplicates', false);

            // Run execute
            $importer = new MagnetismChecksheetImport();
            $results = $importer->execute(
                $fullPath,
                $itemCode,
                $itemName,
                $machineNo,
                $updateDuplicates
            );

            // Clean up temp file
            Storage::delete($tempPath);
            session()->forget([
                'magnetism_import_file',
                'magnetism_import_item_code',
                'magnetism_import_item_name',
                'magnetism_import_machine_no',
            ]);

            // Calculate totals for message
            $totalImported = $results['batches_imported'] + $results['checkpoints_imported'];
            $totalUpdated = $results['batches_updated'] + $results['checkpoints_updated'];
            $totalSkipped = $results['batches_skipped'] + $results['checkpoints_skipped'];

            $message = "Import completed: {$totalImported} created";
            if ($totalUpdated > 0) $message .= ", {$totalUpdated} updated";
            if ($totalSkipped > 0) $message .= ", {$totalSkipped} skipped";

            // Get first checksheet ID for redirect
            $redirectId = null;
            if (!empty($results['checksheets_created'])) {
                $redirectId = $results['checksheets_created'][0]['id'];
            } else {
                // Find existing checksheet
                $checksheet = MagnetismChecksheet::where('item_code', $itemCode)
                    ->where('machine_no', $machineNo)
                    ->first();
                $redirectId = $checksheet?->id;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'results' => $results,
                'redirect_id' => $redirectId,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to import: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export checksheet to Excel.
     */
    public function export(MagnetismChecksheet $magnetism_checksheet)
    {
        // Export logic will be implemented with Maatwebsite/Excel
        // For now, return a placeholder response
        return response()->json(['message' => 'Export functionality coming soon.']);
    }
}
