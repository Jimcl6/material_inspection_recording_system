<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityService;
use App\Services\ApprovalWorkflowService;
use App\Services\DashboardReportingService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(
        ApprovalWorkflowService $approvalWorkflowService,
        DashboardReportingService $dashboardReportingService
    ): \Inertia\Response {
        /** @var User $user */
        $user = Auth::user();
        $report = $dashboardReportingService->reportFor($user);

        $approvalModules = $approvalWorkflowService->modulesForUser($user);
        $pendingApprovalsCount = $approvalWorkflowService->totalPending($approvalModules);

        return Inertia::render('Dashboard', [
            ...$report,
            'recentActivity' => ActivityService::getTodayActivitiesForUser(20), // Get more for scrolling
            'approvalModules' => $approvalModules,
            'pendingApprovalsCount' => $approvalModules->isNotEmpty() ? $pendingApprovalsCount : null,
        ]);
    }
}
