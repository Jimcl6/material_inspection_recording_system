# Technical Specifications Proposal

## Local Hosting Setup for Material Inspection Recording System

Prepared for: HPI internal company use

Proposed domain: mirs.hpi.org.ph

Prepared date: June 3, 2026

## 1. Executive Summary

This proposal recommends hosting the Material Inspection Recording System (MIRS) as an internal company web application using the custom domain `mirs.hpi.org.ph`. The recommended approach is to deploy the Laravel application on a dedicated internal server or virtual machine, expose it only through the company LAN or approved remote-access layer, and resolve `mirs.hpi.org.ph` through internal DNS.

This setup gives employees a professional domain-based access point while keeping the application private to the company network. It is more appropriate than placing the system directly on a public shared-hosting account because MIRS contains operational records, user accounts, approval workflows, and inspection data that should remain controlled.

## 2. Current Application Context

The existing project is a Laravel 9 web application using Inertia, Vue 3, Vite, and MySQL-compatible database connectivity. The current environment already points toward LAN-style deployment because `APP_URL` and `DB_HOST` are configured with private network addresses.

Important current indicators:

- Backend framework: Laravel 9
- Frontend stack: Inertia, Vue 3, Vite
- Database: MySQL-compatible database
- Current application URL style: LAN IP address
- Current database host style: LAN IP address
- Authentication and authorization: Laravel auth routes, role-based routes, and module permission routes

## 3. Recommended Deployment Model

The recommended deployment model is an internal intranet deployment:

Company users and tablets connect through the company LAN or Wi-Fi. Internal DNS resolves `mirs.hpi.org.ph` to the private IP address of the internal application server. The web server serves the Laravel application over HTTPS. The database remains private and is reachable only by the application server.

Recommended flow:

Company users/tablets -> Company LAN/Wi-Fi -> Internal DNS -> Internal app server -> Laravel app -> MySQL database

If remote access is required, it should be provided through VPN or a zero-trust access layer such as Cloudflare Access/Tunnel. The application should not be directly exposed to the public internet through router port forwarding.

## 4. Domain and DNS Recommendation

The company should configure `mirs.hpi.org.ph` to resolve internally to the application server IP address.

Example:

`mirs.hpi.org.ph -> 192.168.2.50`

Recommended DNS options:

- Windows Server DNS, if the company already uses Active Directory or Windows domain services
- Router-level DNS override, if supported by the company firewall/router
- Dedicated internal DNS such as Technitium DNS or Pi-hole
- Split-horizon DNS, where internal users resolve the domain to the private server IP while external users do not receive a public app endpoint

If `hpi.org.ph` is publicly managed by an external DNS provider, the company can still keep MIRS private by handling `mirs.hpi.org.ph` internally only, or by using a controlled access layer instead of direct public exposure.

## 5. Server Specification

Minimum pilot server:

- CPU: 4 cores
- RAM: 8 GB
- Storage: 250 GB SSD
- Network: Gigabit Ethernet
- Backup target: Separate NAS, external drive, or cloud backup destination

Recommended production server:

- CPU: 4 to 8 cores
- RAM: 16 GB
- Storage: 500 GB SSD or more
- Network: Gigabit Ethernet
- UPS: Recommended for power stability
- Backup target: Automated daily backup location separate from the server

Recommended operating system:

- Ubuntu Server LTS

Alternative:

- Windows Server with IIS or Apache/Nginx, if the company strongly prefers Windows administration

## 6. Software Stack

Recommended production stack:

- Web server: Nginx or Apache
- PHP: Compatible with Laravel 9 requirements, preferably PHP 8.1 or 8.2
- Database: MySQL or MariaDB
- Runtime tools: Composer, Node.js, npm
- TLS/HTTPS: Internal certificate authority, DNS-validated public certificate, or zero-trust provider certificate
- Backup tooling: Scheduled database dump and storage backup

Laravel production settings:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mirs.hpi.org.ph
ASSET_URL=https://mirs.hpi.org.ph
```

The web server must point to Laravel's `public/` directory only. The project root, `.env`, `storage/`, source code, and vendor files must not be directly browsable from the web.

## 7. Security Controls

Required controls:

- Keep MIRS LAN-only unless remote access is formally approved
- Use HTTPS for all browser access
- Keep the database private and accessible only from the app server
- Use strong Laravel user passwords
- Review admin and super-admin accounts regularly
- Disable debug mode in production
- Limit server administrator access
- Apply operating system, PHP, Laravel dependency, and database updates
- Maintain separate backups and test restoration

Recommended additional controls:

- VPN or Cloudflare Access/Tunnel for remote users
- IP restrictions for administrative server access
- Firewall rules that expose only HTTP/HTTPS to internal users
- Audit log review for sensitive actions
- Password reset and account deactivation procedure
- Quarterly permission review

Important privacy note:

`noindex` headers or robots.txt rules do not make an application private. They only discourage search engine indexing. Real privacy requires network restriction, authentication, access control, VPN, IP restriction, or a zero-trust access layer.

## 8. Backup and Recovery Plan

Daily backups should include:

- MySQL database
- Laravel `storage/app`
- Uploaded files and generated documents
- Production `.env` file stored securely
- Deployment notes and server configuration

Recommended retention:

- Daily backups for 14 to 30 days
- Monthly backups for 6 to 12 months

Recovery target:

- Restore the database and application files to a replacement server
- Confirm login, dashboard access, records listing, creation/editing, approval workflows, and export/import features

## 9. Deployment Procedure

High-level deployment steps:

1. Prepare internal server or VM.
2. Install web server, PHP, database client tools, Composer, Node.js, and npm.
3. Create a production database and database user.
4. Upload or clone the Laravel application.
5. Configure `.env` for production.
6. Install PHP dependencies.
7. Install and build frontend assets.
8. Run database migrations.
9. Configure the web server to point to Laravel `public/`.
10. Configure internal DNS for `mirs.hpi.org.ph`.
11. Enable HTTPS.
12. Run Laravel optimization commands.
13. Test application access from office PCs and tablets.
14. Configure backup schedule and verify restore process.

Core Laravel commands:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 10. Rollout Plan

Phase 1 - Pilot setup:

- Prepare the internal server
- Deploy the app
- Configure `mirs.hpi.org.ph` internally
- Test with selected users and selected tablets

Phase 2 - Security and backup readiness:

- Enforce HTTPS
- Verify role permissions
- Configure daily backup
- Test restore process
- Document admin access and recovery steps

Phase 3 - Company rollout:

- Add production users
- Train department representatives
- Monitor errors and user feedback
- Finalize support process

Phase 4 - Remote access, if needed:

- Implement VPN or zero-trust access
- Avoid direct public exposure
- Test access rules with selected remote users

## 11. Acceptance Criteria

The setup should be considered successful when:

- Users can access `https://mirs.hpi.org.ph` inside the company network
- The login page and authenticated dashboard load correctly
- Core modules can create, view, edit, approve, import, and export records as expected
- Only the Laravel `public/` directory is exposed by the web server
- `APP_ENV=production` and `APP_DEBUG=false` are active
- HTTPS is enabled
- Database access is not publicly exposed
- Daily backups run successfully
- A restore test has been completed
- Remote access, if enabled, requires VPN or zero-trust authentication

## 12. Key Risks and Mitigations

Risk: Application is exposed directly to the public internet.

Mitigation: Keep the server LAN-only and use VPN or zero-trust access for remote users.

Risk: Server failure causes loss of inspection records.

Mitigation: Configure daily database and file backups, then test restoration.

Risk: Misconfigured Laravel deployment exposes source code or `.env`.

Mitigation: Configure the web server document root to Laravel `public/` only.

Risk: Users cannot resolve `mirs.hpi.org.ph`.

Mitigation: Configure internal DNS and distribute DNS settings through DHCP.

Risk: Weak account management.

Mitigation: Use strong passwords, remove inactive users, and review admin permissions regularly.

## 13. Final Recommendation

The best setup for HPI is a dedicated internal Laravel server with internal DNS resolving `mirs.hpi.org.ph` to the server's private IP address, HTTPS enabled, database access restricted to the server, and remote access handled only through VPN or a zero-trust access layer.

This provides a professional company domain while keeping MIRS aligned with the security expectations of an internal operational system.
