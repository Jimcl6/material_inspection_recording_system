<?php

namespace App\Support;

use Illuminate\Database\MySqlConnection;

final class LegacySchemaManager
{
    public function __construct(
        private readonly MySqlConnection $connection,
    ) {}

    /**
     * Preserve the index-inspection contract used by an existing migration
     * without retaining Doctrine DBAL as an application dependency.
     *
     * @return array<string, array<string, mixed>>
     */
    public function listTableIndexes(string $table): array
    {
        return collect($this->connection->getSchemaBuilder()->getIndexes($table))
            ->mapWithKeys(fn (array $index): array => [$index['name'] => $index])
            ->all();
    }
}
