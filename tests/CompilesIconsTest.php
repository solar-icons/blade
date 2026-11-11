<?php

declare(strict_types=1);

namespace Tests;

use BladeUI\Icons\BladeIconsServiceProvider;
use Illuminate\Support\Facades\Blade;
use Orchestra\Testbench\TestCase;
use SolarIcons\Blade\BladeServiceProvider;

class CompilesIconsTest extends TestCase
{
    public function test_it_compiles_a_single_anonymous_component()
    {
        $result = svg('solar-linear-heart')->toHtml();

        $rawSvg = trim(file_get_contents(__DIR__.'/../resources/svg/linear-heart.svg'));
        $expected = preg_replace('/^<svg /', '<svg stroke-width="1.5" ', $rawSvg);

        $this->assertSame($expected, $result);
    }

    public function test_it_can_add_classes_to_icons()
    {
        $result = svg('solar-linear-heart', 'w-6 h-6 text-gray-500')->toHtml();

        $rawSvg = trim(file_get_contents(__DIR__.'/../resources/svg/linear-heart.svg'));
        $expected = preg_replace('/^<svg /', '<svg stroke-width="1.5" class="w-6 h-6 text-gray-500" ', $rawSvg);

        $this->assertSame($expected, $result);
    }

    public function test_it_can_add_styles_to_icons()
    {
        $result = svg('solar-linear-heart', ['style' => 'color: #555'])->toHtml();

        $rawSvg = trim(file_get_contents(__DIR__.'/../resources/svg/linear-heart.svg'));
        $expected = preg_replace('/^<svg /', '<svg stroke-width="1.5" style="color: #555" ', $rawSvg);

        $this->assertSame($expected, $result);
    }

    public function test_a_static_component_override_does_not_duplicate_attributes()
    {
        $result = Blade::render('<x-solar-linear-heart stroke-width="2" class="w-6 h-6" />');

        // Blade Icons prepends passed attributes; the shipped files carry no
        // stroke-width (see config/generation.php), so the override replaces
        // the config default instead of doubling the attribute.
        $this->assertSame(1, substr_count($result, 'stroke-width="'));
        $this->assertStringContainsString('stroke-width="2"', $result);
    }

    public function test_it_compiles_every_style()
    {
        $outputs = [];

        foreach (BladeServiceProvider::STYLES as $style) {
            $result = svg("solar-{$style}-heart")->toHtml();

            $this->assertStringContainsString('<svg', $result);
            $outputs[$style] = $result;
        }

        // Each style resolves to its own file with distinct artwork.
        $this->assertCount(6, array_unique($outputs));
    }

    protected function getPackageProviders($app)
    {
        return [
            BladeIconsServiceProvider::class,
            BladeServiceProvider::class,
        ];
    }
}
