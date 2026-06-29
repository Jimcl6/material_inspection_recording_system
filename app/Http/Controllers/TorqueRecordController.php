<?php

namespace App\Http\Controllers;

use App\Models\TorqueRecord;
use App\Models\ApprovalNotification;
use App\Imports\TorqueChecksheetImport;
use App\Services\ActivityService;
use App\Services\ApprovalNotificationService;
use App\Services\ApprovalWorkflowService;
use App\Services\DuplicateRecordGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TorqueRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = TorqueRecord::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('model_series', 'like', "%{$search}%")
                  ->orWhere('driver_model', 'like', "%{$search}%")
                  ->orWhere('control_no', 'like', "%{$search}%")
                  ->orWhere('screw_type', 'like', "%{$search}%")
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

        // Driver model filter
        if ($request->filled('driver_model')) {
            $query->where('driver_model', $request->driver_model);
        }

        // Line assigned filter
        if ($request->filled('line_assigned')) {
            $query->where('line_assigned', $request->line_assigned);
        }

        if (config('features.approvals', false) && $request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $records = $query->orderByDesc('id')->paginate(10)->withQueryString()->through(function ($r) {
            return [
                'id' => $r->id,
                'date' => $r->date,
                'model_series' => $r->model_series,
                'driver_model' => $r->driver_model,
                'driver_type' => $r->driver_type,
                'line_assigned' => $r->line_assigned,
                'control_no' => $r->control_no,
                'screw_type' => $r->screw_type,
                'process_assigned' => $r->process_assigned,
                'person_in_charge' => $r->person_in_charge,
                'time_am' => $r->time_am,
                'torque_am' => $r->torque_am,
                'time_pm' => $r->time_pm,
                'torque_pm' => $r->torque_pm,
                'col_remarks' => $r->col_remarks,
                'checked_by' => $r->checked_by,
                'status' => $r->status,
                'submitted_at' => $r->submitted_at,
                'approved_at' => $r->approved_at,
            ];
        });

        return Inertia::render('TorqueRecords/Index', [
            'records' => $records,
            'filters' => $request->only(['search', 'date_from', 'date_to', 'driver_model', 'line_assigned', 'status']),
            'driverModels' => TorqueRecord::whereNotNull('driver_model')
                ->where('driver_model', '!=', '')
                ->distinct()
                ->orderBy('driver_model')
                ->pluck('driver_model'),
            'lineOptions' => TorqueRecord::whereNotNull('line_assigned')
                ->where('line_assigned', '!=', '')
                ->distinct()
                ->orderBy('line_assigned')
                ->pluck('line_assigned'),
        ]);
    }

    public function create()
    {
        return Inertia::render('TorqueRecords/Create');
    }

    public function store(
        Request $request,
        DuplicateRecordGuard $duplicateRecordGuard,
        ApprovalWorkflowService $approvalWorkflowService,
        ApprovalNotificationService $approvalNotificationService
    )
    {
        $data = $request->validate([
            'date' => ['nullable','date'],
            'model_series'=> ['nullable','string','max:100'],
            'driver_model' => ['nullable','string','max:100'],
            'driver_type' => ['nullable','string','max:100'],
            'line_assigned' => ['nullable','string','max:100'],
            'control_no' => ['nullable','string','max:50'],
            'screw_type' => ['nullable','string','max:50'],
            'process_assigned' => ['nullable','string','max:100'],
            'person_in_charge' => ['nullable','string','max:100'],
            'time_am' => ['nullable','regex:/^(?:[01]?\\d|2[0-3]):[0-5]\\d$/'],
            'torque_am' => ['nullable','string','max:20'],
            'time_pm' => ['nullable','regex:/^(?:[01]?\\d|2[0-3]):[0-5]\\d$/'],
            'torque_pm' => ['nullable','string','max:20'],
            'col_remarks' => ['nullable','string','max:100'],
            'checked_by' => ['nullable','string','max:100'],
        ]);

        $data = array_merge($data, $approvalWorkflowService->initialState());

        $rec = $duplicateRecordGuard->create(
            TorqueRecord::class,
            [
                'date' => $data['date'] ?? null,
                'screw_type' => $data['screw_type'] ?? null,
                'process_assigned' => $data['process_assigned'] ?? null,
                'line_assigned' => $data['line_assigned'] ?? null,
            ],
            'torque record for this date, screw type, process, and line',
            fn () => TorqueRecord::create($data)
        );

        if ($rec->status === 'pending') {
            $approvalNotificationService->notifyApprovers($rec, 'new_submission', 'torque');
        }

        ActivityService::log(
            'create',
            "Created torque record for {$rec->model_series}",
            $rec,
            $rec->toArray(),
            'torque'
        );

        $message = $rec->status === 'pending'
            ? 'Record created and submitted for approval.'
            : 'Record created.';

        return redirect()->route('torque-records.show', $rec->id)->with('success', $message);
    }

    public function show(TorqueRecord $torque_record)
    {
        return Inertia::render('TorqueRecords/Show', [
            'record' => $torque_record,
        ]);
    }

    public function edit(TorqueRecord $torque_record)
    {
        return Inertia::render('TorqueRecords/Edit', [
            'record' => $torque_record,
        ]);
    }

    public function update(Request $request, TorqueRecord $torque_record)
    {
        $data = $request->validate([
            'date' => ['nullable','date'],
            'model_series'=> ['nullable','string','max:100'],
            'driver_model' => ['nullable','string','max:100'],
            'driver_type' => ['nullable','string','max:100'],
            'line_assigned' => ['nullable','string','max:100'],
            'control_no' => ['nullable','string','max:50'],
            'screw_type' => ['nullable','string','max:50'],
            'process_assigned' => ['nullable','string','max:100'],
            'person_in_charge' => ['nullable','string','max:100'],
            'time_am' => ['nullable','regex:/^(?:[01]?\\d|2[0-3]):[0-5]\\d$/'],
            'torque_am' => ['nullable','string','max:20'],
            'time_pm' => ['nullable','regex:/^(?:[01]?\\d|2[0-3]):[0-5]\\d$/'],
            'torque_pm' => ['nullable','string','max:20'],
            'col_remarks' => ['nullable','string','max:100'],
            'checked_by' => ['nullable','string','max:100'],
        ]);

        $before = ActivityService::snapshot($torque_record);
        $torque_record->update($data);

        ActivityService::logSnapshotUpdate(
            $torque_record,
            $before,
            ActivityService::snapshot($torque_record),
            "Updated torque record for {$torque_record->model_series}",
            'torque'
        );

        return redirect()->route('torque-records.show', $torque_record->id)->with('success', 'Record updated.');
    }

    public function destroy(TorqueRecord $torque_record)
    {
        $recordData = $torque_record->toArray();
        $modelSeries = $torque_record->model_series;
        
        $torque_record->delete();

        ActivityService::log(
            'delete',
            "Deleted torque record for {$modelSeries}",
            null,
            $recordData,
            'torque'
        );

        return redirect()->route('torque-records.index')->with('success', 'Record deleted.');
    }

    public function approval(): \Inertia\Response
    {
        $pendingRecords = TorqueRecord::where('status', 'pending')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('TorqueRecords/Approval', [
            'pendingRecords' => $pendingRecords,
        ]);
    }

    public function bulkApprove(Request $request)
    {
        $data = $request->validate([
            'record_ids' => ['required', 'array', 'min:1'],
            'record_ids.*' => ['integer', 'exists:torque_records,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $records = TorqueRecord::whereIn('id', $data['record_ids'])
            ->where('status', 'pending')
            ->get();

        foreach ($records as $record) {
            $previousStatus = $record->status;

            $record->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approval_notes' => $data['notes'] ?? null,
            ]);

            ActivityService::logApprove($record, [
                'previous_status' => $previousStatus,
                'new_status' => 'approved',
                'notes' => $data['notes'] ?? null,
                'source' => 'bulk_approval',
            ]);
        }

        $this->markApprovalNotificationsActed($records->pluck('id')->all());

        return redirect()->route('torque-records.approval')
            ->with('success', $records->count() . ' torque record(s) approved successfully.');
    }

    public function bulkReject(Request $request)
    {
        $data = $request->validate([
            'record_ids' => ['required', 'array', 'min:1'],
            'record_ids.*' => ['integer', 'exists:torque_records,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $records = TorqueRecord::whereIn('id', $data['record_ids'])
            ->where('status', 'pending')
            ->get();

        foreach ($records as $record) {
            $previousStatus = $record->status;

            $record->update([
                'status' => 'rejected',
                'approved_at' => now(),
                'approval_notes' => $data['notes'] ?? null,
            ]);

            ActivityService::logReject($record, $data['notes'] ?? '', [
                'previous_status' => $previousStatus,
                'new_status' => 'rejected',
                'notes' => $data['notes'] ?? null,
                'source' => 'bulk_approval',
            ]);
        }

        $this->markApprovalNotificationsActed($records->pluck('id')->all());

        return redirect()->route('torque-records.approval')
            ->with('success', $records->count() . ' torque record(s) rejected successfully.');
    }

    private function markApprovalNotificationsActed(array $recordIds): void
    {
        if ($recordIds === []) {
            return;
        }

        ApprovalNotification::where('module', 'torque')
            ->where('approvable_type', TorqueRecord::class)
            ->whereIn('approvable_id', $recordIds)
            ->where('status', 'pending')
            ->update(['status' => 'acted']);
    }

    /**
     * Show the import form
     */
    public function importForm()
    {
        return Inertia::render('TorqueRecords/Import');
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

            $import = new TorqueChecksheetImport();
            $results = $import->preview($fullPath);

            // Store the temp file path in session for execute phase
            session(['torque_import_file' => $tempPath]);

            return response()->json([
                'success' => true,
                'preview' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Torque Import Preview failed', [
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

        $tempPath = session('torque_import_file');

        if (!$tempPath) {
            return response()->json([
                'success' => false,
                'error' => 'No file to import. Please upload a file first.',
            ], 400);
        }

        $fullPath = storage_path('app/' . $tempPath);

        if (!file_exists($fullPath)) {
            session()->forget('torque_import_file');
            return response()->json([
                'success' => false,
                'error' => 'Import file expired. Please upload again.',
            ], 400);
        }

        try {
            $updateDuplicates = $request->boolean('update_duplicates', false);

            $import = new TorqueChecksheetImport();
            $results = $import->execute($fullPath, $updateDuplicates);

            // Clean up temp file
            @unlink($fullPath);
            session()->forget('torque_import_file');

            $message = "Import completed: {$results['imported']} created";
            if ($results['updated'] > 0) $message .= ", {$results['updated']} updated";
            if ($results['skipped'] > 0) $message .= ", {$results['skipped']} skipped";
            if (count($results['errors']) > 0) $message .= ", " . count($results['errors']) . " errors";

            ActivityService::logImport('torque', $results['imported'] + $results['updated'], [
                'imported' => $results['imported'],
                'updated' => $results['updated'],
                'skipped' => $results['skipped'],
                'errors' => count($results['errors']),
            ]);

            return response()->json([
                'success' => true,
                'results' => $results,
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            Log::error('Torque Import Execute failed', [
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
     * Direct import (single step, for simple imports without preview)
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'update_duplicates' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('file');

        try {
            $tempPath = $file->store('temp');
            $fullPath = storage_path('app/' . $tempPath);

            $updateDuplicates = $request->boolean('update_duplicates', false);

            $import = new TorqueChecksheetImport();
            $results = $import->execute($fullPath, $updateDuplicates);

            @unlink($fullPath);

            $message = "Import completed: {$results['imported']} created";
            if ($results['updated'] > 0) $message .= ", {$results['updated']} updated";
            if ($results['skipped'] > 0) $message .= ", {$results['skipped']} skipped";
            if (count($results['errors']) > 0) $message .= ", " . count($results['errors']) . " errors";

            ActivityService::logImport('torque', $results['imported'] + $results['updated'], [
                'imported' => $results['imported'],
                'updated' => $results['updated'],
                'skipped' => $results['skipped'],
                'errors' => count($results['errors']),
            ]);

            return Inertia::render('TorqueRecords/Import', [
                'import_results' => $results,
                'success' => $message,
            ]);

        } catch (\Exception $e) {
            Log::error('Torque Import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Inertia::render('TorqueRecords/Import', [
                'error' => 'Error importing file: ' . $e->getMessage(),
            ]);
        }
    }
}
