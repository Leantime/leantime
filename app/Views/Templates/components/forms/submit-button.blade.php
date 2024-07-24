@props([
    "htmxIndicator" => true,
    "value" => __('buttons.save'),
    "name" => "submitBtn"
])

<div class="tw-inline tw-float-left tw-mr-xs">
    <button type="submit" value="{{ $value }}" name="{{ $name }}" {{ $attributes->merge([ 'class' => 'btn btn-primary tw-float-left ']) }}>
        {{ $value }}
    </button>
    @if($htmxIndicator)
        <div class="htmx-indicator htmx-indicator-small tw-float-left tw-mt-[4px]">
            <x-global::elements.loader id="loadingthis" size="25px" />
        </div>
    @endif
    <div class="clearall"></div>
</div>
