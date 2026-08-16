<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const MATERIAL_FIELDS = [
        ['key' => 'air_valve', 'label' => 'Airvalve', 'type' => 'text'],
        ['key' => 'casing', 'label' => 'Casing', 'type' => 'text'],
        ['key' => 'valve_cover', 'label' => 'Valve Cover', 'type' => 'text'],
    ];

    private const MATERIAL_COLUMNS = [
        'air_valve' => 'M',
        'casing' => 'K',
        'valve_cover' => 'L',
    ];

    private const FIELD_RENAMES = [
        'vcr' => 'air_valve',
        'tank' => 'casing',
        'cd_partition' => 'valve_cover',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('welding_checksheet_types')) {
            return;
        }

        $type = DB::table('welding_checksheet_types')
            ->where('key', 'casing_tank')
            ->first();

        if (! $type) {
            return;
        }

        DB::transaction(function () use ($type): void {
            $this->updateTypeConfiguration((int) $type->id, $type->import_config);

            if (Schema::hasTable('welding_checksheets')) {
                $this->renameExistingChecksheetFields((int) $type->id);
            }
        });
    }

    public function down(): void
    {
        // Existing checksheet data may have been edited after migration.
    }

    private function updateTypeConfiguration(int $typeId, mixed $importConfig): void
    {
        $decodedConfig = $this->decodeJsonObject($importConfig);
        $decodedConfig['material_columns'] = self::MATERIAL_COLUMNS;

        DB::table('welding_checksheet_types')
            ->where('id', $typeId)
            ->update([
                'material_fields' => json_encode(self::MATERIAL_FIELDS, JSON_THROW_ON_ERROR),
                'import_config' => json_encode($decodedConfig, JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);
    }

    private function renameExistingChecksheetFields(int $typeId): void
    {
        DB::table('welding_checksheets')
            ->where('checksheet_type_id', $typeId)
            ->select(['id', 'material_fields'])
            ->chunkById(100, function ($checksheets): void {
                foreach ($checksheets as $checksheet) {
                    $fields = $this->decodeJsonObject($checksheet->material_fields);
                    $renamed = $this->renameMaterialFields($fields);

                    if ($renamed === $fields) {
                        continue;
                    }

                    DB::table('welding_checksheets')
                        ->where('id', $checksheet->id)
                        ->update([
                            'material_fields' => json_encode($renamed, JSON_THROW_ON_ERROR),
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    private function renameMaterialFields(array $fields): array
    {
        foreach (self::FIELD_RENAMES as $oldKey => $newKey) {
            if (! array_key_exists($oldKey, $fields)) {
                continue;
            }

            if (! array_key_exists($newKey, $fields) || $fields[$newKey] === null || $fields[$newKey] === '') {
                $fields[$newKey] = $fields[$oldKey];
            }

            unset($fields[$oldKey]);
        }

        return $fields;
    }

    private function decodeJsonObject(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return [];
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }
};
