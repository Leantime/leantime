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

<div {{ $attributes->merge(['class' => 'dropdown' . ($containerClass ? ' ' . $containerClass : '')]) }}
     @if($variant === 'link') style="display:inline-block;" @endif
>
    <a href="javascript:void(0)"
       class="dropdown-toggle {{ $resolvedTriggerClass }}"
       data-toggle="dropdown"
       @if(!$label) aria-label="{{ __('label.more_options') }}" @endif
    >
        @if($leadingVisual)
            <i class="{{ $leadingVisual }}"></i>
        @endif
        @if($label)
            {!! $label !!}
        @endif
        @if(!$leadingVisual && !$label)
            <i class="fa-solid fa-ellipsis-vertical"></i>
        @endif
        @if($variant === 'button')
            <span class="caret"></span>
        @endif
        @if($trailingVisual)
            <i class="{{ $trailingVisual }}"></i>
        @endif
    </a>
    <ul class="dropdown-menu {{ $menuClass }}">
        {{ $slot }}
    </ul>
</div>
