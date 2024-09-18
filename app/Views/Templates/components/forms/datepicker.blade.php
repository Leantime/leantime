@props([
    "dateName" => '',
    "value" => '',
    "timeName" => '',
    "showTime" => true,
    "noDateLabel" => "",
    "labelText" => '',
    "labelRight" => '',
    "caption" => '',
    "validationText" => '',
])

<div class='form-control relative w-full max-w-xs'>

    <x-global::forms.label-row>
        @if($labelText)
            <x-slot:label-text> {!! $labelText !!}</x-slot:label-text>
        @endif
        @if($labelRight)
            <x-slot:label-right> {!! $labelRight !!}</x-slot:label-right>
        @endif
    </x-global::forms.label-row>

    @if($caption)
        <span class="label-text">{{ $caption }}</span>
    @endif

    <x-global::actions.dropdown variant="card" content-role="ghost">
        <x-slot:label-text>
            Date Button
            @if(empty($value))
                <span class="dateField">{{ $noDateLabel }}</span>
                <span class="timeField"></span>
            @else
                <span class="dateField">{{ format($value)->date() }}</span>
                <span class="timeField">
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
        <x-global::forms.label-row class="mt-1 transition-opacity duration-500 ease-in-out opacity-100">
            <x-slot:label-text-right class="{{ $validationClass }}"> {!! $validationText !!}</x-slot:label-text-right>
        </x-global::forms.label-row>
    @endif

</div>


<script type="module">
    import datePickersModule from "/assets/js/app/core/datePickers.module.js";

    datePickersModule.initDateTimePicker("#datepickerDropDown-{{ $dateName }}");

    @if(dtHelper()->isValidDateString($value) && !dtHelper()->parseDbDateTime($value)->setToUserTimezone()->isEndOfDay() && !dtHelper()->parseDbDateTime($value)->setToUserTimezone()->isStartOfDay())
        datePickersModule.toggleTime('#datepickerDropDown-{{ $dateName }}', '.timeToggleButton-{{ $dateName }}');
    @endif

</script>


