<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solar Icons Blade Playground</title>
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body { font-family: ui-sans-serif, system-ui, sans-serif; margin: 0; padding: 24px; background: #f8fafc; color: #0f172a; }
        @media (prefers-color-scheme: dark) { body { background: #0f172a; color: #e2e8f0; } }
        h1 { font-size: 20px; margin: 0 0 4px; }
        p.sub { margin: 0 0 16px; color: #64748b; font-size: 14px; }
        form.controls { display: flex; flex-wrap: wrap; gap: 12px; align-items: end; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 8px; }
        @media (prefers-color-scheme: dark) { form.controls { background: #1e293b; border-color: #334155; } }
        label { display: flex; flex-direction: column; gap: 4px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
        input, select { font: inherit; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 8px; background: inherit; color: inherit; text-transform: none; }
        input[type="color"] { padding: 2px; width: 56px; height: 34px; }
        button { font: inherit; padding: 8px 16px; border-radius: 8px; border: 0; background: #2563eb; color: #fff; cursor: pointer; }
        .stats { font-size: 13px; color: #64748b; margin: 12px 2px; }
        .pager { display: flex; gap: 8px; margin: 0 2px 12px; font-size: 13px; }
        .pager a { color: #2563eb; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(128px, 1fr)); gap: 12px; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 8px 10px; display: flex; flex-direction: column; align-items: center; gap: 8px; }
        @media (prefers-color-scheme: dark) { .card { background: #1e293b; border-color: #334155; } }
        .card code { font-size: 10.5px; color: #64748b; word-break: break-all; text-align: center; }
        .dynamic-demo { display: flex; align-items: center; gap: 16px; background: #fff; border: 1px dashed #94a3b8; border-radius: 12px; padding: 16px; margin: 16px 0; }
        @media (prefers-color-scheme: dark) { .dynamic-demo { background: #1e293b; } }
        .dynamic-demo code { font-size: 12px; }
    </style>
</head>
<body>
    <h1>Solar Icons Blade Playground</h1>
    <p class="sub">Static components on the left, dynamic component below — same params, same output.</p>

    <form class="controls" method="get" action="/solar">
        <label>Style
            <select name="style">
                @foreach (SolarIcons\Blade\BladeServiceProvider::STYLES as $s)
                    <option value="{{ $s }}" @selected($style === $s)>{{ $s }}</option>
                @endforeach
            </select>
        </label>
        <label>Search
            <input type="search" name="search" value="{{ $search }}" placeholder="heart…">
        </label>
        <label>Size (px)
            <input type="number" name="size" value="{{ $size }}" min="16" max="96" step="4">
        </label>
        <label>Color
            <input type="color" name="color" value="{{ $color }}">
        </label>
        <label>Stroke width
            <input type="number" name="stroke" value="{{ $stroke }}" min="0.5" max="3" step="0.25">
        </label>
        <label>Duotone accent
            <input type="color" name="secondary" value="{{ $secondary }}">
        </label>
        <label>Duotone opacity
            <input type="number" name="opacity" value="{{ $opacity }}" min="0" max="1" step="0.05">
        </label>
        <button type="submit">Apply</button>
    </form>

    <div class="dynamic-demo">
        <x-solar-icon name="heart" :weight="$style" :width="$size" :height="$size" :style="$styleAttr" :stroke-width="$stroke" />
        <code>&lt;x-solar-icon name="heart" weight="{{ $style }}" … /&gt;</code>
    </div>

    <p class="stats">Showing {{ count($icons) }} of {{ $total }} icons ({{ $style }}) — page {{ $page }} of {{ $pages }}.</p>

    @if ($pages > 1)
        <p class="pager">
            @for ($p = 1; $p <= $pages; $p++)
                @if ($p === $page)
                    <strong>{{ $p }}</strong>
                @else
                    <a href="/solar?{{ http_build_query(array_merge(request()->query(), ['page' => $p])) }}">{{ $p }}</a>
                @endif
            @endfor
        </p>
    @endif

    <div class="grid">
        @foreach ($icons as $icon)
            <div class="card" title="<x-solar-{{ $style }}-{{ $icon }} />">
                <x-dynamic-component :component="'solar-'.$style.'-'.$icon" :width="$size" :height="$size" :style="$styleAttr" :stroke-width="$stroke" />
                <code>{{ $icon }}</code>
            </div>
        @endforeach
    </div>
</body>
</html>
