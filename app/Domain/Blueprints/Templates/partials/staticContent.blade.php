<div class="row canvas-row">
    @foreach($row['columns'] as $col)
        <div class="column" style="width: {{ $col['width'] }}%">
            @if(isset($col['title']))
                <x-global::elements.board-column-header align="center" :icon="$col['icon'] ?? null">{!! __($col['title']) !!}</x-global::elements.board-column-header>
            @endif
            @if(isset($col['content']))
                <div class="contentInner even" style="padding-top: 10px;">
                    {!! sprintf(__($col['content']), BASE_URL) !!}
                </div>
            @endif
        </div>
    @endforeach
</div>
