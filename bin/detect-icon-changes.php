#!/usr/bin/env php
<?php

/**
 * Detect icon changes in resources/svg/ from git status and recommend a
 * version bump. Emits KEY=VALUE lines for GitHub Actions outputs.
 *
 * - deleted files  → minor (breaking: icons removed)
 * - added/modified → patch
 * - no changes      → none
 */

declare(strict_types=1);

exec('git status --porcelain -- resources/svg/', $lines);

$added = 0;
$deleted = 0;
$modified = 0;

foreach ($lines as $line) {
    $status = substr(trim($line), 0, 1);

    match ($status) {
        'D' => $deleted++,
        'A', '?' => $added++,
        default => $modified++,
    };
}

$bump = 'none';

if ($deleted > 0) {
    $bump = 'minor';
} elseif ($added > 0 || $modified > 0) {
    $bump = 'patch';
}

echo "bump={$bump}\n";
echo "added={$added}\n";
echo "deleted={$deleted}\n";
echo "modified={$modified}\n";
