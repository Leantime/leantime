@props([
    'tabs' => [],
    'activeTab' => '',
    'orientation' => 'horizontal',
    'size' => 'md',
    'variant' => 'bordered',
    'fullWidth' => false
])

@php
    $orientationClass = $orientation === 'vertical' ? 'tabs-vertical' : '';
    $sizeClass = $size && $size !== 'md' ? "tabs-{$size}" : '';
    $variantClass = $variant && $variant !== 'bordered' ? "tabs-{$variant}" : '';
    $fullWidthClass = $fullWidth ? 'w-full' : '';
@endphp

<div class="tabs {{ $orientationClass }} {{ $sizeClass }} {{ $variantClass }} {{ $fullWidthClass }}">
    @foreach ($tabs as $tab)
        <a class="tab {{ $activeTab === $tab['id'] ? 'tab-active' : '' }}"
           href="#{{ $tab['id'] }}"
           @click.prevent="$dispatch('tab-clicked', '{{ $tab['id'] }}')">
            @if (isset($tab['icon']))
                <span class="mr-2">{{ $tab['icon'] }}</span>
            @endif
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>

<div class="mt-4">
    {{ $slot }}
</div>