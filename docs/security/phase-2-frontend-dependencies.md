# Phase 2 frontend dependency security notes

Phase 2 applies compatible frontend dependency patches and keeps the existing Vite major version.

The production dependency audit (`npm audit --omit=dev`) reports no vulnerabilities. The full development audit still reports advisories through Vite 4 and its build-only dependency chain. npm's available remediation upgrades Vite and `laravel-vite-plugin` across major versions, which is outside this phase's patch-only compatibility scope.

Until the supported frontend toolchain upgrade is scheduled:

- Do not expose the Vite development server outside a trusted development network.
- Do not run the development server in production.
- Build production assets from trusted source and dependencies only.
- Keep the remaining development-only findings visible; do not add audit suppressions.

The production application serves the compiled assets and does not install development dependencies in the final Docker image.
