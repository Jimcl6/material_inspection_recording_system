<?php

namespace App\Http\Controllers;

use App\Models\TempRecord;
use App\Imports\TempRecordImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TempRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = TempRecord::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('model_series', 'like', "%{$search}%")
                  ->orWhere('solder_model', 'like', "%{$search}%")
                  ->orWhere('control_no', 'like', "%{$search}%")
                  ->orWhere('person_in_charge', 'like', "%{$search}%")
                  ->orWhere('checked_by', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Equipment type filter
        if ($request->filled('equipment_type')) {
            $query->where('equipment_type', $request->equipment_type);
        }

        // Line assigned filter
        if ($request->filled('line_assigned')) {
            $query->where('line_assigned', $request->line_assigned);
        }

        $records = $query->orderByDesc('id')->paginate(10)->withQueryString()->through(function ($r) {
            return [
                'id' => $r->id,
                'date' => $r->date,
                'model_series' => $r->model_series,
                'solder_model' => $r->solder_model,
                'line_assigned' => $r->line_assigned,
                'control_no' => $r->control_no,
                'equipment_type' => $r->equipment_type,
                'process_assigned' => $r->process_assigned,
                'person_in_charge' => $r->person_in_charge,
                'time_am' => $r->time_am,
                'temp_am' => $r->temp_am,
                'time_pm' => $r->time_pm,
                'temp_pm' => $r->temp_pm,
                'checked_by' => $r->checked_by,
            ];
        });

        return Inertia::render('TempRecords/Index', [
            'records' => $records,
            'filters' => $request->only(['search', 'date_from', 'date_to', 'equipment_type', 'line_assigned']),
            'equipmentTypes' => TempRecord::whereNotNull('equipment_type')
                ->where('equipment_type', '!=', '')
                ->distinct()
                ->orderBy('equipment_type')
                ->pluck('equipment_type'),
            'lineOptions' => TempRecord::whereNotNull('line_assigned')
                ->where('line_assigned', '!=', '')
                ->distinct()
                ->orderBy('line_assigned')
                ->pluck('line_assigned'),
        ]);
    }

    public function create()
    {
        $equipmentTypes = TempRecord::query()->select('equipment_type')->whereNotNull('equipment_type')->distinct()->orderBy('equipment_type')->pluck('equipment_type');
        return Inertia::render('TempRecords/Create', [
            'equipmentTypes' => $equipmentTypes,
        ]);
    }

    public function store(Request $request)
    {
        $date = $this->parseDateInput($request->input('date'));
        if ($date !== null) { $request->merge(['date' => $date]); }
        $data = $request->validate([
            'date' => ['nullable','date'],
            'model_series' => ['required','string','max:100'],
            'solder_model' => ['nullable','string','max:100'],
            'line_assigned' => ['nullable','string','max:100'],
            'control_no' => ['nullable','string','max:50'],
            'equipment_type' => ['required','string','max:100'],
            'process_assigned' => ['nullable','string','max:100'],
            'person_in_charge' => ['nullable','string','max:100'],
            'time_am' => ['nullable','regex:/^(?:[01]?\\d|2[0-3]):[0-5]\\d$/'],
            'temp_am' => ['nullable','string','max:20'],
            'time_pm' => ['nullable','regex:/^(?:[01]?\\d|2[0-3]):[0-5]\\d$/'],
            'temp_pm' => ['nullable','string','max:20'],
            'col_remarks' => ['nullable','string','max:100'],
            'checked_by' => ['nullable','string','max:100'],
        ]);

        $rec = TempRecord::create($data);
        return redirect()->route('temp-records.show', $rec->id)->with('success', 'Record created.');
    }

    public function show(TempRecord $temp_record)
    {
        return Inertia::render('TempRecords/Show', [
            'record' => $temp_record,
        ]);
    }

    public function edit(TempRecord $temp_record)
    {
        $equipmentTypes = TempRecord::query()->select('equipment_type')->whereNotNull('equipment_type')->distinct()->orderBy('equipment_type')->pluck('equipment_type');
        return Inertia::render('TempRecords/Edit', [
            'record' => $temp_record,
            'equipmentTypes' => $equipmentTypes,
        ]);
    }

    public function update(Request $request, TempRecord $temp_record)
    {
        $date = $this->parseDateInput($request->input('date'));
        if ($date !== null) { $request->merge(['date' => $date]); }
        $data = $request->validate([
            'date' => ['nullable','date'],
            'model_series' => ['required','string','max:100'],
            'solder_model' => ['nullable','string','max:100'],
            'line_assigned' => ['nullable','string','max:100'],
            'control_no' => ['nullable','string','max:50'],
            'equipment_type' => ['required','string','max:100'],
            'process_assigned' => ['nullable','string','max:100'],
            'person_in_charge' => ['nullable','string','max:100'],
            'time_am' => ['nullable','regex:/^(?:[01]?\\d|2[0-3]):[0-5]\\d$/'],
            'temp_am' => ['nullable','string','max:20'],
            'time_pm' => ['nullable','regex:/^(?:[01]?\\d|2[0-3]):[0-5]\\d$/'],
            'temp_pm' => ['nullable','string','max:20'],
            'col_remarks' => ['nullable','string','max:100'],
            'checked_by' => ['nullable','string','max:100'],
        ]);

        $temp_record->update($data);
        return redirect()->route('temp-records.show', $temp_record->id)->with('success', 'Record updated.');
    }

    public function destroy(TempRecord $temp_record)
    {
        $temp_record->delete();
        return redirect()->route('temp-records.index')->with('success', 'Record deleted.');
    }

    protected function parseDateInput(?string $value): ?string
    {
        if (!$value) return null;
        $formats = [
            'd/m/Y H:i', 'd/m/Y', 'Y-m-d\TH:i', 'Y-m-d H:i', 'Y-m-d',
        ];
        foreach ($formats as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $value);
                if ($dt !== false) return $dt->format('Y-m-d H:i:s');
            } catch (\Exception $e) {}
        }
        // Fallback: let validator handle invalid format
        return null;
    }

    /**
     * Show the import form
     */
    public function importForm()
    {
        return Inertia::render('TempRecords/Import');
    }

    /**
     * Preview import - Phase 1: Parse file and detect duplicates
     */
    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'equipment_type' => ['nullable', 'string'],
        ]);

        try {
            $file = $request->file('file');
            $tempPath = $file->store('temp');
            $fullPath = storage_path('app/' . $tempPath);

            $equipmentType = $request->input('equipment_type');

            $import = new TempRecordImport();
            $results = $import->preview($fullPath, $equipmentType);

            // Store the temp file path in session for execute phase
            session(['temp_record_import_file' => $tempPath]);

            return response()->json([
                'success' => true,
                'preview' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Temp Record Import Preview failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to process file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute import - Phase 2: Create/update records based on user choice
     */
    public function importExecute(Request $request)
    {
        $request->validate([
            'equipment_type' => ['required', 'string'],
            'line_assigned' => ['nullable', 'string', 'max:100'],
            'process_assigned' => ['nullable', 'string', 'max:100'],
            'update_duplicates' => ['nullable', 'boolean'],
        ]);

        $tempPath = session('temp_record_import_file');

        if (!$tempPath) {
            return response()->json([
                'success' => false,
                'error' => 'No file to import. Please upload a file first.',
            ], 400);
        }

        $fullPath = storage_path('app/' . $tempPath);

        if (!file_exists($fullPath)) {
            session()->forget('temp_record_import_file');
            return response()->json([
                'success' => false,
                'error' => 'Import file expired. Please upload again.',
            ], 400);
        }

        try {
            $equipmentType = $request->input('equipment_type');
            $lineAssigned = $request->input('line_assigned');
            $processAssigned = $request->input('process_assigned');
            $updateDuplicates = $request->boolean('update_duplicates', false);

            $import = new TempRecordImport();
            $results = $import->execute($fullPath, $equipmentType, $lineAssigned, $processAssigned, $updateDuplicates);

            // Clean up temp file
            @unlink($fullPath);
            session()->forget('temp_record_import_file');

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
            Log::error('Temp Record Import Execute failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to import file: ' . $e->getMessage(),
            ], 500);
        }
    }
}
