<?php

namespace App\Http\Controllers;

use App\Models\AnnealingCheck;
// use App\Models\TemperatureReading;
use App\Http\Requests\StoreAnnealingCheckRequest;
use App\Http\Requests\UpdateAnnealingCheckRequest;
use App\Http\Requests\ImportAnnealingCheckRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnnealingChecksExport;
// use App\Imports\AnnealingChecksImport;
use App\Imports\AnnealingChecksWithHeadersImport;

class AnnealingCheckController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request): \Inertia\Response
    {
        $annealingChecks = AnnealingCheck::with(['pic', 'checkedBy', 'verifiedBy'])
            ->latest()
            ->paginate(15);

        return Inertia::render('AnnealingChecks/Index', [
            'annealingChecks' => $annealingChecks,
            'filters' => $request->only(['search', 'date_from', 'date_to', 'machine_number']),
            'machineNumbers' => AnnealingCheck::whereNotNull('machine_number')
                ->where('machine_number', '!=', '')
                ->distinct()
                ->orderBy('machine_number')
                ->pluck('machine_number'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create(): \Inertia\Response
    {
        return Inertia::render('AnnealingChecks/Create', [
            'users' => \App\Models\User::select('id', 'name')->orderBy('name')->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAnnealingCheckRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreAnnealingCheckRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        $data['status'] = 'pending'; // Manual entries start as pending

        // Convert user names back to IDs for database storage
        // pic_id is required, so pass true as second parameter
        $data['pic_id'] = $this->convertNameToId($data['pic_id'] ?? null, true);
        $data['checked_by_id'] = $this->convertNameToId($data['checked_by_id'] ?? null);
        $data['verified_by_id'] = $this->convertNameToId($data['verified_by_id'] ?? null);

        $temperatureReadings = $data['temperature_readings'];
        unset($data['temperature_readings']);

        $annealingCheck = AnnealingCheck::create($data);

        // Add temperature readings
        foreach ($temperatureReadings as $reading) {
            $reading['recorded_by'] = Auth::id();
            $annealingCheck->temperatureReadings()->create($reading);
        }

        // Notify administrators and inspectors
        if (class_exists('\App\Services\ApprovalNotificationService')) {
            $notificationService = new \App\Services\ApprovalNotificationService();
            $notificationService->notifyApprovers($annealingCheck, 'new_submission');
        }

        return redirect()->route('annealing-checks.index')
            ->with('success', 'Annealing check created successfully and submitted for approval.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AnnealingCheck  $annealingCheck
     * @return \Inertia\Response
     */
    public function show(AnnealingCheck $annealingCheck): \Inertia\Response
    {
        $annealingCheck->load(['pic', 'checkedBy', 'verifiedBy', 'temperatureReadings']);
        
        return Inertia::render('AnnealingChecks/Show', [
            'annealingCheck' => $annealingCheck
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
        
        // Log the conversion attempt for debugging
        \Log::info("Name to ID conversion:", [
            'input_name' => $name,
            'clean_name' => $cleanName,
            'found_user' => $user ? $user->toArray() : null,
            'result_id' => $user ? $user->id : ($isRequired ? Auth::id() : null)
        ]);
        
        // Return user ID if found, otherwise use current user for required fields or null for optional
        return $user ? $user->id : ($isRequired ? Auth::id() : null);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AnnealingCheck  $annealingCheck
     * @return \Inertia\Response
     */
    public function edit(AnnealingCheck $annealingCheck): \Inertia\Response
    {
        $annealingCheck->load('temperatureReadings');
        
        return Inertia::render('AnnealingChecks/Edit', [
            'annealingCheck' => $annealingCheck,
            'temperatureReadings' => $annealingCheck->temperatureReadings,
            'users' => \App\Models\User::select('id', 'name')->orderBy('name')->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAnnealingCheckRequest  $request
     * @param  \App\Models\AnnealingCheck  $annealingCheck
     * @return \Illuminate\Http\RedirectResponse
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

        $temperatureReadings = $data['temperature_readings'] ?? [];
        unset($data['temperature_readings']);

        $annealingCheck->update($data);

        // Update temperature readings
        $existingIds = $annealingCheck->temperatureReadings->pluck('id')->toArray();
        $newIds = [];

        foreach ($temperatureReadings as $reading) {
            if (isset($reading['id']) && in_array($reading['id'], $existingIds)) {
                // Update existing reading
                $annealingCheck->temperatureReadings()
                    ->where('id', $reading['id'])
                    ->update([
                        'reading_time' => $reading['reading_time'],
                        'temperature' => $reading['temperature'],
                        'updated_at' => now(),
                    ]);
                $newIds[] = $reading['id'];
            } else {
                // Create new reading
                $newReading = $annealingCheck->temperatureReadings()->create([
                    'reading_time' => $reading['reading_time'],
                    'temperature' => $reading['temperature'],
                    'recorded_by' => Auth::id(),
                ]);
                $newIds[] = $newReading->id;
            }
        }

        // Delete readings that were removed
        $toDelete = array_diff($existingIds, $newIds);
        if (!empty($toDelete)) {
            $annealingCheck->temperatureReadings()->whereIn('id', $toDelete)->delete();
        }

        // Notify administrators and inspectors if status changed
        if (isset($data['status']) && $annealingCheck->wasChanged('status')) {
            if (class_exists('\App\Services\ApprovalNotificationService')) {
                $notificationService = new \App\Services\ApprovalNotificationService();
                $notificationService->notifyApprovers($annealingCheck, 'update');
            }
        }

        return redirect()->route('annealing-checks.index')
            ->with('success', 'Annealing check updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AnnealingCheck  $annealingCheck
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(AnnealingCheck $annealingCheck)
    {
        $annealingCheck->delete();
        
        return redirect()->route('annealing-checks.index')
            ->with('success', 'Annealing check deleted successfully!');
    }

    /**
     * Show the import form.
     *
     * @return \Inertia\Response
     */
    public function importForm(): \Inertia\Response
    {
        return Inertia::render('AnnealingChecks/Import');
    }

    /**
     * Import annealing checks from Excel file.
     *
     * @param  \App\Http\Requests\ImportAnnealingCheckRequest  $request
     * @return \Inertia\Response
     */
    public function import(ImportAnnealingCheckRequest $request): \Inertia\Response
    {
        Log::info('Import method called', [
            'has_file' => $request->hasFile('file'),
            'file_info' => $request->file('file') ? [
                'original_name' => $request->file('file')->getClientOriginalName(),
                'size' => $request->file('file')->getSize(),
                'mime_type' => $request->file('file')->getMimeType()
            ] : null
        ]);

        $file = $request->file('file');
        $overwrite = $request->boolean('overwrite', false);
        
        if (!$file) {
            Log::error('No file uploaded');
            return Inertia::render('AnnealingChecks/Import')
                ->with('error', 'No file uploaded.');
        }

        try {
            if ($overwrite) {
                // Delete existing records if overwrite is true
                AnnealingCheck::truncate();
            }

            Log::info('Starting Excel import');
            $import = new AnnealingChecksWithHeadersImport();
            
            // Store the uploaded file temporarily and import using PhpSpreadsheet directly
            $tempPath = $file->store('temp');
            $fullPath = storage_path('app/' . $tempPath);
            
            $import->import($fullPath);
            
            // Clean up temp file
            @unlink($fullPath);
            
            Log::info('Excel import completed');
            
            $results = $import->getResults();
            
            Log::info('Import results', $results);
            
            // Prepare summary message
            $totalImported = array_sum(array_column($results, 'imported'));
            $totalSkipped = array_sum(array_column($results, 'skipped'));
            $totalErrors = array_sum(array_map('count', array_column($results, 'errors')));
            
            $message = "Import completed: {$totalImported} imported";
            if ($totalSkipped > 0) $message .= ", {$totalSkipped} skipped";
            if ($totalErrors > 0) $message .= ", {$totalErrors} errors";
            
            return Inertia::render('AnnealingChecks/Import', [
                'import_results' => $results,
                'success' => $message
            ]);
            
        } catch (\Exception $e) {
            Log::error('Import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Inertia::render('AnnealingChecks/Import')
                ->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    /**
     * Export annealing checks to Excel file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new AnnealingChecksExport, 'annealing-checks-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Debug route to check annealing checks data
     */
    public function debug()
    {
        // Read the uploaded Excel file directly with PhpSpreadsheet to understand its structure
        $filePath = storage_path('app/reference-excels/ANNEALING CHECKSHEET.xlsx');
        
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found at: ' . $filePath]);
        }
        
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
        $spreadsheet = $reader->load($filePath);
        
        $result = [];
        foreach ($spreadsheet->getSheetNames() as $index => $sheetName) {
            $sheet = $spreadsheet->getSheet($index);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            
            $sheetData = [
                'name' => $sheetName,
                'highest_row' => $highestRow,
                'highest_column' => $highestColumn,
                'rows' => []
            ];
            
            // Read rows 7-15 to see headers and first data rows
            for ($row = 7; $row <= min($highestRow, 15); $row++) {
                $rowData = [];
                foreach (range('A', 'R') as $col) {
                    $rowData[$col] = $sheet->getCell($col . $row)->getCalculatedValue();
                }
                $sheetData['rows'][$row] = $rowData;
            }
            
            $result[] = $sheetData;
            
            // Only show first 3 sheets to keep response manageable
            if (count($result) >= 3) break;
        }
        
        return response()->json([
            'total_sheets' => $spreadsheet->getSheetCount(),
            'sheet_names' => $spreadsheet->getSheetNames(),
            'sheets' => $result,
        ]);
    }

    /**
     * Show the approval page for administrators and inspectors
     */
    public function approval(): \Inertia\Response
    {
        $user = Auth::user();
        
        // Check if user has permission to view approvals
        if (!in_array($user->role?->slug, ['admin', 'inspector'])) {
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
    public function bulkApprove(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'check_ids' => 'required|array',
            'check_ids.*' => 'exists:annealing_checks,id',
            'notes' => 'nullable|string',
        ]);

        $checks = AnnealingCheck::whereIn('id', $request->check_ids)->get();
        
        foreach ($checks as $check) {
            $check->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approval_notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            // Mark notifications as acted
            $check->approvalNotifications()
                ->where('user_id', Auth::id())
                ->update(['status' => 'acted']);
        }

        return redirect()->route('annealing-checks.approval')
            ->with('success', count($checks) . ' annealing check(s) approved successfully.');
    }

    /**
     * Bulk reject annealing checks
     */
    public function bulkReject(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'check_ids' => 'required|array',
            'check_ids.*' => 'exists:annealing_checks,id',
            'notes' => 'nullable|string',
        ]);

        $checks = AnnealingCheck::whereIn('id', $request->check_ids)->get();
        
        foreach ($checks as $check) {
            $check->update([
                'status' => 'rejected',
                'approved_at' => now(),
                'approval_notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            // Mark notifications as acted
            $check->approvalNotifications()
                ->where('user_id', Auth::id())
                ->update(['status' => 'acted']);
        }

        return redirect()->route('annealing-checks.approval')
            ->with('success', count($checks) . ' annealing check(s) rejected successfully.');
    }
}
