{{--
    Row of summary stat tiles with optional period-over-period deltas.

    Expects:
    $tiles: array of [
        'label' => string,
        'value' => string|int|float,
        'tone'  => 'default'|'danger' (danger colors the value red when > 0),
        'delta' => null|['value' => float, 'goodWhenUp' => bool|null, 'vs' => string],
    ]
--}}
<div class="reportStatTiles">
    @foreach ($tiles as $tile)
        @php
            $isDanger = ($tile['tone'] ?? 'default') === 'danger' && (float) $tile['value'] > 0;
            $delta = $tile['delta'] ?? null;
            $deltaClass = '';
            if ($delta !== null && $delta['value'] != 0 && ($delta['goodWhenUp'] ?? null) !== null) {
                $isGood = ($delta['value'] > 0) === $delta['goodWhenUp'];
                $deltaClass = $isGood ? 'deltaGood' : 'deltaBad';
            }
        @endphp
        <div class="reportStatTile">
            <span class="tileLabel">{{ $tile['label'] }}</span>
            <span class="tileValue @if ($isDanger) tileValueDanger @endif">{{ $tile['value'] }}</span>
            @if ($delta !== null)
                <span class="tileDelta {{ $deltaClass }}">
                    @if ($delta['value'] > 0) ▲ +{{ \Illuminate\Support\Number::format($delta['value'], maxPrecision: 1) }}
                    @elseif ($delta['value'] < 0) ▼ {{ \Illuminate\Support\Number::format($delta['value'], maxPrecision: 1) }}
                    @else ±0
                    @endif
                    {{ $delta['vs'] }}
                </span>
            @endif
        </div>
    @endforeach
</div>
