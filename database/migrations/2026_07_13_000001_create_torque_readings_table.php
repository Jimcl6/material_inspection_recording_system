<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->assertLegacyReadingsAreNumeric();

        Schema::create('torque_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('torque_record_id')
                ->constrained('torque_records')
                ->cascadeOnDelete();
            $table->enum('period', ['AM', 'PM']);
            $table->unsignedTinyInteger('reading_no');
            $table->decimal('torque_value', 10, 2);
            $table->timestamps();

            $table->unique(
                ['torque_record_id', 'period', 'reading_no'],
                'torque_readings_record_period_number_unique'
            );
            $table->index(['torque_record_id', 'period']);
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE torque_readings ADD CONSTRAINT torque_readings_number_check CHECK (reading_no BETWEEN 1 AND 8)'
            );
        }

        DB::table('torque_records')
            ->select(['id', 'torque_am', 'torque_pm'])
            ->orderBy('id')
            ->chunkById(200, function ($records) {
                $rows = [];
                $timestamp = now();

                foreach ($records as $record) {
                    foreach (['AM' => $record->torque_am, 'PM' => $record->torque_pm] as $period => $value) {
                        if ($value === null || trim((string) $value) === '') {
                            continue;
                        }

                        if (! is_numeric($value)) {
                            throw new RuntimeException(
                                "Cannot migrate non-numeric {$period} torque value for torque record #{$record->id}."
                            );
                        }

                        $rows[] = [
                            'torque_record_id' => $record->id,
                            'period' => $period,
                            'reading_no' => 1,
                            'torque_value' => $value,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                    }
                }

                if ($rows !== []) {
                    DB::table('torque_readings')->insert($rows);
                }
            }, 'id');
    }

    public function down(): void
    {
        Schema::dropIfExists('torque_readings');
    }

    private function assertLegacyReadingsAreNumeric(): void
    {
        DB::table('torque_records')
            ->select(['id', 'torque_am', 'torque_pm'])
            ->orderBy('id')
            ->chunkById(200, function ($records) {
                foreach ($records as $record) {
                    foreach (['AM' => $record->torque_am, 'PM' => $record->torque_pm] as $period => $value) {
                        if ($value !== null && trim((string) $value) !== '' && ! is_numeric($value)) {
                            throw new RuntimeException(
                                "Cannot migrate non-numeric {$period} torque value for torque record #{$record->id}."
                            );
                        }
                    }
                }
            }, 'id');
    }
};
