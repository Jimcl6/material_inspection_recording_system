<?php

namespace App\Http\Controllers;

use App\Models\TorqueRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TorqueRecordController extends Controller
{
    public function index()
    {
        $records = TorqueRecord::orderByDesc('id')->paginate(10)->through(function ($r) {
            return [
                'id' => $r->id,
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
        ]);
    }

    public function create()
    {
        return Inertia::render('TorqueRecords/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
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
