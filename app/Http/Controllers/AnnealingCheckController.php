<?php

namespace App\Http\Controllers;

use App\Exports\AnnealingChecksExport;
use App\Http\Requests\ImportAnnealingCheckRequest;
use App\Http\Requests\IndexAnnealingCheckRequest;
use App\Http\Requests\StoreAnnealingCheckRequest;
use App\Http\Requests\UpdateAnnealingCheckRequest;
use App\Imports\AnnealingChecksWithHeadersImport;
use App\Models\AnnealingCheck;
use App\Services\ActivityService;
use App\Services\ApprovalNotificationService;
use App\Services\ApprovalWorkflowService;
use App\Services\DuplicateRecordGuard;
use App\Support\SpreadsheetImportSecurity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class AnnealingCheckController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexAnnealingCheckRequest $request): \Inertia\Response
    {
        $filters = $request->validated();
        $search = $filters['search'] ?? null;

        $annealingChecks = AnnealingCheck::query()
            ->with(['pic', 'checkedBy', 'verifiedBy'])
            ->when($search, function ($query, string $search): void {
                $escapedSearch = addcslashes($search, '\\%_');
                $pattern = "%{$escapedSearch}%";

                $query->where(function ($query) use ($pattern): void {
                    $query->where('item_code', 'like', $pattern)
                        ->orWhere('supplier_lot_number', 'like', $pattern)
                        ->orWhere('machine_number', 'like', $pattern);
                });
            })
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->where('annealing_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->where('annealing_date', '<=', $date))
            ->when(
                $filters['machine_number'] ?? null,
                fn ($query, string $machineNumber) => $query->where('machine_number', $machineNumber)
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('AnnealingChecks/Index', [
            'annealingChecks' => $annealingChecks,
            'filters' => $filters,
            'machineNumbers' => AnnealingCheck::whereNotNull('machine_number')
                ->where('machine_number', '!=', '')
                ->distinct()
                ->orderBy('machine_number')
                ->pluck('machine_number'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Inertia\Response
    {
        return Inertia::render('AnnealingChecks/Create', [
            'users' => \App\Models\User::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        StoreAnnealingCheckRequest $request,
        ApprovalWorkflowService $approvalWorkflowService,
        ApprovalNotificationService $approvalNotificationService,
        DuplicateRecordGuard $duplicateRecordGuard
    ): \Illuminate\Http\RedirectResponse {
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        $data = array_merge($data, $approvalWorkflowService->initialState());
        $approvalsEnabled = $data['status'] === 'pending';

        // Convert user names back to IDs for database storage
        // pic_id is required, so pass true as second parameter
        $data['pic_id'] = $this->convertNameToId($data['pic_id'] ?? null, true);
        $data['checked_by_id'] = $this->convertNameToId($data['checked_by_id'] ?? null);
        $data['verified_by_id'] = $this->convertNameToId($data['verified_by_id'] ?? null);

        $annealingCheck = $duplicateRecordGuard->create(
            AnnealingCheck::class,
            [
                'item_code' => $data['item_code'],
                'annealing_date' => $data['annealing_date'],
            ],
            'annealing check for this item code and annealing date',
            fn () => AnnealingCheck::create($data)
        );

        $approvalNotificationService->notifyApprovers($annealingCheck, 'new_submission', 'annealing');

        // Log activity
        ActivityService::log(
            'create',
            "Created annealing check for {$annealingCheck->item_code}",
            $annealingCheck,
            ['item_code' => $annealingCheck->item_code],
            'annealing'
        );

        return redirect()->route('annealing-checks.index')
            ->with(
                'success',
                $approvalsEnabled
                    ? 'Annealing check created successfully and submitted for approval.'
                    : 'Annealing check created successfully.'
            );
    }

    /**
     * Display the specified resource.
     */
    public function show(IndexAnnealingCheckRequest $request, AnnealingCheck $annealingCheck): \Inertia\Response
    {
        $annealingCheck->load(['pic', 'checkedBy', 'verifiedBy']);

        return Inertia::render('AnnealingChecks/Show', [
            'annealingCheck' => $annealingCheck,
            'filters' => $request->validated(),
        ]);
    }

    /**
     * Convert user name to user ID for database storage
     * If name doesn't exist in users table, use current user for required fields
     */
    private function convertNameToId($name, $isRequired = false)
    {
        if (empty($name)) {
            return $isRequired ? Auth::id() : null;
        }

        // If it's already a number, return as-is
        if (is_numeric($name)) {
            return (int) $name;
        }

        // Clean up the name - trim whitespace and convert to lowercase for comparison
        $cleanName = trim(strtolower($name));

        // Try to find user by name (case-insensitive)
        $user = \App\Models\User::whereRaw('LOWER(name) = ?', [$cleanName])->first();

        // Return user ID if found, otherwise use current user for required fields or null for optional
        return $user ? $user->id : ($isRequired ? Auth::id() : null);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(IndexAnnealingCheckRequest $request, AnnealingCheck $annealingCheck): \Inertia\Response
    {
        return Inertia::render('AnnealingChecks/Edit', [
            'annealingCheck' => $annealingCheck,
            'users' => \App\Models\User::select('id', 'name')->orderBy('name')->get(),
            'filters' => $request->validated(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnnealingCheckRequest $request, AnnealingCheck $annealingCheck): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        // If status is changing to approved, set approved_at
        if (isset($data['status']) && $data['status'] === 'approved' && $annealingCheck->status !== 'approved') {
            $data['approved_at'] = now();
        }

        // Convert user names back to IDs for database storage
        $data['pic_id'] = $this->convertNameToId($data['pic_id'] ?? null, true);
        $data['checked_by_id'] = $this->convertNameToId($data['checked_by_id'] ?? null);
        $data['verified_by_id'] = $this->convertNameToId($data['verified_by_id'] ?? null);

        $before = ActivityService::snapshot($annealingCheck);
        $annealingCheck->update($data);

        // Notify administrators and inspectors if status changed
        if (isset($data['status']) && $annealingCheck->wasChanged('status')) {
            if (class_exists('\App\Services\ApprovalNotificationService')) {
                $notificationService = app(ApprovalNotificationService::class);
                $notificationService->notifyApprovers($annealingCheck, 'update', 'annealing');
            }
        }

        // Log activity
        ActivityService::logSnapshotUpdate(
            $annealingCheck,
            $before,
            ActivityService::snapshot($annealingCheck),
            "Updated annealing check for {$annealingCheck->item_code}",
            'annealing',
            ['item_code' => $annealingCheck->item_code]
        );

        return redirect()->route('annealing-checks.index')
            ->with('success', 'Annealing check updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(AnnealingCheck $annealingCheck)
    {
        $itemName = $annealingCheck->item_code;
        $itemCode = $annealingCheck->item_code;

        $annealingCheck->delete();

        // Log activity
        ActivityService::log(
            'delete',
            "Deleted annealing check for {$itemName}",
            null,
            ['item_code' => $itemCode, 'item_name' => $itemName],
            'annealing'
        );

        return redirect()->route('annealing-checks.index')
            ->with('success', 'Annealing check deleted successfully!');
    }

    /**
     * Show the import form.
     */
    public function importForm(): \Inertia\Response
    {
        return Inertia::render('AnnealingChecks/Import');
    }

    /**
     * Import annealing checks from Excel file.
     */
    public function import(ImportAnnealingCheckRequest $request): \Inertia\Response
    {
        $file = $request->file('file');
        $overwrite = $request->boolean('overwrite', false);
        $tempPath = null;

        try {
            if ($overwrite) {
                // Delete existing records if overwrite is true
                AnnealingCheck::truncate();
            }

            $import = new AnnealingChecksWithHeadersImport();

            [$tempPath, $fullPath] = SpreadsheetImportSecurity::store($file, 'annealing');
            $import->import($fullPath);

            $results = $import->getResults();

            // Prepare summary message
            $totalImported = array_sum(array_column($results, 'imported'));
            $totalSkipped = array_sum(array_column($results, 'skipped'));
            $totalErrors = array_sum(array_map('count', array_column($results, 'errors')));

            $message = "Import completed: {$totalImported} imported";
            if ($totalSkipped > 0) {
                $message .= ", {$totalSkipped} skipped";
            }
            if ($totalErrors > 0) {
                $message .= ", {$totalErrors} errors";
            }

            return Inertia::render('AnnealingChecks/Import', [
                'import_results' => $results,
                'success' => $message,
            ]);
        } catch (\Throwable $e) {
            $correlationId = SpreadsheetImportSecurity::reportFailure('annealing.direct', $e);

            return Inertia::render('AnnealingChecks/Import')
                ->with('error', SpreadsheetImportSecurity::browserError($correlationId));
        } finally {
            SpreadsheetImportSecurity::delete($tempPath);
        }
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
            SpreadsheetImportSecurity::delete(session('annealing_import_file'));
            [$tempPath, $fullPath] = SpreadsheetImportSecurity::store($file, 'annealing');

            $import = new AnnealingChecksWithHeadersImport();
            $results = $import->preview($fullPath);

            // Store the temp file path in session for execute phase
            session(['annealing_import_file' => $tempPath]);

            return response()->json([
                'success' => true,
                'preview' => $results,
            ]);
        } catch (\Throwable $e) {
            SpreadsheetImportSecurity::delete($tempPath);
            $correlationId = SpreadsheetImportSecurity::reportFailure('annealing.preview', $e);

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

        $tempPath = session('annealing_import_file');

        if (! $tempPath) {
            return response()->json([
                'success' => false,
                'error' => 'No file to import. Please upload a file first.',
            ], 400);
        }

        $fullPath = SpreadsheetImportSecurity::resolve($tempPath);

        if ($fullPath === null) {
            SpreadsheetImportSecurity::delete($tempPath);
            session()->forget('annealing_import_file');

            return response()->json([
                'success' => false,
                'error' => 'Import file expired. Please upload again.',
            ], 400);
        }

        try {
            $updateDuplicates = $request->boolean('update_duplicates', false);

            $import = new AnnealingChecksWithHeadersImport();
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

            return response()->json([
                'success' => true,
                'results' => $results,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            $correlationId = SpreadsheetImportSecurity::reportFailure('annealing.execute', $e);

            return response()->json([
                'success' => false,
                'error' => SpreadsheetImportSecurity::browserError($correlationId),
            ], 500);
        } finally {
            SpreadsheetImportSecurity::delete($tempPath);
            session()->forget('annealing_import_file');
        }
    }

    /**
     * Export annealing checks to Excel file.
     */
    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new AnnealingChecksExport, 'annealing-checks-'.now()->format('Y-m-d').'.xlsx');
    }

    /**
     * Show the approval page for users with annealing approval permission.
     */
    public function approval(): \Inertia\Response
    {
        $user = Auth::user();

        // Check if user has permission to view approvals
        if (! $user->hasPermission('annealing', 'approve')) {
            abort(403, 'Unauthorized');
        }

        $pendingChecks = AnnealingCheck::with(['createdBy', 'pic'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return Inertia::render('AnnealingChecks/Approval', [
            'pendingChecks' => $pendingChecks,
            'user' => $user,
        ]);
    }

    /**
     * Bulk approve annealing checks
     */
    public function bulkApprove(
        Request $request,
        ApprovalNotificationService $approvalNotificationService
    ): \Illuminate\Http\RedirectResponse {
        $request->validate([
            'check_ids' => 'required|array',
            'check_ids.*' => 'exists:annealing_checks,id',
            'notes' => 'nullable|string',
        ]);

        $checks = AnnealingCheck::whereIn('id', $request->check_ids)->get();

        foreach ($checks as $check) {
            $previousStatus = $check->status;

            $check->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approval_notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            ActivityService::logApprove($check, [
                'previous_status' => $previousStatus,
                'new_status' => 'approved',
                'notes' => $request->notes,
                'source' => 'bulk_approval',
            ]);
        }

        $approvalNotificationService->markRecordsActed($checks, 'annealing');

        return redirect()->route('annealing-checks.approval')
            ->with('success', count($checks).' annealing check(s) approved successfully.');
    }

    /**
     * Bulk reject annealing checks
     */
    public function bulkReject(
        Request $request,
        ApprovalNotificationService $approvalNotificationService
    ): \Illuminate\Http\RedirectResponse {
        $request->validate([
            'check_ids' => 'required|array',
            'check_ids.*' => 'exists:annealing_checks,id',
            'notes' => 'nullable|string',
        ]);

        $checks = AnnealingCheck::whereIn('id', $request->check_ids)->get();

        foreach ($checks as $check) {
            $previousStatus = $check->status;

            $check->update([
                'status' => 'rejected',
                'approved_at' => now(),
                'approval_notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            ActivityService::logReject($check, $request->notes ?? '', [
                'previous_status' => $previousStatus,
                'new_status' => 'rejected',
                'notes' => $request->notes,
                'source' => 'bulk_approval',
            ]);
        }

        $approvalNotificationService->markRecordsActed($checks, 'annealing');

        return redirect()->route('annealing-checks.approval')
            ->with('success', count($checks).' annealing check(s) rejected successfully.');
    }
}
