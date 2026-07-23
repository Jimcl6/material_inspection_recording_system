<?php

namespace App\Support;

final class CorsConfiguration
{
    /**
     * Parse a comma-separated list into unique HTTP(S) origins.
     * Wildcards, paths, credentials, queries, and fragments are rejected.
     *
     * @return array<int, string>
     */
    public static function allowedOrigins(?string $configured): array
    {
        $origins = array_map(
            static fn (string $origin): string => rtrim(trim($origin), '/'),
            explode(',', (string) $configured)
        );

        $origins = array_filter($origins, static function (string $origin): bool {
            return $origin !== '*'
                && preg_match('#^https?://(?:\[[0-9a-f:]+\]|[a-z0-9.-]+)(?::\d{1,5})?$#i', $origin) === 1;
        });

        return array_values(array_unique($origins));
    }

    public static function supportsCredentials(array $allowedOrigins): bool
    {
        return $allowedOrigins !== [] && ! in_array('*', $allowedOrigins, true);
    }
}
