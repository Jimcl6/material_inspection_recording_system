# Material Inspection Recording System

Material Inspection Recording System (MIRS) is an internal Laravel application for recording, importing, approving, and auditing manufacturing inspection records. It is built for company LAN or VPN use and combines Laravel, Inertia, Vue, MySQL-compatible storage, role-based access control, module permissions, and spreadsheet import/export workflows.

## Core Modules

- Dashboard and authenticated profile management
- Annealing Checks
- Magnetism Checksheet
- Temperature Records
- Torque Records
- Material Monitoring Checksheets
- Welding Checksheets, including legacy Diaphragm Welding redirects
- Modification Logs
- Pending Approvals and approval notifications
- User Management with employee QR code generation and scanning
- Departments, Positions, Roles, and permission management
- Activity Logs for administrative audit review

Most operational modules are protected by module permissions such as `view`, `create`, `update`, `delete`, `import`, `export`, `approve`, and `reject`.

## Tech Stack

- PHP 8.0.2+
- Laravel 9
- MySQL or MariaDB
- Inertia.js
- Vue 3
- Vite
- Tailwind CSS and Bootstrap
- Ziggy route helpers
- Laravel Sanctum
- Maatwebsite Excel
- Docker, Nginx, and PHP-FPM support for internal deployment

## Local Development

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

Create and configure the environment file:

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` for the local database:

```env
APP_NAME="Material Inspection Recording System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mirs
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations:

```bash
php artisan migrate
```

Start the Laravel and Vite development servers:

```bash
php artisan serve
npm run dev
```

If PowerShell blocks `npm` through `npm.ps1`, use `npm.cmd` instead:

```bash
npm.cmd run dev
```

## Frontend Build

Build production frontend assets with:

```bash
npm run build
```

After adding, renaming, or removing Laravel routes used by Vue through `route(...)`, regenerate Ziggy routes:

```bash
php artisan ziggy:generate
```

## Database Seeders

The project includes seeders for roles, permissions, users, activity data, material titles, and welding item-code configuration.

Common seeders:

```bash
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=UserPermissionSeeder
php artisan db:seed --class=PositionPermissionSeeder
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=UserManagementSeeder
php artisan db:seed --class=MaterialSubLotTitleSeeder
php artisan db:seed --class=DiaphragmItemCodeSeeder
```

Do not run destructive commands such as `migrate:fresh` or broad reseeding against a live database unless a full data reset is intentional and approved. For production or shared databases, run only the specific seeder needed for the change.

## Approval Workflow

Approvals are controlled by the `APPROVALS_ENABLED` feature flag.

```env
APPROVALS_ENABLED=false
```

When enabled, approval routes and notifications are available only to users with the required module approval permissions. Current approval-capable areas include Annealing Checks, Temperature Records, Torque Records, and Welding Checksheets.

## Import and Export Workflows

Several modules support spreadsheet import, preview, execute, and export flows. Import workflows should use the preview step first so users can review parsed rows, duplicates, validation errors, and record changes before writing to the database.

Import/export support exists across modules such as Annealing, Magnetism, Temperature, Torque, and Welding. Keep import validation close to each module controller/request so business rules remain module-specific and auditable.

## Internal Deployment Notes

MIRS contains operational records, user accounts, approval data, and audit logs. It should normally be deployed as an internal application behind LAN, VPN, firewall, or a controlled access layer.

Production environment basics:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-internal-mirs-domain
SESSION_SECURE_COOKIE=true
```

The web server must point to Laravel's `public/` directory only. Do not expose the project root, `.env`, `storage/`, `vendor/`, or source files directly through the web server.

Recommended internal deployment controls:

- Keep the database private and reachable only from the app server.
- Expose only HTTP/HTTPS to approved internal users.
- Use HTTPS for browser access whenever possible.
- Keep `APP_DEBUG=false` in production.
- Review admin and super-admin accounts regularly.
- Maintain database and `storage/` backups.
- Test restore procedures, not just backup creation.

## Docker Deployment

The repository includes Docker deployment files for an internal Laravel runtime:

- `Dockerfile`
- `docker-compose.yml`
- `.env.docker.example`
- `docker/nginx/default.conf`
- `docker/php/php.ini`
- `docker/supervisord.conf`
- `docker/entrypoint.sh`

Create an environment file from the Docker example:

```bash
cp .env.docker.example .env
```

Set the correct values for the deployment host, port, application key, and database:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-linux-server-ip:8080
APP_PORT=8080

DB_CONNECTION=mysql
DB_HOST=your-database-host
DB_PORT=3306
DB_DATABASE=your-database-name
DB_USERNAME=your-database-user
DB_PASSWORD=your-database-password
```

Build and start the container:

```bash
docker compose up -d --build
```

Run Laravel commands inside the container:

```bash
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan ziggy:generate
```

Use targeted seeders only when needed:

```bash
docker compose exec app php artisan db:seed --class=UserPermissionSeeder --force
docker compose exec app php artisan db:seed --class=AdminUserSeeder --force
```

## Common Maintenance Commands

Clear Laravel caches:

```bash
php artisan optimize:clear
```

List routes:

```bash
php artisan route:list
```

Build frontend assets:

```bash
npm run build
```

Run tests:

```bash
php artisan test
```

Check PHP syntax for a changed file:

```bash
php -l app/Http/Controllers/ExampleController.php
```

## Verification Checklist

After deployment or major changes, verify:

- Login works for an authorized administrator.
- Dashboard loads with compiled assets.
- Main modules list records without errors.
- Create, edit, import, export, and delete actions work for permitted users.
- Approval pages work when `APPROVALS_ENABLED=true`.
- Activity Logs record sensitive actions.
- User Management and QR scanning work on intended devices.
- The app does not emit stale Vite dev-server URLs in production.

## Related Documentation

- [User Management Module](USER_MANAGEMENT_README.md)
- [Local Hosting Technical Specifications](docs/mirs-local-hosting-technical-specs-proposal.md)

## Security Reporting

Report security issues, exposed credentials, incorrect permissions, or production access concerns to the project maintainer or internal IT owner. Do not commit real production `.env` values, database dumps, user exports, or private credentials to the repository.
