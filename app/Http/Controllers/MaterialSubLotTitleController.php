<?php

namespace App\Http\Controllers;

use App\Models\MaterialSubLotTitle;
use Illuminate\Http\Request;

class MaterialSubLotTitleController extends Controller
{
    /**
     * Get ordered Sub Lot Titles for a given Material Type.
     */
    public function index(string $materialType)
    {
        $titles = MaterialSubLotTitle::forMaterialType($materialType)->get(['title', 'sort_order']);

        return response()->json($titles);
    }
}
