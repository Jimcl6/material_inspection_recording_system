<?php

$configuredProxies = array_map(
    static fn (string $proxy): string => trim($proxy),
    explode(',', (string) env('TRUSTED_PROXIES', ''))
);

return [
    // Wildcard trust is intentionally rejected. Configure exact proxy IPs or CIDRs.
    'proxies' => array_values(array_filter(
        $configuredProxies,
        static fn (string $proxy): bool => $proxy !== '' && $proxy !== '*'
    )),
];
