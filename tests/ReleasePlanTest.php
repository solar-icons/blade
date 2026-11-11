<?php

declare(strict_types=1);

namespace Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SolarIcons\Release\ReleasePlan;

require_once __DIR__.'/../bin/lib/ReleasePlan.php';

class ReleasePlanTest extends TestCase
{
    private const NPM = ['2.0.0-beta.0', '2.1.0', '2.3.3', '2.3.4', '2.4.0', '2.4.1', '2.5.0-beta.1'];

    public function test_nothing_new_means_no_release(): void
    {
        $this->assertNull(ReleasePlan::fromStaticVersions('2.4.1', self::NPM, 2));
    }

    public function test_a_declared_minor_gives_a_minor(): void
    {
        $plan = ReleasePlan::fromStaticVersions('2.3.4', ['2.3.4', '2.4.0'], 2);

        $this->assertSame('minor', $plan['bump']);
        $this->assertSame('2.4.0', $plan['latest']);
    }

    public function test_a_declared_patch_gives_a_patch(): void
    {
        $plan = ReleasePlan::fromStaticVersions('2.3.3', ['2.3.3', '2.3.4'], 2);

        $this->assertSame('patch', $plan['bump']);
    }

    public function test_the_highest_step_between_two_syncs_wins(): void
    {
        $plan = ReleasePlan::fromStaticVersions('2.3.4', self::NPM, 2);

        $this->assertSame('minor', $plan['bump']);
        $this->assertSame('2.4.1', $plan['latest']);
        $this->assertSame(['2.4.0', '2.4.1'], $plan['steps']);
    }

    public function test_prereleases_are_ignored(): void
    {
        $this->assertSame(['2.4.0'], ReleasePlan::versionsAfter('2.3.4', ['2.5.0-beta.1', '2.4.0']));
    }

    public function test_versions_are_ordered_numerically_not_lexically(): void
    {
        $this->assertSame(['2.9.0', '2.10.0'], ReleasePlan::versionsAfter('2.8.0', ['2.10.0', '2.9.0']));
    }

    public function test_a_major_step_upstream_stops_the_release(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('major');

        ReleasePlan::fromStaticVersions('2.4.1', ['3.0.0'], 2);
    }

    public function test_a_major_that_differs_from_blade_stops_the_release(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('share its major');

        ReleasePlan::fromStaticVersions('2.4.1', ['2.4.1', '2.5.0'], 3);
    }

    public function test_removals_cannot_ship_as_a_patch(): void
    {
        $this->expectException(RuntimeException::class);

        ReleasePlan::assertRemovalsAllowed('patch', 3);
    }

    public function test_removals_are_fine_in_a_minor_and_additions_in_a_patch(): void
    {
        ReleasePlan::assertRemovalsAllowed('minor', 3);
        ReleasePlan::assertRemovalsAllowed('patch', 0);

        $this->addToAssertionCount(2);
    }

    public function test_next_version_is_computed_from_blade_own_tag(): void
    {
        $this->assertSame('2.1.0', ReleasePlan::nextVersion('v2.0.3', 'minor'));
        $this->assertSame('2.0.4', ReleasePlan::nextVersion('v2.0.3', 'patch'));
        $this->assertSame('2.8.0', ReleasePlan::nextVersion('2.7.9', 'minor'));
    }

    public function test_next_version_rejects_unknown_input(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ReleasePlan::nextVersion('v2.0.3', 'major');
    }

    public function test_it_reads_blade_major_from_its_tag(): void
    {
        $this->assertSame(2, ReleasePlan::majorOf('v2.0.3'));
    }

    public function test_the_summary_keeps_the_maintainer_words_without_links_or_credit(): void
    {
        $summary = ReleasePlan::summaryFromStaticChangelog($this->staticChangelog(), ['2.4.0']);

        $this->assertStringStartsWith('Expand the catalogue to 1,479 icon families (8,874 SVGs) and deliver refined artwork:', $summary);
        $this->assertStringContainsString("\n- Add `cookie` family across all 6 styles", $summary);
        $this->assertStringContainsString('(closes [#631](https://github.com/saoudi-h/solar-icons/issues/631))', $summary);
        $this->assertStringNotContainsString('Thanks', $summary);
        $this->assertStringNotContainsString('54825778823d8de6c349c36d809f4d30f5874be1', $summary);
        $this->assertStringEndsWith('unified silhouettes.', $summary);
    }

    public function test_the_summary_covers_every_version_since_the_pin_in_order(): void
    {
        $summary = ReleasePlan::summaryFromStaticChangelog($this->staticChangelog(), ['2.3.2', '2.4.0']);

        $this->assertLessThan(
            strpos($summary, 'Expand the catalogue'),
            strpos($summary, 'Apply the latest Figma corrections'),
        );
    }

    public function test_dependency_only_bullets_are_dropped(): void
    {
        $summary = ReleasePlan::summaryFromStaticChangelog($this->staticChangelog(), ['2.3.2']);

        $this->assertSame('Apply the latest Figma corrections to the generated Solar Icons SVG catalogue.', $summary);
    }

    public function test_a_plain_bullet_without_links_is_kept(): void
    {
        $this->assertSame(
            'Plain entry without links or credit.',
            ReleasePlan::summaryFromStaticChangelog($this->staticChangelog(), ['2.3.1']),
        );
    }

    public function test_an_unknown_version_gives_an_empty_summary(): void
    {
        $this->assertSame('', ReleasePlan::summaryFromStaticChangelog($this->staticChangelog(), ['9.9.9']));
    }

    public function test_the_changelog_gets_a_new_section_and_unreleased_notes_move_into_it(): void
    {
        $before = "# Changelog\n\nAll notable changes to `solar-icons/blade` will be documented in this file.\n\n"
            ."## Unreleased\n\n- PHP-only fix.\n\n## v2.0.3 (old)\n\n## v2.0.2\n\n- Old.\n";

        $after = ReleasePlan::applyToChangelog($before, '2.1.0', "Built from static 2.4.0.\n\nExpand things.");

        $this->assertSame(
            "# Changelog\n\nAll notable changes to `solar-icons/blade` will be documented in this file.\n\n"
            ."## Unreleased\n\n## v2.1.0\n\nBuilt from static 2.4.0.\n\nExpand things.\n\n- PHP-only fix.\n\n"
            ."## v2.0.3 (old)\n\n## v2.0.2\n\n- Old.\n",
            $after,
        );
    }

    public function test_applying_the_same_version_twice_changes_nothing(): void
    {
        $before = "# Changelog\n\nAll notable changes to `solar-icons/blade` will be documented in this file.\n\n## Unreleased\n\n## v2.0.3\n";
        $once = ReleasePlan::applyToChangelog($before, '2.1.0', 'Body.');

        $this->assertSame($once, ReleasePlan::applyToChangelog($once, '2.1.0', 'Body.'));
    }

    public function test_it_adds_unreleased_when_the_changelog_has_none(): void
    {
        $before = "# Changelog\n\nAll notable changes to `solar-icons/blade` will be documented in this file.\n\n## v2.0.3\n";
        $after = ReleasePlan::applyToChangelog($before, '2.1.0', 'Body.');

        $this->assertStringContainsString("## Unreleased\n\n## v2.1.0\n\nBody.\n\n## v2.0.3", $after);
    }

    public function test_the_latest_release_is_read_back_for_the_tag_and_notes(): void
    {
        $changelog = "# Changelog\n\n## Unreleased\n\n## v2.1.0\n\nBody line.\n\n- Item.\n\n## v2.0.3\n\nOld.\n";

        $this->assertSame(
            ['version' => '2.1.0', 'body' => "Body line.\n\n- Item."],
            ReleasePlan::latestRelease($changelog),
        );
    }

    private function staticChangelog(): string
    {
        return (string) file_get_contents(__DIR__.'/fixtures/static-changelog.md');
    }
}
