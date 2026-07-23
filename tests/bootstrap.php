<?php

declare(strict_types=1);

use Tests\Support\TestDatabaseGuard;

require dirname(__DIR__).'/vendor/autoload.php';

TestDatabaseGuard::assertSafe(TestDatabaseGuard::environment());
