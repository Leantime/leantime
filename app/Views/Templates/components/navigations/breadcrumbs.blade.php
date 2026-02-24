{{-- Stub: navigations/breadcrumbs — placeholder for future implementation --}}
@props([
    'items' => [],
])

<nav {{ $attributes->merge(['class' => 'breadcrumbs-nav']) }} aria-label="Breadcrumb">
    {{ $slot }}
</nav>
