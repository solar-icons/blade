# Blade Playground (local only, never published)

Minimal Laravel app consuming `solar-icons/blade` through a path repository,
so the grid always renders the working tree: no Packagist, no release.

## Run

Docker is used as the PHP runtime only. The stock `php:8.3-cli` image already
bundles what the app needs (`dom`, `mbstring`, `pdo_sqlite`, `curl`), and
`playground/vendor` is already installed, so no custom image and no Composer
run are required:

```bash
docker run -d --rm --name blade-playground \
  -v "$PWD/..:/bladesrc" -w /bladesrc/playground \
  -p 8321:8000 php:8.3-cli \
  php artisan serve --host=0.0.0.0 --port=8000
```

Then open http://localhost:8321/solar. Attribute checks live at
http://localhost:8321/solar/challenge. Stop the server with
`docker stop blade-playground`; the `--rm` flag removes the container at the
same time.

Reinstalling dependencies (`vendor/` is ignored by git) is the only case that
needs Composer, and therefore the `zip` extension: use the `composer:2` image
for that. The `solar-icons/blade` entry under `vendor/` is a relative symlink
to the repository root, so every edit is picked up without reinstalling.

## What it shows

- Full icon grid for one style at a time; use search to narrow it down.
- Controls: style, search, size, color, stroke width, duotone accent + opacity.
- Top box renders the same icon through the dynamic
  `<x-solar-icon name="…" weight="…" />` component for comparison.
- Routes: `routes/web.php` (`/solar` and `/solar/challenge`), views in
  `resources/views/`.
