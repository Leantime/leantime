{{--
    Reporting period selector: quarter presets swap the report body via HTMX (falling back to a
    full page load without JS), the custom range submits a plain GET form.

    @props
    period: \Leantime\Domain\Reports\Models\ReportPeriod - the active period
    url:    string - page URL (fallback links, pushed browser URL, custom-range form action)
    hxUrl:  string - HTMX endpoint rendering the report body partial
    target: string - CSS selector of the report body element to swap
--}}
@props(['period', 'url', 'hxUrl', 'target' => '#reportBody'])

@php
    $presets = [
        \Leantime\Domain\Reports\Models\ReportPeriod::PRESET_LAST_QUARTER => __('label.period_last_quarter'),
        \Leantime\Domain\Reports\Models\ReportPeriod::PRESET_THIS_QUARTER => __('label.period_this_quarter'),
        \Leantime\Domain\Reports\Models\ReportPeriod::PRESET_NEXT_QUARTER => __('label.period_next_quarter'),
    ];
    $isCustom = $period->preset === \Leantime\Domain\Reports\Models\ReportPeriod::PRESET_CUSTOM;
@endphp

<div {{ $attributes->merge(['class' => 'periodPicker tw-flex tw-items-center tw-gap-2 tw-flex-wrap']) }}>

    <div class="btn-group" role="group">
        @foreach ($presets as $presetKey => $presetLabel)
            <a href="{{ $url }}?preset={{ $presetKey }}"
               hx-get="{{ $hxUrl }}?preset={{ $presetKey }}"
               hx-target="{{ $target }}"
               hx-swap="outerHTML"
               hx-push-url="{{ $url }}?preset={{ $presetKey }}"
               class="btn btn-sm btn-secondary @if ($period->preset === $presetKey) active @endif">
                {{ $presetLabel }}
            </a>
        @endforeach
        <button type="button"
                onclick="jQuery(this).closest('.periodPicker').find('.periodPickerCustom').toggle();"
                aria-expanded="{{ $isCustom ? 'true' : 'false' }}"
                class="btn btn-sm btn-secondary @if ($isCustom) active @endif">
            {{ __('label.period_custom') }}
        </button>
    </div>

    <form method="GET" action="{{ $url }}" class="periodPickerCustom tw-items-center tw-gap-1" style="display: {{ $isCustom ? 'flex' : 'none' }};">
        <input type="hidden" name="preset" value="custom" />
        <input type="text" name="from" class="periodPickerDate" style="width: 110px;"
               placeholder="{{ __('label.period_from') }}"
               value="{{ $isCustom ? $period->from->setToUserTimezone()->formatDateForUser() : '' }}" />
        <span>–</span>
        <input type="text" name="to" class="periodPickerDate" style="width: 110px;"
               placeholder="{{ __('label.period_to') }}"
               value="{{ $isCustom ? $period->to->setToUserTimezone()->formatDateForUser() : '' }}" />
        <button type="submit" class="btn btn-sm btn-primary">{{ __('label.period_apply') }}</button>
    </form>

    <span class="tw-text-sm tw-opacity-70 periodLabel">{{ $period->label() }}</span>

</div>

@once
    @push('scripts')
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('.periodPickerDate').datepicker({
                    dateFormat: leantime.dateHelper.getFormatFromSettings('dateformat', 'jquery')
                });
            });
        </script>
    @endpush
@endonce
