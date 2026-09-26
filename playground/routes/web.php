<?php

use Illuminate\Http\Request;
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

    foreach (glob($svgDir.'/'.$style.'-*.svg') ?: [] as $path) {
        $icon = substr(basename($path, '.svg'), strlen($style) + 1);

        if ($search === '' || str_contains($icon, strtolower($search))) {
            $icons[] = $icon;
        }
    }

    sort($icons);
    $total = count($icons);
    $icons = array_slice($icons, 0, 240);

    $styleAttr = "color: {$color}; --solar-secondary-color: {$secondary}; --solar-secondary-opacity: {$opacity}";

    return view('solar', compact(
        'style', 'search', 'size', 'color', 'stroke',
        'secondary', 'opacity', 'icons', 'total', 'styleAttr'
    ));
});
