<?php

declare(strict_types=1);

namespace Tests;

use BladeUI\Icons\BladeIconsServiceProvider;
use BladeUI\Icons\Exceptions\SvgNotFound;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\ViewException;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase;
use SolarIcons\Blade\BladeServiceProvider;

class DynamicIconTest extends TestCase
{
    public function test_it_renders_an_icon_by_name_and_weight()
    {
        $result = Blade::render('<x-solar-icon name="heart" weight="bold" />');

        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('solar-heart-bold', $result);
    }

    public function test_it_defaults_to_linear()
    {
        $result = Blade::render('<x-solar-icon name="heart" />');

        $this->assertStringContainsString('solar-heart-linear', $result);
    }

    public function test_it_forwards_class_and_style()
    {
        $result = Blade::render('<x-solar-icon name="heart" weight="linear" class="w-6 h-6" style="color: #555" />');

        $this->assertStringContainsString('class="w-6 h-6"', $result);
        $this->assertStringContainsString('style="color: #555"', $result);
    }

    public function test_it_rejects_an_unknown_weight()
    {
        $this->assertComponentFailsWith(
            '<x-solar-icon name="heart" weight="lineair" />',
            InvalidArgumentException::class,
            'Unknown icon weight "lineair"'
        );
    }

    public function test_it_rejects_path_traversal_names()
    {
        $this->assertComponentFailsWith(
            '<x-solar-icon name="../../secret" weight="linear" />',
            InvalidArgumentException::class,
            'Invalid icon name'
        );
    }

    public function test_it_rejects_names_with_extensions()
    {
        $this->assertComponentFailsWith(
            '<x-solar-icon name="heart.blade.php" weight="linear" />',
            InvalidArgumentException::class,
            'Invalid icon name'
        );
    }

    public function test_it_fails_fast_on_unknown_icons()
    {
        $this->assertComponentFailsWith(
            '<x-solar-icon name="heurt" weight="linear" />',
            SvgNotFound::class,
            'linear-heurt'
        );
    }

    /**
     * Component errors surface wrapped in ViewException layers (component
     * view inside the outer string view) — walk the chain instead of
     * expecting the raw exception type.
     */
    private function assertComponentFailsWith(string $template, string $expected, string $messageFragment): void
    {
        try {
            Blade::render($template);
            $this->fail("Expected a {$expected} failure for: {$template}");
        } catch (ViewException $exception) {
            $previous = $exception;
            $found = false;

            while ($previous = $previous->getPrevious()) {
                if ($previous instanceof $expected && str_contains($previous->getMessage(), $messageFragment)) {
                    $found = true;
                    break;
                }
            }

            $this->assertTrue($found, "Expected a {$expected} containing \"{$messageFragment}\" in the exception chain.");
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
