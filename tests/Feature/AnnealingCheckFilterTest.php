<?php

namespace Tests\Feature;

use App\Models\AnnealingCheck;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AnnealingCheckFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::query()->where('slug', 'super_admin')->firstOrFail();
        $this->user = User::factory()->for($role)->create();
        $this->actingAs($this->user);
    }

    public function test_text_search_matches_each_supported_field(): void
    {
        $item = $this->record(['item_code' => 'FILTER-ITEM-ALPHA']);
        $lot = $this->record(['supplier_lot_number' => 'FILTER-LOT-BRAVO']);
        $machine = $this->record(['machine_number' => 'FILTER-MACHINE-CHARLIE']);

        $this->assertSearchFinds('ITEM-ALPHA', $item);
        $this->assertSearchFinds('LOT-BRAVO', $lot);
        $this->assertSearchFinds('MACHINE-CHARLIE', $machine);
    }

    public function test_text_and_date_filters_can_be_combined(): void
    {
        $matching = $this->record([
            'item_code' => 'COMBINED-MATCH',
            'annealing_date' => '2026-03-15',
        ]);
        $this->record([
            'item_code' => 'COMBINED-OUTSIDE',
            'annealing_date' => '2026-04-01',
        ]);
        $this->record([
            'item_code' => 'UNRELATED',
            'annealing_date' => '2026-03-15',
        ]);

        $this->get(route('annealing-checks.index', [
            'search' => 'COMBINED',
            'date_from' => '2026-03-01',
            'date_to' => '2026-03-31',
        ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('annealingChecks.data', 1)
                ->where('annealingChecks.data.0.id', $matching->id));
    }

    public function test_start_date_is_inclusive(): void
    {
        $boundary = $this->record(['annealing_date' => '2026-05-10']);
        $this->record(['annealing_date' => '2026-05-09']);

        $this->get(route('annealing-checks.index', ['date_from' => '2026-05-10']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('annealingChecks.data', 1)
                ->where('annealingChecks.data.0.id', $boundary->id));
    }

    public function test_end_date_is_inclusive(): void
    {
        $boundary = $this->record(['annealing_date' => '2026-05-10']);
        $this->record(['annealing_date' => '2026-05-11']);

        $this->get(route('annealing-checks.index', ['date_to' => '2026-05-10']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('annealingChecks.data', 1)
                ->where('annealingChecks.data.0.id', $boundary->id));
    }

    public function test_same_day_range_returns_that_day(): void
    {
        $boundary = $this->record(['annealing_date' => '2026-02-28']);
        $this->record(['annealing_date' => '2026-02-27']);
        $this->record(['annealing_date' => '2026-03-01']);

        $this->get(route('annealing-checks.index', [
            'date_from' => '2026-02-28',
            'date_to' => '2026-02-28',
        ]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('annealingChecks.data', 1)
                ->where('annealingChecks.data.0.id', $boundary->id));
    }

    public function test_machine_dropdown_uses_exact_matching(): void
    {
        $exact = $this->record(['machine_number' => 'MACHINE-1']);
        $this->record(['machine_number' => 'MACHINE-10']);

        $this->get(route('annealing-checks.index', ['machine_number' => 'MACHINE-1']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('annealingChecks.data', 1)
                ->where('annealingChecks.data.0.id', $exact->id));
    }

    public function test_invalid_date_input_is_rejected(): void
    {
        $this->from(route('annealing-checks.index'))
            ->get(route('annealing-checks.index', ['date_from' => 'not-a-date']))
            ->assertRedirect(route('annealing-checks.index'))
            ->assertSessionHasErrors('date_from');
    }

    public function test_start_date_after_end_date_is_rejected(): void
    {
        $this->from(route('annealing-checks.index'))
            ->get(route('annealing-checks.index', [
                'date_from' => '2026-06-02',
                'date_to' => '2026-06-01',
            ]))
            ->assertRedirect(route('annealing-checks.index'))
            ->assertSessionHasErrors('date_to');
    }

    public function test_pagination_links_preserve_all_active_filters(): void
    {
        foreach (range(1, 16) as $index) {
            $this->record([
                'item_code' => sprintf('PAGE-ITEM-%02d', $index),
                'annealing_date' => '2026-07-10',
                'machine_number' => 'PAGE-MACHINE',
            ]);
        }

        $expectedQuery = [
            'search' => 'PAGE-ITEM',
            'date_from' => '2026-07-01',
            'date_to' => '2026-07-31',
            'machine_number' => 'PAGE-MACHINE',
        ];

        $this->get(route('annealing-checks.index', $expectedQuery))
            ->assertInertia(fn (Assert $page) => $page
                ->where('annealingChecks.current_page', 1)
                ->where('annealingChecks.last_page', 2)
                ->where('annealingChecks.next_page_url', function (?string $url) use ($expectedQuery): bool {
                    if ($url === null) {
                        return false;
                    }

                    parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

                    return (int) ($query['page'] ?? 0) === 2
                        && collect($expectedQuery)->every(
                            fn (string $value, string $key): bool => ($query[$key] ?? null) === $value
                        );
                }));
    }

    public function test_empty_results_are_returned_safely(): void
    {
        $this->record(['item_code' => 'EXISTING-ITEM']);

        $this->get(route('annealing-checks.index', ['search' => 'NO-SUCH-ITEM']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('annealingChecks.data', 0)
                ->where('annealingChecks.total', 0));
    }

    public function test_filters_are_forwarded_to_view_and_edit_screens(): void
    {
        $record = $this->record();
        $filters = [
            'search' => 'TEST',
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
            'machine_number' => 'TEST-MACHINE-01',
        ];

        $this->get(route('annealing-checks.show', ['annealing_check' => $record->id, ...$filters]))
            ->assertInertia(fn (Assert $page) => $page->where('filters', $filters));

        $this->get(route('annealing-checks.edit', ['annealing_check' => $record->id, ...$filters]))
            ->assertInertia(fn (Assert $page) => $page->where('filters', $filters));
    }

    private function assertSearchFinds(string $search, AnnealingCheck $expected): void
    {
        $this->get(route('annealing-checks.index', ['search' => $search]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('annealingChecks.data', 1)
                ->where('annealingChecks.data.0.id', $expected->id));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function record(array $attributes = []): AnnealingCheck
    {
        static $sequence = 0;
        $sequence++;

        return AnnealingCheck::factory()->create([
            'item_code' => sprintf('TEST-ITEM-%03d', $sequence),
            'supplier_lot_number' => sprintf('TEST-LOT-%03d', $sequence),
            'annealing_date' => '2026-01-15',
            'machine_number' => sprintf('TEST-MACHINE-%03d', $sequence),
            'pic_id' => $this->user->id,
            'created_by' => $this->user->id,
            ...$attributes,
        ]);
    }
}
