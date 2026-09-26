<?php

/**
 * Icon generation map for `vendor/bin/blade-icons-generate`.
 *
 * Sources are the `@solar-icons/static` npm dist files (see package.json).
 * Icons land in a single set as `{style}-{icon}.svg` via `output-prefix`
 * (the blade-icons `svg()` helper only resolves single-segment prefixes,
 * so the style lives in the filename, not in the set name).
 * The `after` hook strips width/height so icons scale via CSS, following
 * the blade-icons convention.
 */

$svgNormalization = static function (string $tempFilepath, array $iconSet) {
    $doc = new DOMDocument();
    $doc->load($tempFilepath);
    $svgElement = $doc->getElementsByTagName('svg')[0];
    $svgElement->removeAttribute('width');
    $svgElement->removeAttribute('height');
    $doc->save($tempFilepath);

    $fileLines = file($tempFilepath);
    array_shift($fileLines);

    $lastKey = count($fileLines) - 1;
    $fileLines[$lastKey] = trim($fileLines[$lastKey]);
    file_put_contents($tempFilepath, $fileLines);
};

return [
    [
        'source' => __DIR__.'/../node_modules/@solar-icons/static/dist/icons/linear',
        'destination' => __DIR__.'/../resources/svg',
        'output-prefix' => 'linear-',
        'after' => $svgNormalization,
        'safe' => true,
    ],
    [
        'source' => __DIR__.'/../node_modules/@solar-icons/static/dist/icons/bold',
        'destination' => __DIR__.'/../resources/svg',
        'output-prefix' => 'bold-',
        'after' => $svgNormalization,
        'safe' => true,
    ],
    [
        'source' => __DIR__.'/../node_modules/@solar-icons/static/dist/icons/broken',
        'destination' => __DIR__.'/../resources/svg',
        'output-prefix' => 'broken-',
        'after' => $svgNormalization,
        'safe' => true,
    ],
    [
        'source' => __DIR__.'/../node_modules/@solar-icons/static/dist/icons/outline',
        'destination' => __DIR__.'/../resources/svg',
        'output-prefix' => 'outline-',
        'after' => $svgNormalization,
        'safe' => true,
    ],
    [
        'source' => __DIR__.'/../node_modules/@solar-icons/static/dist/icons/bold-duotone',
        'destination' => __DIR__.'/../resources/svg',
        'output-prefix' => 'bold-duotone-',
        'after' => $svgNormalization,
        'safe' => true,
    ],
    [
        'source' => __DIR__.'/../node_modules/@solar-icons/static/dist/icons/line-duotone',
        'destination' => __DIR__.'/../resources/svg',
        'output-prefix' => 'line-duotone-',
        'after' => $svgNormalization,
        'safe' => true,
    ],
];
