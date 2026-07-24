# Phase 4 test and CI guide

This guide reproduces the Phase 4 GitHub Actions checks without connecting to production. All destructive database commands pass through `Tests\Support\TestDatabaseGuard` before Laravel boots.

## Supported tools

- PHP 8.4 is the required local, CI, and production runtime.
- Composer 2.
- Node.js 24 LTS and npm 11.
- MySQL 8.4 matches CI. A disposable local MySQL 8.x instance is acceptable.
- Docker with BuildKit for the production image check.

Do not run this workflow with the XAMPP PHP 8.0 executable. Do not reuse the production database, database account, host, or application key.

## One-time local setup

Create a dedicated MySQL database and account using a local administrative connection. Substitute a local-only password and do not commit it:

```sql
CREATE DATABASE mirs_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mirs_testing'@'localhost' IDENTIFIED BY '<local-only-password>';
GRANT ALL PRIVILEGES ON mirs_testing.* TO 'mirs_testing'@'localhost';
FLUSH PRIVILEGES;
```

Copy `.env.testing.example` to the ignored `.env.testing`, set only the disposable account values, and generate a test-only application key:

```powershell
Copy-Item .env.testing.example .env.testing
php artisan key:generate --env=testing
composer install
npm ci
```

The guard requires `APP_ENV=testing`, MySQL, a database ending in `_test` or `_testing`, an empty `DATABASE_URL`, an explicitly allowed host, and a database name that is not prohibited. The historical live database name `fc_1_data_db_testing` is explicitly prohibited despite its testing-style suffix. The guard throws before migrations or refresh operations when any condition fails.

## Backend checks

Reset the disposable database and run the same formatting, analysis, unit, and feature gates used by CI:

```powershell
composer test:reset
composer ci:backend
```

`composer test:reset` is the only documented reset command. It invokes the guard before `migrate:fresh`. Tests may call `MirsTestingSeeder`; production seeders must never be called from automated tests.

Run the Composer metadata and exact-advisory gate separately:

```powershell
composer validate --strict
$report = Join-Path $env:TEMP 'mirs-composer-audit.json'
composer audit --locked --format=json | Set-Content -Encoding utf8 $report
php scripts/ci/check-composer-audit.php $report
Remove-Item -LiteralPath $report
```

Composer can return a non-zero status for reviewed informational advisories, so the JSON gate determines whether the advisory set is allowed. Any unreviewed advisory is blocking.

## Frontend checks

```powershell
npm ci
npm audit --omit=dev --audit-level=high
$report = Join-Path $env:TEMP 'mirs-npm-audit.json'
npm audit --json | Set-Content -Encoding utf8 $report
node scripts/ci/check-npm-audit.cjs $report
Remove-Item -LiteralPath $report
npm run check
```

Production dependency advisories are blocking. The full npm audit permits only the exact reviewed Vite 4 toolchain advisory sources already scheduled for a later tooling upgrade; any new source is blocking.

## Production Docker build

```powershell
docker build --target app --tag mirs-ci:local .
```

The Docker build uses Node 24 for assets and PHP 8.4 for the production application image.

## Reset and failure checks

To reset the disposable schema, run `composer test:reset` again. To prove the guard, temporarily set `DB_DATABASE=mirs` in the current process and run the reset command; it must fail before Laravel boots. Restore the safe value afterward. Never use a real production database name, host, or credential for this proof.

CI separately executes an unsafe-name rejection check. A temporary failing-test branch was also used to prove that a PHPUnit failure fails the workflow; that branch and test are removed after validation.

The complete local sequence is:

```powershell
composer validate --strict
composer ci:backend
npm ci
npm run check
docker build --target app --tag mirs-ci:local .
```

Run the two audit gates shown above as part of a release check.
