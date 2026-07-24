<?php

namespace Tests\Feature;

use App\Models\AnnealingCheck;
use App\Models\MagnetismChecksheet;
use App\Models\MaterialPart;
use App\Models\ModificationLog;
use App\Models\Role;
use App\Models\TempRecord;
use App\Models\TorqueRecord;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\WeldingChecksheet;
use App\Models\WeldingChecksheetType;
use App\Services\DashboardReportingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardReportingTest extends TestCase
{
    use RefreshDatabase;

    private DashboardReportingService $reporting;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reporting = app(DashboardReportingService::class);
        config()->set('features.approvals', false);
    }

    public function test_one_module_permission_scopes_global_and_module_totals(): void
    {
        $user = $this->userWithViewPermissions(['annealing']);
        $this->annealing('2026-07-02', 'approved');
        $this->annealing('2026-07-03', 'pending');
        $this->annealing('2026-06-20', 'rejected');
        TempRecord::factory()->create(['date' => '2026-07-04']);

        $report = $this->reporting->reportFor($user, CarbonImmutable::parse('2026-07-24'));

        $this->assertSame(['annealing'], array_keys($report['moduleStats']));
        $this->assertSame(3, $report['dashboardSummary']['totalRecords']);
        $this->assertSame(2, $report['dashboardSummary']['currentMonthTotal']);
        $this->assertSame(1, $report['dashboardSummary']['previousMonthTotal']);
        $this->assertSame(100.0, $report['dashboardSummary']['trendPercentage']);
        $this->assertSame([
            'approved' => 1,
            'pending' => 1,
            'rejected' => 1,
        ], $report['dashboardSummary']['approvals']);
    }

    public function test_several_module_permissions_sum_only_visible_modules(): void
    {
        $user = $this->userWithViewPermissions(['annealing', 'temperature', 'material']);
        $this->annealing('2026-07-01');
        TempRecord::factory()->create(['date' => '2026-07-02']);
        MaterialPart::factory()->create(['date' => '2026-06-30']);
        TorqueRecord::factory()->create(['date' => '2026-07-03']);

        $report = $this->reporting->reportFor($user, CarbonImmutable::parse('2026-07-24'));

        $this->assertSame(
            ['annealing', 'temperature', 'material'],
            array_keys($report['moduleStats'])
        );
        $this->assertSame(3, $report['dashboardSummary']['totalRecords']);
        $this->assertSame(2, $report['dashboardSummary']['currentMonthTotal']);
        $this->assertSame(1, $report['dashboardSummary']['previousMonthTotal']);
    }

    public function test_user_with_no_module_permissions_receives_empty_neutral_report(): void
    {
        $user = $this->userWithViewPermissions([]);
        $this->annealing('2026-07-01');

        $report = $this->reporting->reportFor($user, CarbonImmutable::parse('2026-07-24'));

        $this->assertSame([], $report['moduleStats']);
        $this->assertSame([
            'totalRecords' => 0,
            'currentMonthTotal' => 0,
            'previousMonthTotal' => 0,
            'trendPercentage' => null,
            'approvals' => null,
        ], $report['dashboardSummary']);
    }

    public function test_current_and_previous_month_use_calendar_boundaries(): void
    {
        $user = $this->userWithViewPermissions(['material']);
        MaterialPart::factory()->create(['date' => '2026-05-31']);
        MaterialPart::factory()->create(['date' => '2026-06-01']);
        MaterialPart::factory()->create(['date' => '2026-06-30']);
        MaterialPart::factory()->create(['date' => '2026-07-01']);
        MaterialPart::factory()->create(['date' => '2026-07-31']);
        MaterialPart::factory()->create(['date' => '2026-08-01']);

        $stats = $this->reporting
            ->reportFor($user, CarbonImmutable::parse('2026-07-24'))['moduleStats']['material'];

        $this->assertSame(2, $stats['currentMonthTotal']);
        $this->assertSame(2, $stats['previousMonthTotal']);
        $this->assertSame(0.0, $stats['trendPercentage']);
    }

    public function test_december_to_january_boundary_is_correct_for_date_and_calendar_modules(): void
    {
        $user = $this->userWithViewPermissions(['annealing', 'magnetism']);
        $this->annealing('2025-12-31');
        $this->annealing('2026-01-01');
        $this->magnetism(12, 2025);
        $this->magnetism(1, 2026);

        $stats = $this->reporting
            ->reportFor($user, CarbonImmutable::parse('2026-01-15'))['moduleStats'];

        $this->assertSame(1, $stats['annealing']['currentMonthTotal']);
        $this->assertSame(1, $stats['annealing']['previousMonthTotal']);
        $this->assertSame(1, $stats['magnetism']['currentMonthTotal']);
        $this->assertSame(1, $stats['magnetism']['previousMonthTotal']);
    }

    public function test_leap_day_is_counted_in_february(): void
    {
        $user = $this->userWithViewPermissions(['torque']);
        TorqueRecord::factory()->create(['date' => '2024-02-29']);
        TorqueRecord::factory()->create(['date' => '2024-03-01']);

        $stats = $this->reporting
            ->reportFor($user, CarbonImmutable::parse('2024-02-10'))['moduleStats']['torque'];

        $this->assertSame(1, $stats['currentMonthTotal']);
        $this->assertSame(0, $stats['previousMonthTotal']);
    }

    public function test_zero_previous_month_returns_null_trend(): void
    {
        $user = $this->userWithViewPermissions(['temperature']);
        TempRecord::factory()->create(['date' => '2026-07-01']);

        $stats = $this->reporting
            ->reportFor($user, CarbonImmutable::parse('2026-07-24'))['moduleStats']['temperature'];

        $this->assertNull($stats['trendPercentage']);
    }

    public function test_approval_aggregation_excludes_modules_without_approval_states(): void
    {
        $user = $this->userWithViewPermissions([
            'annealing',
            'temperature',
            'torque',
            'magnetism',
            'material',
            'welding',
        ]);
        $this->annealing('2026-07-01', 'approved');
        TempRecord::factory()->create(['date' => '2026-07-01', 'status' => 'pending']);
        TorqueRecord::factory()->create(['date' => '2026-07-01', 'status' => 'rejected']);
        $this->welding('2026-07-01', 'approved');
        $this->magnetism(7, 2026);
        MaterialPart::factory()->create(['date' => '2026-07-01']);

        $report = $this->reporting->reportFor($user, CarbonImmutable::parse('2026-07-24'));

        $this->assertSame([
            'approved' => 2,
            'pending' => 1,
            'rejected' => 1,
        ], $report['dashboardSummary']['approvals']);
        $this->assertNull($report['moduleStats']['magnetism']['approvals']);
        $this->assertNull($report['moduleStats']['material']['approvals']);
    }

    public function test_modification_logs_are_never_included_in_dashboard_statistics(): void
    {
        $user = $this->userWithViewPermissions(['modification']);
        ModificationLog::query()->create([
            'model_code' => 'SYNTHETIC-MODEL',
            'item_for_modification' => 'Synthetic item',
            'recorded_by' => 'Synthetic user',
            'prod_date' => '2026-07-01',
        ]);

        $report = $this->reporting->reportFor($user, CarbonImmutable::parse('2026-07-24'));

        $this->assertArrayNotHasKey('modification', $report['moduleStats']);
        $this->assertSame(0, $report['dashboardSummary']['totalRecords']);
    }

    public function test_dashboard_inertia_props_use_the_new_names_and_structure(): void
    {
        $user = $this->userWithViewPermissions(['annealing']);
        $this->annealing('2026-07-01');
        CarbonImmutable::setTestNow('2026-07-24 10:00:00');

        try {
            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('Dashboard')
                    ->missing('stats')
                    ->has('dashboardSummary', fn (Assert $summary) => $summary
                        ->where('totalRecords', 1)
                        ->where('currentMonthTotal', 1)
                        ->where('previousMonthTotal', 0)
                        ->where('trendPercentage', null)
                        ->has('approvals.approved')
                        ->has('approvals.pending')
                        ->has('approvals.rejected'))
                    ->has('moduleStats.annealing', fn (Assert $stats) => $stats
                        ->where('label', 'Annealing')
                        ->where('totalRecords', 1)
                        ->where('currentMonthTotal', 1)
                        ->where('previousMonthTotal', 0)
                        ->where('trendPercentage', null)
                        ->has('approvals')));
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    /**
     * @param  array<int, string>  $modules
     */
    private function userWithViewPermissions(array $modules): User
    {
        $role = Role::factory()->create();
        $user = User::factory()->for($role)->create();

        foreach ($modules as $module) {
            $permission = UserPermission::query()->firstOrCreate(
                ['slug' => "{$module}.view"],
                [
                    'name' => "View {$module} test records",
                    'description' => 'Synthetic test permission',
                    'module' => $module,
                    'action' => 'view',
                ]
            );
            $role->grantPermission($permission);
        }

        return $user;
    }

    private function annealing(string $date, string $status = 'approved'): AnnealingCheck
    {
        $this->sequence++;
        $user = User::factory()->create();

        return AnnealingCheck::factory()->create([
            'item_code' => sprintf('DASH-ANNEAL-%03d', $this->sequence),
            'supplier_lot_number' => sprintf('DASH-LOT-%03d', $this->sequence),
            'machine_number' => sprintf('DASH-MACHINE-%03d', $this->sequence),
            'annealing_date' => $date,
            'status' => $status,
            'pic_id' => $user->id,
            'created_by' => $user->id,
        ]);
    }

    private function magnetism(int $month, int $year): MagnetismChecksheet
    {
        $this->sequence++;

        return MagnetismChecksheet::factory()->create([
            'item_code' => sprintf('DASH-MAG-%03d', $this->sequence),
            'machine_no' => sprintf('DASH-MACHINE-%03d', $this->sequence),
            'month' => $month,
            'year' => $year,
        ]);
    }

    private function welding(string $date, string $status): WeldingChecksheet
    {
        $this->sequence++;
        $type = WeldingChecksheetType::factory()->create([
            'key' => sprintf('dashboard-welding-%03d', $this->sequence),
        ]);

        return WeldingChecksheet::factory()->for($type, 'type')->create([
            'item_code' => sprintf('DASH-WELD-%03d', $this->sequence),
            'production_date' => $date,
            'status' => $status,
        ]);
    }
}
