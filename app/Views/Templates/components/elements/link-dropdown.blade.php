@props([
    'label' => '',
    'icon' => 'fa fa-caret-down',
    'align' => 'end',
    'menuClass' => '',
    'triggerClass' => '',
])

<div {{ $attributes->merge(['class' => 'dropdown']) }} style="display:inline-block;">
    <a href="javascript:void(0)" class="dropdown-toggle {{ $triggerClass }}" data-toggle="dropdown">
        {!! $label !!}
        <i class="{{ $icon }}"></i>
    </a>
    <ul class="dropdown-menu {{ $menuClass }}">
        {{ $slot }}
    </ul>
</div>
