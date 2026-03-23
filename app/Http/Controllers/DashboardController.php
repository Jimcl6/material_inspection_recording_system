<?php

namespace App\Http\Controllers;

use App\Services\ActivityService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                ['name' => 'Total Inspections', 'value' => '12,345', 'change' => '+12%', 'changeType' => 'increase'],
                ['name' => 'Passed', 'value' => '8,456', 'change' => '+5%', 'changeType' => 'increase'],
                ['name' => 'Failed', 'value' => '2,345', 'change' => '-2%', 'changeType' => 'decrease'],
                ['name' => 'Pending Review', 'value' => '1,544', 'change' => '+18%', 'changeType' => 'increase'],
            ],
            'recentActivity' => ActivityService::getTodayActivitiesForUser(20) // Get more for scrolling
        ]);
    }
}