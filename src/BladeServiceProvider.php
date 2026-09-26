<?php

declare(strict_types=1);

namespace SolarIcons\Blade;

use BladeUI\Icons\Factory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

final class BladeServiceProvider extends ServiceProvider
{
    /**
     * Icon styles shipped by this package, mirroring the
     * `@solar-icons/*` npm subpaths. Files are stored as
     * `{style}-{icon}.svg` in a single set (the blade-icons
     * `svg()` helper only resolves single-segment prefixes,
     * so one `solar` set with style-prefixed files covers both
     * `<x-solar-linear-heart />` and `svg('solar-linear-heart')`).
     *
     * @var string[]
     */
    public const STYLES = [
        'linear',
        'bold',
        'broken',
        'outline',
        'bold-duotone',
        'line-duotone',
    ];

    public function register(): void
    {
        $this->registerConfig();

        $this->callAfterResolving(Factory::class, function (Factory $factory, Container $container) {
            $config = $container->make('config')->get('solar-icons-blade', []);

            $factory->add('solar', array_merge(['path' => __DIR__.'/../resources/svg'], $config));
        });
    }

    private function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/solar-icons-blade.php', 'solar-icons-blade');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/svg' => public_path('vendor/solar-icons-blade'),
            ], 'solar-icons-blade');

            $this->publishes([
                __DIR__.'/../config/solar-icons-blade.php' => $this->app->configPath('solar-icons-blade.php'),
            ], 'solar-icons-blade-config');
        }
    }
}
