{{-- Stub: navigations/steps — placeholder for future implementation --}}
@props([
    'steps' => [],
    'current' => 0,
])

<nav {{ $attributes->merge(['class' => 'steps-nav']) }} aria-label="Progress">
    {{ $slot }}
</nav>
