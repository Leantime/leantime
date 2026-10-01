{{--
    Thin adapter over the shared x-global::statTiles component.

    The report body passes tiles in this domain's own shape; this maps it onto
    the component's props so the reports use the SAME tile as the program report
    (they used to render a separate `.reportStatTile` that had drifted apart).

    $tiles: array of [
        'label' => string,
        'value' => string|int|float,
        'unit'  => string|null,   (optional trailing subscript, e.g. "/6" or "h")
        'tone'  => 'default'|'danger' (danger colors the value red when > 0),
        'delta' => null|['value' => float, 'goodWhenUp' => bool|null, 'vs' => string],
    ]
--}}
<x-global::statTiles>
    @foreach ($tiles as $tile)
        @php
            // 'danger' only fires when there is actually something to worry
            // about — a zero overdue count is good news, not an alarm.
            $isRisk = ($tile['tone'] ?? 'default') === 'danger' && (float) $tile['value'] > 0;

            $tileDelta = null;
            if (! empty($tile['delta'])) {
                $tileDelta = [
                    'value' => $tile['delta']['value'],
                    'goodWhenUp' => $tile['delta']['goodWhenUp'] ?? null,
                    'label' => $tile['delta']['vs'] ?? null,
                ];
            }
        @endphp

        <x-global::statTile
            :value="$tile['value']"
            :label="$tile['label']"
            :unit="$tile['unit'] ?? null"
            :delta="$tileDelta"
            :tone="$isRisk ? 'risk' : 'default'" />
    @endforeach
</x-global::statTiles>
