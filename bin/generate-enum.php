#!/usr/bin/env php
<?php

/**
 * Generate the SolarIcon enum from `@solar-icons/static` metadata.
 *
 * Only canonical icon names are emitted — deprecated aliases are files on
 * disk (for drop-in compatibility) but never API surface.
 *
 * Usage: php bin/generate-enum.php [path/to/metadata-descriptions.json]
 */

declare(strict_types=1);

$metadataPath = $argv[1] ?? __DIR__.'/../node_modules/@solar-icons/static/dist/metadata-descriptions.json';

if (! file_exists($metadataPath)) {
    fwrite(STDERR, "Metadata not found: {$metadataPath}\n");
    exit(1);
}

$descriptions = json_decode((string) file_get_contents($metadataPath), true, 512, JSON_THROW_ON_ERROR);

$styles = [
    'linear' => 'Linear',
    'bold' => 'Bold',
    'broken' => 'Broken',
    'outline' => 'Outline',
    'bold-duotone' => 'BoldDuotone',
    'line-duotone' => 'LineDuotone',
];

$toPascal = static fn (string $kebab): string => str_replace(' ', '', ucwords(str_replace('-', ' ', $kebab)));

$cases = [];

foreach ($descriptions as $description) {
    $icon = $description['name'];

    foreach ($styles as $style => $stylePascal) {
        $cases["solar-{$style}-{$icon}"] = $toPascal($style).$toPascal($icon);
    }
}

ksort($cases);

$lines = [];

foreach ($cases as $value => $case) {
    $lines[] = "    case {$case} = '{$value}';";
}

$body = implode("\n", $lines);

$php = <<<PHP
<?php

declare(strict_types=1);

namespace SolarIcons\\Blade;

/**
 * Every Solar icon, in every style. Values are the blade-icons icon names,
 * usable directly: `svg(SolarIcon::LinearHeart->value)`.
 *
 * GENERATED FILE — do not edit. Regenerate with `php bin/generate-enum.php`.
 */
enum SolarIcon: string
{
{$body}

    public function weight(): string
    {
        return match (true) {
            str_starts_with(\$this->value, 'solar-bold-duotone-') => 'bold-duotone',
            str_starts_with(\$this->value, 'solar-line-duotone-') => 'line-duotone',
            str_starts_with(\$this->value, 'solar-linear-') => 'linear',
            str_starts_with(\$this->value, 'solar-bold-') => 'bold',
            str_starts_with(\$this->value, 'solar-broken-') => 'broken',
            default => 'outline',
        };
    }

    public function icon(): string
    {
        return substr(\$this->value, strlen('solar-'.\$this->weight().'-'));
    }
}

PHP;

$output = __DIR__.'/../src/SolarIcon.php';
file_put_contents($output, $php);

echo 'Generated '.count($cases)." cases → {$output}\n";
