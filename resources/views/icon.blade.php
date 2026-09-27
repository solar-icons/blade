{{-- Dynamic icon: attributes are merged (not prepended) so the output
      carries a single, valid attribute set — file classes + config
      default + passed classes concatenated, passed values winning
      everywhere else. Resolution stays inside resources/svg (allowlisted
      weight, constrained name, ".svg" files only), so user input can
      never compile or execute code. --}}
{!! SolarIcons\Blade\SvgMerger::merge(
    app(BladeUI\Icons\Factory::class)->svg("solar-{$weight}-{$name}")->contents(),
    $attributes->getAttributes(),
    config('solar-icons-blade.class', ''),
    config('solar-icons-blade.attributes', []),
) !!}
