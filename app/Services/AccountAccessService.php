<?php

namespace App\Services;

use App\Models\SecurityAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Throwable;

class AccountAccessService
{
    public function revoke(User $user, string $source): void
    {
        $revokedTokenCount = 0;

        try {
            $revokedTokenCount = $user->tokens()->delete();
        } catch (Throwable $exception) {
            Log::error('Account token revocation could not be persisted.', [
                'target_user_id' => $user->id,
                'source' => $source,
                'exception_class' => $exception::class,
            ]);
        }

        try {
            SecurityAuditLog::query()->create([
                'actor' => auth()->check() ? 'user:'.auth()->id() : 'system:account-status',
                'target_user_id' => $user->id,
                'action' => 'account_access_revoked',
                'occurred_at' => now(),
                'context' => [
                    'source' => $source,
                    'account_status' => $user->status,
                    'revoked_token_count' => $revokedTokenCount,
                ],
            ]);
        } catch (Throwable $exception) {
            Log::error('Account access revocation audit could not be persisted.', [
                'target_user_id' => $user->id,
                'source' => $source,
                'account_status' => $user->status,
                'exception_class' => $exception::class,
            ]);
        }
    }
}
