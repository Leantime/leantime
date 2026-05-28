<div class="row canvas-row">
    @foreach($row['columns'] as $col)
        <div class="column center" style="width: {{ $col['width'] }}%">
            @if(isset($col['icon']))
                <i class="{{ $col['icon'] }}"></i>
            @else
                &nbsp;
            @endif
        </div>
    @endforeach
</div>
