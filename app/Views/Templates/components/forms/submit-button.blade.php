@props([
    "htmxIndicator" => true,
    "value" => __('buttons.save'),
    "name" => "submitBtn"
])

<div class="inline float-left mr-xs">
    <button type="submit" value="{{ $value }}" name="{{ $name }}" {{ $attributes->merge([ 'class' => 'btn btn-primary float-left ']) }}>
        {{ $value }}
    </button>
    @if($htmxIndicator)
        <div class="htmx-indicator htmx-indicator-small float-left mt-[4px]">
            <x-global::elements.loader id="loadingthis" size="25px" />
        </div>
    @endif
    <div class="clearall"></div>
</div>
