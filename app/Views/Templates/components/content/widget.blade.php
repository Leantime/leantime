@props([
    'title' => null,
    'icon' => null,
    'collapsible' => false,
])

<div {{ $attributes->merge(['class' => 'widget lt-glass-subtle']) }}>
    @if($title)
        <h4 class="widgettitle">
            @if($icon)<i class="{{ $icon }}"></i> @endif
            {{ $title }}
            @isset($titleActions)
                <span style="float: right;">{{ $titleActions }}</span>
            @endisset
        </h4>
    @endif
    <div class="widgetcontent">
        {{ $slot }}
    </div>
</div>
