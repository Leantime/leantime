@props([
    "dateName" => '',
    "value" => '',
    "timeName" => '',
    "showTime" => true,
    "noDateLabel" => "",
])
<div class="dropDownContainer">
    <a href="javascript:void(0);" class="dropdown-toggle input-proxy" data-toggle="dropdown" id="dateTimeDropdown-{{ $dateName }}">
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
    </a>
    <div class="datetime-dropdown dropdown-menu">

        <button type="button" class="btn btn-default tw-float-right timeToggleButton-{{ $dateName }}" onclick="leantime.dateController.toggleTime('#datepickerDropDown-{{ $dateName }}', this)">
            <i class="fa fa-clock"></i>
        </button>
        <div class="clearall"></div>
        <input type="date" id="datepickerDropDown-{{ $dateName }}" value="{{ format($value)->isoDateTime() }}" />
        <hr class="tw-mt-xs"/>
        <button type="button" class="btn btn-default tw-float-right" onclick="jQuery(body).click()" >Close</button>
        <button type="button" class="btn btn-default tw-float-right" onclick="datepickerInstance.clear(); timePickerInstance.clear();" >Clear</button>
    </div>
</div>

<script>
    jQuery(".datetime-dropdown").click(function(event){
        event.stopPropagation();
    });

    leantime.dateController.initDateTimePicker("#datepickerDropDown-{{ $dateName }}");

    @if(dtHelper()->isValidDateString($value) && !dtHelper()->parseDbDateTime($value)->setToUserTimezone()->isEndOfDay() && !dtHelper()->parseDbDateTime($value)->setToUserTimezone()->isStartOfDay())
        console.log("notEndOfDay");
        leantime.dateController.toggleTime('#datepickerDropDown-{{ $dateName }}', '.timeToggleButton-{{ $dateName }}');
    @else
        console.log("endOfDay");
    @endif


</script>


