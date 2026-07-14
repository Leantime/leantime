@props([
    'segments' => [],
    'total' => null,
    'height' => '8px',
    'radius' => '4px',
    'showLabels' => false,
    'trackColor' => '#F0F1F3',
])

@php
    $computedTotal = $total ?? collect($segments)->sum('value');
    if ($computedTotal <= 0) $computedTotal = 1;
@endphp

<div
    class="tw-w-full tw-overflow-hidden tw-flex"
    style="height: {{ $height }}; border-radius: {{ $radius }}; background: {{ $trackColor }}"
>
    @foreach($segments as $seg)
        @php
            $pct = ($seg['value'] / $computedTotal) * 100;
            $overlayPct = isset($seg['overlay']) ? ($seg['overlay']['value'] / max($seg['value'], 1)) * 100 : 0;
        @endphp
        <div
            class="tw-relative tw-h-full tw-flex tw-items-center tw-justify-center"
            style="width: {{ $pct }}%; background: {{ $seg['color'] }}"
        >
            @if($showLabels && $pct > 12 && isset($seg['label']))
                <span class="tw-text-[10px] tw-font-bold tw-text-white tw-relative tw-z-10">
                    {{ $seg['label'] }}
                </span>
            @endif

            {{-- V2 overlay --}}
            @if(isset($seg['overlay']))
                <div
                    class="tw-absolute tw-bottom-0 tw-left-0 tw-w-full"
                    style="height: {{ min($overlayPct, 100) }}%; background: {{ $seg['overlay']['color'] }}"
                ></div>
            @endif
        </div>
    @endforeach
</div>
