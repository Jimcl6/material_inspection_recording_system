<?php

namespace App\Http\Controllers;

use App\Models\TempRecord;
use Illuminate\Http\Request;
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
}
