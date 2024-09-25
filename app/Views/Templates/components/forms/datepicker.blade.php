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
    if($variant !== 'chip') {
        $buttonSize = 'w-full';
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
        <x-slot:label-text class="font-normal">
            @if(!empty(trim($leadingVisual)))
                <div class="h-6 w-6">
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
            <button type="button" class="btn btn-default float-right timeToggleButton-{{ $dateName }}" onclick="leantime.dateController.toggleTime('#datepickerDropDown-{{ $dateName }}', this)">
                <i class="fa fa-clock"></i>
            </button>

            <input type="date" id="datepickerDropDown-{{ $dateName }}" value="{{ format($value)->isoDateTime() }}" />

            <hr class="mt-xs"/>
            <button type="button" class="btn btn-default float-right" onclick="jQuery(body).click()" >Close</button>
            <button type="button" class="btn btn-default float-right" onclick="datepickerInstance.clear(); timePickerInstance.clear();" >Clear</button>
        </x-slot:card-content>
    </x-global::actions.dropdown>

    @if($validationText)
        <x-slot:validation-text> {!! $validationText !!}</x-slot:validation-text>
    @endif


</x-global::forms.field-row>

<script>

    leantime.datePickers.initDateTimePicker("#datepickerDropDown-{{ $dateName }}");

    @if(dtHelper()->isValidDateString($value) && !dtHelper()->parseDbDateTime($value)->setToUserTimezone()->isEndOfDay() && !dtHelper()->parseDbDateTime($value)->setToUserTimezone()->isStartOfDay())
        leantime.datePickers.toggleTime('#datepickerDropDown-{{ $dateName }}', '.timeToggleButton-{{ $dateName }}');
    @endif

</script>


