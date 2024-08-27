@props([
    "htmxIndicator" => false,
    "value" => __('buttons.cancel'),
    "name" => "secondary"
])

<div class="tw-inline tw-float-left">
    <button type="reset" name="{{ $name }}" {{ $attributes->merge([ 'class' => 'btn btn-secondary tw-float-left tw-mr-xs']) }}>
        {{ $value }}
    </button>
    @if($htmxIndicator)
        <div class="htmx-indicator htmx-indicator-small tw-float-right tw-mt-[13px]">
            <x-global::elements.loader id="loadingthis" size="25px" />
        </div>
    @endif
    <div class="clearall"></div>
</div>
