<?php

namespace Tests\Unit;

use App\Models\Activity;
use App\Services\ActivityService;
use Tests\TestCase;

class ActivityLogDisplayFormattingTest extends TestCase
{
    public function test_update_snapshot_formats_before_and_after_changes(): void
    {
        $activity = new Activity([
            'type' => 'update',
            'module' => 'temperature',
            'description' => 'Updated temperature record for TMP-001',
            'properties' => ActivityService::updatePropertiesFromSnapshot(
                [
                    'model_series' => 'TMP-001',
                    'temp_am' => '320',
                    'password' => 'old-secret',
                    'updated_at' => '2026-06-25 08:00:00',
                ],
                [
                    'model_series' => 'TMP-002',
                    'temp_am' => '330',
                    'password' => 'new-secret',
                    'updated_at' => '2026-06-25 09:00:00',
                ]
            ),
        ]);

        $display = ActivityService::formatForDisplay($activity);

        $this->assertTrue($display['has_before_after']);
        $this->assertSame(['Model Series', 'AM Temperature', 'Password'], array_column($display['changes'], 'label'));
        $this->assertSame('TMP-001', $display['changes'][0]['before']);
        $this->assertSame('TMP-002', $display['changes'][0]['after']);
        $this->assertSame('[Hidden]', $display['changes'][2]['before']);
        $this->assertSame('[Hidden]', $display['changes'][2]['after']);
    }

    public function test_legacy_update_payload_formats_as_saved_details(): void
    {
        $activity = new Activity([
            'type' => 'update',
            'module' => 'temperature',
            'description' => 'Updated temperature record for TMP-001',
            'properties' => [
                'id' => 84,
                'model_series' => 'TMP-001',
                'temp_am' => '342',
                'updated_at' => '2026-06-25 09:00:00',
            ],
        ]);

        $display = ActivityService::formatForDisplay($activity);

        $this->assertFalse($display['has_before_after']);
        $this->assertSame('Saved Details', $display['recorded_details_title']);
        $this->assertSame(['Model Series', 'AM Temperature'], array_column($display['recorded_details'], 'label'));
    }

    public function test_legacy_old_and_new_data_formats_as_before_and_after(): void
    {
        $activity = new Activity([
            'type' => 'update',
            'module' => 'departments',
            'description' => 'Updated department: Quality Control',
            'properties' => [
                'old_data' => ['name' => 'Quality', 'is_active' => true],
                'new_data' => ['name' => 'Quality Control', 'is_active' => false],
            ],
        ]);

        $display = ActivityService::formatForDisplay($activity);

        $this->assertTrue($display['has_before_after']);
        $this->assertSame(['Name', 'Active'], array_column($display['changes'], 'label'));
        $this->assertSame('Quality', $display['changes'][0]['before']);
        $this->assertSame('Quality Control', $display['changes'][0]['after']);
        $this->assertSame('Yes', $display['changes'][1]['before']);
        $this->assertSame('No', $display['changes'][1]['after']);
    }

    public function test_approval_status_payload_formats_as_before_and_after(): void
    {
        $activity = new Activity([
            'type' => 'approve',
            'module' => 'annealing',
            'description' => 'Approved AnnealingCheck: # 1',
            'properties' => [
                'previous_status' => 'pending',
                'new_status' => 'approved',
                'notes' => 'Approved during audit test',
            ],
        ]);

        $display = ActivityService::formatForDisplay($activity);

        $this->assertTrue($display['has_before_after']);
        $this->assertSame('Status', $display['changes'][0]['label']);
        $this->assertSame('pending', $display['changes'][0]['before']);
        $this->assertSame('approved', $display['changes'][0]['after']);
        $this->assertSame('Notes', $display['details'][0]['label']);
    }

    public function test_login_payload_formats_ip_address_as_detail(): void
    {
        $activity = new Activity([
            'type' => 'login',
            'description' => 'Logged in to the system',
            'properties' => ['ip' => '192.168.2.243'],
        ]);

        $display = ActivityService::formatForDisplay($activity);

        $this->assertSame('IP Address', $display['details'][0]['label']);
        $this->assertSame('192.168.2.243', $display['details'][0]['value']);
        $this->assertSame([], $display['recorded_details']);
    }
}
