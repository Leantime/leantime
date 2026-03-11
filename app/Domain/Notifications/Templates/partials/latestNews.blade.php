<h1>Latest From Leantime</h1>
<br />
<div>
    <ul>
        @if(is_string($rss))
            {{ $rss }}
        @endif
        @foreach ($rss->channel->item as $item)
            <li class="tw:border-b tw:border-b-[var(--main-border-color)]">
                <strong><a href="{{ $item->link }}" target="_blank">{{ $item->title }}</a></strong><br/>
                <small class="tw:pb-1">{{ $item->pubDate }}</small><br />
                <p>{!! $item->description !!}</p>
                <div class="clearall"></div>
            </li>
        @endforeach
    </ul>
</div>
