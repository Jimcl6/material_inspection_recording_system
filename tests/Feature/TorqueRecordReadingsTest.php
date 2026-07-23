<?php

namespace Tests\Feature;

use App\Imports\TorqueChecksheetImport;
use App\Models\Activity;
use App\Models\Role;
use App\Models\TorqueRecord;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TorqueRecordReadingsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_store_persists_up_to_eight_readings_for_each_period(): void
    {
        $payload = $this->payload([
            'readings' => [
                'am' => collect(range(1, 8))
                    ->map(fn ($number) => ['torque_value' => (string) (10 + $number / 10)])
                    ->all(),
                'pm' => [
                    ['torque_value' => '12.25'],
                    ['torque_value' => '12.50'],
                ],
            ],
        ]);

        $response = $this->actingAs($this->superAdmin())->post(route('torque-records.store'), $payload);

        $record = TorqueRecord::where('model_series', $payload['model_series'])->firstOrFail();

        $response->assertRedirect(route('torque-records.show', $record));
        $this->assertSame(10, $record->readings()->count());
        $this->assertSame(8, $record->readings()->where('period', 'AM')->count());
        $this->assertSame(2, $record->readings()->where('period', 'PM')->count());
        $this->assertDatabaseHas('torque_readings', [
            'torque_record_id' => $record->id,
            'period' => 'AM',
            'reading_no' => 8,
            'torque_value' => '10.80',
        ]);

        // Keep the first value mirrored until the legacy scalar columns are removed in phase two.
        $this->assertSame('10.1', $record->torque_am);
        $this->assertSame('12.25', $record->torque_pm);
    }

    public function test_store_rejects_a_ninth_reading(): void
    {
        $payload = $this->payload([
            'model_series' => 'TRQ-NINE-READINGS',
            'readings' => [
                'am' => collect(range(1, 9))->map(fn ($number) => ['torque_value' => (string) $number])->all(),
                'pm' => [],
            ],
        ]);

        $response = $this->actingAs($this->superAdmin())
            ->from(route('torque-records.create'))
            ->post(route('torque-records.store'), $payload);

        $response->assertRedirect(route('torque-records.create'));
        $response->assertSessionHasErrors('readings.am');
        $this->assertDatabaseMissing('torque_records', ['model_series' => 'TRQ-NINE-READINGS']);
    }

    public function test_store_requires_a_matching_time_and_at_least_one_reading(): void
    {
        $user = $this->superAdmin();

        $missingTime = $this->payload([
            'model_series' => 'TRQ-MISSING-TIME',
            'time_am' => '',
            'readings' => ['am' => [['torque_value' => '10.50']], 'pm' => []],
        ]);

        $this->actingAs($user)
            ->from(route('torque-records.create'))
            ->post(route('torque-records.store'), $missingTime)
            ->assertSessionHasErrors('time_am');

        $noReadings = $this->payload([
            'model_series' => 'TRQ-NO-READINGS',
            'readings' => ['am' => [['torque_value' => '']], 'pm' => []],
        ]);

        $this->actingAs($user)
            ->from(route('torque-records.create'))
            ->post(route('torque-records.store'), $noReadings)
            ->assertSessionHasErrors('readings');
    }

    public function test_update_replaces_readings_and_renumbers_them_transactionally(): void
    {
        $user = $this->superAdmin();
        $createPayload = $this->payload(['model_series' => 'TRQ-UPDATE-READINGS']);

        $this->actingAs($user)->post(route('torque-records.store'), $createPayload);
        $record = TorqueRecord::where('model_series', 'TRQ-UPDATE-READINGS')->firstOrFail();
        $oldReadingIds = $record->readings()->pluck('id')->all();

        $updatePayload = $this->payload([
            'model_series' => 'TRQ-UPDATE-READINGS',
            'time_am' => '',
            'time_pm' => '15:30',
            'readings' => [
                'am' => [],
                'pm' => [
                    ['torque_value' => ''],
                    ['torque_value' => '14.10'],
                    ['torque_value' => '14.20'],
                ],
            ],
        ]);

        $response = $this->actingAs($user)->put(route('torque-records.update', $record), $updatePayload);

        $response->assertRedirect(route('torque-records.show', $record));
        $this->assertDatabaseMissing('torque_readings', ['id' => $oldReadingIds[0]]);
        $this->assertDatabaseHas('torque_readings', [
            'torque_record_id' => $record->id,
            'period' => 'PM',
            'reading_no' => 1,
            'torque_value' => '14.10',
        ]);
        $this->assertDatabaseHas('torque_readings', [
            'torque_record_id' => $record->id,
            'period' => 'PM',
            'reading_no' => 2,
            'torque_value' => '14.20',
        ]);
        $this->assertSame(2, $record->readings()->count());

        $activity = Activity::where('subject_type', TorqueRecord::class)
            ->where('subject_id', $record->id)
            ->where('type', 'update')
            ->latest('id')
            ->firstOrFail();
        $readingChange = collect(ActivityService::formatForDisplay($activity)['changes'])
            ->firstWhere('field', 'torque_readings');

        $this->assertSame('Torque Readings', $readingChange['label']);
        $this->assertStringContainsString('PM 1: 14.10 N·m', $readingChange['after']);
    }

    public function test_legacy_import_values_are_mapped_to_reading_number_one(): void
    {
        $record = TorqueRecord::create([
            'date' => '2026-07-13',
            'model_series' => 'TRQ-IMPORT-MAPPING',
            'status' => 'approved',
        ]);

        $import = new class extends TorqueChecksheetImport
        {
            public function syncReadings(TorqueRecord $record, array $data): void
            {
                $this->syncImportedReadings($record, $data);
            }
        };

        $import->syncReadings($record, ['torque_am' => '9.75', 'torque_pm' => '10.50']);

        $this->assertDatabaseHas('torque_readings', [
            'torque_record_id' => $record->id,
            'period' => 'AM',
            'reading_no' => 1,
            'torque_value' => '9.75',
        ]);
        $this->assertDatabaseHas('torque_readings', [
            'torque_record_id' => $record->id,
            'period' => 'PM',
            'reading_no' => 1,
            'torque_value' => '10.50',
        ]);
    }

    public function test_deleting_parent_cascades_to_readings(): void
    {
        $record = TorqueRecord::create([
            'date' => '2026-07-13',
            'model_series' => 'TRQ-CASCADE',
            'status' => 'approved',
        ]);
        $reading = $record->readings()->create([
            'period' => 'AM',
            'reading_no' => 1,
            'torque_value' => '11.25',
        ]);

        $record->delete();

        $this->assertDatabaseMissing('torque_readings', ['id' => $reading->id]);
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'date' => '2026-07-13',
            'model_series' => 'TRQ-MULTI-'.uniqid(),
            'driver_model' => 'Electric Driver',
            'driver_type' => 'Automatic',
            'line_assigned' => 'Line 1',
            'control_no' => 'CTRL-'.uniqid(),
            'screw_type' => 'M4',
            'process_assigned' => 'Assembly',
            'person_in_charge' => 'Operator One',
            'time_am' => '09:15',
            'time_pm' => '14:30',
            'readings' => [
                'am' => [['torque_value' => '10.25']],
                'pm' => [['torque_value' => '11.50']],
            ],
            'col_remarks' => 'Multiple-reading test',
            'checked_by' => 'Quality One',
        ], $overrides);
    }

    private function superAdmin(): User
    {
        $role = Role::firstOrCreate(
            ['slug' => 'super_admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Test super administrator',
                'is_system' => true,
                'is_active' => true,
            ]
        );

        $user = User::create([
            'name' => 'Torque Test Admin',
            'email' => 'torque-test-'.uniqid().'@example.test',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }
}
