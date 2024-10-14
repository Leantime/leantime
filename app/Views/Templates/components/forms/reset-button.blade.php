@props([
    "htmxIndicator" => false,
    "value" => __('buttons.cancel'),
    "name" => "secondary"
])

<div class="inline float-left">
    <x-global::forms.button type="reset" name="{{ $name }}" content-role="secondary" {{ $attributes->merge(['class' => 'float-left mr-xs']) }}>
        {{ $value }}
    </x-global::forms.button>
    @if($htmxIndicator)
        <div class="htmx-indicator htmx-indicator-small float-right mt-[13px]">
            <x-global::elements.loader id="loadingthis" size="25px" />
        </div>
    @endif
    <div class="clearall"></div>
</div>
