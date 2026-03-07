@props([
    'label' => '',
    'value' => '',
    'suffix' => '',
    'secondary' => '',
    'secondaryColor' => null,
])

<div {{ $attributes->merge(['class' => 'tw:p-2']) }}>
    <div class="tw:text-[var(--rv-muted)]" style="font-size: var(--font-size-xs, 11px); line-height: 1.3;">
        {{ $label }}
    </div>
    <div class="tw:font-semibold tw:mt-0.5" style="font-size: var(--font-size-l, 18px); line-height: 1.2;">
        {{ $value }}@if($suffix) <span style="font-size: var(--font-size-s, 12px); font-weight: 400;">{{ $suffix }}</span>@endif
    </div>
    @if($secondary)
        <div style="font-size: var(--font-size-xs, 11px); margin-top: 2px;{{ $secondaryColor ? ' color: ' . $secondaryColor . ';' : ' color: var(--rv-muted);' }}">
            {{ $secondary }}
        </div>
    @endif
    {{ $slot }}
</div>
