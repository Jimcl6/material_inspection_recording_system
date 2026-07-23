<?php

declare(strict_types=1);

namespace Tests\Support;

use RuntimeException;

final class TestDatabaseGuard
{
    /**
     * Read only the environment values that can influence the test connection.
     *
     * @return array<string, string>
     */
    public static function environment(): array
    {
        $names = [
            'APP_ENV',
            'DATABASE_URL',
            'DB_CONNECTION',
            'DB_HOST',
            'DB_DATABASE',
            'TEST_ALLOWED_DATABASE_HOSTS',
            'TEST_PROHIBITED_DATABASES',
        ];

        $environment = [];

        foreach ($names as $name) {
            $value = $_SERVER[$name] ?? $_ENV[$name] ?? getenv($name);
            $environment[$name] = is_string($value) ? trim($value) : '';
        }

        return $environment;
    }

    /**
     * Fail before Laravel boots whenever the configured database is not disposable.
     *
     * @param  array<string, string>  $environment
     */
    public static function assertSafe(array $environment): void
    {
        $appEnvironment = strtolower(trim($environment['APP_ENV'] ?? ''));
        $connection = strtolower(trim($environment['DB_CONNECTION'] ?? ''));
        $database = trim($environment['DB_DATABASE'] ?? '');
        $host = strtolower(trim($environment['DB_HOST'] ?? ''));
        $databaseUrl = trim($environment['DATABASE_URL'] ?? '');

        self::ensure($appEnvironment === 'testing', 'APP_ENV must be testing.');
        self::ensure($databaseUrl === '', 'DATABASE_URL must be empty so it cannot override the guarded test connection.');
        self::ensure($connection === 'mysql', 'DB_CONNECTION must be mysql.');
        self::ensure($database !== '', 'DB_DATABASE must be set.');
        self::ensure(
            preg_match('/(?:_test|_testing)$/i', $database) === 1,
            'DB_DATABASE must end in _test or _testing.'
        );

        $prohibitedDatabases = self::csv($environment['TEST_PROHIBITED_DATABASES'] ?? '');
        self::ensure(
            ! in_array(strtolower($database), $prohibitedDatabases, true),
            'DB_DATABASE matches a prohibited database name.'
        );

        $allowedHosts = self::csv($environment['TEST_ALLOWED_DATABASE_HOSTS'] ?? '');
        self::ensure($host !== '', 'DB_HOST must be set.');
        self::ensure($allowedHosts !== [], 'TEST_ALLOWED_DATABASE_HOSTS must list explicit disposable MySQL hosts.');
        self::ensure(
            in_array($host, $allowedHosts, true),
            'DB_HOST is not an approved disposable MySQL host.'
        );
    }

    /**
     * @return list<string>
     */
    private static function csv(string $value): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn (string $item): string => strtolower(trim($item)),
            explode(',', $value)
        ))));
    }

    private static function ensure(bool $condition, string $message): void
    {
        if (! $condition) {
            throw new RuntimeException('Unsafe PHPUnit database configuration: '.$message);
        }
    }
}
