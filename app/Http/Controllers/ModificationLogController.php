<?php

namespace App\Http\Controllers;

use App\Models\ModificationLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class ModificationLogController extends Controller
{
    /**
     * Display a listing of the modification logs.
     */
    public function index(Request $request): Response
    {
        $query = ModificationLog::orderByDesc('prod_date')->orderByDesc('id');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('model_code', 'like', "%{$search}%")
                  ->orWhere('item_for_modification', 'like', "%{$search}%")
                  ->orWhere('recorded_by', 'like', "%{$search}%")
                  ->orWhere('material_lot_no', 'like', "%{$search}%")
                  ->orWhere('job_no_transfer_order', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('prod_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('prod_date', '<=', $request->input('date_to'));
        }

        $logs = $query->paginate(15)->withQueryString();

        return Inertia::render('ModificationLogs/Index', [
            'logs' => $logs,
            'filters' => [
                'search' => $request->input('search', ''),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
            ],
        ]);
    }

    /**
     * Display the specified modification log.
     */
    public function show(ModificationLog $modification_log): Response
    {
        return Inertia::render('ModificationLogs/Show', [
            'log' => $modification_log,
        ]);
    }

    /**
     * Show the form for creating a new modification log.
     */
    public function create(): Response
    {
        return Inertia::render('ModificationLogs/Create');
    }

    /**
     * Store a newly created modification log in storage.
     */
    public function store(Request $request)
    {
        $date = $this->parseDateInput($request->input('prod_date'));
        if ($date !== null) { 
            $request->merge(['prod_date' => $date]); 
        }
        
        $data = $request->validate([
            'prod_date' => ['required','date'],
            'col_4m' => ['nullable','string','max:50'],
            'col_line' => ['nullable','string','max:50'],
            'model_code' => ['required','string','max:100'],
            'item_for_modification' => ['required','string','max:255'],
            'nature_of_change' => ['nullable','string'],
            'col_from' => ['nullable','string','max:255'],
            'col_to' => ['nullable','string','max:255'],
            'material_lot_no' => ['nullable','string','max:255'],
            'start_serial' => ['nullable','string','max:255'],
            'end_serial' => ['nullable','string','max:255'],
            'recorded_by' => ['required','string','max:255'],
            'source_of_info' => ['nullable','string','max:255'],
            'approved_by' => ['nullable','string','max:255'],
            'job_no_transfer_order' => ['nullable','string','max:255'],
            'col_remarks' => ['nullable','string'],
        ]);

        $log = ModificationLog::create($data);
        
        return redirect()->route('modification-logs.show', $log->id)
            ->with('success', 'Modification log created successfully!');
    }

    /**
     * Show the form for editing the specified modification log.
     */
    public function edit(ModificationLog $modification_log): Response
    {
        return Inertia::render('ModificationLogs/Edit', [
            'log' => $modification_log,
        ]);
    }

    /**
     * Update the specified modification log in storage.
     */
    public function update(Request $request, ModificationLog $modification_log)
    {
        $date = $this->parseDateInput($request->input('prod_date'));
        if ($date !== null) { 
            $request->merge(['prod_date' => $date]); 
        }
        
        $data = $request->validate([
            'prod_date' => ['required','date'],
            'col_4m' => ['nullable','string','max:50'],
            'col_line' => ['nullable','string','max:50'],
            'model_code' => ['required','string','max:100'],
            'item_for_modification' => ['required','string','max:255'],
            'nature_of_change' => ['nullable','string'],
            'col_from' => ['nullable','string','max:255'],
            'col_to' => ['nullable','string','max:255'],
            'material_lot_no' => ['nullable','string','max:255'],
            'start_serial' => ['nullable','string','max:255'],
            'end_serial' => ['nullable','string','max:255'],
            'recorded_by' => ['required','string','max:255'],
            'source_of_info' => ['nullable','string','max:255'],
            'approved_by' => ['nullable','string','max:255'],
            'job_no_transfer_order' => ['nullable','string','max:255'],
            'col_remarks' => ['nullable','string'],
        ]);

        $modification_log->update($data);
        
        return redirect()->route('modification-logs.show', $modification_log->id)
            ->with('success', 'Modification log updated successfully!');
    }

    /**
     * Remove the specified modification log from storage.
     */
    public function destroy(ModificationLog $modification_log)
    {
        $modification_log->delete();
        
        return redirect()->route('modification-logs.index')
            ->with('success', 'Modification log deleted successfully!');
    }

    /**
     * Parse date input with multiple format support.
     */
    protected function parseDateInput(?string $value): ?string
    {
        if (!$value) return null;
        $formats = ['d/m/Y H:i', 'd/m/Y', 'Y-m-d\TH:i', 'Y-m-d H:i', 'Y-m-d'];
        foreach ($formats as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $value);
                if ($dt !== false) return $dt->format('Y-m-d H:i:s');
            } catch (\Exception $e) {}
        }
        return null;
    }
}
