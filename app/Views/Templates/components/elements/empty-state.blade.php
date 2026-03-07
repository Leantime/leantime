@props([
    'headline' => null,
    'image' => null,
])

<div {{ $attributes->merge(['class' => 'center', 'style' => 'padding: 40px 20px;']) }}>
    @if($image)
        <x-globals::undrawSvg image="{{ $image }}" maxWidth="250px" />
    @endif
    @if($headline)
        <h3 style="margin-top: 15px; color: var(--primary-font-color);">{{ $headline }}</h3>
    @endif
    @if($slot->isNotEmpty())
        <p style="margin-top: 8px; color: var(--secondary-font-color);">{{ $slot }}</p>
    @endif
    @isset($actions)
        <div style="margin-top: 15px;">
            {{ $actions }}
        </div>
    @endisset
</div>
