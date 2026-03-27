<?php

namespace App\Http\Controllers;

use App\Models\TorqueRecord;
use Illuminate\Http\Request;
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
            ];
        });

        return Inertia::render('TorqueRecords/Index', [
            'records' => $records,
            'filters' => $request->only(['search', 'date_from', 'date_to', 'driver_model', 'line_assigned']),
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

    public function store(Request $request)
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

        $rec = TorqueRecord::create($data);
        return redirect()->route('torque-records.show', $rec->id)->with('success', 'Record created.');
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

        $torque_record->update($data);
        return redirect()->route('torque-records.show', $torque_record->id)->with('success', 'Record updated.');
    }

    public function destroy(TorqueRecord $torque_record)
    {
        $torque_record->delete();
        return redirect()->route('torque-records.index')->with('success', 'Record deleted.');
    }
}
