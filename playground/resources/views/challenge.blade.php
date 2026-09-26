<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solar Icons Blade — Attribute Priority Challenge</title>
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 0; padding: 24px; background: #f8fafc; color: #0f172a; max-width: 960px; }
        @media (prefers-color-scheme: dark) { body { background: #0f172a; color: #e2e8f0; } }
        h1 { font-size: 20px; margin: 0 0 4px; }
        p.sub { margin: 0 0 16px; color: #64748b; font-size: 14px; }
        section { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 12px; }
        @media (prefers-color-scheme: dark) { section { background: #1e293b; border-color: #334155; } }
        h2 { font-size: 15px; margin: 0 0 8px; }
        .render { font-size: 32px; margin: 8px 0; }
        .render svg { width: 48px; height: 48px; }
        .expected { font-size: 13px; background: #f1f5f9; border-radius: 8px; padding: 8px 12px; margin: 8px 0; }
        @media (prefers-color-scheme: dark) { .expected { background: #0f172a; } }
        pre { font-size: 11px; overflow-x: auto; background: #0f172a; color: #e2e8f0; border-radius: 8px; padding: 12px; white-space: pre-wrap; word-break: break-all; }
        a { color: #2563eb; font-size: 13px; }
    </style>
</head>
<body>
    <h1>Attribute priority challenge</h1>
    <p class="sub">Eyeball page: for each scenario, check the rendered icon against the expected rule, then inspect the raw HTML. <a href="/solar">← back to grid</a></p>

    @foreach ($scenarios as $i => $scenario)
        <section>
            <h2>{{ $i + 1 }}. {{ $scenario['title'] }}</h2>
            <div class="render">{!! $scenario['html'] !!}</div>
            <p class="expected"><strong>Expected:</strong> {{ $scenario['expected'] }}</p>
            <pre>{{ $scenario['html'] }}</pre>
        </section>
    @endforeach
</body>
</html>
