# SHELTER COFFEE — website + owner dashboard

Rebuild of www.shelterjo.com. Laravel 13 + MySQL on Cloudways, Blade, Vite ([ADR-001](docs/adr/ADR-001-platform.md)).

- **Start here:** [`CLAUDE.md`](CLAUDE.md) (rules + sources of truth) · [`docs/PROGRESS.md`](docs/PROGRESS.md) (live status)
- **Architecture:** [`docs/architecture/PLATFORM-ARCHITECTURE.md`](docs/architecture/PLATFORM-ARCHITECTURE.md) · ops specs in [`docs/platform/`](docs/platform/)
- **Toolchain:** [`docs/TOOLCHAIN.md`](docs/TOOLCHAIN.md)

## Local setup
```sh
composer setup               # install, .env, key, migrate, npm ci, build
git config core.hooksPath .githooks   # gitleaks + pint on commit
php artisan serve
```

## Checks
| Level | Command |
|---|---|
| FAST | `composer qa:fast` (Pint · Larastan · PHPUnit) · `npm run qa:fast` (TypeScript · ESLint · Prettier · Vitest) |
| Build | `npm run build` (tokens → brand guard → Vite → bundle budget) |
| Security | `gitleaks git .` · `semgrep scan --config .semgrep --metrics=off` · `composer audit` · `npm audit` |
| E2E / a11y / responsive | `cd tooling && npm run test:e2e` |
