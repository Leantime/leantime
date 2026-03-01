@props([
    'label' => null,
    'leadingVisual' => null,
    'trailingVisual' => null,
    'contentRole' => 'default',    // default|primary|secondary|ghost|link
    'align' => 'end',
    'menuClass' => '',
    'containerClass' => '',
    'triggerClass' => '',
    'variant' => 'icon',          // icon|button|link
    'position' => null,           // left|right — controls menu open direction
    'scale' => 'm',              // s|m|l — trigger sizing
])

@php
    // Build trigger classes based on variant
    $resolvedTriggerClass = match($variant) {
        'button' => match($contentRole) {
            'primary'              => 'btn btn-primary',
            'secondary', 'default', 'ghost' => 'btn btn-default',
            'danger', 'error'      => 'btn btn-danger',
            'success'              => 'btn btn-success',
            'warning'              => 'btn btn-warning',
            'link'                 => 'btn btn-link',
            default                => 'btn btn-' . $contentRole,
        },
        'link' => $triggerClass,
        default => $triggerClass,
    };
@endphp

@php
    $positionClass = match($position) {
        'left'  => ' dropdown-position-left',
        'right' => ' dropdown-position-right',
        default => '',
    };
    $scaleClass = match($scale) {
        's' => ' dropdown-scale-s',
        'l' => ' dropdown-scale-l',
        default => '',
    };
@endphp

<div {{ $attributes->merge(['class' => 'dropdown' . ($containerClass ? ' ' . $containerClass : '') . $positionClass . $scaleClass]) }}
     @if($variant === 'link') style="display:inline-block;" @endif
>
    <a href="javascript:void(0)"
       class="dropdown-toggle {{ $resolvedTriggerClass }}"
       data-toggle="dropdown"
       @if(!$label) aria-label="{{ __('label.more_options') }}" @endif
    >
        @if($leadingVisual)
            @if(str_contains($leadingVisual, 'fa-') || str_starts_with($leadingVisual, 'fa '))
                <i class="{{ $leadingVisual }}" aria-hidden="true"></i>
            @else
                <x-global::elements.icon :name="$leadingVisual" />
            @endif
        @endif
        @if($label)
            {!! $label !!}
        @endif
        @if(!$leadingVisual && !$label)
            <x-global::elements.icon name="more_vert" />
        @endif
        @if($variant === 'button')
            <span class="caret"></span>
        @endif
        @if($trailingVisual)
            @if(str_contains($trailingVisual, 'fa-') || str_starts_with($trailingVisual, 'fa '))
                <i class="{{ $trailingVisual }}" aria-hidden="true"></i>
            @else
                <x-global::elements.icon :name="$trailingVisual" />
            @endif
        @endif
    </a>
    <ul class="dropdown-menu {{ $menuClass }}">
        {{ $slot }}
    </ul>
</div>
