<?php

declare(strict_types=1);

$allowedAdvisories = [
    'PKSA-3r5d-mb8f-1qw9',
    'PKSA-8qx3-n5y5-vvnd',
    'PKSA-b14r-zh1d-vdrc',
    'PKSA-ft77-7h5f-p3r6',
    'PKSA-m5cs-t1y6-qpcs',
    'PKSA-mdq4-51ck-6kdq',
    'PKSA-v5yj-8nmz-sk2q',
];

$path = $argv[1] ?? '';

if ($path === '' || ! is_file($path)) {
    fwrite(STDERR, "Composer audit report is missing.\n");
    exit(2);
}

$contents = (string) file_get_contents($path);
$contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents) ?? $contents;
$report = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
$unexpected = [];
$informational = [];

foreach ($report['advisories'] ?? [] as $package => $advisories) {
    foreach ($advisories as $advisory) {
        $id = (string) ($advisory['advisoryId'] ?? 'unknown');
        $summary = sprintf('%s %s', $package, $id);

        if (in_array($id, $allowedAdvisories, true)) {
            $informational[] = $summary;
        } else {
            $unexpected[] = $summary;
        }
    }
}

foreach ($informational as $summary) {
    fwrite(STDOUT, "Phase 6 informational advisory: {$summary}\n");
}

if ($unexpected !== []) {
    foreach ($unexpected as $summary) {
        fwrite(STDERR, "Blocking unexpected Composer advisory: {$summary}\n");
    }

    exit(1);
}

fwrite(STDOUT, "Composer audit gate passed; no unexpected advisories were found.\n");
