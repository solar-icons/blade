---
name: 'solar-icons-blade'
type: 'package'
status: 'active'
---

# AGENT CONTEXT: solar-icons/blade

## Context & Objectives

Laravel Blade package for Solar Icons (Packagist: `solar-icons/blade`). The icon catalogue is not edited here: it is generated from the published `@solar-icons/static` package of the main repository (`saoudi-h/solar-icons`). This repository lives apart from the monorepo only because Packagist requires a package at the repository root.

## Workflow & Preferences

- **Releases are declaration-driven.** The bump and the changelog text come from what the maintainer declared in the changeset of `@solar-icons/static`, read by `bin/plan-release.php` (logic in `bin/lib/ReleasePlan.php`). Never decide a bump from file counts and never tag by hand.
- **Flow:** `.github/workflows/sync-static.yml` (daily, or manual dispatch) refreshes the catalogue, runs phpunit and Pint, and opens or updates the `release/next` pull request. Merging that pull request is the go-ahead: `.github/workflows/tag-release.yml` re-verifies the merged code, creates the `v<version>` tag and the GitHub Release. Packagist imports the tag through the GitHub hook. Neither workflow pushes to `main` or tags on its own.
- **Manual release** (PHP-only fix, or an override of the declaration): dispatch `sync-static` with `release: patch|minor` and a `summary`; add `dry_run: true` to rehearse without opening a pull request.
- **Versions:** independent from the other packages; only the major must match `@solar-icons/static`. A major step of static, a major mismatch, or removed icons in a patch stop the run on purpose.
- **Full procedure** (all languages, verification, rollback of partial releases): `docs/RELEASE-PROCEDURE.md` in the main repository, section 3, and section 11 for how the declaration drives the other lanes.
- **Commits:** Conventional Commits. English only.
- **Changelog:** keep notes under `## Unreleased`; the release pull request moves them under the new version.

## Stack & Architecture

- PHP 8.1 to 8.4, Laravel 10 to 12, blade-ui-kit/blade-icons. Tests with PHPUnit and Orchestra Testbench, style with Laravel Pint (`vendor/bin/pint --test`).
- No PHP is installed on some machines: run the tools in a `php:8.3-cli` container with the repository mounted.

## Key Directories

| Path                           | Description                                                            |
| ------------------------------ | ---------------------------------------------------------------------- |
| `resources/svg/`               | Generated SVGs from `@solar-icons/static`. Do not edit by hand.         |
| `src/SolarIcon.php`            | Generated enum of canonical icon names (`bin/generate-enum.php`).       |
| `bin/plan-release.php`         | Release planner CLI. Library: `bin/lib/ReleasePlan.php`.                |
| `.static-version`              | The `@solar-icons/static` version the catalogue was built from.         |
| `.github/workflows/`           | `sync-static.yml` (proposes), `tag-release.yml` (tags on merge), tests. |
| `tests/`                       | PHPUnit tests, including the release planner.                           |

## Known Constraints

- A tag pushed with the default `GITHUB_TOKEN` does not start other workflows: the tag job and the GitHub Release live in the same workflow on purpose.
- Creating the `release/next` pull request needs "Allow GitHub Actions to create and approve pull requests" enabled on the organization and on this repository.
- `bin/plan-release.php`, `bin/lib` and `AGENT.md` are `export-ignore`d: they never ship in the Composer archive.
