<div class="row canvas-row">
    @foreach($row['columns'] as $col)
        <div class="column" style="width: {{ $col['width'] }}%">
            @if(isset($col['title']))
                <h4 class="widgettitle title-primary center"><i class="{{ $col['icon'] ?? '' }}"></i> {!! __($col['title']) !!}</h4>
            @endif
            @if(isset($col['content']))
                <div class="contentInner even" style="padding-top: 10px;">
                    {!! sprintf(__($col['content']), BASE_URL) !!}
                </div>
            @endif
        </div>
    @endforeach
</div>
