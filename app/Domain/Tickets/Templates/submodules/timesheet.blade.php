@php
$values = $timesheetValues;
if ($remainingHours < 0) {
    $remainingHours = 0;
}
$currentPay = $userHours * $userInfo['wage'];
@endphp

        <div class="row">
            <div class="col-md-6">


                <x-global::elements.section-header icon="fa fa-clock-o">{!! __('headline.add_time_entry', false) !!}</x-global::elements.section-header>
                <br />

                <form method="post" action="{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}#timesheet" class="formModal">

                    <x-global::forms.label for="kind">{!! __('label.timesheet_kind') !!}</x-global::forms.label>
                    <span class="field">
                    <x-global::forms.select enhanced id="kind" name="kind">
                    @foreach ($kind as $key => $row)
                        <option value="{{ $key }}"
                            @if ($row == $values['kind']) selected="selected" @endif
                        >{!! __(strtolower($row)) !!}</option>
                    @endforeach
                    </x-global::forms.select>
                    </span>

                    <x-global::forms.label for="timesheetdate">{!! __('label.date') !!}:</x-global::forms.label>
                    <input type="text" id="timesheetdate" name="date" class="dates" value="{{ format($values['date'])->date() }}" /><br/>

                    <x-global::forms.label for="hours">{!! __('label.hours') !!}</x-global::forms.label>
                    <span class="field">
                        <x-global::forms.text-input id="hours" name="hours" value="{{ $values['hours'] }}" size="7" width="sm" />
                    </span>
                    <x-global::forms.label for="description">{!! __('label.description') !!}</x-global::forms.label>
                    <span class="field">
                        <x-global::forms.textarea rows="5" cols="50" id="description" name="description">{{ $values['description'] }}</x-global::forms.textarea><br />
                    </span>
                    <input type="hidden" name="saveTimes" value="1" />
                    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" name="saveTimes" />

                </form>

            </div>
            <div class="col-md-6">
                <x-global::elements.section-header icon="fa fa-bar-chart">{!! __('subtitles.logged_hours_chart') !!}</x-global::elements.section-header>

                <br />
                <canvas id="canvas"></canvas>
                <p><br />
                    {!! __('label.planned_hours') !!}: {{ $ticket->planHours }}<br />
                    {!! __('label.booked_hours') !!}: {{ $timesheetsAllHours }}<br />
                    {!! __('label.actual_hours_remaining') !!}: {{ $remainingHours }}<br />
                </p>
                {{-- Parent + direct subtasks (#1798). Subtask time is logged on the subtask, so no double counting. --}}
                @if (! empty($subtaskHours['subtaskCount']))
                    <p>
                        <strong>{{ sprintf(__('label.including_subtasks'), $subtaskHours['subtaskCount']) }}</strong><br />
                        {!! __('label.planned_hours') !!}: {{ round((float) $ticket->planHours + $subtaskHours['planHours'], 2) }}<br />
                        {!! __('label.booked_hours') !!}: {{ round((float) $timesheetsAllHours + $subtaskHours['loggedHours'], 2) }}<br />
                    </p>
                @endif
            </div>
        </div>

<script type="text/javascript">

    jQuery(document).ready(function($) {

        var d2 = [];
        var d3 = [];
        var labels = [];
        @php
        // Emit every value through json_encode so the generated JS is always
        // valid. Raw concatenation broke when a value was null or non-numeric
        // (e.g. Postgres SUM()/plan hours formatting), producing a JS syntax
        // error that killed the whole time-tracking modal. (#3353)
        $sum = 0;
        $planHours = is_numeric($ticket->planHours) ? (float) $ticket->planHours : 0;
        foreach ($ticketHours as $hours) {
            $sum = $sum + (float) ($hours['summe'] ?? 0);
            try {
                $label = dtHelper()->parseDbDateTime($hours['utc'])->setToUserTimezone()->format('Y-m-d');
                echo 'labels.push(' . json_encode($label) . ");\n";
                echo 'd2.push(' . json_encode($sum) . ");\n";
                echo 'd3.push(' . json_encode($planHours) . ");\n";
            } catch (\Exception $e) {
                // not much we can do at this point. Ignore the datapoint
            }
        }
        @endphp

        leantime.ticketsController.initTimeSheetChart(labels, d2, d3, "canvas")

    });

</script>
