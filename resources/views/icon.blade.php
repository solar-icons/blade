{{-- Dynamic icon: resolution stays inside resources/svg. The weight is
      allowlisted and the name constrained in the component constructor,
      and only ".svg" files are ever read — never Blade views, so user
      input can never compile or execute code. --}}
{!! app(BladeUI\Icons\Factory::class)->svg(
    "solar-{$weight}-{$name}",
    $attributes->get('class', ''),
    $attributes->except('class')->getAttributes(),
)->toHtml() !!}
