<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                ['name' => 'Total Inspections', 'value' => '12,345', 'change' => '+12%', 'changeType' => 'increase'],
                ['name' => 'Passed', 'value' => '8,456', 'change' => '+5%', 'changeType' => 'increase'],
                ['name' => 'Failed', 'value' => '2,345', 'change' => '-2%', 'changeType' => 'decrease'],
                ['name' => 'Pending Review', 'value' => '1,544', 'change' => '+18%', 'changeType' => 'increase'],
            ],
            'recentActivity' => [
                ['id' => 1, 'type' => 'inspection', 'action' => 'Completed', 'user' => 'John Doe', 'time' => '2 hours ago'],
                ['id' => 2, 'type' => 'user', 'action' => 'Logged in', 'user' => 'Jane Smith', 'time' => '3 hours ago'],
                ['id' => 3, 'type' => 'inspection', 'action' => 'Started', 'user' => 'Robert Johnson', 'time' => '5 hours ago'],
            ]
        ]);
    }
}