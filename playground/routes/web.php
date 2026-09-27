<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use SolarIcons\Blade\BladeServiceProvider;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/solar', function (Request $request) {
    $style = $request->query('style', 'linear');
    if (! in_array($style, BladeServiceProvider::STYLES, true)) {
        $style = 'linear';
    }

    $search = trim((string) $request->query('search', ''));
    $size = min(96, max(16, (int) $request->query('size', 32)));
    $color = (string) $request->query('color', '#1c274c');
    if (! preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
        $color = '#1c274c';
    }
    $stroke = min(3, max(0.5, (float) $request->query('stroke', 1.5)));
    $secondary = (string) $request->query('secondary', '#2563eb');
    if (! preg_match('/^#[0-9a-fA-F]{6}$/', $secondary)) {
        $secondary = '#2563eb';
    }
    $opacity = min(1, max(0, (float) $request->query('opacity', 0.5)));

    $svgDir = base_path('vendor/solar-icons/blade/resources/svg');
    $icons = [];
    $hashes = [];

    // Match longest style prefixes first: a "bold-*.svg" glob would also
    // catch "bold-duotone-*" files.
    $ordered = ['bold-duotone', 'line-duotone', 'linear', 'bold', 'broken', 'outline'];

    foreach (glob($svgDir.'/*.svg') ?: [] as $path) {
        $file = basename($path, '.svg');

        foreach ($ordered as $prefix) {
            if (str_starts_with($file, $prefix.'-')) {
                if ($prefix === $style) {
                    $icon = substr($file, strlen($prefix) + 1);

                    if ($search === '' || str_contains($icon, strtolower($search))) {
                        $icons[] = $icon;
                        $hashes[] = md5_file($path);
                    }
                }
                break;
            }
        }
    }

    sort($icons);
    $total = count($icons);
    // Deprecated-alias files reuse the canonical SVG byte-for-byte, so
    // unique contents = unique icons.
    $unique = count(array_unique($hashes));

    $styleAttr = "color: {$color}; --solar-secondary-color: {$secondary}; --solar-secondary-opacity: {$opacity}";

    return view('solar', compact(
        'style', 'search', 'size', 'color', 'stroke',
        'secondary', 'opacity', 'icons', 'total', 'unique', 'styleAttr'
    ));
});

Route::get('/solar/challenge', function () {
    $factory = app(BladeUI\Icons\Factory::class);

    $scenarios = [
        [
            'title' => 'Baseline — no styling attributes',
            'expected' => 'solar + solar-heart-linear effective, stroke 1.5, currentColor inherits. (Fixed 48px via attributes for display only.)',
            'html' => $factory->svg('solar-linear-heart', '', ['width' => '48', 'height' => '48'])->toHtml(),
        ],
        [
            'title' => 'class="demo-red" — KNOWN DIVERGENCE',
            'expected' => 'Renders RED via the passed class (first class attribute wins), solar classes shadowed (React merges them instead). A real stylesheet class is used on purpose: Tailwind utilities passed as PHP strings would resolve to nothing.',
            'html' => $factory->svg('solar-linear-heart', 'demo-red', ['width' => '48', 'height' => '48'])->toHtml(),
        ],
        [
            'title' => 'style="color: red"',
            'expected' => 'Icon renders red. File roots carry no style attribute, so no conflict.',
            'html' => $factory->svg('solar-linear-heart', '', ['style' => 'color: red', 'width' => '48', 'height' => '48'])->toHtml(),
        ],
        [
            'title' => 'stroke-width="2.5" over file default 1.5',
            'expected' => 'Renders at 2.5: the passed attribute is injected BEFORE the file one, browsers keep the first.',
            'html' => $factory->svg('solar-linear-heart', '', ['stroke-width' => '2.5', 'width' => '48', 'height' => '48'])->toHtml(),
        ],
        [
            'title' => 'width="48" height="48"',
            'expected' => 'Sized 48px. Files carry no dimensions, single clean attributes.',
            'html' => $factory->svg('solar-linear-heart', '', ['width' => '48', 'height' => '48'])->toHtml(),
        ],
        [
            'title' => 'Duotone accent + opacity vars',
            'expected' => 'Secondary layer blue at 0.5 opacity, primary inherits color.',
            'html' => $factory->svg('solar-line-duotone-heart', '', ['style' => 'color: #1c274c; --solar-secondary-color: #2563eb; --solar-secondary-opacity: 0.5', 'width' => '48', 'height' => '48'])->toHtml(),
        ],
        [
            'title' => 'Chaos — class + style + stroke + size combined',
            'expected' => 'Size 40, green (inline style beats the demo-red class), stroke 2, orange outline from the passed class, solar classes shadowed (same divergence as #2).',
            'html' => $factory->svg('solar-bold-heart', 'demo-red demo-outline', ['style' => 'color: green', 'stroke-width' => '2', 'width' => '40', 'height' => '40'])->toHtml(),
        ],
        [
            'title' => 'Custom CSS class beats width attributes',
            'expected' => 'Renders at 64px, not 48: .sizer-64 is a real stylesheet rule on this page, and CSS beats presentational width/height attributes. This is the reliable way to size icons via classes.',
            'html' => $factory->svg('solar-linear-heart', 'sizer-64', ['width' => '48', 'height' => '48'])->toHtml(),
        ],
        [
            'title' => 'Dynamic component merges classes (the #2 fix)',
            'expected' => 'Single class attribute: "solar solar-heart-linear demo-red" — file, config and passed classes concatenated. No duplicate attributes anywhere.',
            'html' => Blade::render('<x-solar-icon name="heart" weight="linear" class="demo-red" width="48" height="48" />'),
        ],
    ];

    return view('challenge', compact('scenarios'));
});
