<div class="flex gap-base justify-start">
    @foreach ($links as $link)
        @if (empty($link['display']))
            @continue
        @endif

        <span>
            {{ $link['prefix'] ?? '' }} @if (! empty($link['link'])) <a href="{!! $link['link'] !!}">{!! $link['display'] !!}</a> @else {!! $link['display'] !!} @endif
        </span>
    @endforeach
</div>
