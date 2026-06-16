<?php

namespace App\Http\Controllers;

use App\Services\ApprovalWorkflowService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PendingApprovalController extends Controller
{
    public function index(ApprovalWorkflowService $approvalWorkflowService): \Inertia\Response
    {
        $approvalModules = $approvalWorkflowService->modulesForUser(Auth::user(), true);

        return Inertia::render('PendingApprovals/Index', [
            'approvalModules' => $approvalModules,
            'pendingApprovalsCount' => $approvalWorkflowService->totalPending($approvalModules),
        ]);
    }
}
