{{-- Stub: layout/select-panel — placeholder for future implementation --}}
@props([
    'title' => null,
])

<div {{ $attributes->merge(['class' => 'select-panel']) }}>
    @if($title)
        <div class="select-panel-header">
            <h3>{{ $title }}</h3>
        </div>
    @endif
    <div class="select-panel-body">
        {{ $slot }}
    </div>
</div>
