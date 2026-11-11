<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

class PlanReleaseCliTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir().'/plan-release-'.bin2hex(random_bytes(4));
        mkdir($this->dir);
        file_put_contents($this->dir.'/versions.json', json_encode(['2.3.4', '2.4.0', '2.5.0-beta.0']));
        copy(__DIR__.'/fixtures/static-changelog.md', $this->dir.'/static-changelog.md');
        file_put_contents(
            $this->dir.'/CHANGELOG.md',
            "# Changelog\n\nAll notable changes to `solar-icons/blade` will be documented in this file.\n\n## Unreleased\n\n## v2.0.3 (old)\n",
        );
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir.'/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->dir);
    }

    public function test_a_declared_minor_plans_a_minor_release_with_the_maintainer_words(): void
    {
        [$code, $out] = $this->plan(['--pinned=2.3.4', '--added=168', '--modified=754', '--write']);

        $this->assertSame(0, $code, $out);
        $this->assertStringContainsString("release=true\nbump=minor\nversion=2.1.0\nlatest=2.4.0\n", $out);

        $changelog = (string) file_get_contents($this->dir.'/CHANGELOG.md');
        $this->assertStringContainsString("## Unreleased\n\n## v2.1.0\n\nBuilt from @solar-icons/static 2.4.0 (+168 added, ~754 modified, -0 removed SVG files).", $changelog);
        $this->assertStringContainsString('Expand the catalogue to 1,479 icon families', $changelog);
        $this->assertStringContainsString('## v2.0.3 (old)', $changelog);
        $this->assertStringContainsString('Expand the catalogue', (string) file_get_contents($this->dir.'/release-notes.md'));
    }

    public function test_it_never_plans_beyond_the_downloaded_version(): void
    {
        file_put_contents($this->dir.'/versions.json', json_encode(['2.3.4', '2.4.0', '2.4.1']));

        [$code, $out] = $this->plan(['--pinned=2.3.4', '--upto=2.4.0']);

        $this->assertSame(0, $code, $out);
        $this->assertStringContainsString("bump=minor\nversion=2.1.0\nlatest=2.4.0\n", $out);
    }

    public function test_nothing_new_upstream_plans_no_release_and_touches_nothing(): void
    {
        $before = (string) file_get_contents($this->dir.'/CHANGELOG.md');
        [$code, $out] = $this->plan(['--pinned=2.4.0', '--write']);

        $this->assertSame(0, $code);
        $this->assertSame("release=false\n", $out);
        $this->assertSame($before, (string) file_get_contents($this->dir.'/CHANGELOG.md'));
    }

    public function test_a_manual_release_needs_a_summary(): void
    {
        [$code, $out] = $this->plan(['--pinned=2.4.0', '--bump=patch']);

        $this->assertSame(1, $code);
        $this->assertStringContainsString('needs a summary', $out);
    }

    public function test_a_manual_release_overrides_the_derived_bump(): void
    {
        [$code, $out] = $this->plan(['--pinned=2.3.4', '--bump=patch', '--summary=Hotfix.']);

        $this->assertSame(0, $code, $out);
        $this->assertStringContainsString("bump=patch\nversion=2.0.4\n", $out);
    }

    public function test_a_manual_release_is_the_way_out_of_a_major_mismatch(): void
    {
        file_put_contents($this->dir.'/versions.json', json_encode(['2.4.0', '3.0.0']));

        [$blocked] = $this->plan(['--pinned=2.4.0']);
        [$code, $out] = $this->plan(['--pinned=2.4.0', '--bump=minor', '--summary=PHP work.']);

        $this->assertSame(1, $blocked);
        $this->assertSame(0, $code, $out);
        $this->assertStringContainsString('version=2.1.0', $out);
    }

    public function test_removed_icons_cannot_ship_as_a_derived_patch(): void
    {
        file_put_contents($this->dir.'/versions.json', json_encode(['2.4.0', '2.4.1']));

        [$code, $out] = $this->plan(['--pinned=2.4.0', '--deleted=2']);

        $this->assertSame(1, $code);
        $this->assertStringContainsString('removed', $out);
    }

    public function test_it_reads_the_newest_release_for_the_tag_workflow(): void
    {
        file_put_contents($this->dir.'/CHANGELOG.md', "# Changelog\n\n## Unreleased\n\n## v2.1.0\n\nBody.\n\n## v2.0.3\n");

        $out = $this->exec(['--latest-release', '--blade-changelog='.$this->dir.'/CHANGELOG.md', '--notes-file='.$this->dir.'/notes.md']);

        $this->assertSame("version=2.1.0\n", $out[1]);
        $this->assertSame("Body.\n", file_get_contents($this->dir.'/notes.md'));
    }

    /** @param  list<string>  $args */
    private function plan(array $args): array
    {
        return $this->exec(array_merge([
            '--last-tag=v2.0.3',
            '--versions-file='.$this->dir.'/versions.json',
            '--changelog-file='.$this->dir.'/static-changelog.md',
            '--blade-changelog='.$this->dir.'/CHANGELOG.md',
            '--notes-file='.$this->dir.'/release-notes.md',
        ], $args));
    }

    /** @param  list<string>  $args */
    private function exec(array $args): array
    {
        $command = escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/../bin/plan-release.php')
            .' '.implode(' ', array_map('escapeshellarg', $args)).' 2>&1';
        exec($command, $lines, $code);

        return [$code, implode("\n", $lines).($lines === [] ? '' : "\n")];
    }
}
