@extends($layout)

@section('content')


    @include("auth::partials.onboardingProgress", ['percentComplete' => 88, 'current' => 'time', 'completed' => ['account', 'theme', 'personalization']])


    <h2>🗓️ Shaping A Daily Flow</h2>
    <p>We'll use these times to help prioritize your tasks</p>

<div class="regcontent">

    <form id="resetPassword" action="" method="post">

        <input type="hidden" name="step" value="4"/>

        {{  $tpl->displayInlineNotification() }}

        <label>What time do you usually start working?</label>
        <div class="">
            <x-global::selectable selected="{{ $daySchedule['workStart'] == '8' ? 'true' : 'false' }}" :id="'daySchedule-workStart-1'" :name="'daySchedule-workStart-button'" :value="'8'" :label="''" onclick="jQuery('#daySchedule-workStart').val('8').hide(); jQuery('#daySchedule-workStart-3').show();" class="compact">
                <label for="" class="">
                    {{ format($dayHourOptions[8]['start'], null, \Leantime\Core\Support\FromFormat::User24hTime)->time() }} - {{  format($dayHourOptions[8]['end'], null, \Leantime\Core\Support\FromFormat::User24hTime)->time()}}
                </label>
            </x-global::selectable>
            <x-global::selectable selected="{{ $daySchedule['workStart'] == '10' ? 'true' : 'false' }}" :id="'daySchedule-workStart-2'" :name="'daySchedule-workStart-button'" :value="'10'" :label="''" onclick="jQuery('#daySchedule-workStart').val('10').hide(); jQuery('#daySchedule-workStart-3').show(); " class="compact">
                <label for="" class="">
                    {{ format($dayHourOptions[10]['start'], null, \Leantime\Core\Support\FromFormat::User24hTime)->time() }} - {{  format($dayHourOptions[10]['end'], null, \Leantime\Core\Support\FromFormat::User24hTime)->time()}}
                </label>
            </x-global::selectable>
            <x-global::selectable selected="" :id="'daySchedule-workStart-3'" :name="'daySchedule-workStart-button'" :value="''" :label="''" class="compact" onclick="jQuery(this).hide(); jQuery('#daySchedule-workStart').show()">
                <label for="" class="">
                    <i class="fa fa-clock"></i> Select my own
                </label>
            </x-global::selectable>
            <select name="daySchedule-workStart" id="daySchedule-workStart" style="display:none; vertical-align: top;">
                @foreach($dayHourOptions as $key => $value)
                    <option value="{{ $key }}">
                        {{ format($value['start'], null, \Leantime\Core\Support\FromFormat::User24hTime)->time() }} - {{  format($value['end'], null, \Leantime\Core\Support\FromFormat::User24hTime)->time()}}
                    </option>
                @endforeach
            </select>
        </div>
        <br />
        <label>When do you normally take a lunch break from work?</label>
        <div class="">
            <x-global::selectable selected="{{ $daySchedule['lunch'] == '12' ? 'true' : 'false' }}" :id="'daySchedule-lunch-1'" :name="'daySchedule-lunch-button'" :value="'12'" :label="''" onclick="jQuery('#daySchedule-lunch').val('12').hide(); jQuery('#daySchedule-lunch-3').show();" class="compact">
                <label for="" class="">
                    {{ format($dayHourOptions[12]['start'], null, \Leantime\Core\Support\FromFormat::User24hTime)->time() }} - {{  format($dayHourOptions[12]['end'], null, \Leantime\Core\Support\FromFormat::User24hTime)->time()}}
                </label>
            </x-global::selectable>
            <x-global::selectable selected="{{ $daySchedule['lunch'] == '14' ? 'true' : 'false' }}" :id="'daySchedule-lunch-2'" :name="'daySchedule-lunch-button'" :value="'14'" :label="''" onclick="jQuery('#daySchedule-lunch').val('14').hide(); jQuery('#daySchedule-lunch-3').show();" class="compact">
                <label for="" class="">
                    {{ format($dayHourOptions[14]['start'], null, \Leantime\Core\Support\FromFormat::User24hTime)->time() }} - {{  format($dayHourOptions[14]['end'], null, \Leantime\Core\Support\FromFormat::User24hTime)->time()}}
                </label>
            </x-global::selectable>
            <x-global::selectable selected="" :id="'daySchedule-lunch-3'" :name="'daySchedule-lunch-button'" :value="''" :label="''" class="compact" onclick="jQuery(this).hide(); jQuery('#daySchedule-lunch').show()">
                <label for="" class="">
                    <i class="fa fa-clock"></i> Select my own
                </label>
            </x-global::selectable>
            <select name="daySchedule-lunch" id="daySchedule-lunch" style="display:none; vertical-align: top;">
                @foreach($dayHourOptions as $key => $value)
                    <option value="{{ $key }}">
                        {{ format($value['start'], null, \Leantime\Core\Support\FromFormat::User24hTime)->time() }} - {{  format($value['end'], null, \Leantime\Core\Support\FromFormat::User24hTime)->time()}}
                    </option>
                @endforeach
            </select>
        </div>
        <br />
        <label>When do you normally end your work day? 🥳</label>

        <div class="">
            <x-global::selectable selected="{{ $daySchedule['workEnd'] == '16' ? 'true' : 'false' }}" :id="'daySchedule-workEnd-1'" :name="'daySchedule-workEnd-button'" :value="'16'" :label="''" onclick="jQuery('#daySchedule-workEnd').val('16').hide(); jQuery('#daySchedule-workEnd-3').show();" class="compact">
                <label for="" class="">
                    {{ format($dayHourOptions[16]['start'], null, \Leantime\Core\Support\FromFormat::User24hTime)->time() }} - {{  format($dayHourOptions[16]['end'], null, \Leantime\Core\Support\FromFormat::User24hTime)->time()}}
                </label>
            </x-global::selectable>
            <x-global::selectable selected="{{ $daySchedule['workEnd'] == '18' ? 'true' : 'false' }}" :id="'daySchedule-workEnd-2'" :name="'daySchedule-workEnd-button'" :value="'18'" :label="''" onclick="jQuery('#daySchedule-workEnd').val('18').hide(); jQuery('#daySchedule-workEnd-3').show();" class="compact">
                <label for="" class="">
                    {{ format($dayHourOptions[18]['start'], null, \Leantime\Core\Support\FromFormat::User24hTime)->time() }} - {{  format($dayHourOptions[18]['end'], null, \Leantime\Core\Support\FromFormat::User24hTime)->time()}}
                </label>
            </x-global::selectable>
            <x-global::selectable selected="" :id="'daySchedule-workEnd-3'" :name="'daySchedule-workEnd-button'" :value="''" :label="''" class="compact" onclick="jQuery(this).hide(); jQuery('#daySchedule-workEnd').show()">
                <label for="" class="">
                    <i class="fa fa-clock"></i> Select my own
                </label>
            </x-global::selectable>
            <select name="daySchedule-workEnd" id="daySchedule-workEnd" style="display:none; vertical-align: top;">
                @foreach($dayHourOptions as $key => $value)
                    <option value="{{ $key }}">
                        {{ format($value['start'], null, \Leantime\Core\Support\FromFormat::User24hTime)->time() }} - {{  format($value['end'], null, \Leantime\Core\Support\FromFormat::User24hTime)->time()}}
                    </option>
                @endforeach
            </select>
        </div>

        {{--        <div class="tw-flex">--}}
{{--            @foreach([1,2,3,4,5,6,7] as $dayOfWeekIso)--}}
{{--                <x-global::selectable type="checkbox" class="circle" selected="{{ isset($workdays[$dayOfWeekIso]) ? 'true' : '' }}" :id="'dayOfWeek-'.$dayOfWeekIso" :name="'dayOfWeek-'.$dayOfWeekIso" :value="$dayOfWeekIso" :label="''" onclick="showTimeForm({{$dayOfWeekIso}})">--}}
{{--                    <label for="dayOfWeek-{{ $dayOfWeekIso }}" class="">--}}
{{--                        {{ substr(__('dates.day_of_week_iso-'.$dayOfWeekIso), 0, 2) }}--}}
{{--                    </label>--}}
{{--                </x-global::selectable>--}}
{{--            @endforeach--}}
{{--        </div>--}}
{{--        <div>--}}
{{--            @foreach([1,2,3,4,5,6,7] as $dayOfWeekIso)--}}
{{--                <div class="dayOfWeekInputs dayOfWeekInput-{{$dayOfWeekIso}} {{ isset($workdays[$dayOfWeekIso]) ? 'tw-flex' : 'tw-hidden' }}">--}}
{{--                    <div class="tw-w-1/4 tw-leading-[32px]">--}}
{{--                        {{ __('dates.day_of_week_iso-'.$dayOfWeekIso) }}--}}
{{--                    </div>--}}
{{--                    <div class="tw-w-1/4">--}}
{{--                        <input type="time" class="dayStart" name="dayOfWeek-{{$dayOfWeekIso}}-start" value='{{  isset($workdays[$dayOfWeekIso]) ? $workdays[$dayOfWeekIso]['start'] : '09:00'}}' step="1800"/>--}}
{{--                    </div>--}}
{{--                    <div class="tw-px-2 tw-leading-[32px]">to</div>--}}
{{--                    <div class="tw-w-1/4">--}}
{{--                        <input type="time" class="dayEnd" name="dayOfWeek-{{$dayOfWeekIso}}-end" value='{{  isset($workdays[$dayOfWeekIso]) ? $workdays[$dayOfWeekIso]['end'] : '17:00'}}' step="1800"/>--}}
{{--                    </div>--}}
{{--                    <div class="tw-w tw-leading-[32px] tw-pl-2 applyBox">--}}
{{--                        @if($loop->index == 0)--}}
{{--                            <a href="javascript:void(0)">Apply to all</a>--}}
{{--                        @endif--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            @endforeach--}}


{{--        </div>--}}
        <br /> <br />
        <div class="tw-text-right">
            <a href="{{BASE_URL}}/auth/userInvite/{{$inviteId}}?step=3" class="btn btn-secondary" style="width:auto; margin-right:10px">Back</a>
            <input type="submit" name="createAccount" class="tw-w-auto" style="width:auto" value="<?php echo $tpl->language->__("buttons.next"); ?>" />
        </div>

    </form>

</div>

<script>
    function applyToAllClick() {
        jQuery('.dayOfWeekInputs').each(function() {

            let linkParentContainer = jQuery(this);

            jQuery(this).find('.applyBox a').click(function() {
                let startInput = jQuery(linkParentContainer).find("input.dayStart").val();
                let endInput = jQuery(linkParentContainer).find("input.dayEnd").val();

                jQuery('.dayOfWeekInputs input.dayStart').val(startInput);
                jQuery('.dayOfWeekInputs input.dayEnd').val(endInput);
            });
        })
    }

    jQuery(document).ready(function() {
        applyToAllClick();

        var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        jQuery("#timezone").val(timezone);

        var now=new Date(2010,11,31);
        var str=now.toLocaleDateString();



        str=str.replace("31","dd");
        str=str.replace("12","mm");
        str=str.replace("2010","yyyy");

        console.log(str);

    })

    function showTimeForm($id) {
        let isVisible = jQuery('.dayOfWeekInput-'+$id).hasClass("tw-flex");
        if(isVisible) {
            jQuery('.dayOfWeekInput-'+$id).removeClass("tw-flex");
            jQuery('.dayOfWeekInput-'+$id).addClass("tw-hidden");
        }else{
            jQuery('.dayOfWeekInput-'+$id).addClass("tw-flex");
            jQuery('.dayOfWeekInput-'+$id).removeClass("tw-hidden");
        }

        jQuery('.dayOfWeekInputs').find('.applyBox').html("");
        jQuery('.dayOfWeekInputs.tw-flex').each(function(index){
            console.log(index);

            if(index == 0) {
                console.log( jQuery(this).find('.applyBox'));
                jQuery(this).find('.applyBox').html("<a href='javascript:void(0);'>Apply to all")
            }else{
                jQuery(this).find('.applyBox').html();
            }
        });

        applyToAllClick();

        }
</script>

@endsection
