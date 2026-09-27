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
        // Bold artwork is filled, linear is stroked — proves the weight resolved.
        $this->assertStringContainsString('fill="currentColor"', $result);
    }

    public function test_it_defaults_to_linear()
    {
        $result = Blade::render('<x-solar-icon name="heart" />');

        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('stroke-linecap="round"', $result);
    }

    public function test_it_forwards_class_and_style()
    {
        $result = Blade::render('<x-solar-icon name="heart" weight="linear" class="w-6 h-6" style="color: #555" />');

        $this->assertStringContainsString('style="color: #555"', $result);
    }

    public function test_it_merges_classes_into_a_single_attribute()
    {
        $result = Blade::render('<x-solar-icon name="heart" weight="linear" class="demo-red" />');

        $this->assertSame(1, substr_count($result, 'class="'));
        $this->assertStringContainsString('class="demo-red"', $result);
    }

    public function test_it_keeps_a_single_stroke_width_with_the_passed_value()
    {
        $result = Blade::render('<x-solar-icon name="heart" weight="linear" stroke-width="2.5" />');

        $this->assertSame(1, substr_count($result, 'stroke-width="'));
        $this->assertStringContainsString('stroke-width="2.5"', $result);
    }

    public function test_it_merges_config_default_classes()
    {
        config()->set('solar-icons-blade.class', 'icon-default');

        $result = Blade::render('<x-solar-icon name="heart" weight="linear" class="demo-red" />');

        $this->assertSame(1, substr_count($result, 'class="'));
        $this->assertStringContainsString('class="icon-default demo-red"', $result);
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
