<?php

namespace App\Http\Controllers;

use App\Models\ProductionBatch;
use App\Models\InspectionCheckpoint;
use App\Models\InspectionSample;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductionBatchController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductionBatch::query();

        // Filters
        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('QRCode', 'like', "%{$q}%")
                    ->orWhere('MaterialLotNumber', 'like', "%{$q}%")
                    ->orWhere('JobNumber', 'like', "%{$q}%");
            });
        }
        if ($request->filled('qr')) {
            $query->where('QRCode', 'like', "%{$request->string('qr')}%");
        }
        if ($request->filled('lot')) {
            $query->where('MaterialLotNumber', 'like', "%{$request->string('lot')}%");
        }
        if ($request->filled('date_from')) {
            $query->whereDate('ProductionDate', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('ProductionDate', '<=', $request->date('date_to'));
        }

        $query->orderByDesc('ProductionDate')
              ->orderByDesc('BatchID');

        $batches = $query->paginate(10);

        return Inertia::render('Batches/Index', [
            'batches' => $batches,
            'filters' => [
                'q' => $request->input('q'),
                'qr' => $request->input('qr'),
                'lot' => $request->input('lot'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
            ],
        ]);
    }

    public function show($magnetism_checksheet)
    {
        $batch = ProductionBatch::findOrFail($magnetism_checksheet);
        
        try {
            $batch->load(['checkpoints.samples']);
        } catch (\Exception $e) {
            // If checkpoints relationship fails, continue without checkpoints
            $batch->checkpoints = collect();
        }

        // Build checkpoints with samples organized by phase
        $checkpointsData = $batch->checkpoints->map(function ($cp) {
            $samplesFirst = $cp->samples->where('Phase', 'FIRST')->sortBy('SampleOrder')->pluck('Value')->values()->toArray();
            $samplesLast = $cp->samples->where('Phase', 'LAST')->sortBy('SampleOrder')->pluck('Value')->values()->toArray();
            
            // Pad arrays to always have 5 elements
            while (count($samplesFirst) < 5) $samplesFirst[] = '';
            while (count($samplesLast) < 5) $samplesLast[] = '';
            
            return [
                'CheckpointID' => $cp->CheckpointID,
                'CheckpointNumber' => $cp->CheckpointNumber,
                'PositionLabel' => InspectionCheckpoint::POSITION_LABELS[$cp->CheckpointNumber] ?? "Checkpoint {$cp->CheckpointNumber}",
                'InspectorName_First' => $cp->InspectorName_First,
                'Judgement_First' => $cp->Judgement_First,
                'InspectorName_Last' => $cp->InspectorName_Last,
                'Judgement_Last' => $cp->Judgement_Last,
                'samples_first' => $samplesFirst,
                'samples_last' => $samplesLast,
            ];
        })->sortBy('CheckpointNumber')->values();

        return Inertia::render('Batches/Show', [
            'batch' => [
                'BatchID' => $batch->BatchID,
                'ProductionDate' => $batch->ProductionDate,
                'LetterCode' => $batch->LetterCode,
                'QRCode' => $batch->QRCode,
                'MaterialLotNumber' => $batch->MaterialLotNumber,
                'ProduceQty' => $batch->ProduceQty,
                'JobNumber' => $batch->JobNumber,
                'TotalQty' => $batch->TotalQty,
                'Remarks' => $batch->Remarks,
                'ItemName' => $batch->ItemName,
                'ItemCode' => $batch->ItemCode,
            ],
            'checkpoints' => $checkpointsData,
        ]);
    }

    public function create()
    {
        return Inertia::render('Batches/Create');
    }

    public function createCheckpoint($magnetism_checksheet)
    {
        $productionBatch = ProductionBatch::findOrFail($magnetism_checksheet);
        
        return Inertia::render('Batches/CreateCheckpoint', [
            'batch' => $productionBatch
        ]);
    }

    public function storeCheckpoint(Request $request, $magnetism_checksheet)
    {
        $productionBatch = ProductionBatch::findOrFail($magnetism_checksheet);
        $data = $request->validate([
            'CheckpointNumber' => ['required', 'integer', 'min:1'],
            'InspectorName_First' => ['nullable', 'string', 'max:255'],
            'Judgement_First' => ['nullable', 'string', 'max:255'],
            'InspectorName_Last' => ['nullable', 'string', 'max:255'],
            'Judgement_Last' => ['nullable', 'string', 'max:255'],
            'samples' => ['nullable', 'array'],
            'samples.*.SampleOrder' => ['nullable', 'integer', 'min:1'],
            'samples.*.Phase' => ['nullable', 'string', 'in:FIRST,LAST'],
            'samples.*.Value' => ['nullable', 'string', 'max:255'],
        ]);

        // Create the checkpoint
        $checkpoint = InspectionCheckpoint::create([
            'BatchID' => $productionBatch->BatchID,
            'CheckpointNumber' => $data['CheckpointNumber'],
            'InspectorName_First' => $data['InspectorName_First'] ?? null,
            'Judgement_First' => $data['Judgement_First'] ?? null,
            'InspectorName_Last' => $data['InspectorName_Last'] ?? null,
            'Judgement_Last' => $data['Judgement_Last'] ?? null,
        ]);

        // Create samples if provided
        if (!empty($data['samples'])) {
            foreach ($data['samples'] as $sample) {
                if (!empty($sample['Value'])) {
                    InspectionSample::create([
                        'CheckpointID' => $checkpoint->CheckpointID, // Use the model's primary key
                        'SampleOrder' => $sample['SampleOrder'] ?? 1,
                        'Phase' => $sample['Phase'] ?? 'FIRST',
                        'Value' => $sample['Value'],
                    ]);
                }
            }
        }

        return redirect()->route('magnetism-checksheet.show', $productionBatch->BatchID)
            ->with('success', 'Checkpoint created successfully!');
    }

    public function editCheckpoint($magnetism_checksheet, $checkpoint = null)
    {
        $productionBatch = ProductionBatch::findOrFail($magnetism_checksheet);
        
        // Load all checkpoints with samples for grid editing
        $productionBatch->load(['checkpoints.samples']);
        
        // Build checkpoints data organized by checkpoint number (1-4)
        $checkpointsData = [];
        $inspectorFirst = '';
        $inspectorLast = '';
        
        for ($i = 1; $i <= 4; $i++) {
            $cp = $productionBatch->checkpoints->firstWhere('CheckpointNumber', $i);
            
            if ($cp) {
                $samplesFirst = $cp->samples->where('Phase', 'FIRST')->sortBy('SampleOrder')->pluck('Value')->values()->toArray();
                $samplesLast = $cp->samples->where('Phase', 'LAST')->sortBy('SampleOrder')->pluck('Value')->values()->toArray();
                
                // Get inspector names from first checkpoint found
                if (!$inspectorFirst && $cp->InspectorName_First) $inspectorFirst = $cp->InspectorName_First;
                if (!$inspectorLast && $cp->InspectorName_Last) $inspectorLast = $cp->InspectorName_Last;
                
                $checkpointsData[] = [
                    'CheckpointID' => $cp->CheckpointID,
                    'CheckpointNumber' => $i,
                    'label' => InspectionCheckpoint::POSITION_LABELS[$i] ?? "Checkpoint {$i}",
                    'Judgement_First' => $cp->Judgement_First ?? '',
                    'Judgement_Last' => $cp->Judgement_Last ?? '',
                    'samples_first' => array_pad($samplesFirst, 5, ''),
                    'samples_last' => array_pad($samplesLast, 5, ''),
                ];
            } else {
                // Checkpoint doesn't exist yet, create empty placeholder
                $checkpointsData[] = [
                    'CheckpointID' => null,
                    'CheckpointNumber' => $i,
                    'label' => InspectionCheckpoint::POSITION_LABELS[$i] ?? "Checkpoint {$i}",
                    'Judgement_First' => '',
                    'Judgement_Last' => '',
                    'samples_first' => ['', '', '', '', ''],
                    'samples_last' => ['', '', '', '', ''],
                ];
            }
        }
        
        return Inertia::render('Batches/EditCheckpoint', [
            'batch' => [
                'BatchID' => $productionBatch->BatchID,
                'ProductionDate' => $productionBatch->ProductionDate,
                'LetterCode' => $productionBatch->LetterCode,
                'QRCode' => $productionBatch->QRCode,
                'MaterialLotNumber' => $productionBatch->MaterialLotNumber,
            ],
            'checkpoints' => $checkpointsData,
            'inspectorFirst' => $inspectorFirst,
            'inspectorLast' => $inspectorLast,
        ]);
    }

    public function updateCheckpoint(Request $request, $magnetism_checksheet, $checkpoint = null)
    {
        $productionBatch = ProductionBatch::findOrFail($magnetism_checksheet);
        
        $data = $request->validate([
            'inspectorFirst' => ['nullable', 'string', 'max:255'],
            'inspectorLast' => ['nullable', 'string', 'max:255'],
            'checkpoints' => ['required', 'array'],
            'checkpoints.*.CheckpointNumber' => ['required', 'integer', 'min:1', 'max:4'],
            'checkpoints.*.Judgement_First' => ['nullable', 'string', 'max:255'],
            'checkpoints.*.Judgement_Last' => ['nullable', 'string', 'max:255'],
            'checkpoints.*.samples_first' => ['nullable', 'array'],
            'checkpoints.*.samples_last' => ['nullable', 'array'],
        ]);

        $inspectorFirst = $data['inspectorFirst'] ?? null;
        $inspectorLast = $data['inspectorLast'] ?? null;

        // Process each checkpoint
        foreach ($data['checkpoints'] as $cpData) {
            $checkpointNumber = (int)$cpData['CheckpointNumber'];
            
            // Find or create checkpoint
            $cp = InspectionCheckpoint::where('BatchID', $productionBatch->BatchID)
                ->where('CheckpointNumber', $checkpointNumber)
                ->first();
            
            if (!$cp) {
                $cp = InspectionCheckpoint::create([
                    'BatchID' => $productionBatch->BatchID,
                    'CheckpointNumber' => $checkpointNumber,
                    'InspectorName_First' => $inspectorFirst,
                    'Judgement_First' => $cpData['Judgement_First'] ?? null,
                    'InspectorName_Last' => $inspectorLast,
                    'Judgement_Last' => $cpData['Judgement_Last'] ?? null,
                ]);
            } else {
                $cp->update([
                    'InspectorName_First' => $inspectorFirst,
                    'Judgement_First' => $cpData['Judgement_First'] ?? null,
                    'InspectorName_Last' => $inspectorLast,
                    'Judgement_Last' => $cpData['Judgement_Last'] ?? null,
                ]);
            }

            // Delete existing samples for this checkpoint
            InspectionSample::where('CheckpointID', $cp->CheckpointID)->delete();

            // Store First Inspection samples
            $samplesFirst = $cpData['samples_first'] ?? [];
            foreach ($samplesFirst as $i => $val) {
                if ($val !== null && $val !== '') {
                    InspectionSample::create([
                        'CheckpointID' => $cp->CheckpointID,
                        'SampleOrder' => $i + 1,
                        'Phase' => 'FIRST',
                        'Value' => (string)$val,
                    ]);
                }
            }

            // Store Last Inspection samples
            $samplesLast = $cpData['samples_last'] ?? [];
            foreach ($samplesLast as $i => $val) {
                if ($val !== null && $val !== '') {
                    InspectionSample::create([
                        'CheckpointID' => $cp->CheckpointID,
                        'SampleOrder' => $i + 1,
                        'Phase' => 'LAST',
                        'Value' => (string)$val,
                    ]);
                }
            }
        }

        return redirect()->route('magnetism-checksheet.show', $productionBatch->BatchID)
            ->with('success', 'Inspection samples updated successfully!');
    }

    public function destroyCheckpoint($magnetism_checksheet, $checkpoint)
    {
        $productionBatch = ProductionBatch::findOrFail($magnetism_checksheet);
        $checkpoint = InspectionCheckpoint::where('BatchID', $productionBatch->BatchID)
            ->findOrFail($checkpoint);
        
        // Delete the checkpoint and its samples (cascade delete should handle samples)
        $checkpoint->delete();

        return redirect()->route('magnetism-checksheet.show', $productionBatch->BatchID)
            ->with('success', 'Checkpoint deleted successfully!');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ProductionDate' => ['required','date'],
            'LetterCode' => ['nullable','string','max:5'],
            'QRCode' => ['required','string','max:20'],
            'MaterialLotNumber' => ['required','string','max:50'],
            'ProduceQty' => ['required','integer','min:0'],
            'JobNumber' => ['required','string','max:50'],
            'TotalQty' => ['required','integer','min:0'],
            'Remarks' => ['nullable','string'],
            'ItemName' => ['nullable','string','max:50'],
            'ItemCode' => ['nullable','string','max:50'],
        ]);

        // Auto-generate letter if missing or set to AUTO
        $letter = strtoupper((string)($data['LetterCode'] ?? ''));
        if ($letter === '' || $letter === 'AUTO') {
            $next = $this->nextLetterForDate($data['ProductionDate']);
            if ($next === null) {
                return back()->withErrors(['LetterCode' => 'All letters A-Z already used for this date.'])->withInput();
            }
            $data['LetterCode'] = $next;
        }

        $batch = ProductionBatch::create($data);

        // Handle checkpoints grid (4 checkpoints × 5 samples × 2 phases)
        $checkpoints = $request->input('checkpoints');
        if (is_array($checkpoints)) {
            foreach ($checkpoints as $cpData) {
                $checkpointNumber = (int)($cpData['CheckpointNumber'] ?? 0);
                if ($checkpointNumber < 1 || $checkpointNumber > 4) continue;

                $cp = InspectionCheckpoint::create([
                    'BatchID' => $batch->BatchID,
                    'CheckpointNumber' => $checkpointNumber,
                    'InspectorName_First' => $cpData['InspectorName_First'] ?? null,
                    'Judgement_First' => $cpData['Judgement_First'] ?? null,
                    'InspectorName_Last' => $cpData['InspectorName_Last'] ?? null,
                    'Judgement_Last' => $cpData['Judgement_Last'] ?? null,
                ]);

                // Store First Inspection samples (fixed 5)
                $samplesFirst = $cpData['samples_first'] ?? [];
                foreach ($samplesFirst as $i => $val) {
                    $order = $i + 1;
                    if (is_array($val)) {
                        $order = (int)($val['SampleOrder'] ?? $order);
                        $val = $val['Value'] ?? null;
                    }
                    if ($val === null || $val === '') continue;
                    InspectionSample::create([
                        'CheckpointID' => $cp->CheckpointID,
                        'SampleOrder' => $order,
                        'Phase' => 'FIRST',
                        'Value' => (string)$val,
                    ]);
                }

                // Store Last Inspection samples (fixed 5)
                $samplesLast = $cpData['samples_last'] ?? [];
                foreach ($samplesLast as $i => $val) {
                    $order = $i + 1;
                    if (is_array($val)) {
                        $order = (int)($val['SampleOrder'] ?? $order);
                        $val = $val['Value'] ?? null;
                    }
                    if ($val === null || $val === '') continue;
                    InspectionSample::create([
                        'CheckpointID' => $cp->CheckpointID,
                        'SampleOrder' => $order,
                        'Phase' => 'LAST',
                        'Value' => (string)$val,
                    ]);
                }
            }
        }

        // If stay=1 (modal flow), return to index with new_batch_id instead of show
        if ($request->boolean('stay')) {
            return redirect()->route('magnetism-checksheet.index')
                ->with('success', 'Batch created.')
                ->with('new_batch_id', $batch->BatchID);
        }

        return redirect()->route('magnetism-checksheet.show', ['magnetism_checksheet' => $batch->BatchID])
            ->with('success', 'Batch created.');
    }

    public function edit($magnetism_checksheet)
    {
        $batch = ProductionBatch::findOrFail($magnetism_checksheet);
        
        return Inertia::render('Batches/Edit', [
            'batch' => [
                'BatchID' => $batch->BatchID,
                'ProductionDate' => $batch->ProductionDate,
                'LetterCode' => $batch->LetterCode,
                'QRCode' => $batch->QRCode,
                'MaterialLotNumber' => $batch->MaterialLotNumber,
                'ProduceQty' => $batch->ProduceQty,
                'JobNumber' => $batch->JobNumber,
                'TotalQty' => $batch->TotalQty,
                'Remarks' => $batch->Remarks,
                'ItemName' => $batch->ItemName,
                'ItemCode' => $batch->ItemCode,
            ],
        ]);
    }

    public function update(Request $request, $magnetism_checksheet)
    {
        $batch = ProductionBatch::findOrFail($magnetism_checksheet);
        $data = $request->validate([
            'ProductionDate' => ['required','date'],
            'LetterCode' => ['required','string','max:5'],
            'QRCode' => ['required','string','max:20'],
            'MaterialLotNumber' => ['required','string','max:50'],
            'ProduceQty' => ['required','integer','min:0'],
            'JobNumber' => ['required','string','max:50'],
            'TotalQty' => ['required','integer','min:0'],
            'Remarks' => ['nullable','string'],
            'ItemName' => ['nullable','string','max:50'],
            'ItemCode' => ['nullable','string','max:50'],
        ]);

        $batch->update($data);

        if ($request->boolean('stay')) {
            return redirect()->route('magnetism-checksheet.index')->with('success', 'Batch updated.');
        }

        return redirect()->route('magnetism-checksheet.show', ['magnetism_checksheet' => $batch->BatchID])
            ->with('success', 'Batch updated.');
    }

    public function destroy($magnetism_checksheet)
    {
        $batch = ProductionBatch::findOrFail($magnetism_checksheet);
        $batch->delete();
        return redirect()->route('magnetism-checksheet.index')->with('success', 'Batch deleted.');
    }

    public function nextLetter(Request $request)
    {
        $request->validate(['date' => ['required','date']]);
        $letter = $this->nextLetterForDate($request->input('date'));
        return response()->json(['letter' => $letter]);
    }

    protected function nextLetterForDate(string $date): ?string
    {
        $last = ProductionBatch::whereDate('ProductionDate', $date)
            ->orderBy('LetterCode', 'desc')
            ->value('LetterCode');
        $last = strtoupper((string)$last);
        if ($last === '') return 'A';
        $code = ord($last);
        if ($code < ord('A')) return 'A';
        if ($code >= ord('Z')) return null; // exhausted
        return chr($code + 1);
    }
}
