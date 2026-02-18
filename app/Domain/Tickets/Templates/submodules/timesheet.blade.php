@php
    $values = $tpl->get('timesheetValues');
    $ticket = $tpl->get('ticket');
    $userInfo = $tpl->get('userInfo');
    $remainingHours = $tpl->get('remainingHours');
    if ($remainingHours < 0) {
        $remainingHours = 0;
    }
    $currentPay = $tpl->get('userHours') * $userInfo['wage'];
@endphp

<div class="tw:grid tw:md:grid-cols-2 tw:gap-4">
    <div>

        <h4 class="widgettitle title-light"><span class="fa fa-clock-o"></span>{{ __('headline.add_time_entry', false) }}</h4>
        <br />

        <form method="post" action="{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}#timesheet" class="formModal">

            <label for="kind">{{ __('label.timesheet_kind') }}</label>
            <span class="field">
                <x-global::forms.select id="kind" name="kind">
                    @foreach($tpl->get('kind') as $key => $row)
                        <option value="{{ $key }}" {{ $row == $values['kind'] ? 'selected="selected"' : '' }}>{{ __(strtolower($row)) }}</option>
                    @endforeach
                </x-global::forms.select>
            </span>

            <label for="timesheetdate">{{ __('label.date') }}:</label>
            <input type="text" id="timesheetdate" name="date" class="dates" value="{{ format($values['date'])->date() }}" /><br/>

            <label for="hours">{{ __('label.hours') }}</label>
            <span class="field">
                <x-global::forms.input id="hours" name="hours" value="{{ $values['hours'] }}" size="7" class="input-small" />
            </span>

            <label for="description">{{ __('label.description') }}</label>
            <span class="field">
                <x-global::forms.textarea name="description" id="description" rows="5" value="{{ $values['description'] }}" /><br />
            </span>

            <input type="hidden" name="saveTimes" value="1" />
            <x-global::button submit type="primary" name="saveTimes">{{ __('buttons.save') }}</x-global::button>
        </form>

    </div>
    <div>
        <h4 class="widgettitle title-light"><span class="fa fa-bar-chart"></span>{{ __('subtitles.logged_hours_chart') }}</h4>

        <br />
        <canvas id="canvas"></canvas>
        <p><br />
            {{ __('label.planned_hours') }}: {{ $ticket->planHours }}<br />
            {{ __('label.booked_hours') }}: {{ $tpl->get('timesheetsAllHours') }}<br />
            {{ __('label.actual_hours_remaining') }}: {{ $remainingHours }}<br />
        </p>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {

        var d2 = [];
        var d3 = [];
        var labels = [];
        @php
            $sum = 0;
            $ticketHours = $tpl->get('ticketHours');
            foreach ($ticketHours as $hours) {
                $sum = $sum + $hours['summe'];
                try {
                    echo "labels.push('" . dtHelper()->parseDbDateTime($hours['utc'])->setToUserTimezone()->format('Y-m-d') . "');\n";
                    echo "d2.push(" . $sum . ");\n";
                    echo "d3.push(" . $ticket->planHours . ");\n";
                } catch (\Exception $e) {
                    // not much we can do at this point. Ignore the datapoint
                }
            }
        @endphp

        leantime.ticketsController.initTimeSheetChart(labels, d2, d3, "canvas")

    });
</script>
