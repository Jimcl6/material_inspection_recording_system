<?php

namespace App\Http\Controllers;

use App\Models\InspectionCheckpoint;
use App\Models\InspectionSample;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InspectionSampleController extends Controller
{
    public function store(Request $request, InspectionCheckpoint $checkpoint)
    {
        $data = $request->validate([
            'Phase' => ['required', Rule::in(['FIRST','LAST'])],
            'SampleOrder' => [
                'required','integer','min:1',
                Rule::unique('InspectionSamples', 'SampleOrder')
                    ->where('CheckpointID', $checkpoint->CheckpointID)
                    ->where('Phase', $request->input('Phase')),
            ],
            'Value' => ['required','string','max:50'],
        ]);

        $data['CheckpointID'] = $checkpoint->CheckpointID;
        InspectionSample::create($data);

        return back()->with('success', 'Sample added.');
    }

    public function update(Request $request, InspectionSample $sample)
    {
        $phase = $request->input('Phase', $sample->Phase);
        $data = $request->validate([
            'Phase' => [Rule::in(['FIRST','LAST'])],
            'SampleOrder' => [
                'nullable','integer','min:1',
                Rule::unique('InspectionSamples', 'SampleOrder')
                    ->where('CheckpointID', $sample->CheckpointID)
                    ->where('Phase', $phase)
                    ->ignore($sample->SampleID, 'SampleID'),
            ],
            'Value' => ['nullable','string','max:50'],
        ]);

        if (!array_key_exists('Phase', $data)) { $data['Phase'] = $phase; }
        if (empty($data)) { return back(); }

        $sample->update($data);

        return back()->with('success', 'Sample updated.');
    }

    public function destroy(InspectionSample $sample)
    {
        $sample->delete();
        return back()->with('success', 'Sample deleted.');
    }
}
