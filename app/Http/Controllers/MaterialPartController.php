<?php

namespace App\Http\Controllers;

use App\Models\MaterialPart;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MaterialPartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MaterialPart::query();

        // Filter by material type
        if ($request->filled('material_type')) {
            $query->byMaterialType($request->material_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        // Search by lot number
        if ($request->filled('search')) {
            $query->byLotNumber($request->search);
        }

        // Sort
        $sortField = $request->get('sort', 'date');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $materialParts = $query->paginate(25)->appends($request->query());

        return Inertia::render('MaterialMonitoringChecksheets/Index', [
            'materialParts' => $materialParts,
            'materialTypes' => MaterialPart::getMaterialTypes(),
            'filters' => $request->only(['material_type', 'date_from', 'date_to', 'search', 'sort', 'direction']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('MaterialMonitoringChecksheets/Create', [
            'materialTypes' => MaterialPart::getMaterialTypes(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_type' => 'required|string|max:50',
            'date' => 'required|date',
            'item_block_code' => 'required|string|max:100',
            'letter_code' => 'nullable|string|max:1',
            'main_lot_number' => 'nullable|string|max:50',
            'sub_lot_numbers' => 'nullable|array',
            'sub_lot_numbers.sub_lots' => 'nullable|array',
            'sub_lot_numbers.sub_lots.*' => 'nullable|string|max:100',
            'produced_qty' => 'required|integer|min:0',
            'operator' => 'nullable|string|max:100',
            'job_number' => 'nullable|string|max:100',
        ]);

        // Auto-generate letter code if not provided
        if (empty($validated['letter_code'])) {
            $validated['letter_code'] = MaterialPart::getNextLetterCode(
                $validated['item_block_code'], 
                $validated['date']
            );
        }

        MaterialPart::create($validated);

        return redirect()->route('material-monitoring-checksheets.index')
            ->with('success', 'Material part created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($material_monitoring_checksheet)
{
    $materialPart = MaterialPart::findOrFail($material_monitoring_checksheet);
    
    return Inertia::render('MaterialMonitoringChecksheets/Show', [
        'materialPart' => $materialPart,
        'materialTypes' => MaterialPart::getMaterialTypes(),
    ]);
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($material_monitoring_checksheet)
{
    $materialPart = MaterialPart::findOrFail($material_monitoring_checksheet);
    
    // Debug: Log what we're getting
        \Log::info('Edit method called with materialPart:', [
            'materialPart' => $materialPart,
            'materialPart->toArray()' => $materialPart->toArray(),
            'request params' => request()->all()
        ]);
        
        return Inertia::render('MaterialMonitoringChecksheets/Edit', [
            'materialPart' => $materialPart,
            'materialTypes' => MaterialPart::getMaterialTypes(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MaterialPart $materialPart)
    {
        $validated = $request->validate([
            'material_type' => 'required|string|max:50',
            'date' => 'required|date',
            'item_block_code' => 'required|string|max:100',
            'letter_code' => 'nullable|string|max:1',
            'main_lot_number' => 'required|string|max:50',
            'sub_lot_numbers' => 'nullable|array',
            'sub_lot_numbers.sub_lots' => 'nullable|array',
            'sub_lot_numbers.sub_lots.*' => 'nullable|string|max:100',
            'produced_qty' => 'required|integer|min:0',
            'operator' => 'nullable|string|max:100',
            'job_number' => 'nullable|string|max:100',
        ]);

        $materialPart->update($validated);

        return redirect()->route('material-monitoring-checksheets.index')
            ->with('success', 'Material part updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaterialPart $materialPart)
    {
        $materialPart->delete();

        return redirect()->route('material-monitoring-checksheets.index')
            ->with('success', 'Material part deleted successfully.');
    }

    /**
     * Get material parts data for AI model
     */
    public function getForAI(Request $request)
    {
        $query = MaterialPart::query();

        // Apply filters
        if ($request->filled('material_types')) {
            $query->whereIn('material_type', $request->material_types);
        }

        if ($request->filled('date_from')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        $materialParts = $query->get();

        return response()->json([
            'data' => $materialParts,
            'total' => $materialParts->count(),
        ]);
    }
}
