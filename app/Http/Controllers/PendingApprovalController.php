<?php

namespace App\Http\Controllers;

use App\Services\ApprovalWorkflowService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PendingApprovalController extends Controller
{
    public function index(ApprovalWorkflowService $approvalWorkflowService): Response
    {
        $approvalModules = $approvalWorkflowService->modulesForUser(Auth::user(), true);

        return Inertia::render('PendingApprovals/Index', [
            'approvalModules' => $approvalModules,
            'pendingApprovalsCount' => $approvalWorkflowService->totalPending($approvalModules),
        ]);
    }
}
