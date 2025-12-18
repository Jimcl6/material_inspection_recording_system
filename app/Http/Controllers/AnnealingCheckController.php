<?php

namespace App\Http\Controllers;

use App\Models\AnnealingCheck;
use App\Models\TemperatureReading;
use App\Http\Requests\StoreAnnealingCheckRequest;
use App\Http\Requests\UpdateAnnealingCheckRequest;
use App\Http\Requests\ImportAnnealingCheckRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnnealingChecksExport;
use App\Imports\AnnealingChecksImport;

class AnnealingCheckController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request): \Inertia\Response
    {
        $query = AnnealingCheck::with(['pic', 'checkedBy', 'verifiedBy', 'temperatureReadings'])
            ->latest();

        // Apply filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                  ->orWhere('supplier_lot_number', 'like', "%{$search}%")
                  ->orWhere('machine_number', 'like', "%{$search}%");
            });
        }

        $annealingChecks = $query->paginate(15);

        return Inertia::render('AnnealingChecks/Index', [
            'annealingChecks' => $annealingChecks,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create(): \Inertia\Response
    {
        return Inertia::render('AnnealingChecks/Create', [
            'users' => \App\Models\User::select('id', 'name')->orderBy('name')->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAnnealingCheckRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreAnnealingCheckRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        $temperatureReadings = $data['temperature_readings'];
        unset($data['temperature_readings']);

        $annealingCheck = AnnealingCheck::create($data);

        // Add temperature readings
        foreach ($temperatureReadings as $reading) {
            $reading['recorded_by'] = Auth::id();
            $annealingCheck->temperatureReadings()->create($reading);
        }

        return redirect()->route('annealing-checks.index')
            ->with('success', 'Annealing check created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AnnealingCheck  $annealingCheck
     * @return \Inertia\Response
     */
    public function show(AnnealingCheck $annealingCheck): \Inertia\Response
    {
        $annealingCheck->load(['pic', 'checkedBy', 'verifiedBy', 'temperatureReadings']);
        
        return Inertia::render('AnnealingChecks/Show', [
            'annealingCheck' => $annealingCheck
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AnnealingCheck  $annealingCheck
     * @return \Inertia\Response
     */
    public function edit(AnnealingCheck $annealingCheck): \Inertia\Response
    {
        $annealingCheck->load('temperatureReadings');
        
        return Inertia::render('AnnealingChecks/Edit', [
            'annealingCheck' => $annealingCheck
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAnnealingCheckRequest  $request
     * @param  \App\Models\AnnealingCheck  $annealingCheck
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateAnnealingCheckRequest $request, AnnealingCheck $annealingCheck): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = Auth::id();

        // Update main check data
        $annealingCheck->update($data);

        // Update or create temperature readings
        if (isset($data['temperature_readings'])) {
            $existingIds = [];
            
            foreach ($data['temperature_readings'] as $reading) {
                $reading['recorded_by'] = Auth::id();
                
                if (isset($reading['id'])) {
                    // Update existing reading
                    $temperatureReading = $annealingCheck->temperatureReadings()
                        ->findOrFail($reading['id']);
                    $temperatureReading->update($reading);
                    $existingIds[] = $temperatureReading->id;
                } else {
                    // Create new reading
                    $newReading = $annealingCheck->temperatureReadings()->create($reading);
                    $existingIds[] = $newReading->id;
                }
            }
            
            // Delete readings not in the updated list
            $annealingCheck->temperatureReadings()
                ->whereNotIn('id', $existingIds)
                ->delete();
        }

        return redirect()->route('annealing-checks.index')
            ->with('success', 'Annealing check updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AnnealingCheck  $annealingCheck
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(AnnealingCheck $annealingCheck)
    {
        $annealingCheck->delete();
        
        return redirect()->route('annealing-checks.index')
            ->with('success', 'Annealing check deleted successfully!');
    }

    /**
     * Show the import form.
     *
     * @return \Inertia\Response
     */
    public function importForm(): \Inertia\Response
    {
        return Inertia::render('AnnealingChecks/Import');
    }

    /**
     * Import annealing checks from Excel file.
     *
     * @param  \App\Http\Requests\ImportAnnealingCheckRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(ImportAnnealingCheckRequest $request): \Illuminate\Http\RedirectResponse
    {
        $file = $request->file('file');
        $overwrite = $request->boolean('overwrite', false);

        try {
            if ($overwrite) {
                // Delete existing records if overwrite is true
                AnnealingCheck::truncate();
            }

            Excel::import(new AnnealingChecksImport, $file);

            return redirect()->route('annealing-checks.index')
                ->with('success', 'Annealing checks imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    /**
     * Export annealing checks to Excel file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new AnnealingChecksExport, 'annealing-checks-' . now()->format('Y-m-d') . '.xlsx');
    }
}
