# Android PWA Rollout

MIRS can be installed from Google Chrome as a standalone Android web app. The
installed app remains online-first: users need a network connection to view or
submit inspection records.

## Local use during a server outage

The application can continue running from a development PC while the Linux host
is unavailable. Set `APP_URL`, `ASSET_URL`, and `VITE_APP_URL` to the PC's
current LAN address and use the same port for all three values. Then run:

```powershell
php artisan optimize:clear
php artisan serve --host=0.0.0.0 --port=8000
```

Other devices on the same network can open `http://<PC-LAN-IP>:8000`. The
compiled frontend in `public/build` is sufficient for normal use; run
`npm run dev` separately only when actively changing frontend source files.

This LAN HTTP setup is temporary browser access, not the final Android
installation path. Android does not treat a non-localhost HTTP origin as a
secure PWA context, and PHP's built-in server does not apply the production
Apache or Nginx service-worker headers. Wait for the canonical HTTPS origin
before installing MIRS from Chrome.

## Production prerequisites

- Use one canonical HTTPS origin. Plain LAN HTTP addresses are development-only
  and cannot register the production service worker.
- Set `APP_URL` and `VITE_APP_URL` to the HTTPS origin. Keep `ASSET_URL` empty or
  on that same origin, and align `SESSION_DOMAIN` and
  `SANCTUM_STATEFUL_DOMAINS`.
- Build and serve Laravel from `public/`. Delete `public/hot` before starting the
  production runtime. Production Laravel also ignores that development marker as
  a safeguard against accidentally emitting Vite development-server URLs.
- Serve `/build/sw.js` with `Service-Worker-Allowed: /` and
  `Cache-Control: no-cache`. The included Apache and Nginx configurations provide
  these headers.
- Do not install the app until the final production origin is stable. The
  manifest identity and launcher installation are tied to that origin.

The Docker image already removes `public/hot` and runs the production frontend
build. Rebuild the image when PWA or frontend files change:

```bash
docker compose build app
docker compose up -d app
```

Before deployment, run the complete frontend and PWA regression gate:

```bash
npm run check
```

## Deployment verification

Run these checks against the final HTTPS origin:

```bash
curl -I https://mirs.example.com/build/sw.js
curl -I https://mirs.example.com/build/manifest.webmanifest
curl -s https://mirs.example.com/login
```

Confirm:

- `/build/sw.js` returns `200`, JavaScript content, root worker permission, and
  no-cache headers.
- The manifest returns `200` with `application/manifest+json`.
- Login HTML references `/build/assets/...`, not port `5173` or another Vite
  development server.
- Browser developer tools show a service-worker registration whose scope is the
  HTTPS origin root.
- The manifest reports `id`, `start_url`, and `scope` as `/dashboard`,
  `/dashboard`, and `/`, with `display` set to `standalone`.

If Cloudflare returns HTTP `530` with error `1033`, the named tunnel has no
healthy connector. Restore the `cloudflared` service or container on the origin
server and confirm that its configured local app port is reachable before
continuing PWA checks. This is a tunnel outage, not a manifest or service-worker
failure.

## Tablet installation

1. Update Google Chrome on the Android tablet.
2. Remove any older MIRS shortcut or web app. A shortcut with a small Chrome
   badge is not the required installation.
3. Open the final HTTPS MIRS address in Chrome and sign in.
4. Use the in-app **Install MIRS** action, or open Chrome's menu and choose
   **Add to home screen**, then **Install**. Do not choose **Create shortcut**.
5. Launch MIRS from the Android launcher rather than from an existing browser
   tab.

Accept the installation only when:

- MIRS appears in Android **Settings > Apps**.
- The launcher uses the branded MIRS icon without a Chrome shortcut badge.
- Launching the icon opens `/dashboard` without Chrome's address bar or toolbar.
- Login redirects, session expiry, logout, deep links, Android back navigation,
  exports, and record forms still work.

## Updates and connectivity

- A waiting service worker displays **A MIRS update is ready**. Users choose when
  to reload so in-progress form work is not interrupted.
- The app checks for updates when it returns to the foreground and once per hour
  while online.
- An offline banner explains that records cannot be viewed or submitted. No
  authenticated HTML, inspection data, or pending submissions are cached.

## Managed tablets

Standalone PWA installation does not lock users into MIRS or hide Android system
navigation. Company-owned single-purpose tablets should use an Android
Enterprise managed web app with full-screen or kiosk policy. That is a device
management deployment and does not require the MIRS team to distribute an APK.
