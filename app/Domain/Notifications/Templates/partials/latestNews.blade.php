<h1>Latest From Leantime</h1>
<br />
<div>
    <ul>
        @if(is_string($rss))
            {{ $rss }}
        @endif
        @foreach ($rss->channel->item as $item)
            <li style="border-bottom:1px solid var(--main-border-color)">
                <h2><a href="{{ $item->link }}" target="_blank">{{ $item->title }}</a></h2>
                <small>{{ $item->pubDate }}</small>
                <p>{!! $item->description !!}</p>
            </li>
        @endforeach
    </ul>
</div>
