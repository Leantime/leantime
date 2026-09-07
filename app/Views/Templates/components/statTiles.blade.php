{{--
    Grid wrapper for x-global::statTile.

    Two ways to use it:

    1. Pass tiles as data (the reports' array shape):
         <x-global::statTiles :tiles="$tiles" />
       where each entry is ['value','label','unit','delta','tone','icon'].

    2. Compose them by hand when tiles need extra markup (drill-down panels,
       meters, custom attributes):
         <x-global::statTiles>
             <x-global::statTile value="3" label="…" class="has-detail" />
         </x-global::statTiles>
--}}
@props(['tiles' => null])

<div {{ $attributes->merge(['class' => 'lt-stats']) }}>
    @if ($tiles !== null)
        @foreach ($tiles as $tile)
            <x-global::statTile
                :value="$tile['value']"
                :label="$tile['label']"
                :unit="$tile['unit'] ?? null"
                :delta="$tile['delta'] ?? null"
                :tone="$tile['tone'] ?? 'default'"
                :icon="$tile['icon'] ?? null" />
        @endforeach
    @endif

    {{ $slot }}
</div>
