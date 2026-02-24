@props([
    'label' => null,
    'icon' => null,
    'align' => 'end',
    'buttonClass' => '',
    'menuClass' => '',
    'containerClass' => '',
])

<div {{ $attributes->merge(['class' => 'dropdown' . ($containerClass ? ' ' . $containerClass : '')]) }}>
    <a href="javascript:void(0)" class="dropdown-toggle {{ $buttonClass }}" data-toggle="dropdown" @if(!$label) aria-label="{{ __('label.more_options') }}" @endif>
        @if($icon)
            <i class="{{ $icon }}"></i>
        @endif
        @if($label)
            {!! $label !!}
        @endif
        @if(!$icon && !$label)
            <i class="fa-solid fa-ellipsis-vertical"></i>
        @endif
    </a>
    <ul class="dropdown-menu {{ $menuClass }}">
        {{ $slot }}
    </ul>
</div>
