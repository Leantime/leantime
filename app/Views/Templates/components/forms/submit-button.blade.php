@props([
    "htmxIndicator" => true,
    "value" => __('buttons.save'),
    "name" => "submitBtn"
])

<div class="tw-inline ">
    <input type="submit" value="{{ $value }}" name="{{ $name }}" {{ $attributes->merge([ 'class' => 'btn btn-primary tw-float-left']) }} />
    @if($htmxIndicator)
        <div class="htmx-indicator htmx-indicator-small tw-float-left tw-mt-[13px]">
            <x-global::elements.loader id="loadingthis" size="25px" />
        </div>
    @endif
    <div class="clearall"></div>
</div>
