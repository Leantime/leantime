@props([
    'label' => '',
    'icon' => 'fa fa-caret-down',
    'align' => 'end',
    'menuClass' => '',
    'triggerClass' => '',
])

<div {{ $attributes->merge(['class' => 'tw:dropdown tw:dropdown-' . $align . ' tw:inline-block']) }}>
    <a tabindex="0" role="button" class="{{ $triggerClass }}" href="javascript:void(0)">
        {!! $label !!}
        <i class="{{ $icon }}"></i>
    </a>
    <ul tabindex="0" class="tw:dropdown-content tw:menu tw:bg-base-100 tw:rounded-box tw:z-50 tw:min-w-52 tw:p-2 tw:shadow-sm {{ $menuClass }}">
        {{ $slot }}
    </ul>
</div>
