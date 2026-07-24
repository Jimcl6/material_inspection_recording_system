<?php

namespace App\Services;

use App\Models\AnnealingCheck;
use App\Models\MagnetismChecksheet;
use App\Models\MaterialPart;
use App\Models\TempRecord;
use App\Models\TorqueRecord;
use App\Models\User;
use App\Models\WeldingChecksheet;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class DashboardReportingService
{
    /**
     * @var array<string, array{
     *     label: string,
     *     model: class-string<\Illuminate\Database\Eloquent\Model>,
     *     date_column?: string,
     *     calendar_columns?: bool,
     *     approvals: bool
     * }>
     */
    private const MODULES = [
        'annealing' => [
            'label' => 'Annealing',
            'model' => AnnealingCheck::class,
            'date_column' => 'annealing_date',
            'approvals' => true,
        ],
        'temperature' => [
            'label' => 'Temperature',
            'model' => TempRecord::class,
            'date_column' => 'date',
            'approvals' => true,
        ],
        'torque' => [
            'label' => 'Torque',
            'model' => TorqueRecord::class,
            'date_column' => 'date',
            'approvals' => true,
        ],
        'magnetism' => [
            'label' => 'Magnetism',
            'model' => MagnetismChecksheet::class,
            'calendar_columns' => true,
            'approvals' => false,
        ],
        'material' => [
            'label' => 'Material Monitoring',
            'model' => MaterialPart::class,
            'date_column' => 'date',
            'approvals' => false,
        ],
        'welding' => [
            'label' => 'Welding',
            'model' => WeldingChecksheet::class,
            'date_column' => 'production_date',
            'approvals' => true,
        ],
    ];

    /**
     * @return array{
     *     dashboardSummary: array{
     *         totalRecords: int,
     *         currentMonthTotal: int,
     *         previousMonthTotal: int,
     *         trendPercentage: float|null,
     *         approvals: array{approved: int, pending: int, rejected: int}|null
     *     },
     *     moduleStats: array<string, array<string, mixed>>
     * }
     */
    public function reportFor(User $user, ?CarbonImmutable $asOf = null): array
    {
        $asOf ??= CarbonImmutable::now();
        $currentMonthStart = $asOf->startOfMonth();
        $nextMonthStart = $currentMonthStart->addMonth();
        $previousMonthStart = $currentMonthStart->subMonth();

        $visibleModules = $this->visibleModules($user);
        $moduleStats = [];

        foreach ($visibleModules as $module => $definition) {
            $counts = isset($definition['calendar_columns'])
                ? $this->aggregateCalendarModule(
                    $definition['model'],
                    $currentMonthStart,
                    $previousMonthStart
                )
                : $this->aggregateDateModule(
                    $definition['model'],
                    $definition['date_column'],
                    $previousMonthStart,
                    $currentMonthStart,
                    $nextMonthStart,
                    $definition['approvals']
                );

            $stats = [
                'label' => $definition['label'],
                'totalRecords' => $counts['totalRecords'],
                'currentMonthTotal' => $counts['currentMonthTotal'],
                'previousMonthTotal' => $counts['previousMonthTotal'],
                'trendPercentage' => $this->trend(
                    $counts['currentMonthTotal'],
                    $counts['previousMonthTotal']
                ),
                'approvals' => $definition['approvals']
                    ? [
                        'approved' => $counts['approved'],
                        'pending' => $counts['pending'],
                        'rejected' => $counts['rejected'],
                    ]
                    : null,
            ];

            $moduleStats[$module] = $stats;
        }

        $summary = [
            'totalRecords' => array_sum(array_column($moduleStats, 'totalRecords')),
            'currentMonthTotal' => array_sum(array_column($moduleStats, 'currentMonthTotal')),
            'previousMonthTotal' => array_sum(array_column($moduleStats, 'previousMonthTotal')),
            'trendPercentage' => null,
            'approvals' => $this->approvalSummary($moduleStats),
        ];
        $summary['trendPercentage'] = $this->trend(
            $summary['currentMonthTotal'],
            $summary['previousMonthTotal']
        );

        return [
            'dashboardSummary' => $summary,
            'moduleStats' => $moduleStats,
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function visibleModules(User $user): array
    {
        $user->loadMissing(['role.permissions', 'position.permissions']);

        if ($user->isSuperAdmin()) {
            return self::MODULES;
        }

        $roleModules = collect($user->role?->permissions)
            ->where('action', 'view')
            ->pluck('module');
        $positionModules = collect($user->position?->permissions)
            ->where('action', 'view')
            ->pluck('module');
        $permittedModules = $roleModules->merge($positionModules)->unique();

        return array_filter(
            self::MODULES,
            fn (string $module): bool => $permittedModules->contains($module),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @return array{
     *     totalRecords: int,
     *     currentMonthTotal: int,
     *     previousMonthTotal: int,
     *     approved: int,
     *     pending: int,
     *     rejected: int
     * }
     */
    private function aggregateDateModule(
        string $model,
        string $dateColumn,
        CarbonImmutable $previousMonthStart,
        CarbonImmutable $currentMonthStart,
        CarbonImmutable $nextMonthStart,
        bool $supportsApprovals
    ): array {
        /** @var Builder<\Illuminate\Database\Eloquent\Model> $query */
        $query = $model::query();
        $query->selectRaw('COUNT(*) AS total_records')
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN {$dateColumn} >= ? AND {$dateColumn} < ? THEN 1 ELSE 0 END), 0) AS current_month_total",
                [$currentMonthStart->toDateString(), $nextMonthStart->toDateString()]
            )
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN {$dateColumn} >= ? AND {$dateColumn} < ? THEN 1 ELSE 0 END), 0) AS previous_month_total",
                [$previousMonthStart->toDateString(), $currentMonthStart->toDateString()]
            );

        if ($supportsApprovals) {
            $query
                ->selectRaw("COALESCE(SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END), 0) AS approved")
                ->selectRaw("COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) AS pending")
                ->selectRaw("COALESCE(SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END), 0) AS rejected");
        }

        $result = $query->toBase()->first();

        return [
            'totalRecords' => (int) ($result->total_records ?? 0),
            'currentMonthTotal' => (int) ($result->current_month_total ?? 0),
            'previousMonthTotal' => (int) ($result->previous_month_total ?? 0),
            'approved' => (int) ($result->approved ?? 0),
            'pending' => (int) ($result->pending ?? 0),
            'rejected' => (int) ($result->rejected ?? 0),
        ];
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     * @return array{
     *     totalRecords: int,
     *     currentMonthTotal: int,
     *     previousMonthTotal: int,
     *     approved: int,
     *     pending: int,
     *     rejected: int
     * }
     */
    private function aggregateCalendarModule(
        string $model,
        CarbonImmutable $currentMonthStart,
        CarbonImmutable $previousMonthStart
    ): array {
        /** @var Builder<\Illuminate\Database\Eloquent\Model> $query */
        $query = $model::query();
        $result = $query
            ->selectRaw('COUNT(*) AS total_records')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN year = ? AND month = ? THEN 1 ELSE 0 END), 0) AS current_month_total',
                [$currentMonthStart->year, $currentMonthStart->month]
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN year = ? AND month = ? THEN 1 ELSE 0 END), 0) AS previous_month_total',
                [$previousMonthStart->year, $previousMonthStart->month]
            )
            ->toBase()
            ->first();

        return [
            'totalRecords' => (int) ($result->total_records ?? 0),
            'currentMonthTotal' => (int) ($result->current_month_total ?? 0),
            'previousMonthTotal' => (int) ($result->previous_month_total ?? 0),
            'approved' => 0,
            'pending' => 0,
            'rejected' => 0,
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $moduleStats
     * @return array{approved: int, pending: int, rejected: int}|null
     */
    private function approvalSummary(array $moduleStats): ?array
    {
        $approvalStats = array_filter(
            $moduleStats,
            fn (array $stats): bool => is_array($stats['approvals'])
        );

        if ($approvalStats === []) {
            return null;
        }

        $summary = ['approved' => 0, 'pending' => 0, 'rejected' => 0];

        foreach ($approvalStats as $stats) {
            foreach (array_keys($summary) as $status) {
                $summary[$status] += (int) $stats['approvals'][$status];
            }
        }

        return $summary;
    }

    private function trend(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
