<?php

declare(strict_types=1);

namespace Tests;

use BladeUI\Icons\BladeIconsServiceProvider;
use Orchestra\Testbench\TestCase;
use SolarIcons\Blade\BladeServiceProvider;

class CompilesIconsTest extends TestCase
{
    public function test_it_compiles_a_single_anonymous_component()
    {
        $result = svg('solar-linear-heart')->toHtml();

        // Note: the empty class here seems to be a Blade components bug.
        $expected = <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.5" class="solar solar-heart-linear"><path d="M2 9.1371C2 14 6.01943 16.5914 8.96173 18.9109C10 19.7294 11 20.5 12 20.5C13 20.5 14 19.7294 15.0383 18.9109C17.9806 16.5914 22 14 22 9.1371C22 4.27416 16.4998 0.825464 12 5.50063C7.50016 0.825464 2 4.27416 2 9.1371Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>
            SVG;

        $this->assertSame($expected, $result);
    }

    public function test_it_can_add_classes_to_icons()
    {
        $result = svg('solar-linear-heart', 'w-6 h-6 text-gray-500')->toHtml();

        $expected = <<<'SVG'
            <svg class="w-6 h-6 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.5" class="solar solar-heart-linear"><path d="M2 9.1371C2 14 6.01943 16.5914 8.96173 18.9109C10 19.7294 11 20.5 12 20.5C13 20.5 14 19.7294 15.0383 18.9109C17.9806 16.5914 22 14 22 9.1371C22 4.27416 16.4998 0.825464 12 5.50063C7.50016 0.825464 2 4.27416 2 9.1371Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>
            SVG;

        $this->assertSame($expected, $result);
    }

    public function test_it_can_add_styles_to_icons()
    {
        $result = svg('solar-linear-heart', ['style' => 'color: #555'])->toHtml();

        $expected = <<<'SVG'
            <svg style="color: #555" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.5" class="solar solar-heart-linear"><path d="M2 9.1371C2 14 6.01943 16.5914 8.96173 18.9109C10 19.7294 11 20.5 12 20.5C13 20.5 14 19.7294 15.0383 18.9109C17.9806 16.5914 22 14 22 9.1371C22 4.27416 16.4998 0.825464 12 5.50063C7.50016 0.825464 2 4.27416 2 9.1371Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>
            SVG;

        $this->assertSame($expected, $result);
    }

    public function test_it_compiles_every_style()
    {
        foreach (BladeServiceProvider::STYLES as $style) {
            $result = svg("solar-{$style}-heart")->toHtml();

            $this->assertStringContainsString('<svg', $result);
            $this->assertStringContainsString("solar-heart-{$style}", $result);
        }
    }

    protected function getPackageProviders($app)
    {
        return [
            BladeIconsServiceProvider::class,
            BladeServiceProvider::class,
        ];
    }
}
