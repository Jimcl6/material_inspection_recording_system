<?php

namespace App\Services;

use App\Models\AnnealingCheck;
use App\Models\User;
use App\Models\WeldingChecksheet;
use Illuminate\Support\Collection;

class ApprovalWorkflowService
{
    public function initialState(): array
    {
        if (config('features.approvals', false)) {
            return [
                'status' => 'pending',
                'submitted_at' => now(),
                'approved_at' => null,
            ];
        }

        return [
            'status' => 'approved',
            'submitted_at' => null,
            'approved_at' => now(),
        ];
    }

    public function modulesForUser(User $user, bool $includeRecords = false, int $recordLimit = 5): Collection
    {
        if (!config('features.approvals', false)) {
            return collect();
        }

        return collect($this->workflows())
            ->filter(fn (array $workflow) => $user->hasPermission($workflow['module'], 'approve'))
            ->map(fn (array $workflow) => $this->moduleSummary($workflow, $includeRecords, $recordLimit))
            ->values();
    }

    public function totalPending(Collection $modules): int
    {
        return $modules->sum('pendingCount');
    }

    protected function moduleSummary(array $workflow, bool $includeRecords, int $recordLimit): array
    {
        $query = $workflow['query']();

        $summary = [
            'module' => $workflow['module'],
            'label' => $workflow['label'],
            'routeName' => $workflow['routeName'],
            'pendingCount' => (clone $query)->count(),
        ];

        if ($includeRecords) {
            $summary['records'] = (clone $query)
                ->latest()
                ->limit($recordLimit)
                ->get()
                ->map($workflow['normalize'])
                ->values();
        }

        return $summary;
    }

    protected function workflows(): array
    {
        return [
            [
                'module' => 'annealing',
                'label' => 'Annealing Checks',
                'routeName' => 'annealing-checks.approval',
                'query' => fn () => AnnealingCheck::with(['createdBy', 'pic'])
                    ->where('status', 'pending'),
                'normalize' => fn (AnnealingCheck $check) => [
                    'id' => $check->id,
                    'title' => $check->item_code ?: 'Annealing Check #' . $check->id,
                    'subtitle' => $check->supplier_lot_number
                        ? 'Supplier Lot: ' . $check->supplier_lot_number
                        : 'Quantity: ' . $check->quantity,
                    'date' => optional($check->annealing_date)->toDateString(),
                    'submittedBy' => $check->createdBy?->name ?? 'System',
                    'showRouteName' => 'annealing-checks.show',
                    'showRouteParams' => [$check->id],
                ],
            ],
            [
                'module' => 'welding',
                'label' => 'Welding Checksheet',
                'routeName' => 'welding-checksheets.approval',
                'query' => fn () => WeldingChecksheet::with(['type', 'createdBy', 'operator'])
                    ->where('status', 'pending'),
                'normalize' => fn (WeldingChecksheet $checksheet) => [
                    'id' => $checksheet->id,
                    'title' => $checksheet->item_code ?: 'Welding Checksheet #' . $checksheet->id,
                    'subtitle' => collect([
                        $checksheet->type?->name,
                        $checksheet->job_number ? 'Job: ' . $checksheet->job_number : null,
                        $checksheet->machine_no ? 'Machine: ' . $checksheet->machine_no : null,
                    ])->filter()->implode(' | '),
                    'date' => optional($checksheet->production_date)->toDateString(),
                    'submittedBy' => $checksheet->createdBy?->name ?? 'System',
                    'showRouteName' => 'welding-checksheets.show',
                    'showRouteParams' => [$checksheet->id],
                ],
            ],
        ];
    }
}
