# Phase 3: Internal-only access deployment requirements

MIRS is admin-provisioned. Public registration routes do not exist, and all authenticated access requires an active account. Deployments must remain reachable only through the approved internal network, VPN, or authenticated internal reverse proxy.

## Required environment configuration

Use the actual internal hostname in the deployment environment; do not commit it to source control.

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mirs.internal.example
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=mirs.internal.example
SANCTUM_STATEFUL_DOMAINS=mirs.internal.example
CORS_ALLOWED_ORIGINS=https://mirs.internal.example
TRUSTED_PROXIES=192.0.2.10
```

`CORS_ALLOWED_ORIGINS` accepts a comma-separated list of exact `http://` or `https://` origins, including ports when needed. Wildcards, URL paths, embedded credentials, queries, and fragments are rejected. Credentialed CORS is enabled only when at least one named origin is valid.

`TRUSTED_PROXIES` accepts comma-separated exact proxy IP addresses or CIDRs. Set it to the real reverse-proxy hop visible to the container. Wildcard proxy trust is intentionally rejected. Confirm HTTPS redirects and generated URLs after changing this value.

Use `SESSION_SAME_SITE=strict` when no cross-site navigation workflow is required. Use `lax` for the normal same-site application flow. Never use `none` without an explicit reviewed cross-site requirement and secure cookies.

## Web server and container requirements

- Terminate TLS at the approved internal reverse proxy and redirect HTTP to HTTPS. `APP_URL` must use `https://`; the application middleware then enforces HTTPS for that configured host.
- Forward the original host and protocol only from the proxy addresses listed in `TRUSTED_PROXIES`.
- Expose only Laravel's `public` directory. The supplied Nginx configuration uses `/var/www/html/public` and sends only `/index.php` to PHP-FPM.
- Do not publish the repository root, `.env`, `vendor`, `bootstrap/cache`, or private `storage` paths. Only the deliberate `public/storage` link to `storage/app/public` may be served.
- Keep `APP_DEBUG=false` and `LOG_LEVEL=warning` (or stricter) so production responses use generic error pages without stack traces.
- Do not run a Vite development server or PHP development server in production.

## Deployment verification

1. Confirm the deployed environment uses the approved hostname and contains no wildcard CORS origin or wildcard trusted proxy.
2. Run `php artisan optimize:clear`, then `php artisan config:cache` and `php artisan route:cache` inside the new container.
3. Confirm plain HTTP redirects to HTTPS and the session cookie is `Secure`, `HttpOnly`, and has the selected `SameSite` attribute.
4. From an allowed internal origin, verify the material API preflight returns that exact origin and `Access-Control-Allow-Credentials: true`.
5. From an unlisted origin, verify no `Access-Control-Allow-Origin` or credentials header is returned.
6. Request a controlled nonexistent URL and confirm the response contains no stack trace, environment value, or filesystem path.
7. Confirm requests for `/.env`, `/vendor/`, `/bootstrap/cache/`, and `/storage/logs/` are denied or not found.
8. Confirm active login, inactive-account rejection, session termination after deactivation, and material API 401/403/200 behavior.
