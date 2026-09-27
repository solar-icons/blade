<?php

declare(strict_types=1);

namespace Tests;

use Orchestra\Testbench\TestCase;
use SolarIcons\Blade\BladeServiceProvider;

class IconCatalogTest extends TestCase
{
    private string $svgDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->svgDir = __DIR__.'/../resources/svg';
    }

    /** @return array<string, string[]> style => filenames */
    private function filesByStyle(): array
    {
        // Longest prefixes first: "bold-" would also match "bold-duotone-*".
        $ordered = ['bold-duotone', 'line-duotone', 'linear', 'bold', 'broken', 'outline'];
        $byStyle = [];

        foreach (BladeServiceProvider::STYLES as $style) {
            $byStyle[$style] = [];
        }

        foreach (glob($this->svgDir.'/*.svg') as $path) {
            $file = basename($path);

            foreach ($ordered as $style) {
                if (str_starts_with($file, "{$style}-")) {
                    $byStyle[$style][] = $file;
                    break;
                }
            }
        }

        return $byStyle;
    }

    public function test_every_style_ships_the_same_icon_count()
    {
        $byStyle = $this->filesByStyle();
        $counts = array_map('count', $byStyle);

        $this->assertNotEmpty($byStyle['linear']);
        $this->assertCount(1, array_unique($counts), 'Style file counts diverged: '.json_encode($counts));
        $this->assertGreaterThanOrEqual(8706, array_sum($counts));
    }

    public function test_svgs_have_no_root_dimensions_and_no_hardcoded_hex()
    {
        $badDimensions = [];
        $badHex = [];

        foreach (glob($this->svgDir.'/*.svg') as $path) {
            $contents = file_get_contents($path);

            if (preg_match('/<svg[^>]*\s(width|height)=/', $contents)) {
                $badDimensions[] = basename($path);
            }

            if (preg_match('/#1c274c/i', $contents)) {
                $badHex[] = basename($path);
            }
        }

        $this->assertSame([], array_slice($badDimensions, 0, 10), 'SVGs with root width/height found');
        $this->assertSame([], array_slice($badHex, 0, 10), 'SVGs with hardcoded hex colors found');
    }

    public function test_no_file_carries_a_class_attribute()
    {
        // Shipped files intentionally carry no class: a passed class would
        // otherwise duplicate it and browsers would keep only the first,
        // making `.solar-*` hooks unreliable. Target all icons through
        // the `class` config option instead.
        $withClass = [];

        foreach (glob($this->svgDir.'/*.svg') as $path) {
            if (preg_match('/<svg[^>]*\sclass=/', (string) file_get_contents($path))) {
                $withClass[] = basename($path);
            }
        }

        $this->assertSame([], array_slice($withClass, 0, 10), 'SVGs with a class attribute found');
    }
}
