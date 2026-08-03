<?php

namespace Tests\Unit;

use App\Support\ModelTypeOptions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ModelTypeOptionsTest extends TestCase
{
    #[DataProvider('torqueAliases')]
    public function test_torque_aliases_are_normalized(string $source, ?string $expected): void
    {
        $this->assertSame($expected, ModelTypeOptions::normalizeTorque($source));
    }

    public static function torqueAliases(): array
    {
        return [
            ['Electric', 'Electric'],
            ['Electric Driver', 'Electric'],
            ['Electic Driver', 'Electric'],
            ['Air', 'Air'],
            ['Air Driver', 'Air'],
            ['Pneumatic Driver', 'Air'],
            ['Unknown', null],
        ];
    }

    #[DataProvider('temperatureEquipmentTypes')]
    public function test_temperature_equipment_types_are_mapped(string $source, ?string $expected): void
    {
        $this->assertSame($expected, ModelTypeOptions::temperatureFromEquipmentType($source));
    }

    public static function temperatureEquipmentTypes(): array
    {
        return [
            ['Soldering Iron', 'Iron'],
            ['Soldering Pot', 'Pot'],
            ['Unknown', null],
        ];
    }
}
