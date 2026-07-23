<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('welding_checksheet_types', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->json('material_fields')->nullable();
            $table->json('check_items')->nullable();
            $table->json('import_config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('welding_item_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checksheet_type_id')->constrained('welding_checksheet_types')->cascadeOnDelete();
            $table->string('item_code', 50);
            $table->string('item_name', 100)->nullable();
            $table->json('validation_rules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['checksheet_type_id', 'item_code'], 'welding_item_type_code_unique');
            $table->index('item_code');
        });

        Schema::create('welding_checksheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checksheet_type_id')->constrained('welding_checksheet_types')->restrictOnDelete();
            $table->foreignId('item_config_id')->nullable()->constrained('welding_item_configs')->nullOnDelete();
            $table->string('item_name', 100)->nullable();
            $table->string('item_code', 50)->nullable();
            $table->string('month_year', 30)->nullable();
            $table->date('production_date');
            $table->string('machine_no', 20)->nullable();
            $table->string('letter_code', 10)->nullable();
            $table->integer('prod_qty')->nullable();
            $table->string('job_number', 50)->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('temperature', 6, 2)->nullable();
            $table->json('material_fields')->nullable();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checked_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('operator_name_raw', 255)->nullable();
            $table->string('technician_name_raw', 255)->nullable();
            $table->string('checked_by_name_raw', 255)->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source_file', 255)->nullable();
            $table->string('source_sheet', 100)->nullable();
            $table->unsignedInteger('source_row')->nullable();
            $table->unsignedBigInteger('legacy_diaphragm_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('item_code');
            $table->index('production_date');
            $table->index('machine_no');
            $table->index('status');
            $table->index('legacy_diaphragm_id');
        });

        Schema::create('welding_checksheet_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checksheet_id')->constrained('welding_checksheets')->cascadeOnDelete();
            $table->string('check_item_key', 50);
            $table->string('check_item_label', 100);
            $table->string('requirement_text', 150)->nullable();
            $table->json('sample_values')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['checksheet_id', 'check_item_key'], 'welding_samples_sheet_item_index');
        });

        $this->seedTypes();
        $this->seedPermissions();
        $this->backfillDiaphragmData();
    }

    public function down(): void
    {
        Schema::dropIfExists('welding_checksheet_samples');
        Schema::dropIfExists('welding_checksheets');
        Schema::dropIfExists('welding_item_configs');
        Schema::dropIfExists('welding_checksheet_types');
    }

    private function seedTypes(): void
    {
        $now = now();

        DB::table('welding_checksheet_types')->insert([
            [
                'key' => 'diaphragm',
                'name' => 'Diaphragm Welding Checksheet',
                'description' => 'Legacy diaphragm welding checksheet format.',
                'material_fields' => json_encode([
                    ['key' => 'lasermark_lot_number', 'label' => 'Lasermark Lot Number', 'type' => 'text'],
                    ['key' => 'df_rubber_lot', 'label' => 'DF Rubber Lot', 'type' => 'text'],
                    ['key' => 'center_plate_a_lot', 'label' => 'Center Plate A Lot', 'type' => 'text'],
                    ['key' => 'center_plate_b_lot', 'label' => 'Center Plate B Lot', 'type' => 'text'],
                ]),
                'check_items' => json_encode($this->diaphragmCheckItems()),
                'import_config' => json_encode([
                    'data_start_row' => 10,
                    'record_span' => 10,
                    'sample_columns' => ['V', 'W', 'X', 'Y', 'Z'],
                    'format' => 'legacy_diaphragm',
                ]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'casing_tank',
                'name' => 'Casing-Tank Welding Checksheet',
                'description' => 'Casing-Tank welding checksheet format from reference Excel files.',
                'material_fields' => json_encode([
                    ['key' => 'tank', 'label' => 'Tank', 'type' => 'text'],
                    ['key' => 'cd_partition', 'label' => 'CD Partition', 'type' => 'text'],
                    ['key' => 'vcr', 'label' => 'VCR', 'type' => 'text'],
                ]),
                'check_items' => json_encode($this->casingTankCheckItems()),
                'import_config' => json_encode([
                    'data_start_row' => 10,
                    'record_span' => 5,
                    'material_columns' => ['tank' => 'K', 'cd_partition' => 'L', 'vcr' => 'M'],
                    'sample_columns' => ['S', 'T', 'U', 'V', 'W'],
                    'format' => 'casing_tank',
                ]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $diaphragmTypeId = DB::table('welding_checksheet_types')->where('key', 'diaphragm')->value('id');
        $casingTankTypeId = DB::table('welding_checksheet_types')->where('key', 'casing_tank')->value('id');

        if ($casingTankTypeId) {
            foreach (['CSB29046P3', 'CSB29052P1', 'TKB4000100'] as $itemCode) {
                DB::table('welding_item_configs')->insert([
                    'checksheet_type_id' => $casingTankTypeId,
                    'item_code' => $itemCode,
                    'validation_rules' => json_encode([
                        'collapse_depth_min' => $itemCode === 'TKB4000100' ? null : 0.37,
                        'collapse_time_min' => $itemCode === 'TKB4000100' ? null : 1.3,
                        'collapse_time_max' => $itemCode === 'TKB4000100' ? null : 1.7,
                    ]),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if ($diaphragmTypeId && Schema::hasTable('diaphragm_item_codes')) {
            DB::table('diaphragm_item_codes')->orderBy('item_code')->chunkById(100, function ($rows) use ($diaphragmTypeId, $now) {
                foreach ($rows as $row) {
                    DB::table('welding_item_configs')->insert([
                        'checksheet_type_id' => $diaphragmTypeId,
                        'item_code' => $row->item_code,
                        'item_name' => $row->item_name,
                        'validation_rules' => json_encode([
                            'strength_min' => $row->strength_min,
                            'measurement_1_type' => $row->measurement_1_type,
                            'measurement_1_min' => $row->measurement_1_min,
                            'measurement_1_max' => $row->measurement_1_max,
                            'circumference_diff_type' => $row->circumference_diff_type,
                            'circumference_diff_max' => $row->circumference_diff_max,
                        ]),
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });
        }
    }

    private function seedPermissions(): void
    {
        if (! Schema::hasTable('user_permissions')) {
            return;
        }

        $now = now();
        $actions = ['view', 'create', 'update', 'delete', 'approve', 'reject', 'import', 'export'];
        $permissionMap = [];

        foreach ($actions as $action) {
            $existing = DB::table('user_permissions')
                ->where('module', 'welding')
                ->where('action', $action)
                ->first();

            if ($existing) {
                $permissionMap[$action] = $existing->id;

                continue;
            }

            $permissionMap[$action] = DB::table('user_permissions')->insertGetId([
                'name' => ucfirst($action).' Welding',
                'slug' => "welding.{$action}",
                'description' => "Permission to {$action} welding checksheet module",
                'module' => 'welding',
                'action' => $action,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $oldPermissions = DB::table('user_permissions')
            ->where('module', 'diaphragm')
            ->whereIn('action', $actions)
            ->get()
            ->keyBy('action');

        if (Schema::hasTable('role_permissions')) {
            foreach ($oldPermissions as $action => $oldPermission) {
                $newPermissionId = $permissionMap[$action] ?? null;
                if (! $newPermissionId) {
                    continue;
                }

                $roleIds = DB::table('role_permissions')
                    ->where('permission_id', $oldPermission->id)
                    ->pluck('role_id');

                foreach ($roleIds as $roleId) {
                    DB::table('role_permissions')->updateOrInsert(
                        ['role_id' => $roleId, 'permission_id' => $newPermissionId],
                        ['created_at' => $now, 'updated_at' => $now]
                    );
                }
            }
        }

        if (Schema::hasTable('position_permissions')) {
            foreach ($oldPermissions as $action => $oldPermission) {
                $newPermissionId = $permissionMap[$action] ?? null;
                if (! $newPermissionId) {
                    continue;
                }

                $positionIds = DB::table('position_permissions')
                    ->where('permission_id', $oldPermission->id)
                    ->pluck('position_id');

                foreach ($positionIds as $positionId) {
                    DB::table('position_permissions')->updateOrInsert(
                        ['position_id' => $positionId, 'permission_id' => $newPermissionId],
                        ['created_at' => $now, 'updated_at' => $now]
                    );
                }
            }
        }
    }

    private function backfillDiaphragmData(): void
    {
        if (! Schema::hasTable('diaphragm_welding_checksheets') || ! Schema::hasTable('diaphragm_welding_samples')) {
            return;
        }

        $typeId = DB::table('welding_checksheet_types')->where('key', 'diaphragm')->value('id');
        if (! $typeId) {
            return;
        }

        DB::table('diaphragm_welding_checksheets')->orderBy('id')->chunkById(100, function ($rows) use ($typeId) {
            foreach ($rows as $row) {
                $exists = DB::table('welding_checksheets')
                    ->where('legacy_diaphragm_id', $row->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $itemConfigId = DB::table('welding_item_configs')
                    ->where('checksheet_type_id', $typeId)
                    ->where('item_code', $row->item_code)
                    ->value('id');

                $checksheetId = DB::table('welding_checksheets')->insertGetId([
                    'checksheet_type_id' => $typeId,
                    'item_config_id' => $itemConfigId,
                    'item_name' => $row->item_name,
                    'item_code' => $row->item_code,
                    'month_year' => $row->month_year,
                    'production_date' => $row->production_date,
                    'machine_no' => $row->machine_no,
                    'letter_code' => $row->letter_code,
                    'prod_qty' => $row->prod_qty,
                    'job_number' => $row->jo_number,
                    'temperature' => $row->temperature,
                    'material_fields' => json_encode([
                        'lasermark_lot_number' => $row->lasermark_lot_number,
                        'df_rubber_lot' => $row->df_rubber_lot,
                        'center_plate_a_lot' => $row->center_plate_a_lot,
                        'center_plate_b_lot' => $row->center_plate_b_lot,
                    ]),
                    'operator_id' => $row->operator_id,
                    'technician_id' => $row->technician_id,
                    'checked_by_id' => $row->checked_by_id,
                    'remarks' => $row->remarks,
                    'status' => $row->status,
                    'submitted_at' => $row->submitted_at,
                    'approved_at' => $row->approved_at,
                    'approval_notes' => $row->approval_notes,
                    'created_by' => $row->created_by,
                    'updated_by' => $row->updated_by,
                    'legacy_diaphragm_id' => $row->id,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                    'deleted_at' => $row->deleted_at,
                ]);

                $samples = DB::table('diaphragm_welding_samples')
                    ->where('checksheet_id', $row->id)
                    ->get();

                foreach ($samples as $sample) {
                    $meta = $this->findCheckItem($sample->check_item, $this->diaphragmCheckItems());
                    DB::table('welding_checksheet_samples')->insert([
                        'checksheet_id' => $checksheetId,
                        'check_item_key' => $sample->check_item,
                        'check_item_label' => $meta['label'] ?? $sample->check_item,
                        'requirement_text' => $meta['requirement_text'] ?? null,
                        'sample_values' => json_encode([
                            $sample->sample_1,
                            $sample->sample_2,
                            $sample->sample_3,
                            $sample->sample_4,
                            $sample->sample_5,
                        ]),
                        'sort_order' => $meta['sort_order'] ?? 0,
                        'created_at' => $sample->created_at,
                        'updated_at' => $sample->updated_at,
                    ]);
                }
            }
        });
    }

    private function diaphragmCheckItems(): array
    {
        return [
            ['key' => 'collapse_depth', 'label' => 'COLLAPSE - DEPTH (mm)', 'requirement_text' => null, 'sort_order' => 1],
            ['key' => 'collapse_time', 'label' => 'COLLAPSE - TIME (sec.)', 'requirement_text' => null, 'sort_order' => 2],
            ['key' => 'strength', 'label' => 'STRENGTH (kN)', 'requirement_text' => 'Minimum configured per item code', 'sort_order' => 3],
            ['key' => 'appearance', 'label' => 'APPEARANCE', 'requirement_text' => 'P or /', 'sort_order' => 4],
            ['key' => 'welding_condition', 'label' => 'WELDING CONDITION', 'requirement_text' => null, 'sort_order' => 5],
            ['key' => 'measurement_1', 'label' => 'MEASUREMENT 1 - CENTER', 'requirement_text' => 'Configured per item code', 'sort_order' => 6],
            ['key' => 'measurement_2', 'label' => 'MEASUREMENT 2 - UP', 'requirement_text' => 'Configured per item code', 'sort_order' => 7],
            ['key' => 'measurement_3', 'label' => 'MEASUREMENT 3 - LOW', 'requirement_text' => 'Configured per item code', 'sort_order' => 8],
            ['key' => 'measurement_4', 'label' => 'MEASUREMENT 4 - LEFT', 'requirement_text' => 'Configured per item code', 'sort_order' => 9],
            ['key' => 'measurement_5', 'label' => 'MEASUREMENT 5 - RIGHT', 'requirement_text' => 'Configured per item code', 'sort_order' => 10],
        ];
    }

    private function casingTankCheckItems(): array
    {
        return [
            ['key' => 'collapse_depth', 'label' => 'COLLAPSE - DEPTH (mm)', 'requirement_text' => '>= 0.37 mm when configured', 'sort_order' => 1],
            ['key' => 'collapse_time', 'label' => 'COLLAPSE - TIME (sec.)', 'requirement_text' => '1.3 - 1.7 sec when configured', 'sort_order' => 2],
            ['key' => 'airleak', 'label' => 'AIRLEAK', 'requirement_text' => null, 'sort_order' => 3],
            ['key' => 'appearance', 'label' => 'APPEARANCE', 'requirement_text' => 'P or /', 'sort_order' => 4],
            ['key' => 'welding_condition', 'label' => 'WELDING CONDITION', 'requirement_text' => null, 'sort_order' => 5],
        ];
    }

    private function findCheckItem(string $key, array $items): ?array
    {
        foreach ($items as $item) {
            if ($item['key'] === $key) {
                return $item;
            }
        }

        return null;
    }
};
