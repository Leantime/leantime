@props([
    'label' => '',
    'type' => 'primary',
    'icon' => null,
    'align' => 'end',
    'menuClass' => '',
])

@php
    $bsClass = match($type) {
        'primary'   => 'btn btn-primary',
        'secondary' => 'btn btn-default',
        'default'   => 'btn btn-default',
        'danger'    => 'btn btn-danger',
        default     => 'btn btn-' . $type,
    };
@endphp

<div {{ $attributes->merge(['class' => 'dropdown']) }}>
    <a href="javascript:void(0)" class="{{ $bsClass }} dropdown-toggle" data-toggle="dropdown">
        @if($icon)<i class="{{ $icon }}"></i> @endif
        {!! $label !!}
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu {{ $menuClass }}">
        {{ $slot }}
    </ul>
</div>
