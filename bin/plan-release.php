#!/usr/bin/env php
<?php

/**
 * Plan a Blade release from the intent declared in @solar-icons/static.
 *
 * Plan mode (default): decide whether to release, which bump and which
 * version, and optionally write CHANGELOG.md and the release notes.
 * Emits KEY=VALUE lines for GitHub Actions outputs.
 *
 *   php bin/plan-release.php --pinned=2.3.4 [--deleted=0 --added=0 --modified=0] --write
 *   php bin/plan-release.php --bump=patch --summary="PHP-only fix" --write   (explicit override)
 *
 * Notes mode: read the newest released section of CHANGELOG.md.
 *
 *   php bin/plan-release.php --latest-release --notes-file=release-notes.md
 *
 * Inputs normally come from npm, GitHub and git; every one can be given as
 * a file or value for tests: --versions-file, --changelog-file, --last-tag.
 */

declare(strict_types=1);

use SolarIcons\Release\ReleasePlan;

require_once __DIR__.'/lib/ReleasePlan.php';

const STATIC_PACKAGE = '@solar-icons/static';
const STATIC_REPOSITORY = 'saoudi-h/solar-icons';

$root = dirname(__DIR__);
$options = getopt('', [
    'pinned:', 'upto:', 'bump:', 'summary:', 'deleted:', 'added:', 'modified:',
    'versions-file:', 'changelog-file:', 'last-tag:',
    'blade-changelog:', 'notes-file:', 'write', 'latest-release',
]);
$changelogPath = $options['blade-changelog'] ?? $root.'/CHANGELOG.md';
$notesPath = $options['notes-file'] ?? $root.'/release-notes.md';

function fail(string $message): never
{
    fwrite(STDERR, "::error::{$message}\n");
    exit(1);
}

function run(string $command): string
{
    return trim((string) shell_exec($command.' 2>/dev/null'));
}

function staticVersions(?string $file, ?string $upto = null): array
{
    $raw = $file !== null ? (string) file_get_contents($file) : run('npm view '.STATIC_PACKAGE.' versions --json');
    $decoded = json_decode($raw, true);
    if (is_string($decoded)) {
        return [$decoded];
    }
    if (! is_array($decoded)) {
        fail('Could not read the published versions of '.STATIC_PACKAGE.' from npm.');
    }

    $versions = array_values(array_filter($decoded, 'is_string'));

    // Never plan beyond the version whose catalogue was actually downloaded.
    return $upto === null || $upto === ''
        ? $versions
        : array_values(array_filter($versions, static fn (string $v): bool => version_compare($v, $upto, '<=')));
}

function staticChangelog(?string $file, string $version): string
{
    if ($file !== null) {
        return (string) file_get_contents($file);
    }

    $url = sprintf(
        'https://raw.githubusercontent.com/%s/%s/packages/static/CHANGELOG.md',
        STATIC_REPOSITORY,
        rawurlencode(STATIC_PACKAGE.'@'.$version),
    );
    $context = stream_context_create(['http' => ['timeout' => 20]]);
    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $body = @file_get_contents($url, false, $context);
        if (is_string($body) && $body !== '') {
            return $body;
        }
        sleep(15);
    }
    fail('Could not read the changelog of '.STATIC_PACKAGE."@{$version} ({$url}).");
}

if (isset($options['latest-release'])) {
    $release = ReleasePlan::latestRelease((string) file_get_contents($changelogPath));
    file_put_contents($notesPath, $release['body']."\n");
    echo "version={$release['version']}\n";
    exit(0);
}

try {
    $pinned = $options['pinned'] ?? trim((string) file_get_contents($root.'/.static-version'));
    $lastTag = $options['last-tag'] ?? run('git describe --tags --abbrev=0');
    if ($lastTag === '') {
        fail('No release tag found: refusing to guess a first version.');
    }
    $bladeMajor = ReleasePlan::majorOf($lastTag);
    $deleted = (int) ($options['deleted'] ?? 0);
    $added = (int) ($options['added'] ?? 0);
    $modified = (int) ($options['modified'] ?? 0);

    $manualBump = $options['bump'] ?? 'none';
    $manualSummary = trim($options['summary'] ?? '');
    try {
        $derived = ReleasePlan::fromStaticVersions($pinned, staticVersions($options['versions-file'] ?? null, $options['upto'] ?? null), $bladeMajor);
    } catch (RuntimeException $e) {
        // A manual release is the way out of a blocked derivation.
        if ($manualBump === 'none') {
            throw $e;
        }
        $derived = null;
    }

    if ($manualBump !== 'none') {
        // An explicit override is the maintainer's own declaration: it wins.
        if ($manualSummary === '') {
            fail('A manual release needs a summary: say what changed.');
        }
        $bump = $manualBump;
        $summary = $manualSummary;
    } elseif ($derived === null) {
        echo "release=false\n";
        exit(0);
    } else {
        $bump = $derived['bump'];
        ReleasePlan::assertRemovalsAllowed($bump, $deleted);
        $summary = ReleasePlan::summaryFromStaticChangelog(
            staticChangelog($options['changelog-file'] ?? null, $derived['latest']),
            $derived['steps'],
        );
    }

    $version = ReleasePlan::nextVersion($lastTag, $bump);

    $body = '';
    if ($derived !== null) {
        $body = sprintf(
            'Built from %s %s (+%d added, ~%d modified, -%d removed SVG files).',
            STATIC_PACKAGE,
            $derived['latest'],
            $added,
            $modified,
            $deleted,
        )."\n\n";
    }
    $body .= $summary !== '' ? $summary : 'No upstream release notes.';
} catch (Throwable $e) {
    fail($e->getMessage());
}

if (isset($options['write'])) {
    file_put_contents($changelogPath, ReleasePlan::applyToChangelog((string) file_get_contents($changelogPath), $version, $body));
    file_put_contents($notesPath, $body."\n");
}

echo "release=true\n";
echo "bump={$bump}\n";
echo "version={$version}\n";
echo 'latest='.($derived['latest'] ?? $pinned)."\n";
