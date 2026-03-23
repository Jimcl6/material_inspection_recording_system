<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class MaterialTypeController extends Controller
{
    /**
     * Get sub-lot fields for a specific material type
     */
    public function getSubLotFields($materialType)
    {
        $fields = Config::get("sublot_fields.{$materialType}", []);
        
        return response()->json($fields);
    }
    
    /**
     * Get all material types with their sub-lot fields
     */
    public function getAllMaterialTypes()
    {
        $allFields = Config::get('sublot_fields', []);
        
        return response()->json($allFields);
    }
}
