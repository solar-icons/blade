<?php

declare(strict_types=1);

namespace SolarIcons\Blade;

use function htmlspecialchars;

/**
 * Merges user-supplied attributes into a raw SVG string with a single,
 * valid attribute set — unlike Blade Icons' prepend behaviour, which
 * emits duplicate attributes (second occurrence ignored by browsers).
 *
 * Merge rules (mirroring the v2 framework packages):
 * - `class`: file classes + config default + passed classes, concatenated
 *   in that order into ONE class attribute.
 * - everything else: passed value wins, kept at a single occurrence.
 * - `title`: rendered as a `<title>` element with `role="img"`, matching
 *   the Blade Icons accessibility behaviour.
 */
final class SvgMerger
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function merge(string $svg, array $attributes, string $configClass = '', array $configAttributes = []): string
    {
        $attributes = array_merge($configAttributes, $attributes);

        $passedClass = trim((string) ($attributes['class'] ?? ''));
        unset($attributes['class']);

        $title = $attributes['title'] ?? null;
        unset($attributes['title']);

        $merged = preg_replace_callback(
            '/<svg([^>]*)>/',
            static function (array $matches) use ($passedClass, $configClass, $attributes): string {
                $map = [];

                if (preg_match_all('/([\w:.-]+)="([^"]*)"/', $matches[1], $pairs, PREG_SET_ORDER)) {
                    foreach ($pairs as [, $key, $value]) {
                        $map[$key] = $value;
                    }
                }

                $class = trim(implode(' ', array_filter([
                    $map['class'] ?? '',
                    $configClass,
                    $passedClass,
                ])));

                if ($class !== '') {
                    $map['class'] = $class;
                } else {
                    unset($map['class']);
                }

                foreach ($attributes as $key => $value) {
                    $map[$key] = (string) $value;
                }

                $rebuilt = '';

                foreach ($map as $key => $value) {
                    $rebuilt .= ' '.$key.'="'.htmlspecialchars($value, ENT_QUOTES).'"';
                }

                return "<svg{$rebuilt}>";
            },
            $svg,
            1
        );

        if ($title !== null && $title !== '') {
            $merged = preg_replace(
                '/<svg[^>]*>/',
                '$0<title>'.htmlspecialchars((string) $title, ENT_QUOTES).'</title>',
                $merged ?? $svg,
                1
            );
            // Ensure role="img" alongside the title.
            $merged = preg_replace('/<svg/', '<svg role="img"', $merged ?? $svg, 1);
        }

        return $merged ?? $svg;
    }
}
