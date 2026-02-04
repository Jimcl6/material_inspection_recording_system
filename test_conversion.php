<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test the conversion function directly
$controller = new App\Http\Controllers\AnnealingCheckController();

// Use reflection to access private method
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('convertNameToId');
$method->setAccessible(true);

$testNames = [
    'Admin User',      // Exact match
    'admin user',      // Lowercase
    ' Admin User ',    // With spaces
    'Admin User ',     // With trailing space
    'Admin User',      // Different case
    'John Doe',        // Non-existent user
    '',                // Empty
    '1',               // Numeric ID
];

echo "Testing name to ID conversion:\n";
echo "==============================\n\n";

foreach ($testNames as $name) {
    $result = $method->invoke($controller, $name);
    echo "Input: '$name' => Result: " . ($result ?? 'NULL') . "\n";
}

echo "\n";
