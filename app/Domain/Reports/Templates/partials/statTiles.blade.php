{{--
    Row of summary stat tiles with optional period-over-period deltas.

    Expects:
    $tiles: array of ['label' => string, 'value' => string|int|float, 'delta' => string|null, 'tone' => 'default'|'danger']
--}}
<div class="row reportStatTiles">
    @foreach ($tiles as $tile)
        <div class="col-md-3">
            <div class="boxedHighlight tw-py-4">
                <span class="headline">{{ $tile['label'] }}</span>
                <span class="value" @if (($tile['tone'] ?? 'default') === 'danger' && $tile['value'] > 0) style="color: var(--red);" @endif>{{ $tile['value'] }}</span>
                @if (!empty($tile['delta']))
                    <span class="tw-block tw-text-xs tw-opacity-70">{{ $tile['delta'] }}</span>
                @endif
            </div>
        </div>
    @endforeach
</div>
