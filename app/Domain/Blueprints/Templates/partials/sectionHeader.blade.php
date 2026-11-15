<div class="row canvas-row">
    @foreach($row['columns'] as $col)
        <div class="column" style="width: {{ $col['width'] }}%">
            @if(isset($col['header']))
                <x-global::elements.board-column-header align="center" class="canvas-title-only" :icon="$col['header']['icon'] ?? null">
                    {!! __($col['header']['title']) !!}
                </x-global::elements.board-column-header>
            @endif
        </div>
    @endforeach
</div>
