@props([
    'label' => '',
    'type' => 'primary',
    'icon' => null,
    'align' => 'end',
    'menuClass' => '',
])

@php
    $typeClass = match($type) {
        'primary'   => 'tw:btn-primary',
        'secondary' => 'tw:btn-secondary',
        'default'   => 'tw:btn-ghost',
        default     => 'tw:btn-' . $type,
    };
@endphp

<div {{ $attributes->merge(['class' => 'tw:dropdown tw:dropdown-' . $align]) }}>
    <div tabindex="0" role="button" class="tw:btn {{ $typeClass }}">
        @if($icon)<i class="{{ $icon }}"></i> @endif
        {!! $label !!}
        <i class="fa fa-caret-down tw:text-xs"></i>
    </div>
    <ul tabindex="0" class="tw:dropdown-content tw:menu tw:bg-base-100 tw:rounded-box tw:z-50 tw:min-w-52 tw:p-2 tw:shadow-sm {{ $menuClass }}">
        {{ $slot }}
    </ul>
</div>
