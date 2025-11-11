{{-- Modern Search Input Component --}}

@props([
    'id' => 'modernSearch',
    'placeholder' => 'Search...',
    'width' => '400',
    'containerClass' => '',
    'value' => '',
])

<div class="modern-search-wrapper {{ $containerClass }}" id="{{ $id }}Wrapper" style="width: {{ $width }}px;">
    <input
        type="text"
        id="{{ $id }}"
        class="modern-search-input"
        placeholder="{{ $placeholder }}"
        autocomplete="off"
        value="{{ $value }}"
    />
    <button type="button" class="modern-search-clear" id="{{ $id }}Clear" aria-label="Clear search">
        <span class="fa fa-times"></span>
    </button>
</div>

