<?php

namespace App\Http\Controllers;

use App\Models\ProductionBatch;
use App\Models\InspectionCheckpoint;
use App\Models\InspectionSample;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class InspectionCheckpointController extends Controller
{
    public function store(Request $request, ProductionBatch $batch)
    {
        $data = $request->validate([
            'CheckpointNumber' => ['required','integer','min:1'],
            'InspectorName_First' => ['nullable','string','max:100'],
            'Judgement_First' => ['nullable','string','max:20'],
            'InspectorName_Last' => ['nullable','string','max:100'],
            'Judgement_Last' => ['nullable','string','max:20'],
            // optional samples arrays
            'samples_first' => ['nullable','array'],
            'samples_last' => ['nullable','array'],
        ]);

        $data['BatchID'] = $batch->BatchID;
        $cp = InspectionCheckpoint::create($data);

        // Optional sample creation
        $first = $request->input('samples_first', []);
        $last = $request->input('samples_last', []);
        foreach ($first as $i => $val) {
            $order = $i + 1;
            if (is_array($val)) { $order = (int)($val['SampleOrder'] ?? $order); $val = $val['Value'] ?? null; }
            if ($val === null || $val === '') continue;
            InspectionSample::create([
                'CheckpointID' => $cp->CheckpointID,
                'SampleOrder' => $order,
                'Phase' => 'FIRST',
                'Value' => (string)$val,
            ]);
        }
        foreach ($last as $i => $val) {
            $order = $i + 1;
            if (is_array($val)) { $order = (int)($val['SampleOrder'] ?? $order); $val = $val['Value'] ?? null; }
            if ($val === null || $val === '') continue;
            InspectionSample::create([
                'CheckpointID' => $cp->CheckpointID,
                'SampleOrder' => $order,
                'Phase' => 'LAST',
                'Value' => (string)$val,
            ]);
        }

        if ($request->boolean('stay')) {
            return redirect()->route('batches.index')->with('success', 'Checkpoint created.');
        }

        return redirect()->route('batches.show', ['batch' => $batch->BatchID])
            ->with('success', 'Checkpoint created.');
    }

    public function edit(InspectionCheckpoint $checkpoint)
    {
        $checkpoint->load('batch');
        $samples = $checkpoint->samples()->orderBy('Phase')->orderBy('SampleOrder')->get([
            'SampleID','CheckpointID','SampleOrder','Phase','Value'
        ]);

        return Inertia::render('Checkpoints/Edit', [
            'batch' => [
                'BatchID' => $checkpoint->batch->BatchID,
                'QRCode' => $checkpoint->batch->QRCode,
                'ProductionDate' => $checkpoint->batch->ProductionDate,
                'LetterCode' => $checkpoint->batch->LetterCode,
            ],
            'checkpoint' => [
                'CheckpointID' => $checkpoint->CheckpointID,
                'BatchID' => $checkpoint->BatchID,
                'CheckpointNumber' => $checkpoint->CheckpointNumber,
                'InspectorName_First' => $checkpoint->InspectorName_First,
                'Judgement_First' => $checkpoint->Judgement_First,
                'InspectorName_Last' => $checkpoint->InspectorName_Last,
                'Judgement_Last' => $checkpoint->Judgement_Last,
            ],
            'samples' => $samples,
        ]);
    }

    public function update(Request $request, InspectionCheckpoint $checkpoint)
    {
        $data = $request->validate([
            'CheckpointNumber' => ['required','integer','min:1'],
            'InspectorName_First' => ['nullable','string','max:100'],
            'Judgement_First' => ['nullable','string','max:20'],
            'InspectorName_Last' => ['nullable','string','max:100'],
            'Judgement_Last' => ['nullable','string','max:20'],
        ]);

        $checkpoint->update($data);

        return back()->with('success', 'Checkpoint updated.');
    }

    public function destroy(InspectionCheckpoint $checkpoint)
    {
        $batchId = $checkpoint->BatchID;
        $checkpoint->delete();
        return redirect()->route('batches.show', ['batch' => $batchId])
            ->with('success', 'Checkpoint deleted.');
    }
}
