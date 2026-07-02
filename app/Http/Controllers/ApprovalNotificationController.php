<?php

namespace App\Http\Controllers;

use App\Services\ApprovalNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ApprovalNotificationController extends Controller
{
    public function summary(ApprovalNotificationService $approvalNotificationService): JsonResponse
    {
        return response()->json($approvalNotificationService->summaryForUser(Auth::user()));
    }
}
