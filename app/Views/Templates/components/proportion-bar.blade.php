@props([
    'segments' => [],
    'total' => 0,
    'height' => '8px',
    'radius' => '4px',
])

@php
    $resolvedTotal = $total > 0 ? $total : array_sum(array_column($segments, 'value'));
    if ($resolvedTotal <= 0) {
        $resolvedTotal = 1;
    }
@endphp

<div {{ $attributes->merge(['class' => 'proportion-bar']) }}
     style="display: flex; height: {{ $height }}; border-radius: {{ $radius }}; overflow: hidden; background: var(--neutral, #e5e7eb);"
     role="img"
     aria-label="Proportion bar">
    @foreach($segments as $seg)
        @php
            $pct = min(100, max(0, ($seg['value'] / $resolvedTotal) * 100));
            $color = $seg['color'] ?? 'var(--accent1)';
            $label = $seg['label'] ?? '';
        @endphp
        @if($pct > 0)
            <div style="width: {{ $pct }}%; background: {{ $color }}; position: relative;"
                 @if($label) data-tippy-content="{{ $label }}" @endif>
                @if(!empty($seg['overlay']) && ($seg['overlay']['value'] ?? 0) > 0)
                    @php
                        $overlayPct = min(100, max(0, ($seg['overlay']['value'] / $seg['value']) * 100));
                        $overlayColor = $seg['overlay']['color'] ?? 'rgba(0,0,0,0.15)';
                    @endphp
                    <div style="position: absolute; inset: 0; width: {{ $overlayPct }}%; background: {{ $overlayColor }};"></div>
                @endif
            </div>
        @endif
    @endforeach
</div>
