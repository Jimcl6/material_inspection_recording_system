<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureFeatureEnabled;
use App\Models\User;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class ApprovalFeatureFlagTest extends TestCase
{
    public function test_disabled_approvals_auto_approve_and_expose_no_modules(): void
    {
        config(['features.approvals' => false]);

        $service = app(ApprovalWorkflowService::class);
        $state = $service->initialState();

        $this->assertSame('approved', $state['status']);
        $this->assertNull($state['submitted_at']);
        $this->assertNotNull($state['approved_at']);
        $this->assertCount(0, $service->modulesForUser(new User));
    }

    public function test_enabled_approvals_create_pending_state(): void
    {
        config(['features.approvals' => true]);

        $state = app(ApprovalWorkflowService::class)->initialState();

        $this->assertSame('pending', $state['status']);
        $this->assertNotNull($state['submitted_at']);
        $this->assertNull($state['approved_at']);
    }

    public function test_disabled_feature_middleware_returns_not_found(): void
    {
        config(['features.approvals' => false]);

        $this->expectException(NotFoundHttpException::class);

        app(EnsureFeatureEnabled::class)->handle(
            Request::create('/approvals'),
            fn () => 'allowed',
            'approvals'
        );
    }
}
