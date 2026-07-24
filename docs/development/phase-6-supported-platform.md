# Phase 6 supported platform

Phase 6 standardizes MIRS on PHP 8.4, Node.js 24 LTS with npm 11.16, Laravel 12, and MySQL. It intentionally preserves the Laravel 10 application structure and does not begin Phase 7 refactoring.

## Runtime and dependency checkpoints

| Checkpoint | Framework | Coupled dependency changes | Verification |
| --- | --- | --- | --- |
| Baseline | Laravel 9.52.21 | PHP 8.4 and Node 24 runtime declarations; PHP 8.4 Docker image; explicit nullable signatures | Complete CI and production Docker build passed before the first framework upgrade |
| Laravel 10 | Laravel 10.50.2 | Collision 7.12, PHPUnit 10.5, Ignition 2.9, Breeze 1.29, and PHPUnit 10 data providers | Fresh MySQL migrations, 138 tests, Larastan, Pint, frontend checks, and Docker build passed |
| Laravel 11 | Laravel 11.55.0 | Sanctum 4.3, Breeze 2.4, Collision 8.5, Carbon 3.13; Doctrine DBAL removed | Complete checkpoint passed after adding a narrow legacy migration index adapter |
| Laravel 12 | Laravel 12.64.0 | PHPUnit 11.5, Collision 8.9, Larastan 3.10 / PHPStan 2.2, Vite 8.1, Laravel Vite Plugin 3.1 | Complete CI, audits, production assets, and Docker build are release gates |

Laravel Excel remains at 3.1.69 and PhpSpreadsheet at 1.30.6. Guzzle remains at 7.15.1 and PSR-7 at 2.13.0. These versions are compatible with the Laravel 12 lock and have no active Composer advisories.

## Compatibility changes

- Sanctum 4 uses the framework cookie, CSRF validation, and session-authentication middleware classes.
- The current Laravel 10-style application structure, HTTP kernel, exception handler, service providers, and scheduler remain supported and were not rewritten to resemble a new skeleton.
- A small `LegacySchemaManager` adapter maps one historical migration's removed Doctrine index-inspection call to Laravel's native schema builder. The historical migration itself remains unchanged.
- Carbon 3 may return fractional minute differences; the login-history accessor preserves its documented integer-minute result.
- PHPUnit data providers use PHPUnit 10+ attributes.
- The authentication redirect middleware now has Laravel 12's explicit nullable return contract.
- Pint excludes historical migrations so formatting cannot rewrite already-applied schema history.
- Larastan 3's exact 109 pre-existing findings are captured in a reviewed baseline. New or changed static-analysis findings remain blocking.

## Fresh-install verification

CI creates an empty `mirs_testing` MySQL 8.4 database, proves the unsafe-database guard rejects a prohibited name, runs every migration, executes `MirsTestingSeeder`, registers routes, and runs unit and feature tests. Production seeders are never invoked.

The Laravel 12 regression suite includes create, update, and delete cycles for Annealing, Temperature, Torque, Magnetism, Material Monitoring, and Welding. Existing tests cover authentication and account status, permissions, dashboard reporting, Annealing filters, approvals, activities, Sanctum APIs, Excel imports and exports, QR regeneration, badge rendering, and QR scanning.

## Existing-clone verification

The encrypted post-restore recovery archive was restored to an isolated temporary MySQL instance. Before application use, personal identifiers, credentials, tokens, sessions, QR payloads, audit context, and business text were replaced or removed. No live database was used.

Verification results:

- 36 restored tables, four sanitized users, two sanitized badge rows, and 12 Torque records were present before the upgrade.
- One pending canonical migration was applied, bringing the migration ledger to 52 entries.
- Route caching and route registration succeeded with 174 routes.
- QR regeneration dry-run left stored badge rows byte-for-byte unchanged.
- Transactional smoke checks passed for active login, suspended-login rejection, permissions, dashboard response shape, local encrypted QR generation and scanning, activity logging, Sanctum token creation, and Excel export.
- The transaction rolled back completely: user, Torque, and badge counts were unchanged and no synthetic smoke user remained.

The temporary plaintext recovery extract was deleted immediately after the sanitized clone was created. The encrypted recovery archive was not modified.

## Release checks

```powershell
composer validate --strict
composer audit --locked --abandoned=fail
composer ci:backend
npm ci
npm audit --audit-level=high
npm run check
docker build --target app --tag mirs-ci:local .
```

Use PHP 8.4 and Node 24 for these commands. Never point them at `fc_1_data_db_testing` or another live database.
