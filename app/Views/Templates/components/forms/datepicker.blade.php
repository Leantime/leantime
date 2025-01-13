@props([
    //Basic Definition
    'contentRole' => 'ghost', //default, primary, secondary, tertiary (ghost), accent, link
    'state' => '', //default, info, warning, danger, success,
    'scale' => '', //xs, sm, md, lg, xl

    //labels & content
    'labelPosition' => 'top',
    'labelText' => '',
    'helpText' => '',
    'leadingVisual' => '',
    'trailingVisual' => '',
    'caption' => '',
    'validationText' => '',
    'validationState' => '',

    //Variation options
    'variant' => '', //chip, input
    'dateName' => '',
    'value' => '',
    'timeName' => '',
    'showTime' => true,
    'noDateLabel' => "",
])

@php

    $buttonSize = '';
    $elementHeight = 5;
    if($variant !== 'chip') {
        $buttonSize = 'w-full';
        $elementHeight = 6;
    }


@endphp

<x-global::forms.field-row :label-position="$labelPosition">
    @if($labelText)
        <x-slot:label-text>{!! $labelText !!}</x-slot:label-text>
    @endif

    @if($helpText)
        <x-slot:help-text>{!! $helpText !!}</x-slot:help-text>
    @endif

    @if($caption)
        <span class="label-text">{{ $caption }}</span>
    @endif

    <x-global::actions.dropdown variant="card" :content-role="$contentRole" class="date-dropdown {{ $buttonSize }}" button-variant="{{ $variant }}">
        <x-slot:label-text>
            @if(!empty(trim($leadingVisual)))
                <div class="h-{{ $elementHeight }} w-{{ $elementHeight }}">
                    {!! $leadingVisual !!}
                </div>
            @endif

            @if(empty($value) || ! dtHelper()->isValidDateString($value))
                <span class="dateField font-light">{{ $noDateLabel }}</span>
                <span class="timeField font-light"></span>
            @else
                <span class="dateField font-light">{{ format($value)->date() }}</span>
                <span class="timeField font-light">
                        @if(dtHelper()->isValidDateString($value) && !dtHelper()->parseDbDateTime($value)->setToUserTimezone()->isEndOfDay() && !dtHelper()->parseDbDateTime($value)->setToUserTimezone()->isStartOfDay())
                            {{ format($value)->time() }}
                    @endif
                </span>
            @endif
        </x-slot:label-text>
        <x-slot:card-content>


            <input type="date"
                   data-component="datepicker"
                   id="datepickerDropDown-{{ $dateName }}"
                   class="input-md datepickerDropDown-{{ $dateName }}"
                   value="{{ format($value)->isoDateTime() }}"
                   {{ $attributes->filter(
                        function ($value, $key) {
                            return \Illuminate\Support\Str::contains($key, "hx-");
                        }) }}
            />
            <button type="button" class="btn btn-sm float-right timeToggleButton-{{ $dateName }}" onclick="datePickers.toggleTime('.datepickerDropDown-{{ $dateName }}', this)">
                <i class="fa fa-clock"></i>
            </button>
            <hr class="mb-xs mt-0"/>
            <div class="flex justify-end gap-x-xs">
                <button type="button" class="btn btn-sm float-right" onclick="datePickers.clear('.datepickerDropDown-{{ $dateName }}', this);" >Clear</button>
                <button type="button" class="btn btn-primary btn-sm float-right" onclick="jQuery(body).click()" >Ok</button>

            </div>
        </x-slot:card-content>
    </x-global::actions.dropdown>

    @if($validationText)
        <x-slot:validation-text> {!! $validationText !!}</x-slot:validation-text>
    @endif


</x-global::forms.field-row>

<script type="module">

   import "@mix('/js/components/datePickers.module.js')"

   jQuery(document).ready(function () {
       jQuery(document).on('click', '.date-dropdown', function (e) {
           e.stopPropagation();
       });
   });

    @if(dtHelper()->isValidDateString($value) && !dtHelper()->parseDbDateTime($value)->setToUserTimezone()->isEndOfDay() && !dtHelper()->parseDbDateTime($value)->setToUserTimezone()->isStartOfDay())
        datePickers.toggleTime('.datepickerDropDown-{{ $dateName }}', '.timeToggleButton-{{ $dateName }}');
    @endif

</script>


