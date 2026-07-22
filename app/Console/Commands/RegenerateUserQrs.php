<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RegenerateUserQrs extends Command
{
    protected $signature = 'mirs:regenerate-user-qrs
                            {--dry-run : Report the affected users without changing stored QR data}
                            {--active-only : Include only users whose account status is active}';

    protected $description = 'Re-encrypt existing stored user QR badges with the current application key';

    public function handle(QrCodeService $qrCodeService): int
    {
        $query = User::query()->with('qrCode')->orderBy('id');

        if ($this->option('active-only')) {
            $query->active();
        }

        $users = $query->get();
        $eligible = $users->filter(fn (User $user): bool => $user->qrCode !== null);
        $missing = $users->count() - $eligible->count();

        $this->line('Users considered: '.$users->count());
        $this->line('Stored badges eligible: '.$eligible->count());
        $this->line('Users without a stored badge: '.$missing);

        if ($eligible->isNotEmpty()) {
            $this->line('Eligible user IDs: '.$eligible->pluck('id')->implode(', '));
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run complete. No QR records were changed.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($eligible, $qrCodeService): void {
            foreach ($eligible as $user) {
                $qrCodeService->regenerateStoredQrCode($user);
            }
        });

        $this->info('Stored badges regenerated: '.$eligible->count());

        return self::SUCCESS;
    }
}
