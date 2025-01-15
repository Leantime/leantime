<h1 style="font-size: var(--font-size-xxl); font-weight: 200; margin-left: 0.8rem; margin-top: 0.2rem;">Latest From Leantime</h1>
<br />
<div>
    <ul>
        @if(is_string($rss))
            {{ $rss }}
        @endif
        @foreach ($rss->channel->item as $item)
            <li style="border-bottom:1px solid var(--main-border-color)">
                <strong><a href="{{ $item->link }}" target="_blank">{{ $item->title }}</a></strong><br/>
                <small class="tw-pb-1">{{ $item->pubDate }}</small><br />
                <p>{!! $item->description !!}</p>
            </li>
        @endforeach
    </ul>
</div>
