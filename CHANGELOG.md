# Changelog

All notable changes to `solar-icons/blade` will be documented in this file.

## Unreleased

- Stroke width is now applied once from the package config instead of being baked into each file: this removes the duplicate `stroke-width` attribute Blade Icons produced when overriding the stroke width on a static `<x-solar-*>` component or via `svg()`. Raw SVGs published as assets ship without a baked `stroke-width` and render with the SVG default when used standalone.

## v2.0.0 (Initial release, in line with the Solar Icons v2 family)

- Full catalog generated from `@solar-icons/static` (8,784 SVGs: 1,451 icons × 6 styles + 13 deprecated aliases × 6), with catalog guards (per-style counts, no root dimensions, no hardcoded hex, solar classes) and generated `SolarIcon` enum (8,706 canonical cases).
- Dynamic `<x-solar-icon name="…" weight="…" />` component with fail-fast validation and path-traversal guards.
- Local Laravel playground (`playground/`, never published) with full icon grid and controls.
- Initial release scaffold (P0): ServiceProvider with single `solar` set, config, generation map, test suite and CI. First release will be versioned `2.0.0`, in line with the Solar Icons v2 family.
