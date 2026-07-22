<?php

namespace App\Http\Controllers;

use App\Imports\TorqueChecksheetImport;
use App\Models\TorqueRecord;
use App\Services\ActivityService;
use App\Services\ApprovalNotificationService;
use App\Services\ApprovalWorkflowService;
use App\Services\DuplicateRecordGuard;
use App\Support\SpreadsheetImportSecurity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class TorqueRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = TorqueRecord::query()->with('readings');

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
                'time_pm' => $r->time_pm,
                'readings' => $r->readings->values(),
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
    ) {
        $data = $this->validatedData($request);
        $readingRows = $this->normalizeReadings($data['readings']);
        unset($data['readings']);
        $data = $this->withLegacyReadingValues($data, $readingRows);

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
            function () use ($data, $readingRows) {
                $record = TorqueRecord::create($data);
                $record->readings()->createMany($readingRows);

                return $record->load('readings');
            }
        );

        if ($rec->status === 'pending') {
            $approvalNotificationService->notifyApprovers($rec, 'new_submission', 'torque');
        }

        ActivityService::log(
            'create',
            "Created torque record for {$rec->model_series}",
            $rec,
            $this->recordSnapshot($rec),
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
            'record' => $torque_record->load('readings'),
        ]);
    }

    public function edit(TorqueRecord $torque_record)
    {
        return Inertia::render('TorqueRecords/Edit', [
            'record' => $torque_record->load('readings'),
        ]);
    }

    public function update(Request $request, TorqueRecord $torque_record)
    {
        $data = $this->validatedData($request);
        $readingRows = $this->normalizeReadings($data['readings']);
        unset($data['readings']);
        $data = $this->withLegacyReadingValues($data, $readingRows);

        $before = $this->recordSnapshot($torque_record->load('readings'));

        DB::transaction(function () use ($torque_record, $data, $readingRows) {
            $torque_record->update($data);
            $torque_record->readings()->delete();
            $torque_record->readings()->createMany($readingRows);
        });

        $torque_record->refresh()->load('readings');

        ActivityService::logSnapshotUpdate(
            $torque_record,
            $before,
            $this->recordSnapshot($torque_record),
            "Updated torque record for {$torque_record->model_series}",
            'torque'
        );

        return redirect()->route('torque-records.show', $torque_record->id)->with('success', 'Record updated.');
    }

    public function destroy(TorqueRecord $torque_record)
    {
        $recordData = $this->recordSnapshot($torque_record->load('readings'));
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
        $pendingRecords = TorqueRecord::with('readings')->where('status', 'pending')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('TorqueRecords/Approval', [
            'pendingRecords' => $pendingRecords,
        ]);
    }

    public function bulkApprove(Request $request, ApprovalNotificationService $approvalNotificationService)
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

        $approvalNotificationService->markRecordsActed($records, 'torque');

        return redirect()->route('torque-records.approval')
            ->with('success', $records->count().' torque record(s) approved successfully.');
    }

    public function bulkReject(Request $request, ApprovalNotificationService $approvalNotificationService)
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

        $approvalNotificationService->markRecordsActed($records, 'torque');

        return redirect()->route('torque-records.approval')
            ->with('success', $records->count().' torque record(s) rejected successfully.');
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
            'file' => SpreadsheetImportSecurity::rules(),
        ]);

        $file = $request->file('file');
        $tempPath = null;

        try {
            SpreadsheetImportSecurity::delete(session('torque_import_file'));
            [$tempPath, $fullPath] = SpreadsheetImportSecurity::store($file, 'torque');

            $import = new TorqueChecksheetImport();
            $results = $import->preview($fullPath);

            // Store the temp file path in session for execute phase
            session(['torque_import_file' => $tempPath]);

            return response()->json([
                'success' => true,
                'preview' => $results,
            ]);
        } catch (\Throwable $e) {
            SpreadsheetImportSecurity::delete($tempPath);
            $correlationId = SpreadsheetImportSecurity::reportFailure('torque.preview', $e);

            return response()->json([
                'success' => false,
                'error' => SpreadsheetImportSecurity::browserError($correlationId),
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

        if (! $tempPath) {
            return response()->json([
                'success' => false,
                'error' => 'No file to import. Please upload a file first.',
            ], 400);
        }

        $fullPath = SpreadsheetImportSecurity::resolve($tempPath);

        if ($fullPath === null) {
            SpreadsheetImportSecurity::delete($tempPath);
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

            $message = "Import completed: {$results['imported']} created";
            if ($results['updated'] > 0) {
                $message .= ", {$results['updated']} updated";
            }
            if ($results['skipped'] > 0) {
                $message .= ", {$results['skipped']} skipped";
            }
            if (count($results['errors']) > 0) {
                $message .= ', '.count($results['errors']).' errors';
            }

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
        } catch (\Throwable $e) {
            $correlationId = SpreadsheetImportSecurity::reportFailure('torque.execute', $e);

            return response()->json([
                'success' => false,
                'error' => SpreadsheetImportSecurity::browserError($correlationId),
            ], 500);
        } finally {
            SpreadsheetImportSecurity::delete($tempPath);
            session()->forget('torque_import_file');
        }
    }

    /**
     * Direct import (single step, for simple imports without preview)
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => SpreadsheetImportSecurity::rules(),
            'update_duplicates' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('file');
        $tempPath = null;

        try {
            [$tempPath, $fullPath] = SpreadsheetImportSecurity::store($file, 'torque');

            $updateDuplicates = $request->boolean('update_duplicates', false);

            $import = new TorqueChecksheetImport();
            $results = $import->execute($fullPath, $updateDuplicates);

            $message = "Import completed: {$results['imported']} created";
            if ($results['updated'] > 0) {
                $message .= ", {$results['updated']} updated";
            }
            if ($results['skipped'] > 0) {
                $message .= ", {$results['skipped']} skipped";
            }
            if (count($results['errors']) > 0) {
                $message .= ', '.count($results['errors']).' errors';
            }

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
        } catch (\Throwable $e) {
            $correlationId = SpreadsheetImportSecurity::reportFailure('torque.direct', $e);

            return Inertia::render('TorqueRecords/Import', [
                'error' => SpreadsheetImportSecurity::browserError($correlationId),
            ]);
        } finally {
            SpreadsheetImportSecurity::delete($tempPath);
        }
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'date' => ['nullable', 'date'],
            'model_series' => ['nullable', 'string', 'max:100'],
            'driver_model' => ['nullable', 'string', 'max:100'],
            'driver_type' => ['nullable', 'string', 'max:100'],
            'line_assigned' => ['nullable', 'string', 'max:100'],
            'control_no' => ['nullable', 'string', 'max:50'],
            'screw_type' => ['nullable', 'string', 'max:50'],
            'process_assigned' => ['nullable', 'string', 'max:100'],
            'person_in_charge' => ['nullable', 'string', 'max:100'],
            'time_am' => ['nullable', 'regex:/^(?:[01]?\\d|2[0-3]):[0-5]\\d$/'],
            'time_pm' => ['nullable', 'regex:/^(?:[01]?\\d|2[0-3]):[0-5]\\d$/'],
            'readings' => ['required', 'array'],
            'readings.am' => ['present', 'array', 'max:8'],
            'readings.am.*' => ['array'],
            'readings.am.*.torque_value' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'readings.pm' => ['present', 'array', 'max:8'],
            'readings.pm.*' => ['array'],
            'readings.pm.*.torque_value' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'col_remarks' => ['nullable', 'string', 'max:100'],
            'checked_by' => ['nullable', 'string', 'max:100'],
        ], [
            'readings.am.max' => 'Morning readings may not contain more than 8 torque values.',
            'readings.pm.max' => 'Afternoon readings may not contain more than 8 torque values.',
            'readings.am.*.torque_value.numeric' => 'Every morning torque reading must be numeric.',
            'readings.pm.*.torque_value.numeric' => 'Every afternoon torque reading must be numeric.',
        ]);

        $readings = $this->normalizeReadings($data['readings']);
        $errors = [];

        if ($readings === []) {
            $errors['readings'] = 'Enter at least one torque reading.';
        }

        if ($this->hasPeriod($readings, 'AM') && empty($data['time_am'])) {
            $errors['time_am'] = 'Morning time is required when morning readings are entered.';
        }

        if ($this->hasPeriod($readings, 'PM') && empty($data['time_pm'])) {
            $errors['time_pm'] = 'Afternoon time is required when afternoon readings are entered.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $data;
    }

    private function normalizeReadings(array $readings): array
    {
        $rows = [];

        foreach (['am' => 'AM', 'pm' => 'PM'] as $key => $period) {
            $readingNo = 1;

            foreach ($readings[$key] ?? [] as $reading) {
                $value = $reading['torque_value'] ?? null;

                if ($value === null || trim((string) $value) === '') {
                    continue;
                }

                $rows[] = [
                    'period' => $period,
                    'reading_no' => $readingNo++,
                    'torque_value' => $value,
                ];
            }
        }

        return $rows;
    }

    private function hasPeriod(array $readings, string $period): bool
    {
        foreach ($readings as $reading) {
            if ($reading['period'] === $period) {
                return true;
            }
        }

        return false;
    }

    private function withLegacyReadingValues(array $data, array $readings): array
    {
        $data['torque_am'] = null;
        $data['torque_pm'] = null;

        foreach ($readings as $reading) {
            $legacyKey = $reading['period'] === 'AM' ? 'torque_am' : 'torque_pm';

            if ($data[$legacyKey] === null) {
                $data[$legacyKey] = $reading['torque_value'];
            }
        }

        return $data;
    }

    private function recordSnapshot(TorqueRecord $record): array
    {
        $snapshot = ActivityService::snapshot($record);
        $snapshot['torque_readings'] = $record->readings
            ->map(fn ($reading) => "{$reading->period} {$reading->reading_no}: {$reading->torque_value} N·m")
            ->implode('; ');

        return $snapshot;
    }
}
