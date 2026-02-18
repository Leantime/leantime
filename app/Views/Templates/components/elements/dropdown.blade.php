@props([
    'label' => null,
    'icon' => null,
    'align' => 'end',
    'buttonClass' => 'tw:btn tw:btn-ghost tw:btn-sm',
    'menuClass' => '',
    'containerClass' => '',
])

<div {{ $attributes->merge(['class' => 'tw:dropdown tw:dropdown-' . $align . ($containerClass ? ' ' . $containerClass : '')]) }}>
    <div tabindex="0" role="button" class="{{ $buttonClass }}">
        @if($icon)
            <i class="{{ $icon }}"></i>
        @endif
        @if($label)
            {!! $label !!}
        @endif
        @if(!$icon && !$label)
            <i class="fa-solid fa-ellipsis-vertical"></i>
        @endif
    </div>
    <ul tabindex="0" class="tw:dropdown-content tw:menu tw:bg-base-100 tw:rounded-box tw:z-50 tw:min-w-52 tw:p-2 tw:shadow-sm {{ $menuClass }}">
        {{ $slot }}
    </ul>
</div>
