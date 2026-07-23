<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Tests\Support\TestDatabaseGuard;

$root = dirname(__DIR__, 2);

require $root.'/vendor/autoload.php';

if (is_file($root.'/.env.testing')) {
    Dotenv\Dotenv::createImmutable($root, '.env.testing')->safeLoad();
}

TestDatabaseGuard::assertSafe(TestDatabaseGuard::environment());

$app = require $root.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$input = new ArrayInput([
    'command' => 'migrate:fresh',
    '--force' => true,
    '--no-interaction' => true,
]);
$output = new ConsoleOutput;
$status = $kernel->handle($input, $output);
$kernel->terminate($input, $status);

exit($status);
