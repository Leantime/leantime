<div class="row canvas-row">
    @foreach($row['columns'] as $col)
        <div class="column" style="width: {{ $col['width'] }}%">
            @if(isset($col['header']))
                <h4 class="widgettitle title-primary center canvas-title-only">
                    <large><i class="{{ $col['header']['icon'] }}"></i> {!! __($col['header']['title']) !!}</large>
                </h4>
            @endif
        </div>
    @endforeach
</div>
