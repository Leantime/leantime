@foreach($links as $link)
    @if (! empty($link['text']))
        {{ ! $loop->first ? '|' : '' }}
        {{ $link['prefix'] ?? '' }} @if (! empty($link['link'])) <a href="{!! $link['link'] !!}">{{ $link['text'] }}</a> @else {{ $link['text'] }} @endif
    @endif
@endforeach
