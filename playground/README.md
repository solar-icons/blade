# Blade Playground (local only, never published)

Minimal Laravel app consuming `solar-icons/blade` through a path repository,
so the grid always renders the working tree — no Packagist, no release.

## Run

Requires Docker only (no local PHP needed):

```bash
docker run --rm -v "$PWD/..:/bladesrc" -w /bladesrc/playground -p 8321:8000 solar-blade-php php artisan serve --host=0.0.0.0 --port=8000
```

Then open http://localhost:8321/solar.

Build the PHP image once (see flagship worklog `2026-09-26-BLADE-P0.md`
follow-ups for the Dockerfile): `php:8.3-cli` + `dom`, `mbstring`, `zip`
+ Composer 2.

## What it shows

- Full icon grid for one style at a time (capped at 240, use search).
- Controls: style, search, size, color, stroke width, duotone accent + opacity.
- Top box renders the same icon through the dynamic
  `<x-solar-icon name="…" weight="…" />` component for comparison.
- Route: `routes/web.php` (`/solar`), view: `resources/views/solar.blade.php`.
