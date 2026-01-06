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

        $batches = $query->paginate(10)->through(function ($b) {
            return [
                'BatchID' => $b->BatchID,
                'ProductionDate' => $b->ProductionDate,
                'LetterCode' => $b->LetterCode,
                'QRCode' => $b->QRCode,
                'MaterialLotNumber' => $b->MaterialLotNumber,
                'ProduceQty' => $b->ProduceQty,
                'JobNumber' => $b->JobNumber,
                'TotalQty' => $b->TotalQty,
                'Remarks' => $b->Remarks,
            ];
        });

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

    public function show(ProductionBatch $batch)
    {
        $batch->load(['checkpoints' => function ($q) {
            $q->withCount('samples');
        }]);

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
            ],
            'checkpoints' => $batch->checkpoints->map(function ($cp) {
                return [
                    'CheckpointID' => $cp->CheckpointID,
                    'CheckpointNumber' => $cp->CheckpointNumber,
                    'InspectorName_First' => $cp->InspectorName_First,
                    'Judgement_First' => $cp->Judgement_First,
                    'InspectorName_Last' => $cp->InspectorName_Last,
                    'Judgement_Last' => $cp->Judgement_Last,
                    'samples_count' => $cp->samples_count ?? 0,
                ];
            })->values(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Batches/Create');
    }

    public function createCheckpoint(ProductionBatch $productionBatch)
    {
        return Inertia::render('Batches/CreateCheckpoint', [
            'batch' => $productionBatch
        ]);
    }

    public function storeCheckpoint(Request $request, ProductionBatch $productionBatch)
    {
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

        return redirect()->route('production-batches.show', $productionBatch->BatchID)
            ->with('success', 'Checkpoint created successfully!');
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

        // Optional nested checkpoint + samples (for modal flow)
        $checkpoint = $request->input('checkpoint');
        if (is_array($checkpoint)) {
            $cp = new InspectionCheckpoint([
                'BatchID' => $batch->BatchID,
                'CheckpointNumber' => (int)($checkpoint['CheckpointNumber'] ?? 1),
                'InspectorName_First' => $checkpoint['InspectorName_First'] ?? null,
                'Judgement_First' => $checkpoint['Judgement_First'] ?? null,
                'InspectorName_Last' => $checkpoint['InspectorName_Last'] ?? null,
                'Judgement_Last' => $checkpoint['Judgement_Last'] ?? null,
            ]);
            $cp->save();

            $first = $checkpoint['samples_first'] ?? [];
            $last = $checkpoint['samples_last'] ?? [];

            // Accept arrays of strings or array of objects
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
        }

        // If stay=1 (modal flow), return to index with new_batch_id instead of show
        if ($request->boolean('stay')) {
            return redirect()->route('production-batches.index')
                ->with('success', 'Batch created.')
                ->with('new_batch_id', $batch->BatchID);
        }

        return redirect()->route('production-batches.show', ['batch' => $batch->BatchID])
            ->with('success', 'Batch created.');
    }

    public function edit(ProductionBatch $batch)
    {
        return redirect()->route('production-batches.index')
            ->with('edit_batch_id', $batch->BatchID)
            ->with('edit_batch', [
                'BatchID' => $batch->BatchID,
                'ProductionDate' => $batch->ProductionDate,
                'LetterCode' => $batch->LetterCode,
                'QRCode' => $batch->QRCode,
                'MaterialLotNumber' => $batch->MaterialLotNumber,
                'ProduceQty' => $batch->ProduceQty,
                'JobNumber' => $batch->JobNumber,
                'TotalQty' => $batch->TotalQty,
                'Remarks' => $batch->Remarks,
            ]);
    }

    public function update(Request $request, ProductionBatch $batch)
    {
        $data = $request->validate([
            'ProductionDate' => ['required','date'],
            'LetterCode' => ['required','string','max:5'],
            'QRCode' => ['required','string','max:20'],
            'MaterialLotNumber' => ['required','string','max:50'],
            'ProduceQty' => ['required','integer','min:0'],
            'JobNumber' => ['required','string','max:50'],
            'TotalQty' => ['required','integer','min:0'],
            'Remarks' => ['nullable','string'],
        ]);

        $batch->update($data);

        if ($request->boolean('stay')) {
            return redirect()->route('production-batches.index')->with('success', 'Batch updated.');
        }

        return redirect()->route('production-batches.show', ['batch' => $batch->BatchID])
            ->with('success', 'Batch updated.');
    }

    public function destroy(ProductionBatch $batch)
    {
        $batch->delete();
        return redirect()->route('production-batches.index')->with('success', 'Batch deleted.');
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
