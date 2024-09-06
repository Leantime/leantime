@props([
    "htmxIndicator" => false,
    "value" => __('buttons.cancel'),
    "name" => "secondary"
])

<div class="inline float-left">
    <button type="reset" name="{{ $name }}" {{ $attributes->merge([ 'class' => 'btn btn-secondary float-left mr-xs']) }}>
        {{ $value }}
    </button>
    @if($htmxIndicator)
        <div class="htmx-indicator htmx-indicator-small float-right mt-[13px]">
            <x-global::elements.loader id="loadingthis" size="25px" />
        </div>
    @endif
    <div class="clearall"></div>
</div>
