<?php

declare(strict_types=1);

namespace Tests;

use Orchestra\Testbench\TestCase;
use SolarIcons\Blade\BladeServiceProvider;
use SolarIcons\Blade\SolarIcon;

class SolarIconEnumTest extends TestCase
{
    public function test_it_exposes_every_canonical_icon_in_every_style()
    {
        $this->assertCount(8706, SolarIcon::cases());
        $this->assertSame('solar-linear-heart', SolarIcon::LinearHeart->value);
        $this->assertSame('solar-bold-duotone-heart', SolarIcon::BoldDuotoneHeart->value);
    }

    public function test_it_parses_weight_and_icon_back()
    {
        $this->assertSame('line-duotone', SolarIcon::LineDuotoneHeart->weight());
        $this->assertSame('heart', SolarIcon::LineDuotoneHeart->icon());
        $this->assertSame('linear', SolarIcon::LinearHeart->weight());
    }

    public function test_every_case_resolves_to_a_shipped_svg()
    {
        $missing = [];

        foreach (SolarIcon::cases() as $case) {
            $file = substr($case->value, strlen('solar-'));
            $path = __DIR__.'/../resources/svg/'.$file.'.svg';

            if (! is_file($path)) {
                $missing[] = $case->value;
            }
        }

        $this->assertSame([], array_slice($missing, 0, 10));
    }

    public function test_it_renders_through_the_svg_helper()
    {
        $result = svg(SolarIcon::LinearHeart->value)->toHtml();

        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('<path', $result);
    }

    protected function getPackageProviders($app)
    {
        return [
            \BladeUI\Icons\BladeIconsServiceProvider::class,
            BladeServiceProvider::class,
        ];
    }
}
