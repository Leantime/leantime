@props([
    'title' => null,
    'compact' => false,
])

<div {{ $attributes->merge(['class' => 'tw:card tw:bg-base-100 tw:shadow-sm' . ($compact ? ' tw:card-sm' : '')]) }}>
    <div class="tw:card-body">
        @if($title)
            <h2 class="tw:card-title">{{ $title }}</h2>
        @endif
        {{ $slot }}
        @isset($actions)
            <div class="tw:card-actions tw:justify-end">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
