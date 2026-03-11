@props([
    'sticky' => false,
])

<div {{ $attributes->merge(['class' => 'lt-nav-tabs' . ($sticky ? ' lt-nav-tabs--sticky' : '')]) }}>
    <ul role="tablist">
        {{ $slot }}
    </ul>
</div>
