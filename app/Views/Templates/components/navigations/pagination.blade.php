{{-- Stub: navigations/pagination — placeholder for future implementation --}}
@props([
    'total' => 0,
    'perPage' => 25,
    'currentPage' => 1,
])

<nav {{ $attributes->merge(['class' => 'pagination-nav']) }} aria-label="Pagination">
    {{ $slot }}
</nav>
