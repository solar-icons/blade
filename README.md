# Solar Icons for Laravel Blade

A package to easily make use of [Solar Icons](https://solar-icons.vercel.app) in your Laravel Blade views.

For a full list of available icons see the SVG directory or preview them at [solar-icons.vercel.app/icons](https://solar-icons.vercel.app/icons).

## Requirements

- PHP 8.1 or higher
- Laravel 9.0 or higher

## Installation

```bash
composer require solar-icons/blade
```

## Blade Icons

Solar Icons Blade uses [Blade Icons](https://github.com/driesvints/blade-icons) under the hood. Please refer to the Blade Icons readme for additional functionality. We also recommend to [enable icon caching](https://github.com/driesvints/blade-icons#icon-caching) with this library — the package ships over 8,000 SVGs, so caching keeps rendering fast.

## Usage

Icons are available in six styles — `linear`, `bold`, `broken`, `outline`, `bold-duotone` and `line-duotone` — as self-closing Blade components which compile to SVG icons:

```blade
<x-solar-linear-heart />
<x-solar-bold-heart />
<x-solar-line-duotone-heart />
```

You can also pass classes to your icon components:

```blade
<x-solar-linear-heart class="w-6 h-6 text-gray-500" />
```

And even use inline styles:

```blade
<x-solar-linear-heart style="color: #555" />
```

### A note on Tailwind classes

Tailwind generates CSS by scanning your template files for class names.
Classes passed as dynamic PHP strings (variables, database values, or the
dynamic `<x-solar-icon>` component) are invisible to that scan, so no CSS
is generated for them. Either safelist the classes you use in
`tailwind.config.js`, or prefer attributes that always work regardless of
your CSS pipeline: `width`/`height`, `style`, and `stroke-width`.

### A note on the `class` attribute

Blade Icons prepends a passed `class` to the icon's own
`solar solar-{name}-{style}` classes, producing two `class` attributes on
the `<svg>`. Browsers apply the first one, so a passed class shadows the
built-in solar classes (our JS packages merge them instead). If you rely
on `.solar-*` selectors, avoid passing `class` on the same icon.

## Dynamic icons

One component covers every icon and style — handy for switching styles
server-side or rendering icon names coming from the database:

```blade
<x-solar-icon name="heart" weight="linear" />
<x-solar-icon name="heart" weight="bold-duotone" class="w-6 h-6" />
<x-solar-icon :name="$menuItem->icon" weight="linear" />
```

`weight` defaults to `linear`. Unknown names or weights fail fast with an
exception instead of rendering silently broken output.

Duotone styles (`bold-duotone`, `line-duotone`) expose a second color via CSS variables, which the browser resolves at render time:

```blade
<x-solar-line-duotone-heart style="--solar-secondary-color: #2563eb; --solar-secondary-opacity: 0.5" />
```

## Raw SVG Icons

If you want to use the raw SVG icons as assets, you can publish them using:

```bash
php artisan vendor:publish --tag=solar-icons-blade --force
```

Then use them in your views like:

```blade
<img src="{{ asset('vendor/solar-icons-blade/linear-heart.svg') }}" width="10" height="10" />
```

## Configuration

This package also offers the ability to use features from Blade Icons like default classes, default attributes, etc. If you'd like to configure these, publish the config file:

```bash
php artisan vendor:publish --tag=solar-icons-blade-config
```

## Documentation, issues and icon requests

Full documentation lives on the [Solar Icons docs site](https://solar-icons.vercel.app). Please report PHP-specific bugs here, and open general issues and icon requests in the [main repository](https://github.com/saoudi-h/solar-icons) so everything stays in one place.

## Changelog

Check out the CHANGELOG in this repository for all the recent changes.

## License

Package code is MIT. Icons by [480 Design](https://www.figma.com/community/file/1166831539721848736) (CC BY 4.0) plus curated Solar extensions — see the main repository for full attribution.
