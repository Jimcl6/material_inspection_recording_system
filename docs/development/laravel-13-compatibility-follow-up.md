# Laravel 13 compatibility follow-up

Laravel 13 is outside Phase 6. MIRS must remain on the verified Laravel 12 release until a separate, reviewed upgrade is authorized.

Use the official [Laravel 13 upgrade guide](https://laravel.com/docs/13.x/upgrade) as the authoritative checklist when that work is approved.

## Current dependency blockers

`composer prohibits laravel/framework ^13.0 --locked` identifies these current lock or root constraints:

- The application intentionally requires `laravel/framework ^12.0`.
- `inertiajs/inertia-laravel` 1.3 supports Laravel through 12 only. Upgrade the Laravel adapter and the Vue adapter together, then regression-test every Inertia response and form.
- `laravel/tinker` 2.11 supports Laravel through 12 only. Laravel 13 requires Tinker 3.
- The current locked `spatie/laravel-ignition` 2.9 supports Laravel through 12. A compatible 2.12 release exists, but it should be updated and tested with the Laravel 13 dependency set.
- The application intentionally requires PHPUnit 11. Laravel 13's upgrade guide requires PHPUnit 12.

Laravel Excel 3.1.69 now declares Laravel 13 compatibility, so it is not a current framework-version blocker. It must still pass the complete import/export security and regression suite during a future upgrade.

## Code and configuration review

Before changing the framework constraint:

1. Update Inertia server and Vue packages together and verify page props, validation redirects, history, file uploads, and asset loading.
2. Update Tinker to 3 and PHPUnit to 12.
3. Update Ignition within its supported Laravel 13 range.
4. Replace direct `VerifyCsrfToken` / `ValidateCsrfToken` references with Laravel 13's `PreventRequestForgery` middleware and verify Sanctum stateful requests.
5. Explicitly configure cache, Redis, and session prefixes if preserving the underscore-based Laravel 12 defaults matters.
6. Review the new cache `serializable_classes` hardening option before enabling object cache payloads.
7. Search for MySQL `upsert` calls with an empty `uniqueBy` value and joined `DELETE` queries using ordering or limits.
8. Re-run queue, notification, domain-route, pagination, and model-serialization regressions called out by the Laravel 13 guide.
9. Repeat both the fresh-MySQL and restored-sanitized-clone verification gates.

Do not combine this work with Phase 7 architectural extraction.
