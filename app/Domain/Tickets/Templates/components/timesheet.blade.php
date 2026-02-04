@php
    foreach ($__data as $var => $val) {
        $$var = $val; // necessary for blade refactor
    }
    $values = $timesheetValues;
    if ($remainingHours < 0) {
        $remainingHours = 0;
    }
    $currentPay = $userHours * $userInfo['wage'];
@endphp

<div class="flex-col">
    <div class="col-md-12">


        <h4 class="widgettitle title-light"><span class="fa fa-clock-o"></span><?php echo $tpl->__('headline.add_time_entry', false); ?></h4>
        <br />

        <form hx-post="{{ BASE_URL }}/tickets/showTicket/{{ $ticket->id }}#timesheet" hx-trigger="submit"
            hx-swap="none" class="space-y-4" hx-indicator="#save-indicator">
            <input type="hidden" name="saveTimes" value="1" />

            <x-global::forms.select :label-text="__('label.timesheet_kind')" name="kind" id="kind">
                @foreach ($tpl->get('kind') as $key => $row)
                    <option value="{{ $key }}" @selected($row == $values['kind'])>
                        {{ $tpl->__(strtolower($row)) }}
                    </option>
                @endforeach
            </x-global::forms.select>

            <x-global::forms.text-input :label-text="__('label.date')" name="date" id="timesheetdate" :value="format($values['date'])->date()" />

            <x-global::forms.text-input :label-text="__('label.hours')" type="number" name="hours" id="hours"
                :value="$values['hours']" />

            <div class="form-control">
                <label for="description" class="text-sm font-medium text-gray-700">
                    {{ $tpl->__('label.description') }}
                </label>
                <textarea id="description" name="description" rows="5" class="textarea textarea-primary">{{ $values['description'] }}</textarea>
            </div>

            <br>
            <x-global::forms.button variant="primary" labelText="Save" scale="sm" />
            <div id="save-indicator" class="htmx-indicator">
                <span class="loading loading-spinner"></span> Saving...
            </div>
        </form>

    </div>
    <div class="col-md-12">
        <h4 class="widgettitle title-light"><span class="fa fa-bar-chart"></span><?php echo $tpl->__('subtitles.logged_hours_chart'); ?></h4>

        <br />
        <canvas id="canvas"></canvas>
        <p><br />
            {!! __('label.planned_hours') !!}: {{ $ticket->planHours }}<br />
            {!! __('label.booked_hours') !!}: {{ $tpl->get('timesheetsAllHours') }}<br />
            {!! __('label.actual_hours_remaining') !!}: {{ $remainingHours }}<br />
        </p>
    </div>
</div>
<script type="module">
    import "@mix('/js/Domain/Tickets/Js/ticketsController.js')"
    jQuery(document).ready(function($) {
        var d2 = [];
        var d3 = [];
        var labels = [];
        @php
            $sum = 0;
            $ticketHours = $tpl->get('ticketHours');
            foreach ($ticketHours as $hours) {
                $sum = $sum + $hours['summe'];

                echo "labels.push('" .
                    date('Y-m-d', strtotime($hours['utc'] ?? '')) .
                    "');
                                                                                            ";
                echo 'd2.push(' .
                    $sum .
                    ');
                                                                                            ';
                echo 'd3.push(' .
                    $ticket->planHours .
                    ');
                                                                                            ';
            }
        @endphp

        ticketsController.initTimeSheetChart(labels, d2, d3, "canvas")
        console.log('Timesheet chart initialized');
    });
</script>
