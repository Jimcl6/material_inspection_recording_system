<?php

namespace App\Support;

final class ModelTypeOptions
{
    public const TORQUE = ['Electric', 'Air'];

    public const TEMPERATURE = ['Pot', 'Iron'];

    public static function normalizeTorque(?string $value): ?string
    {
        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            'electric', 'electric driver', 'electic driver' => 'Electric',
            'air', 'air driver', 'pneumatic', 'pneumatic driver' => 'Air',
            default => null,
        };
    }

    public static function temperatureFromEquipmentType(?string $value): ?string
    {
        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            'pot', 'soldering pot' => 'Pot',
            'iron', 'soldering iron' => 'Iron',
            default => null,
        };
    }
}
