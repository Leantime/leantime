@props([
    'headings',
    'contents',
    'persist' => '',
    'id' => '',
])

<div
    {{ $attributes->merge(['class' => 'lt-tabs tabbedwidget']) }}
    data-tabs
    style="visibility:hidden;"
    @if($persist) data-tabs-persist="{{ $persist }}" @endif
    @if($id) id="{{ $id }}" @endif
>
    <ul {{ $headings->attributes->merge(['role' => 'tablist']) }}>
        {{ $headings }}
    </ul>

    {{ $contents }}
</div>
