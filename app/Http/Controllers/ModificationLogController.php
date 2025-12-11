<?php

namespace App\Http\Controllers;

use App\Models\ModificationLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ModificationLogController extends Controller
{
    public function index()
    {
        $logs = ModificationLog::orderByDesc('prod_date')->orderByDesc('id')->paginate(10)->through(function ($m) {
            return [
                'id' => $m->id,
                'prod_date' => $m->prod_date,
                'model_code' => $m->model_code,
                'item_for_modification' => $m->item_for_modification,
                'recorded_by' => $m->recorded_by,
            ];
        });
        return Inertia::render('ModificationLogs/Index', [
            'logs' => $logs,
        ]);
    }
    
    protected function parseDateInput(?string $value): ?string
    {
        if (!$value) return null;
        $formats = ['d/m/Y H:i', 'd/m/Y', 'Y-m-d\\TH:i', 'Y-m-d H:i', 'Y-m-d'];
        foreach ($formats as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $value);
                if ($dt !== false) return $dt->format('Y-m-d H:i:s');
            } catch (\Exception $e) {}
        }
        return null;
    }

    public function create()
    {
        return Inertia::render('ModificationLogs/Create');
    }

    public function store(Request $request)
    {
        $date = $this->parseDateInput($request->input('prod_date'));
        if ($date !== null) { $request->merge(['prod_date' => $date]); }
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
        return redirect()->route('modification-logs.show', $log->id)->with('success', 'Log created.');
    }

    public function show(ModificationLog $modification_log)
    {
        return Inertia::render('ModificationLogs/Show', [
            'log' => $modification_log,
        ]);
    }

    public function edit(ModificationLog $modification_log)
    {
        return Inertia::render('ModificationLogs/Edit', [
            'log' => $modification_log,
        ]);
    }

    public function update(Request $request, ModificationLog $modification_log)
    {
        $date = $this->parseDateInput($request->input('prod_date'));
        if ($date !== null) { $request->merge(['prod_date' => $date]); }
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
        return redirect()->route('modification-logs.show', $modification_log->id)->with('success', 'Log updated.');
    }

    public function destroy(ModificationLog $modification_log)
    {
        $modification_log->delete();
        return redirect()->route('modification-logs.index')->with('success', 'Log deleted.');
    }
}
