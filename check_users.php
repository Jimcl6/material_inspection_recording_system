<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Available users in database:\n";
echo "============================\n";

$users = App\Models\User::select('id', 'name')->get();

foreach ($users as $user) {
    echo "ID: {$user->id} - Name: '{$user->name}'\n";
}

echo "\nTotal users: " . $users->count() . "\n";
