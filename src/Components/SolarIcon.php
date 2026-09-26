<?php

declare(strict_types=1);

namespace SolarIcons\Blade\Components;

use BladeUI\Icons\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use InvalidArgumentException;
use SolarIcons\Blade\BladeServiceProvider;

class SolarIcon extends Component
{
    public function __construct(
        private Factory $icons,
        public string $name,
        public string $weight = 'linear',
    ) {
        if (! in_array($weight, BladeServiceProvider::STYLES, true)) {
            throw new InvalidArgumentException(
                'Unknown icon weight "'.$weight.'". Valid weights: '.implode(', ', BladeServiceProvider::STYLES).'.'
            );
        }

        // The name becomes part of a file path, so constrain it to the
        // icon filename alphabet. This rejects path traversal (`../`),
        // slashes and extensions at the gate — before any file access.
        if (! preg_match('/^[a-z0-9-]+$/', $name)) {
            throw new InvalidArgumentException(
                'Invalid icon name "'.$name.'". Use the kebab-case icon name, e.g. "heart".'
            );
        }
    }

    public function render(): View
    {
        // Attribute forwarding happens in the view: the $attributes bag is
        // only hydrated after render() runs (see compiled component flow),
        // so resolving here would see an empty bag.
        return view('solar-icons-blade::icon');
    }
}
