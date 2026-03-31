<?php

namespace App\Http\Controllers;

use App\Services\ActivityService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\AnnealingCheck;

class DashboardController extends Controller
{
    public function index(): \Inertia\Response
    {
        $user = Auth::user();
        
        // Get actual stats
        $totalInspections = AnnealingCheck::count();
        $passed = AnnealingCheck::where('status', 'approved')->count();
        $failed = AnnealingCheck::where('status', 'rejected')->count();
        $pending = AnnealingCheck::where('status', 'pending')->count();
        
        // Calculate percentage changes compared to last month
        $lastMonth = now()->subMonth();
        $lastMonthTotal = AnnealingCheck::where('created_at', '<', $lastMonth)->count();
        $thisMonthTotal = AnnealingCheck::where('created_at', '>=', $lastMonth)->count();
        $totalChange = $lastMonthTotal > 0 ? round((($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 0) : 0;
        
        return Inertia::render('Dashboard', [
            'stats' => [
                ['name' => 'Total Inspections', 'value' => number_format($totalInspections), 'change' => $totalChange >= 0 ? "+{$totalChange}%" : "{$totalChange}%", 'changeType' => $totalChange >= 0 ? 'increase' : 'decrease'],
                ['name' => 'Passed', 'value' => number_format($passed), 'change' => '+5%', 'changeType' => 'increase'],
                ['name' => 'Failed', 'value' => number_format($failed), 'change' => '-2%', 'changeType' => 'decrease'],
                ['name' => 'Pending Review', 'value' => number_format($pending), 'change' => '+18%', 'changeType' => 'increase'],
            ],
            'recentActivity' => ActivityService::getTodayActivitiesForUser(20), // Get more for scrolling
            'pendingApprovalsCount' => in_array($user->role?->slug, ['admin', 'inspector']) ? $pending : null,
        ]);
    }
}