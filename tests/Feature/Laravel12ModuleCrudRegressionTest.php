<?php

namespace Tests\Feature;

use App\Models\AnnealingCheck;
use App\Models\MagnetismChecksheet;
use App\Models\MaterialPart;
use App\Models\TempRecord;
use App\Models\TorqueRecord;
use App\Models\WeldingChecksheet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Laravel12ModuleCrudRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_operational_module_supports_a_create_update_delete_cycle(): void
    {
        $records = [
            [AnnealingCheck::factory()->create(), 'remarks', 'Laravel 12 annealing update'],
            [TempRecord::factory()->create(), 'col_remarks', 'Laravel 12 temperature update'],
            [TorqueRecord::factory()->create(), 'col_remarks', 'Laravel 12 torque update'],
            [MagnetismChecksheet::factory()->create(), 'item_name', 'Laravel 12 magnetism update'],
            [MaterialPart::factory()->create(), 'operator', 'Laravel 12 material update'],
            [WeldingChecksheet::factory()->create(), 'remarks', 'Laravel 12 welding update'],
        ];

        foreach ($records as [$record, $attribute, $value]) {
            $this->assertTrue($record->exists);
            $this->assertTrue($record->update([$attribute => $value]));
            $this->assertSame($value, $record->fresh()->getAttribute($attribute));

            $this->deletePermanently($record);

            $this->assertDatabaseMissing($record->getTable(), [
                $record->getKeyName() => $record->getKey(),
            ]);
        }
    }

    private function deletePermanently(Model $record): void
    {
        if (method_exists($record, 'forceDelete')) {
            $record->forceDelete();

            return;
        }

        $record->delete();
    }
}
