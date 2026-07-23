<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Support\TestDatabaseGuard;

class TestDatabaseGuardTest extends TestCase
{
    /**
     * @dataProvider unsafeConfigurations
     *
     * @param  array<string, string>  $overrides
     */
    public function test_it_rejects_unsafe_database_configurations(array $overrides, string $expectedMessage): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($expectedMessage);

        TestDatabaseGuard::assertSafe(array_merge($this->safeConfiguration(), $overrides));
    }

    public function test_it_accepts_an_explicit_disposable_mysql_database(): void
    {
        TestDatabaseGuard::assertSafe($this->safeConfiguration());

        $this->addToAssertionCount(1);
    }

    /**
     * @return array<string, array{array<string, string>, string}>
     */
    public function unsafeConfigurations(): array
    {
        return [
            'non-testing environment' => [['APP_ENV' => 'production'], 'APP_ENV must be testing'],
            'database URL override' => [['DATABASE_URL' => 'mysql://example.invalid/not-used'], 'DATABASE_URL must be empty'],
            'non-MySQL driver' => [['DB_CONNECTION' => 'sqlite'], 'DB_CONNECTION must be mysql'],
            'missing suffix' => [['DB_DATABASE' => 'mirs'], 'must end in _test or _testing'],
            'known production name' => [
                [
                    'DB_DATABASE' => 'archive_testing',
                    'TEST_PROHIBITED_DATABASES' => 'mirs,archive_testing',
                ],
                'matches a prohibited database name',
            ],
            'unapproved host' => [['DB_HOST' => 'production-db.internal'], 'not an approved disposable MySQL host'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function safeConfiguration(): array
    {
        return [
            'APP_ENV' => 'testing',
            'DATABASE_URL' => '',
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => '127.0.0.1',
            'DB_DATABASE' => 'mirs_testing',
            'TEST_ALLOWED_DATABASE_HOSTS' => '127.0.0.1,localhost,mysql',
            'TEST_PROHIBITED_DATABASES' => 'forge,laravel,mirs,production',
        ];
    }
}
