<?php

declare(strict_types=1);

namespace SolarIcons\Release;

use InvalidArgumentException;
use RuntimeException;

/**
 * Derives a Blade release from the intent already declared upstream.
 *
 * The maintainer declares the intent once, in the changeset of
 * `@solar-icons/static`: the version jump (patch/minor) and the changelog
 * text. Blade never guesses from file diffs; it reads that declaration.
 * Version numbers stay independent per package, only the major must match.
 */
final class ReleasePlan
{
    public const PATCH = 'patch';

    public const MINOR = 'minor';

    private const RANK = ['patch' => 1, 'minor' => 2, 'major' => 3];

    private const STABLE = '/^\d+\.\d+\.\d+$/';

    /**
     * Stable versions strictly newer than $pinned, ascending. Prereleases
     * (2.0.0-beta.0) are never released.
     *
     * @param  list<string>  $versions
     * @return list<string>
     */
    public static function versionsAfter(string $pinned, array $versions): array
    {
        $newer = array_values(array_filter(
            $versions,
            static fn (string $v): bool => preg_match(self::STABLE, $v) === 1
                && version_compare($v, $pinned, '>'),
        ));
        usort($newer, static fn (string $a, string $b): int => version_compare($a, $b));

        return $newer;
    }

    /** 'major' | 'minor' | 'patch' for a single step between two versions. */
    public static function kindBetween(string $from, string $to): string
    {
        [$fromMajor, $fromMinor] = array_map('intval', explode('.', $from));
        [$toMajor, $toMinor] = array_map('intval', explode('.', $to));

        return match (true) {
            $toMajor !== $fromMajor => 'major',
            $toMinor !== $fromMinor => 'minor',
            default => 'patch',
        };
    }

    /**
     * The release implied by the static versions published since the pin:
     * the highest step wins (2.3.4 -> 2.4.0 -> 2.4.1 is a minor).
     *
     * @param  list<string>  $versions
     * @return array{latest: string, steps: list<string>, bump: string}|null null when nothing new
     */
    public static function fromStaticVersions(string $pinned, array $versions, int $bladeMajor): ?array
    {
        $newer = self::versionsAfter($pinned, $versions);
        if ($newer === []) {
            return null;
        }

        $bump = self::PATCH;
        $previous = $pinned;
        foreach ($newer as $version) {
            $kind = self::kindBetween($previous, $version);
            if ($kind === 'major') {
                throw new RuntimeException(sprintf(
                    '@solar-icons/static went from %s to %s: a major change is not a Blade release. Decide it by hand (v%d family rule).',
                    $previous,
                    $version,
                    $bladeMajor,
                ));
            }
            if (self::RANK[$kind] > self::RANK[$bump]) {
                $bump = $kind;
            }
            $previous = $version;
        }

        $latest = $newer[array_key_last($newer)];
        $latestMajor = (int) explode('.', $latest)[0];
        if ($latestMajor !== $bladeMajor) {
            throw new RuntimeException(sprintf(
                '@solar-icons/static %s has major %d but Blade is on major %d: the family must share its major.',
                $latest,
                $latestMajor,
                $bladeMajor,
            ));
        }

        return ['latest' => $latest, 'steps' => $newer, 'bump' => $bump];
    }

    /** Removed icons are a breaking change: refuse to ship them as a patch. */
    public static function assertRemovalsAllowed(string $bump, int $deleted): void
    {
        if ($deleted > 0 && $bump === self::PATCH) {
            throw new RuntimeException(sprintf(
                '%d icon file(s) were removed but the declared bump is a patch. Declare a minor upstream, or release by hand.',
                $deleted,
            ));
        }
    }

    public static function nextVersion(string $lastTag, string $bump): string
    {
        if (! in_array($bump, [self::PATCH, self::MINOR], true)) {
            throw new InvalidArgumentException("Unsupported bump '{$bump}' (patch or minor).");
        }
        if (preg_match('/^v?(\d+)\.(\d+)\.(\d+)$/', $lastTag, $m) !== 1) {
            throw new InvalidArgumentException("Cannot read a version from tag '{$lastTag}'.");
        }
        [, $major, $minor, $patch] = array_map('intval', $m);

        return $bump === self::MINOR
            ? sprintf('%d.%d.0', $major, $minor + 1)
            : sprintf('%d.%d.%d', $major, $minor, $patch + 1);
    }

    public static function majorOf(string $tag): int
    {
        if (preg_match('/^v?(\d+)\./', $tag, $m) !== 1) {
            throw new InvalidArgumentException("Cannot read a major from tag '{$tag}'.");
        }

        return (int) $m[1];
    }

    /**
     * The maintainer's own words for the given static versions, taken from a
     * Changesets-generated changelog. Commit/PR links and the "Thanks" credit
     * are stripped; the declared text is kept verbatim, nested lists included.
     *
     * @param  list<string>  $versions
     */
    public static function summaryFromStaticChangelog(string $changelog, array $versions): string
    {
        $wanted = array_flip($versions);
        $bullets = [];
        $current = null;
        $inBullet = false;
        $buffer = [];

        $flush = static function () use (&$buffer, &$bullets, &$inBullet): void {
            if ($inBullet && $buffer !== []) {
                $text = trim(implode("\n", $buffer));
                if ($text !== '' && ! str_starts_with($text, 'Updated dependencies')) {
                    $bullets[] = $text;
                }
            }
            $buffer = [];
            $inBullet = false;
        };

        $byVersion = [];
        foreach (preg_split('/\R/', $changelog) ?: [] as $line) {
            if (preg_match('/^## (\S+)\s*$/', $line, $m) === 1) {
                $flush();
                if ($current !== null && $bullets !== []) {
                    $byVersion[$current] = $bullets;
                }
                $bullets = [];
                $current = isset($wanted[$m[1]]) ? $m[1] : null;

                continue;
            }
            if ($current === null) {
                continue;
            }
            if (str_starts_with($line, '#')) {
                $flush();

                continue;
            }
            if (str_starts_with($line, '- ')) {
                $flush();
                $inBullet = true;
                $buffer[] = self::stripCredit(substr($line, 2));

                continue;
            }
            if ($inBullet) {
                $buffer[] = str_starts_with($line, '  ') ? substr($line, 2) : $line;
            }
        }
        $flush();
        if ($current !== null && $bullets !== []) {
            $byVersion[$current] = $bullets;
        }

        $ordered = [];
        foreach ($versions as $version) {
            foreach ($byVersion[$version] ?? [] as $bullet) {
                $ordered[] = $bullet;
            }
        }

        return implode("\n\n", $ordered);
    }

    private static function stripCredit(string $text): string
    {
        return (string) preg_replace(
            '/^(?:\[#\d+\]\([^)]*\) )?(?:\[`[0-9a-f]{7,}`\]\([^)]*\) )?(?:Thanks \[@[^\]]+\]\([^)]*\)! )?(?:- )?/',
            '',
            $text,
        );
    }

    /**
     * Writes a release section into Blade's CHANGELOG.md. Whatever was
     * accumulated under "## Unreleased" moves into the new section, and
     * "## Unreleased" starts empty again. Idempotent.
     */
    public static function applyToChangelog(string $changelog, string $version, string $body): string
    {
        if (str_contains($changelog, "## v{$version}\n")) {
            return $changelog;
        }

        $notes = '';
        $section = '';
        if (preg_match('/^## Unreleased[^\n]*\n(?P<body>.*?)(?=^## |\z)/ms', $changelog, $m, PREG_OFFSET_CAPTURE) === 1) {
            $notes = trim($m['body'][0], "\n");
            $start = $m[0][1];
            $end = $start + strlen($m[0][0]);
        } else {
            $marker = 'will be documented in this file.';
            $pos = strpos($changelog, $marker);
            if ($pos === false) {
                throw new RuntimeException('CHANGELOG.md has neither "## Unreleased" nor the introduction line.');
            }
            $start = $end = $pos + strlen($marker) + 1;
        }

        $section = "## Unreleased\n\n## v{$version}\n\n".trim($body, "\n")."\n";
        if ($notes !== '') {
            $section .= "\n{$notes}\n";
        }

        return substr($changelog, 0, $start).$section."\n".ltrim(substr($changelog, $end), "\n");
    }

    /**
     * The newest released section of Blade's CHANGELOG.md.
     *
     * @return array{version: string, body: string}
     */
    public static function latestRelease(string $changelog): array
    {
        if (preg_match('/^## v(\d+\.\d+\.\d+)[^\n]*\n(?P<body>.*?)(?=^## |\z)/ms', $changelog, $m) !== 1) {
            throw new RuntimeException('No released section found in CHANGELOG.md.');
        }

        return ['version' => $m[1], 'body' => trim($m['body'], "\n")];
    }
}
