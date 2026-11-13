# Changelog

All notable changes to `solar-icons/blade` will be documented in this file.

## Unreleased

## v2.1.0

Built from @solar-icons/static 2.6.0 (+354 added, ~306 modified, -0 removed SVG files).

Fix the secondary tone of duotone icons rendering lighter than intended on about 300 icons (for example `printer` in Bold Duotone). The secondary opacity was applied on the group and again on each element inside it, so it multiplied (0.25 instead of 0.5 by default). It is now applied once per accent layer, so `--solar-secondary-opacity` means what it says.

Expand catalogue to 1,576 icon families (9,456 SVGs):

- Add 59 new icon families across all 6 canonical styles (`Bold`, `BoldDuotone`, `Broken`, `Linear`, `LineDuotone`, `Outline`): `bank`, `factory`, `chart-area`, `chart-candlestick`, `chart-gantt`, `chart-scatter`, `bluetooth-off`, `lightbulb-off`, `monitor-off`, `plug`, `apple`, `burger`, `cake`, `fork-knife`, `ice-cream-cone`, `pizza`, `door-closed`, `git-merge`, `workflow`, `flask`, `microscope`, `bitcoin`, `coins`, `piggy-bank`, `pound`, `rupee`, `yen`, `fish`, `flower`, `mountains`, `sprout`, `tree`, `airplane`, `anchor`, `bicycle`, `ship`, `truck`, `fingerprint`, `wrench`, `highlighter`, `paragraph`, `subscript`, `superscript`, `text-align-center`, `text-align-left`, `text-align-right`, `calendar-check`, `calendar-close`, `pen-tool`, `cursor-click`, `diamond`, `dice`, `flag-off`, `gavel`, `justice-scale`, `megaphone`, `puzzle`, `skull`, and `gallery-off`.

## v2.0.4 (Built from @solar-icons/static 2.5.0 (+228/~42/-0))

## v2.0.3 (Built from @solar-icons/static 2.4.0 (+168/~754/-0))

## v2.0.2 (Built from @solar-icons/static 2.3.4 (+0/~7542/-0))

## v2.0.1 (Manual PHP-only release)

- Stroke width is now applied once from the package config instead of being baked into each file: this removes the duplicate `stroke-width` attribute Blade Icons produced when overriding the stroke width on a static `<x-solar-*>` component or via `svg()`. Raw SVGs published as assets ship without a baked `stroke-width` and render with the SVG default when used standalone.

## v2.0.0 (Initial release, in line with the Solar Icons v2 family)

- Full catalog generated from `@solar-icons/static` (8,784 SVGs: 1,451 icons × 6 styles + 13 deprecated aliases × 6), with catalog guards (per-style counts, no root dimensions, no hardcoded hex, solar classes) and generated `SolarIcon` enum (8,706 canonical cases).
- Dynamic `<x-solar-icon name="…" weight="…" />` component with fail-fast validation and path-traversal guards.
- Local Laravel playground (`playground/`, never published) with full icon grid and controls.
- Initial release scaffold (P0): ServiceProvider with single `solar` set, config, generation map, test suite and CI. First release will be versioned `2.0.0`, in line with the Solar Icons v2 family.
