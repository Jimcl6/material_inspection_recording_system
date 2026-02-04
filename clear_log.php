<?php

// Clear the Laravel log file
$logFile = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    file_put_contents($logFile, '');
    echo "Laravel log cleared.\n";
} else {
    echo "Log file not found.\n";
}

echo "\nNow try updating the Annealing Check with 'Admin User' in the user fields.\n";
echo "Then check the log again with: tail -f storage/logs/laravel.log\n";
